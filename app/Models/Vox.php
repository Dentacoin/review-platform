<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use WebPConvert\WebPConvert;

use App\Models\UserDevice;
use App\Models\VoxAnswer;
use App\Models\DcnReward;
use App\Models\VoxBadge;
use App\Models\VoxScale;
use App\Models\User;

use Carbon\Carbon;

use Image;
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
        return $this->hasMany('App\Models\VoxQuestion', 'vox_id', 'id')->where('used_for_stats', '!=', '')->orderBy('order', 'ASC');
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
            $rewards_count = $this->rewards()->count();

            $this->last_count_at = Carbon::now();
            $this->rewards_count = $rewards_count;
            $this->save();

            return $rewards_count;

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
        return $this->hasimage_social ? url('/storage/voxes/'.($this->id%100).'/'.$this->id.'-'.$type.'.png').'?rev=1'.$this->updated_at->timestamp : url('new-vox-img/stats-dummy.png');
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

            $respondents_users = DcnReward::where('reference_id', $this->id)->where('platform', 'vox')->where('type', 'survey')->has('user')->get();

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

            return !empty($this->country_percentage) && !empty($this->users_percentage) && array_key_exists($user->country_id, $this->users_percentage) && $this->users_percentage[$user->country_id] >= $this->country_percentage && empty($has_started_the_survey) && ( (!empty($this->exclude_countries_ids) && !in_array($user->country_id, $this->exclude_countries_ids)) || empty($this->exclude_countries_ids));
        } else {
            return false;
        }
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

    public static function exportStatsXlsx($vox, $q, $demographics, $results, $scale_for, $all_period, $is_admin) {

        $cols = ['Survey Date'];
        $cols2 = [''];

        foreach ($demographics as $dem) {
            if($dem != 'relation') {
                $cols[] = config('vox.stats_scales')[$dem];
                $cols2[] = '';
            }
        }

        $slist = VoxScale::get();
        $scales = [];
        foreach ($slist as $sitem) {
            $scales[$sitem->id] = $sitem;
        }

        if(in_array('relation', $demographics) && $q->used_for_stats == 'dependency') {
            if(!empty($q->stats_answer_id)) {

                $list = $q->related->vox_scale_id && !empty($scales[$q->related->vox_scale_id]) ? explode(',', $scales[$q->related->vox_scale_id]->answers) :  json_decode($q->related->answers, true);

                $cols[] = $q->related->questionWithoutTooltips().' ['.$q->removeAnswerTooltip($list[$q->stats_answer_id - 1]).']';
                $cols2[] = '';
            } else {
                if($q->related->type == 'multiple_choice') {
                    $list = $q->related->vox_scale_id && !empty($scales[$q->related->vox_scale_id]) ? explode(',', $scales[$q->related->vox_scale_id]->answers) :  json_decode($q->related->answers, true);
                    foreach ($list as $l) {
                        $cols[] = $q->related->questionWithoutTooltips();
                        $cols2[] = mb_substr($l, 0, 1)=='!' ? mb_substr($l, 1) : $l;
                    }
                } else {
                    $cols[] = $q->related->questionWithoutTooltips();
                    $cols2[] = '';
                }
            }
        }

        if( $q->type == 'single_choice' || $q->type == 'number') {
            $cols[] = in_array('relation', $demographics) && $q->used_for_stats == 'dependency' ? $q->questionWithoutTooltips() : strip_tags(!empty($q->stats_title_question) ? $q->questionWithoutTooltips() : (!empty($q->stats_title) ? $q->stats_title : $q->questionWithoutTooltips() ));
            $cols2[] = '';

        } else if( $q->type == 'scale' ) {
            $list = json_decode($q->answers, true);
            $cols[] = $q->stats_title.' ['.$list[($scale_for - 1)].']';
            $cols2[] = '';

        } else if( $q->type == 'rank' ) {
            $list = $q->vox_scale_id && !empty($scales[$q->vox_scale_id]) ? explode(',', $scales[$q->vox_scale_id]->answers) :  json_decode($q->answers, true);
            foreach ($list as $l) {
                $cols[] = in_array('relation', $demographics) && $q->used_for_stats == 'dependency' ? $q->questionWithoutTooltips() : strip_tags(!empty($q->stats_title_question) ? $q->questionWithoutTooltips() : (!empty($q->stats_title) ? $q->stats_title : $q->questionWithoutTooltips() ));
                $cols2[] = $q->removeAnswerTooltip(mb_substr($l, 0, 1)=='!' ? mb_substr($l, 1) : $l);
            }

        } else if( $q->type == 'multiple_choice' ) {
            $list = $q->vox_scale_id && !empty($scales[$q->vox_scale_id]) ? explode(',', $scales[$q->vox_scale_id]->answers) :  json_decode($q->answers, true);

            $list_done = [];
            foreach ($list as $k => $elm) {
                if(mb_strpos($elm, '!')===0 || mb_strpos($elm, '#')===0) {
                    $list_done[$k] = mb_substr($elm, 1);
                } else {
                    $list_done[$k] = $elm;
                }
            }

            foreach ($list_done as $l) {
                $cols[] = in_array('relation', $demographics) && $q->used_for_stats == 'dependency' ? $q->questionWithoutTooltips() : strip_tags(!empty($q->stats_title_question) ? $q->questionWithoutTooltips() : (!empty($q->stats_title) ? $q->stats_title : $q->questionWithoutTooltips() ));
                $cols2[] = $q->removeAnswerTooltip(mb_substr($l, 0, 1)=='!' ? mb_substr($l, 1) : $l);
            }
        }
        // echo $scale_for.'<br/>';

        if($q->type == 'scale') {
            $breakdown_results = clone $results;
            $breakdown_results = $breakdown_results->where('scale', $scale_for)->groupBy('user_id')->get();
            $all_results = $results->where('answer', $scale_for)->get();
        } else if ($q->type == 'rank' || $q->type == 'multiple_choice') {
            $breakdown_results = clone $results;
            $breakdown_results = $breakdown_results->groupBy('user_id')->get();
            $all_results = $results->get();
        } else {
            $all_results = $results->get();
            $breakdown_results = $all_results;
        }

        // if($q->type == 'scale') {
        //     $results_resp = clone $results;
        //     $results_resp = $results_resp->where('scale', $scale_for)->groupBy('user_id')->get()->count();
        // } else {
            $results_resp = clone $results;
            $results_resp = $results_resp->groupBy('user_id')->get()->count();
        // }
        // dd($results_resp, $results);

        $cols_title = [
            strtoupper($vox->title).', Base: '.$results_resp.' respondents, '.$all_period
        ];


        if(!empty($is_admin)) {
            if(!empty($scale_for)) {
                $list = json_decode($q->answers, true);
                $t_stats = strip_tags(!empty($q->stats_title_question) ? $q->questionWithoutTooltips() : (!empty($q->stats_title) ? $q->stats_title : $q->questionWithoutTooltips() )).' ['.$list[($scale_for - 1)].']';
            } else {
                $t_stats = ($q->type == 'multiple_choice' ? '[Multiple choice] ' : '' ).strip_tags(!empty($q->stats_title_question) ? $q->questionWithoutTooltips() : (!empty($q->stats_title) ? $q->stats_title : $q->questionWithoutTooltips() ));
            }

            $rows = [
                $cols_title,
                [$t_stats],
                $cols,
                $cols2
            ];
        } else {
            $rows = [
                $cols_title,
                $cols,
                $cols2
            ];
        }

        // dd($breakdown_results);

        foreach ($all_results as $answ) {
            $row = [];

            $row[] = $answ->created_at ? $answ->created_at->format('d.m.Y') : '';

            foreach ($demographics as $dem) {
                if($dem != 'relation') {

                    if($dem == 'gender') {

                        if(!empty($answ->gender)) {
                            $row[] = $answ->gender=='m' ? 'Male '.$answ->user_id : 'Female '.$answ->user_id;
                        } else {
                            $row[] = '0';
                        }

                    } else if($dem == 'age') {
                        if(!empty($answ->age)) {

                            $row[] = config('vox.age_groups.'.$answ->age);
                        } else {
                            $row[] = '0';
                        }

                    } else if($dem == 'country_id') {

                        if(!empty($answ->country_id)) {
                            $row[] = $answ->country->name;
                        } else {
                            $row[] = '0';
                        }

                    } else {
                        $row[] = !empty($answ->$dem) ? config('vox.details_fields.'.$dem.'.values')[$answ->$dem] : '';
                    }
                }
            }

            if(in_array('relation', $demographics) && $q->used_for_stats == 'dependency') {
                if(!empty($q->stats_answer_id)) {

                    $list = $q->related->vox_scale_id && !empty($scales[$q->related->vox_scale_id]) ? explode(',', $scales[$q->related->vox_scale_id]->answers) :  json_decode($q->related->answers, true);

                    $row[] = $q->removeAnswerTooltip($list[$q->stats_answer_id - 1]);
                } else {
                    if($q->related->type == 'multiple_choice') {
                        $list = $q->related->vox_scale_id && !empty($scales[$q->related->vox_scale_id]) ? explode(',', $scales[$q->related->vox_scale_id]->answers) : json_decode($q->related->answers, true);
                        $i=1;
                        foreach ($list as $l) {
                            $thisanswer = $i == $answ->answer;
                            $row[] = $thisanswer ? '1' : '0';
                            $i++;
                        }
                    } else {

                        $list = $q->related->vox_scale_id && !empty($scales[$q->related->vox_scale_id]) ? explode(',', $scales[$q->related->vox_scale_id]->answers) :  json_decode($q->related->answers, true);

                        $given_related_answer = VoxAnswer::whereNull('is_admin')->where('user_id', $answ->user_id)->where('question_id', $q->related->id)->first();
                        $row[] = $given_related_answer ? $q->removeAnswerTooltip(mb_strpos($list[$given_related_answer->answer - 1], '!')===0 || mb_strpos($list[$given_related_answer->answer - 1], '#')===0 ?  mb_substr($list[$given_related_answer->answer - 1], 1) : $list[$given_related_answer->answer - 1]) : '0';
                    }
                }
            }

            if( $q->type == 'single_choice' ) {
                $answerwords = $q->vox_scale_id && !empty($scales[$q->vox_scale_id]) ? explode(',', $scales[$q->vox_scale_id]->answers) : json_decode($q->answers, true);

                if(isset( $answerwords[ ($answ->answer)-1 ] )) {
                    if(mb_strpos($answerwords[ ($answ->answer)-1 ], '!')===0 || mb_strpos($answerwords[ ($answ->answer)-1 ], '#')===0) {
                        $row[] = strip_tags($q->removeAnswerTooltip(mb_substr($answerwords[ ($answ->answer)-1 ], 1)));
                    } else {
                        $row[] = strip_tags($q->removeAnswerTooltip($answerwords[ ($answ->answer)-1 ]));
                    }
                } else {
                    $row[] = '0';
                }
                
            } else if( $q->type == 'scale' ) {

                $list = json_decode($q->answers, true);
                $answerwords = $q->vox_scale_id && !empty($scales[$q->vox_scale_id]) ? explode(',', $scales[$q->vox_scale_id]->answers) : json_decode($q->answers, true);
                $row[] = isset( $answerwords[ ($answ->scale)-1 ] ) ? $answerwords[ ($answ->scale)-1 ] : '0';

            } else if( $q->type == 'rank' ) {
                $vox_answers = VoxAnswer::where('user_id', $answ->user_id)->where('question_id', $q->id)->get();
                foreach ($vox_answers as $va) {
                    $row[] = $va->scale;
                }
                
            } else if( $q->type == 'multiple_choice' ) {
                $list = $q->vox_scale_id && !empty($scales[$q->vox_scale_id]) ? explode(',', $scales[$q->vox_scale_id]->answers) : json_decode($q->answers, true);

                $i=1;
                foreach ($list as $l) {
                    $thisanswer = $i == $answ->answer;
                    $row[] = $thisanswer ? '1' : '0';
                    $i++;
                }
            } else if($q->type == 'number') {
                $row[] = $answ->answer;
            }

            $rows[] = $row;
        }

        $rows[] = [''];

        $flist['Raw Data'] = $rows;



        ///Breakdown Sheet

        $answers_array = $q->vox_scale_id && !empty($scales[$q->vox_scale_id]) ? explode(',', $scales[$q->vox_scale_id]->answers) :  json_decode($q->answers, true);

        $breakdown_rows_count = 0;

        if($q->type == 'scale') {
            $results_total = $results->where('answer', $scale_for)->select(DB::raw('count(distinct `user_id`) as num'))->first()->num;
        } else {
            $results_total = $results->select(DB::raw('count(distinct `user_id`) as num'))->first()->num;
        }

        $total = $results_total; 

        $cols_title_second = [
            strtoupper($vox->title).', Base: '.$results_resp.' respondents, '.$all_period
        ];

        $rows_breakdown = [
            $cols_title_second,
        ];

        foreach($demographics as $chosen_dem) {

            if($chosen_dem == 'relation' && $q->used_for_stats == 'dependency') {

                $second_chart = [];

                $answers_related_array = $q->related->vox_scale_id && !empty($scales[$q->related->vox_scale_id]) ? explode(',', $scales[$q->related->vox_scale_id]->answers) :  json_decode($q->related->answers, true);

                foreach ($answers_related_array as $key => $value) {
                    $second_chart[$key][] = mb_strpos($value, '!')===0 || mb_strpos($value, '#')===0 ? mb_substr($q->removeAnswerTooltip($value), 1) : $q->removeAnswerTooltip($value);
                }

                if(!empty($q->stats_answer_id)) {
                    $convertedRelation = self::downlaodRelationXlsx($q, $q->stats_answer_id, $scales, $rows_breakdown);
                    $rows_breakdown = $convertedRelation['rows_breakdown'];
                    $rows_breakdown[] = [''];

                    $breakdown_rows_count = $convertedRelation['breakdown_rows_count'];
                } else {
                    for($i = 1; $i <= count($second_chart); $i++) {
                        $convertedRelation = self::downlaodRelationXlsx($q, $i, $scales, $rows_breakdown);
                        $rows_breakdown = $convertedRelation['rows_breakdown'];
                        $rows_breakdown[] = [''];

                        $breakdown_rows_count = $convertedRelation['breakdown_rows_count'];
                    }
                }

            } else if($chosen_dem == 'gender') {

                $main_breakdown_chart = [];
                $male_breakdown_chart = [];
                $female_breakdown_chart = [];

                $main_total_count = 0;
                $male_total_count = 0;
                $female_total_count = 0;

                $unique_total_count = 0;
                $unique_male_total_count = 0;
                $unique_female_total_count = 0;

                // dd($all_results, $breakdown_results);
                // dd($answers_array);
                foreach ($answers_array as $key => $value) {
                    $main_breakdown_chart[$key][] = mb_strpos($value, '!')===0 || ($q->type != 'single_choice' && mb_strpos($value, '#')===0) ? mb_substr($q->removeAnswerTooltip($value), 1) : $q->removeAnswerTooltip($value);
                    $male_breakdown_chart[$key][] = mb_strpos($value, '!')===0 || ($q->type != 'single_choice' && mb_strpos($value, '#')===0) ? mb_substr($q->removeAnswerTooltip($value), 1) : $q->removeAnswerTooltip($value);
                    $female_breakdown_chart[$key][] = mb_strpos($value, '!')===0 || ($q->type != 'single_choice' && mb_strpos($value, '#')===0) ? mb_substr($q->removeAnswerTooltip($value), 1) : $q->removeAnswerTooltip($value);

                    $count_people = 0;
                    $count_people_male = 0;
                    $count_people_female = 0;

                    foreach ($all_results as $k => $v) {

                        if(!empty($v->gender)) {
                            if($q->type == 'scale' ) {
                                if($v->scale == ($key + 1)) {
                                    $count_people++;

                                    if($v->gender == 'm') {
                                        $count_people_male++;
                                    }

                                    if($v->gender == 'f') {
                                        $count_people_female++;
                                    }
                                }
                            } else {

                                if($v->answer == ($key + 1)) {
                                    $count_people++;

                                    if($v->gender == 'm') {
                                        $count_people_male++;
                                    }

                                    if($v->gender == 'f') {
                                        $count_people_female++;
                                    }
                                }
                            }
                        }
                    }
                    
                    $unique_count_people = 0;
                    $unique_count_people_male = 0;
                    $unique_count_people_female = 0;

                    foreach ($breakdown_results as $k => $v) {
                        if(!empty($v->gender)) {
                            if($q->type == 'scale' ) {
                                if($v->scale == ($key + 1)) {
                                    $unique_count_people++;

                                    if($v->gender == 'm') {
                                        $unique_count_people_male++;
                                    }

                                    if($v->gender == 'f') {
                                        $unique_count_people_female++;
                                    }
                                }
                            } else {

                                if($v->answer == ($key + 1)) {
                                    $unique_count_people++;

                                    if($v->gender == 'm') {
                                        $unique_count_people_male++;
                                    }

                                    if($v->gender == 'f') {
                                        $unique_count_people_female++;
                                    }
                                }
                            }
                        }
                    }

                    $unique_total_count = $unique_total_count + $unique_count_people;
                    $unique_male_total_count = $unique_male_total_count + $unique_count_people_male;
                    $unique_female_total_count = $unique_female_total_count + $unique_count_people_female;

                    $main_total_count = $main_total_count + $count_people;
                    $male_total_count = $male_total_count + $count_people_male;
                    $female_total_count = $female_total_count + $count_people_female;

                    $main_breakdown_chart[$key][] = $count_people;
                    $male_breakdown_chart[$key][] = $count_people_male;
                    $female_breakdown_chart[$key][] = $count_people_female;
                }

                // dd($main_breakdown_chart);


                if(!empty($scale_for)) {
                    $list = json_decode($q->answers, true);
                    $title_stats = strip_tags(!empty($q->stats_title_question) ? $q->questionWithoutTooltips() : (!empty($q->stats_title) ? $q->stats_title : $q->questionWithoutTooltips() )).' ['.$list[($scale_for - 1)].']';
                } else {
                    $title_stats = ($q->type == 'multiple_choice' ? '[Multiple choice] ' : '' ).strip_tags(!empty($q->stats_title_question) ? $q->questionWithoutTooltips() : (!empty($q->stats_title) ? $q->stats_title : $q->questionWithoutTooltips() ));
                }

                $cols_q_title_second = [
                    $title_stats,
                ];

                $rows_breakdown[] = $cols_q_title_second;

                $chart_titles = [
                    '',
                    'Total',
                    'Total',
                    'Men',
                    'Men',
                    'Women',
                    'Women',
                ];

                $rows_breakdown[] = $chart_titles;

                foreach ($main_breakdown_chart as $key => $value) {
                    foreach ($value as $k => $v) {
                        if($k == 1 && $v == 0) {
                            $value[$k] = '0';
                        } else {
                            $value[$k] =  $v;
                        }
                    }
                    $main_breakdown_chart[$key] = $value;
                    $main_breakdown_chart[$key][] = $value[1] == 0 ? '0' : ($value[1] / ($q->type == 'scale' ? $main_total_count : $unique_total_count));
                }

                foreach ($female_breakdown_chart as $key => $value) {
                    foreach ($value as $k => $v) {
                        if($k == 1 && $v == 0) {
                            $value[$k] = '0';
                        } else {
                            $value[$k] =  $v;
                        }
                    }
                    $female_breakdown_chart[$key] = $value;
                    $female_breakdown_chart[$key][] = $value[1] == 0 ? '0' : ($value[1] / ($q->type == 'scale' ? $female_total_count : $unique_female_total_count));
                }

                foreach ($male_breakdown_chart as $key => $value) {
                    foreach ($value as $k => $v) {
                        if($k == 1 && $v == 0) {
                            $value[$k] = '0';
                        } else {
                            $value[$k] =  $v;
                        }
                    }
                    $male_breakdown_chart[$key] = $value;
                    $male_breakdown_chart[$key][] = $value[1] == 0 ? '0' : ($value[1] / ($q->type == 'scale' ? $male_total_count : $unique_male_total_count));
                }

                usort($main_breakdown_chart, function($a, $b) {
                    return $a[2] <= $b[2];
                });
                
                $male_breakdown_final = [];
                $female_breakdown_final = [];
                foreach($main_breakdown_chart as $key => $value) {
                    foreach ($male_breakdown_chart as $k => $v) {
                        if($v[0] == $value[0]) {
                            $male_breakdown_final[$key] = [
                                $v[1],
                                $v[2],
                            ];
                        }
                    }

                    foreach ($female_breakdown_chart as $k => $v) {
                        if($v[0] == $value[0]) {
                            $female_breakdown_final[$key] = [
                                $v[1],
                                $v[2],
                            ];
                        }
                    }
                }

                foreach($main_breakdown_chart as $key => $value) {
                    $main_breakdown_chart[$key][] = $male_breakdown_final[$key][0];
                    $main_breakdown_chart[$key][] = $male_breakdown_final[$key][1];
                    $main_breakdown_chart[$key][] = $female_breakdown_final[$key][0];
                    $main_breakdown_chart[$key][] = $female_breakdown_final[$key][1];
                }

                $ordered_diez = [];

                foreach ($main_breakdown_chart as $key => $value) {

                    if(mb_strpos($value[0], '#')===0) {
                        $ordered_diez[] = $value;
                        unset( $main_breakdown_chart[$key] );
                    }
                }

                if(count($ordered_diez)) {

                    if( count($ordered_diez) > 1) {
                        usort($ordered_diez, function($a, $b) {
                            return $a[2] <= $b[2];
                        });

                        foreach ($ordered_diez as $key => $value) {

                            $value[0] = mb_substr($value[0], 1);

                            $main_breakdown_chart[] = $value;
                        }
                    } else {
                        foreach ($ordered_diez as $key => $value) {

                            $ordered_diez[$key][0] = mb_substr($value[0], 1);
                        }
                        $main_breakdown_chart[] = $ordered_diez[0];
                    }

                    $main_breakdown_chart = array_values($main_breakdown_chart);
                }

                $rows_breakdown[] = $main_breakdown_chart;
                $rows_breakdown[] = [
                    '',
                    $main_total_count,
                    '',
                    $male_total_count,
                    '',
                    $female_total_count,
                ];
                $rows_breakdown[] = [''];

            } else if($chosen_dem != 'relation') {

                $main_breakdown_chart = [];
                $dem_breakdown_chart = [];
                $unique_dem_breakdown_chart = [];
                $main_total_count = 0;
                $unique_main_total_count = 0;

                if($chosen_dem == 'age' ) {
                    $config_dem_groups = config('vox.age_groups');
                } else if($chosen_dem == 'country_id') {
                    $config_dem_groups = Country::with('translations')->get()->pluck('name', 'id')->toArray();
                } else {
                    $config_dem_groups = config('vox.details_fields')[$chosen_dem]['values'];
                }
               
                foreach ($answers_array as $key => $value) {
                    $main_breakdown_chart[$key][] = mb_strpos($value, '!')===0 || ($q->type != 'single_choice' && mb_strpos($value, '#')===0) ? mb_substr($q->removeAnswerTooltip($value), 1) : $q->removeAnswerTooltip($value);
                    $dem_breakdown_chart[$key][] = mb_strpos($value, '!')===0 || ($q->type != 'single_choice' && mb_strpos($value, '#')===0) ? mb_substr($q->removeAnswerTooltip($value), 1) : $q->removeAnswerTooltip($value);
                    $unique_dem_breakdown_chart[$key][] = mb_strpos($value, '!')===0 || ($q->type != 'single_choice' && mb_strpos($value, '#')===0) ? mb_substr($q->removeAnswerTooltip($value), 1) : $q->removeAnswerTooltip($value);

                    $count_people = 0;
                    $unique_count_people = 0;

                    $dem_count = [];
                    foreach($config_dem_groups as $k => $v) {
                        $dem_count[$k] = [
                            'count' => 0,
                        ];
                    }

                    $unique_dem_count = [];
                    foreach($config_dem_groups as $k => $v) {
                        $unique_dem_count[$k] = [
                            'count' => 0,
                        ];
                    }

                    foreach ($all_results as $k => $v) {

                        if(!empty($v->$chosen_dem)) {

                            if($q->type == 'scale' ) {
                                if($v->scale == ($key + 1)) {
                                    $count_people++;
                                    $dem_count[$v->$chosen_dem]['count']++;
                                }

                            } else {
                                if($v->answer == ($key + 1)) {
                                    $count_people++;                                                
                                    $dem_count[$v->$chosen_dem]['count']++;
                                }
                            }
                        }
                    }

                    foreach ($breakdown_results as $k => $v) {

                        if(!empty($v->$chosen_dem)) {

                            if($q->type == 'scale' ) {
                                if($v->scale == ($key + 1)) {
                                    $unique_count_people++;
                                    $unique_dem_count[$v->$chosen_dem]['count']++;
                                }

                            } else {
                                if($v->answer == ($key + 1)) {
                                    $unique_count_people++;                                                
                                    $unique_dem_count[$v->$chosen_dem]['count']++;
                                }
                            }
                        }
                    }

                    $unique_main_total_count = $unique_main_total_count + $unique_count_people;
                    $main_total_count = $main_total_count + $count_people;
                    $main_breakdown_chart[$key][] = $count_people;
                    $dem_breakdown_chart[$key][] = $dem_count;
                    $unique_dem_breakdown_chart[$key][] = $unique_dem_count;
                }

                // dd($main_breakdown_chart, $age_breakdown_chart);

                if(!empty($scale_for)) {
                    $list = json_decode($q->answers, true);
                    $title_stats = strip_tags(!empty($q->stats_title_question) ? $q->questionWithoutTooltips() : (!empty($q->stats_title) ? $q->stats_title : $q->questionWithoutTooltips() )).' ['.$list[($scale_for - 1)].']';
                } else {
                    $title_stats = ($q->type == 'multiple_choice' ? '[Multiple choice] ' : '' ).strip_tags(!empty($q->stats_title_question) ? $q->questionWithoutTooltips() : (!empty($q->stats_title) ? $q->stats_title : $q->questionWithoutTooltips() ));
                }

                $cols_q_title_second = [
                    $title_stats,
                ];

                $rows_breakdown[] = $cols_q_title_second;

                $chart_titles = [
                    '',
                    'Total',
                    'Total',
                ];

                foreach($config_dem_groups as $ak => $dem_name) {
                    $chart_titles[] = $dem_name;
                    $chart_titles[] = $dem_name;
                }

                $rows_breakdown[] = $chart_titles;

                foreach ($main_breakdown_chart as $key => $value) {
                    foreach ($value as $k => $v) {
                        if($k == 1 && $v == 0) {
                            $value[$k] = '0';
                        } else {
                            $value[$k] =  $v;
                        }
                    }
                    $main_breakdown_chart[$key] = $value;
                    $main_breakdown_chart[$key][] = $value[1] == 0 ? '0' : ($value[1] / ($q->type == 'scale' ? $main_total_count : $unique_main_total_count));
                }

                $total_count_by_group = [];

                foreach($config_dem_groups as $k => $v) {
                    $total_count_by_group[$k] = 0;
                }

                foreach ($dem_breakdown_chart as $key => $value) {
                    foreach($value[1] as $k => $v) {
                        $total_count_by_group[$k]+=$v['count'];
                    }
                }

                $unique_total_count_by_group = [];

                foreach($config_dem_groups as $k => $v) {
                    $unique_total_count_by_group[$k] = 0;
                }

                foreach ($unique_dem_breakdown_chart as $key => $value) {
                    foreach($value[1] as $k => $v) {
                        $unique_total_count_by_group[$k]+=$v['count'];
                    }
                }

                foreach ($dem_breakdown_chart as $key => $value) {
                    foreach($value[1] as $k => $v) {
                        $dem_breakdown_chart[$key][1][$k] = [
                            $v['count'],
                            $v['count'] == 0 ? '0' : ($v['count'] / $total_count_by_group[$k]) //tuk trqbwa da e unique ?
                        ];
                    }
                }

                usort($main_breakdown_chart, function($a, $b) {
                    return $a[2] <= $b[2];
                });
                
                $dem_breakdown_final = [];
                foreach($main_breakdown_chart as $key => $value) {
                    foreach ($dem_breakdown_chart as $k => $v) {

                        if($v[0] == $value[0]) {
                            $dem_breakdown_final[$key] = $v;
                        }
                    }
                }

                foreach ($dem_breakdown_final as $key => $value) {
                    foreach($value[1] as $k => $v) {
                        // dd($k, $v);
                        $main_breakdown_chart[$key][] = $v[0];
                        $main_breakdown_chart[$key][] = $v[1];
                    }
                }

                $ordered_diez = [];

                foreach ($main_breakdown_chart as $key => $value) {

                    if(mb_strpos($value[0], '#')===0) {
                        $ordered_diez[] = $value;
                        unset( $main_breakdown_chart[$key] );
                    }
                }

                if(count($ordered_diez)) {

                    if( count($ordered_diez) > 1) {
                        usort($ordered_diez, function($a, $b) {
                            return $a[2] <= $b[2];
                        });

                        foreach ($ordered_diez as $key => $value) {

                            $value[0] = mb_substr($value[0], 1);

                            $main_breakdown_chart[] = $value;
                        }
                    } else {
                        foreach ($ordered_diez as $key => $value) {

                            $ordered_diez[$key][0] = mb_substr($value[0], 1);
                        }
                        $main_breakdown_chart[] = $ordered_diez[0];
                    }

                    $main_breakdown_chart = array_values($main_breakdown_chart);
                }

                $rows_breakdown[] = $main_breakdown_chart;

                $final_count_group = [
                    '',
                    $main_total_count,
                ];

                foreach($total_count_by_group as $k => $v) {
                    $final_count_group[] = '';
                    $final_count_group[] = $v;
                } 

                $rows_breakdown[] = $final_count_group;
                $rows_breakdown[] = [''];
            }                        

        }

        $flist['Breakdown'] = $rows_breakdown;

        return [
            'flist' => $flist,
            'breakdown_rows_count' => $breakdown_rows_count, 
        ];
    }

    public static function downlaodRelationXlsx($q, $answer, $scales, $rows_breakdown) {

        $list = $q->related->vox_scale_id && !empty($scales[$q->related->vox_scale_id]) ? explode(',', $scales[$q->related->vox_scale_id]->answers) :  json_decode($q->related->answers, true);

        $rows_breakdown[] = [$q->related->questionWithoutTooltips().' ['.$q->removeAnswerTooltip($list[$answer - 1]).']'];

        $rows_breakdown[] = ['in relation to:'];

        $cols_q_title_second = [
            ($q->type == 'multiple_choice' ? '[Multiple choice] ' : '' ).$q->questionWithoutTooltips()
        ];

        $rows_breakdown[] = $cols_q_title_second;

        $m_original_chart = [];
        $answers_array = $q->vox_scale_id && !empty($scales[$q->vox_scale_id]) ? explode(',', $scales[$q->vox_scale_id]->answers) :  json_decode($q->answers, true);

        $breakdown_rows_count = count($answers_array);
        $total_count = 0;

        foreach ($answers_array as $key => $value) {
            $m_original_chart[$key][] = mb_strpos($value, '!')===0 || mb_strpos($value, '#')===0 ? mb_substr($q->removeAnswerTooltip($value), 1) : $q->removeAnswerTooltip($value);

            $answer_resp = VoxAnswersDependency::where('question_id', $q->id)->where('question_dependency_id', $q->related->id)->where('answer_id', $answer)->where('answer', $key+1)->first();

            if($answer_resp) {
                $m_original_chart[$key][] = $answer_resp->cnt; 
                $total_count+=$answer_resp->cnt;
            } else {

                $cur_answer = VoxAnswer::whereNull('is_admin')
                ->where('question_id', $q->id)
                ->where('is_completed', 1)
                ->where('is_skipped', 0)
                ->where('answer', $key+1)
                ->has('user');        

                $quest = $q->related->id;
                $aaa = $answer;
                $cur_answer = $cur_answer->whereIn('user_id', function($query) use ($quest, $aaa) {
                    $query->select('user_id')
                    ->from('vox_answers')
                    ->where('question_id', $quest)
                    ->where('answer', $aaa);
                } )->groupBy('answer')->selectRaw('answer, COUNT(*) as cnt')->first();

                // dd($cur_answers);
                $m_original_chart[$key][] = $cur_answer ? $cur_answer->cnt : 0; 
                $total_count+=$cur_answer ? $cur_answer->cnt : 0; 
            }
        }
        // dd($m_original_chart, $total_count);

        foreach ($m_original_chart as $key => $value) {
            foreach ($value as $k => $v) {
                if($k == 1 && $v == 0) {
                    $value[$k] = '0';
                } else {
                    $value[$k] =  $v;
                }
            }
            $m_original_chart[$key] = $value;

            $m_original_chart[$key][] = $value[1] == 0 ? '0' : ($value[1] / $total_count);
        }

        usort($m_original_chart, function($a, $b) {
            return $a[2] <= $b[2];
        });

        $ordered_diez = [];

        foreach ($m_original_chart as $key => $value) {

            if(mb_strpos($value[0], '#')===0) {
                $ordered_diez[] = $value;
                unset( $m_original_chart[$key] );
            }
        }

        if(count($ordered_diez)) {

            if( count($ordered_diez) > 1) {
                usort($ordered_diez, function($a, $b) {
                    return $a[2] <= $b[2];
                });

                foreach ($ordered_diez as $key => $value) {

                    $value[0] = mb_substr($value[0], 1);

                    $m_original_chart[] = $value;
                }
            } else {
                foreach ($ordered_diez as $key => $value) {

                    $ordered_diez[$key][0] = mb_substr($value[0], 1);
                }
                $m_original_chart[] = $ordered_diez[0];
            }

            $m_original_chart = array_values($m_original_chart);
        }

        $rows_breakdown[] = $m_original_chart;

        return [
            'rows_breakdown' => $rows_breakdown,
            'breakdown_rows_count' => $breakdown_rows_count,
        ];
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
                    'data' => User::encrypt(json_encode(array('type' => 'new-survey')))
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