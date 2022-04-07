<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use WebPConvert\WebPConvert;

use App\Models\VoxAnswerOld;
use App\Models\UserDevice;
use App\Models\VoxAnswer;
use App\Models\DcnReward;

use App\Helpers\GeneralHelper;
use Carbon\Carbon;

use App;
use DB;

class Vox extends Model {
    
    use \Dimsav\Translatable\Translatable;
    use SoftDeletes;
    
    public $translatedAttributes = [
        'title',
        'description',
        'stats_description',
        'slug',
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
        'translation_langs',
        'featured',
        'stats_featured',
        'has_stats',
        'hasimage',
        'hasimage_social',
        'hasimage_stats',
        'sort_order',
        'country_count',
        'questions_count',
        'dcn_questions_count',
        'manually_calc_reward',
        'respondents_count',
        'rewards_count',
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
        'thumbnail_name',
        'social_image_name',
        'stats_image_name',
    ];

    protected $dates = [
		'respondents_country_last_count_at',
        'respondents_last_count_at',
		'rewards_last_count_at',
        'created_at',
        'launched_at',
        'scheduled_at',
        'updated_at',
        'deleted_at'
    ];

    protected $casts = [
        'countries_ids' => 'array',
        'exclude_countries_ids' => 'array',
        'users_percentage' => 'array',
        'dcn_questions_triggers' => 'array',
    ];
    
    public function questions() {
        return $this->hasMany('App\Models\VoxQuestion', 'vox_id', 'id')->with('translations')->orderBy('order', 'ASC');
    }

    public function categories() {
        return $this->hasMany('App\Models\VoxToCategory', 'vox_id', 'id');
    }

    public function history() {
        return $this->hasMany('App\Models\VoxHistory', 'vox_id', 'id')->orderBy('id', 'DESC');;
    }

    public function historyOnlyVox() {
        return $this->hasMany('App\Models\VoxHistory', 'vox_id', 'id')->whereNull('question_id')->orderBy('id', 'DESC');;
    }

    public function historyOnlyVoxQuestions() {
        return $this->hasMany('App\Models\VoxHistory', 'vox_id', 'id')->whereNotNull('question_id')->orderBy('id', 'DESC');;
    }

    public function stats_questions() {
        return $this->hasMany('App\Models\VoxQuestion', 'vox_id', 'id')->with('translations')->where('used_for_stats', '!=', '')->orderBy('order', 'ASC');
    }
    
    public function stats_main_question() {
        return $this->hasOne('App\Models\VoxQuestion', 'vox_id', 'id')->where('stats_featured', '1'); // we used to show the first question in the Stats list
    }

    public function questionsReal() {
        return $this->hasMany('App\Models\VoxQuestion', 'vox_id', 'id')->whereNull('is_control')->orderBy('order', 'ASC');
    }

    public function rewards() {
        return $this->hasMany('App\Models\DcnReward', 'reference_id', 'id')->where('platform', 'vox')->where('type', 'survey')->orderBy('id', 'DESC');
    }

    public function processingForTranslations() {
        return $this->hasMany('App\Models\VoxCronjobLang', 'vox_id', 'id')->whereNull('is_completed');
    }

    public function related() {
        return $this->hasMany('App\Models\VoxRelated', 'vox_id', 'id')->orderBy('id', 'ASC');
    }

    public function questionsCount() {
        return !empty($this->questions_count) ? $this->questions_count : $this->questions->count();
    }
    
    public function respondentsCount() {
        $date = $this->respondents_last_count_at;
        $now = Carbon::now();

        $diff = !$this->respondents_last_count_at ? 1 : $date->diffInDays($now);

        if ($diff >= 1) {

            $this->respondents_last_count_at = Carbon::now();
            $this->respondents_count = DcnReward::where('reference_id', $this->id)
            ->where('platform', 'vox')
            ->where('type', 'survey')
            ->has('user')
            ->count();
            $this->save();

            return DcnReward::where('reference_id', $this->id)
            ->where('platform', 'vox')
            ->where('type', 'survey')
            ->has('user')
            ->count();

        } else {
            return $this->respondents_count;
        }
    }

    public function realRespondentsCountForAdminPurposes() {
        return DcnReward::where('reference_id', $this->id)
        ->where('platform', 'vox')
        ->where('type', 'survey')
        ->has('user')
        ->count();
    }

