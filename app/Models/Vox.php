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
use App\Models\VoxAnswer;

use WebPConvert\WebPConvert;

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
        'dcn_questions_count',
        'manually_calc_reward',
        'respondents_count',
        'rewards_count',
        'sort_order',
        'gender',
        'marital_status',
        'children',
        'household_children',
        'education',
        'employment',
        'job',
        'job_title',
        'income',
        'age',
        'country_percentage',
        'dentists_patients',
    ];

    protected $dates = [
        'last_count_at',
        'respondents_last_count_at',
        'created_at',
        'launched_at',
        'updated_at',
        'deleted_at'
    ];

    protected $casts = [
        'countries_ids' => 'array',
        'users_percentage' => 'array',
        'dcn_questions_triggers' => 'array',
    ];

    
    public function questions() {
        return $this->hasMany('App\Models\VoxQuestion', 'vox_id', 'id')->orderBy('order', 'ASC');
    }

    public function questionsCount() {
        if ($this->type == 'hidden') {
            return $this->questions()->count();
        } else {
            
            $date = $this->last_count_at;
            $now = Carbon::now();

            $diff = !$this->last_count_at ? 1 : $date->diffInDays($now);

            if ($diff >= 1) {

                $this->questions_count = $this->questions()->count();
                $this->last_count_at = Carbon::now();
                $this->save();

                return $this->questions()->count();

            } else {
                return !empty($this->questions_count) ? $this->questions_count : $this->questions()->count();
            }
        }
    }

    public function stats_questions() {
        return $this->hasMany('App\Models\VoxQuestion', 'vox_id', 'id')->where('type', '!=', 'rank')->where('used_for_stats', '!=', '')->orderBy('order', 'ASC');
    }
    
    public function stats_main_question() {
        return $this->hasOne('App\Models\VoxQuestion', 'vox_id', 'id')->where('stats_featured', '1'); // we used to show the first question in the Stats list
    }
    
    public function respondentsCount() {
        $date = $this->respondents_last_count_at;
        $now = Carbon::now();

        $diff = !$this->respondents_last_count_at ? 1 : $date->diffInDays($now);

        if ($diff >= 1) {

            $this->respondents_last_count_at = Carbon::now();
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
            return ( $inusd ? $this->getRewardPerQuestion()->amount : $this->getRewardPerQuestion()->dcn) * (!empty($this->manually_calc_reward) && !empty($this->dcn_questions_count) ? $this->dcn_questions_count : $this->questionsCount());
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

        $destination = self::getImagePath().'.webp';
        WebPConvert::convert(self::getImagePath(), $destination, []);

        $destination_thumb = self::getImagePath(true).'.webp';
        WebPConvert::convert(self::getImagePath(true), $destination_thumb, []);
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

    public function getGenderAttribute($value) {
        if(!empty($value)) {
            return explode(',', $value);            
        }
        return [];
    }
    
    public function setGenderAttribute($value) {
        $this->attributes['gender'] = null;
        if(!empty($value) && is_array($value)) {
            $this->attributes['gender'] = implode(',', $value);            
        }
    }

    public function getMaritalStatusAttribute($value) {
        if(!empty($value)) {
            return explode(',', $value);            
        }
        return [];
    }
    
    public function setMaritalStatusAttribute($value) {
        $this->attributes['marital_status'] = null;
        if(!empty($value) && is_array($value)) {
            $this->attributes['marital_status'] = implode(',', $value);            
        }
    }

    public function getChildrenAttribute($value) {
        if(!empty($value)) {
            return explode(',', $value);            
        }
        return [];
    }
    
    public function setChildrenAttribute($value) {
        $this->attributes['children'] = null;
        if(!empty($value) && is_array($value)) {
            $this->attributes['children'] = implode(',', $value);            
        }
    }

    public function getHouseholdChildrenAttribute($value) {
        if(!empty($value)) {
            return explode(',', $value);            
        }
        return [];
    }
    
    public function setHouseholdChildrenAttribute($value) {
        $this->attributes['household_children'] = null;
        if(!empty($value) && is_array($value)) {
            $this->attributes['household_children'] = implode(',', $value);            
        }
    }

    public function getEducationAttribute($value) {
        if(!empty($value)) {
            return explode(',', $value);            
        }
        return [];
    }
    
    public function setEducationAttribute($value) {
        $this->attributes['education'] = null;
        if(!empty($value) && is_array($value)) {
            $this->attributes['education'] = implode(',', $value);            
        }
    }

    public function getEmploymentAttribute($value) {
        if(!empty($value)) {
            return explode(',', $value);            
        }
        return [];
    }
    
    public function setEmploymentAttribute($value) {
        $this->attributes['employment'] = null;
        if(!empty($value) && is_array($value)) {
            $this->attributes['employment'] = implode(',', $value);            
        }
    }

    public function getJobAttribute($value) {
        if(!empty($value)) {
            return explode(',', $value);            
        }
        return [];
    }
    
    public function setJobAttribute($value) {
        $this->attributes['job'] = null;
        if(!empty($value) && is_array($value)) {
            $this->attributes['job'] = implode(',', $value);            
        }
    }

    public function getJobTitleAttribute($value) {
        if(!empty($value)) {
            return explode(',', $value);            
        }
        return [];
    }
    
    public function setJobTitleAttribute($value) {
        $this->attributes['job_title'] = null;
        if(!empty($value) && is_array($value)) {
            $this->attributes['job_title'] = implode(',', $value);            
        }
    }

    public function getIncomeAttribute($value) {
        if(!empty($value)) {
            return explode(',', $value);            
        }
        return [];
    }
    
    public function setIncomeAttribute($value) {
        $this->attributes['income'] = null;
        if(!empty($value) && is_array($value)) {
            $this->attributes['income'] = implode(',', $value);            
        }
    }

    public function getAgeAttribute($value) {
        if(!empty($value)) {
            return explode(',', $value);            
        }
        return [];
    }
    
    public function setAgeAttribute($value) {
        $this->attributes['age'] = null;
        if(!empty($value) && is_array($value)) {
            $this->attributes['age'] = implode(',', $value);            
        }
    }

    public function getDentistsPatientsAttribute($value) {
        if(!empty($value)) {
            return explode(',', $value);            
        }
        return [];
    }
    
    public function setDentistsPatientsAttribute($value) {
        $this->attributes['dentists_patients'] = null;
        if(!empty($value) && is_array($value)) {
            $this->attributes['dentists_patients'] = implode(',', $value);
        }
    }

    public function recalculateUsersPercentage($user) {

        if(!empty($this->country_percentage) ) {
            $country = $user->country_id;

            $respondents_count = $this->realRespondentsCountForAdminPurposes();

            if ($respondents_count > 9) {
                
                $respondents_users = DcnReward::where('reference_id', $this->id)->where('platform', 'vox')->where('type', 'survey')->has('user')->get();

                $arr = [];
                foreach ($respondents_users as $ru) {
                    if (!empty($ru->user->country_id)) {

                        if (!isset($arr[$ru->user->country_id])) {
                            $arr[$ru->user->country_id] = 0;
                        }
                        $arr[$ru->user->country_id] += 1;
                    }
                }

                foreach ($arr as $key => $value) {
                    $arr[$key] = round((($value / $respondents_count) * 100), 2);
                }

                $this->users_percentage = $arr;
                $this->save();
            }
        }
    }

    public function getLongestPath() {
        $res = 0;

        $givenAnswers = [];

        foreach ($this->questions as $q) {
            //Davame otgovor
            $givenAnswers = $this->dcn_questions_triggers;

            //Ako ima trigger
            if($q->question_trigger) {

                //Ako e same as previous
                if($q->question_trigger=='-1') {
                    foreach ($this->questions as $originalTrigger) {
                        if($originalTrigger->id == $q->id) {
                            break;
                        }

                        if( $originalTrigger->question_trigger && $originalTrigger->question_trigger!='-1' ) {
                           $triggers = $originalTrigger->question_trigger;
                        }
                    }
                } else {
                    $triggers = $q->question_trigger;
                }

                $triggers = explode(';', $triggers);

                $triggerSuccess = [];

                foreach ($triggers as $trigger) {

                    list($triggerId, $triggerAnswers) = explode(':', $trigger);

                    if(mb_strpos($triggerAnswers, '!')!==false) {
                        $invert_trigger_logic = true;
                        $triggerAnswers = substr($triggerAnswers, 1);
                    } else {
                        $invert_trigger_logic = false;
                    }

                    if(mb_strpos($triggerAnswers, '-')!==false) {
                        list($from, $to) = explode('-', $triggerAnswers);

                        $allowedAnswers = [];
                        for ($i=$from; $i <= $to ; $i++) { 
                            $allowedAnswers[] = $i;
                        }

                    } else {
                        $allowedAnswers = explode(',', $triggerAnswers);
                    }

                    //echo 'Trigger for: '.$triggerId.' / Valid answers '.var_export($triggerAnswers, true).' / Answer: '.$answers[$triggerId].'<br/>';

                    if(!empty($givenAnswers[$triggerId]) && strpos(',',$givenAnswers[$triggerId]) !== 0) {
                        $given_answers_array = explode(',', $givenAnswers[$triggerId]);

                        $found = false;
                        foreach ($given_answers_array as $key => $value) {
                            if(in_array($value, $allowedAnswers)) {
                                $found = true;
                                break;
                            }
                        }

                        if($invert_trigger_logic) {
                            if(!$found) {
                                $triggerSuccess[] = true;
                            } else {
                                $triggerSuccess[] = false;
                            }
                        } else {

                            if($found) {
                                $triggerSuccess[] = true;
                            } else {
                                $triggerSuccess[] = false;
                            }
                        }

                    } else {

                        if($invert_trigger_logic) {
                            if( !empty($givenAnswers[$triggerId]) && !in_array($givenAnswers[$triggerId], $allowedAnswers) ) {
                                $triggerSuccess[] = true;
                            } else {
                                $triggerSuccess[] = false;
                            }
                        } else {

                            if( !empty($givenAnswers[$triggerId]) && in_array($givenAnswers[$triggerId], $allowedAnswers) ) {
                                $triggerSuccess[] = true;
                            } else {
                                $triggerSuccess[] = false;
                            }
                        }
                    }
                }

                //dd($triggerSuccess);

                if( $q->trigger_type == 'or' ) { // ANY of the conditions should be met (A or B or C)
                    if( in_array(true, $triggerSuccess) ) {
                        $res++;
                    }
                }  else { //ALL the conditions should be met (A and B and C)
                    if( !in_array(false, $triggerSuccess) ) {
                        $res++;
                    }
                }

            } else {
                //Inache go go pravim vinagi
                $res++;
            }
        }

        $this->dcn_questions_count = $res;
        $this->save();
    }

    public static function getBirthyearOptions() {
        $ret = '';        

        for($i=(date('Y')-18);$i>=(date('Y')-90);$i--) {
            $age = date('Y') - $i;

            if ($age <= 24) {
                $index = '1';
            } else if($age <= 34) {
                $index = '2';
            } else if($age <= 44) {
                $index = '3';
            } else if($age <= 54) {
                $index = '4';
            } else if($age <= 64) {
                $index = '5';
            } else if($age <= 74) {
                $index = '6';
            } else if($age > 74) {
                $index = '7';
            }

            $ret .= '<option value="'.$i.'" demogr-index="'.$index.'">'.$i.'</option>';
        }

        return $ret;
    }
}

class VoxTranslation extends Model {

    public $timestamps = false;
    protected $fillable = [
        'title',
        'description',
        'stats_description',
        'slug',
    ];

}



?>