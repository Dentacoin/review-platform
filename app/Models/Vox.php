<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Image;

use DB;
use App;
use Carbon\Carbon;
use App\Models\VoxToCategory;
use App\Models\Reward;
use App\Models\DcnReward;
use App\Models\VoxBadge;
use App\Models\VoxRelated;

class Vox extends Model {
    
    use \Dimsav\Translatable\Translatable;
    use SoftDeletes;
    
    public $translatedAttributes = [
        'title',
        'description',
        'stats_description',
    ];

    protected $fillable = [
        'title',
        'description',
        'stats_description',
        'slug',
        'reward',
        'reward_usd',
        'duration',
        'type',
        'complex',
        'featured',
        'stats_featured',
        'has_stats',
        'hasimage',
        'hasimage_social',
        'hasimage_stats',
        'country_count',
        'questions_count',
        'respondents_count',
        'rewards_count',
        'sort_order',
    ];

    protected $dates = [
        'last_count_at',
        'created_at',
        'launched_at',
        'updated_at',
        'deleted_at'
    ];

    
    public function questions() {
        return $this->hasMany('App\Models\VoxQuestion', 'vox_id', 'id')->orderBy('order', 'ASC');
    }

    public function questionsCount() {
        $date = $this->last_count_at;
        $now = Carbon::now();

        $diff = !$this->last_count_at ? 1 : $date->diffInDays($now);

        if ($diff >= 1 || empty($this->questions_count)) {

            $this->last_count_at = Carbon::now();
            $this->questions_count = $this->questions()->count();
            $this->save();

            return $this->questions()->count();

        } else {
            return $this->questions_count;
        }
    }

    public function stats_questions() {
        return $this->hasMany('App\Models\VoxQuestion', 'vox_id', 'id')->where('used_for_stats', '!=', '')->orderBy('order', 'ASC');
    }
    
    public function stats_main_question() {
        return $this->hasOne('App\Models\VoxQuestion', 'vox_id', 'id')->where('stats_featured', '1'); // we used to show the first question in the Stats list
    }
    
    public function respondentsCount() {
        $date = $this->last_count_at;
        $now = Carbon::now();

        $diff = !$this->last_count_at ? 1 : $date->diffInDays($now);

        if ($diff >= 1 || empty($this->respondents_count)) {

            $this->last_count_at = Carbon::now();
            $this->respondents_count = DcnReward::where('reference_id', $this->id)->where('platform', 'vox')->where('type', 'survey')->has('user')->count();
            $this->save();

            return DcnReward::where('reference_id', $this->id)->where('platform', 'vox')->where('type', 'survey')->has('user')->count();

        } else {
            return $this->respondents_count;
        }
    }

    public function realRespondentsCountForAdminPurposes() {
         return DcnReward::where('reference_id', $this->id)->where('platform', 'vox')->where('type', 'survey')->has('user')->count();
    }

    public function respondentsCountryCount() {

        $date = $this->last_count_at;
        $now = Carbon::now();

        $diff = !$this->last_count_at ? 1 : $date->diffInDays($now);

        if ($diff >= 1 || empty($this->country_count)) {

            $counted_countries = DB::table('users')
            ->join('dcn_rewards', 'users.id', '=', 'dcn_rewards.user_id')
            ->where('dcn_rewards.platform', 'vox')
            ->where('dcn_rewards.type', 'survey')
            ->where('dcn_rewards.reference_id', $this->id)
            ->select(DB::raw('COUNT(*) AS `cnt`'))
            ->groupBy(DB::raw('users.country_id'))
            ->get()
            ->count();

            $this->last_count_at = Carbon::now();
            $this->country_count = $counted_countries;
            $this->save();

            return $counted_countries;

        } else {
            return $this->country_count;
        }
        
    }

    public function questionsReal() {
        return $this->hasMany('App\Models\VoxQuestion', 'vox_id', 'id')->whereNull('is_control')->orderBy('order', 'ASC');
    }

    public function rewards() {
        return $this->hasMany('App\Models\DcnReward', 'reference_id', 'id')->where('platform', 'vox')->where('type', 'survey')->orderBy('id', 'DESC');
    }

    public function rewardsCount() {
        $date = $this->last_count_at;
        $now = Carbon::now();

        $diff = !$this->last_count_at ? 1 : $date->diffInDays($now);

        if ($diff >= 1 || empty($this->rewards_count)) {

            $this->last_count_at = Carbon::now();
            $this->rewards_count = $this->rewards()->count();
            $this->save();

            return $this->rewards()->count();

        } else {
            return $this->rewards_count;
        }
    }

    public function related() {
        return $this->hasMany('App\Models\VoxRelated', 'vox_id', 'id')->orderBy('id', 'ASC');
    }

    public function formatDuration() {
        return ceil( $this->questionsCount()/6 ).' min';
    }

    public function getRewardPerQuestion() {
        $reward = json_decode(file_get_contents('/tmp/reward_vox_question'));
        if ($this->featured) {
            $reward->dcn *= 2;
            $reward->amount *= 2;
        }
        return $reward;
    }

    public function getRewardTotal($inusd = false) {
        if ($this->type == 'home') {
            return 100;
        } else if ($this->type == 'user_details') {
            return 0;
        } else {
            return ( $inusd ? $this->getRewardPerQuestion()->amount : $this->getRewardPerQuestion()->dcn) * $this->questionsCount();
        }        
    }

