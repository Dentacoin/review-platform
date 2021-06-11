<?php

namespace App\Http\Controllers\Front;
use App\Http\Controllers\FrontController;

use DeviceDetector\Parser\Device\DeviceParserAbstract;
use DeviceDetector\DeviceDetector;

use Illuminate\Support\Facades\Input;
use Maatwebsite\Excel\Facades\Excel;

use App\Models\UserGuidedTour;
use App\Models\AnonymousUser;
use App\Models\UserCategory;
use App\Models\UserHistory;
use App\Models\UserInvite;
use App\Models\UserAction;
use App\Models\DcnReward;
use App\Models\UserPhoto;
use App\Models\UserTeam;
use App\Models\Country;
use App\Models\UserAsk;
use App\Models\Reward;
use App\Models\Review;
use App\Models\User;

use App\Imports\Import;
use Carbon\Carbon;

use Validator;
use Response;
use Request;
use Image;
use Route;
use Mail;
use Auth;
use File;

class ProfileController extends FrontController {

    public function __construct(\Illuminate\Http\Request $request, Route $route, $locale=null) {
        parent::__construct($request, $route, $locale);

        $this->profile_fields = [
            'title' => [
                'type' => 'select',
                'values' => config('titles')
            ],
            'name' => [
                'type' => 'text',
                'required' => true,
                'min' => 3,
            ],
            'name_alternative' => [
                'type' => 'text',
                'required' => false,
            ],
            'description' => [
                'type' => 'text',
                'required' => false,
            ],
            'specialization' => [
                'type' => 'specialization',
                'required' => false,
            ],
            'accepted_payment' => [
                'type' => 'array',
                'required' => false,
            ],
            // 'email' => [
            //     'type' => 'text',
            //     'required' => true,
            //     'is_email' => true,
            // ],
            'phone' => [
                'type' => 'text',
                'required' => true,
                'regex' => 'regex: /^[- +()]*[0-9][- +()0-9]*$/u',
            ],
            'country_id' => [
                'type' => 'country',
                'required' => false,
            ],
            'address' => [
                'type' => 'text',
                'required' => true,
            ],
            'website' => [
                'type' => 'text',
                'required' => true,
                'regex' => 'regex:/^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/',
            ],
            'socials' => [
                'type' => 'text',
                'required' => false,
            ],
            'work_hours' => [
                'required' => false,
                'hide' => true
            ],
            'short_description' => [
                'type' => 'textarea',
                'required' => false,
                'max' => 150
            ],
            'email_public' => [
                'type' => 'text',
                'required' => false,
                'is_email' => true,
            ],
        ];
    }

    /**
     * dentist invites patients manually
     */
    public function invite($locale=null) {

        if(!empty($this->user) && $this->user->canInvite('trp') ) {

            $validator = Validator::make(Request::all(), [
                'email' => ['required', 'email'],
                'name' => ['required', 'string'],
            ]);

            if ($validator->fails()) {
                return Response::json(['success' => false, 'message' => trans('trp.page.profile.invite.failure') ] );
            } else {

                $valid_email = $this->user->sendgridEmailValidation(68, Request::Input('email'));

                if(!$valid_email) {
                    $invitation = new UserInvite;
                    $invitation->user_id = $this->user->id;
                    $invitation->invited_email = Request::Input('email');
                    $invitation->invited_name = Request::Input('name');
                    $invitation->platform = 'trp';
                    $invitation->review = true;
                    $invitation->suspicious_email = true;
                    $invitation->save();

                    // sendgridEmailValidation
                    return Response::json(['success' => false, 'message' => 'Not sent - suspicious email address.' ] );
                }

                $is_dentist = User::where('email', 'LIKE', Request::Input('email') )->where('is_dentist', 1)->first();

                if (!empty($is_dentist)) {
                    return Response::json(['success' => false, 'message' => trans('trp.page.profile.invite-dentist.failure')] );
                }

                $invitation = UserInvite::where([
                    ['user_id', $this->user->id],
                    ['invited_email', 'LIKE', Request::Input('email')],
                ])->first();

                $existing_patient = User::withTrashed()->where('email', 'LIKE', Request::Input('email') )->where('is_dentist', 0)->first();
                $existing_anonymous = AnonymousUser::where('email', 'LIKE', Request::Input('email'))->first();

                if(!empty($existing_patient) && !empty($existing_patient->deleted_at)) {
                    return Response::json(['success' => false, 'message' => trans('trp.page.profile.invite.patient-deleted', ['email' => Request::Input('email') ])] );
                }

                if($invitation) {

                    if(!empty($existing_patient)) {
                        $d_id = $this->user->id;

                        $patient_review = Review::where('user_id', $existing_patient->id )->where(function($query) use ($d_id) {
                            $query->where( 'dentist_id', $d_id)->orWhere('clinic_id', $d_id);
                        })->orderBy('id', 'desc')->first();
                        
                        if($invitation->created_at->timestamp > Carbon::now()->subMonths(1)->timestamp) {
                            return Response::json(['success' => false, 'message' => trans('trp.page.profile.invite.already-invited-month') ] );
                        }
                    }

                    if($invitation->created_at->timestamp > Carbon::now()->subMonths(1)->timestamp) {
                        return Response::json(['success' => false, 'message' => trans('trp.page.profile.invite.already-invited-month')] );
                    }

                    $invitation->invited_name = Request::Input('name');
                    $invitation->created_at = Carbon::now();

                    if (empty($invitation->unsubscribed)) {
                        $invitation->review = true;
                        $invitation->completed = null;
                        $invitation->notified1 = null;
                        $invitation->notified2 = null;
                        $invitation->notified3 = null;
                    }

                    if(!empty(Request::Input('invite_hubapp')) && $this->user->is_partner) {
                        $invitation->for_dentist_patients = true;
                    }
                    $invitation->save();

                    if(!empty($existing_patient)) {
                        $last_ask = UserAsk::where('user_id', $existing_patient->id)->where('dentist_id', $this->user->id)->first();
                        if(!empty($last_ask)) {
                            $last_ask->created_at = Carbon::now();
                            $last_ask->on_review = true;
                            $last_ask->save();
                        } else {
                            $ask = new UserAsk;
                            $ask->user_id = $existing_patient->id;
                            $ask->dentist_id = $this->user->id;
                            $ask->status = 'yes';
                            $ask->on_review = true;
                            $ask->save();
                        }
                    }
                } else {
                    $invitation = new UserInvite;
                    $invitation->user_id = $this->user->id;
                    $invitation->invited_email = Request::Input('email');
                    $invitation->invited_name = Request::Input('name');
                    $invitation->platform = 'trp';
                    $invitation->review = true;
                    if(!empty(Request::Input('invite_hubapp')) && $this->user->is_partner) {
                        $invitation->for_dentist_patients = true;
                    }
                    $invitation->save();
                }

                if(!empty($existing_patient)) {

                    if(!empty(Request::Input('invite_hubapp')) && $this->user->is_partner) {
                        $existing_patient->patient_of = $this->user->id;
                        $existing_patient->save();
                    }

                    $substitutions = [
                        'type' => $this->user->is_clinic ? 'dental clinic' : ($this->user->is_dentist ? 'your dentist' : ''),
                        'inviting_user_name' => ($this->user->is_dentist && !$this->user->is_clinic && $this->user->title) ? config('titles')[$this->user->title].' '.$this->user->name : $this->user->name,
                        'invited_user_name' => Request::Input('name'),
                        "invitation_link" => $this->user->getLink().'?'. http_build_query(['dcn-gateway-type'=>'patient-login', 'inviter' => User::encrypt($this->user->id), 'inviteid' => User::encrypt($invitation->id) ]),
                    ];

                    $existing_patient->sendGridTemplate(68, $substitutions, 'trp');

                } else {

                    $inviter_email = $this->user->email ? $this->user->email : $this->user->mainBranchEmail();

                    if(Request::Input('email') != $inviter_email) {

                        if ( $this->user->is_dentist) {

                            $dentist_name = $this->user->name;
                            $dentist_email = $inviter_email;

                            $unsubscribed = User::isUnsubscribedAnonymous(106, 'trp', Request::Input('email'));

                            if(!empty($existing_anonymous)) {

                                if(!$unsubscribed) {
                                    $subscribe_cats = $existing_anonymous->website_notifications;

                                    if(!isset($subscribe_cats['trp'])) {

                                        $subscribe_cats[] = 'trp';
                                        $existing_anonymous->website_notifications = $subscribe_cats;
                                        $existing_anonymous->save();
                                    }
                                }

                            } else {
                                $new_anonymous_user = new AnonymousUser;
                                $new_anonymous_user->email = Request::Input('email');
                                $new_anonymous_user->website_notifications = ['trp'];
                                $new_anonymous_user->save();
                            }

                            $substitutions = [
                                'type' => $this->user->is_clinic ? 'dental clinic' : ($this->user->is_dentist ? 'your dentist' : ''),
                                'inviting_user_name' => ($this->user->is_dentist && !$this->user->is_clinic && $this->user->title) ? config('titles')[$this->user->title].' '.$dentist_name : $dentist_name,
                                'invited_user_name' => Request::Input('name'),
                                "invitation_link" => $this->user->getLink().'?'. http_build_query(['dcn-gateway-type'=>'patient-register', 'inviter' => User::encrypt($this->user->id), 'inviteid' => User::encrypt($invitation->id) ]),
                            ];

                            $mail = User::unregisteredSendGridTemplate($this->user, Request::Input('email'), Request::Input('name'),  106, $substitutions, 'trp', $unsubscribed, Request::Input('email'));
                        } else {

                            $unsubscribed = User::isUnsubscribedAnonymous(17, 'trp', Request::Input('email'));

                            if(!empty($existing_anonymous)) {

                                if(!$unsubscribed) {
                                    $subscribe_cats = $existing_anonymous->website_notifications;

                                    if(!isset($subscribe_cats['trp'])) {

                                        $subscribe_cats[] = 'trp';
                                        $existing_anonymous->website_notifications = $subscribe_cats;
                                        $existing_anonymous->save();
                                    }
                                }

                            } else {
                                $new_anonymous_user = new AnonymousUser;
                                $new_anonymous_user->email = Request::Input('email');
                                $new_anonymous_user->website_notifications = ['trp'];
                                $new_anonymous_user->save();
                            }

                            $user = User::find(113928);
                            $dentist_name = $user->name;
                            $dentist_email = $user->email;
                            $user->name = Request::Input('name');
                            $user->email = Request::Input('email');
                            $user->save();

                            $mail = $user->sendTemplate( 17 , [
                                'friend_name' => $dentist_name,
                                'invitation_id' => $invitation->id
                            ], 'trp', $unsubscribed, Request::Input('email'));

                            $user->name = $dentist_name;
                            $user->email = $dentist_email;
                            $user->save();
                        }

                        $mail->delete();

                    } else {
                        return Response::json(['success' => false, 'message' => trans('trp.page.profile.invite.yourself') ] );
                    }
                }
                return Response::json(['success' => true, 'message' => trans('trp.page.profile.invite.success') ] );
            }
        }

        return null;
    }

