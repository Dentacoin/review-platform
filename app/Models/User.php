<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Support\Str;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

use App\Models\Email;
use App\Models\Reward;
use App\Models\VoxReward;
use App\Models\VoxCashout;
use App\Models\UserBan;
use App\Models\UserAsk;
use App\Models\UserTeam;
use App\Models\DcnTransaction;
use App\Models\UserLogin;
use Carbon\Carbon;

use Request;
use Image;
use Auth;


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
        'description',
        'zip',
        'address',
        'phone',
        'website',
        'city_id',
        'country_id',
        'gender',
        'birthyear',
        'avg_rating',
        'ratings',
        'strength',
        'widget_activated',
        'invited_by',
        'hasimage',
        'is_approved',
        'is_verified',
        'verified_on',
        'verification_code',
        'phone_verified',
        'phone_verified_on',
        'register_reward',
        'register_tx',
        'vox_address',
        'vox_active',
        'fb_id',
        'civic_id',
        'platform',
        'gdpr_privacy',
        'self_deleted',
        'allow_withdraw'
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
        return $this->hasMany('App\Models\Review', 'user_id', 'id')->where('status', 'accepted')->orderBy('id', "DESC");
    }
    public function reviews_in_dentist() {
        return $this->hasMany('App\Models\Review', 'dentist_id', 'id')->where('status', 'accepted');
    }
    public function reviews_in_clinic() {
        return $this->hasMany('App\Models\Review', 'clinic_id', 'id')->where('status', 'accepted');
    }
    public function reviews_in() {
        return $this->reviews_in_dentist->merge($this->reviews_in_clinic)->sortBy(function ($review, $key) {

            if($review->verified) {
                return -$review->upvotes;
            } else {
                return 99999 - $review->upvotes;
            }
        });
    }
    public function upvotes() {
        return $this->hasMany('App\Models\ReviewUpvote', 'user_id', 'id');
    }
    public function photos() {
        return $this->hasMany('App\Models\UserPhoto', 'user_id', 'id');
    }
    public function bans() {
        return $this->hasMany('App\Models\UserBan', 'user_id', 'id')->orderBy('id', 'DESC');
    }
    public function invites() {
        return $this->hasMany('App\Models\UserInvite', 'user_id', 'id')->orderBy('id', 'DESC');
    }
    public function asks() {
        return $this->hasMany('App\Models\UserAsk', 'dentist_id', 'id')->orderBy('id', 'DESC');
    }
    public function history() {
        return $this->hasMany('App\Models\DcnTransaction', 'user_id', 'id')->orderBy('id', 'DESC');
    }
    public function logins() {
        return $this->hasMany('App\Models\UserLogin', 'user_id', 'id')->orderBy('id', 'DESC');
    }
    public function team() {
        return $this->hasMany('App\Models\UserTeam', 'user_id', 'id');
    }
    public function teamApproved() {
        return $this->hasMany('App\Models\UserTeam', 'user_id', 'id')->where('approved', true);
    }
    public function my_workplace() {
        return $this->hasMany('App\Models\UserTeam', 'dentist_id', 'id');
    }
    public function my_workplace_approved() {
        return $this->hasMany('App\Models\UserTeam', 'dentist_id', 'id')->where('approved', true);
    }

    public function getWebsiteUrl() {
        return mb_strpos( $this->website, 'http' )!==false ? $this->website : 'http://'.$this->website;
    }

    public function getName() {
        return ($this->title ? $this->title.' ' : '').$this->name;
    }

    public function getNameShort() {
        return explode(' ', $this->name)[0];
    }

    public function getMaskedPhone() {
        return '0'.substr($this->phone, 0, 3).' **** '.substr($this->phone, mb_strlen($this->phone)-2, 2);
    }
    public function getMaskedEmail() {
        $mail_arr = explode('@', $this->email);
        return substr($mail_arr[0], 0, 3).'****@'.$mail_arr[1];
    }

    public function wasInvitedBy($user_id) {
        return $this->hasMany('App\Models\UserInvite', 'invited_id', 'id')->where('user_id', $user_id)->first();
    }

    public function hasAskedDentist($dentist_id) {
        return $this->hasMany('App\Models\UserAsk', 'user_id', 'id')->where('dentist_id', $dentist_id)->first();
    }

    public function isBanned($domain) {
        foreach ($this->bans as $ban) {
            if($ban->domain==$domain && ($ban->expires===null || Carbon::now()->lt( $ban->expires ) ) ) {
                return $ban;
            }
        }

        return false;
    }

    public function getStrength() {
        $ret = [];

        if($this->is_dentist) {
            $ret['photo-dentist'] = $this->hasimage ? true : false;
            $ret['info'] = ($this->name && $this->phone && $this->description && $this->email && $this->country_id && $this->city_id && $this->zip && $this->address && $this->website) ? true : false;
            $ret['gallery'] = $this->photos->isNotEmpty() ? true : false;
            $ret['wallet'] = $this->my_address() ? true : false;
            $ret['invite-dentist'] = $this->invites->isNotEmpty() ? true : false;
            $ret['widget'] = $this->widget_activated ? true : false;
        } else {
            $ret['photo-patient'] = $this->hasimage ? true : false;
            $ret['wallet'] = $this->my_address() ? true : false;
            $ret['review'] = $this->reviews_out->isNotEmpty() ? true : false;
            $ret['invite-patient'] = $this->invites->isNotEmpty() ? true : false;
        }
        return $ret;
    }

    public function getStrengthNumber() {
            
        $num = 0;
        $s = $this->getStrength();
        foreach ($s as $val) {
            if ($val == true) {
                $num++;
            }            
        }

        return $num;
    }

    public function updateStrength() {
        $this->strength = $this->getStrengthNumber();
        $this->save();
    }

    public function getPrevBansCount($domain='vox') {
        $times = 0;
        foreach ($this->bans as $ban) {
            if($ban->domain==$domain) {
                $times++;
            }
        }

        return $times;
    }

    public function banUser($domain, $reason='') {
        $times = $this->getPrevBansCount();
        $ban = new UserBan;
        $ban->user_id = $this->id;
        $ban->domain = $domain;
        $days = 0;
        if($times==0) {
            $days = 1;
            $ban->expires = Carbon::now()->addDays( $days );
        } else if($times==1) {
            $days = 3;
            $ban->expires = Carbon::now()->addDays( $days );
        } else if($times==2) {
            $days = 7;
            $ban->expires = Carbon::now()->addDays( $days );
        }
        if($reason) {
            $ban->type = $reason;
        }
        $ban->save();

        if($times<3) {
            $this->sendTemplate(15, [
                'expires' => $ban->expires->toTimeString().' '.$ban->expires->toFormattedDateString(),
                'ban_days' => $days,
                'ban_hours' => $days*24
            ]);
        } else {
            $this->sendTemplate(16);              
        }

        return [
            'ban' => $ban,
            'times' => $times,
            'days' => $days,
        ];
    }

    public function hasReviewTo($dentist_id) {
        $dr = Review::where([
            ['user_id', $this->id],
            ['dentist_id', $dentist_id],
        ])->first();
        $cr = Review::where([
            ['user_id', $this->id],
            ['clinic_id', $dentist_id],
        ])->first();
        return $dr ? $dr : ( $cr ? $cr : null );
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
        $token = md5($this->id.date('WY').env('SALT_INVITE'));
        $token = preg_replace("/[^a-zA-Z0-9]/", "", $token);
        return $token;
    }
    public function get_token() {
        //dd($this->email.$this->id);
        $token = md5($this->email.$this->id.date('WY').env('SALT'));
        $token = preg_replace("/[^a-zA-Z0-9]/", "", $token);
        return $token;
    }
    public function get_widget_token() {
        //dd($this->email.$this->id);
        $token = md5($this->email.$this->id.date('WY').env('SALT_WIDGET'));
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
        $this->attributes['slug'] = $this->makeSlug();
        //
    }

    private function makeSlug() {
        $name = $this->name;
        $i=0;
        $tryval = $name;
        while( self::where('slug', 'LIKE', Str::slug($tryval))->where('id', '!=', $this->id)->first() ) {
            $i++;
            $tryval = $name.$i;
        }
        return Str::slug($tryval);
    }

    public function getLink() {
        return getLangUrl('dentist/'.$this->slug);
    }

    public function parseCategories($categories) {
        return array_intersect_key( $categories, array_flip( array_intersect_key(config('categories'), array_flip( $this->categories->pluck('category_id')->toArray() ) ) ) );
    }

    public function getImageUrl($thumb = false) {
        return $this->hasimage ? url('/storage/avatars/'.($this->id%100).'/'.$this->id.($thumb ? '-thumb' : '').'.jpg') : url('new-vox-img/no-avatar-'.($this->is_dentist ? '1' : '0').'.png');
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
        $this->updateStrength();
        $this->save();
    }

    public function recalculateRating() {
        $rating = 0;
        foreach ($this->reviews_in() as $review) {
            $rating += $review->rating;
        }

        $this->avg_rating = $this->reviews_in()->count() ? $rating / $this->reviews_in()->count() : 0;
        $this->ratings = $this->reviews_in()->count();
        $this->save();
    }

    public function sendSMS($sms_text) {
        $formatted_phone = $this->country->phone_code.$this->phone;
        file_get_contents('https://bulksrv.allterco.net/sendsms/sms.php?nmb_from=1909&user=SWISSDENTAPRIME&pass=m9rr95er9em&nmb_to='.$formatted_phone.'&text='.urlencode($sms_text).'&dlrr=1');
    }

    public function getReviewLimits() {
        if( Auth::guard('admin')->user() ) {
            return null;
        }

        $limits = config('limits.reviews');
        
        if($this->reviews_out->isEmpty()) {
            return null;
        }

        $yearly = 0;
        $quarterly = 0;
        //$monthly = 0;
        foreach ($this->reviews_out as $review) {
            $days = $review->created_at->diffInDays( Carbon::now() );
            if($days>365) {
                break;
            }
            $yearly++;
            // if($days>=31) {
            //     $monthly++;
            // }
            if($days<=93) {
                $quarterly++;
            }
        }

        if($quarterly>=$limits['quarterly']) {
            return 'quarterly';
        }
        if($yearly>=$limits['yearly']) {
            return 'yearly';
        }
        
        return null;
    }

    public function cantReviewDentist($dentist_id) {        
        if($this->reviews_out->isEmpty()) {
            return null;
        }        
        if($this->hasReviewTo($dentist_id)) {
            return null;
        }
        if($this->wasInvitedBy($dentist_id)) {
            return null;
        }

        $nonverified = [];
        foreach ($this->reviews_out as $review) {
            if(!$review->verified) {
                if($review->dentist_id) {
                    $nonverified[$review->dentist_id] = $review->dentist_id;
                }
                if($review->clinic_id) {
                    $nonverified[$review->clinic_id] = $review->clinic_id;
                }
            }
        }

        return count($nonverified)>=3 ? true : null;
    }

    public function my_address() {
        $my_dcn_address = $this->register_reward ? $this->register_reward : ( $this->vox_address ? $this->vox_address : null );
        if(!$my_dcn_address) {
            foreach ($this->reviews_out as $ro) {
                if($ro->reward_address) {
                    $my_dcn_address = $ro->reward_address;
                    break;
                }
            }
        }

        return $my_dcn_address;        
    }

    public function canIuseAddress( $address ) {
        $used = self::where('register_reward', 'LIKE', $address)->first();
        if($used && $used->id!=$this->id) {
            return false;
        }

        $used_vox = self::where('vox_address', 'LIKE', $address)->first();
        if($used_vox && $used_vox->id!=$this->id) {
            return false;
        }

        $used_reward = Review::where('reward_address', 'LIKE', $address)->first();
        if($used_reward && $used_reward->user_id!=$this->id) {
            return false;
        }
        $used_transaction = DcnTransaction::where('address', 'LIKE', $address)->first();
        if($used_transaction && $used_transaction->user_id!=$this->id) {
            return false;
        }

        return true;
    }

    public static function getBalance($address) {

        $ret = [
            'success' => false
        ];
        $curl = file_get_contents('https://api.etherscan.io/api?module=account&action=tokenbalance&contractaddress=0x08d32b0da63e2C3bcF8019c9c5d849d7a9d791e6&address='.$address.'&tag=latest&apikey='.env('ETHERSCAN_API'));
        if(!empty($curl)) {
            $curl = json_decode($curl, true);
            if($curl['status']) {
                $ret['success'] = true;
                $ret['result'] = $curl['result'];
            }
        }

        return $ret;
    }

    public function getTrpBalance() {
        $income = TrpReward::where('user_id', $this->id)->sum('reward');
        $cashouts = TrpCashout::where('user_id', $this->id)->sum('reward');

        return $income - $cashouts;
    }

    //
    //
    // Vox 
    //
    //

    public function getVoxBalance() {
        $income = VoxReward::where('user_id', $this->id)->sum('reward');
        $cashouts = VoxCashout::where('user_id', $this->id)->sum('reward');

        return $income - $cashouts;
    }

    public function madeTest($id) {
        return VoxReward::where('user_id', $this->id)
        ->where('vox_id', $id)
        ->first();
    }

    public function filledVoxes() {
        return VoxReward::where('user_id', $this->id)->with('vox')->whereHas('vox', function ($query) {
            $query->where('type', 'normal');
        })->get()->pluck('vox_id')->toArray();
    }

    public function vox_cashouts() {
        return $this->hasMany('App\Models\VoxCashout', 'user_id', 'id')->orderBy('id', 'DESC');
    }
    public function vox_rewards() {
        return $this->hasMany('App\Models\VoxReward', 'user_id', 'id')->orderBy('id', 'DESC');
    }

    public function vox_should_ban() {
        $tests = VoxReward::where('user_id', $this->id)
        ->where('is_scam', '1')
        ->where('created_at', '>', Carbon::now()->subDays(7))
        ->count();


        $answers = VoxAnswer::where('user_id', $this->id)
        ->where('is_scam', '1')
        ->where('created_at', '>', Carbon::now()->subDays(7))
        ->count();

        return $tests>=3 || $answers>=3;

    }

    public function canInvite($platform) {
        if($platform == 'trp') {
            return ($this->is_verified || $this->fb_id) && !( $this->is_dentist && !$this->is_approved );
        } else {
            return $this->is_verified || $this->fb_id;            
        }
    }

    public function canWithdraw($platform) {
        if($platform == 'trp') {
            return $this->is_verified && $this->email && $this->civic_id && !( $this->is_dentist && !$this->is_approved );
        } else {
            return $this->is_verified && $this->email && $this->civic_id;
        }
    }

    public function getSameIPUsers() {
        if( $this->logins->pluck('ip')->toArray() ) {
            $list = UserLogin::where('user_id', '!=', $this->id)->whereIn('ip', $this->logins->pluck('ip')->toArray() )->groupBy('user_id')->get();
            return $list->count();
        }
        return false;
    }

    public static function getCount($type) {
        $fn = storage_path('user-count-'.$type);
        $t = file_exists($fn) ? filemtime($fn) : null;
        if(!$t || $t < time()-300) {
            $cnt = self::where('platform', $type)->count();
            file_put_contents($fn, $cnt);
        }
        return file_get_contents($fn);

    }

    public static function getDentistCount() {
        $fn = storage_path('dentist-count-'.$type);
        $t = file_exists($fn) ? filemtime($fn) : null;
        if(!$t || $t < time()-300) {
            $cnt = self::where('is_dentist', 1)->where('is_approved', 1)->count();
            file_put_contents($fn, $cnt);
        }
        return file_get_contents($fn);

    }


 

    public static function getTempImageName() {
        return md5( microtime(false) ).'.jpg';
    }
    public static function getTempImageUrl($name, $thumb = false) {
        return url('/storage/tmp/'.($thumb ? 'thumb-' : '').$name);
    }
    public static function getTempImagePath($name, $thumb = false) {
        $folder = storage_path().'/app/public/tmp';
        if(!is_dir($folder)) {
            mkdir($folder);
        }
        return $folder.'/'.($thumb ? 'thumb-' : '').$name;
    }

    public static function addTempImage($img) {
        $name = self::getTempImageName();
        $to = self::getTempImagePath($name);
        $to_thumb = self::getTempImagePath($name, true);

        $img->resize(1920, null, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });
        $img->save($to);
        $img->heighten(400, function ($constraint) {
            $constraint->upsize();
        });
        $img->save($to_thumb);
        
        return [ self::getTempImageUrl($name, true), self::getTempImageUrl($name), $name ];
    }

    public static function isGasExpensive() {

        $url = file_get_contents('https://dentacoin.net/gas-price');

        $gas = json_decode($url, true);

        if(intVal($gas['gasPrice']) > intVal($gas['treshold']) ) {
            return true;
        } else {
            return false;
        }
    }

    public function loggedFromBadIp() {
        $users_with_same_ip = UserLogin::where('ip', 'like', Request::ip())->where('user_id', '!=', $this->ip)->groupBy('user_id')->get()->count();

        if ($users_with_same_ip >=3 && !$this->allow_withdraw ) {
            return true;
        } else {
            return false;
        }
    }

    public static function lastLoginUserId() {
        return UserLogin::where('ip', 'like', Request::ip())->orderBy('id', 'DESC')->first();
    }


    public function checkForWelcomeCompletion() {

        $first = Vox::where('type', 'home')->first();
        $has_test = !empty($_COOKIE['first_test']) ? json_decode($_COOKIE['first_test'], true) : null;
        if( $has_test ) {

            $first_question_ids = $first->questions->pluck('id')->toArray();

            if(!$this->madeTest($first->id)) {
                foreach ($has_test as $q_id => $a_id) {

                    if($q_id == 'location') {
                        $country_id = $a_id;
                        $this->country_id = $country_id;
                        $this->save();
                    } else if($q_id == 'birthyear') {
                        $this->birthyear = $a_id;
                        $this->save();
                    } else if($q_id == 'gender') {
                        $this->gender = $a_id;
                        $this->save();
                    } else {
                        $answer = new VoxAnswer;
                        $answer->user_id = $this->id;
                        $answer->vox_id = $first->id;
                        $answer->question_id = $q_id;
                        $answer->answer = $a_id;
                        $answer->country_id = $this->country_id;
                        $answer->save();
                    }
                }
                $reward = new VoxReward;
                $reward->user_id = $this->id;
                $reward->vox_id = $first->id;
                $reward->reward = $first->getRewardTotal();
                $reward->save();
            }
            setcookie('first_test', null, time()-600, '/');

        }
    }

    public static function checkForBlockedIP() {
        include_once("/var/www/html/blocked/blockscript/detector.php");
        return !empty($_SERVER['blockscript_blocked']) && $_SERVER['blockscript_blocked']=='YES';
    }
}