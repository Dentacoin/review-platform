<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\VoxAnswersDependency;
use App\Models\VoxAnswer;

use WebPConvert\WebPConvert;

use DB;

class VoxQuestion extends Model {
    
    use \Dimsav\Translatable\Translatable;
    
    public $translatedAttributes = [
        'question',
        'answers',
        'stats_title',
        'stats_subtitle',
        'rank_explanation',
    ];

    protected $fillable = [
        'vox_id',
        'type',
        'question_trigger',
        'trigger_type',
        'question',
        'answers',
        'answers_images_filename',
        'vox_scale_id',
        'is_control',
        'prev_q_id_answers',
        'order',
        'number_limit',
        'used_for_stats',
        'dependency_caching',
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
        'dont_randomize_answers',
        'has_image',
        'image_in_tooltip',
        'image_in_question',
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

        $new_title = str_replace("{/}","</a>", str_replace("[/]","</span>", str_replace('"', '&quot;', $this->question)));
        $new_title = preg_replace('/\{([^\]]*)\}/', '<a href="${1}" target="_blank">', $new_title);

        return preg_replace('/\[([^\]]*)\]/', '<span class="tooltip-text" text="${1}">', $new_title);
    }

    public function questionWithoutTooltips() {

        $new_title = str_replace("{/}","</a>", str_replace("[/]","",$this->question));
        $new_title = preg_replace('/\{([^\]]*)\}/', '<a href="${1}" target="_blank">', $new_title);
        
        return preg_replace('/\[([^\]]*)\]/', '', $new_title);
    }

    public static function handleAnswerTooltip($answer) {

        $new_answer = str_replace("{/}","</a>", str_replace("[/]","</span>", str_replace('"', '&quot;', $answer)));
        $new_answer = preg_replace('/\{([^\]]*)\}/', '<a href="${1}" target="_blank">', $new_answer);
        
        return preg_replace('/\[([^\]]*)\]/', '<span class="tooltip-text" text="${1}">', $new_answer);
    }

    public static function removeAnswerTooltip($answer) {

        $new_answer = str_replace("{/}","</a>", str_replace("[/]","", $answer));
        $new_answer = preg_replace('/\{([^\]]*)\}/', '<a href="${1}" target="_blank">', $new_answer);
        
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

    public function getStatsFieldsAttribute($value) {
        return $value ? explode(',', $value) : [];
    }

    public function setStatsFieldsAttribute($value) {
        $this->attributes['stats_fields'] = implode(',', $value);
    }

    public function allAnswersHaveImages() {
        $arr = 0;
        if(!empty($this->answers_images_filename)) {
            foreach (json_decode($this->answers_images_filename, true) as $key => $value) {
                if(!empty($value)) {
                    $arr++;
                }
            }
        }

        if(!$this->vox_scale_id && count(json_decode($this->answers, true)) == $arr) {
            return true;
        } else {
            return false;
        }
    }

    public function getAnswerImageUrl($thumb = false, $answer_num) {
        
        if($this->answers_images_filename && !empty(json_decode($this->answers_images_filename, true)[$answer_num])) {
            return url('/storage/vox-question-answers/'.($this->id%100).'/'.$this->id.'-'.json_decode($this->answers_images_filename, true)[$answer_num].($thumb ? '-thumb' : '').'.jpg');
        }
        return false;
    }

    public function getAnswerImagePath($thumb = false, $answer_num) {
        $folder = storage_path().'/app/public/vox-question-answers/'.($this->id%100);
        if(!is_dir($folder)) {
            mkdir($folder);
        }
        return $folder.'/'.$this->id.'-'.$answer_num.($thumb ? '-thumb' : '').'.jpg';
    }

    public function addAnswerImage($img, $answer_num) {

        $to = $this->getAnswerImagePath(false, $answer_num);
        $to_thumb = $this->getAnswerImagePath(true, $answer_num);

        $img->resize(1920, null, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });
        $img->save($to);
        $img->fit( 130, 130 );
        $img->save($to_thumb);

        $destination = self::getAnswerImagePath(false, $answer_num).'.webp';
        WebPConvert::convert(self::getAnswerImagePath(false, $answer_num), $destination, []);

        $destination_thumb = self::getAnswerImagePath(true, $answer_num).'.webp';
        WebPConvert::convert(self::getAnswerImagePath(true, $answer_num), $destination_thumb, []);
    }