    public function getRewardForUser($user_id) {
        if ($this->type == 'user_details') {
            return 0;
        } else {
            $how_many_questions = VoxAnswer::where('vox_id', $this->id)->where('user_id', $user_id)->where('answer', '!=' , 0)->groupBy('question_id')->get()->count();
            $reward_per_question = $this->getRewardPerQuestion()->dcn;

            return $how_many_questions * $reward_per_question;
        }        
    }

    public function categories() {
        return $this->hasMany('App\Models\VoxToCategory', 'vox_id', 'id');
    }

    public function getStatsList() {
        return getLangUrl('dental-survey-stats/'.$this->translate(App::getLocale(), true)->slug );        
    }
    public function getLink() {
        return $this->type=='hidden' || $this->type=='normal' ? getLangUrl('paid-dental-surveys/'.$this->translate(App::getLocale(), true)->slug ) : getLangUrl( $this->translate(App::getLocale(), true)->slug );        
    }

    public function checkComplex() {

        foreach ($this->questions as $q) {
            if($q->question_trigger) {
                $this->complex = 1;
                $this->save();
            }
        }
    }


    public function getImageUrl($thumb = false) {
        return $this->hasimage ? url('/storage/voxes/'.($this->id%100).'/'.$this->id.($thumb ? '-thumb' : '').'.jpg') : url('new-vox-img/stats-dummy.png');
    }
    public function getImagePath($thumb = false) {
        $folder = storage_path().'/app/public/voxes/'.($this->id%100);
        if(!is_dir($folder)) {
            mkdir($folder);
        }
        return $folder.'/'.$this->id.($thumb ? '-thumb' : '').'.jpg';
    }

    public function addImage($img) {

        $to = $this->getImagePath();
        $to_thumb = $this->getImagePath(true);

        $img->resize(1920, null, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });
        $img->save($to);
        $img->fit( 520, 352 );
        $img->save($to_thumb);
        $this->hasimage = true;
        $this->save();
    }

    public function getSocialImageUrl($type = 'social') {
        return $this->hasimage_social ? url('/storage/voxes/'.($this->id%100).'/'.$this->id.'-'.$type.'.png').'?rev='.$this->updated_at->timestamp : url('new-vox-img/stats-dummy.png');
    }
    public function getSocialImagePath($type = 'social') {
        $folder = storage_path().'/app/public/voxes/'.($this->id%100);
        if(!is_dir($folder)) {
            mkdir($folder);
        }
        return $folder.'/'.$this->id.'-'.$type.'.png';
    }

    public function addSocialImage($img, $type='social') {

        $to = $this->getSocialImagePath($type);

        $img->fit(1920, 1005);
        $img->save($to);
        if($type=='social') {
            $this->hasimage_social = true;
        } else if($type=='for-stats') {
            $this->hasimage_stats = true;
        }
        $this->save();

        $this->regenerateSocialImages();
    }
    public function regenerateSocialImages() {

        if( $this->hasimage_social ) {
            $original = Image::make( $this->getSocialImagePath() );
            $badge_file = VoxBadge::find(1)->getImagePath(); //survey
            if(file_exists($badge_file)) {
                $original->insert( $badge_file, 'bottom-left', 0, 0);                
            }
            $original->save( $this->getSocialImagePath('survey') );
        }

        if( $this->hasimage_stats ) {
            $original = Image::make( $this->getSocialImagePath('for-stats') );
            $badge_file = VoxBadge::find(2)->getImagePath(); //stats
            if(file_exists($badge_file)) {
                $original->insert( $badge_file, 'bottom-left', 0, 0);                
            }
            $original->save( $this->getSocialImagePath('stats') );
        }

        $this->updated_at = Carbon::now();
    }



    public function setTypeAttribute($newvalue) {
        if (!empty($this->attributes['type']) && $this->attributes['type'] != 'normal' && $newvalue == 'normal' && empty($this->attributes['launched_at'])) {
            $this->attributes['launched_at'] = Carbon::now();
        }
        $this->attributes['type'] = $newvalue;
    }

    public static function getDemographicQuestions() {
        $demographic_questions = [];
        $welcome_survey = Vox::find(11);
        $welcome_questions = VoxQuestion::where('vox_id', $welcome_survey->id)->get();
        
        foreach ($welcome_questions as $welcome_question) {
            $demographic_questions[$welcome_question->id] = $welcome_question->question;
        }

        $demographic_questions['gender'] = 'What is your biological sex?';
        $demographic_questions['birthyear'] = "What's your year of birth?";
        foreach (config('vox.details_fields') as $k => $v) {
            $demographic_questions[$k] = $v['label'];
        }

        return $demographic_questions;
    }

    public static function getDemographicAnswers() {

        $welcome_answers = [];

        foreach (self::getDemographicQuestions() as $key => $value) {
            if (is_numeric($key)) {
                $welcome_question = VoxQuestion::where('id', $key)->first();
                $welcome_answers[$welcome_question->id] = json_decode($welcome_question->answers, true);
            } else {
                if ($key == 'gender') {
                    $welcome_answers['gender'] = [
                        'Male',
                        'Female'
                    ];
                } else if ($key == 'birthyear') {
                    $welcome_answers['birthyear'] = [
                        '',
                    ];
                } else {
                    $welcome_answers[$key] = config('vox.details_fields')[$key]['values'];
                }
                
            }
            
        }

        return $welcome_answers;
    }
    
}

class VoxTranslation extends Model {

    public $timestamps = false;
    protected $fillable = [
        'title',
        'description',
        'stats_description',
        'seo_title',
        'seo_description',
        'slug',
    ];

}



?>