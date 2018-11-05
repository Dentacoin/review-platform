<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App;
use App\Models\VoxToCategory;
use App\Models\Reward;
use App\Models\VoxReward;

class Vox extends Model {
    
    use \Dimsav\Translatable\Translatable;
    use SoftDeletes;
    
    public $translatedAttributes = [
        'title',
        'description',
        'stats_description',
        'seo_title',
        'seo_description',
        'social_title',
        'social_description',
        'seo_stats_title',
        'seo_stats_description',
        'social_stats_title',
        'social_stats_description',
    ];

    protected $fillable = [
        'title',
        'description',
        'stats_description',
        'seo_title',
        'seo_description',
        'social_title',
        'social_description',
        'seo_stats_title',
        'seo_stats_description',
        'social_stats_title',
        'social_stats_description',
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
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    
    public function questions() {
        return $this->hasMany('App\Models\VoxQuestion', 'vox_id', 'id')->orderBy('order', 'ASC');
    }

    public function stats_questions() {
        return $this->hasMany('App\Models\VoxQuestion', 'vox_id', 'id')->where('used_for_stats', '!=', '')->orderBy('order', 'ASC');
    }
    
    public function stats_main_question() {
        return $this->hasOne('App\Models\VoxQuestion', 'vox_id', 'id')->where('stats_featured', '1');
    }
    
    public function respondentsCount() {
        return VoxReward::where('vox_id', $this->id)->count();
    }

    public function questionsReal() {
        return $this->hasMany('App\Models\VoxQuestion', 'vox_id', 'id')->whereNull('is_control')->orderBy('order', 'ASC');
    }

    public function rewards() {
        return $this->hasMany('App\Models\VoxReward', 'vox_id', 'id')->orderBy('id', 'DESC');
    }

    public function formatDuration() {
        return ceil( $this->questions()->count()/6 ).' min';
    }

    public function getRewardPerQuestion() {
        return Reward::where('reward_type', 'vox_question')->first();
    }

    public function getRewardTotal($inusd = false) {
        if ($this->type == 'home') {
            return 100;
        } else if ($this->type == 'user_details') {
            return 0;
        } else {
            return ( $inusd ? $this->getRewardPerQuestion()->amount : $this->getRewardPerQuestion()->dcn) * $this->questions->count();
        }        
    }

    public function getRewardForUser($user_id) {
        if ($this->type == 'user_details') {
            return 0;
        } else {
            $how_many_questions = VoxAnswer::where('vox_id', $this->id)->where('user_id', $user_id)->where('answer', '!=' , 0)->groupBy('question_id')->get()->count();
            $reward_per_question = Reward::where('reward_type', 'vox_question')->first()->dcn;

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