    /**
     * dentist invites patients by whatsApp
     */
    public function invite_whatsapp($locale=null) {

        if(!empty($this->user) && $this->user->canInvite('trp') ) {

            $invitation = new UserInvite;
            $invitation->user_id = $this->user->id;
            $invitation->invited_email = 'whatsapp';
            $invitation->platform = 'trp';
            $invitation->save();

            $text = trans('trp.page.profile.invite.whatsapp', ['name' => $this->user->getNames() ]).rawurlencode($this->user->getLink().'?'. http_build_query(['dcn-gateway-type'=>'patient-register', 'inviter' => User::encrypt($this->user->id), 'inviteid' => User::encrypt($invitation->id) ]));

            return Response::json([
                'success' => true,
                'message' => trans('trp.page.profile.invite.success'),
                'text' => $text,
            ] );
        }
    }

    /**
     * dentist invites patients by copy/paste step 1
     */
    public function invite_copypaste($locale=null) {

        if(!empty($this->user) && $this->user->canInvite('trp') ) {

            $validator = Validator::make(Request::all(), [
                'copypaste' => array('required'),
            ]);

            if ($validator->fails()) {
                $ret = array(
                    'success' => false,
                    'message' => trans('trp.page.profile.invite.copypaste.error'),
                );

                return Response::json( $ret );
            } else {

                $columns = explode(PHP_EOL, Request::input('copypaste'));
                $rows = [];
                foreach ($columns as $column) {
                    $rows[] = preg_split('/[\t,]/', $column);
                }

                if (count($rows[0]) <= 1) {
                    return Response::json([
                        'success' => false,
                        'message' => trans('trp.page.profile.invite.copypaste.error'),
                    ] );
                }

                $reversedRows = [];
                $maxcnt = 0;
                foreach ($rows as $row) {
                    if(count($row) > $maxcnt) {
                        $maxcnt = count($row);
                    }
                }

                for($i=0;$i<$maxcnt; $i++) {
                    $reversedRows[$i] = [];
                }

                foreach ($rows as $row) {
                    for($i=0;$i<$maxcnt; $i++) {
                        $reversedRows[$i][] = isset($row[$i]) ? trim($row[$i]) : '';
                    }
                }

                if (session('bulk_invites')) {
                    session()->pull('bulk_invites');
                }                
                session(['bulk_invites' => $reversedRows]);

                return Response::json([
                    'success' => true,
                    'info' => $reversedRows,
                ] );

            }
        }
    }

    /**
     * dentist invites patients from file and by copy/paste step 2 (merged method)
     */
    public function invite_copypaste_emails($locale=null) {

        if(!empty($this->user) && $this->user->canInvite('trp') ) {

            if(Request::Input('patient-emails')) {

                $bulk_invites = session('bulk_invites');
                $bulk_emails = $bulk_invites[Request::Input('patient-emails') - 1];

                unset($bulk_invites[Request::Input('patient-emails') - 1]);

                if (session('bulk_emails')) {
                    session()->pull('bulk_emails');
                }                
                session(['bulk_emails' => $bulk_emails]);

                $first_ten_emails = array_slice($bulk_emails, 0, 10);
                $p_emails = implode(', ', $first_ten_emails);

                return Response::json([
                    'success' => true,
                    'info' => $bulk_invites,
                    'emails' => $p_emails,
                ] );
            }
        }
    }

    /**
     * dentist invites patients from file and by copy/paste step 3 (merged method)
     */
    public function invite_copypaste_names($locale=null) {

        if(!empty($this->user) && $this->user->canInvite('trp') ) {

            if(Request::Input('patient-names')) {

                $bulk_invites = session('bulk_invites');
                $bulk_names = $bulk_invites[Request::Input('patient-names') - 1];

                if (session('bulk_names')) {
                    session()->pull('bulk_names');
                }                
                session(['bulk_names' => $bulk_names]);

                $first_ten_names = array_slice($bulk_names, 0, 10);
                $p_names = implode(', ', $first_ten_names);

                return Response::json([
                    'success' => true,
                    'names' => $p_names,
                ] );
            }
        }
    }

