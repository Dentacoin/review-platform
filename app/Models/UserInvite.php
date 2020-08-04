<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Carbon\Carbon;

class UserInvite extends Model {

    protected $fillable = [
        'user_id',
        'invited_email',
        'invited_name',
        'invited_id',
        'has_image',
        'for_team',
        'rewarded',
        'join_clinic',
        'review',
        'completed',
        'notified1',
        'notified2',
        'notified3',
        'unsubscribed',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    public function user() {
        return $this->hasOne('App\Models\User', 'id', 'user_id')->withTrashed();
    }

    public function invited() {
        return $this->hasOne('App\Models\User', 'id', 'invited_id')->withTrashed();
    }

    public function getImageUrl($thumb = false) {
        return $this->has_image ? url('/storage/invites/'.($this->id%100).'/'.$this->id.($thumb ? '-thumb' : '').'.jpg').'?rev='.$this->updated_at->timestamp : url('new-vox-img/no-avatar-0.png');
    }
    public function getImagePath($thumb = false) {
        $folder = storage_path().'/app/public/invites/'.($this->id%100);
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
        $img->heighten(400, function ($constraint) {
            $constraint->upsize();
        });
        $img->save($to_thumb);

        $this->has_image = true;
        $this->save();
    }

    public function hasReview($id) {
        $review = Review::where('user_id', $this->invited_id)->where(function($query) use ($id) {
            $query->where( 'dentist_id', $id)->orWhere('clinic_id', $id);
        })->orderby('id', 'desc')->first();

        if(!empty($review)) {
            return $review;
        } else {
            return false;
        }
    }

    public function dentistInviteAgain($id) {

        if(!empty($this->hasReview($id))) {

            $days = $this->hasReview($id)->created_at->diffInDays( Carbon::now() );
            $to_month = Carbon::now()->modify('-1 months');
            $dentist_invites = self::where('user_id', $id)->where('invited_id', $this->invited_id)->where('created_at', '<=', $to_month)->first();

            if($days>30 && !empty($dentist_invites)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}


?>