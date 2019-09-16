<?php

namespace App\Models;

use DeviceDetector\DeviceDetector;
use DeviceDetector\Parser\Device\DeviceParserAbstract;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Support\Str;
use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

use App\Models\Email;
use App\Models\Reward;
use App\Models\DcnReward;
use App\Models\DcnCashout;
use App\Models\VoxCrossCheck;
use App\Models\UserBan;
use App\Models\UserAsk;
use App\Models\UserTeam;
use App\Models\DcnTransaction;
use App\Models\UserLogin;
use App\Models\Blacklist;
use App\Models\BlacklistBlock;
use App\Models\DentistPageview;
use Carbon\Carbon;

use Request;
use Image;
use Auth;
use Mail;
use App;

use \SendGrid\Mail\From as From;
use \SendGrid\Mail\To as To;
use \SendGrid\Mail\Subject as Subject;
use \SendGrid\Mail\PlainTextContent as PlainTextContent;
use \SendGrid\Mail\HtmlContent as HtmlContent;
use \SendGrid\Mail\Mail as SendGridMail;


class User extends Model implements AuthenticatableContract, CanResetPasswordContract
{
    use Notifiable, HasApiTokens, SoftDeletes, Authenticatable, CanResetPassword;

    protected $fillable = [
    	'email',
        'email_public',
        'email_clean',
    	'password', 
        'is_dentist',
        'is_partner',
        'title',
        'slug',
        'name',
        'name_alternative',
        'description',
        'short_description',
        'zip',
        'city_name',
        'state_name',
        'state_slug',
        'address',
        'phone',
        'website',
        'accepted_payment',
        'status',
        'ownership',
        'socials',
        'work_hours',
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
        'hasimage_social',
        'is_approved',
        'vox_active',
        'fb_id',
        'civic_id',
        'civic_kyc',
        'civic_kyc_hash',
        'platform',
        'unsubscribe',
        'gdpr_privacy',
        'self_deleted',
        'allow_withdraw',
        'grace_end',
        'grace_notified',
        'dcn_address',
        'lat',
        'lon',
    ];
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
        'grace_end',
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
    public function cross_check() {
        return $this->hasMany('App\Models\VoxCrossCheck', 'user_id', 'id');
    }
    public function invitor() {
        return $this->hasOne('App\Models\User', 'id', 'invited_by');
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
        return $this->hasMany('App\Models\Review', 'dentist_id', 'id')->where('status', 'accepted')->orderBy('id', 'desc');
    }
    public function reviews_in_clinic() {
        return $this->hasMany('App\Models\Review', 'clinic_id', 'id')->where('status', 'accepted')->orderBy('id', 'desc');
    }
    public function reviews_in() {
        return $this->reviews_in_dentist->merge($this->reviews_in_clinic)->sortBy(function ($review, $key) {

            if($review->verified) {
                return -1;
            } else {
                return 99999;
            }
        });
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
    public function invites() {
        return $this->hasMany('App\Models\UserInvite', 'user_id', 'id')->orderBy('created_at', 'DESC');
    }
    public function claims() {
        return $this->hasMany('App\Models\DentistClaim', 'dentist_id', 'id')->orderBy('created_at', 'DESC');
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

    public function isBanned($domain) {
        foreach ($this->bans as $ban) {
            if($ban->domain==$domain && ($ban->expires===null || Carbon::now()->lt( $ban->expires ) ) ) {
                return $ban;
            }
        }

        return false;
    }

    public function getStrengthPlatform($platform) {

        $ret = [];

        if ($platform == 'trp') {

            if($this->is_dentist) {

                $array_number_shuffle = [
                    'important' => 0,
                    'not_important' => 0,
                ];


                //Monthly progress

                $carbon_month = \Carbon\Carbon::now();
                $prev_month = $carbon_month->subMonth()->format('F');



                $first_day_of_month = Carbon::now()->startOfMonth();
                $five_day = $first_day_of_month->addDays(4);

                $today = Carbon::now();

                if ($today < $five_day) {

                    $id = $this->id;

                    $first_day_of_last_month = new Carbon('first day of last month');
                    $last_day_of_last_month = new Carbon('last day of last month');

                    $last_month_reviews = Review::where(function($query) use ($id) {
                        $query->where( 'dentist_id', $id)->orWhere('clinic_id', $id);
                    })
                    ->where('created_at', '>=', $first_day_of_last_month)
                    ->where('created_at', '<=', $last_day_of_last_month)
                    ->get();   

                    //1.

                    if ($last_month_reviews->count()) {

                        foreach ($last_month_reviews as $rev) {
                            foreach($rev->answers as $answer) {
                                //echo $answer->question['label'].' '.array_sum(json_decode($answer->options, true)) / count(json_decode($answer->options, true)).'<br>';
                                if(!isset($aggregated[$answer->question['label']])) {
                                    $aggregated[$answer->question['label']] = 0;
                                }

                                $aggregated[$answer->question['label']] += array_sum(json_decode($answer->options, true)) / count(json_decode($answer->options, true));
                            }
                        }

                        foreach ($aggregated as $key => $value) {
                            $aggregated[$key] /= $last_month_reviews->count();
                        }

                        $now = Carbon::now();

                        if ($now->month == '8') {
                            $prev_month_rating = array_values($aggregated)[0];
                            $prev_month_label = array_keys($aggregated)[0];
                            
                        } else {
                            $prev_month_rating = array_values($aggregated)[1];
                            $prev_month_label = array_keys($aggregated)[1];
                        }

                        $ret[] = [
                            'title' => trans('trp.strength.dentist.invites.check-rating.title', ['month' => $prev_month]),
                            'text' =>  trans('trp.strength.dentist.invites.check-rating.text', ['prev_month_rating' => $prev_month_rating, 'prev_month_category' => $prev_month_label]),
                            'image' => 'check-rating',
                            'completed' => false,
                            'buttonText' => trans('trp.strength.dentist.invites.check-rating.button-text'),
                            'buttonHref' => $this->getLink().'/#reviews',
                            'target' => false,
                            'event_category' => 'ProfileStrengthDentist',
                            'event_action' => 'Check',
                            'event_label' => 'ReviewsLastMonth',
                        ];
                    } else {
                        $ret[] = [
                            'title' => trans('trp.strength.dentist.invites.check-no-rating.title', ['month' => $prev_month]),
                            'text' => trans('trp.strength.dentist.invites.check-no-rating.text'),
                            'image' => 'check-rating',
                            'completed' => false,
                            'buttonText' => trans('trp.strength.dentist.invites.check-no-rating.button-text'),
                            'buttonHref' => 'https://account.dentacoin.com/invite?platform=trusted-reviews',
                            'target' => true,
                            'event_category' => 'ProfileStrengthDentist',
                            'event_action' => 'Invite',
                            'event_label' => 'RatingInvite',
                        ];
                    }
                    $array_number_shuffle['important']++;

                    //2.

                    $last_month_invitations = UserInvite::where( 'user_id', $this->id)
                    ->where('created_at', '>=', $first_day_of_last_month)
                    ->where('created_at', '<=', $last_day_of_last_month)
                    ->get();

                    if($last_month_invitations->count() && $last_month_reviews->count()) {
                        $ret[] = [
                            'title' => trans('trp.strength.dentist.invites.send-last-month.title', ['last_month_invitations' => $last_month_invitations->count()]),
                            'text' => trans('trp.strength.dentist.invites.send-last-month.text'),
                            'image' => 'invite-patients',
                            'completed' => false,
                            'buttonText' => trans('trp.strength.dentist.invites.send-last-month.button-text'),
                            'buttonHref' => 'https://account.dentacoin.com/invite?platform=trusted-reviews',
                            'target' => true,
                            'event_category' => 'ProfileStrengthDentist',
                            'event_action' => 'Invite',
                            'event_label' => 'InvitesLastMonth',
                        ];
                    } else {
                        $ret[] = [
                            'title' => trans('trp.strength.dentist.invites.not-send-last-month.title'),
                            'text' => trans('trp.strength.dentist.invites.not-send-last-month.text'),
                            'image' => 'invite-patients',
                            'completed' => false,
                            'buttonText' => trans('trp.strength.dentist.invites.not-send-last-month.button-text'),
                            'buttonHref' => 'https://account.dentacoin.com/invite?platform=trusted-reviews',
                            'target' => true,
                            'event_category' => 'ProfileStrengthDentist',
                            'event_action' => 'Invite',
                            'event_label' => 'NoInvitesLastMonth',
                        ];
                    }
                    $array_number_shuffle['important']++;

                    //3.

                    if($this->country_id) {
                        $country_id = $this->country_id;

                        $country_reviews = Review::whereHas('user', function ($query) use ($country_id) {
                            $query->where('country_id', $country_id);
                        })
                        ->where('created_at', '>=', $first_day_of_last_month)
                        ->where('created_at', '<=', $last_day_of_last_month)
                        ->get();

                        $has_country_reviews = false;
                        if ($country_reviews->count()) {
                            $has_country_reviews = true;
                            $country_rating = 0;
                            foreach ($country_reviews as $c_review) {
                                $country_rating += $c_review->rating;
                            }

                            $avg_country_rating = number_format($country_rating / $country_reviews->count(), 2);

                            $dentist_country = Country::find($this->country_id)->name;

                            $ret[] = [
                                'title' => trans('trp.strength.dentist.invites.country-rating-last-month.title', ['dentist_country' => $dentist_country ]),
                                'text' => trans('trp.strength.dentist.invites.country-rating-last-month.text', ['dentist_country' => $dentist_country, 'country_rating' => $avg_country_rating ]),
                                'image' => 'outrank-dentists',
                                'completed' => false,
                                'buttonText' => trans('trp.strength.dentist.invites.country-rating-last-month.button-text'),
                                'buttonHref' => 'https://account.dentacoin.com/invite?platform=trusted-reviews',
                                'target' => true,
                                'event_category' => 'ProfileStrengthDentist',
                                'event_action' => 'Invite',
                                'event_label' => 'Country',
                            ];


                            $array_number_shuffle['important']++;
                        }
                    }
                } else {
                    $current_month_invitations = UserInvite::where( 'user_id', $this->id)
                    ->where('created_at', '>=', $first_day_of_month)
                    ->get();

                    //2.

                    if ($current_month_invitations->count()) {

                        $id = $this->id;

                        $current_month_reviews = Review::where(function($query) use ($id) {
                            $query->where( 'dentist_id', $id)->orWhere('clinic_id', $id);
                        })
                        ->where('created_at', '>=', $first_day_of_month)
                        ->get();

                        if ($current_month_reviews->count()) {
                            foreach ($current_month_reviews as $rev) {
                                foreach($rev->answers as $answer) {
                                    //echo $answer->question['label'].' '.array_sum(json_decode($answer->options, true)) / count(json_decode($answer->options, true)).'<br>';
                                    if(!isset($aggregated[$answer->question['label']])) {
                                        $aggregated[$answer->question['label']] = 0;
                                    }

                                    $aggregated[$answer->question['label']] += array_sum(json_decode($answer->options, true)) / count(json_decode($answer->options, true));
                                }
                            }

                            foreach ($aggregated as $key => $value) {
                                $aggregated[$key] /= $current_month_reviews->count();
                            }

                            $now = Carbon::now();

                            $arrayIndex = (intval(date('Y')) - 2019)*12 + intval(date('n')); // + ....
                            $arrayIndex = $arrayIndex % 9;

                            $cur_month_rating = array_values($aggregated)[$arrayIndex];
                            $cur_month_label = array_keys($aggregated)[$arrayIndex];

                            $ret[] = [
                                'title' => trans('trp.strength.dentist.invites.rating-this-month.title'),
                                'text' => trans('trp.strength.dentist.invites.rating-this-month.text', ['this_month_rating' => $cur_month_rating, 'this_month_category' => $cur_month_label ]),
                                'image' => 'invite-patients',
                                'completed' => false,
                                'buttonText' => trans('trp.strength.dentist.invites.rating-this-month.button-text'),
                                'buttonHref' => 'https://account.dentacoin.com/invite?platform=trusted-reviews',
                                'target' => true,
                                'event_category' => 'ProfileStrengthDentist',
                                'event_action' => 'Check',
                                'event_label' => 'ScoreRatingThisMonth',
                            ];
                        } else {

                            $ret[] = [
                                'title' => trans('trp.strength.dentist.invites.sent-this-month.title', ['invites_number' => $current_month_invitations->count() ]),
                                'text' => trans('trp.strength.dentist.invites.sent-this-month.text'),
                                'image' => 'invite-patients',
                                'completed' => false,
                                'buttonText' => trans('trp.strength.dentist.invites.sent-this-month.button-text'),
                                'buttonHref' => 'https://account.dentacoin.com/invite?platform=trusted-reviews',
                                'target' => true,
                                'event_category' => 'ProfileStrengthDentist',
                                'event_action' => 'Invite',
                                'event_label' => 'InvitesThisMonth',
                            ];
                        }

                    } else {
                        $ret[] = [
                            'title' => trans('trp.strength.dentist.invite-patients.title'),
                            'text' => nl2br(trans('trp.strength.dentist.invite-patients.text')),
                            'image' => 'invite-patients',
                            'completed' => false,
                            'buttonText' => trans('trp.strength.dentist.invite-patients.button-text'),
                            'buttonHref' => 'https://account.dentacoin.com/invite?platform=trusted-reviews',
                            'event_category' => 'ProfileStrengthDentist',
                            'event_action' => 'Invite',
                            'event_label' => 'PatientInvites',
                        ];
                    }
                    $array_number_shuffle['important']++;

                    //3.

                    if($this->country_id) {
                        $country_id = $this->country_id;
                        
                        $country_reviews = Review::whereHas('user', function ($query) use ($country_id) {
                            $query->where('country_id', $country_id);
                        })->where('created_at', '>=', $first_day_of_month)->get();

                        $has_country_reviews = false;
                        if ($country_reviews->count()) {
                            $has_country_reviews = true;

                            $country_rating = 0;
                            foreach ($country_reviews as $c_review) {
                                $country_rating += $c_review->rating;
                            }

                            $avg_country_rating = number_format($country_rating / $country_reviews->count(), 2);
                            $dentist_country = Country::find($this->country_id)->name;

                            $ret[] = [
                                'title' => trans('trp.strength.dentist.invites.country-rating-this-month.title', ['dentist_country' => $dentist_country ]),
                                'text' => trans('trp.strength.dentist.invites.country-rating-this-month.text', ['dentist_country' => $dentist_country, 'country_rating' => $avg_country_rating ]),
                                'image' => 'outrank-dentists',
                                'completed' => false,
                                'buttonText' => trans('trp.strength.dentist.invites.country-rating-this-month.button-text'),
                                'buttonHref' => 'https://account.dentacoin.com/invite?platform=trusted-reviews',
                                'target' => true,
                                'event_category' => 'ProfileStrengthDentist',
                                'event_action' => 'Invite',
                                'event_label' => 'Country',
                            ];
                            $array_number_shuffle['important']++;
                        }
                    }
                }

                //End Monthly progress

                $missing_info = [];
                $event_missing = [];

                if(empty($this->short_description)) {
                    $missing_info[] = 'a short bio';
                    $event_missing[] = 'ShortDescription';
                }
                if(empty($this->description)) {
                    $missing_info[] = 'a longer description';
                    $event_missing[] = 'Description';
                }
                if(empty($this->work_hours)) {
                    $missing_info[] = 'working hours';
                    $event_missing[] = 'WorkHours';
                }
                if($this->photos->isEmpty()) {
                    $missing_info[] = 'photos';
                    $event_missing[] = 'Photos';
                }
                if(empty($this->socials)) {
                    $missing_info[] = 'social channels';
                    $event_missing[] = 'SocialChannels';
                }                

                if( empty($missing_info )) {
                    $ret[] = [
                        'title' => trans('trp.strength.dentist.complete-profile.title'),
                        'text' => nl2br(trans('trp.strength.dentist.complete-profile.text-complete')),
                        'image' => 'complete-profile',
                        'completed' => true,
                        'buttonText' => trans('trp.strength.dentist.complete-profile.button-text'),
                    ];
                } else {
                    $missing_parts = count($missing_info) > 1 ? $missing_info[0].' and '.$missing_info[1] : $missing_info[0];
                    $missing_parts_event = count($event_missing) > 1 ? $event_missing[0].'And'.$event_missing[1] : $event_missing[0];
                    $ret[] = [
                        'title' => trans('trp.strength.dentist.complete-profile.title'),
                        'image' => 'complete-profile',
                        'text' => nl2br(trans('trp.strength.dentist.complete-profile.text', ['missing' => $missing_parts])),
                        'completed' => false,
                        'buttonText' => trans('trp.strength.dentist.complete-profile.button-text'),
                        'buttonHref' => getLangUrl('/'),
                        'event_category' => 'ProfileStrengthDentist',
                        'event_action' => 'Add',
                        'event_label' => $missing_parts_event,
                    ];
                }
                $array_number_shuffle['important']++;

                if( !empty($this->dcn_address )) {
                    $ret[] = [
                        'title' => trans('trp.strength.dentist.set-wallet.title'),
                        'text' => nl2br(trans('trp.strength.dentist.set-wallet.text')),
                        'image' => 'wallet',
                        'completed' => true,
                        'buttonText' => trans('trp.strength.dentist.set-wallet.button-text'),
                    ];
                } else {
                    $ret[] = [
                        'title' => trans('trp.strength.dentist.set-wallet.title'),
                        'text' => nl2br(trans('trp.strength.dentist.set-wallet.text')),
                        'image' => 'wallet',
                        'completed' => false,
                        'buttonText' => trans('trp.strength.dentist.set-wallet.button-text'),
                        'buttonHref' => 'https://wallet.dentacoin.com/',
                        'target' => true,
                        'event_category' => 'ProfileStrengthDentist',
                        'event_action' => 'Create',
                        'event_label' => 'NewWallet',
                    ];
                }
                $array_number_shuffle['important']++;

                if( !empty($this->description )) {
                    $ret[] = [
                        'title' => trans('trp.strength.dentist.add-description.title'),
                        'text' => nl2br(trans('trp.strength.dentist.add-description.text')),
                        'image' => 'description',
                        'completed' => true,
                        'buttonText' => trans('trp.strength.dentist.add-description.button-text'),
                    ];
                } else {
                    $ret[] = [
                        'title' => trans('trp.strength.dentist.add-description.title'),
                        'text' => nl2br(trans('trp.strength.dentist.add-description.text')),
                        'image' => 'description',
                        'completed' => false,
                        'buttonText' => trans('trp.strength.dentist.add-description.button-text'),
                        'buttonHref' => getLangUrl('/'),
                        'event_category' => 'ProfileStrengthDentist',
                        'event_action' => 'Add',
                        'event_label' => 'Description',
                    ];
                }
                $array_number_shuffle['important']++;

                if( !empty($this->socials )) {
                    $ret[] = [
                        'title' => trans('trp.strength.dentist.add-socials.title'),
                        'text' => nl2br(trans('trp.strength.dentist.add-socials.text')),
                        'image' => 'socials',
                        'completed' => true,
                        'buttonText' => trans('trp.strength.dentist.add-socials.button-text'),
                    ];
                } else {
                    $ret[] = [
                        'title' => trans('trp.strength.dentist.add-socials.title'),
                        'text' => nl2br(trans('trp.strength.dentist.add-socials.text')),
                        'image' => 'socials',
                        'completed' => false,
                        'buttonText' => trans('trp.strength.dentist.add-socials.button-text'),
                        'buttonHref' => getLangUrl('/'),
                        'event_category' => 'ProfileStrengthDentist',
                        'event_action' => 'Add',
                        'event_label' => 'Social',
                    ];
                }
                $array_number_shuffle['important']++;

                if ($this->is_clinic) {
                    $ret[] = [
                        'title' => trans('trp.strength.clinic.show-team.title'),
                        'text' => nl2br(trans('trp.strength.clinic.show-team.text')),
                        'image' => 'team',
                        'completed' => false,
                        'buttonText' => trans('trp.strength.clinic.show-team.button-text'),
                        'buttonHref' => $this->getLink().'?popup-loged=add-team-popup',
                        'event_category' => 'ProfileStrengthDentist',
                        'event_action' => 'Add',
                        'event_label' => 'Team',
                    ];

                    $array_number_shuffle['not_important']++;
                }

                if($this->photos->isNotEmpty() && $this->photos->count() >= 10) {
                    $ret[] = [                        
                        'title' => trans('trp.strength.dentist.add-photos.title'),
                        'text' => nl2br(trans('trp.strength.dentist.add-photos.text')),
                        'image' => 'photos',
                        'completed' => true,
                        'buttonText' => trans('trp.strength.dentist.add-photos.button-text'),
                    ];
                } else {
                    $ret[] = [
                        'title' => trans('trp.strength.dentist.add-photos.title'),
                        'text' => nl2br(trans('trp.strength.dentist.add-photos.text')),
                        'image' => 'photos',
                        'completed' => false,
                        'buttonText' => trans('trp.strength.dentist.add-photos.button-text'),
                        'buttonHref' => getLangUrl('/'),
                        'event_category' => 'ProfileStrengthDentist',
                        'event_action' => 'Add',
                        'event_label' => 'Photos',
                    ];
                }
                $array_number_shuffle['not_important']++;

                if( !empty($this->work_hours )) {
                    $ret[] = [
                        'title' => trans('trp.strength.dentist.add-work-hours.title'),
                        'text' => nl2br(trans('trp.strength.dentist.add-work-hours.text')),
                        'image' => 'work-hours',
                        'completed' => true,
                        'buttonText' => trans('trp.strength.dentist.add-work-hours.button-text'),
                    ];
                } else {
                    $ret[] = [
                        'title' => trans('trp.strength.dentist.add-work-hours.title'),
                        'text' => nl2br(trans('trp.strength.dentist.add-work-hours.text')),
                        'image' => 'work-hours',
                        'completed' => false,
                        'buttonText' => trans('trp.strength.dentist.add-work-hours.button-text'),
                        'buttonHref' => $this->getLink().'?popup-loged=popup-wokring-time',
                        'event_category' => 'ProfileStrengthDentist',
                        'event_action' => 'Add',
                        'event_label' => 'Hours',
                    ];
                }
                $array_number_shuffle['not_important']++;

                if( $this->widget_activated) {
                    $ret[] = [
                        'title' => trans('trp.strength.dentist.add-widget.title'),
                        'text' => nl2br(trans('trp.strength.dentist.add-widget.text')),
                        'image' => 'widget',
                        'completed' => true,
                        'buttonText' => trans('trp.strength.dentist.add-widget.button-text'),
                    ];
                } else {
                    $ret[] = [
                        'title' => trans('trp.strength.dentist.add-widget.title'),
                        'text' => nl2br(trans('trp.strength.dentist.add-widget.text')),
                        'image' => 'widget',
                        'completed' => false,
                        'buttonText' => trans('trp.strength.dentist.add-widget.button-text'),
                        'buttonHref' => $this->getLink().'?popup-loged=popup-widget',
                        'event_category' => 'ProfileStrengthDentist',
                        'event_action' => 'Add',
                        'event_label' => 'Widget',
                    ];
                }
                $array_number_shuffle['not_important']++;

                $total_balance = $this->getTotalBalance();
                if ($total_balance > env('VOX_MIN_WITHDRAW') ) {
                    $ret[] = [
                        'title' => trans('trp.strength.dentist.withdraw-rewards.title'),
                        'text' => nl2br(trans('trp.strength.dentist.withdraw-rewards.text', ['link' => '<a href="https://blog.dentacoin.com/what-is-dentacoin-8-use-cases/" target="_blank">', 'endlink' => '</a>'])),
                        'image' => 'balance',
                        'completed' => false,
                        'buttonText' => trans('trp.strength.dentist.withdraw-rewards.button-text'),
                        'buttonHref' => 'https://account.dentacoin.com/?platform=trusted-reviews',
                        'event_category' => 'ProfileStrengthDentist',
                        'event_action' => 'Withdraw',
                        'event_label' => 'WithdrawRewards',
                    ];
                    $array_number_shuffle['not_important']++;
                }

                $stats = Vox::with('stats_main_question')->where('has_stats', 1)->where('featured', 1)->orderBy('id', 'desc')->first();
                $ret[] = [
                    'title' => trans('trp.strength.dentist.check-stats.title'),
                    'text' => nl2br(trans('trp.strength.dentist.check-stats.text', ['name' => $stats->title ])),
                    'image' => 'stats',
                    'completed' => false,
                    'buttonText' => trans('trp.strength.dentist.check-stats.button-text'),
                    'buttonHref' => getVoxUrl('dental-survey-stats/'.$stats->translate(App::getLocale(), true)->slug ),
                    'target' => true,
                    'event_category' => 'ProfileStrengthDentist',
                    'event_action' => 'Check',
                    'event_label' => 'Stats',
                ];
                $array_number_shuffle['not_important']++;

                $ret[] = [
                    'title' => trans('trp.strength.dentist.browse-surveys.title'),
                    'text' => nl2br(trans('trp.strength.dentist.browse-surveys.text')),
                    'image' => 'dentavox',
                    'completed' => false,
                    'buttonText' => trans('trp.strength.dentist.browse-surveys.button-text'),
                    'buttonHref' => getVoxUrl('/'),
                    'target' => true,
                    'event_category' => 'ProfileStrengthDentist',
                    'event_action' => 'Browse',
                    'event_label' => 'SurveysList',
                ];
                $array_number_shuffle['not_important']++;

                $ret[] = [
                    'title' => trans('trp.strength.dentist.join-assurance.title'),
                    'text' => nl2br(trans('trp.strength.dentist.join-assurance.text')),
                    'image' => 'assurance',
                    'completed' => false,
                    'buttonText' => trans('trp.strength.dentist.join-assurance.button-text'),
                    'buttonHref' => 'https://assurance.dentacoin.com',
                    'target' => true,
                    'event_category' => 'ProfileStrengthDentist',
                    'event_action' => 'Join',
                    'event_label' => 'Assurance',
                ];
                $array_number_shuffle['not_important']++;

                $ret[] = [
                    'title' => trans('trp.strength.dentist.join-dentacare.title'),
                    'text' => nl2br(trans('trp.strength.dentist.join-dentacare.text')),
                    'image' => 'dentacare',
                    'completed' => false,
                    'buttonText' => trans('trp.strength.dentist.join-dentacare.button-text'),
                    'buttonHref' => 'https://dentacare.dentacoin.com',
                    'target' => true,
                    'event_category' => 'ProfileStrengthDentist',
                    'event_action' => 'Recommend',
                    'event_label' => 'Dentacare',
                ];
                $array_number_shuffle['not_important']++;

                // $first_part = array_slice($ret, 0, $array_number_shuffle['important'], true);
                // shuffle($first_part);

                // $last_part = array_slice($ret, $array_number_shuffle['important'], $array_number_shuffle['not_important'], true);
                // shuffle($last_part);

                // $ret = array_merge($first_part, $last_part);


                // $ret['photo-dentist'] = $this->hasimage ? true : false;
                // $ret['info'] = ($this->name && $this->phone && $this->description && $this->email && $this->country_id && $this->city_id && $this->zip && $this->address && $this->website) ? true : false;
                // $ret['gallery'] = $this->photos->isNotEmpty() ? true : false;
                // $ret['invite-dentist'] = $this->invites->isNotEmpty() ? true : false;
                // $ret['widget'] = $this->widget_activated ? true : false;

            } else {

                if( $this->reviews_out->isNotEmpty()) {
                    $last_review = $this->reviews_out->first();

                    if($last_review->created_at->timestamp < Carbon::now()->modify('-6 months')->timestamp) {
                        $ret[] = [
                            'title' => trans('trp.strength.patient.visit-dentist.title'),
                            'text' => nl2br(trans('trp.strength.patient.visit-dentist.text')),
                            'image' => 'review',
                            'completed' => false,
                            'buttonText' => trans('trp.strength.patient.visit-dentist.button-text'),
                            'buttonHref' => $this->country_id ? getLangUrl('dentists/'.Country::find($this->country_id)->slug) : getLangUrl('/'),
                            'event_category' => 'ProfileStrengthPatient',
                            'event_action' => 'Write',
                            'event_label' => 'VisitedLatelyRequestInvite',
                        ];
                    } else {
                        //complete step
                        $ret[] = [
                            'title' => trans('trp.strength.patient.visit-dentist.title'),
                            'text' => nl2br(trans('trp.strength.patient.visit-dentist.text')),
                            'image' => 'review',
                            'completed' => true,
                            'buttonText' => trans('trp.strength.patient.visit-dentist.button-text'),
                        ];
                    }

                } else {

                    if($this->created_at->timestamp < Carbon::now()->modify('-1 months')->timestamp) {
                        $ret[] = [
                            'title' => trans('trp.strength.patient.routine-check.title'),
                            'text' => nl2br(trans('trp.strength.patient.routine-check.text')),
                            'image' => 'review',
                            'completed' => false,
                            'buttonText' => trans('trp.strength.patient.routine-check.button-text'),
                            'buttonHref' => $this->country_id ? getLangUrl('dentists/'.Country::find($this->country_id)->slug) : getLangUrl('/'),
                            'event_category' => 'ProfileStrengthPatient',
                            'event_action' => 'Write',
                            'event_label' => 'RoutineCheckReview',
                        ];
                    } else {
                        $ret[] = [
                            'title' => trans('trp.strength.patient.submit-review.title'),
                            'text' => nl2br(trans('trp.strength.patient.submit-review.text')),
                            'image' => 'review',
                            'completed' => false,
                            'buttonText' => trans('trp.strength.patient.submit-review.button-text'),
                            'buttonHref' => $this->country_id ? getLangUrl('dentists/'.Country::find($this->country_id)->slug) : getLangUrl('/'),
                            'event_category' => 'ProfileStrengthPatient',
                            'event_action' => 'Write',
                            'event_label' => 'FirstReview',
                        ];
                    }
                }

                if( $this->reviews_out->isNotEmpty()) {
                    $ret[] = [
                        'title' => trans('trp.strength.patient.invite-dentist.title'),
                        'text' => nl2br(trans('trp.strength.patient.invite-dentist.text')),
                        'image' => 'invite-dentist',
                        'completed' => true,
                        'buttonText' => trans('trp.strength.patient.invite-dentist.button-text'),
                        'buttonHref' => getLangUrl('/').'?popup=invite-new-dentist-popup',
                        'event_category' => 'ProfileStrengthPatient',
                        'event_action' => 'Invite',
                        'event_label' => 'AddNewDentist',
                    ];
                } else {
                    $ret[] = [
                        'title' => trans('trp.strength.patient.invite-dentist.title'),
                        'text' => nl2br(trans('trp.strength.patient.invite-dentist.text')),
                        'image' => 'invite-dentist',
                        'completed' => false,
                        'buttonText' => trans('trp.strength.patient.invite-dentist.button-text'),
                        'buttonHref' => getLangUrl('/').'?popup=invite-new-dentist-popup',
                        'event_category' => 'ProfileStrengthPatient',
                        'event_action' => 'Invite',
                        'event_label' => 'AddNewDentist',
                    ];
                }


                if( $this->dcn_address) {
                    $ret[] = [
                        'title' => trans('trp.strength.patient.set-wallet.title'),
                        'text' => nl2br(trans('trp.strength.patient.set-wallet.text')),
                        'image' => 'wallet',
                        'completed' => true,
                        'buttonText' => trans('trp.strength.patient.set-wallet.button-text'),
                    ];
                } else {
                    $ret[] = [
                        'title' => trans('trp.strength.patient.set-wallet.title'),
                        'text' => nl2br(trans('trp.strength.patient.set-wallet.text')),
                        'image' => 'wallet',
                        'completed' => false,
                        'buttonText' => trans('trp.strength.patient.set-wallet.button-text'),
                        'buttonHref' => 'https://wallet.dentacoin.com/',
                        'target' => true,
                        'event_category' => 'ProfileStrengthPatient',
                        'event_action' => 'Create',
                        'event_label' => 'NewWallet',
                    ];
                }

                $ret[] = [
                    'title' => trans('trp.strength.patient.invite-friends.title'),
                    'text' => nl2br(trans('trp.strength.patient.invite-friends.text')),
                    'image' => 'invite-friends',
                    'completed' => false,
                    'buttonText' => trans('trp.strength.patient.invite-friends.button-text'),
                    'buttonHref' => 'https://account.dentacoin.com/invite?platform=trusted-reviews',
                    'event_category' => 'ProfileStrengthPatient',
                    'event_action' => 'Invite',
                    'event_label' => 'InviteFriends',
                ];

                $total_balance = $this->getTotalBalance();
                if ($total_balance > env('VOX_MIN_WITHDRAW') ) {
                    $ret[] = [
                        'title' => trans('trp.strength.patient.withdraw-rewards.title'),
                        'text' => nl2br(trans('trp.strength.patient.withdraw-rewards.text', ['link' => '<a href="https://blog.dentacoin.com/what-is-dentacoin-8-use-cases/" target="_blank">', 'endlink' => '</a>'])),
                        'image' => 'balance',
                        'completed' => false,
                        'buttonText' => trans('trp.strength.patient.withdraw-rewards.button-text'),
                        'buttonHref' => 'https://account.dentacoin.com/?platform=trusted-reviews',
                        'event_category' => 'ProfileStrengthPatient',
                        'event_action' => 'Withdraw',
                        'event_label' => 'WithdrawRewards',
                    ];
                }

                $all_surveys = Vox::where('type', 'normal')->get();
                $taken = $this->filledVoxes();
                $done_all = false;

                if ($all_surveys->count() == count($taken)) {
                    $done_all = true;
                }

                if(empty($taken)) {
                    $ret[] = [
                        'title' => trans('trp.strength.patient.take-first-survey.title'),
                        'text' => nl2br(trans('trp.strength.patient.take-first-survey.text')),
                        'image' => 'dentavox',
                        'completed' => false,
                        'buttonText' => trans('trp.strength.patient.take-first-survey.button-text'),
                        'buttonHref' => getVoxUrl('/'),
                        'target' => true,
                        'event_category' => 'ProfileStrengthPatient',
                        'event_action' => 'Take',
                        'event_label' => 'FirstSurvey',
                    ];
                } else if (empty($done_all) ) {

                    $voxes = Vox::where('type', 'normal')->orderBy('featured', 'desc')->orderBy('id', 'desc')->get()->pluck('id')->toArray();
                    
                    $filled_voxes = $this->filledVoxes();

                    $latest_voxes = array_diff($voxes, $filled_voxes);
                    $latest_vox = Vox::find(array_values($latest_voxes)[0]);

                    $ret[] = [
                        'title' => trans('trp.strength.patient.take-latest-survey.title'),
                        'text' => nl2br(trans('trp.strength.patient.take-latest-survey.text', ['name' => $latest_vox->title, 'reward' => $latest_vox->getRewardTotal() ])),
                        'image' => 'dentavox',
                        'completed' => false,
                        'buttonText' => trans('trp.strength.patient.take-latest-survey.button-text'),
                        'buttonHref' => getVoxUrl('paid-dental-surveys/'.$latest_vox->translate(App::getLocale(), true)->slug ),
                        'target' => true,
                        'event_category' => 'ProfileStrengthPatient',
                        'event_action' => 'Take',
                        'event_label' => 'LatestSurvey',
                    ];

                } else if($done_all) {
                    $ret[] = [
                        'title' => trans('trp.strength.patient.take-latest-survey.title'),
                        'text' => nl2br(trans('trp.strength.patient.take-latest-survey.text-complete')),
                        'image' => 'dentavox',
                        'completed' => true,
                        'buttonText' => trans('trp.strength.patient.take-latest-survey.button-text'),
                    ];
                }

                $ret[] = [
                    'title' => trans('trp.strength.patient.join-dentacare.title'),
                    'text' => nl2br(trans('trp.strength.patient.join-dentacare.text')),
                    'image' => 'dentacare',
                    'completed' => false,
                    'iosLink' => 'https://apps.apple.com/bg/app/dentacare-health-training/id1274148338',
                    'androidLink' => 'https://play.google.com/store/apps/details?id=com.dentacoin.dentacare&hl=en',
                    'event_category' => 'ProfileStrengthPatient',
                    'event_action' => 'Download',
                    'event_label' => 'Dentacare',
                ];
            }

        } else {

            if($this->is_dentist) {

                $ret[] = [
                    'title' => trans('vox.strength.dentist.public-profile.title'),
                    'text' => nl2br(trans('vox.strength.dentist.public-profile.text')),
                    'image' => 'public-profile',
                    'completed' => false,
                    'buttonText' => trans('vox.strength.dentist.public-profile.button-text'),
                    'buttonHref' => getLangUrl('/', null, 'https://reviews.dentacoin.com/'),
                    'target' => true,
                    'event_category' => 'ProfileStrengthDentist',
                    'event_action' => 'Check',
                    'event_label' => 'TRP',
                ];

                $stats = Vox::with('stats_main_question')->where('has_stats', 1)->where('featured', 1)->orderBy('id', 'desc')->first();
                $ret[] = [
                    'title' => trans('vox.strength.dentist.check-stats.title'),
                    'text' => nl2br(trans('vox.strength.dentist.check-stats.text', ['name' => $stats->title])),
                    'image' => 'stats',
                    'completed' => false,
                    'buttonText' => trans('vox.strength.dentist.check-stats.button-text'),
                    'buttonHref' => getLangUrl('dental-survey-stats/'.$stats->translate(App::getLocale(), true)->slug ),
                    'event_category' => 'ProfileStrengthDentist',
                    'event_action' => 'Check',
                    'event_label' => 'Stats',
                ];

                $ret[] = [
                    'title' => trans('vox.strength.dentist.browse-surveys.title'),
                    'text' => nl2br(trans('vox.strength.dentist.browse-surveys.text')),
                    'image' => 'dentavox',
                    'completed' => false,
                    'buttonText' => trans('vox.strength.dentist.browse-surveys.button-text'),
                    'buttonHref' => getLangUrl('/'),
                    'event_category' => 'ProfileStrengthDentist',
                    'event_action' => 'Browse',
                    'event_label' => 'SurveysList',
                ];

                $ret[] = [
                    'title' => trans('vox.strength.dentist.invite-patients.title'),
                    'text' => nl2br(trans('vox.strength.dentist.invite-patients.text')),
                    'image' => 'invite-friends',
                    'completed' => false,
                    'buttonText' => trans('vox.strength.dentist.invite-patients.button-text'),
                    'buttonHref' => 'https://account.dentacoin.com/invite?platform=dentavox',
                    'event_category' => 'MonthlyDentist',
                    'event_action' => 'Send',
                    'event_label' => 'PatientInvites',
                ];


                if( $this->dcn_address) {
                    $ret[] = [
                        'title' => trans('vox.strength.dentist.set-wallet.title'),
                        'text' => nl2br(trans('vox.strength.dentist.set-wallet.text')),
                        'image' => 'wallet',
                        'completed' => true,
                        'buttonText' => trans('vox.strength.dentist.set-wallet.button-text'),
                    ];
                } else {
                    $ret[] = [
                        'title' => trans('vox.strength.dentist.set-wallet.title'),
                        'text' => nl2br(trans('vox.strength.dentist.set-wallet.text')),
                        'image' => 'wallet',
                        'completed' => false,
                        'buttonText' => trans('vox.strength.dentist.set-wallet.button-text'),
                        'buttonHref' => 'https://wallet.dentacoin.com/',
                        'target' => true,
                        'event_category' => 'ProfileStrengthDentist',
                        'event_action' => 'Create',
                        'event_label' => 'NewWallet',
                    ];
                }

                $missing_info = [];
                $event_missing = [];

                if(empty($this->short_description)) {
                    $missing_info[] = 'a short bio';
                    $event_missing[] = 'ShortDescription';
                }
                if(empty($this->description)) {
                    $missing_info[] = 'a longer description';
                    $event_missing[] = 'Description';
                }
                if(empty($this->work_hours)) {
                    $missing_info[] = 'working hours';
                    $event_missing[] = 'WorkHours';
                }
                if($this->photos->isEmpty()) {
                    $missing_info[] = 'photos';
                    $event_missing[] = 'Photos';
                }
                if(empty($this->socials)) {
                    $missing_info[] = 'social channels';
                    $event_missing[] = 'SocialChannels';
                }

                if( empty($missing_info )) {
                    $ret[] = [
                        'title' => trans('vox.strength.dentist.complete-profile.title'),
                        'text' => nl2br(trans('vox.strength.dentist.complete-profile.text-complete')),
                        'image' => 'complete-profile',
                        'completed' => true,
                        'buttonText' => trans('vox.strength.dentist.complete-profile.button-text'),
                    ];
                } else {
                    $missing_parts = count($missing_info) > 1 ? $missing_info[0].' and '.$missing_info[1] : $missing_info[0];
                    $missing_parts_event = count($event_missing) > 1 ? $event_missing[0].'And'.$event_missing[1] : $event_missing[0];
                    $ret[] = [
                        'title' => trans('vox.strength.dentist.complete-profile.title'),
                        'image' => 'complete-profile',
                        'text' => nl2br(trans('vox.strength.dentist.complete-profile.text', ['missing' => $missing_parts])),
                        'completed' => false,
                        'buttonText' => trans('vox.strength.dentist.complete-profile.button-text'),
                        'buttonHref' => getLangUrl('/', null, 'https://reviews.dentacoin.com/'),
                        'target' => true,
                        'event_category' => 'ProfileStrengthDentist',
                        'event_action' => 'Add',
                        'event_label' => $missing_parts_event,
                    ];
                }

                $ret[] = [
                    'title' => trans('vox.strength.dentist.join-assurance.title'),
                    'text' => nl2br(trans('vox.strength.dentist.join-assurance.text')),
                    'image' => 'assurance',
                    'completed' => false,
                    'buttonText' => trans('vox.strength.dentist.join-assurance.button-text'),
                    'buttonHref' => 'https://assurance.dentacoin.com',
                    'target' => true,
                    'event_category' => 'ProfileStrengthDentist',
                    'event_action' => 'Join',
                    'event_label' => 'Assurance',
                ];

                $total_balance = $this->getTotalBalance();
                if ($total_balance > env('VOX_MIN_WITHDRAW') ) {
                    $ret[] = [
                        'title' => trans('vox.strength.dentist.withdraw-rewards.title'),
                        'text' => nl2br(trans('vox.strength.dentist.withdraw-rewards.text', ['link' => '<a href="https://blog.dentacoin.com/what-is-dentacoin-8-use-cases/" target="_blank">', 'endlink' => '</a>' ])),
                        'image' => 'balance',
                        'completed' => false,
                        'buttonText' => trans('vox.strength.dentist.withdraw-rewards.button-text'),
                        'buttonHref' => 'https://account.dentacoin.com/?platform=dentavox',
                        'event_category' => 'ProfileStrengthDentist',
                        'event_action' => 'Withdraw',
                        'event_label' => 'WithdrawRewards',
                    ];
                }

                $ret[] = [
                    'title' => trans('vox.strength.dentist.join-dentacare.title'),
                    'text' => nl2br(trans('vox.strength.dentist.join-dentacare.text')),
                    'image' => 'dentacare',
                    'completed' => false,
                    'buttonText' => trans('vox.strength.dentist.join-dentacare.button-text'),
                    'buttonHref' => 'https://dentacare.dentacoin.com',
                    'target' => true,
                    'event_category' => 'ProfileStrengthDentist',
                    'event_action' => 'Recommend',
                    'event_label' => 'Dentacare',
                ];

                // $ret['photo-dentist'] = $this->hasimage ? true : false;
                // $ret['info'] = ($this->name && $this->phone && $this->description && $this->email && $this->country_id && $this->city_id && $this->zip && $this->address && $this->website) ? true : false;
                // $ret['gallery'] = $this->photos->isNotEmpty() ? true : false;
                // $ret['wallet'] = $this->dcn_address ? true : false;
                // $ret['invite-dentist'] = $this->invites->isNotEmpty() ? true : false;
                // $ret['widget'] = $this->widget_activated ? true : false;


            } else {

                $all_surveys = Vox::where('type', 'normal')->get();
                $taken = $this->filledVoxes();
                $done_all = false;

                if ($all_surveys->count() == count($taken)) {
                    $done_all = true;
                }

                if(empty($taken)) {
                    $ret[] = [
                        'title' => trans('vox.strength.patient.take-first-survey.title'),
                        'text' => nl2br(trans('vox.strength.patient.take-first-survey.text')),
                        'image' => 'dentavox',
                        'completed' => false,
                        'buttonText' => trans('vox.strength.patient.take-first-survey.button-text'),
                        'buttonHref' => getVoxUrl('/'),
                        'event_category' => 'ProfileStrengthPatient',
                        'event_action' => 'Browse',
                        'event_label' => 'SurveysList',
                    ];
                } else if (empty($done_all) ) {

                    $voxes = Vox::where('type', 'normal')->orderBy('featured', 'desc')->orderBy('id', 'desc')->get()->pluck('id')->toArray();
                    
                    $filled_voxes = $this->filledVoxes();

                    $latest_voxes = array_diff($voxes, $filled_voxes);
                    $latest_vox = Vox::find(array_values($latest_voxes)[0]);

                    $ret[] = [
                        'title' => trans('vox.strength.patient.take-latest-survey.title'),
                        'text' => nl2br(trans('vox.strength.patient.take-latest-survey.text', ['name' => $latest_vox->title, 'reward' => $latest_vox->getRewardTotal() ])),
                        'image' => 'dentavox',
                        'completed' => false,
                        'buttonText' => trans('vox.strength.patient.take-latest-survey.button-text'),
                        'buttonHref' => getVoxUrl('paid-dental-surveys/'.$latest_vox->translate(App::getLocale(), true)->slug ),
                        'event_category' => 'ProfileStrengthPatient',
                        'event_action' => 'Take',
                        'event_label' => 'LatestSurvey',
                    ];
                } else if($done_all) {
                    $ret[] = [
                        'title' => trans('vox.strength.patient.take-latest-survey.title'),
                        'text' => nl2br(trans('vox.strength.patient.take-latest-survey.text-complete')),
                        'image' => 'dentavox',
                        'completed' => true,
                        'buttonText' => trans('vox.strength.patient.take-latest-survey.button-text'),
                    ];
                }

                if( $this->dcn_address) {
                    $ret[] = [
                        'title' => trans('vox.strength.patient.set-wallet.title'),
                        'text' => nl2br(trans('vox.strength.patient.set-wallet.text')),
                        'image' => 'wallet',
                        'completed' => true,
                        'buttonText' => trans('vox.strength.patient.set-wallet.button-text'),
                    ];
                } else {
                    $ret[] = [
                        'title' => trans('vox.strength.patient.set-wallet.title'),
                        'text' => nl2br(trans('vox.strength.patient.set-wallet.text')),
                        'image' => 'wallet',
                        'completed' => false,
                        'buttonText' => trans('vox.strength.patient.set-wallet.button-text'),
                        'buttonHref' => 'https://wallet.dentacoin.com/',
                        'target' => true,
                        'event_category' => 'ProfileStrengthPatient',
                        'event_action' => 'Create',
                        'event_label' => 'NewWallet',
                    ];
                }

                $total_balance = $this->getTotalBalance();
                if ($total_balance > env('VOX_MIN_WITHDRAW') ) {
                    $ret[] = [
                        'title' => trans('vox.strength.patient.withdraw-rewards.title'),
                        'text' => nl2br(trans('vox.strength.patient.withdraw-rewards.text', ['link' => '<a href="https://blog.dentacoin.com/what-is-dentacoin-8-use-cases/" target="_blank">', 'endlink' => '</a>'])),
                        'image' => 'balance',
                        'completed' => false,
                        'buttonText' => trans('vox.strength.patient.withdraw-rewards.button-text'),
                        'buttonHref' => 'https://account.dentacoin.com/?platform=dentavox',
                        'event_category' => 'ProfileStrengthPatient',
                        'event_action' => 'Withdraw',
                        'event_label' => 'WithdrawRewards',
                    ];
                }

                $ret[] = [
                    'title' => trans('vox.strength.patient.invite-friends.title'),
                    'text' => nl2br(trans('vox.strength.patient.invite-friends.text')),
                    'image' => 'invite-friends',
                    'completed' => false,
                    'buttonText' => trans('vox.strength.patient.invite-friends.button-text'),
                    'buttonHref' => 'https://account.dentacoin.com/invite?platform=dentavox',
                    'event_category' => 'ProfileStrengthPatient',
                    'event_action' => 'Invite',
                    'event_label' => 'InviteFriends',
                ];


                if( $this->reviews_out->isNotEmpty()) {
                    $last_review = $this->reviews_out->first();

                    if($last_review->created_at->timestamp < Carbon::now()->modify('-6 months')->timestamp) {
                        $ret[] = [
                            'title' => trans('vox.strength.patient.visit-dentist.title'),
                            'text' => nl2br(trans('vox.strength.patient.visit-dentist.text')),
                            'image' => 'review',
                            'completed' => false,
                            'buttonText' => trans('vox.strength.patient.visit-dentist.button-text'),
                            'buttonHref' => $this->country_id ? getLangUrl('dentists/'.Country::find($this->country_id)->slug, null, 'https://reviews.dentacoin.com/') : getLangUrl('/', null, 'https://reviews.dentacoin.com/'),
                            'target' => true,
                            'event_category' => 'ProfileStrengthPatient',
                            'event_action' => 'Write',
                            'event_label' => 'VisitedLatelyRequestInvite',
                        ];
                    } else {
                        $ret[] = [
                            'title' => trans('vox.strength.patient.visit-dentist.title'),
                            'text' => nl2br(trans('vox.strength.patient.visit-dentist.text')),
                            'image' => 'review',
                            'completed' => true,
                            'buttonText' => trans('vox.strength.patient.visit-dentist.button-text'),
                        ];
                    }

                } else {

                    if($this->created_at->timestamp < Carbon::now()->modify('-1 months')->timestamp) {
                        $ret[] = [
                            'title' => trans('vox.strength.patient.routine-check.title'),
                            'text' => nl2br(trans('vox.strength.patient.routine-check.text')),
                            'image' => 'review',
                            'completed' => false,
                            'buttonText' => trans('vox.strength.patient.routine-check.button-text'),
                            'buttonHref' => $this->country_id ? getLangUrl('dentists/'.Country::find($this->country_id)->slug, null, 'https://reviews.dentacoin.com/') : getLangUrl('/', null, 'https://reviews.dentacoin.com/'),
                            'target' => true,
                            'event_category' => 'ProfileStrengthPatient',
                            'event_action' => 'Write',
                            'event_label' => 'RoutineCheckReview',
                        ];
                    } else {
                        $ret[] = [
                            'title' => trans('vox.strength.patient.submit-review.title'),
                            'text' => nl2br(trans('vox.strength.patient.submit-review.text')),
                            'image' => 'review',
                            'completed' => false,
                            'buttonText' => trans('vox.strength.patient.submit-review.button-text'),
                            'buttonHref' => $this->country_id ? getLangUrl('dentists/'.Country::find($this->country_id)->slug, null, 'https://reviews.dentacoin.com/') : getLangUrl('/', null, 'https://reviews.dentacoin.com/'),
                            'target' => true,
                            'event_category' => 'ProfileStrengthPatient',
                            'event_action' => 'Write',
                            'event_label' => 'FirstReview',
                        ];
                    }
                }

                $ret[] = [
                    'title' => trans('vox.strength.patient.join-dentacare.title'),
                    'text' => nl2br(trans('vox.strength.patient.join-dentacare.text')),
                    'image' => 'dentacare',
                    'completed' => false,
                    'iosLink' => 'https://apps.apple.com/bg/app/dentacare-health-training/id1274148338',
                    'androidLink' => 'https://play.google.com/store/apps/details?id=com.dentacoin.dentacare&hl=en',
                    'event_category' => 'ProfileStrengthPatient',
                    'event_action' => 'Download',
                    'event_label' => 'Dentacare',
                ];
            }
        }

        return $ret;
    }

    public function getStrength() {
        $ret = [];

        if($this->is_dentist) {
            $ret['photo-dentist'] = $this->hasimage ? true : false;
            $ret['info'] = ($this->name && $this->phone && $this->description && $this->email && $this->country_id && $this->city_id && $this->zip && $this->address && $this->website) ? true : false;
            $ret['gallery'] = $this->photos->isNotEmpty() ? true : false;
            $ret['wallet'] = $this->dcn_address ? true : false;
            $ret['invite-dentist'] = $this->invites->isNotEmpty() ? true : false;
            $ret['widget'] = $this->widget_activated ? true : false;
        } else {
            $ret['photo-patient'] = $this->hasimage ? true : false;
            $ret['wallet'] = $this->dcn_address ? true : false;
            $ret['review'] = $this->reviews_out->isNotEmpty() ? true : false;
            $ret['invite-patient'] = $this->invites->isNotEmpty() ? true : false;
        }
        return $ret;
    }

    public function getStrengthCompleted($platform) {
            
        $num = 0;
        $s = $this->getStrengthPlatform($platform);
        foreach ($s as $val) {
            if ($val['completed'] == true) {
                $num++;
            }
        }

        return $num;
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

    public function getPrevBansCount($domain='vox', $type=null) {
        $times = 0;
        foreach ($this->bans as $ban) {
            if($ban->domain==$domain && (!$type || $type==$ban->type) ) {
                $times++;
            }
        }

        return $times;
    }

    public function banUser($domain, $reason='') {
        $times = $this->getPrevBansCount($domain, $reason);
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
                'expires' => $ban->expires->toFormattedDateString().', '.$ban->expires->toTimeString(),
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
                ->where('created_at', '>=', Carbon::now()->modify('-3 months'))
                ->first();

                return $heAllowed ? false : true;
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

    public function sendTemplate($template_id, $params=null, $platform=null) {
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
        $item->send();

        return $item;
    }

    public function sendGridTemplate($template_id, $substitutions=null, $platform=null, $is_skipped=null) {
        $item = new Email;
        $item->user_id = $this->id;
        $item->template_id = $template_id;
        $item->meta = $substitutions;
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

        if (empty($is_skipped)) {

            $sender = $item->platform=='vox' ? config('mail.from.address-vox') : config('mail.from.address');
            $sender_name = $item->platform=='vox' ? config('mail.from.name-vox') : config('mail.from.name');
            //$sender_name = config('platforms.'.$item->platform.'.name') ?? config('mail.from.name');

            $from = new From($sender, $sender_name);

            $tos = [new To( $this->email)];

            $email = new SendGridMail(
                $from,
                $tos
            );
            $email->setTemplateId($item->template->sendgrid_template_id);
            $email->addBcc("4097841@bcc.hubspot.com");

            if ($item->template->category) {
                $email->addCategory($item->template->category);
            } else {
                $email->addCategory(strtoupper($item->platform).' Service '.($this->is_dentist ? 'Dentist' : 'Patient'));
            }

            $domain = 'https://'.config('platforms.'.$this->platform.'.url').'/';

            $pageviews = DentistPageview::where('dentist_id', $this->id)->count();

            $defaulth_substitutions  = [
                "name" => $this->getNameSendGrid(),
                "platform" => $item->platform,
                "invite-patient" => getLangUrl( 'dentist/'.$this->slug, null, $domain).'?'. http_build_query(['popup'=>'popup-invite']),
                "homepage" => getLangUrl('/', null, $domain),
                "trp_profile" => $this->getLink(),
                "town" => $this->city_name ? $this->city_name : 'your town',
                "country" => $this->country_id ? Country::find($this->country_id)->name : 'your country',
                "unsubscribe" => getLangUrl( 'unsubscribe/'.$this->id.'/'.$this->get_token(), null, $domain),
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

    public function setNameAttribute($value) {
        $this->attributes['name'] = $value;
        $this->attributes['slug'] = $this->makeSlug();
        //
    }
    public function setAddressAttribute($newvalue) {
        $this->attributes['address'] = $newvalue;
        $this->attributes['lat'] = null;
        $this->attributes['lon'] = null;
        $this->attributes['city_name'] = null;
        $this->attributes['state_name'] = null;
        $this->attributes['state_slug'] = null;
        if( $this->country) {
            $info = self::validateAddress($this->country->name, $newvalue);
            if(!empty($info)) {
                foreach ($info as $key => $value) {
                    if( in_array($key, $this->fillable) ) {
                        $this->attributes[$key] = $value;                        
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
                        $cname = iconv('UTF-8', 'ASCII//TRANSLIT', $ac->long_name);
                        $cname = iconv('ASCII', 'UTF-8', $cname);
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
                        $cname = iconv('UTF-8', 'ASCII//TRANSLIT', $ac->long_name);
                        $cname = iconv('ASCII', 'UTF-8', $cname);
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
                        $cname = iconv('UTF-8', 'ASCII//TRANSLIT', $ac->long_name);
                        $cname = iconv('ASCII', 'UTF-8', $cname);
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
                        $cname = iconv('UTF-8', 'ASCII//TRANSLIT', $ac->long_name);
                        $cname = iconv('ASCII', 'UTF-8', $cname);
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
        $img->fit( 400, 400 );
        $img->save($to_thumb);
        $this->hasimage = true;
        $this->hasimage_social = false;
        $this->updateStrength();
        $this->refreshReviews();
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

    public function canIuseAddress( $address ) {
        $used = self::where('dcn_address', 'LIKE', $address)->first();
        if($used && $used->id!=$this->id) {
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
        $income = DcnReward::where('user_id', $this->id)->where('platform', 'trp')->sum('reward');
        $cashouts = DcnCashout::where('user_id', $this->id)->where('platform', 'trp')->sum('reward');

        return $income - $cashouts;
    }

    //
    //
    // Vox 
    //
    //

    public function getVoxBalance() {
        $income = DcnReward::where('user_id', $this->id)->where('platform', 'vox')->sum('reward');
        $cashouts = DcnCashout::where('user_id', $this->id)->where('platform', 'vox')->sum('reward');

        return $income - $cashouts;
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

    public function deleteActions() {
        foreach ($this->reviews_out as $r) {
            $r->delete();
        }

        $id = $this->id;
        $teams = UserTeam::where(function($query) use ($id) {
            $query->where( 'dentist_id', $id)->orWhere('user_id', $id);
        })->get();

        if (!empty($teams)) {
           foreach ($teams as $team) {
               $team->delete();
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

        if(!$this->is_dentist) {
            $this->sendTemplate(9);
        }

        $this->logoutActions();
    }

    public function canInvite($platform) {
        return ($this->status=='approved' || $this->status=='added_approved' || $this->status=='test') && !$this->loggedFromBadIp();
    }

    public function canWithdraw($platform) {
        return ($this->status=='approved' || $this->status=='added_approved' || $this->status=='test') && $this->civic_kyc && !$this->loggedFromBadIp() && ($this->created_at->timestamp <= (time() - 259200)) ;
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

        // $url = file_get_contents('https://dentacoin.net/gas-price');

        // $gas = json_decode($url, true);

        // if(intVal($gas['gasPrice']) > intVal($gas['treshold']) ) {
        //     return true;
        // } else {
        //     return false;
        // }

        return false;
    }

    public function loggedFromBadIp() {
        $users_with_same_ip = UserLogin::where('ip', 'like', self::getRealIp())->where('user_id', '!=', $this->ip)->groupBy('user_id')->get()->count();

        if ($users_with_same_ip >=3 && !$this->allow_withdraw && !$this->is_dentist && $this::getRealIp() != '78.130.213.163' ) {
            return true;
        } else {
            return false;
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
                    $reward->os = $dd->getOs()['name'];
                }

                $reward->save();
            }
            setcookie('first_test', null, time()-600, '/');

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
            1=> 'Mon',
            'Tue',
            'Wed',
            'Thur',
            'Fri',
            'Sat',
            'Sun',
        ];
        $opens = null;

        if( $this->work_hours && $this->country) {

            $identifiers = \DateTimeZone::listIdentifiers(\DateTimeZone::PER_COUNTRY, mb_strtoupper($this->country->code));
            if(!empty($identifiers)) {
                $tz = current($identifiers);
                $date = Carbon::now($tz);
                $dow = $date->dayOfWeekIso;
                if( isset( $this->work_hours[$dow] ) ) {
                    $oa = explode(':', $this->work_hours[$dow][0]);
                    $ca = explode(':', $this->work_hours[$dow][1]);
                    if(!empty($oa[0]) && is_numeric($oa[0]) && !empty($ca[0]) && is_numeric($ca[0]) && !empty($oa[1]) && is_numeric($oa[1]) && !empty($ca[1]) && is_numeric($ca[1])) {
                        $open = Carbon::createFromTime( intval($oa[0]), intval($oa[1]), 0, $tz );
                        $close = Carbon::createFromTime( intval($ca[0]), intval($ca[1]), 0, $tz );
                        if( $date->lessThan($close) && $date->greaterThan($open) ) {
                            $opens = '<span class="green-text">Open now</span>&nbsp;<span>('.$this->work_hours[$dow][0].' - '.$this->work_hours[$dow][1].')</span>';
                        }
                    }
                } 

                if( empty($opens) ) {
                    while($dow<=7) {
                        $dow++;
                        if( isset( $this->work_hours[$dow] ) ) {
                            $opens = '<span>Opens on '.$dows[$dow].' at '.$this->work_hours[$dow][0].'</span>';
                            break;
                        }
                    }
                    if(empty($opens)) {
                        $wh = $this->work_hours;
                        reset($wh);
                        $dow = key( $wh );
                        $opens = '<span>Opens on '.$dows[$dow].' at '.$wh[$dow][0].'</span>';
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
                    $ret[] = $workplace->clinic->getName();
                } else {
                    if( $isme ) {
                        $ret[] = $workplace->clinic->getName().' (pending)';
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
        $img->insert( public_path().'/img-trp/cover-dentist.png');

        $avatar = $this->hasimage ? $this->getImagePath(true) : public_path().'/new-vox-img/no-avatar-1.png';
        $img->insert($avatar , 'top-left', 80, 170 );


        $names = $this->getName();
        $names_size = 66;
        if(mb_strlen($names)>20) {
            $names_size = 56;
        }
        if(mb_strlen($names)>25) {
            $names_size = 50;
        }
        if(mb_strlen($names)>30) {
            $names_size = 45;
        }
        $img->text($names, 515, 205, function($font) use ($names_size) {
            $font->file(public_path().'/fonts/Calibri-Bold.ttf');
            $font->size($names_size);
            $font->color('#000000');
            $font->align('left');
            $font->valign('top');
        });
        $type = ($this->is_partner ? 'Partner ' : '').($this->is_clinic ? 'Clinic' : 'Dentist');
        $type_left = $this->is_partner ? 575 : 515;
        if($this->is_partner) {
            $avatar = public_path().'/img-trp/cover-partner.png';
            $img->insert($avatar , 'top-left', 515, 283 );            
        }

        $img->text($type, $type_left, 283, function($font) {
            $font->file(public_path().'/fonts/Calibri.ttf');
            $font->size(46);
            $font->color('#555555');
            $font->align('left');
            $font->valign('top');
        });
        $location = ($this->city_name ? $this->city_name.', ' : '').($this->state_name ? $this->state_name.', ' : '').$this->country->name;
        $img->text($location, 562, 365, function($font) {
            $font->file(public_path().'/fonts/Calibri.ttf');
            $font->size(30);
            $font->color('#000000');
            $font->align('left');
            $font->valign('top');
        });
        $reviews = '('.intval($this->ratings).' reviews)';
        $img->text($reviews, 860, 470, function($font) {
            $font->file(public_path().'/fonts/Calibri-Light.ttf');
            $font->size(30);
            $font->color('#555555');
            $font->align('left');
            $font->valign('top');
        });

        $step = 67;
        $start = 518;
        for($i=1;$i<=$this->avg_rating;$i++) {
            $img->insert( public_path().'/img-trp/cover-star.png' , 'top-left', $start, 452 );
            $start += $step;
        }

        $rest = ( $this->avg_rating - floor( $this->avg_rating ) );
        if($rest) {
            $halfstar = Image::canvas(59*$rest, 62, '#fff');
            $halfstar->insert( public_path().'/img-trp/cover-star.png', 'top-left', 0, 0 );
            $img->insert($halfstar , 'top-left', $start, 452 );
        }

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

    public function getSlugAttribute($value) {


        if(empty($this->attributes['slug'])) {
            $this->attributes['slug'] = $this->makeSlug();
            $this->save();
        }
        return $this->attributes['slug'];
    }

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

    //Handles Civic Scam
    public function validateCivicKyc($civic) {
        $data = json_decode($civic->response, true);
        $ret = [
            'success' => false,
            'weak' => true
        ];

        if(!empty($data['data'])) {
            foreach ($data['data'] as $key => $value) {
                if( mb_strpos( $value['label'], 'documents.' ) !==false ) {
                    unset($ret['weak']);
                    break;
                }
            }
        } 
        if(empty($ret['weak']) && !empty($data['userId'])) {
            $u = self::where('civic_id', 'LIKE', $data['userId'])->first();
            if(!empty($u) && $u->id != $this->id) {
                $ret['duplicate'] = true;
            } else {

                $u = self::where('civic_kyc_hash', 'LIKE', $civic->hash)->first();
                if(!empty($u) && $u->id != $this->id) {
                    $ret['duplicate'] = true;
                    $notifyMe = [
                        'official@youpluswe.com',
                        'petya.ivanova@dentacoin.com',
                        'donika.kraeva@dentacoin.com',
                        //'daria.kerancheva@dentacoin.com',
                        'petar.stoykov@dentacoin.com'
                    ];
                    $mtext = 'A user just tried to withdraw with duplicated ID card:
Original holder: '.$u->getName().' (https://reviews.dentacoin.com/cms/users/edit/'.$u->id.')
Scammer: '.$this->getName().' (https://reviews.dentacoin.com/cms/users/edit/'.$this->id.')';

                    foreach ($notifyMe as $n) {
                        Mail::raw($mtext, function ($message) use ($n) {
                            $message->from(config('mail.from.address'), config('mail.from.name'));
                            $message->to( $n );
                            $message->subject('New Scam attempt');
                        });
                    }

                    $this->deleteActions();
                    self::destroy( $this->id );
                    $u->deleteActions();
                    self::destroy( $u->id );

                } else {
                    $this->civic_kyc_hash = $civic->hash;
                    $this->civic_kyc = 1;
                    $this->civic_id = $data['userId'];
                    $this->save();
                    $ret['success'] = true;            
                }
            }
        }
        return $ret;
    }


    public function logoutActions() {
        session([
            'mark-login' => null,
            'login-logged' => null,
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
        $arr['trp_public_profile_link'] = $this->is_dentist && ($this->status=='approved' || $this->status=='test' || $this->status=='added_approved') ? $this->getLink() : null;

        return $arr;
    }
}