    /**
     * dentist invites patients from file and by copy/paste - final step (merged method)
     */
    public function invite_copypaste_final($locale=null) {

        if(!empty($this->user) && $this->user->canInvite('trp') ) {

            if(session('bulk_names') && session('bulk_emails')) {
                $emails = session('bulk_emails');
                $names = session('bulk_names');

                $invalid = 0;
                $invalid_emails = [];
                $already_invited = 0;
                $already_invited_emails = [];

                foreach ($emails as $key => $email) {
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $invalid++;
                    }

                    $valid_email = $this->user->sendgridEmailValidation(68, $email);

                    if(!$valid_email) {
                        $invalid++;

                        $invalid_emails[] = $email;
                    }

                    $invitation = UserInvite::where([
                        ['user_id', $this->user->id],
                        ['invited_email', 'LIKE', $email],
                    ])->first();

                    if ($invitation) {
                        $already_invited++;

                        $already_invited_emails[] = $email;
                    }
                }

                $final_message = '';
                $alert_color = '';
                $gtag_tracking = true;

                if (!empty($invalid) && $invalid == count($emails)) {
                    $final_message = trans('trp.page.profile.invite.copypaste.all-not-sent').'<br/>'.implode(',', $invalid_emails);
                    $alert_color = 'warning';
                    $gtag_tracking = false;
                } else if(!empty($invalid) && $invalid != count($emails)) {
                    if (empty($already_invited)) {
                        $final_message = trans('trp.page.profile.invite.copypaste.unvalid-emails').'<br/>'.implode(',', $invalid_emails);
                        $alert_color = 'orange';
                    } else {
                        $final_message = trans('trp.page.profile.invite.copypaste.submitted-feedback').'<br/>'.implode(',', $invalid_emails).(count($already_invited_emails) ? ','.implode(',', $already_invited_emails) : '');
                        $alert_color = 'orange';
                    }
                } else if(!empty($already_invited) && ($already_invited == count($emails))) {
                    $final_message = trans('trp.page.profile.invite.copypaste.all-submitted-feedback').'<br/>'.implode(',', $already_invited_emails);
                    $alert_color = 'warning';
                    $gtag_tracking = false;
                } else if(!empty($already_invited) && $already_invited != count($emails)) {
                    $final_message = trans('trp.page.profile.invite.copypaste.submitted-feedback-invalid-emails').'<br/>'.implode(',', $invalid_emails).','.implode(',', $already_invited_emails);
                    $alert_color = 'orange';
                } else {
                    $final_message = trans('trp.page.profile.invite.success');
                    $alert_color = 'success';
                }

                foreach ($emails as $key => $email) {
                    $inviter_email = $this->user->email ? $this->user->email : $this->user->mainBranchEmail();
                    if(!empty($names[$key]) && ($email != $inviter_email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {

                        $send_mail = false;

                        $invitation = UserInvite::where([
                            ['user_id', $this->user->id],
                            ['invited_email', 'LIKE', $email],
                        ])->first();

                        $existing_patient = User::withTrashed()->where('email', 'LIKE', $email )->where('is_dentist', 0)->first();
                        $existing_anonymous = AnonymousUser::where('email', 'LIKE', $email)->first();

                        if(empty($existing_patient) || empty($existing_patient->deleted_at)) { ///da proveeq

                            if($invitation) {

                                if ($invitation->created_at->timestamp < Carbon::now()->subMonths(1)->timestamp) {

                                    $invitation->invited_name = $names[$key];
                                    $invitation->created_at = Carbon::now();

                                    if (empty($invitation->unsubscribed)) {
                                        $invitation->review = true;
                                        $invitation->completed = null;
                                        $invitation->notified1 = null;
                                        $invitation->notified2 = null;
                                        $invitation->notified3 = null;
                                    }
                                    if(!empty(Request::Input('invite_hubapp')) && $this->user->is_partner) {
                                        $invitation->for_dentist_patients = true;
                                    }
                                    $invitation->save();
                                    $send_mail = true;

                                    if(!empty($existing_patient)) {
                                        $last_ask = UserAsk::where('user_id', $existing_patient->id)->where('dentist_id', $this->user->id)->first();
                                        if(!empty($last_ask)) {
                                            $last_ask->created_at = Carbon::now();
                                            $last_ask->on_review = true;
                                            $last_ask->save();
                                        } else {
                                            $ask = new UserAsk;
                                            $ask->user_id = $existing_patient->id;
                                            $ask->dentist_id = $this->user->id;
                                            $ask->status = 'yes';
                                            $ask->on_review = true;
                                            $ask->save();
                                        }
                                    }
                                }
                            } else {

                                $valid_email = $this->user->sendgridEmailValidation(68, $email);

                                $invitation = new UserInvite;
                                $invitation->user_id = $this->user->id;
                                $invitation->invited_email = $email;
                                $invitation->invited_name = $names[$key];
                                $invitation->platform = 'trp';
                                $invitation->review = true;
                                if(!empty(Request::Input('invite_hubapp')) && $this->user->is_partner) {
                                    $invitation->for_dentist_patients = true;
                                }

                                if(!$valid_email) {
                                    $invitation->suspicious_email = true;
                                } else {
                                    $send_mail = true;
                                }

                                $invitation->save();

                            }

                            if ($send_mail) {
                                if(!empty($existing_patient)) {

                                    if(!empty(Request::Input('invite_hubapp')) && $this->user->is_partner) {
                                        $existing_patient->patient_of = $this->user->id;
                                        $existing_patient->save();
                                    }

                                    $substitutions = [
                                        'type' => $this->user->is_clinic ? 'dental clinic' : ($this->user->is_dentist ? 'your dentist' : ''),
                                        'inviting_user_name' => ($this->user->is_dentist && !$this->user->is_clinic && $this->user->title) ? config('titles')[$this->user->title].' '.$this->user->name : $this->user->name,
                                        'invited_user_name' => $names[$key],
                                        "invitation_link" => $this->user->getLink().'?'. http_build_query(['dcn-gateway-type'=>'patient-login', 'inviter' => User::encrypt($this->user->id), 'inviteid' => User::encrypt($invitation->id) ]),
                                    ];

                                    $existing_patient->sendGridTemplate(68, $substitutions, 'trp');

                                } else {
                                    $dentist_name = $this->user->name;

                                    if ( $this->user->is_dentist) {
                                        $unsubscribed = User::isUnsubscribedAnonymous(106, 'trp', $email);

                                        if(!empty($existing_anonymous)) {

                                            if(!$unsubscribed) {
                                                $subscribe_cats = $existing_anonymous->website_notifications;

                                                if(!isset($subscribe_cats['trp'])) {

                                                    $subscribe_cats[] = 'trp';
                                                    $existing_anonymous->website_notifications = $subscribe_cats;
                                                    $existing_anonymous->save();
                                                }
                                            }

                                        } else {
                                            $new_anonymous_user = new AnonymousUser;
                                            $new_anonymous_user->email = $email;
                                            $new_anonymous_user->website_notifications = ['trp'];
                                            $new_anonymous_user->save();
                                        }

                                        $substitutions = [
                                            'type' => $this->user->is_clinic ? 'dental clinic' : ($this->user->is_dentist ? 'your dentist' : ''),
                                            'inviting_user_name' => ($this->user->is_dentist && !$this->user->is_clinic && $this->user->title) ? config('titles')[$this->user->title].' '.$dentist_name : $dentist_name,
                                            'invited_user_name' => $names[$key],
                                            "invitation_link" => $this->user->getLink().'?'. http_build_query(['dcn-gateway-type'=>'patient-register', 'inviter' => User::encrypt($this->user->id), 'inviteid' => User::encrypt($invitation->id) ]),
                                        ];

                                        User::unregisteredSendGridTemplate($this->user, $email, $names[$key], 106, $substitutions, 'trp', $unsubscribed, $email);
                                    } else {

                                        $unsubscribed = User::isUnsubscribedAnonymous(17, 'trp', $email);

                                        if(!empty($existing_anonymous)) {

                                            if(!$unsubscribed) {
                                                $subscribe_cats = $existing_anonymous->website_notifications;

                                                if(!isset($subscribe_cats['trp'])) {

                                                    $subscribe_cats[] = 'trp';
                                                    $existing_anonymous->website_notifications = $subscribe_cats;
                                                    $existing_anonymous->save();
                                                }
                                            }

                                        } else {
                                            $new_anonymous_user = new AnonymousUser;
                                            $new_anonymous_user->email = $email;
                                            $new_anonymous_user->website_notifications = ['trp'];
                                            $new_anonymous_user->save();
                                        }

                                        $user = User::find(113928);
                                        $temp_email = $user->email;
                                        $user->email = $email;
                                        $user->save();

                                        $user->sendTemplate( 17 , [
                                            'friend_name' => $dentist_name,
                                            'invitation_id' => $invitation->id
                                        ], 'trp', $unsubscribed, $email);

                                        //Back to original
                                        $user->email = $temp_email;
                                        $user->save();
                                    }
                                }
                            }
                        }
                    }
                }

                session()->pull('bulk_names');
                session()->pull('bulk_emails');

                return Response::json([
                    'success' => true,
                    'message' => $final_message,
                    'color' => $alert_color,
                    'gtag_tracking' => $gtag_tracking,
                ] );
            } else {
                return Response::json([
                    'success' => false,
                    'message' => trans('trp.page.profile.invite.copypaste.failed'),
                ] );
            }
        }
    }

    /**
     * dentist invites patients from file step 1
     */
    public function invite_file($locale=null) {

        if(!empty($this->user) && $this->user->canInvite('trp') ) {

            $validator = Validator::make(request()->all(), [
                'invite-file' => array('required','file', 'mimes:txt,csv'),
            ]);

            $ret = [
                'success' => false
            ];
 
            if ($validator->fails()) {

                $ret['message'] = trans('trp.page.profile.invite.file.error');
                return Response::json($ret);

            } else {

                if (Input::file('invite-file')->getMimeType() == 'text/plain') {
                    $columns = explode(PHP_EOL, File::get(Input::file('invite-file')->path()));
                    $rows = [];

                    foreach ($columns as $column) {
                        if(!empty($column)) {
                            $rows[] = preg_split('/[\t,]/', $column);
                        }                        
                    }

                    if (count($rows[0]) <= 1) {
                        return Response::json([
                            'success' => false,
                            'message' => trans('trp.page.profile.invite.file.error'),
                        ] );
                    }

                    $reversedRows = [];
                    $maxcnt = 0;
                    foreach ($rows as $row) {
                        if(count($row) > $maxcnt) {
                            $maxcnt = count($row);
                        }
                    }

                    for($i=0;$i<$maxcnt; $i++) {
                        $reversedRows[$i] = [];
                    }

                    foreach ($rows as $row) {
                        for($i=0;$i<$maxcnt; $i++) {
                            $reversedRows[$i][] = isset($row[$i]) ? trim($row[$i]) : '';
                        }
                    }

                } else {
                    //not using
                    global $reversedRows;

                    $newName = '/tmp/'.str_replace(' ', '-', Input::file('invite-file')->getClientOriginalName());
                    copy( Input::file('invite-file')->path(), $newName );

                    $results = Excel::toArray(new Import, $newName );

                    if(!empty($results)) {

                        $reversedRows = [];
                        $maxcnt = 0;
                        if(!empty($results)) {
                            foreach ($results as $row) {
                                if(count($row) > $maxcnt) {
                                    $maxcnt = count($row);
                                }
                            }
                        }

                        for($i=0;$i<$maxcnt; $i++) {
                            $reversedRows[$i] = [];
                        }

                        foreach ($results as $key => $value) {
                            $results[$key] = array_values($value);
                        }

                        foreach ($results as $row) {
                            for($i=0;$i<$maxcnt; $i++) {
                                $reversedRows[$i][] = isset($row[$i]) ? trim($row[$i]) : '';
                            }
                        }
                    }

                    unlink($newName);
                }

                if (session('bulk_invites')) {
                    session()->pull('bulk_invites');
                }                
                session(['bulk_invites' => $reversedRows]);

                return Response::json([
                    'success' => true,
                    'info' => $reversedRows,
                ] ); 
            }
        }
    }

    /**
     * dentist invites patient again
     */
    public function invite_patient_again($locale=null) {
        $id = Request::input('id');

        if (!empty($this->user) && !empty($id)) {

            if(Request::isMethod('post') && $this->user->canInvite('trp') ) {

                $last_invite = UserInvite::find($id);
                $existing_patient = User::find($last_invite->invited_id);

                if (!empty($last_invite) && !empty($existing_patient)) {
                    
                    $last_invite->created_at = Carbon::now();
                    $last_invite->save();
                    
                    $last_ask = UserAsk::where('user_id', $existing_patient->id)->where('dentist_id', $this->user->id)->first();
                    if(!empty($last_ask)) {
                        $last_ask->created_at = Carbon::now();
                        $last_ask->on_review = true;
                        $last_ask->save();
                    } else {
                        $ask = new UserAsk;
                        $ask->user_id = $existing_patient->id;
                        $ask->dentist_id = $this->user->id;
                        $ask->status = 'yes';
                        $ask->on_review = true;
                        $ask->save();
                    }

                    $substitutions = [
                        'type' => $this->user->is_clinic ? 'dental clinic' : ($this->user->is_dentist ? 'your dentist' : ''),
                        'inviting_user_name' => ($this->user->is_dentist && !$this->user->is_clinic && $this->user->title) ? config('titles')[$this->user->title].' '.$this->user->name : $this->user->name,
                        'invited_user_name' => $last_invite->invited_name,
                        "invitation_link" => $this->user->getLink().'?'. http_build_query(['dcn-gateway-type'=>'patient-login', 'inviter' => User::encrypt($this->user->id) ]),
                    ];

                    $existing_patient->sendGridTemplate(68, $substitutions, 'trp');

                    return Response::json(['success' => true, 'url' => getLangUrl('/').'?tab=asks' ] );
                } else {
                    return Response::json(['success' => false ] );
                }
            }

            return redirect(getLangUrl('/'));
        }
    }

    /**
     * clinic invites team member
     */
    public function invite_team_member($locale=null) {
        
        if( (!empty($this->user) && $this->user->canInvite('trp')) || (empty($this->user) && !empty(request('last_user_id')) && !empty(User::find(request('last_user_id'))) && !empty(request('last_user_hash')) && request('last_user_hash') == User::find(request('last_user_id'))->get_token() )) {

            if (!empty($this->user)) {
                $current_user = $this->user;
            } else if(!empty(request('last_user_id'))) {
                $current_user = User::find(request('last_user_id'));

                if(empty($current_user)) {
                    return Response::json(['success' => false, 'message' => trans('trp.common.something-wrong') ] );
                }
            } else {
                return Response::json(['success' => false, 'message' => trans('trp.common.something-wrong') ] );
            }

            $validator = Validator::make(Request::all(), [
                'name' => ['required', 'string'],
                'team-job' => ['required'],
            ]);

            if ($validator->fails()) {
                return Response::json(['success' => false, 'message' => trans('trp.popup.verification-popup.clinic.team-name-error') ] );
            } else {
                if (Request::input('team-job') != 'dentist') {
                    $invitation = new UserInvite;
                    $invitation->user_id = $current_user->id;
                    $invitation->invited_email = ' ';
                    $invitation->invited_name = Request::Input('name');
                    $invitation->job = Request::Input('team-job');
                    $invitation->join_clinic = true;
                    $invitation->for_team = true;
                    $invitation->platform = 'trp';
                    $invitation->save();

                    if( Request::input('photo') ) {
                        $img = Image::make( User::getTempImagePath( Request::input('photo') ) )->orientate();
                        $invitation->addImage($img);
                    }

                    return Response::json(['success' => true, 'message' => trans('trp.popup.verification-popup.clinic.no-email-success', ['name' => Request::Input('name') ]) ] );
                } else {

                    if(empty(Request::input('check-for-same'))) {
                        $username = Request::Input('name');
                        $team_ids = [];

                        if( $current_user->team->isNotEmpty()) {

                            foreach ($current_user->team as $t) {
                                $team_ids[] = $t->dentist_id;
                            }
                        }

                        $dentists_with_same_name = User::where('is_dentist', true)->where('is_clinic', '!=', 1)->where(function($query) use ($username) {
                            $query->where('name', 'LIKE', $username)
                            ->orWhere('name_alternative', 'LIKE', $username);
                        })->whereIn('status', config('dentist-statuses.dentist_approved'))
                        ->whereNull('self_deleted');

                        if (!empty($team_ids)) {
                            $dentists_with_same_name = $dentists_with_same_name->whereNotIn('id', $team_ids)->get();
                        } else {
                            $dentists_with_same_name = $dentists_with_same_name->get();
                        }

                        if($dentists_with_same_name->isNotEmpty()) {
                            
                            foreach ($dentists_with_same_name as $same_dentist) {
                                $user_list[] = [
                                    'name' => $same_dentist->getNames().( $same_dentist->name_alternative && mb_strtolower($same_dentist->name)!=mb_strtolower($same_dentist->name_alternative) ? ' / '.$same_dentist->name_alternative : '' ),
                                    'id' => $same_dentist->id,
                                    'avatar' => $same_dentist->getImageUrl(),
                                    'location' => !empty($same_dentist->country) ? $same_dentist->city_name.', '.$same_dentist->country->name : '',
                                ];
                            }

                            return Response::json( [
                                'success' => true,
                                'dentists' => $user_list
                            ] );
                        }
                    }

                    if(!empty(Request::input('email'))) {

                        if (!filter_var(Request::input('email'), FILTER_VALIDATE_EMAIL)) {
                            return Response::json(['success' => false, 'message' => trans('trp.popup.verification-popup.clinic.invalid-email-error') ] );
                        }

                        $valid_email = $this->user->sendgridEmailValidation(92, Request::input('email'));

                        if(!$valid_email) {
                            return Response::json(['success' => false, 'message' => 'Not sent - suspicious email address.' ] );
                        }

                        $existing_dentist = User::where('email', 'LIKE', Request::input('email'))->withTrashed()->first();

                        if( !empty($existing_dentist)) {

                            if(!$existing_dentist->is_dentist) {
                                return Response::json(['success' => false, 'message' => trans('trp.popup.verification-popup.clinic.invite-patient-error') ] );
                            }

                            if($existing_dentist->is_clinic) {
                                return Response::json(['success' => false, 'message' => trans('trp.popup.verification-popup.clinic.invite-clinic-error') ] );
                            }

                            $existing_team = UserTeam::where('user_id', $current_user->id)->where('dentist_id', $existing_dentist->id )->first();

                            if(!empty($existing_team)) {
                                return Response::json(['success' => false, 'message' => trans('trp.popup.verification-popup.clinic.existing-team-error') ] );

                            } else if(empty($existing_dentist->self_deleted) && empty($existing_dentist->deleted_at) && ($existing_dentist->status == 'approved' || $existing_dentist->status == 'added_by_clinic_claimed' || $existing_dentist->status == 'added_by_clinic_unclaimed' || $existing_dentist->status == 'test' || $existing_dentist->status == 'added_approved' || $existing_dentist->status == 'added_new' || $existing_dentist->status == 'admin_imported' || $existing_dentist->status == 'added_by_clinic_new') ) {

                                $newteam = new UserTeam;
                                $newteam->dentist_id = $existing_dentist->id;
                                $newteam->user_id = $current_user->id;
                                $newteam->approved = 1;
                                $newteam->save();

                                if(!empty($this->user) && ($existing_dentist->status == 'approved' || $existing_dentist->status == 'added_by_clinic_claimed' || $existing_dentist->status == 'test')) {

                                    $existing_dentist->sendTemplate(33, [
                                        'clinic-name' => $this->user->getNames(),
                                        'clinic-link' => $this->user->getLink()
                                    ], 'trp');
                                }

                                if($existing_dentist->status == 'test') {
                                    $mtext = 'Clinic '.$current_user->getNames().' added a new team member that is with status Test.
                                    Link to dentist\'s profile:
                                    '.url('https://reviews.dentacoin.com/cms/users/edit/'.$existing_dentist->id).'
                                    Link to clinic\'s profile: 
                                    '.url('https://reviews.dentacoin.com/cms/users/edit/'.$current_user->id).'
                                    '.(!empty(Auth::guard('admin')->user()) ? 'This is a Dentacoin ADMIN' : '').'
                                    ';

                                    Mail::raw($mtext, function ($message) use ($current_user) {

                                        $sender = config('mail.from.address');
                                        $sender_name = config('mail.from.name');

                                        $message->from($sender, $sender_name);
                                        $message->to( 'petya.ivanova@dentacoin.com' );
                                        $message->to( 'donika.kraeva@dentacoin.com' );
                                        $message->to( 'betina.bogdanova@dentacoin.com' );
                                        $message->subject('Clinic '.$current_user->getNames().' added a new team member that is with status Test');
                                    });
                                }

                                return Response::json(['success' => true, 'message' => trans('trp.popup.verification-popup.clinic.success') ] );
                            } else if(empty($existing_dentist->self_deleted) && empty($existing_dentist->deleted_at) && ($existing_dentist->status == 'new') ) {

                                if (!empty($this->user)) {
                                    $user_history = new UserHistory;
                                    $user_history->user_id = $existing_dentist->id;
                                    $user_history->status = $existing_dentist->status;
                                    $user_history->save();

                                    $existing_dentist->status = 'added_by_clinic_claimed';
                                    $existing_dentist->slug = $existing_dentist->makeSlug();
                                    $existing_dentist->save();

                                    $existing_dentist->sendGridTemplate(26, [], 'trp');

                                    $newteam = new UserTeam;
                                    $newteam->dentist_id = $existing_dentist->id;
                                    $newteam->user_id = $current_user->id;
                                    $newteam->approved = 1;
                                    $newteam->save();

                                    $existing_dentist->sendTemplate(33, [
                                        'clinic-name' => $this->user->getNames(),
                                        'clinic-link' => $this->user->getLink()
                                    ], 'trp');
                                } else {

                                    $newteam = new UserTeam;
                                    $newteam->dentist_id = $existing_dentist->id;
                                    $newteam->user_id = $current_user->id;
                                    $newteam->approved = 0;
                                    $newteam->new_clinic = 1;
                                    
                                    $newteam->save();
                                }

                                return Response::json(['success' => true, 'message' => trans('trp.popup.verification-popup.clinic.success') ] );

                            } else if(!empty($existing_dentist->self_deleted) || !empty($existing_dentist->deleted_at) || $existing_dentist->status == 'rejected' || $existing_dentist->status == 'added_by_clinic_rejected' || $existing_dentist->status == 'added_rejected' || $existing_dentist->status == 'pending') {

                                $mtext = 'Clinic '.$current_user->getNames().' added a new team member that is deleted OR with status rejected/suspicious. Link to dentist\'s profile:
                                '.url('https://reviews.dentacoin.com/cms/users/edit/'.$existing_dentist->id).'
                                Link to clinic\'s profile: 
                                '.url('https://reviews.dentacoin.com/cms/users/edit/'.$current_user->id).'
                                '.(!empty(Auth::guard('admin')->user()) ? 'This is a Dentacoin ADMIN' : '').'
                                ';

                                Mail::raw($mtext, function ($message) use ($current_user) {

                                    $sender = config('mail.from.address');
                                    $sender_name = config('mail.from.name');

                                    $message->from($sender, $sender_name);
                                    $message->to( 'petya.ivanova@dentacoin.com' );
                                    $message->to( 'donika.kraeva@dentacoin.com' );
                                    $message->to( 'betina.bogdanova@dentacoin.com' );
                                    $message->subject('Clinic '.$current_user->getNames().' added a new team member that is deleted OR with status rejected/suspicious');
                                });

                                return Response::json(['success' => false, 'message' => trans('trp.popup.verification-popup.clinic.suspicious-email-error') ] );
                            }
                        }

                        $newuser = new User;
                        $newuser->name = Request::input('name');
                        $newuser->email = Request::input('email');
                        $newuser->status = !empty($this->user) ? 'added_by_clinic_unclaimed' : 'added_by_clinic_new';
                        $newuser->country_id = $current_user->country_id;
                        $newuser->address = $current_user->address;

                    } else {
                        $newuser = new User;
                        $newuser->name = Request::input('name');
                        $newuser->status = 'dentist_no_email';
                    }

                    $newuser->platform = 'trp';                    
                    $newuser->gdpr_privacy = true;
                    $newuser->is_dentist = 1;
                    $newuser->invited_by = $current_user->id;
                    $newuser->save();

                    if( Request::input('photo') ) {
                        $img = Image::make( User::getTempImagePath( Request::input('photo') ) )->orientate();
                        $newuser->addImage($img);
                    }
                    
                    $newteam = new UserTeam;
                    $newteam->dentist_id = $newuser->id;
                    $newteam->user_id = $current_user->id;
                    $newteam->approved = 1;
                    $newteam->new_clinic = 1;
                    $newteam->save();

                    if(!empty(Request::input('email'))) {

                        if(!empty($this->user)) {

                            $newuser->slug = $newuser->makeSlug();
                            $newuser->save();

                            $newuser->sendGridTemplate( 92 , [
                                'clinic_name' => $current_user->getNames(),
                                "invitation_link" => getLangUrl( 'dentist/'.$newuser->slug.'/claim/'.$newuser->id).'?'. http_build_query(['popup'=>'claim-popup']).'&without-info=true',
                            ], 'trp');
                        }

                        $mtext = 'Clinic '.$current_user->getNames().' added a new team member. Link to profile:
                        '.(!empty(Auth::guard('admin')->user()) ? 'This is a Dentacoin ADMIN' : '').'
                        '.url('https://reviews.dentacoin.com/cms/users/edit/'.$newuser->id).'

                        ';

                        Mail::raw($mtext, function ($message) use ( $current_user) {

                            $sender = config('mail.from.address');
                            $sender_name = config('mail.from.name');

                            $message->from($sender, $sender_name);
                            $message->to( 'betina.bogdanova@dentacoin.com' );
                            $message->subject('Clinic '.$current_user->getNames().' added a new team member');
                        });
                    }
                }
            }

            return Response::json(['success' => true, 'message' => trans('trp.popup.verification-popup.clinic.success'), 'with_email' => ($newuser->status == 'dentist_no_email' ? false : true) ] );
        }
        
        return Response::json(['success' => false, 'message' => trans('trp.common.something-wrong') ] );
    }

    /**
     * clinic invites existing user as team member
     */
    public function invite_existing_team_member($locale=null) {

        if((!empty($this->user) && $this->user->canInvite('trp')) || (empty($this->user) && !empty(request('ex_d_id')) && !empty(User::find(request('ex_d_id')) ) && !empty(request('clinic_id')) && !empty(User::find(request('clinic_id')) ))) {

            $newteam = new UserTeam;
            $newteam->dentist_id = request('ex_d_id');
            $newteam->user_id = !empty($this->user) ? $this->user->id : request('clinic_id');
            $newteam->approved = empty($this->user) ? 0 : 1;
            if(empty($this->user)) {
                $newteam->new_clinic = 1;
            }
            
            $newteam->save();
            
            $dentist = User::find(request('ex_d_id'));

            if(!empty($this->user) && ($dentist->status == 'approved' || $dentist->status == 'added_by_clinic_claimed' ) ) {

                $dentist->sendTemplate(33, [
                    'clinic-name' => $this->user->getNames(),
                    'clinic-link' => $this->user->getLink()
                ], 'trp');
            }

            return Response::json(['success' => true, 'message' => trans('trp.popup.verification-popup.dentist-invite.success', ['dentist-name' => $dentist->getNames()])] );
        }
        
        return Response::json(['success' => false, 'message' => trans('trp.common.something-wrong') ] );
    }

    /**
     * dentist uploads a profile photo
     */
    public function upload($locale=null) {
        if(empty($this->user) || !$this->user->is_dentist || ($this->user->is_dentist && !in_array($this->user->status, config('dentist-statuses.approved_test')))) {
            return Response::json(['success' => false ]);
        }

        if( Request::file('image') && Request::file('image')->isValid() ) {
            $img = Image::make( Input::file('image') )->orientate();
            $this->user->addImage($img);

            $this->user->hasimage_social = false;
            $this->user->save();

            foreach ($this->user->reviews_out as $review_out) {
                $review_out->hasimage_social = false;
                $review_out->save();
            }

            foreach ($this->user->reviews_in_dentist as $review_in_dentist) {
                $review_in_dentist->hasimage_social = false;
                $review_in_dentist->save();
            }

            foreach ($this->user->reviews_in_clinic as $review_in_clinic) {
                $review_in_clinic->hasimage_social = false;
                $review_in_clinic->save();
            }

            return Response::json(['success' => true, 'thumb' => $this->user->getImageUrl(true), 'name' => '' ]);
        }
    }
    

    /**
     * dentist profile edit
     */
    public function info($locale=null) {

        if(empty($this->user) || !$this->user->is_dentist || ($this->user->is_dentist && !in_array($this->user->status, config('dentist-statuses.approved_test')))) {
            if( Request::input('json') ) {
                $ret = [
                    'success' => false,
                ];
                return Response::json($ret);
            }
            return redirect(getLangUrl('/'));
        }

        $validator_arr = [];
        foreach ($this->profile_fields as $key => $value) {
            if( Request::input('field') && $key!=Request::input('field') ) {
                continue;
            }

            $arr = [];
            if (!empty($value['required'])) {
                $arr[] = 'required';
            }
            if (!empty($value['is_email'])) {
                $arr[] = 'email';
                $arr[] = 'unique:users,email,'.$this->user->id;
            }
            if (!empty($value['min'])) {
                $arr[] = 'min:'.$value['min'];
            }
            if (!empty($value['max'])) {
                $arr[] = 'max:'.$value['max'];
            }
            if (!empty($value['number'])) {
                $arr[] = 'numeric';
            }
            if (!empty($value['array'])) {
                $arr[] = 'array';
            }
            if (!empty($value['values'])) {
                $arr[] = 'in:'.implode(',', array_keys($value['values']) );
            }
            if (!empty($value['regex'])) {
                $arr[] = $value['regex'];
            }

            if (!empty($arr)) {
                $validator_arr[$key] = $arr;
            }
        }

        if (request('website') && mb_strpos(mb_strtolower(request('website')), 'http') !== 0) {
            request()->merge([
                'website' => 'http://'.request('website')
            ]);
        }

        $validator = Validator::make(Request::all(), $validator_arr);

        if ($validator->fails()) {

            if( Request::input('json') ) {
                $ret = [
                    'success' => false
                ];

                $msg = $validator->getMessageBag()->toArray();
                $ret['messages'] = [];
                foreach ($msg as $field => $errors) {
                    $ret['messages'][$field] = implode(', ', $errors);
                }
                return Response::json($ret);
            }

            return redirect( getLangUrl('/') )
            ->withInput()
            ->withErrors($validator);
        } else {

            if(is_numeric(request('country_id')) && empty(Request::input('field')) && $this->user->is_dentist && !User::validateAddress( $this->user->country_id, request('address') ) ) {
                if( Request::input('json') ) {
                    $ret = [
                        'success' => false,
                        'messages' => [
                            'address' => trans('trp.common.invalid-address')
                        ]
                    ];
                    return Response::json($ret);
                }

                return redirect( getLangUrl('/') )
                ->withInput()
                ->withErrors([
                    'address' => trans('trp.common.invalid-address')
                ]);
            }

            if (!empty(Request::input('description')) && mb_strlen(Request::input('description')) > 512) {
                $ret = [
                    'success' => false,
                    'messages' => [
                        'description' => trans('trp.common.invalid-description')
                    ]
                ];
                return Response::json($ret);
            }

            if (!empty(Request::input('short_description')) && mb_strlen(json_encode(Request::input('short_description'))) > 150) {
                $ret = [
                    'success' => false,
                    'messages' => [
                        'short_description' => trans('trp.common.invalid-short-description')
                    ]
                ];
                return Response::json($ret);
            }

            if(!empty(Request::input('name')) && (User::validateLatin(Request::input('name')) == false)) {
                if( Request::input('json') ) {
                    $ret = [
                        'success' => false,
                        'messages' => [
                            'name' => trans('trp.common.invalid-name')
                        ]
                    ];
                    return Response::json($ret);
                }

                return redirect( getLangUrl('/') )
                ->withInput()
                ->withErrors([
                    'name' => trans('trp.common.invalid-name')
                ]);
            }

            // if($this->user->validateMyEmail() == true) {

            //     if( Request::input('json') ) {
            //         $ret = [
            //             'success' => false,
            //             'messages' => [
            //                 'email' => trans('trp.common.invalid-email')
            //             ]
            //         ];
            //         return Response::json($ret);
            //     }

            //     return redirect( getLangUrl('/') )
            //     ->withInput()
            //     ->withErrors([
            //         'email' => trans('trp.common.invalid-email')
            //     ]);
            // }

            foreach ($this->profile_fields as $key => $value) {
                if( Request::exists($key) || (Request::input('field')=='specialization' && $key=='specialization') || $key=='email_public' || (Request::input('field')=='accepted_payment' && $key=='accepted_payment') ) {

                    if($key=='work_hours') {
                        $wh = Request::input('work_hours');

                        foreach ($wh as $k => $v) {
                            if( empty($wh[$k][0][0]) || empty($wh[$k][0][1]) || empty($wh[$k][1][0]) || empty($wh[$k][1][1]) || empty(Request::input('day-'.$k))) { 
                                unset($wh[$k]);
                                continue;
                            }

                            if( !empty($wh[$k][0]) && !empty(Request::input('day-'.$k))) {
                                $wh[$k][0] = implode(':', $wh[$k][0]);
                            }
                            if( !empty($wh[$k][1]) && !empty(Request::input('day-'.$k)) ) {
                                $wh[$k][1] = implode(':', $wh[$k][1]);
                            }
                        }
                        $this->user->$key = $wh;
                    } else if($value['type']=='specialization') {
                        UserCategory::where('user_id', $this->user->id)->delete();
                        if(!empty(Request::input('specialization'))) {
                            foreach (Request::input('specialization') as $cat) {
                                $newc = new UserCategory;
                                $newc->user_id = $this->user->id;
                                $newc->category_id = $cat;
                                $newc->save();
                            }
                        }
                    } else {
                        $this->user->$key = Request::input($key);
                    }
                }
            }

            $this->user->hasimage_social = false;
            $this->user->save();

            foreach ($this->user->reviews_out as $review_out) {
                $review_out->hasimage_social = false;
                $review_out->save();
            }

            foreach ($this->user->reviews_in_dentist as $review_in_dentist) {
                $review_in_dentist->hasimage_social = false;
                $review_in_dentist->save();
            }

            foreach ($this->user->reviews_in_clinic as $review_in_clinic) {
                $review_in_clinic->hasimage_social = false;
                $review_in_clinic->save();
            }
            
            if( Request::input('json') ) {
                $ret = [
                    'success' => true,
                    'href' => getLangUrl('/')
                ];

                if( Request::input('field') ) {
                    if( Request::input('field')=='specialization' ) {
                        $ret['value'] = implode(', ', $this->user->parseCategories( $this->categories ));
                    } else if( Request::input('field')=='work_hours' ) {
                        $ret['value'] = strip_tags( $this->user->getWorkHoursText() );
                    } else if( Request::input('field')=='accepted_payment' ) {
                        $ret['value'] = $this->user->parseAcceptedPayment( $this->user->accepted_payment );
                    } else {
                        $ret['value'] = nl2br($this->user[ Request::input('field') ]) ;                            
                    }
                }
                return Response::json($ret);
            }

            Request::session()->flash('success-message', trans('trp.page.profile.info.updated'));
            return redirect( getLangUrl('/') );
        }
    }

    /**
     * dentist verifies patient
     */
    public function asks_accept($locale=null, $ask_id) {
        if(empty($this->user) || !$this->user->is_dentist || ($this->user->is_dentist && !in_array($this->user->status, config('dentist-statuses.approved_test')))) {
            return redirect(getLangUrl('/'));
        }

        $ask = UserAsk::find($ask_id);
        if(!empty($ask) && $ask->dentist_id==$this->user->id && $ask->status=='waiting') {
            $ask->status = 'yes';
            $ask->save();

            $last_invite = UserInvite::where('user_id', $this->user->id)->where('invited_id', $ask->user->id)->first();
            if (!empty($last_invite)) {
                $last_invite->created_at = Carbon::now();
                $last_invite->rewarded = true;
                $last_invite->save();   
            } else {
                $inv = new UserInvite;
                $inv->user_id = $this->user->id;
                $inv->invited_email = $ask->user->email;
                $inv->invited_name = $ask->user->name;
                $inv->invited_id = $ask->user->id;
                $inv->platform = 'trp';
                $inv->rewarded = true;
                $inv->save();                    
            }

            if ($ask->on_review) {
                $ask->user->sendTemplate( $ask->on_review ? 64 : 24 ,[
                    'dentist_name' => $this->user->getNames(),
                    'dentist_link' => $this->user->getLink(),
                ], 'trp');
            }

            $d_id = $this->user->id;
            $reviews = Review::where(function($query) use ($d_id) {
                $query->where( 'dentist_id', $d_id)->orWhere('clinic_id', $d_id);
            })->where('user_id', $ask->user->id)
            ->get();

            if ($reviews->count()) {
                
                foreach ($reviews as $review) {

                    if(empty($review->verified)) {

                        $review->verified = true;
                        $review->save();

                        $reward = new DcnReward();
                        $reward->user_id = $ask->user->id;
                        $reward->platform = 'trp';
                        $reward->reward = Reward::getReward('review_trusted');
                        $reward->type = 'review_trusted';
                        $reward->reference_id = null;

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
                }
            }
        }
        
        Request::session()->flash('success-message', trans('trp.page.profile.asks.accepted'));
        return redirect( getLangUrl('/').'?tab=asks');
    }

    /**
     * dentist denies patient's request
     */
    public function asks_deny($locale=null, $ask_id) {
        if(empty($this->user) || !$this->user->is_dentist || ($this->user->is_dentist && !in_array($this->user->status, config('dentist-statuses.approved_test')))) {
            return redirect(getLangUrl('/'));
        }

        $ask = UserAsk::find($ask_id);
        if(!empty($ask) && $ask->dentist_id==$this->user->id && $ask->status=='waiting') {
            $ask->status = 'no';
            $ask->save();
        }
        
        Request::session()->flash('success-message', trans('trp.page.profile.asks.denied'));
        return redirect( getLangUrl('/').'?tab=asks');
    }
    
    /**
     * section TRP in accont.dentacoin.com
     */
    public function trp($locale=null) {
        if(!empty($this->user)) {
            
            $params = [
                'is_dentist' => $this->user->is_dentist,
                'xframe' => true,
                'reviews' => $this->user->is_dentist ? $this->user->reviews_in() : $this->user->reviews_out,
                'css' => [
                    'common-profile.css',
                    'trp-users.css'
                ],
                'js' => [
                    'profile.js',
                ],
                'csscdn' => [
                    'https://fonts.googleapis.com/css?family=Lato:700&display=swap&subset=latin-ext',
                ],
            ];

            if ($this->user->isBanned('trp')) {
                $params['current_ban'] = true;
            }

            $path = explode('/', request()->path())[2];
            if ($path == 'trp-iframe') {
                $params['skipSSO'] = true;
            }

            return $this->ShowView('profile-trp', $params);
        }
    }

    /**
     * dentist adds a gallery photos
     */
    public function gallery($locale=null, $position=null) {
        if( Request::file('image') && Request::file('image')->isValid() ) {
            $dapic = new UserPhoto;
            $dapic->user_id = $this->user->id;
            $dapic->save();
            $img = Image::make( Input::file('image') )->orientate();
            $dapic->addImage($img);
            return Response::json([
                'success' => true,
                'url' => $dapic->getImageUrl(true),
                'original' => $dapic->getImageUrl(),
            ]);
        }
        $ret = [
            'success' => true
        ];
        return Response::json( $ret );
    }    

    /**
     * dentist deletes a gallery photo
     */
    public function gallery_delete($locale=null, $id) {
        UserPhoto::destroy($id);

        return Response::json( [
            'success' => true,
        ] );
    }

    /**
     * clinic removes team members
     */
    public function dentists_delete( $locale=null, $id ) {
        $dentist = User::find( $id );
        $clinic = $this->user;

        if($dentist->status=='dentist_no_email') {
            $action = new UserAction;
            $action->user_id = $dentist->id;
            $action->action = 'deleted';
            $action->reason = 'Automatically - Clinic '.$clinic->getNames().' remove from team this dentist with no email';
            $action->actioned_at = Carbon::now();
            $action->save();

            $dentist->deleteActions();
            User::destroy( $dentist->id );
        }

        $res = UserTeam::where('user_id', $this->user->id)->where('dentist_id', $id)->delete();
        
        return Response::json( [
            'success' => true,
        ] );
    }

    /**
     * clinic rejects team member
     */
    public function dentists_reject( $locale=null, $id ) {

        $res = UserTeam::where('user_id', $this->user->id)->where('dentist_id', $id)->delete();

        if( $res ) {
            $dentist = User::find( $id );

            $dentist->sendTemplate(36, [
                'clinic-name' => $this->user->getNames()
            ], 'trp');
        }
        
        return Response::json( [
            'success' => true,
        ] );
    }

    /**
     * clinic accepts team member
     */
    public function dentists_accept( $locale=null, $id ) {

        $item = UserTeam::where('dentist_id', $id)->where('user_id', $this->user->id)->first();

        if ($item) {
            
            $item->approved = 1;
            $item->save();

            $dentist = User::find( $id );

            $dentist->sendTemplate(35, [
                'clinic-name' => $this->user->getNames()
            ], 'trp');
        }

        return Response::json( [
            'success' => true,
        ] );
    }
    
    /**
     * clinic accepts team member
     */
    public function clinics_delete( $locale=null, $id ) {
        $res = UserTeam::where('dentist_id', $this->user->id)->where('user_id', $id)->delete();

        if( $res ) {
            $clinic = User::find( $id );

            $clinic->sendTemplate(38, [
                'dentist-name' => $this->user->getNames()
            ], 'trp');
        }

        return Response::json( [
            'success' => true,
        ] );
    }

    /**
     * dentist sends a request for clinic team
     */
    public function inviteClinic() {

        if(!empty(Request::input('joinclinicid'))) {

            $clinic = User::find( Request::input('joinclinicid') );
            if(!empty($clinic)) {

                $newclinic = new UserTeam;
                $newclinic->dentist_id = $this->user->id;
                $newclinic->user_id = Request::input('joinclinicid');
                $newclinic->approved = 0;
                $newclinic->save();

                $clinic->sendTemplate(34, [
                    'dentist-name' =>$this->user->getNames()
                ], 'trp');

                return Response::json( [
                    'success' => true,
                    'message' => trans('trp.page.user.clinic-invited', ['name' => $clinic->getNames() ])
                ] );
            }
        } 
            
        return Response::json( [
            'success' => false,
            'message' => trans('trp.page.user.clinic-invited-error')
        ] );
    }

    /**
     * clinic adds an existing dentist to team
     */
    public function inviteDentist() {

        if(!empty(Request::input('invitedentistid')) && (!empty($this->user) || !empty(Request::input('user_id'))) ) {

            $dentist = User::find( Request::input('invitedentistid') );

            if(!empty($dentist)) {
                $user = !empty($this->user) ? $this->user : (!empty(Request::input('user_id')) && !empty(User::find(Request::input('user_id'))) ? User::find(Request::input('user_id')) : '');

                if(!empty($user)) {
                    $newdentist = new UserTeam;
                    $newdentist->dentist_id = Request::input('invitedentistid');
                    $newdentist->user_id = $user->id;
                    $newdentist->approved = !empty($this->user) ? 1 : 0;
                    $newdentist->new_clinic = !empty($this->user) ? 0 : 1;
                    $newdentist->save();

                    if(!empty($this->user)) {                        
                        $dentist->sendTemplate(33, [
                            'clinic-name' => $this->user->getNames(),
                            'clinic-link' => $this->user->getLink()
                        ], 'trp');
                    }

                    return Response::json( [
                        'success' => true,
                        'message' => trans('trp.page.user.dentist-invited', ['name' => $dentist->getNames() ])
                    ] );
                }
            }
        }

        return Response::json( [
            'success' => false,
            'message' => trans('trp.page.user.dentist-invited-error')
        ] );
    }

    /**
     * clinic deletes invite as his team member
     */
    public function invites_delete( $locale=null, $id ) {

        UserInvite::destroy($id);
        return Response::json( [
            'success' => true,
        ] );
    }

    /**
     * dentist's strength scale action - check assurance.dentacoin.com
     */
    public function checkAssurance( $locale=null ) {

        if(!empty($this->user) && $this->user->is_dentist) {
            $gt = UserGuidedTour::where('user_id', $this->user->id)->first();

            if(!empty($gt)) {

                $gt->dcn_assurance = true;
                $gt->save();

                return Response::json( [
                    'success' => true,
                ] );
            }
        }
        return redirect(getLangUrl('/'));
    }

    /**
     * dentist's strength scale action - check dentacare.dentacoin.com
     */
    public function checkDentacare( $locale=null ) {

        if(!empty($this->user) && $this->user->is_dentist) {
            $gt = UserGuidedTour::where('user_id', $this->user->id)->first();

            if(!empty($gt)) {

                $gt->dentacare_app = true;
                $gt->save();

                return Response::json( [
                    'success' => true,
                ] );
            }
        }
        return redirect(getLangUrl('/'));
    }

    /**
     * dentist's strength scale action - check reviews
     */
    public function checkReviews( $locale=null ) {

        if(!empty($this->user) && $this->user->is_dentist) {
            $gt = UserGuidedTour::where('user_id', $this->user->id)->first();

            if(!empty($gt)) {

                $gt->check_reviews_on = Carbon::now();
                $gt->save();

                return Response::json( [
                    'success' => true,
                ] );
            }
        }
        return redirect(getLangUrl('/'));
    }

    /**
     * starts guided tour after dentist's registration
     */
    public function firstGuidedTour($locale=null) {
        session()->pull('first_guided_tour');

        if(!empty($this->user) && $this->user->is_dentist) {

            if(empty(session('guided_tour'))) {

                $arr=[];

                if(empty($this->user->work_hours) || empty($this->user->socials)) {
                    $arr[] = [
                        'action' => 'edit',
                        'title' => trans('trp.guided-tour.first.edit.title'),
                        'description' => trans('trp.guided-tour.first.edit.description'),
                        'skip' => false,
                    ];

                    if(empty($this->user->work_hours)) {
                        $arr[] = [
                            'action' => 'work_hours',
                            'title' => trans('trp.guided-tour.first.work-hours.title'),
                            'description' => trans('trp.guided-tour.first.work-hours.description'),
                            'skip' => true,
                            'skip_text' => trans('trp.guided-tour.skip-step'),
                        ];
                    }

                    if(empty($this->user->socials)) {
                        $arr[] = [
                            'action' => 'socials',
                            'title' => trans('trp.guided-tour.first.socials.title'),
                            'description' => trans('trp.guided-tour.first.socials.description'),
                            'skip' => true,
                            'skip_text' => strtoupper(trans('trp.guided-tour.ok')),
                            'is_button' => true
                        ];
                    }

                    $arr[] = [
                        'action' => 'save',
                        'title' => trans('trp.guided-tour.first.save.title'),
                        'description' => trans('trp.guided-tour.first.save.description'),
                        'skip' => false,
                    ];
                }

                if(!empty(Request::input('full'))) {
                    $arr[] = [
                        'action' => 'invite',
                        'title' => trans('trp.guided-tour.first.invite.title'),
                        'description' => trans('trp.guided-tour.first.invite.description'),
                        'skip' => true,
                        'skip_text' => trans('trp.guided-tour.skip-step'),
                    ];
                }

                if(empty($this->user->description)) {
                    $arr[] = [
                        'action' => 'description',
                        'title' => trans('trp.guided-tour.first.description.title'),
                        'description' => trans('trp.guided-tour.first.description.description'),
                        'skip' => true,
                        'skip_text' => trans('trp.guided-tour.skip-step'),
                    ];
                }

                if($this->user->photos->isEmpty()) {
                    $arr[] = [
                        'action' => 'photos',
                        'title' => trans('trp.guided-tour.first.photos.title'),
                        'description' => trans('trp.guided-tour.first.photos.description'),
                        'skip' => true,
                        'skip_text' => trans('trp.guided-tour.skip-step'),
                    ];
                }

                if(!empty($this->user->is_clinic) && ($this->user->team->isEmpty() || $this->user->invites_team_unverified->isEmpty() )) {

                    $arr[] = [
                        'action' => 'team',
                        'title' => trans('trp.guided-tour.first.team.title'),
                        'description' => trans('trp.guided-tour.first.team.description'),
                        'skip' => true,
                        'skip_text' => trans('trp.guided-tour.skip-step'),
                    ];
                }

                session(['guided_tour_count' => count($arr)]);
                session(['guided_tour' => $arr]);
            }

            return Response::json([
                'success' => true,
                'steps' => session('guided_tour'),
                'count_all_steps' => session('guided_tour_count'),
            ] );
        }

        return Response::json([
            'success' => false,
        ]);
    }

    /**
     * remove first guided tour
     */
    public function removeFirstGuidedTour($locale=null) {

        session()->pull('guided_tour');
        session()->pull('guided_tour_count');

        return Response::json([
            'success' => false,
        ]);
    }

    /**
     * remove reviews guided tour
     */
    public function removeReviewsGuidedTour($locale=null) {

        session()->pull('reviews_guided_tour');

        return Response::json([
            'success' => false,
        ]);
    }

    /**
     * guided tour after first dentist's review
     */
    public function reviewsGuidedTour($locale=null, $layout=null) {
        session()->pull('reviews_guided_tour');
        
        if(!empty($this->user) && $this->user->is_dentist && $this->user->reviews_in_standard()->isNotEmpty()) {

            $arr = [
                [
                    'action' => 'add',
                    'title' => trans('trp.guided-tour.reviews.add.title'),
                    'description' => trans('trp.guided-tour.reviews.add.description'),
                ],
                [
                    'action' => 'layout',
                    'title' => trans('trp.guided-tour.reviews.layout.title'),
                    'description' => trans('trp.guided-tour.reviews.layout.description'),
                ],
            ];

            if($layout && ($layout == 'list' || $layout == 'carousel')) {

                $arr[] = [
                    'action' => 'reviews_type',
                    'title' => trans('trp.guided-tour.reviews.reviews-type.title'),
                    'description' => trans('trp.guided-tour.reviews.reviews-type.description'),
                    'skip' => true,
                    'skip_text' => trans('trp.guided-tour.ok'),
                ];

                $arr[] = [
                    'action' => 'copy',
                    'title' => trans('trp.guided-tour.reviews.copy.title'),
                    'description' => trans('trp.guided-tour.reviews.copy.description'),
                ];
            }

            if($layout && $layout == 'badge') {

                $arr[] = [
                    'action' => 'copy',
                    'title' => trans('trp.guided-tour.reviews.copy.title'),
                    'description' => trans('trp.guided-tour.reviews.copy.description'),
                ];
            }

            if($layout && $layout == 'fb') {

                $arr[] = [
                    'action' => 'fb_id',
                    'title' => trans('trp.guided-tour.reviews.fb.title'),
                    'description' => trans('trp.guided-tour.reviews.fb.description'),
                    'skip' => true,
                    'skip_text' => trans('trp.guided-tour.ok'),
                ];

                $arr[] = [
                    'action' => 'reviews_type',
                    'title' => trans('trp.guided-tour.reviews.reviews-type.title'),
                    'description' => trans('trp.guided-tour.reviews.reviews-type.description'),
                    'skip' => true,
                    'skip_text' => trans('trp.guided-tour.ok'),
                ];
            }

            return Response::json([
                'success' => true,
                'steps' => $arr,
                'count_all_steps' => $layout == 'badge' ? 3 : 4,
                'image' => url('img-trp/reviews-step-icon.svg'),
            ] );
        }

        return Response::json([
            'success' => false,
        ]);
    }

    /**
     * Patient social profile form
     */
    public function socialProfile($locale=null) {

        if(!empty($this->user)) {

            if (request('link') && mb_strpos(mb_strtolower(request('link')), 'http') !== 0) {
                request()->merge([
                    'link' => 'http://'.request('link')
                ]);
            }

            $validator = Validator::make(Request::all(), [
                'link' =>  array('required', 'regex:/^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/'),
            ]);

            if ($validator->fails()) {

                $msg = $validator->getMessageBag()->toArray();
                $ret = array(
                    'success' => false,
                    'messages' => array()
                );

                foreach ($msg as $field => $errors) {
                    $ret['messages'][$field] = implode(', ', $errors);
                }

                return Response::json( $ret );
            } else {

                if(Request::has('photo') && empty(Request::input('photo'))) {
                    return Response::json( [
                        'success' => false,
                        'without_image' => true,
                    ] );
                }

                $this->user->website = Request::input('link');
                $this->user->save();

                if( Request::input('photo') ) {
                    $img = Image::make( User::getTempImagePath( Request::input('photo') ) )->orientate();
                    $this->user->addImage($img);
                }

                return Response::json( [
                    'success' => true,
                ] );
            }
        }
    }
}