    public function respondentsCountryCount() {

        $date = $this->respondents_country_last_count_at;
        $now = Carbon::now();

        $diff = !$this->respondents_country_last_count_at ? 1 : $date->diffInDays($now);

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

            $this->respondents_country_last_count_at = Carbon::now();
            $this->country_count = $counted_countries;
            $this->save();

            return $counted_countries;

        } else {
            return $this->country_count;
        }
    }

    public function rewardsCount() {
        $date = $this->rewards_last_count_at;
        $now = Carbon::now();

        $diff = !$this->rewards_last_count_at ? 1 : $date->diffInDays($now);

        if ($diff >= 1 || empty($this->rewards_count)) {
            $rewards_count = $this->rewards()->count();

            $this->rewards_last_count_at = Carbon::now();
            $this->rewards_count = $rewards_count;
            $this->save();

            return $rewards_count;

        } else {
            return $this->rewards_count;
        }
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

    public function getRewardForUser($user, $answers_count) {
        if ($this->type == 'user_details') {
            return 0;
        } else {
            $reward_per_question = $this->getRewardPerQuestion()->dcn;

            $double_reward = 1;

            if(!empty($user->vip_access)) {
                $double_reward = 2;
            }

            return $answers_count * $reward_per_question * $double_reward;
        }        
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
        if($this->hasimage) {
            if(!empty($this->thumbnail_name)) {
                return url('/storage/voxes/'.($this->id%100).'/'.$this->thumbnail_name.($thumb ? '-thumb' : '').'.jpg').'?rev=1'.$this->updated_at->timestamp;
            } else {
                return url('/storage/voxes/'.($this->id%100).'/'.$this->id.($thumb ? '-thumb' : '').'.jpg').'?rev=1'.$this->updated_at->timestamp;
            }
        } else {
            return url('new-vox-img/stats-dummy.png');
        }
    }

    public function getImagePath($thumb = false, $name) {
        $folder = storage_path().'/app/public/voxes/'.($this->id%100);
        if(!is_dir($folder)) {
            mkdir($folder);
        }
        return $folder.'/'.$name.($thumb ? '-thumb' : '').'.jpg';
    }

    public function addImage($img, $name) {
        $extensions = ['image/jpeg', 'image/png'];
        
        if (in_array($img->mime(), $extensions)) {
            $to = $this->getImagePath(false, $name);
            $to_thumb = $this->getImagePath(true, $name);

            $img->resize(1920, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            $img->save($to);
            $img->fit( 520, 352 );
            $img->save($to_thumb);

            $this->thumbnail_name = $name;
            $this->hasimage = true;
            $this->save();

            $destination = $this->getImagePath(false, $name).'.webp';
            WebPConvert::convert($this->getImagePath(false, $name), $destination, []);

            $destination_thumb = $this->getImagePath(true, $name).'.webp';
            WebPConvert::convert($this->getImagePath(true, $name), $destination_thumb, []);
        }
    }

    public function getSocialImageUrl($type = 'social') {
        
        if($this->hasimage_social) {
            if(!empty($this->social_image_name)) {
                return url('/storage/voxes/'.($this->id%100).'/'.($type == 'social' ? $this->social_image_name : $this->stats_image_name).'.png').'?rev=1'.$this->updated_at->timestamp;
            } else {
                return url('/storage/voxes/'.($this->id%100).'/'.$this->id.'-'.$type.'.png').'?rev=1'.$this->updated_at->timestamp;
            }
        } else {
            return url('new-vox-img/stats-dummy.png');
        }
    }

    public function getSocialImagePath($type = 'social', $name=null) {

        $folder = storage_path().'/app/public/voxes/'.($this->id%100);
        if(!is_dir($folder)) {
            mkdir($folder);
        }

        if(!empty($name)) {
            return $folder.'/'.$name.'.png';
        } else {
            return $folder.'/'.$this->id.'-'.$type.'.png';
        }
    }

    public function addSocialImage($img, $name, $type='social') {

        $to = $this->getSocialImagePath($type, $name);
        $img->fit(1920, 1005);
        $img->save($to);
        if($type=='social') {
            $this->hasimage_social = true;
            $this->social_image_name = $name;
        } else if($type=='for-stats') {
            $this->hasimage_stats = true;
            $this->stats_image_name = $name;
        }
        $this->save();
    }

    public function setTypeAttribute($newvalue) {
        if (!empty($this->attributes['type']) && $this->attributes['type'] != 'normal' && $newvalue == 'normal') {

            if(empty($this->attributes['launched_at'])) {
                $this->attributes['launched_at'] = Carbon::now();
            }

            $this->attributes['questions_count'] = $this->questions->count();
            $this->save();
        }
        $this->attributes['type'] = $newvalue;
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

    public function getTranslationLangsAttribute($value) {
        if(!empty($value)) {
            return explode(',', $value);            
        }
        return [];
    }
    
    public function setTranslationLangsAttribute($value) {
        $this->attributes['translation_langs'] = null;
        if(!empty($value) && is_array($value)) {
            $this->attributes['translation_langs'] = implode(',', $value);            
        }
    }

    public function recalculateUsersPercentage($user) {

        if(!empty($this->country_percentage) ) {
            $country = $user->country_id;

            $respondents_users = DcnReward::where('reference_id', $this->id)
            ->where('platform', 'vox')
            ->where('type', 'survey')
            ->has('user')
            ->get();

            if ($respondents_users->count() > 9) {

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
                    $arr[$key] = round((($value / $respondents_users->count()) * 100), 2);
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

                if(!empty($triggers)) {

                    $triggers = explode(';', $triggers);

                    $triggerSuccess = [];

                    foreach ($triggers as $trigger) {

                        list($triggerId, $triggerAnswers) = explode(':', $trigger);

                        $trigger_question = VoxQuestion::find($triggerId);
                        if ($trigger_question && $trigger_question->type == 'multiple_choice') {

                            $triggerSuccess[] = true;

                        } else {

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

                            if(!empty($allowedAnswers)) {
                                if(!empty($givenAnswers[$triggerId]) && strpos(',',$givenAnswers[$triggerId]) !== false) {
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

                                    if(strpos($allowedAnswers[0], '>') !== false) {
                                        $trg_ans = substr($allowedAnswers[0], 1);

                                        if(intval($givenAnswers[$triggerId]) > intval($trg_ans)) {
                                            $triggerSuccess[] = true;
                                        } else {
                                            $triggerSuccess[] = false;
                                        }
                                    } else if(strpos($allowedAnswers[0], '<') !== false) {
                                        $trg_ans = substr($allowedAnswers[0], 1);

                                        if(intval($givenAnswers[$triggerId]) < intval($trg_ans)) {
                                            $triggerSuccess[] = true;
                                        } else {
                                            $triggerSuccess[] = false;
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
                            }
                        }
                    }

                    if( $q->trigger_type == 'or' ) { // ANY of the conditions should be met (A or B or C)
                        if( in_array(true, $triggerSuccess) ) {
                            $res++;
                        }
                    }  else { //ALL the conditions should be met (A and B and C)
                        if( !in_array(false, $triggerSuccess) ) {
                            $res++;
                        }
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

    public function voxCountryRestricted($user) {

        if(!empty($user->country_id) && empty($user->vip_access)) {

            $has_started_the_survey = VoxAnswer::where('vox_id', $this->id)->where('user_id', $user->id)->first();

            if(empty($has_started_the_survey)) {
                $has_started_the_survey = VoxAnswerOld::where('vox_id', $this->id)->where('user_id', $user->id)->first();
            }

            return !empty($this->country_percentage) && !empty($this->users_percentage) && array_key_exists($user->country_id, $this->users_percentage) && $this->users_percentage[$user->country_id] >= $this->country_percentage && empty($has_started_the_survey) && ( (!empty($this->exclude_countries_ids) && !in_array($user->country_id, $this->exclude_countries_ids)) || empty($this->exclude_countries_ids));
        } else {
            return false;
        }
    }

    public function convertForResponse() {
        $arr = $this->toArray();
        $arr['categories'] = [];
        if($this->categories->isNotEmpty()) {
            foreach ($this->categories as $cat) {
                $arr['categories'][$cat->category->id] = $cat->category->name;
            }
        }
        $arr['avatar'] = $this->getImageUrl();
        $arr['thumb'] = $this->getImageUrl(true);
        $arr['rewardTotal'] = $this->getRewardTotal();
        $arr['rewardSingle'] = $this->getRewardPerQuestion()->dcn;
        $arr['duration'] = $this->formatDuration();

        return $arr;
    }

    public function activeVox() {

        $urls = [
            'https://hub-app-api.dentacoin.com/internal-api/push-notification/',
            'https://dcn-hub-app-api.dentacoin.com/manage-push-notifications'
        ];

        foreach ($urls as $url) {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_POST => 1,
                CURLOPT_URL => $url,
                CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_POSTFIELDS => array(
                    'data' => GeneralHelper::encrypt(json_encode(array('type' => 'new-survey')))
                )
            ));
             
            $resp = json_decode(curl_exec($curl));
            curl_close($curl);
        }

        UserDevice::sendPush('New paid survey published!', 'Take it now', [
            'page' => '/paid-dental-surveys/'.$this->slug,
        ]);
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