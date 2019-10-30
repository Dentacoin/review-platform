<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

use App;

class UserStrength extends Model
{
    
    public static function getStrengthPlatform($platform, $user) {

        $ret = [];

        if ($platform == 'trp') {

            if($user->is_dentist) {

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

                    $id = $user->id;

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
                            'buttonHref' => $user->getLink().'/#reviews',
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

                    $last_month_invitations = UserInvite::where( 'user_id', $user->id)
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

                    if($user->country_id) {
                        $country_id = $user->country_id;

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

                            $dentist_country = Country::find($user->country_id)->name;

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
                    $current_month_invitations = UserInvite::where( 'user_id', $user->id)
                    ->where('created_at', '>=', $first_day_of_month)
                    ->get();

                    //2.

                    if ($current_month_invitations->count()) {

                        $id = $user->id;

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
                                'buttonHref' => getLangUrl('/').'?'. http_build_query(['popup'=>'popup-invite']),
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
                                'buttonHref' => getLangUrl('/').'?'. http_build_query(['popup'=>'popup-invite']),
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
                            'buttonHref' => getLangUrl('/').'?'. http_build_query(['popup'=>'popup-invite']),
                            'event_category' => 'ProfileStrengthDentist',
                            'event_action' => 'Invite',
                            'event_label' => 'PatientInvites',
                        ];
                    }
                    $array_number_shuffle['important']++;

                    //3.

                    if($user->country_id) {
                        $country_id = $user->country_id;
                        
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
                            $dentist_country = Country::find($user->country_id)->name;

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

                if(empty($user->short_description)) {
                    $missing_info[] = 'a short bio';
                    $event_missing[] = 'ShortDescription';
                }
                if(empty($user->description)) {
                    $missing_info[] = 'a longer description';
                    $event_missing[] = 'Description';
                }
                if(empty($user->work_hours)) {
                    $missing_info[] = 'working hours';
                    $event_missing[] = 'WorkHours';
                }
                if($user->photos->isEmpty()) {
                    $missing_info[] = 'photos';
                    $event_missing[] = 'Photos';
                }
                if(empty($user->socials)) {
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

                if( !empty($user->dcn_address )) {
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

                if( !empty($user->description )) {
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

                if( !empty($user->socials )) {
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

                if ($user->is_clinic) {
                    $ret[] = [
                        'title' => trans('trp.strength.clinic.show-team.title'),
                        'text' => nl2br(trans('trp.strength.clinic.show-team.text')),
                        'image' => 'team',
                        'completed' => false,
                        'buttonText' => trans('trp.strength.clinic.show-team.button-text'),
                        'buttonHref' => $user->getLink().'?popup-loged=add-team-popup',
                        'event_category' => 'ProfileStrengthDentist',
                        'event_action' => 'Add',
                        'event_label' => 'Team',
                    ];

                    $array_number_shuffle['not_important']++;
                }

                if($user->photos->isNotEmpty() && $user->photos->count() >= 10) {
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

                if( !empty($user->work_hours )) {
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
                        'buttonHref' => $user->getLink().'?popup-loged=popup-wokring-time',
                        'event_category' => 'ProfileStrengthDentist',
                        'event_action' => 'Add',
                        'event_label' => 'Hours',
                    ];
                }
                $array_number_shuffle['not_important']++;

                if($user->reviews_in_standard()->count()) {
                    
                    if( $user->widget_activated) {
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
                            'buttonHref' => $user->getLink().'?popup-loged=popup-widget',
                            'event_category' => 'ProfileStrengthDentist',
                            'event_action' => 'Add',
                            'event_label' => 'Widget',
                        ];
                    }
                    $array_number_shuffle['not_important']++;
                }

                $total_balance = $user->getTotalBalance();
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

                $stats = Vox::where('has_stats', 1)->where('featured', 1)->orderBy('id', 'desc')->first();
                if (empty($stats)) {
                    $stats = Vox::where('has_stats', 1)->orderBy('id', 'desc')->first();
                }
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


                // $ret['photo-dentist'] = $user->hasimage ? true : false;
                // $ret['info'] = ($user->name && $user->phone && $user->description && $user->email && $user->country_id && $user->city_id && $user->zip && $user->address && $user->website) ? true : false;
                // $ret['gallery'] = $user->photos->isNotEmpty() ? true : false;
                // $ret['invite-dentist'] = $user->invites->isNotEmpty() ? true : false;
                // $ret['widget'] = $user->widget_activated ? true : false;

            } else {

                if( $user->reviews_out->isNotEmpty()) {
                    $last_review = $user->reviews_out->first();

                    if($last_review->created_at->timestamp < Carbon::now()->modify('-6 months')->timestamp) {
                        $ret[] = [
                            'title' => trans('trp.strength.patient.visit-dentist.title'),
                            'text' => nl2br(trans('trp.strength.patient.visit-dentist.text')),
                            'image' => 'review',
                            'completed' => false,
                            'buttonText' => trans('trp.strength.patient.visit-dentist.button-text'),
                            'buttonHref' => $user->country_id ? getLangUrl('dentists/'.Country::find($user->country_id)->slug) : getLangUrl('/'),
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

                    if($user->created_at->timestamp < Carbon::now()->modify('-1 months')->timestamp) {
                        $ret[] = [
                            'title' => trans('trp.strength.patient.routine-check.title'),
                            'text' => nl2br(trans('trp.strength.patient.routine-check.text')),
                            'image' => 'review',
                            'completed' => false,
                            'buttonText' => trans('trp.strength.patient.routine-check.button-text'),
                            'buttonHref' => $user->country_id ? getLangUrl('dentists/'.Country::find($user->country_id)->slug) : getLangUrl('/'),
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
                            'buttonHref' => $user->country_id ? getLangUrl('dentists/'.Country::find($user->country_id)->slug) : getLangUrl('/'),
                            'event_category' => 'ProfileStrengthPatient',
                            'event_action' => 'Write',
                            'event_label' => 'FirstReview',
                        ];
                    }
                }

                if( $user->reviews_out->isNotEmpty()) {
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


                if( $user->dcn_address) {
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

                $total_balance = $user->getTotalBalance();
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
                $taken = $user->filledVoxes();
                $done_all = false;

                if ($all_surveys->count() <= count($taken)) {
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
                    
                    $filled_voxes = $user->filledVoxes();

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

            if($user->is_dentist) {

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

                $stats = Vox::where('has_stats', 1)->where('featured', 1)->orderBy('id', 'desc')->first();
                if (empty($stats)) {
                    $stats = Vox::where('has_stats', 1)->orderBy('id', 'desc')->first();
                }
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


                if( $user->dcn_address) {
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

                if(empty($user->short_description)) {
                    $missing_info[] = 'a short bio';
                    $event_missing[] = 'ShortDescription';
                }
                if(empty($user->description)) {
                    $missing_info[] = 'a longer description';
                    $event_missing[] = 'Description';
                }
                if(empty($user->work_hours)) {
                    $missing_info[] = 'working hours';
                    $event_missing[] = 'WorkHours';
                }
                if($user->photos->isEmpty()) {
                    $missing_info[] = 'photos';
                    $event_missing[] = 'Photos';
                }
                if(empty($user->socials)) {
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

                $total_balance = $user->getTotalBalance();
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

                // $ret['photo-dentist'] = $user->hasimage ? true : false;
                // $ret['info'] = ($user->name && $user->phone && $user->description && $user->email && $user->country_id && $user->city_id && $user->zip && $user->address && $user->website) ? true : false;
                // $ret['gallery'] = $user->photos->isNotEmpty() ? true : false;
                // $ret['wallet'] = $user->dcn_address ? true : false;
                // $ret['invite-dentist'] = $user->invites->isNotEmpty() ? true : false;
                // $ret['widget'] = $user->widget_activated ? true : false;


            } else {

                $all_surveys = Vox::where('type', 'normal')->get();
                $taken = $user->filledVoxes();
                $done_all = false;

                if ($all_surveys->count() <= count($taken)) {
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
                    
                    $filled_voxes = $user->filledVoxes();

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

                if( $user->dcn_address) {
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

                $total_balance = $user->getTotalBalance();
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


                if( $user->reviews_out->isNotEmpty()) {
                    $last_review = $user->reviews_out->first();

                    if($last_review->created_at->timestamp < Carbon::now()->modify('-6 months')->timestamp) {
                        $ret[] = [
                            'title' => trans('vox.strength.patient.visit-dentist.title'),
                            'text' => nl2br(trans('vox.strength.patient.visit-dentist.text')),
                            'image' => 'review',
                            'completed' => false,
                            'buttonText' => trans('vox.strength.patient.visit-dentist.button-text'),
                            'buttonHref' => $user->country_id ? getLangUrl('dentists/'.Country::find($user->country_id)->slug, null, 'https://reviews.dentacoin.com/') : getLangUrl('/', null, 'https://reviews.dentacoin.com/'),
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

                    if($user->created_at->timestamp < Carbon::now()->modify('-1 months')->timestamp) {
                        $ret[] = [
                            'title' => trans('vox.strength.patient.routine-check.title'),
                            'text' => nl2br(trans('vox.strength.patient.routine-check.text')),
                            'image' => 'review',
                            'completed' => false,
                            'buttonText' => trans('vox.strength.patient.routine-check.button-text'),
                            'buttonHref' => $user->country_id ? getLangUrl('dentists/'.Country::find($user->country_id)->slug, null, 'https://reviews.dentacoin.com/') : getLangUrl('/', null, 'https://reviews.dentacoin.com/'),
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
                            'buttonHref' => $user->country_id ? getLangUrl('dentists/'.Country::find($user->country_id)->slug, null, 'https://reviews.dentacoin.com/') : getLangUrl('/', null, 'https://reviews.dentacoin.com/'),
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

}