<?php

namespace App\Models;

use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Authenticatable;
use Illuminate\Support\Str;

use \SendGrid\Mail\PlainTextContent as PlainTextContent;
use \SendGrid\Mail\HtmlContent as HtmlContent;
use \SendGrid\Mail\Mail as SendGridMail;
use \SendGrid\Mail\Subject as Subject;
use \SendGrid\Mail\From as From;
use \SendGrid\Mail\To as To;

use DeviceDetector\Parser\Device\DeviceParserAbstract;
use DeviceDetector\DeviceDetector;

use Laravel\Passport\HasApiTokens;
use WebPConvert\WebPConvert;
use Carbon\Carbon;

use App\Models\DcnTransactionHistory;
use App\Models\StopEmailValidation;
use App\Models\DentistPageview;
use App\Models\EmailValidation;
use App\Models\BlacklistBlock;
use App\Models\DcnTransaction;
use App\Models\EmailTemplate;
use App\Models\WalletAddress;
use App\Models\AnonymousUser;
use App\Models\DentistClaim;
use App\Models\UserStrength;
use App\Models\WhitelistIp;
use App\Models\UserAction;
use App\Models\DcnCashout;
use App\Models\UserLogin;
use App\Models\DcnReward;
use App\Models\Blacklist;
use App\Models\UserTeam;
use App\Models\GasPrice;
use App\Models\UserBan;
use App\Models\UserAsk;
use App\Models\Reward;
use App\Models\Review;
use App\Models\Email;
use App\Models\Vox;

use Request;
use Cookie;
use Image;
use Auth;
use Mail;
use App;

class User extends Model implements AuthenticatableContract, CanResetPasswordContract {

    use Notifiable, HasApiTokens, SoftDeletes, Authenticatable, CanResetPassword;

    protected $fillable = [
    	'email',
        'email_public',
        'email_clean',
    	'password', 
        'remember_token',
        'is_dentist',
        'is_clinic',
        'is_partner',
        'featured',
        'top_dentist_month',
        'title',
        'name',
        'name_alternative',
        'slug',
        'description',
        'short_description',
        'zip',
        'state_name',
        'state_slug',
        'city_name',
        'address',
        'lat',
        'lon',
        'custom_lat_lon',
        'phone',
        'website',
        'socials',
        'work_hours',
        'working_position',
        'working_position_label',
        'dentist_practice',
        'accepted_payment',
        'status',
        'patient_status',
        'ownership',
        'is_verified',
        'is_approved',
        'city_id',
        'country_id',
        'gender',
        'birthyear',
        'maritial_status',
        'children',
        'household_children',
        'education',
        'employment',
        'job',
        'job_title',
        'income',
        'avg_rating',
        'ratings',
        'strength',
        'widget_activated',
        'widget_site',
        'invited_by',
        'invited_from_form',
        'invited_himself_reg',
        'hasimage',
        'hasimage_social',
        'register_reward',
        'dcn_address',
        'vox_address',
        'tw_id',
        'gp_id',
        'fb_id',
        'apple_id',
        'civic_id',
        'civic_kyc',
        'civic_kyc_hash',
        'platform',
        'patient_of',
        'is_hub_app_dentist',
        'place_id',
        'unsubscribe',
        'gdpr_privacy',
        'self_deleted',
        'allow_withdraw',
        'grace_end',
        'grace_notified',
        'recover_token',
        'fb_recommendation',
        'first_login_recommendation',
        'haswebp',
        'ip_protected',
        'is_logout',
        'vip_access',
    ];
    protected $dates = [
        'verified_on',
        'recover_at',
        'self_deleted_at',
        'withdraw_at',
        'created_at',
        'updated_at',
        'deleted_at',
        'grace_end',
    ];

    // protected $casts = [
    //     'top_dentist_month' => 'array',
    // ];


