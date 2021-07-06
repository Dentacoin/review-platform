<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\VoxAnswersDependency;
use App\Models\VoxAnswer;
use App\Models\VoxScale;

use WebPConvert\WebPConvert;

use Log;
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

    protected $casts = [
        'excluded_answers' => 'array',
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
    
    public function scale() {
        return $this->hasOne('App\Models\VoxScale', 'id', 'vox_scale_id');
    }
    
    public function respondent_count() {
        return $this->hasMany('App\Models\VoxAnswer', 'question_id', 'id')->whereNull('is_admin')->where('is_completed', 1)->where('is_skipped', 0)->where('answer', '!=', 0)->has('user')->select(DB::raw('count( distinct `user_id`) as num'))->first()->num;
    }

    public function questionWithTooltips() {

        $new_title = str_replace("{/}","</a>", str_replace("[/]","</span>", str_replace('"', '&quot;', $this->question)));
        $new_title = preg_replace('/\{([^\]]*)\}/', '<a href="${1}" target="_blank">', $new_title);

        return preg_replace('/\[([^\]]*)\]/', '<span class="tooltip-text" text="${1}">', $new_title);
    }

    public function questionWithTooltipsApp() {

        $new_title = str_replace("{/}","</a>", str_replace("[/]","</span>", str_replace('"', '&quot;', $this->question)));
        $new_title = preg_replace('/\{([^\]]*)\}/', '<a href="${1}" target="_blank">', $new_title);

        return preg_replace('/\[([^\]]*)\]/', '<span style="font-weight: bold; color: #41afff;" class="tooltip-text" text="${1}">', $new_title);
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

        if(!$this->vox_scale_id && $this->answers && count(json_decode($this->answers, true)) == $arr) {
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

                if(!empty($existing)) {
                    $existing->delete();
                }

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

    public function convertForResponse() {

        $slist = VoxScale::get();
        $scales = [];
        foreach ($slist as $sitem) {
            $scales[$sitem->id] = $sitem;
        }

        $arr = $this->toArray();
        $arr['thumb'] = $this->getImageUrl(true);
        $arr['avatar'] = $this->getImageUrl();
        $arr['title'] = $this->questionWithTooltipsApp();
        $arr['image_only_in_question'] = $this->imageOnlyInQuestion();
        $arr['image_only_in_tooltip'] = $this->imageOnlyInTooltip();
        $arr['image_in_tooltip_and_question'] = $this->imageInTooltipAndQuestion();
        $arr['has_image_question'] = !empty($this->imageOnlyInQuestion()) || !empty($this->imageInTooltipAndQuestion());
        $arr['has_image_tooltip'] = !empty($this->imageOnlyInTooltip()) || !empty($this->imageInTooltipAndQuestion());
        $arr['all_answers_have_images'] = $this->allAnswersHaveImages();

        $arr['answers_cov'] = $this->vox_scale_id && $this->type != 'scale' && !empty($scales[$this->vox_scale_id]) ? explode(',', $scales[$this->vox_scale_id]->answers) :  json_decode($this->answers, true);

        if($arr['answers_cov']) {

            foreach ($arr['answers_cov'] as $num => $ans) {
                $arr['all_answers'][] = [
                    'id' => $num+1,
                    'answer' => $ans
                ];
            }

            if($this->type == 'multiple_choice') {
                if(empty($this->dont_randomize_answers)) {

                    $arr['all_answers'] = $this->shuffleAnswers($arr['all_answers']);

                }

            } else if($this->type == 'single_choice') {

                if($this->is_control != 1 && empty($this->dont_randomize_answers) && empty($this->vox_scale_id) && $this->vox_id != 11 && !$this->cross_check ) {
                    $arr['all_answers'] = $this->shuffleAnswers($arr['all_answers']);
                }

            } else if($this->type == 'rank') {

                $rank_arr = [];
                foreach ($arr['all_answers'] as $key => $value) {
                    $rank_arr[] = [
                        $key+1,
                        $value['answer'],
                    ];
                }

                $arr['all_answers'] = $rank_arr;
            }

            if($this->type != 'multiple_choice' && $this->type != 'rank') {
                foreach ($arr['all_answers'] as $key => $value) {
                    if(mb_strpos($value['answer'],'!') === 0 || mb_strpos($value['answer'], '#') === 0) {
                        $arr['all_answers'][$key] = [
                            'id' => $value['id'],
                            'answer' => mb_substr($value['answer'], 1)
                        ];
                    }
                }
            }
        } else {
            $arr['all_answers'] = [];
        }

        $arr['answer_images'] = [];
        $arr['answer_images_thumb'] = [];

        if (!empty($arr['all_answers'])) {
            foreach ($arr['all_answers'] as $key => $value) {
                $arr['answer_images'][] = $this->getAnswerImageUrl(false, $key);
                $arr['answer_images_thumb'][] = $this->getAnswerImageUrl(true, $key);
            }
        }

        return $arr;
    }


    public function shuffleAnswers($answers) {
        $append_at_the_end = [];
        $shuffled = [];

        foreach ($answers as $value) {
            if(mb_strpos($value['answer'],'!') === 0 || mb_strpos($value['answer'], '#') === 0) {
                $append_at_the_end[] = $value;
            } else {
                $shuffled[] = $value;
            }
        }

        shuffle($shuffled);

        foreach ($append_at_the_end as $v) {
            $shuffled[] = $v;
        }

        return $shuffled;
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