<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\WithdrawalsCondition;
use App\Models\UserGuidedTour;

use Carbon\Carbon;

use App;

class UserStrength extends Model {
    
    public static function getStrengthPlatform($platform, $user) {

        $ret = [];

        if ($platform == 'trp') {

            if($user->is_dentist) {

                //steps count = 10 hardcode
                $ret['completed_steps'] = 0;

                $array_number_shuffle = [
                    'important' => 0,
                    'not_important' => 0,
                ];

                $missing_info = true;

                if(!empty($user->description) && !empty($user->work_hours) && $user->photos->isNotEmpty() && !empty($user->socials)) {
                    $missing_info = false;

                    if( $user->is_clinic) {

                        if( $user->notVerifiedTeamFromInvitation->isNotEmpty() || $user->team->isNotEmpty() ) {
                            $missing_info = false;
                        } else {
                            $missing_info = true;
                        }
                    } 
                }

                if( empty($missing_info )) {
                    $ret['completed_steps']++;
                } else {
                    $ret[] = [
                        'title' => trans('trp.strength.dentist.complete-profile.title'),
                        'image' => 'complete-profile',
                        'text' => nl2br(trans('trp.strength.dentist.complete-profile.text')),
                        'completed' => false,
                        'buttonText' => trans('trp.strength.dentist.complete-profile.button-text'),
                        'buttonjs' => 'go-first-tour go-tour',
                        'event_category' => 'ProfileStrengthDentist',
                        'event_action' => 'Add',
                        'event_label' => 'MissingInfo',
                    ];
                    $array_number_shuffle['important']++;
                }

                //Monthly progress

                $carbon_month = Carbon::now();
                $prev_month = $carbon_month->subMonth()->format('F');

                $first_day_of_month = Carbon::now()->startOfMonth();
                $five_day = $first_day_of_month->addDays(4);

                $today = Carbon::now();

                $new_user = $user->created_at > Carbon::now()->subDays(30);

                $current_month_invitations = UserInvite::where( 'user_id', $user->id)
                ->where('created_at', '>=', $first_day_of_month)
                ->get();

                if($current_month_invitations->count()) {
                    $ret['completed_steps']++;

                    if($current_month_invitations->count() > 10) {
                        $ret['completed_steps']++;
                    }
                }

                if ($today < $five_day && !$new_user) {

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
                                if(!$user->is_clinic && $user->my_workplace_approved->isNotEmpty()) {
                                    if(in_array($answer->question['label'], ['Doctor', 'Treatment experience', 'Treatment quality'])) {
                                        if(!isset($aggregated[$answer->question['label']])) {
                                            $aggregated[$answer->question['label']] = 0;
                                        }

                                        $aggregated[$answer->question['label']] += array_sum(json_decode($answer->options, true)) / count(json_decode($answer->options, true));
                                    }
                                } else {
                                    if(!isset($aggregated[$answer->question['label']])) {
                                        $aggregated[$answer->question['label']] = 0;
                                    }

                                    $aggregated[$answer->question['label']] += array_sum(json_decode($answer->options, true)) / count(json_decode($answer->options, true));
                                }
                            }
                        }

                        foreach ($aggregated as $key => $value) {
                            $aggregated[$key] /= $last_month_reviews->count();
                        }

                        $arrayIndex = (intval(date('Y')) - 2019)*12 + intval(date('n')); // + ....
                        $arrayIndex = $arrayIndex % count($aggregated);

                        $prev_month_rating = array_values($aggregated)[$arrayIndex];
                        $prev_month_label = array_keys($aggregated)[$arrayIndex];

                        $check_reviews = UserGuidedTour::where('user_id', $user->id)
                        ->whereNotNull('check_reviews_on')
                        ->where('check_reviews_on', '>', $first_day_of_month)
                        ->first();

                        if(!empty($check_reviews)) {
                            $ret['completed_steps']++;
                        }

                        $ret[] = [
                            'title' => trans('trp.strength.dentist.invites.check-rating.title', ['month' => $prev_month]),
                            'text' =>  trans('trp.strength.dentist.invites.check-rating.text', ['month_rating' => round($prev_month_rating, 2), 'prev_month_category' => $prev_month_label]),
                            'image' => 'check-rating',
                            'completed' => false,
                            'buttonText' => trans('trp.strength.dentist.invites.check-rating.button-text'),
                            'buttonjs' => 'str-see-reviews',
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
                            'buttonjs' => 'str-invite',
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
                            'buttonjs' => 'str-invite',
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
                            'buttonjs' => 'str-invite',
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

                        if ($country_reviews->count()) {
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
                                'buttonjs' => 'str-invite',
                                'target' => true,
                                'event_category' => 'ProfileStrengthDentist',
                                'event_action' => 'Invite',
                                'event_label' => 'Country',
                            ];

                            $array_number_shuffle['important']++;
                        }
                    }
                } else {
                    //2.

                    if ($current_month_invitations->count()) {

                        $id = $user->id;

                        $current_month_reviews = Review::where(function($query) use ($id) {
                            $query->where( 'dentist_id', $id)
                            ->orWhere('clinic_id', $id);
                        })
                        ->where('created_at', '>=', $first_day_of_month)
                        ->get();

                        if ($current_month_reviews->count()) {
                            foreach ($current_month_reviews as $rev) {
                                foreach($rev->answers as $answer) {
                                    if(!$user->is_clinic && $user->my_workplace_approved->isNotEmpty()) {
                                        if(in_array($answer->question['label'], ['Doctor', 'Treatment experience', 'Treatment quality'])) {
                                            if(!isset($aggregated[$answer->question['label']])) {
                                                $aggregated[$answer->question['label']] = 0;
                                            }

                                            $aggregated[$answer->question['label']] += array_sum(json_decode($answer->options, true)) / count(json_decode($answer->options, true));
                                        }
                                    } else {

                                        //echo $answer->question['label'].' '.array_sum(json_decode($answer->options, true)) / count(json_decode($answer->options, true)).'<br>';
                                        if(!isset($aggregated[$answer->question['label']])) {
                                            $aggregated[$answer->question['label']] = 0;
                                        }

                                        $aggregated[$answer->question['label']] += array_sum(json_decode($answer->options, true)) / count(json_decode($answer->options, true));
                                    }
                                }
                            }

                            foreach ($aggregated as $key => $value) {
                                $aggregated[$key] /= $current_month_reviews->count();
                            }

                            $arrayIndex = (intval(date('Y')) - 2019)*12 + intval(date('n')); // + ....
                            $arrayIndex = $arrayIndex % count($aggregated);

                            $cur_month_rating = array_values($aggregated)[$arrayIndex];
                            $cur_month_label = array_keys($aggregated)[$arrayIndex];

                            $check_reviews = UserGuidedTour::where('user_id', $user->id)
                            ->whereNotNull('check_reviews_on')
                            ->where('check_reviews_on', '>', $first_day_of_month)
                            ->first();

                            if(!empty($check_reviews)) {
                                $ret['completed_steps']++;
                            }

                            $ret[] = [
                                'title' => trans('trp.strength.dentist.invites.rating-this-month.title'),
                                'text' => trans('trp.strength.dentist.invites.rating-this-month.text', ['this_month_rating' => $cur_month_rating, 'this_month_category' => $cur_month_label ]),
                                'image' => 'invite-patients',
                                'completed' => false,
                                'buttonText' => trans('trp.strength.dentist.invites.rating-this-month.button-text'),
                                'buttonjs' => 'str-see-reviews',
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
                                'buttonjs' => 'str-invite',
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
                            'buttonjs' => 'str-invite',
                            'event_category' => 'ProfileStrengthDentist',
                            'event_action' => 'Invite',
                            'event_label' => 'PatientInvites',
                        ];
                    }
                    $array_number_shuffle['important']++;

                    //3. DENTIST IN COUNTRY

                    if($user->country_id) {
                        $country_id = $user->country_id;
                        
                        $country_reviews = Review::whereHas('user', function ($query) use ($country_id) {
                            $query->where('country_id', $country_id);
                        })->where('created_at', '>=', $first_day_of_month)
                        ->get();

                        if ($country_reviews->count()) {

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
                                'buttonjs' => 'str-invite',
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


                if($user->reviews_in_standard()->count()) {
                    
                    if( $user->widget_activated || $user->dentist_fb_page->isNotEmpty()) {
                        $ret['completed_steps']++;
                    } else {
                        $ret[] = [
                            'title' => trans('trp.strength.dentist.add-widget.title'),
                            'text' => nl2br(trans('trp.strength.dentist.add-widget.text')),
                            'image' => 'widget',
                            'completed' => false,
                            'buttonText' => trans('trp.strength.dentist.add-widget.button-text'),
                            'buttonjs' => 'go-reviews-tour',
                            'event_category' => 'ProfileStrengthDentist',
                            'event_action' => 'Add',
                            'event_label' => 'Widget',
                        ];
                    }
                    $array_number_shuffle['not_important']++;
                }


                if( $user->wallet_addresses->isNotEmpty() ) {
                    $ret['completed_steps']++;
                } else {
                    $ret[] = [
                        'title' => trans('trp.strength.dentist.set-wallet.title'),
                        'text' => nl2br(trans('trp.strength.dentist.set-wallet.text')),
                        'image' => 'wallet',
                        'completed' => false,
                        'buttonText' => trans('trp.strength.dentist.set-wallet.button-text'),
                        'buttonHref' => 'https://account.dentacoin.com/?platform=trusted-reviews',
                        'target' => true,
                        'event_category' => 'ProfileStrengthDentist',
                        'event_action' => 'Create',
                        'event_label' => 'NewWallet',
                    ];
                }
                $array_number_shuffle['important']++;


                $total_balance = $user->getTotalBalance();
                if ($total_balance > WithdrawalsCondition::find(1)->min_amount ) {
                    $ret[] = [
                        'title' => trans('trp.strength.dentist.withdraw-rewards.title'),
                        'text' => nl2br(trans('trp.strength.dentist.withdraw-rewards.text', ['link' => '<span class="open-str-link" href="https://blog.dentacoin.com/what-is-dentacoin-8-use-cases/">', 'endlink' => '</span>'])),
                        'image' => 'balance',
                        'completed' => false,
                        'buttonText' => trans('trp.strength.dentist.withdraw-rewards.button-text'),
                        'buttonHref' => 'https://account.dentacoin.com/?platform=trusted-reviews',
                        'event_category' => 'ProfileStrengthDentist',
                        'event_action' => 'Withdraw',
                        'event_label' => 'WithdrawRewards',
                    ];
                    $array_number_shuffle['not_important']++;
                } else if ( $user->history->isNotEmpty() && $total_balance < WithdrawalsCondition::find(1)->min_amount ) {
                    $ret['completed_steps']++;
                }


                $check_stats = UserGuidedTour::where('user_id', $user->id)
                ->whereNotNull('check_stats_on')
                ->where('check_stats_on', '>', $first_day_of_month)
                ->first();

                if(!empty($check_stats)) {
                    $ret['completed_steps']++;
                }

                $stats = Vox::where('has_stats', 1)
                ->where('stats_featured', 1)
                ->orderBy('launched_at', 'desc')
                ->first();

                if (empty($stats)) {
                    $stats = Vox::where('has_stats', 1)
                    ->orderBy('launched_at', 'desc')
                    ->first();
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


                $check_assurance = UserGuidedTour::where('user_id', $user->id)
                ->whereNotNull('dcn_assurance')
                ->first();

                if(!empty($check_assurance)) {
                    $ret['completed_steps']++;
                } else {

                    $ret[] = [
                        'title' => trans('trp.strength.dentist.join-assurance.title'),
                        'text' => nl2br(trans('trp.strength.dentist.join-assurance.text')),
                        'image' => 'assurance',
                        'completed' => false,
                        'buttonText' => trans('trp.strength.dentist.join-assurance.button-text'),
                        'buttonHref' => 'https://assurance.dentacoin.com',
                        'buttonjs' => 'str-check-assurance',
                        'target' => true,
                        'event_category' => 'ProfileStrengthDentist',
                        'event_action' => 'Join',
                        'event_label' => 'Assurance',
                    ];
                    $array_number_shuffle['not_important']++;
                }


                $check_dentacare = UserGuidedTour::where('user_id', $user->id)
                ->whereNotNull('dentacare_app')
                ->first();

                if(!empty($check_dentacare)) {
                    $ret['completed_steps']++;
                } else {
                    $ret[] = [
                        'title' => trans('trp.strength.dentist.join-dentacare.title'),
                        'text' => nl2br(trans('trp.strength.dentist.join-dentacare.text')),
                        'image' => 'dentacare',
                        'completed' => false,
                        'buttonText' => trans('trp.strength.dentist.join-dentacare.button-text'),
                        'buttonHref' => 'https://dentacare.dentacoin.com',
                        'buttonjs' => 'str-check-dentacare',
                        'target' => true,
                        'event_category' => 'ProfileStrengthDentist',
                        'event_action' => 'Recommend',
                        'event_label' => 'Dentacare',
                    ];
                    $array_number_shuffle['not_important']++;
                }

                // $first_part = array_slice($ret, 0, $array_number_shuffle['important'], true);
                // shuffle($first_part);

                // $last_part = array_slice($ret, $array_number_shuffle['important'], $array_number_shuffle['not_important'], true);
                // shuffle($last_part);

                // $ret = array_merge($first_part, $last_part);


                // $completed = $ret['completed_steps'];
                // unset($ret['completed_steps']);
                // shuffle($ret);

                // $ret['completed_steps'] = $completed;

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

                $ret[] = [
                    'title' => trans('trp.strength.patient.invite-dentist.title'),
                    'text' => nl2br(trans('trp.strength.patient.invite-dentist.text')),
                    'image' => 'invite-dentist',
                    'completed' => $user->reviews_out->isNotEmpty() ? true : false,
                    'buttonText' => trans('trp.strength.patient.invite-dentist.button-text'),
                    'buttonHref' => getLangUrl('/'),
                    'event_category' => 'ProfileStrengthPatient',
                    'event_action' => 'Invite',
                    'event_label' => 'AddNewDentist',
                ];

                if( $user->wallet_addresses->isNotEmpty()) {
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
                if ($total_balance > WithdrawalsCondition::find(1)->min_amount ) {
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

                    $voxes = Vox::where('type', 'normal')
                    ->orderBy('featured', 'desc')
                    ->orderBy('id', 'desc')
                    ->get()
                    ->pluck('id')
                    ->toArray();
                    
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
        }

        return $ret;
    }
}