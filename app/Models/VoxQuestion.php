<?php

namespace App\Models;

use DB;

use Illuminate\Database\Eloquent\Model;

class VoxQuestion extends Model {
    
    use \Dimsav\Translatable\Translatable;
    
    public $translatedAttributes = [
        'question',
        'answers',
        'stats_title',
        'stats_subtitle',
    ];

    protected $fillable = [
        'vox_id',
        'type',
        'question_trigger',
        'trigger_type',
        'invert_trigger_logic',
        'question',
        'answers',
        'vox_scale_id',
        'is_control',
        'order',
        'used_for_stats',
        'stats_title',
        'stats_title_question',
        'stats_subtitle',
        'stats_relation_id',
        'stats_answer_id',
        'stats_featured',
        'stats_fields',
        'stats_scale_answers',
        'stats_top_answers',
        'cross_check',
        'dont_randomize_answers'
    ];

    public $timestamps = false;
    
    public function vox() {
        return $this->hasOne('App\Models\Vox', 'id', 'vox_id');
    }

    public function related() {
        return $this->hasOne('App\Models\VoxQuestion', 'id', 'stats_relation_id');
    }

    public function respondents() {
        return $this->hasMany('App\Models\VoxAnswer', 'question_id', 'id')->whereNull('is_admin')->where('is_completed', 1)->where('is_skipped', 0)->where('answer', '!=', 0)->has('user');
    }
    public function respondent_count() {
        return $this->hasMany('App\Models\VoxAnswer', 'question_id', 'id')->whereNull('is_admin')->where('is_completed', 1)->where('is_skipped', 0)->where('answer', '!=', 0)->has('user')->select(DB::raw('count( distinct `user_id`) as num'))->first()->num;
    }

    public function questionWithTooltips() {
        $new_title = str_replace("[/]","</span>",$this->question);
        
        return preg_replace('/\[([^\]]*)\]/', '<span class="tooltip-text" text="${1}">', $new_title);
    }

    public function questionWithoutTooltips() {
        $new_title = str_replace("[/]","",$this->question);
        
        return preg_replace('/\[([^\]]*)\]/', '', $new_title);
    }

    public static function handleAnswerTooltip($answer) {
        $new_answer = str_replace("[/]","</span>", str_replace('"', '&quot;', $answer));
        
        return preg_replace('/\[([^\]]*)\]/', '<span class="tooltip-text" text="${1}">', $new_answer);
    }

    public static function removeAnswerTooltip($answer) {
        $new_answer = str_replace("[/]","", str_replace('"', '&quot;', $answer));
        
        return preg_replace('/\[([^\]]*)\]/', '', $new_answer);
    }

    public static function hasAnswerTooltip($answer, $question) {
        if (strpos($answer, '[/]') !== false) {

            $string = $question->handleAnswerTooltip($answer);
            $arr = explode('text="',$string);
            $arr = explode('">',$arr[1]);

            return $arr[0];
        } else {
            return false;
        }
    }

    public function getStatsFieldsAttribute($value)
    {
        return $value ? explode(',', $value) : [];
    }

    public function setStatsFieldsAttribute($value)
    {
        $this->attributes['stats_fields'] = implode(',', $value);
    }


}

class VoxQuestionTranslation extends Model {

    public $timestamps = false;
    protected $fillable = [
        'question',
        'answers',
        'stats_title',
        'stats_subtitle',
    ];

}



?>