    public function getImageUrl($thumb = false) {
        return $this->has_image ? url('/storage/vox-questions/'.($this->id%100).'/'.$this->id.($thumb ? '-thumb' : '').'.jpg') : false;
    }
    public function getImagePath($thumb = false) {
        $folder = storage_path().'/app/public/vox-questions/'.($this->id%100);
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
        $img->fit( 90, 90 );
        $img->save($to_thumb);
        $this->has_image = true;
        $this->save();

        $destination = self::getImagePath().'.webp';
        WebPConvert::convert(self::getImagePath(), $destination, []);

        $destination_thumb = self::getImagePath(true).'.webp';
        WebPConvert::convert(self::getImagePath(true), $destination_thumb, []);
    }

    public function imageOnlyInQuestion() {
        if(!empty($this->image_in_question) && !empty($this->getImageUrl(false)) && empty($this->image_in_tooltip)) {
            return true;
        } else {
            return false;
        }
    }

    public function imageOnlyInTooltip() {
        if(!empty($this->image_in_tooltip) && !empty($this->getImageUrl(false)) && empty($this->image_in_question)) {
            return true;
        } else {
            return false;
        }
    }

    public function imageInTooltipAndQuestion() {
        if(!empty($this->image_in_tooltip) && !empty($this->getImageUrl(false)) && !empty($this->image_in_question)) {
            return true;
        } else {
            return false;
        }
    }

    public function generateDependencyCaching() {

        $existing = VoxAnswersDependency::where('question_id', $this->id)->get();

        if($existing->isNotEmpty()) {
            foreach ($existing as $exist) {
                $exist->delete();
            }
        }
        
        if(!empty($this->stats_answer_id)) {

            $results = VoxAnswer::prepareQuery($this->id, null,[
                'dependency_answer' => $this->stats_answer_id,
                'dependency_question' => $this->stats_relation_id,
            ]);

            $results = $results->groupBy('answer')->selectRaw('answer, COUNT(*) as cnt');
            $results = $results->get();

            foreach ($results as $result) {

                $vda = new VoxAnswersDependency;
                $vda->question_dependency_id = $this->stats_relation_id;
                $vda->question_id = $this->id;
                $vda->answer_id = $this->stats_answer_id;
                $vda->answer = $result->answer;
                $vda->cnt = $result->cnt;
                $vda->save();
            }
        } else {
            //да минат през всички отговори
            foreach (json_decode($this->answers, true) as $key => $single_answ) {
                $answer_number = $key + 1;
                
                $results = VoxAnswer::prepareQuery($this->id, null,[
                    'dependency_answer' => $answer_number,
                    'dependency_question' => $this->stats_relation_id,
                ]);

                $results = $results->groupBy('answer')->selectRaw('answer, COUNT(*) as cnt');
                $results = $results->get();

                $existing = VoxAnswersDependency::where('question_id', $this->id)->first();

                foreach ($results as $result) {

                    $vda = new VoxAnswersDependency;
                    $vda->question_dependency_id = $this->stats_relation_id;
                    $vda->question_id = $this->id;
                    $vda->answer_id = $answer_number;
                    $vda->answer = $result->answer;
                    $vda->cnt = $result->cnt;
                    $vda->save();
                }
            }
        }

        $this->dependency_caching = true;
        $this->save();
    }
}

class VoxQuestionTranslation extends Model {

    public $timestamps = false;
    protected $fillable = [
        'question',
        'answers',
        'stats_title',
        'stats_subtitle',
        'rank_explanation',
    ];
}

?>