    public function had_first_transaction() {
        return $this->hasOne('App\Models\DcnTransaction', 'user_id', 'id')->oldest();
    }
    public function firstTransaction() {
        return $this->hasOne('App\Models\DcnTransaction', 'user_id', 'id')->where('status', 'first');
    }
    public function banAppeal() {
        return $this->hasOne('App\Models\BanAppeal', 'user_id', 'id')->oldest();
    }
    public function emails() {
        return $this->hasMany('App\Models\Email', 'user_id', 'id')->orderBy('id', 'DESC');
    }
    public function allBanAppeals() {
        return $this->hasMany('App\Models\BanAppeal', 'user_id', 'id')->orderBy('id', 'DESC');
    }
    public function oldEmails() {
        return $this->hasMany('App\Models\OldEmail', 'user_id', 'id')->orderBy('id', 'DESC');
    }
    public function newBanAppeal() {
        return $this->hasOne('App\Models\BanAppeal', 'user_id', 'id')->where('status', 'new')->oldest();
    }
    public function actions() {
        return $this->hasMany('App\Models\UserAction', 'user_id', 'id');
    }
    public function city() {
        return $this->hasOne('App\Models\City', 'id', 'city_id');
    }
    public function country() {
        return $this->hasOne('App\Models\Country', 'id', 'country_id')->with('translations');
    }
    public function categories() {
        return $this->hasMany('App\Models\UserCategory', 'user_id', 'id');
    }
    public function wallet_addresses() {
        return $this->hasMany('App\Models\WalletAddress', 'user_id', 'id')->orderBy('id', 'DESC');
    }
    public function main_wallet_address() {
        return $this->hasOne('App\Models\WalletAddress', 'id', 'user_id')->where('selected_wallet_address', 1);
    }
    public function cross_check() {
        return $this->hasMany('App\Models\VoxCrossCheck', 'user_id', 'id');
    }
    public function all_rewards() {
        return $this->hasMany('App\Models\DcnReward', 'user_id', 'id')->orderBy('id', 'DESC');
    }
    public function dentist_fb_page() {
        return $this->hasMany('App\Models\DentistFbPage', 'dentist_id', 'id');
    }
    public function invitor() {
        return $this->hasOne('App\Models\User', 'id', 'invited_by');
    }
    public function patient_invites_dentist() {
        return $this->hasMany('App\Models\User', 'invited_by', 'id')->where('is_dentist', 1)->orderBy('id', "DESC");
    }
    public function reviews_out() {
        return $this->hasMany('App\Models\Review', 'user_id', 'id')->where('status', 'accepted')->orderBy('id', "DESC");
    }
    public function reviews_out_standard() {
        return $this->reviews_out->reject(function($item) {
            return $item->youtube_id;
        });
    }
    public function reviews_out_video() {
        return $this->reviews_out->reject(function($item) {
            return !$item->youtube_id;
        });
    }
    public function reviews_in_dentist() {
        return $this->hasMany('App\Models\Review', 'dentist_id', 'id')->where('status', 'accepted')->with('user')->with('answers')->orderBy('id', 'desc');
    }
    public function reviews_in_clinic() {
        return $this->hasMany('App\Models\Review', 'clinic_id', 'id')->where('status', 'accepted')->with('user')->with('answers')->orderBy('id', 'desc');
    }
    public function reviews_in() {
        return $this->reviews_in_dentist->merge($this->reviews_in_clinic)->sortByDesc(function ($review, $key) {
            if($review->verified) {
                return 1000000 + $review->id;
            } else {
                return $review->id;
            }
        });
    }
    public function old_unclaimed_profile() {
        return $this->hasOne('App\Models\UnclaimedDentist', 'user_id', 'id')->whereNull('completed');
    }
    public function reviews_in_standard() {
        return $this->reviews_in()->reject(function($item) {
            return $item->youtube_id;
        });
    }
    public function reviews_in_video() {
        return $this->reviews_in()->reject(function($item) {
            return !$item->youtube_id;
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
    public function bansWithDeleted() {
        return $this->hasMany('App\Models\UserBan', 'user_id', 'id')->orderBy('id', 'DESC')->withTrashed();
    }
    public function vox_bans() {
        return $this->hasMany('App\Models\UserBan', 'user_id', 'id')->where('domain', 'vox')->orderBy('id', 'DESC');
    }
    public function permanentBans() {
        return $this->hasMany('App\Models\UserBan', 'user_id', 'id')->whereNull('expires')->orderBy('id', 'DESC');
    }
    public function permanentVoxBan() {
        return $this->hasOne('App\Models\UserBan', 'id', 'user_id')->where('platform', 'vox')->whereNull('expires');
    }
    public function permanentTrpBan() {
        return $this->hasOne('App\Models\UserBan', 'id', 'user_id')->where('platform', 'trp')->whereNull('expires');
    }
    public function invites() {
        return $this->hasMany('App\Models\UserInvite', 'user_id', 'id')->orderBy('created_at', 'DESC');
    }
    public function invites_team_unverified() {
        return $this->hasMany('App\Models\UserInvite', 'user_id', 'id')->whereNotNull('for_team')->whereNull('invited_id')->orderBy('created_at', 'DESC');
    }
    public function patients_invites() {
        return $this->hasMany('App\Models\UserInvite', 'user_id', 'id')->whereNull('for_team')->where(function ($query) {
            $query->where('platform', 'trp')
            ->orWhere('platform', null);
        })->orderBy('created_at', 'DESC');
    }
    public function claims() {
        return $this->hasMany('App\Models\DentistClaim', 'dentist_id', 'id')->orderBy('created_at', 'DESC');
    }
    public function recommendations() {
        return $this->hasMany('App\Models\Recommendation', 'user_id', 'id')->orderBy('created_at', 'DESC');
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
        return $this->hasMany('App\Models\UserTeam', 'user_id', 'id')->orderBy('approved', 'desc');
    }
    public function team_new_clinic() {
        return $this->hasMany('App\Models\UserTeam', 'user_id', 'id')->where('new_clinic', true);
    }
    public function teamApproved() {
        return $this->hasMany('App\Models\UserTeam', 'user_id', 'id')->where('approved', true)->orderBy('team_order', 'asc');
    }
    public function teamUnapproved() {
        return $this->hasMany('App\Models\UserTeam', 'user_id', 'id')->where('approved', false);
    }
    public function my_workplace() {
        return $this->hasMany('App\Models\UserTeam', 'dentist_id', 'id');
    }
    public function my_workplace_unapproved() {
        return $this->hasMany('App\Models\UserTeam', 'dentist_id', 'id')->where('approved', false);
    }
    public function my_workplace_approved() {
        return $this->hasMany('App\Models\UserTeam', 'dentist_id', 'id')->where('approved', true);
    }
    public function transactions_count() {
        return $this->hasMany('App\Models\DcnTransaction', 'user_id', 'id')->count();
    }
    public function transactions() {
        return $this->hasMany('App\Models\DcnTransaction', 'user_id', 'id');
    }

    public function getWebsiteUrl() {
        return mb_strpos( $this->website, 'http' )!==false ? $this->website : 'http://'.$this->website;
    }

    public function getNames() {
        return ($this->title && $this->is_dentist && !$this->is_clinic ? config('titles')[$this->title].' ' : '').$this->name;
    }

    public function getNameSendGrid() {
        if ($this->title && $this->is_dentist && !$this->is_clinic) {
            $names = explode(' ', $this->name);
            
            if (count($names) > 1) {
                unset($names[0]);
            }
            $last_name = implode(' ', $names);
            
            return config('titles')[$this->title].' '.$last_name;
        } else {
            return $this->name;
        }
    }

    public function getNameShort() {
        return explode(' ', $this->name)[0];
    }

    public function getFormattedPhone($forlink=false) {
        $ret = '+'.$this->country->phone_code.' '.$this->phone;
        if($forlink) {
            $ret = str_replace(' ', '', $ret);
        }
        return $ret;
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

    public function canAskDentist($dentist_id) {
        $user_ask = UserAsk::where('user_id', $this->id)->where('dentist_id', $dentist_id )->orderBy('id', 'desc')->first();

        if (!empty($user_ask)) {
            $days = $user_ask->created_at->diffInDays( Carbon::now() );
            if($days>30) {
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }
    }

    public function approvedPatientcanAskDentist($dentist_id) {

        $lastReview = Review::where('user_id', $this->id)->where(function($query) use ($dentist_id) {
            $query->where( 'dentist_id', $dentist_id)->orWhere('clinic_id', $dentist_id);
        })->orderBy('id', 'desc')->first();

        $days = $lastReview->created_at->diffInDays( Carbon::now() );

        if ($this->canAskDentist($dentist_id)) {
            if($days>30) {
                return true;
            } else {
                return false;
            }

        } else {
            return false;
        }
    }

    public function isBanned($domain) {
        foreach ($this->bans as $ban) {
            if($ban->domain==$domain && ($ban->expires===null || Carbon::now()->lt( $ban->expires ) ) ) {
                return $ban;
            }
        }

        return false;
    }

    public function getStrengthCompleted($platform) {
            
        $num = 0;
        $s = UserStrength::getStrengthPlatform($platform, $this);

        if($platform == 'trp' && $this->is_dentist) {
            $num = intval($s['completed_steps']);
        } else {

            foreach ($s as $val) {
                if ($val['completed'] == true) {
                    $num++;
                }
            }
        }
        return $num;
    }

    public function getPrevBansCount($domain='vox', $type=null) {
        $times = 0;
        foreach ($this->bans as $ban) {
            if($ban->domain==$domain && (!$type || $type==$ban->type) ) {
                $times++;
            }
        }

        return $times;
    }

    public function banUser($domain, $reason='', $ban_for_id=null) {
        $times = $this->getPrevBansCount($domain, $reason);
        $ban = new UserBan;
        $ban->user_id = $this->id;
        $ban->domain = $domain;
        $ban->ban_for_id = $ban_for_id;
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
                'expires' => $ban->expires->toFormattedDateString().', '.$ban->expires->toTimeString(),
                'ban_days' => $days,
                'ban_hours' => $days*24
            ], 'vox');
        } else {

            $notifications = $this->website_notifications;

            if(!empty($notifications)) {
                
                if (($key = array_search('vox', $notifications)) !== false) {
                    unset($notifications[$key]);
                }

                $this->website_notifications = $notifications;
                $this->save();
            }

            $sg = new \SendGrid(env('SENDGRID_PASSWORD'));

            $request_body = new \stdClass();
            $request_body->recipient_emails = [$this->email];
            
            $vox_group_id = config('email-preferences')['product_news']['vox']['sendgrid_group_id'];
            $response = $sg->client->asm()->groups()->_($vox_group_id)->suppressions()->post($request_body);

            $this->sendTemplate(16, null, 'vox');              
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
        ])->orderBy('id', 'desc')->first();

        $cr = Review::where([
            ['user_id', $this->id],
            ['clinic_id', $dentist_id],
        ])->orderBy('id', 'desc')->first();

        return $dr ? $dr : ( $cr ? $cr : null );
    }

    public function cantSubmitReviewToSameDentist($dentist_id) {

        if ($this->hasReviewTo($dentist_id)) {

            $review = $this->hasReviewTo($dentist_id);
            $days = $review->created_at->diffInDays( Carbon::now() );

            if($days>93) {
                return false;
            } else {
                $heAllowed = UserAsk::where('user_id', $this->id)
                ->where('dentist_id', $dentist_id)
                ->where('status', 'yes')
                ->where('created_at', '>=', Carbon::now()->modify('-1 months'))
                ->first();

                return $heAllowed && $days>30 ? false : true;
            }

        } else {
            return false;
        }
    }

    public function usefulVotesForDenist($dentist_id) {
        $myid = $this->id;
        return Review::where([
            ['dentist_id', $dentist_id],
        ])->whereHas('upvotes', function ($query) use ($myid) {
            $query->where('user_id', $myid);
        })->get()->pluck('id')->toArray();
    }
    public function unusefulVotesForDenist($dentist_id) {
        $myid = $this->id;
        return Review::where([
            ['dentist_id', $dentist_id],
        ])->whereHas('downvotes', function ($query) use ($myid) {
            $query->where('user_id', $myid);
        })->get()->pluck('id')->toArray();
    }
    
    public function get_invite_token() {
        //dd($this->email.$this->id);
        $token = md5($this->id.env('SALT_INVITE'));
        $token = preg_replace("/[^a-zA-Z0-9]/", "", $token);
        return $token;
    }
    public function get_token() {
        //dd($this->email.$this->id);
        $token = md5($this->email.$this->id.env('SALT'));
        $token = preg_replace("/[^a-zA-Z0-9]/", "", $token);
        return $token;
    }
    public function get_widget_token() {
        //dd($this->email.$this->id);
        $token = md5($this->email.$this->id.env('SALT_WIDGET'));
        $token = preg_replace("/[^a-zA-Z0-9]/", "", $token);
        return $token;
    }

    public function sendTemplate($template_id, $params=null, $platform=null, $unsubscribed=null, $anonymous_email=null) {
        $item = new Email;
        $item->user_id = $this->id;
        $item->template_id = $template_id;
        $item->meta = $params;

        if($platform) {
            $item->platform = $platform;
        } else {
            if( mb_substr(Request::path(), 0, 3)=='cms' || empty(Request::getHost()) ) {
                $item->platform = $this->platform;
            } else {
                $item->platform = mb_strpos( Request::getHost(), 'vox' )!==false ? 'vox' : 'trp';
            }
        }

        $item->save();

        $to_be_send = $this->sendgridEmailValidation($template_id, $this->email);

        if(!$to_be_send) {
            $item->invalid_email = true;
            $item->save();
        }

        if(!$unsubscribed && $to_be_send) {

            $item->send($anonymous_email);
        }

        return $item;
    }

    public function sendGridTemplate($template_id, $substitutions=null, $platform=null, $is_skipped=null, $anonymous_email=null) {
        return self::unregisteredSendGridTemplate($this, $this->email, $this->getNameSendGrid(), $template_id, $substitutions, $platform, $is_skipped, $anonymous_email);
    }

    public static function unregisteredSendGridTemplate($user, $to_email, $to_name, $template_id, $substitutions=null, $platform=null, $is_skipped=null, $anonymous_email=null) {

        $item = new Email;
        $item->user_id = $user->id;
        $item->template_id = $template_id;
        $item->meta = $substitutions;
        if($platform) {
            $item->platform = $platform;            
        } else {
            if( mb_substr(Request::path(), 0, 3)=='cms' || empty(Request::getHost()) ) {
                $item->platform = $user->platform;
            } else {
                $item->platform = mb_strpos( Request::getHost(), 'vox' )!==false ? 'vox' : 'trp';
            }
        }
        $item->save();

        if(empty($anonymous_email)) {
            
            if($user->id != 3 && !empty($item->template->subscribe_category)) {
                $cat = $item->template->subscribe_category;
                if($item->platform != 'dentacare' && $item->platform != 'dentists' && !in_array($item->platform, $user->$cat)) {
                    $item->unsubscribed = true;
                    $item->save();
                }
            }
        }

        $to_be_send = $user->sendgridEmailValidation($template_id, $to_email);

        if(!$to_be_send) {
            $item->invalid_email = true;
            $item->save();
        }

        if (empty($is_skipped) && empty($item->unsubscribed) && $to_be_send) {
            $sender = $item->platform=='vox' ? config('mail.from.address-vox') : ($item->platform == 'trp' ? config('mail.from.address') : config('mail.from.address-dentacoin'));
            $sender_name = $item->platform=='vox' ? config('mail.from.name-vox') : ($item->platform == 'trp' ? config('mail.from.name') : config('mail.from.name-dentacoin'));
            //$sender_name = config('platforms.'.$item->platform.'.name') ?? config('mail.from.name');

            $from = new From($sender, $sender_name);

            $tos = [new To( $to_email)];

            $email = new SendGridMail(
                $from,
                $tos
            );
            $email->setTemplateId($item->template->sendgrid_template_id);
            if($user->is_dentist && $template_id != 58 && $template_id != 59 && $template_id != 60 && $template_id != 61 && $template_id != 62 && $template_id != 106) {
                $email->addBcc("4097841@bcc.hubspot.com");
            }

            if($user->id == 3 && ($item->template->id == 84 || $item->template->id == 26 || $item->template->id == 83 || $item->template->id == 85 ) ) {

            } else {
                if ($item->template->category) {
                    $email->addCategory($item->template->category);
                } else {
                    $email->addCategory(strtoupper($item->platform).' Service '.($user->is_dentist ? 'Dentist' : 'Patient'));
                }
            }

            $domain = 'https://'.config('platforms.'.$user->platform.'.url').'/';

            $pageviews = DentistPageview::where('dentist_id', $user->id)->count();

            $defaulth_substitutions  = [
                "name" => $to_name,
                "platform" => $item->platform,
                "invite-patient" => getLangUrl( 'dentist/'.$user->slug, null, $domain).'?'. http_build_query(['popup'=>'popup-invite']),
                "lead-magnet-link" => $user->getLink().'?'. http_build_query(['popup'=>'popup-lead-magnet']),
                "homepage" => getLangUrl('/', null, $domain),
                "trp_profile" => $user->getLink(),
                "town" => $user->city_name ? $user->city_name : 'your town',
                "country" => $user->country_id ? Country::find($user->country_id)->name : 'your country',
                //"unsubscribe" => getLangUrl( 'unsubscription/'.$user->id.'/'.$user->get_token(), null, $domain),
                "unsubscribe" => 'https://api.dentacoin.com/api/update-single-email-preference/'.'?'. http_build_query(['fields'=>urlencode(self::encrypt(json_encode(array('email' => ($anonymous_email ? $anonymous_email : $to_email),'email_category' => $item->template->subscribe_category, 'platform' => $item->platform ))))]),
                "pageviews" => $pageviews,
                "trp" => url('https://reviews.dentacoin.com/'),
                "trp-dentist" => url('https://reviews.dentacoin.com/en/welcome-dentist/'),
                "vox" => url('https://dentavox.dentacoin.com/'),
                "partners" => url('https://dentacoin.com/partner-network'),
                "assurance" => url('https://assurance.dentacoin.com/'),
                "wallet" => url('https://wallet.dentacoin.com/'),
                "dcn" => url('https://dentacoin.com/'),
                "dentist" => url('https://dentists.dentacoin.com/'),
                "dentacare" => url('https://dentacare.dentacoin.com/'),
                "giftcards" => url('https://dentacoin.com/?payment=bidali-gift-cards'),
            ];


            if ($substitutions) {
                $defaulth_substitutions = array_merge($defaulth_substitutions, $substitutions);
            }

            foreach ($defaulth_substitutions as $key => $value) {
                $value = $value.'';
                $matches = '';
                preg_match_all("_(^|[\s.:;?\-\]<\(])(https?://[-\w;/?:@&=+$\|\_.!~*\|'()\[\]%#,☺]+[\w/#](\(\))?)(?=$|[\s',\|\(\).:;?\-\[\]>\)])_i", $value , $matches);

                if(!empty($matches)) {
                    foreach ($matches[0] as $match) {

                        $pos = mb_strpos($match, '?');
                        if ($pos === false) {
                            $separator = '?';
                        } else {
                            $separator = '&';
                        }
                        $new_match = $match.$separator.'utm_content='.urlencode($item->template->name);

                        $value = str_replace($match, $new_match, $value);                        
                    }                    
                }
                $defaulth_substitutions[$key] = $value;
            }

            $email->addDynamicTemplateDatas($defaulth_substitutions );
            
            $sendgrid = new \SendGrid(env('SENDGRID_PASSWORD'));
            $sendgrid->send($email);

            $item->sent = 1;
            $item->save();

        } else {
            $item->sent = 0;
            $item->save();
        }

        return $item;
    }

    public static function domain_exists($email, $record = 'MX'){
        if (mb_strpos($email, '@') !== false) {
            list($user, $domain) = explode('@', $email);
            return checkdnsrr($domain, $record);
        } else {
            return false;
        }
    }

    public function sendgridEmailValidation($template_id, $email) {
        $to_be_send = false;

        if($email == 'ali.hashem@dentacoin.com') {
            return true;
        }

        if(!self::domain_exists($email)) {
            return false;
        }

        if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $validation_templates = EmailTemplate::select('id')->where('validate_email', 1)->get()->pluck('id')->toArray();

        if (!in_array($template_id,  $validation_templates)) {
            return true;
        }

        if(!StopEmailValidation::find(1)->stopped) {

            $email_validation = EmailValidation::where('email', 'like', $email)->first();

            if(empty($email_validation)) {
                $query_params = new \stdClass();
                $query_params->email = $email;

                $curl = curl_init();

                curl_setopt_array($curl, array(
                    CURLOPT_URL => "https://api.sendgrid.com/v3/validations/email",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "POST",
                    CURLOPT_POSTFIELDS => json_encode($query_params),
                    CURLOPT_HTTPHEADER => array(
                        "authorization: Bearer ".env('SENDGRID_EMAIL_VALIDATION'),
                        "content-type: application/json"
                    ),
                ));

                $response = curl_exec($curl);
                $err = curl_error($curl);

                curl_close($curl);

                $new_email_validation = new EmailValidation;
                $new_email_validation->email = $email;
                $new_email_validation->from_user_id = $this->id;
                $new_email_validation->template_id = $template_id;

                if ($err) {
                    $new_email_validation->meta = 'resp_err: '.$err;
                    $new_email_validation->valid = false;
                } else {
                    $new_email_validation->meta = $response;
                    if(json_decode($response)->result->verdict == 'Valid') {
                        $new_email_validation->valid = true;
                        $to_be_send = true;
                    } else {
                        $new_email_validation->valid = false;
                    }
                }

                $new_email_validation->save();
            } else {
                if($email_validation->valid) {
                    $to_be_send = true;
                }
            }
        } else {
            $to_be_send = true;
        }

        return $to_be_send;
    } 


    public function setEmailAttribute($value) {
        $this->attributes['email_clean'] = str_replace('.', '', $value);
        $this->attributes['email'] = $value;
        $this->save();
    }

    public static function validateName($name) {
        $result = false;

        $found_name = self::where('name', 'LIKE', $name)->withTrashed()->first();
     
        if ($found_name) {
            $result = true;
        }
     
        return $result;
    }

    public static function validateWebsite($website) {
        $result = false;

        $found_website = self::where('website', 'LIKE', $website)->withTrashed()->first();
     
        if ($found_website) {
            $result = true;
        }
     
        return $result;
    }

    public static function validateEmail($email) {
        $result = false;

        $clean_email = str_replace('.', '', $email);
        $found_email = self::where('email_clean', 'LIKE', $clean_email)->withTrashed()->first();
     
        if ($found_email) {
            $result = true;
        }
     
        return $result;
    }

    public function validateMyEmail() {
        $result = false;

        $clean_email = str_replace('.', '', $this->email);
        $found_email = self::where('email_clean', 'LIKE', $clean_email)->where('id', '!=', $this->id)->first();
     
        if ($found_email) {
            $result = true;
        }
     
        return $result;
    }

    public function getWorkHoursAttribute() {
        return json_decode($this->attributes['work_hours'], true);
    }

    public function setWorkHoursAttribute($value) {
        $this->attributes['work_hours'] = $value ? json_encode($value) : '';
    }

    public function getSocialsAttribute() {
        return json_decode($this->attributes['socials'], true);
    }

    public function setSocialsAttribute($value) {
        if (is_array($value)) {
            foreach ($value as $key => $v) {
                if (empty($v)) {
                    unset($value[$key]);
                }
            }
        }
        $this->attributes['socials'] = $value ? json_encode($value) : '';
    }

    // public function setNameAttribute($value) {
    //     $this->attributes['name'] = $value;
    //     //$this->attributes['slug'] = $this->makeSlug();
    //     //
    // }
    public function setAddressAttribute($newvalue) {
        $this->attributes['address'] = $newvalue;
        if(!$this->custom_lat_lon) {
            $this->attributes['lat'] = null;
            $this->attributes['lon'] = null;
        }
        $this->attributes['city_name'] = null;
        $this->attributes['state_name'] = null;
        $this->attributes['state_slug'] = null;
        if( $this->country) {
            $info = self::validateAddress($this->country->name, $newvalue);
            if(!empty($info)) {
                foreach ($info as $key => $value) {
                    
                    if( in_array($key, $this->fillable) ) {
                        if(($key == 'lat' || $key == 'lon') && $this->custom_lat_lon) {

                        } else {
                            $this->attributes[$key] = $value;                        
                        }
                    }
                }
                if(empty($this->attributes['state_name'])) {
                    $this->attributes['state_name'] = $this->attributes['city_name'];
                }
            } else {
                $this->attributes['address'] = null;
            }
        }
        $this->save();
    }

    public static function validateAddress($country, $address) {
        $kingdoms = [
            'United Arab Emirates',
        ];

        $query = $country.', '.$address;
        //dd($query);

        $geores = \GoogleMaps::load('geocoding')
        ->setParam ([
            'address'    => $query,
        ])
        ->get();

        $geores = json_decode($geores);
        $ret = [];
        // dd($geores);
        if(!empty($geores->results[0]->geometry->location)) {

            if(in_array($country, $kingdoms)) {
                $ret['state_name'] = $country;
                $ret['state_slug'] = str_replace([' ', "'"], ['-', ''], $country);
                $ret['city_name'] = $country;
            } else {
                $ret = self::parseAddress( $geores->results[0]->address_components );
            }

                
            $ret['lat'] = $geores->results[0]->geometry->location->lat;
            $ret['lon'] = $geores->results[0]->geometry->location->lng;

            // $ret['info'] = [];
            // foreach ($geores->results[0]->address_components as $ac) {
            //     $ret['info'][] = implode(', ', $ac->types).': '.$ac->long_name;
            // }
        }

        return $ret;
    }

    public static function parseAddress( $components ) {
        $ret = [];

        $country_fields = [
            'country',
        ];

        foreach ($country_fields as $sf) {
            if( empty($ret['country_name']) ) {
                foreach ($components as $ac) {
                    if( in_array($sf, $ac->types) ) {
                        
                        if (preg_match('/[اأإء-ي]/ui', $ac->long_name)) {
                            $cname = $ac->long_name;
                        } else {
                            $cname = iconv('UTF-8', 'ASCII//TRANSLIT', $ac->long_name);
                            $cname = iconv('ASCII', 'UTF-8', $cname);
                        }
                        $ret['country_name'] = $cname;
                        break;
                    }
                }
            } else {
                break;
            }
        }


        $state_fields = [
            'administrative_area_level_1',
            'administrative_area_level_2',
            'administrative_area_level_3',
        ];

        foreach ($state_fields as $sf) {
            if( empty($ret['state_name']) ) {
                foreach ($components as $ac) {
                    if( in_array($sf, $ac->types) ) {
                        if (preg_match('/[اأإء-ي]/ui', $ac->long_name)) {
                            $cname = $ac->long_name;
                        } else {
                            $cname = iconv('UTF-8', 'ASCII//TRANSLIT', $ac->long_name);
                            $cname = iconv('ASCII', 'UTF-8', $cname);
                        }
                        $ret['state_name'] = $cname;
                        $ret['state_slug'] = str_replace([' ', "'"], ['-', ''], strtolower($cname));
                        break;
                    }
                }
            } else {
                break;
            }
        }

        $city_fields = [
            'postal_town',
            'locality',
            'administrative_area_level_5',
            'administrative_area_level_4',
            'administrative_area_level_3',
            'administrative_area_level_2',
            'sublocality_level_1',
            'neighborhood',
        ];

        foreach ($city_fields as $sf) {
            if( empty($ret['city_name']) ) {
                foreach ( $components as $ac) {
                    if( in_array($sf, $ac->types) ) {
                        
                        if (preg_match('/[اأإء-ي]/ui', $ac->long_name)) {
                            $cname = $ac->long_name;
                        } else {
                            $cname = iconv('UTF-8', 'ASCII//TRANSLIT', $ac->long_name);
                            $cname = iconv('ASCII', 'UTF-8', $cname);
                        }
                        $ret['city_name'] = $cname;
                        break;
                    }
                }
            } else {
                break;
            }
        }

         $zip_fields = [
            'postal_code',
            'zip',
        ];

        foreach ($zip_fields as $sf) {
            if( empty($ret['zip']) ) {
                foreach ( $components as $ac) {
                    if( in_array($sf, $ac->types) ) {
                        
                        if (preg_match('/[اأإء-ي]/ui', $ac->long_name)) {
                            $cname = $ac->long_name;
                        } else {
                            $cname = iconv('UTF-8', 'ASCII//TRANSLIT', $ac->long_name);
                            $cname = iconv('ASCII', 'UTF-8', $cname);
                        }
                        $ret['zip'] = $cname;
                        break;
                    }
                }
            } else {
                break;
            }
        }

        return $ret;
    }

    public function makeSlug() {
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
        return getLangUrl('dentist/'.$this->slug, null, 'https://reviews.dentacoin.com/');
    }

    public function parseCategories($categories) {
        return array_intersect_key( $categories, array_flip( array_intersect_key(config('categories'), array_flip( $this->categories->pluck('category_id')->toArray() ) ) ) );
    }

    public function getImageUrl($thumb = false) {
        return $this->hasimage ? url('/storage/avatars/'.($this->id%100).'/'.$this->id.($thumb ? '-thumb' : '').'.jpg').'?rev='.$this->updated_at->timestamp : url('new-vox-img/no-avatar-'.($this->is_dentist ? '1' : '0').'.png');
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

        if ($img->height() > $img->width()) {
            $img->heighten(400);
        } else {
            $img->widen(400);
        }
        $img->resizeCanvas(400, 400);

        //$img->fit( 400, 400 );
        $img->save($to_thumb);
        $this->hasimage = true;
        $this->hasimage_social = false;
        $this->refreshReviews();
        $this->save();

        $destination = self::getImagePath().'.webp';
        WebPConvert::convert(self::getImagePath(), $destination, []);

        $destination_thumb = self::getImagePath(true).'.webp';
        WebPConvert::convert(self::getImagePath(true), $destination_thumb, []);

    }

    public function recalculateRating() {
        $rating = 0;
        foreach ($this->reviews_in() as $review) {
            if (!empty($review->team_doctor_rating) && ($this->id == $review->dentist_id)) {
                $rating += $review->team_doctor_rating;
            } else {
                $rating += $review->rating;
            }
        }

        $this->avg_rating = $this->reviews_in()->count() ? $rating / $this->reviews_in()->count() : 0;
        $this->ratings = $this->reviews_in()->count();
        $this->save();
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

    public function canIuseAddress( $address ) {
        
        return true;
    }

    //
    //
    // Vox 
    //
    //

    public function filledDailyPolls() {
        return DcnReward::where('user_id', $this->id)->where('platform', 'vox')->where('type', 'daily_poll')->get()->pluck('reference_id')->toArray();
    }

    public function getTotalBalance($platform=null) {
        $income = DcnReward::where('user_id', $this->id);
        if (!empty($platform)) {
            $income = $income->where('platform', $platform);
        }
        $income = $income->sum('reward');
        
        $cashouts = DcnCashout::where('user_id', $this->id);
        if (!empty($platform)) {
            $cashouts = $cashouts->where('platform', $platform);
        }
        $cashouts = $cashouts->sum('reward');

        return $income - $cashouts;
    }

    public function madeTest($id) {
        return DcnReward::where('user_id', $this->id)
        ->where('type', 'survey')
        ->where('platform', 'vox')
        ->where('reference_id', $id)
        ->first();
    }

    public function filledVoxes() {
        return DcnReward::where('user_id', $this->id)->where('platform', 'vox')->where('type', 'survey')->with('vox')->whereHas('vox', function ($query) {
            $query->where('type', 'normal');
        })->get()->pluck('reference_id')->toArray();
    }

    public function filledFeaturedVoxes() {
        return DcnReward::where('user_id', $this->id)->where('platform', 'vox')->where('type', 'survey')->with('vox')->whereHas('vox', function ($query) {
            $query->where('type', 'normal')->where('featured', 1);
        })->get()->pluck('reference_id')->toArray();
    }

    public function dcn_cashouts() {
        return $this->hasMany('App\Models\DcnCashout', 'user_id', 'id')->orderBy('id', 'DESC');
    }

    public function vox_cashouts() {
        return $this->hasMany('App\Models\DcnCashout', 'user_id', 'id')->where('platform', 'vox')->orderBy('id', 'DESC');
    }

    public function vox_rewards() {
        return $this->hasMany('App\Models\DcnReward', 'user_id', 'id')->where('platform', 'vox')->orderBy('id', 'DESC');
    }

    public function surveys_rewards() {
        return $this->hasMany('App\Models\DcnReward', 'user_id', 'id')->where('platform', 'vox')->where('type', 'survey')->orderBy('id', 'DESC');
    }

    public function countAllSurveysRewards() {
        return count(DcnReward::where('user_id', $this->id)->where('platform', 'vox')->where('type', 'survey')->where('reference_id', '!=', 34)->get()->pluck('reference_id')->toArray());
    }

    public function vox_surveys_and_polls() {
        return $this->hasMany('App\Models\DcnReward', 'user_id', 'id')->where('platform', 'vox')->whereIn('type', ['daily_poll', 'survey'])->orderBy('id', 'DESC');
    }

    public function deleteActions() {

        // foreach ($this->reviews_out as $r) {
        //     if (!empty($r->dentist_id)) {
        //         $dentist = self::find($r->dentist_id);
        //     } else if(!empty($r->clinic_id)) {
        //         $dentist = self::find($r->clinic_id);
        //     }
        //     $r->delete();
        //     $dentist->recalculateRating();
        // }

        $id = $this->id;
        $teams = UserTeam::where(function($query) use ($id) {
            $query->where( 'dentist_id', $id)->orWhere('user_id', $id);
        })->get();

        if (!empty($teams)) {
            foreach ($teams as $team) {
                $dent_id = $team->dentist_id;
                $team->delete();

                $dent = User::find($dent_id);
                if(!empty($dent) && $dent->is_clinic) {

                    if ($dent->status == 'added_by_clinic_new') {
                        $dent->status = 'added_by_clinic_rejected';
                        $dent->save();
                    } else if($dent->status == 'dentist_no_email') {
                        $action = new UserAction;
                        $action->user_id = $dent->id;
                        $action->action = 'deleted';
                        $action->reason = 'his dentist was deleted/rejected';
                        $action->actioned_at = Carbon::now();
                        $action->save();

                        $dent->deleteActions();
                        self::destroy( $dent->id );
                    }
                }
            }
        }

        $user_invites = UserInvite::where(function($query) use ($id) {
            $query->where( 'user_id', $id)->orWhere('invited_id', $id);
        })->get();

        if (!empty($user_invites)) {
           foreach ($user_invites as $user_invite) {
               $user_invite->delete();
           }
        }

        if($this->claims->isNotEmpty()) {
            foreach ($this->claims as $c) {
                $c->delete();
            }
        }


        if(!$this->is_dentist && $this->patient_status != 'deleted') {

            $this->patient_status = 'deleted';
            $this->save();

            if(!empty($this->newBanAppeal)) {
                $this->newBanAppeal->status = 'rejected';
                $this->newBanAppeal->save();
            }
            
            if(!empty($this->email) && filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
                $this->sendTemplate(9, null, 'dentacoin');
            }
        }

        if($this->reviews_out->isNotEmpty()) {
            $mtext = 'User with reviews was deleted.
Link to user\'s profile in CMS: https://reviews.dentacoin.com/cms/users/edit/'.$this->id;

            Mail::raw($mtext, function ($message) {
                $message->from(config('mail.from.address'), config('mail.from.name'));
                $message->to( 'petya.ivanova@dentacoin.com' );
                $message->subject('Patient Who Submitted Reviews Was Deleted');
            });
        }

        $transactions = DcnTransaction::where('user_id', $this->id)->whereIn('status', ['new', 'failed', 'first', 'not_sent'])->get();

        if ($transactions->isNotEmpty()) {
            foreach ($transactions as $trans) {
                $trans->status = 'stopped';
                $trans->save();

                $dcn_history = new DcnTransactionHistory;
                $dcn_history->transaction_id = $trans->id;
                $dcn_history->status = $trans->status;
                $dcn_history->history_message = 'Stopped after the user is deleted';
                $dcn_history->save();
            }
        }
        $this->removeFromSendgridSubscribes();
        $this->removeTokens();
        $this->logoutActions();
    }

    public function removeFromSendgridSubscribes() {

        //get sendgrid user id
        $sg = new \SendGrid(env('SENDGRID_PASSWORD'));

        $query_params = new \stdClass();
        $query_params->email = $this->email;

        $response = $sg->client->contactdb()->recipients()->search()->get(null, $query_params);

        if(isset(json_decode($response->body())->recipients[0])) {
            $recipient_id = json_decode($response->body())->recipients[0]->id;
        } else {
            $recipient_id = null;
        }

        if(!empty($recipient_id)) {
            // delete from list
            $request_body = [$recipient_id];
            $response = $sg->client->contactdb()->recipients()->delete($request_body);
        }
    }

    public function restoreActions() {

        $id = $this->id;

        if($this->is_dentist) {

            $teams = UserTeam::where(function($query) use ($id) {
                $query->where( 'dentist_id', $id)->orWhere('user_id', $id);
            })->get();

            if ($teams->isNotEmpty()) {
               foreach ($teams as $team) {
                   $team->restore();
               }
            }

            $claims = DentistClaim::where('dentist_id', $id)->get();

            if($claims->isNotEmpty()) {
                foreach ($claims as $c) {
                    $c->restore();
                }
            }
        }

        $transactions = DcnTransaction::where('user_id', $this->id)->whereIn('status', ['stopped', 'first'])->get();

        if ($transactions->isNotEmpty()) {
            foreach ($transactions as $trans) {
                $trans->status = 'new';
                $trans->save();

                $dcn_history = new DcnTransactionHistory;
                $dcn_history->transaction_id = $trans->id;
                $dcn_history->status = $trans->status;
                $dcn_history->history_message = 'Status new after the user is restored';
                $dcn_history->save();
            }
        }

        if(!$this->is_dentist) {
            if($this->patient_status == 'suspicious_badip') {
                $this->ip_protected = true;
                $this->save();
            }

            if($this->patient_status == 'suspicious_badip' || $this->patient_status == 'suspicious_admin') {
                $this->sendTemplate(112, null, 'dentacoin');
            }

            if($this->patient_status == 'deleted') {
                $this->sendTemplate(111, null, 'dentacoin');
            }

            if($this->history->isNotEmpty()) {
                $this->patient_status = 'new_verified';
            } else {
                $this->patient_status = 'new_not_verified';
            }

            if(!empty($this->newBanAppeal)) {
                $this->newBanAppeal->status = 'approved';
                $this->newBanAppeal->save();
            }

            $this->save();


            $sg = new \SendGrid(env('SENDGRID_PASSWORD'));

            $user_info = new \stdClass();
            $user_info->email = $this->email;
            $user_info->first_name = explode(' ', $this->name)[0];
            $user_info->last_name = isset(explode(' ', $this->name)[1]) ? explode(' ', $this->name)[1] : '';
            $user_info->type = 'patient';

            $request_body = [
                $user_info
            ];

            $response = $sg->client->contactdb()->recipients()->post($request_body);
            $recipient_id = isset(json_decode($response->body())->persisted_recipients) ? json_decode($response->body())->persisted_recipients[0] : null;

            //add to list
            if($recipient_id) {

                $sg = new \SendGrid(env('SENDGRID_PASSWORD'));
                $list_id = config('email-preferences')['product_news']['vox']['sendgrid_list_id'];
                $response = $sg->client->contactdb()->lists()->_($list_id)->recipients()->_($recipient_id)->post();
            }

        } else {

            $sg = new \SendGrid(env('SENDGRID_PASSWORD'));

            $user_info = new \stdClass();
            $user_info->email = $this->email;
            $user_info->title = $this->title ? config('titles')[$this->title] : '';
            $user_info->first_name = explode(' ', $this->name)[0];
            $user_info->last_name = isset(explode(' ', $this->name)[1]) ? explode(' ', $this->name)[1] : '';
            $user_info->type = 'dentist';
            $user_info->partner = $this->is_partner ? 'yes' : 'no';

            $request_body = [
                $user_info
            ];

            $response = $sg->client->contactdb()->recipients()->post($request_body);
            $recipient_id = isset(json_decode($response->body())->persisted_recipients) ? json_decode($response->body())->persisted_recipients[0] : null;

            //add to list
            if($recipient_id) {

                $sg = new \SendGrid(env('SENDGRID_PASSWORD'));
                $list_id = config('email-preferences')['product_news']['dentacoin']['sendgrid_list_id'];
                $response = $sg->client->contactdb()->lists()->_($list_id)->recipients()->_($recipient_id)->post();
            }
        }
    }

    public function sendgridSubscribeToGroup($platform) {

        $sg = new \SendGrid(env('SENDGRID_PASSWORD'));
        $group_id = config('email-preferences')['product_news'][$platform]['sendgrid_group_id'];
        $email = $this->email;

        $response = $sg->client->asm()->groups()->_($group_id)->suppressions()->_($email)->delete();
    }

    public function canInvite($platform) {
        return ($this->status=='approved' || $this->status=='test' || $this->status=='added_by_clinic_claimed' || $this->status=='added_by_dentist_claimed') && !$this->loggedFromBadIp();
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

        if ($img->height() > $img->width()) {
            $img->heighten(400);
        } else {
            $img->widen(400);
        }
        $img->resizeCanvas(400, 400);

        // $img->heighten(400, function ($constraint) {
        //     $constraint->upsize();
        // });
        $img->save($to_thumb);
        
        return [ self::getTempImageUrl($name, true), self::getTempImageUrl($name), $name ];
    }

    public static function isGasExpensive() {

        // $url = file_get_contents('https://dentacoin.net/gas-price');

        // $gas = json_decode($url, true);

        // if(intVal($gas['gasPrice']) > intVal($gas['treshold']) ) {
        //     return true;
        // } else {
        //     return false;
        // }

        $gas = GasPrice::find(1);

        if($gas->gas_price > $gas->max_gas_price) {
            return true;
        }

        return false;
    }

    public static function isApprovalGasExpensive() {

        // $url = file_get_contents('https://dentacoin.net/gas-price');

        // $gas = json_decode($url, true);

        // if(intVal($gas['gasPrice']) > intVal($gas['treshold']) ) {
        //     return true;
        // } else {
        //     return false;
        // }

        $gas = GasPrice::find(1);

        if($gas->gas_price > $gas->max_gas_price_approval) {
            return true;
        }

        return false;
    }

    public function loggedFromBadIp() {

        $ip = self::getRealIp();

        $is_whitelist_ip = WhitelistIp::where('ip', 'like', $ip)->first();

        if (!empty($is_whitelist_ip)) {
            return false;
        } else {
            $users_with_same_ip = UserLogin::where('ip', 'like', $ip)->where('user_id', '!=', $this->id)->groupBy('user_id')->get()->count();

            if ($users_with_same_ip >=2 && !$this->ip_protected && !$this->allow_withdraw && !$this->is_dentist && $this::getRealIp() != '213.91.254.194' ) {

                $similar_users = UserLogin::where('ip', 'like', $ip)->where('user_id', '!=', $this->id)->groupBy('user_id')->get();

                foreach ($similar_users as $su) {
                    
                    $s_user = self::find($su->user_id);

                    if(!empty($s_user) && !$s_user->ip_protected && !$s_user->allow_withdraw && !$s_user->is_dentist && $s_user->patient_status != 'suspicious_badip') {
                        $s_user->patient_status = 'suspicious_badip';
                        $s_user->save();
                        
                        $s_user->sendTemplate(110, null, 'dentacoin');
                        $s_user->removeTokens();
                        $s_user->logoutActions();
                    }
                }

                $this->patient_status = 'suspicious_badip';
                $this->save();
                
                $this->sendTemplate(110, null, 'dentacoin');
                $this->removeTokens();
                $this->logoutActions();

                return true;
            } else {
                return false;
            }
        }
    }

    public static function lastLoginUserId() {
        return UserLogin::where('ip', 'like', self::getRealIp())->orderBy('id', 'DESC')->first();
    }

    public function checkForWelcomeCompletion() {

        $first = Vox::where('type', 'home')->first();
        $has_test = !empty($_COOKIE['first_test']) ? json_decode($_COOKIE['first_test'], true) : null;
        if( $has_test ) {

            $first_question_ids = $first->questions->pluck('id')->toArray();

            if(!$this->madeTest($first->id)) {
                foreach ($has_test as $q_id => $a_id) {

                    // if($q_id == 'location') {
                    //     $country_id = $a_id;
                    //     $this->country_id = $country_id;
                    //     $this->save();
                    // } else 
                    if($q_id == 'birthyear') {
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
                        $answer->is_completed = 1;
                        $answer->is_skipped = 0;
                        $answer->save();
                    }
                }
                $reward = new DcnReward;
                $reward->user_id = $this->id;
                $reward->reference_id = $first->id;
                $reward->type = 'survey';
                $reward->reward = $first->getRewardTotal();
                $reward->platform = 'vox';

                $userAgent = $_SERVER['HTTP_USER_AGENT']; // change this to the useragent you want to parse
                $dd = new DeviceDetector($userAgent);
                $dd->parse();

                if ($dd->isBot()) {
                    // handle bots,spiders,crawlers,...
                    $reward->device = $dd->getBot();
                } else {
                    $reward->device = $dd->getDeviceName();
                    $reward->brand = $dd->getBrandName();
                    $reward->model = $dd->getModel();
                    $reward->os = in_array('name', $dd->getOs()) ? $dd->getOs()['name'] : '';
                }

                $reward->save();
            }

            Cookie::queue(Cookie::forget('first_test'));
            //setcookie('first_test', null, time()-600, '/');

        }
    }

    public static function checkForBlockedIP() {
        include_once("/var/www/html/blocked/blockscript/detector.php");
        return !empty($_SERVER['blockscript_blocked']) && $_SERVER['blockscript_blocked']=='YES';
    }

    public static function checkBlocks($name, $email) {
        foreach (Blacklist::get() as $b) {
            if ($b['field'] == 'name') {
                if (fnmatch(mb_strtolower($b['pattern']), mb_strtolower($name)) == true) {

                    $new_blacklist_block = new BlacklistBlock;
                    $new_blacklist_block->blacklist_id = $b['id'];
                    $new_blacklist_block->name = $name;
                    $new_blacklist_block->email = $email;
                    $new_blacklist_block->save();

                    return trans('front.page.login.blocked-name');
                }
            } else {
                if (fnmatch(mb_strtolower($b['pattern']), mb_strtolower($email)) == true) {

                    $new_blacklist_block = new BlacklistBlock;
                    $new_blacklist_block->blacklist_id = $b['id'];
                    $new_blacklist_block->name = $name;
                    $new_blacklist_block->email = $email;
                    $new_blacklist_block->save();
                    
                    return trans('front.page.login.blocked-email');
                }
            }
        }

        return null;
    }

    public function getWorkHoursText() {
        $dows = [
            1=> trans('trp.page.index.monday'),
            trans('trp.page.index.tuesday'),
            trans('trp.page.index.wednesday'),
            trans('trp.page.index.thursday'),
            trans('trp.page.index.friday'),
            trans('trp.page.index.saturday'),
            trans('trp.page.index.sunday'),
        ];
        $opens = null;

        if( $this->work_hours && $this->country) {
            $work_h = is_array($this->work_hours) ? $this->work_hours : json_decode($this->work_hours, true);

            $identifiers = \DateTimeZone::listIdentifiers(\DateTimeZone::PER_COUNTRY, mb_strtoupper($this->country->code));
            if(!empty($identifiers)) {
                $tz = current($identifiers);
                $date = Carbon::now($tz);
                $dow = $date->dayOfWeekIso;
                if( isset( $work_h[$dow] ) ) {
                    $oa = explode(':', $work_h[$dow][0]);
                    $ca = explode(':', $work_h[$dow][1]);
                    if(!empty($oa[0]) && is_numeric($oa[0]) && !empty($ca[0]) && is_numeric($ca[0]) && !empty($oa[1]) && is_numeric($oa[1]) && !empty($ca[1]) && is_numeric($ca[1])) {
                        $open = Carbon::createFromTime( intval($oa[0]), intval($oa[1]), 0, $tz );
                        $close = Carbon::createFromTime( intval($ca[0]), intval($ca[1]), 0, $tz );
                        if( $date->lessThan($close) && $date->greaterThan($open) ) {
                            $opens = '<span class="green-text">'.trans('trp.page.index.open-now').'</span>&nbsp;<span>('.$work_h[$dow][0].' - '.$work_h[$dow][1].')</span>';
                        }
                    }
                } 

                if( empty($opens) ) {
                    while($dow<=7) {
                        $dow++;
                        if( isset( $work_h[$dow] ) ) {
                            $opens = '<span>'.trans('trp.page.index.opens-on', ['day'=>$dows[$dow], 'hours'=>$work_h[$dow][0]]).'</span>';
                            break;
                        }
                    }
                    if(empty($opens)) {
                        $wh = $work_h;
                        reset($wh);
                        $dow = key( $wh );
                        $opens = '<span>'.trans('trp.page.index.opens-on', ['day'=>$dows[$dow], 'hours'=>$wh[$dow][0]]).'</span>';
                    }
                }
            }
        }

        return $opens;
    }

    public function getWorkplaceText($isme=false) {
        $ret = [];
        if($this->my_workplace->isNotEmpty()) {
            foreach($this->my_workplace as $workplace) {
                if( $workplace->approved ) {
                    $ret[] = '<a href="'.$workplace->clinic->getLink().'">'.$workplace->clinic->getNames().'</a>';
                } else {
                    if( $isme ) {
                        $ret[] = '<a href="'.$workplace->clinic->getLink().'">'.$workplace->clinic->getNames().' ('.trans('trp.page.index.pending').')</a>';
                    }
                }
            }
        }

        return implode(', ', $ret);
    }

    public function getSocialCoverPath() {
        $folder = storage_path().'/app/public/avatars/'.($this->id%100);
        return $folder.'/'.$this->id.'-cover.jpg';
    }
    public function getSocialCover() {
        if(!$this->hasimage_social) {
            $this->generateSocialCover();
        }
        return url('/storage/avatars/'.($this->id%100).'/'.$this->id.'-cover.jpg').'?rev='.$this->updated_at->timestamp;
    }

    public function generateSocialCover() {
        $path = $this->getSocialCoverPath();

        $img = Image::canvas(1200, 628, '#fff');

        if ($this->hasimage) {
            $img->insert( public_path().'/img-trp/cover-dentist-new.png');

            $avatar = Image::make( $this->getImagePath(true) );
            $avatar->resize(366, 365);
            $avatar_mask = Image::canvas(366, 365, '#fff');
            $avatar_mask->insert( $avatar, 'top-left', 0, 0 );
            $avatar_mask->insert( public_path().'/img-trp/cover-dentist-mask-new.png' , 'top-left', 0, 0 );
            $img->insert($avatar_mask , 'top-left', 80, 162 );
        } else {
            $img->insert( public_path().'/img-trp/cover-dentist-new-no-avatar.png');
        }


        if($this->avg_rating) {
            $reviews = '('.intval($this->ratings).' reviews)';
            $img->text($reviews, 860, 470, function($font) {
                $font->file(public_path().'/fonts/Calibri-Light.ttf');
                $font->size(30);
                $font->color('#555555');
                $font->align('left');
                $font->valign('top');
            });

            $step = 67;
            $startX = 518;
            for($i=1;$i<=5;$i++) {
                $img->insert( public_path().'/img-trp/cover-star-review-new-gray.png' , 'top-left', $startX, 452 );
                $startX += $step;
            }

            $step = 67;
            $startX = 518;
            for($i=1;$i<=$this->avg_rating;$i++) {
                $img->insert( public_path().'/img-trp/cover-star-review-new.png' , 'top-left', $startX, 452 );
                $startX += $step;
            }

            $rest = ( $this->avg_rating - floor( $this->avg_rating ) );
            if($rest) {
                $halfstar = Image::canvas(ceil(60*$rest), 61, '#fff');
                $halfstar->insert( public_path().'/img-trp/cover-star-review-new.png', 'top-left', 0, 0 );
                $img->insert($halfstar , 'top-left', $startX, 452 );
            }

            $above_pushing = 0;
        } else {
            $above_pushing = 25;
        }


        $names = $this->getNames();
        $names = wordwrap($names, 30); 
        $lines = count(explode("\n", $names));
        $top = 205 + $above_pushing;
        if($lines == 2) {
            $top -= 60;
        }
        $names_size = 51;
        $img->text($names, 515, $top, function($font) use ($names_size) {
            $font->file(public_path().'/fonts/Calibri-Bold.ttf');
            $font->size($names_size);
            $font->color('#000000');
            $font->align('left');
            $font->valign('top');
        });

        $img->insert( public_path().'/img-trp/cover-pin.png' , 'top-left', 515, 365 + $above_pushing );


        $type = ($this->is_partner ? 'Partner ' : '').($this->is_clinic ? 'Clinic' : 'Dentist');
        $type_left = $this->is_partner ? 575 : 515;
        if($this->is_partner) {
            $avatar = public_path().'/img-trp/cover-partner.png';
            $img->insert($avatar , 'top-left', 515, 283 + $above_pushing );            
        }

        $img->text($type, $type_left, 283 + $above_pushing, function($font) {
            $font->file(public_path().'/fonts/Calibri.ttf');
            $font->size(46);
            $font->color('#555555');
            $font->align('left');
            $font->valign('top');
        });

        $location = ($this->city_name ? $this->city_name.', ' : '').($this->state_name ? $this->state_name.', ' : '').$this->country->name;
        $location = wordwrap($location, 50); 
        $top2 = count(explode("\n", $location));
        $top2 = 365 + $above_pushing;
        $lines = count(explode("\n", $location));
        if($lines == 2) {
            $top2 -= 20;
        }
        $img->text($location, 555, $top2, function($font) {
            $font->file(public_path().'/fonts/Calibri.ttf');
            $font->size(30);
            $font->color('#000000');
            $font->align('left');
            $font->valign('top');
        });


        $img->save($path);
        $this->hasimage_social = true;
        $this->save();
    }

    public function refreshReviews() {
        foreach($this->reviews_out_standard() as $r) {
            $r->hasimage_social = false;
            $r->save();
        }
        foreach($this->reviews_out_video() as $r) {
            $r->hasimage_social = false;
            $r->save();
        }
    }

    public static function validateLatin($string) {
        $result = false;
     
        if (preg_match("/^[\w\d\s\+\'\&.,-]*$/", $string)) {
            $result = true;
        }
     
        return $result;
    }

    // public function getSlugAttribute($value) {


    //     if(empty($this->attributes['slug'])) {
    //         $this->attributes['slug'] = $this->makeSlug();
    //         $this->save();
    //     }
    //     return $this->attributes['slug'];
    // }

    public function getAcceptedPaymentAttribute($value) {
        if(!empty($value)) {
            return explode(',', $value);            
        }
        return [];
    }
    
    public function setAcceptedPaymentAttribute($value) {
        $this->attributes['accepted_payment'] = null;
        if(!empty($value) && is_array($value)) {
            $this->attributes['accepted_payment'] = implode(',', $value);            
        }
    }

    public function parseAcceptedPayment($ap) {
        
        $arr = [];
        foreach ($ap as $v) {
            $arr[$v] = trans('trp.accepted-payments.'.$v);
        }

        return implode(', ', $arr);
    }

    //Handles CloudFlare
    public static function getRealIp() {
        return !empty($_SERVER["HTTP_CF_CONNECTING_IP"]) ? $_SERVER["HTTP_CF_CONNECTING_IP"] : Request::ip();
    }

    public function removeTokens() {

        if($this->tokens->isNotEmpty()) {
            $this->tokens->each(function($token, $key) {
                $token->delete();
            });
        }

        $this->is_logout = true;
        $this->save();
    }

    public function logoutActions() {

        session([
            'mark-login' => false,
            'vox-welcome' => null,
            'login-logged-out' => session('logged_user')['token'] ?? null,
        ]);
    }

    public function getMontlyRating($month=0) {

        $id = $this->id;

        $to_month = Carbon::now()->modify('-'.$month.' months');
        $from_month = Carbon::now()->modify('-'.($month+1).' months');

        $prev_reviews = Review::where(function($query) use ($id) {
            $query->where( 'dentist_id', $id)->orWhere('clinic_id', $id);
        })
        ->where('created_at', '>=', $from_month)
        ->where('created_at', '<=', $to_month)
        ->get();

        return $prev_reviews;        
    }

    public function getMonthlyInvites($month=0) {

        $to_month = Carbon::now()->modify('-'.$month.' months');
        $from_month = Carbon::now()->modify('-'.($month+1).' months');

        $prev_invites = UserInvite::where( 'user_id', $this->id)
        ->where('created_at', '>=', $from_month)
        ->where('created_at', '<=', $to_month)
        ->get();

        return $prev_invites;        
    }

    public function convertForResponse() {
        $arr = $this->toArray();
        $arr['avatar_url'] = $this->getImageUrl();
        $arr['thumbnail_url'] = $this->getImageUrl(true);
        $arr['trp_public_profile_link'] = $this->is_dentist && ($this->status=='approved' || $this->status=='test' || $this->status=='added_approved' || $this->status=='admin_imported' || $this->status=='added_by_clinic_claimed' || $this->status=='added_by_clinic_unclaimed' || $this->status=='added_by_dentist_unclaimed' || $this->status=='added_by_dentist_claimed') ? $this->getLink() : null;

        return $arr;
    }

    public static function encrypt($raw_text) {
        $length = openssl_cipher_iv_length(env('CRYPTO_METHOD'));
        $iv = openssl_random_pseudo_bytes($length);
        $encrypted = openssl_encrypt($raw_text, env('CRYPTO_METHOD'), env('CRYPTO_KEY'), OPENSSL_RAW_DATA, $iv);
        //here we append the $iv to the encrypted, because we will need it for the decryption
        $encrypted_with_iv = base64_encode($encrypted) . '|' . base64_encode($iv);
        return $encrypted_with_iv;
    }

    public static function decrypt($encrypted_text) {
        $arr = explode('|', $encrypted_text);
        if (count($arr)!=2) {
            return null;
        }
        $data = $arr[0];
        $iv = $arr[1];
        $iv = base64_decode($iv);

        try {
            $raw_text = openssl_decrypt($data, env('CRYPTO_METHOD'), env('CRYPTO_KEY'), 0, $iv);
        } catch (\Exception $e) {
            $raw_text = false;
        }

        return $raw_text;
    }

    public static function getAllVoxes() {
        return Vox::with('translations')->with('categories.category')->with('categories.category.translations');
    }

    public function voxesTargeting() {
        $voxlist = self::getAllVoxes();

        $marital_status = $this->marital_status;
        $children = $this->children;
        $household_children = $this->household_children;
        $education = $this->education;
        $employment = $this->employment;
        $job = $this->job;
        $job_title = $this->job_title;
        $income = $this->income;
        $country_id = $this->country_id;
        $gender = $this->gender;
        $dentists_patients = $this->is_dentist ? 'dentists' : 'patients';
        
        $age = !empty($this->birthyear) ? date('Y') - $this->birthyear : null;

        if (!empty($age)) {
            if ($age <= 24) {
                $age = 24;
            } else if($age <= 34) {
                $age = 34;
            } else if($age <= 44) {
                $age = 44;
            } else if($age <= 54) {
                $age = 54;
            } else if($age <= 64) {
                $age = 64;
            } else if($age <= 74) {
                $age = 74;
            } else if($age > 74) {
                $age = 'more';
            }            
        }

        if (!empty($marital_status)) {
            $voxlist = $voxlist->where(function($query) use ($marital_status) {
                $query->whereNull('marital_status')
                ->orWhereRaw('FIND_IN_SET("'.$marital_status.'", `marital_status`)');
            });
        }

        if (!empty($children)) {
            $voxlist = $voxlist->where(function($query) use ($children) {
                $query->whereNull('children')
                ->orWhereRaw('FIND_IN_SET("'.$children.'", `children`)');
            });
        }

        if (!empty($household_children)) {
            $voxlist = $voxlist->where(function($query) use ($household_children) {
                $query->whereNull('household_children')
                ->orWhereRaw('FIND_IN_SET("'.$household_children.'", `household_children`)');
            });
        }

        if (!empty($education)) {
            $voxlist = $voxlist->where(function($query) use ($education) {
                $query->whereNull('education')
                ->orWhereRaw('FIND_IN_SET("'.$education.'", `education`)');
            });
        }

        if (!empty($employment)) {
            $voxlist = $voxlist->where(function($query) use ($employment) {
                $query->whereNull('employment')
                ->orWhereRaw('FIND_IN_SET("'.$employment.'", `employment`)');
            });
        }

        if (!empty($job)) {
            $voxlist = $voxlist->where(function($query) use ($job) {
                $query->whereNull('job')
                ->orWhereRaw('FIND_IN_SET("'.$job.'", `job`)');
            });
        }

        if (!empty($job_title)) {
            $voxlist = $voxlist->where(function($query) use ($job_title) {
                $query->whereNull('job_title')
                ->orWhereRaw('FIND_IN_SET("'.$job_title.'", `job_title`)');
            });
        }

        if (!empty($income)) {
            $voxlist = $voxlist->where(function($query) use ($income) {
                $query->whereNull('income')
                ->orWhereRaw('FIND_IN_SET("'.$income.'", `income`)');
            });
        }

        if (!empty($country_id)) {
            $voxlist = $voxlist->where(function($query) use ($country_id) {
                $query->whereNull('countries_ids')
                ->orWhereRaw('not JSON_CONTAINS( `countries_ids`, \'"'.$country_id.'"\')');
            });
        }

        if (!empty($gender)) {
            $voxlist = $voxlist->where(function($query) use ($gender) {
                $query->whereNull('gender')
                ->orWhereRaw('FIND_IN_SET("'.$gender.'", `gender`)');
            });
        }

        if (!empty($dentists_patients)) {
            $voxlist = $voxlist->where(function($query) use ($dentists_patients) {
                $query->whereNull('dentists_patients')
                ->orWhereRaw('FIND_IN_SET("'.$dentists_patients.'", `dentists_patients`)');
            });
        }

        if (!empty($age)) {
            $voxlist = $voxlist->where(function($query) use ($age) {
                $query->whereNull('age')
                ->orWhereRaw('FIND_IN_SET("'.$age.'", `age`)');
            });
        }

        return $voxlist;

    }

    public function isVoxRestricted($vox) {

        $dentists_patients = $this->is_dentist ? 'dentists' : 'patients';
        $age = !empty($this->birthyear) ? date('Y') - $this->birthyear : null;

        if (!empty($age)) {            
            if ($age <= 24) {
                $age = 24;
            } else if($age <= 34) {
                $age = 34;
            } else if($age <= 44) {
                $age = 44;
            } else if($age <= 54) {
                $age = 54;
            } else if($age <= 64) {
                $age = 64;
            } else if($age <= 74) {
                $age = 74;
            } else if($age > 74) {
                $age = 'more';
            }
        }

        //set fields
        $arr = [
            'marital_status',
            'children',
            'household_children',
            'education',
            'employment',
            'job',
            'job_title',
            'income',
            'gender',
        ];

        $is_restricted = false;

        foreach ($arr as $val) {
            if (!empty($this->$val)) {                
                if (!empty($vox->$val) && !in_array($this->$val, $vox->$val)) {
                    $is_restricted = true;
                }
            }
        }

        if (!empty($this->birthyear)) {
            
            if (!empty($vox->age) && !in_array($age, $vox->age)) {
                $is_restricted = true;
            }
        }

        if (!empty($vox->dentists_patients) && !in_array($dentists_patients, $vox->dentists_patients)) {
            $is_restricted = true;
        }

        if (!empty($this->country_id) && empty($this->vip_access)) {
            if(!empty($vox->exclude_countries_ids) && in_array($this->country_id, $vox->exclude_countries_ids) ) {
            } else {

                if (!empty($vox->countries_ids) && !in_array($this->country_id, $vox->countries_ids)) {
                    $is_restricted = true;
                }
            }

            // if (!empty($vox->country_percentage) && !empty($vox->users_percentage) && array_key_exists($this->country_id, $vox->users_percentage) && $vox->users_percentage[$this->country_id] > $vox->country_percentage) {
            //     $is_restricted = true;
            // }
        }

        return $is_restricted;
    }

    public function getLastTopDentistBadge() {

        $text = '';
        if(!empty($this->top_dentist_month)) {

            $time = 0;
            foreach (explode(';', $this->top_dentist_month) as $badge) {
                if($time < strtotime('01-'.explode(':', $badge)[1].'-'.explode(':', $badge)[0])) {
                    $time = strtotime('01-'.explode(':', $badge)[1].'-'.explode(':', $badge)[0]);
                    $text = trans('trp.months.'.config('months')[explode(':', $badge)[1]]).' '.explode(':', $badge)[0];
                }
            }
        }

        return $text;
    }



    public function setServiceInfoAttribute($value) {
        $this->attributes['service_info'] = null;
        if(!empty($value) && is_array($value)) {
            $this->attributes['service_info'] = implode(',', $value);            
        }
    }
    
    public function getServiceInfoAttribute($value) {
        if(!empty($value)) {
            return explode(',', $value);            
        }
        return [];
    }

    public function setWebsiteNotificationsAttribute($value) {
        $this->attributes['website_notifications'] = null;
        if(!empty($value) && is_array($value)) {
            $this->attributes['website_notifications'] = implode(',', $value);            
        }
    }
    
    public function getWebsiteNotificationsAttribute($value) {
        if(!empty($value)) {
            return explode(',', $value);            
        }
        return [];
    }

    public function setProductNewsAttribute($value) {
        $this->attributes['product_news'] = null;
        if(!empty($value) && is_array($value)) {
            $this->attributes['product_news'] = implode(',', $value);            
        }
    }
    
    public function getProductNewsAttribute($value) {
        if(!empty($value)) {
            return explode(',', $value);            
        }
        return [];
    }

    public function setBlogAttribute($value) {
        $this->attributes['blog'] = null;
        if(!empty($value) && is_array($value)) {
            $this->attributes['blog'] = implode(',', $value);            
        }
    }
    
    public function getBlogAttribute($value) {
        if(!empty($value)) {
            return explode(',', $value);            
        }
        return [];
    }

    public static function isUnsubscribedAnonymous($template_id, $platform, $email) {
        $unsubscribed = false;
        $anonymous_user = AnonymousUser::where('email', 'LIKE', $email)->first();

        if(!empty($anonymous_user)) {
            $cat = EmailTemplate::find($template_id)->subscribe_category;

            if(!empty($cat)) {
                $unsub_cat = 'unsubscribed_'.$cat;

                if(is_array($anonymous_user->$cat) && in_array($platform, $anonymous_user->$cat)) {
                    $unsubscribed = true;
                }
            }
        }

        return $unsubscribed;
    }

    public function getVoxLevelName() {

        $all_surveys_count = Vox::where('type', 'normal')->count();
        $all_done_surveys_count = count($this->filledVoxes());

        $percentage = ceil($all_done_surveys_count / $all_surveys_count * 100);

        foreach (config('vox-levels-names') as $name => $value) {
            if($percentage <= $value) {
                $level_name = $name;
                break;
            }
        }

        return $level_name;
    }

    public function getRewardForSurvey($vox_id) {
        return DcnReward::where('user_id', $this->id)->where('type', 'survey')->where('platform', 'vox')->where('reference_id', $vox_id)->first();
    }


    public function notRestrictedVoxesList($voxList) {

        if(!empty($this->country_id) && empty($this->vip_access)) {
            $user = $this;
            $restricted_voxes = $voxList->filter(function($vox) use ($user) {
                return ((!empty($vox->exclude_countries_ids) && !in_array($user->country_id, $vox->exclude_countries_ids)) || empty($vox->exclude_countries_ids)) && !empty($vox->country_percentage) && !empty($vox->users_percentage) && array_key_exists($user->country_id, $vox->users_percentage) && $vox->users_percentage[$user->country_id] >= $vox->country_percentage;
            });

            $arr = [];

            if($restricted_voxes->count()) {
                foreach ($restricted_voxes as $vl) {
                    $has_started_the_survey = VoxAnswer::where('vox_id', $vl->id)->where('user_id', $this->id)->first();

                    if(empty($has_started_the_survey)) {
                        $arr[] = $vl->id;
                    }
                }

                if (!empty($arr)) {
                    foreach ($arr as $ar) {
                        $voxList = $voxList->filter(function($item) use ($ar) {
                            return $item->id != $ar;
                        });
                    }
                }
            }
        }

        return $voxList;
    }

    public function banAppealInfo() {

        $info = '';

        $duplicated_names = collect();
        if( !empty($this->name)) {
            $duplicated_names = self::where('id', '!=', $this->id)->where('name', 'LIKE', $this->name)->withTrashed()->get();
            if($duplicated_names->isNotEmpty()) {
                $info .= '<p>Duplicated names:</p>';
                $i=0;
                foreach($duplicated_names as $dn) {
                    $i++;
                    $info .= '<p>'.$i.'. <a href="'.url('cms/users/edit/'.$dn->id).'">'.$dn->name.' '.($dn->is_dentist ? '('.config('user-statuses')[$dn->status].($dn->deleted_at ? ', Deleted' : '').')' : '' ).'</a></p><div class="bottom-border"> </div>';
                }
            }
        }

        $duplicated_kyc = collect();
        if( !empty($this->civic_kyc_hash)) {
            $duplicated_kyc = self::where('id', '!=', $this->id)->where('civic_kyc_hash', $this->civic_kyc_hash)->withTrashed()->get();
            if($duplicated_kyc->isNotEmpty()) {
                $info .= '<p>Duplicated KYC:</p>';
                $i=0;
                foreach($duplicated_kyc as $dn) {
                    $i++;
                    $info .= '<p>'.$i.'. <a href="'.url('cms/users/edit/'.$dn->id).'">'.$dn->name.' '.($dn->is_dentist ? '('.config('user-statuses')[$dn->status].($dn->deleted_at ? ', Deleted' : '').')' : '' ).'</a></p><div class="bottom-border"> </div>';
                }
            }
        }

        if( $this->wallet_addresses->isNotEmpty()) {
            foreach($this->wallet_addresses as $wa) {
                $duplicated_wallets = WalletAddress::where('user_id', '!=', $this->id)->where('dcn_address', 'LIKE', $wa->dcn_address)->get();

                if($duplicated_wallets->isNotEmpty()) {
                    $info .= '<p>Duplicated Wallets:</p>';
                    $i=0;
                    foreach($duplicated_wallets as $dw) {
                        $i++;

                        if(!empty(User::withTrashed()->find($dw->user_id))) {
                            $info .= '<p>'.$i.'. <a href="'.url('cms/users/edit/'.$dw->user_id).'">'.User::withTrashed()->find($dw->user_id)->name.'</a></p>';
                        }
                    }
                }
            }
        }

        if( $this->logins->pluck('ip')->toArray() ) {
            $dublicated_ips = UserLogin::where('user_id', '!=', $this->id)->whereIn('ip', $this->logins->pluck('ip')->toArray() )->groupBy('user_id')->get();
            if($dublicated_ips->isNotEmpty()) {
                $info .= '<p>Duplicated IPs:</p>';
                $i=0;
                foreach($dublicated_ips as $di) {
                    $i++;

                    if(!empty(User::withTrashed()->find($di->user_id))) {
                        $info .= '<p>'.$i.'. <a href="'.url('cms/users/edit/'.$di->user_id).'">'.User::withTrashed()->find($di->user_id)->name.'</a></p>';
                    }
                }
            }
        }

        $permanent_vox_ban = UserBan::where('user_id', $this->id)->where('domain', 'vox')->whereNull('expires')->first();
        if(!empty($permanent_vox_ban)) {
            $info .= '<p style="color:red">Permenant Vox ban</p>';
        }
        // if(!empty($this->permanentVoxBan())) {
        //     $info .= '<p style="color:red">Permenant Vox ban</p>';
        // }

        return $info !== '' ? $info : 'Nothing wrong with this user';
    }
}