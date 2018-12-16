<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\Dcn;
use App\Models\User;
use App\Models\Secret;
use App\Models\Reward;
use App\Models\TrpReward;
use App\Models\ReviewAnswer;

class Review extends Model {
    
    use SoftDeletes;
    
    
    protected $fillable = [
        'user_id',
        'dentist_id',
        'clinic_id',
        'rating',
        'youtube_id',
        'youtube_approved',
        'title',
        'answer',
        'reply',
        'verified',
        'upvotes',
        'secret_id',
        'status',
        'reward_address',
        'reward_tx',
        'ipfs',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function secret() {
        return $this->hasOne('App\Models\Secret', 'id', 'secret_id')->withTrashed();
    }

    public function user() {
        return $this->hasOne('App\Models\User', 'id', 'user_id')->withTrashed();
    }
    
    public function dentist() {
        return $this->hasOne('App\Models\User', 'id', 'dentist_id')->withTrashed();
    }
    
    public function clinic() {
        return $this->hasOne('App\Models\User', 'id', 'clinic_id')->withTrashed();
    }

    public function answers() {
        return $this->hasMany('App\Models\ReviewAnswer', 'review_id', 'id')->with('question');
    }

    public function upvotes() {
        return $this->hasMany('App\Models\ReviewUpvote', 'review_id', 'id');
    }
    
    public function downvotes() {
        return $this->hasMany('App\Models\ReviewDownvote', 'review_id', 'id');
    }

    public function afterSubmitActions() {

        if( $this->dentist ) {
            $this->dentist->sendTemplate( $this->verified ? 21 : 6, [
                'review_id' => $this->id,
            ]);            
        }
        if( $this->clinic ) {
            $this->clinic->sendTemplate( $this->verified ? 21 : 6, [
                'review_id' => $this->id,
            ]);            
        }

        if($this->verified) {
            $reward = new TrpReward();
            $reward->user_id = $this->dentist_id;
            $reward->reward = Reward::getReward('reward_dentist');
            $reward->type = 'dentist-review';
            $reward->reference_id = $this->id;
            $reward->save();
        }


        if($this->user->invited_by && !$this->user->invitor->is_dentist) {
            $inv = UserInvite::where('user_id', $this->user->invited_by)->where('invited_id', $this->user->id)->first();
            if(!empty($inv) && !$inv->rewarded) {

                $reward = new TrpReward();
                $reward->user_id = $this->user->invitor;
                $reward->reward = Reward::getReward('reward_invite');
                $reward->type = 'invitation';
                $reward->reference_id = $inv->id;
                $reward->save();
                
                $this->user->invitor->sendTemplate( 22, [
                    'who_joined_name' => $this->user->getName()
                ] );
            }
        }

        if( $this->dentist ) {
            $this->dentist->recalculateRating();
        }
        if( $this->clinic ) {
            $this->clinic->recalculateRating();
        }
    }
}

?>