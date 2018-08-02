<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App;
use App\Models\VoxToCategory;
use App\Models\Reward;

class Vox extends Model {
    
    use \Dimsav\Translatable\Translatable;
    use SoftDeletes;
    
    public $translatedAttributes = [
        'title',
        'description',
    ];

    protected $fillable = [
        'title',
        'description',
        'seo_title',
        'seo_description',
        'slug',
        'reward',
        'reward_usd',
        'duration',
        'type',
        'complex',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    
    public function questions() {
        return $this->hasMany('App\Models\VoxQuestion', 'vox_id', 'id')->orderBy('order', 'ASC');
    }
    
    public function questionsReal() {
        return $this->hasMany('App\Models\VoxQuestion', 'vox_id', 'id')->whereNull('is_control')->orderBy('order', 'ASC');
    }

    public function rewards() {
        return $this->hasMany('App\Models\VoxReward', 'vox_id', 'id')->orderBy('id', 'DESC');
    }

    public function formatDuration() {
        return '~'.ceil( $this->questions()->count()/6 ).' minutes';
    }

    public function getRewardPerQuestion() {
        return Reward::where('reward_type', 'vox_question')->first();
    }

    public function getRewardTotal($inusd = false) {
        if ($this->type == 'user_details') {
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

    public function getLink() {
        return $this->type=='hidden' || $this->type=='normal' ? getLangUrl('paid-surveys/'.$this->translate(App::getLocale(), true)->slug ) : getLangUrl( $this->translate(App::getLocale(), true)->slug );        
    }

    public function checkComplex() {

        foreach ($this->questions as $q) {
            if($q->question_trigger) {
                $this->complex = 1;
                $this->save();
            }
        }
    }
    
}

class VoxTranslation extends Model {

    public $timestamps = false;
    protected $fillable = [
        'title',
        'description',
        'seo_title',
        'seo_description',
        'slug',
    ];

}



?>