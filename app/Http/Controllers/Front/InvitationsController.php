<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\FrontController;

use Illuminate\Support\Facades\Input;
use Maatwebsite\Excel\Facades\Excel;

use App\Models\AnonymousUser;
use App\Models\UserCategory;
use App\Models\UserHistory;
use App\Models\UserInvite;
use App\Models\UserAction;
use App\Models\DcnReward;
use App\Models\UserTeam;
use App\Models\UserAsk;
use App\Models\Reward;
use App\Models\Review;
use App\Models\User;

use App\Helpers\GeneralHelper;
use App\Imports\Import;
use Carbon\Carbon;

use Validator;
use Response;
use Request;
use Image;
use File;

class InvitationsController extends FrontController {

    /**
     * dentist invites patients by whatsApp
     */
    public function inviteWhatsapp($locale=null) {

        if(!empty($this->user) && $this->user->canInvite('trp') ) {

            $invitation = new UserInvite;
            $invitation->user_id = $this->user->id;
            $invitation->invited_email = 'whatsapp';
            $invitation->platform = 'trp';
            $invitation->save();

            $text = trans('trp.page.profile.invite.whatsapp', [
                'name' => str_replace(['&'], ['and'], $this->user->getNames() )
            ]).' '.rawurlencode($this->user->getLink().'?'. http_build_query([
                'dcn-gateway-type'=>'patient-register', 
                'inviter' => GeneralHelper::encrypt($this->user->id), 
                'inviteid' => GeneralHelper::encrypt($invitation->id) 
            ]));

            return Response::json([
                'success' => true,
                'text' => $text,
            ]);
        }
    }

