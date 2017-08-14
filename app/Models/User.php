<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

use Illuminate\Support\Str;
use Image;
use App\Models\Email;


class User extends Model implements AuthenticatableContract, CanResetPasswordContract
{
    use SoftDeletes, Authenticatable, CanResetPassword;

    protected $fillable = [
    	'email', 
    	'password', 
        'is_dentist',
        'is_partner',
        'title',
        'name',
        'zip',
        'address',
        'phone',
        'website',
        'city_id',
        'country_id',
        'avg_rating',
        'ratings',
        'invited_by',
        'hasimage',
        'is_verified',
        'verified_on',
        'verification_code',
        'phone_verified',
        'phone_verified_on',
    ];
    protected $dates = [
        'created_at',
        'updated_at',
    	'deleted_at',
        'verified_on',
        'phone_verified_on',
    ];

    public function city() {
        return $this->hasOne('App\Models\City', 'id', 'city_id');        
    }
    public function country() {
        return $this->hasOne('App\Models\Country', 'id', 'country_id');        
    }
    public function categories() {
        return $this->hasMany('App\Models\UserCategory', 'user_id', 'id');        
    }
    public function invitor() {
        return $this->hasOne('App\Models\User', 'id', 'invited_by');        
    }
    public function reviews_out() {
        return $this->hasMany('App\Models\Review', 'user_id', 'id')->orderBy('id', "DESC");
    }
    public function reviews_in() {
        return $this->hasMany('App\Models\Review', 'dentist_id', 'id')->orderBy('verified', "DESC")->orderBy('upvotes', "DESC");
    }
    public function upvotes() {
        return $this->hasMany('App\Models\ReviewUpvote', 'user_id', 'id');
    }
    public function photos() {
        return $this->hasMany('App\Models\UserPhoto', 'user_id', 'id');
    }

    public function getName() {
        return $this->title.' '.$this->name;
    }

    public function getMaskedPhone() {
        return '0'.substr($this->phone, 0, 3).' **** '.substr($this->phone, mb_strlen($this->phone)-2, 2);
    }
    public function getMaskedEmail() {
        $mail_arr = explode('@', $this->email);
        return substr($mail_arr[0], 0, 3).'****@'.$mail_arr[1];
    }

    public function hasReviewTo($dentist_id) {
        return Review::where([
            ['user_id', $this->id],
            ['dentist_id', $dentist_id],
        ])->first();
    }

    public function usefulVotesForDenist($dentist_id) {
        $myid = $this->id;
        return Review::where([
            ['dentist_id', $dentist_id],
        ])->whereHas('upvotes', function ($query) use ($myid) {
            $query->where('user_id', $myid);
        })->get()->pluck('id')->toArray();
    }
    
    public function get_invite_token() {
        //dd($this->email.$this->id);
        $token = md5($this->id.date('WY'));
        $token = preg_replace("/[^a-zA-Z0-9]/", "", $token);
        return $token;
    }
    public function get_token() {
        //dd($this->email.$this->id);
        $token = md5($this->email.$this->id.date('WY'));
        $token = preg_replace("/[^a-zA-Z0-9]/", "", $token);
        return $token;
    }

    public function sendTemplate($template_id, $params=null) {
        $item = new Email;
        $item->user_id = $this->id;
        $item->template_id = $template_id;
        $item->meta = $params;
        $item->save();
        $item->send();
    }

    public function setNameAttribute($value) {
        $this->attributes['name'] = $value;
        $this->attributes['slug'] = Str::slug($value);
    }

    public function getLink() {
        return getLangUrl('dentist/'.$this->slug);
    }

    public function parseCategories($categories) {
        return array_intersect_key( $categories, array_flip( array_intersect_key(config('categories'), array_flip( $this->categories->pluck('category_id')->toArray() ) ) ) );
    }

    public function getImageUrl($thumb = false) {
        return $this->hasimage ? url('/storage/avatars/'.($this->id%100).'/'.$this->id.($thumb ? '-thumb' : '').'.jpg') : url('img/no-photo.jpg');
    }
    public function getImagePath($thumb = false) {
        $folder = storage_path().'/app/public/avatars/'.($this->id%100);
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
        $img->fit( 400, 400 );
        $img->save($to_thumb);
        $this->hasimage = true;
        $this->save();
    }

    public function recalculateRating() {
        $rating = 0;
        foreach ($this->reviews_in as $review) {
            $rating += $review->rating;
        }

        $this->avg_rating = $this->reviews_in->count() ? $rating / $this->reviews_in->count() : 0;
        $this->ratings = $this->reviews_in->count();
        $this->save();
    }

    public function sendSMS($sms_text) {
        $formatted_phone = $this->country->phone_code.$this->phone;
        file_get_contents('https://bulksrv.allterco.net/sendsms/sms.php?nmb_from=1909&user=SWISSDENTAPRIME&pass=m9rr95er9em&nmb_to='.$formatted_phone.'&text='.urlencode($sms_text).'&dlrr=1');
    }

}