    /**
     * dentist invites patients by copy/paste step 1
     */
    public function inviteCopypaste($locale=null) {

        if(!empty($this->user) && $this->user->canInvite('trp') ) {

            $validator = Validator::make(Request::all(), [
                'copypaste' => array('required'),
            ]);

            if ($validator->fails()) {
                $ret = array(
                    'success' => false,
                    // 'message' => trans('trp.page.profile.invite.copypaste.error'),
                    'message' => 'Please, paste names and emails separated by commas or tabs.',
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
                        // 'message' => trans('trp.page.profile.invite.copypaste.error'),
                        'message' => 'Please, paste names and emails separated by commas or tabs.',
                    ]);
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

                if(count($reversedRows[0]) == 1) {

                    $emails = $reversedRows[0];
                    $names = $reversedRows[1];

                    if (!filter_var($emails[0], FILTER_VALIDATE_EMAIL) && filter_var($names[0], FILTER_VALIDATE_EMAIL)) {
                        $emails = $reversedRows[1];
                        $names = $reversedRows[0];
                    }

                    return Response::json($this->inviteCopypasteValidator($emails, $names));
                } else {

                    if (session('bulk_invites')) {
                        session()->pull('bulk_invites');
                    }                
                    session(['bulk_invites' => $reversedRows]);
    
                    return Response::json([
                        'success' => true,
                        'info' => $reversedRows,
                    ]);
                }
            }
        }
    }

    /**
     * dentist invites patients from file and by copy/paste - final step (merged method)
     */
    public function inviteCopypasteFinal($locale=null) {

        if(!empty($this->user) && $this->user->canInvite('trp') && $this->user->is_dentist ) {

            if(Request::Input('patient-emails')) {

                if(Request::Input('patient-emails') == '1') {

                    $emails = session('bulk_invites')[0];
                    $names = session('bulk_invites')[1];
                } else {
                    $emails = session('bulk_invites')[1];
                    $names = session('bulk_invites')[0];
                }

                return Response::json($this->inviteCopypasteValidator($emails, $names));
            } else {
                return Response::json([
                    'success' => false,
                    // 'message' => trans('trp.page.profile.invite.copypaste.failed'),
                    'message' => 'Something went wrong. Please, start over.',
                ]);
            }
        }
    }

    private function inviteCopypasteValidator($emails, $names) {
        $invalid = 0;
        $invalid_emails = [];
        $already_invited = 0;
        $already_invited_emails = [];

        foreach ($emails as $key => $email) {

            $is_dentist = User::where('email', 'LIKE', $email )
            ->where('is_dentist', 1)
            ->first();

            $valid_email = $this->user->sendgridEmailValidation(68, $email);

            if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !empty($is_dentist) || !$valid_email) {
                $invalid++;

                $invalid_emails[] = $email;
                continue;
            }

            $invitation = UserInvite::where([
                ['user_id', $this->user->id],
                ['invited_email', 'LIKE', $email],
            ])->first();

            if ($invitation) {
                $already_invited++;

                $already_invited_emails[] = $email;
                continue;
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
                // $final_message = trans('trp.page.profile.invite.copypaste.unvalid-emails').'<br/>'.implode(',', $invalid_emails);
                $final_message = 'Your review invites have been sent successfully to patients with valid emails. There were some unvalid emails, however, which were skipped:'.'<br/>'.implode(',', $invalid_emails);
                $alert_color = 'orange';
            } else {
                $final_message = 'Your review invites have been sent successfully to patients with valid emails. Some patients, however, were skipped either because they have already submitted feedback earlier this month or their emails were invalid:'.'<br/>'.implode(',', $invalid_emails).(count($already_invited_emails) ? ','.implode(',', $already_invited_emails) : '');
                // $final_message = trans('trp.page.profile.invite.copypaste.submitted-feedback').'<br/>'.implode(',', $invalid_emails).(count($already_invited_emails) ? ','.implode(',', $already_invited_emails) : '');
                $alert_color = 'orange';
            }
        } else if(!empty($already_invited) && ($already_invited == count($emails))) {
            // $final_message = trans('trp.page.profile.invite.copypaste.all-submitted-feedback').'<br/>'.implode(',', $already_invited_emails);
            $final_message = 'Sending your review invites has failed. You have already invited these patients to submit feedback earlier this month.'.'<br/>'.implode(',', $already_invited_emails);
            $alert_color = 'warning';
            $gtag_tracking = false;
        } else if(!empty($already_invited) && $already_invited != count($emails)) {
            // $final_message = trans('trp.page.profile.invite.copypaste.submitted-feedback-invalid-emails').'<br/>'.implode(',', $invalid_emails).','.implode(',', $already_invited_emails);
            $final_message = 'Your review invites have been sent successfully to patients with valid emails. Some patients, however, were skipped because they have already submitted feedback earlier this month:'.'<br/>'.implode(',', $invalid_emails).','.implode(',', $already_invited_emails);
            $alert_color = 'orange';
        }

        foreach ($emails as $key => $email) {
            $inviter_email = $this->user->email ? $this->user->email : $this->user->mainBranchEmail();
            if(!empty($names[$key]) && ($email != $inviter_email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->sendInvite($email, $names[$key], true);
            }
        }

        return [
            'success' => true,
            'message' => $final_message,
            'color' => $alert_color,
            'gtag_tracking' => $gtag_tracking,
            'show_popup' => true,
        ];
    }

    /**
     * dentist invites patients from file step 1
     */
    public function inviteFile($locale=null) {

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
                ]); 
            }
        }
        
        return Response::json([
            'success' => false 
        ]);
    }

    /**
     * dentist invites patient again
     */
    public function invitePatientAgain($locale=null) {

        $id = Request::input('id');

        if (!empty($this->user) && $this->user->is_dentist && !empty($id) && $this->user->canInvite('trp')) {

            $last_invite = UserInvite::find($id);
            $existing_patient = User::find($last_invite->invited_id);

            if (!empty($last_invite) && !empty($existing_patient)) {
                
                $last_invite->created_at = Carbon::now();
                $last_invite->save();
                
                $this->askDentistToBeHisPatient($existing_patient);

                $substitutions = [
                    'type' => $this->user->is_clinic ? 'dental clinic' : 'your dentist',
                    'inviting_user_name' => $this->user->getNames(),
                    'inviting_user_profile_image' => $this->user->getImageUrl(true),
                    'invited_user_name' => $last_invite->invited_name,
                    "invitation_link" => $this->user->getLink().'?'. http_build_query([
                        'dcn-gateway-type'=>'patient-login', 
                        'inviter' => GeneralHelper::encrypt($this->user->id) 
                    ]),
                ];

                $existing_patient->sendGridTemplate(68, $substitutions, 'trp');

                return Response::json([
                    'success' => true,
                ]);
            }
        }

        return Response::json([
            'success' => false 
        ]);
    }

    /**
     * clinic invites team member
     */
    public function inviteTeamMember($locale=null) {
        
        $current_user = null;

        if (!empty($this->user) && $this->user->canInvite('trp')) {
            //from clinic's profile
            $current_user = $this->user;
        } else {
            // from verification popup after registration
            if(!empty(request('last_user_id'))) {
                $current_user = User::find(request('last_user_id'));
    
                if(!empty($current_user) && request('last_user_hash') == $current_user->get_token()) {
                } else {
                    $current_user = null;
                }
            }
        }

        if(empty($current_user)) {
            return Response::json([
                'success' => false, 
                'message' => trans('trp.common.something-wrong') 
            ]);
        }

        $validator = Validator::make( Request::all(), [
            'name' => ['required', 'string'],
            'team-job' => ['required'],
        ]);

        if ($validator->fails()) {
            return Response::json([
                'success' => false, 
                'message' => trans('trp.popup.verification-popup.clinic.team-name-error') 
            ]);
        } else {
            if (Request::input('team-job') != 'dentist') {
                //add not registered team member
                $invitation = new UserInvite;
                $invitation->user_id = $current_user->id;
                $invitation->invited_email = ' ';
                $invitation->invited_name = Request::Input('name');
                $invitation->job = Request::Input('team-job');
                $invitation->join_clinic = true;
                $invitation->for_team = true;
                $invitation->platform = 'trp';
                $invitation->save();

                if( Request::input('avatar') ) {
                    $img = Image::make( GeneralHelper::decode_base64_image(Request::input('avatar')) )->orientate();
                    $invitation->addImage($img);
                }

                return Response::json([
                    'success' => true, 
                    'message' => trans('trp.popup.verification-popup.clinic.no-email-success', [
                        'name' => Request::Input('name') 
                    ]),
                ]);
            } else {

                if(empty(Request::input('check-for-same'))) {
                    $username = Request::Input('name');
                    $team_ids = [];

                    if( $current_user->team->isNotEmpty()) {
                        foreach ($current_user->team as $t) {
                            $team_ids[] = $t->dentist_id;
                        }
                    }

                    $dentists_with_same_name = User::where('is_dentist', true)
                    ->where('is_clinic', '!=', 1)->where(function($query) use ($username) {
                        $query->where('name', 'LIKE', $username)
                        ->orWhere('name_alternative', 'LIKE', $username);
                    })
                    ->whereIn('status', config('dentist-statuses.dentist_approved'))
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
                                'location' => !empty($same_dentist->country_id) ? $same_dentist->city_name.', '.$same_dentist->country->name : '',
                            ];
                        }

                        return Response::json( [
                            'success' => true,
                            'dentists' => $user_list
                        ]);
                    }
                }
                
                if(!empty(Request::input('email'))) {
                    //check for existing user for team member

                    if (!filter_var(Request::input('email'), FILTER_VALIDATE_EMAIL)) {
                        return Response::json([
                            'success' => false,
                            'message' => trans('trp.popup.verification-popup.clinic.invalid-email-error')
                        ]);
                    }

                    $valid_email = $current_user->sendgridEmailValidation(92, Request::input('email'));

                    if(!$valid_email) {
                        return Response::json([
                            'success' => false,
                            'message' => trans('trp.page.profile.invite-dentist.suspicious-email')
                        ]);
                    }

                    $existing_dentist = User::where('email', 'LIKE', Request::input('email'))->withTrashed()->first();

                    if( !empty($existing_dentist)) {
                        //if is a patient
                        if(!$existing_dentist->is_dentist) {
                            return Response::json([
                                'success' => false, 
                                'message' => trans('trp.popup.verification-popup.clinic.invite-patient-error') 
                            ]);
                        }

                        // if is a clinic
                        if($existing_dentist->is_clinic) {
                            return Response::json([
                                'success' => false, 
                                'message' => trans('trp.popup.verification-popup.clinic.invite-clinic-error') 
                            ]);
                        }

                        $existing_team = UserTeam::where('user_id', $current_user->id)
                        ->where('dentist_id', $existing_dentist->id )
                        ->first();

                        if(!empty($existing_team)) {
                            //already added member
                            return Response::json([
                                'success' => false, 
                                'message' => trans('trp.popup.verification-popup.clinic.existing-team-error') 
                            ]);

                        } else if(
                            empty($existing_dentist->self_deleted) 
                            && empty($existing_dentist->deleted_at) 
                            && in_array($existing_dentist->status, config('dentist-statuses.dentist_for_team')) 
                        ) {

                            $newteam = new UserTeam;
                            $newteam->dentist_id = $existing_dentist->id;
                            $newteam->user_id = $current_user->id;
                            $newteam->approved = 1;
                            $newteam->save();

                            if(!empty($this->user) && in_array($existing_dentist->status, ['approved', 'added_by_clinic_claimed', 'test'])) {

                                $existing_dentist->sendTemplate(33, [
                                    'clinic-name' => $this->user->getNames(),
                                    'clinic-link' => $this->user->getLink()
                                ], 'trp');
                            }

                            // if($existing_dentist->status == 'test') {
                            //     $mtext = 'Clinic '.$current_user->getNames().' added a new team member that is with status Test.
                            //     Link to dentist\'s profile:
                            //     '.url('https://reviews.dentacoin.com/cms/users/users/edit/'.$existing_dentist->id).'
                            //     Link to clinic\'s profile: 
                            //     '.url('https://reviews.dentacoin.com/cms/users/users/edit/'.$current_user->id).'
                            //     '.(!empty(Auth::guard('admin')->user()) ? 'This is a Dentacoin ADMIN' : '').'
                            //     ';

                            //     Mail::raw($mtext, function ($message) use ($current_user) {
                            //         $sender = config('mail.from.address');
                            //         $sender_name = config('mail.from.name');

                            //         $message->from($sender, $sender_name);
                            //         $message->to( 'petya.ivanova@dentacoin.com' );
                            //         $message->subject('Clinic '.$current_user->getNames().' added a new team member that is with status Test');
                            //     });
                            // }

                            return Response::json([
                                'success' => true, 
                                'message' => trans('trp.popup.verification-popup.clinic.success'),
                            ]);

                        } else if(
                            empty($existing_dentist->self_deleted) 
                            && empty($existing_dentist->deleted_at) 
                            && $existing_dentist->status == 'new' 
                        ) {

                            if (!empty($this->user)) {
                                //if existing dentist is with status new -> approve it automaticaly
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

                            return Response::json([
                                'success' => true, 
                                'message' => trans('trp.popup.verification-popup.clinic.success'),
                            ]);

                        } else if(
                            !empty($existing_dentist->self_deleted) 
                            || !empty($existing_dentist->deleted_at) 
                            || in_array($existing_dentist->status, ['rejected', 'added_by_clinic_rejected', 'added_rejected', 'pending'])
                        ) {

                            //if is deleted or rejected

                            // $mtext = 'Clinic '.$current_user->getNames().' added a new team member that is deleted OR with status rejected/suspicious. Link to dentist\'s profile:
                            // '.url('https://reviews.dentacoin.com/cms/users/users/edit/'.$existing_dentist->id).'
                            // Link to clinic\'s profile: 
                            // '.url('https://reviews.dentacoin.com/cms/users/users/edit/'.$current_user->id).'
                            // '.(!empty(Auth::guard('admin')->user()) ? 'This is a Dentacoin ADMIN' : '').'
                            // ';

                            // Mail::raw($mtext, function ($message) use ($current_user) {
                            //     $sender = config('mail.from.address');
                            //     $sender_name = config('mail.from.name');

                            //     $message->from($sender, $sender_name);
                            //     $message->to( 'petya.ivanova@dentacoin.com' );
                            //     $message->subject('Clinic '.$current_user->getNames().' added a new team member that is deleted OR with status rejected/suspicious');
                            // });

                            return Response::json([
                                'success' => false, 
                                'message' => trans('trp.popup.verification-popup.clinic.suspicious-email-error') 
                            ]);
                        }
                    }

                    $newuser = new User;
                    $newuser->email = Request::input('email');
                    $newuser->status = !empty($this->user) ? 'added_by_clinic_unclaimed' : 'added_by_clinic_new';
                } else {
                    $newuser = new User;
                    $newuser->status = 'dentist_no_email';
                }
                
                $newuser->name = Request::input('name');
                $newuser->country_id = $current_user->country_id;
                $newuser->address = $current_user->address;
                $newuser->zip = $current_user->zip;
                $newuser->state_name = $current_user->state_name;
                $newuser->state_slug = $current_user->state_slug;
                $newuser->city_name = $current_user->city_name;
                $newuser->lat = $current_user->lat;
                $newuser->lon = $current_user->lon;
                $newuser->custom_lat_lon = $current_user->custom_lat_lon;

                $newuser->platform = 'trp';                    
                $newuser->gdpr_privacy = true;
                $newuser->is_dentist = 1;
                $newuser->invited_by = $current_user->id;
                $newuser->save();

                if( Request::input('avatar') ) {
                    $img = Image::make( GeneralHelper::decode_base64_image(Request::input('avatar')) )->orientate();
                    $newuser->addImage($img);
                }
                
                $newteam = new UserTeam;
                $newteam->dentist_id = $newuser->id;
                $newteam->user_id = $current_user->id;
                $newteam->approved = 1;
                $newteam->new_clinic = 1;
                $newteam->save();

                if(!empty(Request::input('email'))) {

                    if(!empty(Request::input('team-speciality')) || Request::input('team-speciality') == 0) {
                        $newc = new UserCategory;
                        $newc->user_id = $newuser->id;
                        $newc->category_id = Request::input('team-speciality');
                        $newc->save();
                    }

                    if(!empty($this->user)) {
                        $newuser->slug = $newuser->makeSlug();
                        $newuser->save();

                        $newuser->sendGridTemplate( 92 , [
                            'clinic_name' => $current_user->getNames(),
                            "invitation_link" => getLangUrl( 'dentist/'.$newuser->slug.'/claim/'.$newuser->id).'?'. http_build_query([
                                'popup'=>'claim-popup'
                            ]).'&without-info=true',
                        ], 'trp');
                    }
                }
            }
        }

        return Response::json([
            'success' => true, 
            'message' => trans('trp.popup.verification-popup.clinic.success'), 
            'with_email' => $newuser->status == 'dentist_no_email' ? false : true,
        ]);
    }

    /**
     * clinic invites existing user as team member
     */
    public function inviteExistingTeamMember($locale=null) {

        if(
            (!empty($this->user) && $this->user->canInvite('trp')) 
            || 
            (
                empty($this->user) 
                && !empty(request('ex_d_id')) 
                && !empty(User::find(request('ex_d_id')) ) 
                && !empty(request('clinic_id')) 
                && !empty(User::find(request('clinic_id')) )
            )
        ) {

            $newteam = new UserTeam;
            $newteam->dentist_id = request('ex_d_id');
            $newteam->user_id = !empty($this->user) ? $this->user->id : request('clinic_id');
            $newteam->approved = empty($this->user) ? 0 : 1;
            if(empty($this->user)) {
                $newteam->new_clinic = 1;
            }
            $newteam->save();
            
            $dentist = User::find(request('ex_d_id'));

            if(!empty($this->user) && (in_array($dentist->status, ['approved', 'added_by_clinic_claimed']))) {
                $dentist->sendTemplate(33, [
                    'clinic-name' => $this->user->getNames(),
                    'clinic-link' => $this->user->getLink()
                ], 'trp');
            }

            return Response::json([
                'success' => true, 
                'message' => trans('trp.popup.verification-popup.dentist-invite.success', [
                    'dentist-name' => $dentist->getNames()
                ])
            ]);
        }
        
        return Response::json([
            'success' => false, 
            'message' => trans('trp.common.something-wrong') 
        ]);
    }

    /**
     * dentist verifies/denies patient
     */
    public function asksActions($locale=null, $type, $ask_id) {
        if(
            empty($this->user) 
            || !$this->user->is_dentist 
            || ($this->user->is_dentist && !in_array($this->user->status, config('dentist-statuses.approved_test')))
        ) {
            return Response::json([
                'success' => false, 
            ]);
        }

        $ask = UserAsk::find($ask_id);

        if(!empty($ask) && $ask->dentist_id==$this->user->id && $ask->status=='waiting') {

            if($type == 'accept') {

                $ask->status = 'yes';
                $ask->save();
    
                $last_invite = UserInvite::where('user_id', $this->user->id)
                ->where('invited_id', $ask->user->id)
                ->first();
    
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
                    
                    $existing_patient = User::withTrashed()
                    ->where('email', 'LIKE', $ask->user->email )
                    ->where('is_dentist', 0)
                    ->first();
    
                    if(!empty($existing_patient)) {
                        $inv->invited_id = $existing_patient->id;
                    }
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
                            $reward->reference_id = $review->id;
                            GeneralHelper::deviceDetector($reward);
                            $reward->save();
    
                            $reward = new DcnReward();
                            $reward->user_id = $ask->dentist_id;
                            $reward->platform = 'trp';
                            $reward->reward = Reward::getReward('reward_dentist');
                            $reward->type = 'dentist-review';
                            $reward->reference_id = $review->id;
                            GeneralHelper::deviceDetector($reward);
                            $reward->save();
                        }
                    }
                }
            } else if($type == 'deny') {
                $ask->status = 'no';
                $ask->save();
            }

            $patientAsksPendingCount = 0;

            if (!empty($this->user) && $this->user->asks->isNotEmpty()) {
                foreach ($this->user->asks as $p_ask) {
                    if ($p_ask->status == 'waiting' && !$p_ask->hidden && $p_ask->user) {
                        $patientAsksPendingCount++;
                    }
                }
            }

            return Response::json([
                'success' => true,
                'class' => $ask->status=='yes' ? 'accepted-text' : 'declined-text',
                'text' => $ask->status=='yes' ? 'Accepted' : 'Declined',
                'patientAsksPendingCount' => $patientAsksPendingCount,
            ]);
        }
    }

    /**
     * clinic rejects/deletes/accepts team member
     */
    public function teamMemberActions( $locale=null, $type, $id ) {

        if($type == 'reject') {

            $res = UserTeam::where('user_id', $this->user->id)
            ->where('dentist_id', $id)
            ->delete();

            if( $res ) {
                $dentist = User::find( $id );
                $dentist->sendTemplate(36, [
                    'clinic-name' => $this->user->getNames()
                ], 'trp');
            }

        } else if($type == 'delete') {

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

            UserTeam::where('user_id', $this->user->id)
            ->where('dentist_id', $id)
            ->delete();

        } else if($type == 'accept') {

            $item = UserTeam::where('dentist_id', $id)
            ->where('user_id', $this->user->id)
            ->first();

            if ($item) {
                $item->approved = 1;
                $item->save();

                $dentist = User::find( $id );
                $dentist->sendTemplate(35, [
                    'clinic-name' => $this->user->getNames()
                ], 'trp');
            }
        }

        return Response::json([
            'success' => true,
        ]);
    }
    
    /**
     * clinic deletes team member
     */
    public function clinicDeletesTeamMember( $locale=null, $id ) {
        $res = UserTeam::where('dentist_id', $this->user->id)
        ->where('user_id', $id)
        ->delete();

        if(Request::getHost() != 'urgent.reviews.dentacoin.com') {
            if( $res ) {
                $clinic = User::find( $id );
                $clinic->sendTemplate(38, [
                    'dentist-name' => $this->user->getNames()
                ], 'trp');
            }
        }

        return Response::json( [
            'success' => true,
        ]);
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

                if(Request::getHost() != 'urgent.reviews.dentacoin.com') {
                    $clinic->sendTemplate(34, [
                        'dentist-name' =>$this->user->getNames()
                    ], 'trp');
                }

                return Response::json( [
                    'success' => true,
                    'clinic' => [
                        'name' => $clinic->getNames(),
                        'link' => $clinic->getLink(),
                        'id' => $clinic->id,
                    ],
                ]);
            }
        } 
            
        return Response::json( [
            'success' => false,
            'message' => trans('trp.page.user.clinic-invited-error')
        ]);
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
        ]);
    }

    /**
     * clinic deletes invite as his team member
     */
    public function invitesDelete( $locale=null, $id ) {

        UserInvite::destroy($id);
        return Response::json( [
            'success' => true,
        ]);
    }

    private function sendInvite($email, $name, $for_bulk_invites) {
        $ret = null;
        $send_mail = false;

        $invitation = UserInvite::where([
            ['user_id', $this->user->id],
            ['invited_email', 'LIKE', $email],
        ])->first();

        $existing_patient = User::withTrashed()
        ->where('email', 'LIKE', $email )
        ->where('is_dentist', 0)
        ->first();
        $existing_anonymous = AnonymousUser::where('email', 'LIKE', $email)->first();

        if(!$for_bulk_invites) {
            if(!empty($existing_patient) && (!empty($existing_patient->deleted_at) || $existing_patient->self_deleted )) {
                $ret = [
                    'success' => false, 
                    'message' => trans('trp.page.profile.invite.patient-deleted', [
                        'email' => $email
                    ])
                ];

                return $ret;
            }
        }
        
        if(empty($existing_patient) || empty($existing_patient->deleted_at)) { ///da proveeq

            if($invitation) {
                if(!$for_bulk_invites) {
                    if(!empty($existing_patient)) {                        
                        if($invitation->created_at->timestamp > Carbon::now()->subMonths(1)->timestamp) {
                            $ret = [
                                'success' => false, 
                                'message' => trans('trp.page.profile.invite.already-invited-month') 
                            ];
                            return $ret;
                        }
                        $invitation->invited_id = $existing_patient->id;
                    }
                }

                if ($invitation->created_at->timestamp < Carbon::now()->subMonths(1)->timestamp) {

                    $invitation->invited_name = $name;
                    $invitation->created_at = Carbon::now();
                    $invitation->review = true;
                    $invitation->completed = null;
                    $invitation->notified1 = null;
                    $invitation->notified2 = null;
                    $invitation->notified3 = null;
                    
                    if(!empty(Request::Input('invite_hubapp')) && $this->user->is_partner) {
                        $invitation->for_dentist_patients = true;
                    }
                    $invitation->save();
                    $send_mail = true;

                    if(!empty($existing_patient)) {
                        $this->askDentistToBeHisPatient($existing_patient);
                    }
                }
            } else {

                $valid_email = $this->user->sendgridEmailValidation(68, $email);

                $invitation = new UserInvite;
                $invitation->user_id = $this->user->id;
                $invitation->invited_email = $email;
                $invitation->invited_name = $name;
                $invitation->platform = 'trp';
                $invitation->review = true;
                if(!empty(Request::Input('invite_hubapp')) && $this->user->is_partner) {
                    $invitation->for_dentist_patients = true;
                }

                if(!$valid_email) {
                    $invitation->suspicious_email = true;
                } else {
                    $send_mail = true;
                    
                    if(!empty($existing_patient)) {
                        $invitation->invited_id = $existing_patient->id;
                    }
                }

                $invitation->save();
            }

            if ($send_mail) {
                if(!empty($existing_patient)) {

                    $substitutions = [
                        'type' => $this->user->is_clinic ? 'dental clinic' : ($this->user->is_dentist ? 'your dentist' : ''),
                        'inviting_user_name' => $this->user->getNames(),
                        'inviting_user_profile_image' => $this->user->getImageUrl(true),
                        'invited_user_name' => $name,
                        "invitation_link" => $this->user->getLink().'?'. http_build_query([
                            'dcn-gateway-type'=>'patient-login', 
                            'inviter' => GeneralHelper::encrypt($this->user->id), 
                            'inviteid' => GeneralHelper::encrypt($invitation->id) 
                        ]),
                    ];

                    if(!empty(Request::Input('invite_hubapp')) && $this->user->is_partner) {
                        $existing_patient->patient_of = $this->user->id;
                        $existing_patient->save();

                        $existing_patient->sendGridTemplate(130, $substitutions, 'trp');
                    } else {
                        $existing_patient->sendGridTemplate(68, $substitutions, 'trp');
                    }
                } else {

                    $inviter_email = $this->user->email ? $this->user->email : $this->user->mainBranchEmail();

                    if($email != $inviter_email) {
                        $dentist_name = $this->user->name;
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
                            'inviting_user_profile_image' => $this->user->getImageUrl(true),
                            'invited_user_name' => $name,
                            "invitation_link" => $this->user->getLink().'?'. http_build_query([
                                'dcn-gateway-type'=>'patient-register', 
                                'inviter' => GeneralHelper::encrypt($this->user->id), 
                                'inviteid' => GeneralHelper::encrypt($invitation->id) 
                            ]),
                        ];

                        GeneralHelper::unregisteredSendGridTemplate(
                            $this->user, 
                            $email, 
                            $name, 
                            !empty(Request::Input('invite_hubapp')) && $this->user->is_partner ? 130 : 106, 
                            $substitutions, 
                            'trp', 
                            $unsubscribed, 
                            $email
                        );
                    } else {
                        if(!$for_bulk_invites) {
                            $ret = [
                                'success' => false, 
                                'message' => trans('trp.page.profile.invite.yourself') 
                            ];
                            return $ret;
                        }
                    }
                }

                if(!$for_bulk_invites) {
                    $ret = [
                        'success' => true, 
                        'message' => trans('trp.page.profile.invite.success') 
                    ];
                    return $ret;
                }
            }
        }

        if(!$for_bulk_invites) {
            $ret = [
                'success' => false, 
                'message' => 'Error'
            ];
            return $ret;
        }
    }

    private function askDentistToBeHisPatient($existing_patient) {

        $last_ask = UserAsk::where('user_id', $existing_patient->id)
        ->where('dentist_id', $this->user->id)
        ->first();
        
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