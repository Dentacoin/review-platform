<?php

namespace App\Http\Controllers\Front;
use App\Http\Controllers\FrontController;

use App\Models\User;
use App\Models\UserCategory;
use App\Models\UserInvite;
use App\Models\UserTeam;
use App\Models\Country;
use App\Models\Civic;
use App\Models\IncompleteRegistration;
use Carbon\Carbon;

use Validator;
use Auth;
use Request;
use Response;
use Redirect;
use Mail;
use Image;
use Illuminate\Support\Facades\Input;


class RegisterController extends FrontController
{
    public function register($locale=null) {


        return Redirect::to( getLangUrl('welcome-dentist'), 301); 

        if(!empty($this->user)) {
            return redirect(getLangUrl('/'));
        }

		return $this->ShowView('register', [
			'js' => [
				'register.js'
			],
            'jscdn' => [
                'https://hosted-sip.civic.com/js/civic.sip.min.js',
            ],
            'csscdn' => [
                'https://hosted-sip.civic.com/css/civic-modal.min.css',
            ],
            'countries' => Country::with('translations')->get(),
            'categories' => $this->categories,
            'invitation_email' => session('invitation_email'),
            'invitation_name' => session('invitation_name'),
		]);
    }

    public function check_step_one() {

        $validator = Validator::make(Request::all(), [
            'email' => array('required', 'email', 'unique:users,email'),
            'password' => array('required', 'min:6'),
            'password-repeat' => 'required|same:password',
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

            if(User::validateEmail(Request::input('email')) == true) {
                $ret = array(
                    'success' => false,
                    'messages' =>[
                        'email' => trans('trp.common.invalid-email')
                    ]
                );
                return Response::json( $ret );
            }

            return Response::json( ['success' => true] );
        }

    }

    public function check_step_two() {

        $validator = Validator::make(Request::all(), [
            'mode' => array('required', 'in:dentist,clinic'),
            'name' => array('required', 'min:3'),
            'email' => array('required', 'email', 'unique:users,email'),
            'password' => array('required', 'min:6'),
            'password-repeat' => 'required|same:password',
            'agree' =>  array('required', 'accepted'),
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

        } else {

            $is_blocked = User::checkBlocks( Request::input('name') , Request::input('email') );
            if( $is_blocked ) {
                return Response::json( [
                    'success' => false, 
                    'messages' => [
                        'name' => $is_blocked
                    ]
                ] );
            }

            if(User::validateLatin(Request::input('name')) == false) {
                return Response::json( [
                    'success' => false, 
                    'messages' => [
                        'name' => trans('trp.common.invalid-name')
                    ]
                ] );
            }

            $this->saveIncompleteRegistration(request('email'), [
                'email' => request('email'),
                'password' => request('password'),
                'mode' => request('mode'),
                'name' => request('name'),
                'title' => request('title'),
                'name_alternative' => request('name_alternative'),
            ]);

            $ret = array(
                'success' => true
            );

        }

        return Response::json( $ret );
    }


    public function check_step_three() {

        if (request('website') && mb_strpos(mb_strtolower(request('website')), 'http') !== 0) {
            request()->merge([
                'website' => 'http://'.request('website')
            ]);
        }

        $validator = Validator::make(Request::all(), [
            'country_id' => array('required', 'exists:countries,id'),
            //'city_id' => array('required', 'exists:cities,id'),
            //'zip' => array('required', 'string'),
            'address' =>  array('required', 'string'),
            'website' =>  array('required', 'regex:/^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/'),
            'phone' =>  array('required', 'regex: /^[- +()]*[0-9][- +()0-9]*$/u'),
        ]);

        if ($validator->fails()) {

            $msg = $validator->getMessageBag()->toArray();
            $ret = array(
                'success' => false,
                'messages' => array()
            );

            foreach ($msg as $field => $errors) {
                if($field=='website') {
                    $ret['messages'][$field] = trans('trp.common.invalid-website');
                } else {
                    $ret['messages'][$field] = implode(', ', $errors);
                }
            }

            return Response::json( $ret );
        } else {


            $info = User::validateAddress( Country::find(request('country_id'))->name, request('address') );
            if(empty($info)) {
                $ret = array(
                    'success' => false,
                    'messages' => array(
                        'address' => trans('trp.common.invalid-address')
                    )
                );
            } else {

                $this->saveIncompleteRegistration(request('email'), [
                    'country_id' => request('country_id'),
                    'address' => request('address'),
                    'website' => request('website'),
                    'phone' => request('phone'),
                ]);

                return Response::json( ['success' => true] );
            }
        }

    }

    public function upload($locale=null) {

        if( Request::file('image') && Request::file('image')->isValid() ) {
            $img = Image::make( Input::file('image') )->orientate();
            list($thumb, $full, $name) = User::addTempImage($img);
            return Response::json(['success' => true, 'thumb' => $thumb, 'name' => $name ]);
        }
    }


    public function invite_accept($locale=null) {

        if (!empty(Request::input('info'))) {
            $info = User::decrypt(base64_decode(Request::input('info')));

            if (!empty($info)) {
                $id = json_decode($info, true)['user_id'];
                $hash = json_decode($info, true)['hash'];

                if (isset(json_decode($info, true)['inv_id'])) {
                    $inv_id = json_decode($info, true)['inv_id'];
                } else {
                    $inv_id = null;
                }
                

                $user = User::find($id);

                if (!empty($user) && $user->canInvite('trp')) {

                    if ($hash == $user->get_invite_token()) {
                        // check for GET variables and build query string
                        $get = count($_GET) ? ('?' . http_build_query($_GET)) : '';

                        if($this->user) {
                            if($this->user->id==$user->id) {
                                Request::session()->flash('success-message', trans('trp.popup.registration.invite-yourself'));
                            } else {
                                if(!$this->user->wasInvitedBy($user->id)) {
                                    $inv = UserInvite::find($inv_id);
                                    if(!empty($inv) && empty($inv->invited_id)) {
                                        $inv->invited_id = $this->user->id;

                                        if ($inv->invited_email == 'whatsapp') {
                                            $inv->invited_name = $this->user->name;
                                            $inv->invited_email = $this->user->email;
                                        }

                                        $inv->rewarded = true;
                                        $inv->save();
                                    }
                                }
                                Request::session()->flash('success-message', trans('trp.popup.registration.invitation-registered', [ 'name' => $user->name ]));
                            }
                            return redirect( $user->getLink().$get );
                        } else {
                            $sess = [
                                'invited_by' => $user->id,
                            ];

                            $inv = UserInvite::find($inv_id);
                            if(!empty($inv)) {
                                $sess['invitation_id'] = $inv->id;

                                if($inv->join_clinic) {
                                    $sess['join_clinic'] = true;
                                }
                            }
                            session($sess);

                            if (!empty($inv) && !empty($inv->invited_id)) {
                                $text = 'The invitation has expired. Get in touch with your dentist to request a new invite.';

                                session()->pull('invitation_id');
                            } else {
                                $text = !empty( $sess['join_clinic'] ) ? trans('trp.popup.registration.invitation-clinic', [ 'name' => $user->name ]) : trans('trp.popup.registration.invitation', [ 'name' => $user->name ]);
                            }

                            if (!empty($inv) && !empty($inv->invited_id)) {
                                return redirect()->to( $user->getLink().'?'. http_build_query(['popup'=> !empty( $sess['join_clinic'] ) ? 'popup-register-dentist' : 'popup-register' ]).'&'.http_build_query($_GET))
                                ->withInput()
                                ->with('error-message', $text );
                            } else if($user->is_dentist) {
                                return redirect()->to( $user->getLink().'?'. http_build_query(['popup'=> !empty( $sess['join_clinic'] ) ? 'popup-register-dentist' : 'popup-register' ]).'&'.http_build_query($_GET))
                                ->withInput()
                                ->with('success-message', $text );
                            } else {
                                return redirect()->to(getLangurl('/').'?'. http_build_query(['popup'=>'popup-register']).'&'.http_build_query($_GET))
                                ->withInput()
                                ->with('success-message', $text );
                            }

                        }

                    }
                }
            }
        }

        return redirect('/');

    }

    public function register_form($locale=null) {

        if (request('website') && mb_strpos(mb_strtolower(request('website')), 'http') !== 0) {
            request()->merge([
                'website' => 'http://'.request('website')
            ]);
        }

        $validator = Validator::make(Request::all(), [
            'mode' => array('required', 'in:dentist,clinic'),
            'name' => array('required', 'min:3'),
            'email' => array('required', 'email', 'unique:users,email'),
            'password' => array('required', 'min:6'),
            'password-repeat' => 'required|same:password',
            'country_id' => array('required', 'exists:countries,id'),
            //'city_id' => array('required', 'exists:cities,id'),
            //'zip' => array('required', 'string'),
            'address' =>  array('required', 'string'),
            'website' =>  array('required', 'regex:/^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/'),
            'phone' =>  array('required', 'regex: /^[- +()]*[0-9][- +()0-9]*$/u'),
            'photo' =>  array('required'),
            'specialization' =>  array('required', 'array'),
            'agree' =>  array('required', 'accepted'),
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

            $captcha = false;
            $cpost = [
                'secret' => env('CAPTCHA_SECRET'),
                'response' => Request::input('g-recaptcha-response'),
                'remoteip' => User::getRealIp()
            ];
            $ch = curl_init('https://www.google.com/recaptcha/api/siteverify');
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt ($ch, CURLOPT_POST, 1);
            curl_setopt ($ch, CURLOPT_POSTFIELDS, http_build_query($cpost));
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);    
            curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
            $response = curl_exec($ch);
            curl_close($ch);
            if($response) {
                $api_response = json_decode($response, true);
                if(!empty($api_response['success'])) {
                    $captcha = true;
                }
            }

            if( !$captcha ) {
                $ret = array(
                    'success' => false,
                    'messages' => array(
                        'captcha' => trans('trp.popup.registration.captcha')
                    )
                );

                return Response::json( $ret );
            }

            $is_blocked = User::checkBlocks( Request::input('name') , Request::input('email') );
            if( $is_blocked ) {
                return Response::json( [
                    'success' => false, 
                    'messages' => [
                        'name' => $is_blocked
                    ]
                ] );
            }

            $info = User::validateAddress( Country::find(request('country_id'))->name, request('address') );
            if(empty($info)) {
                $ret = array(
                    'success' => false,
                    'messages' => array(
                        'address' => trans('trp.common.invalid-address')
                    )
                );
            }

            if(User::validateLatin(Request::input('name')) == false) {
                return Response::json( [
                    'success' => false, 
                    'messages' => [
                        'name' => trans('trp.common.invalid-name')
                    ]
                ] );
            }

            if(User::validateEmail(Request::input('email')) == true) {
                $ret = array(
                    'success' => false,
                    'messages' =>[
                        'email' => trans('trp.common.invalid-email')
                    ]
                );
                return Response::json( $ret );
            }
            
            $newuser = new User;
            $newuser->title = Request::input('title');
            $newuser->name = Request::input('name');
            $newuser->name_alternative = Request::input('name_alternative');
            $newuser->email = Request::input('email');
            $newuser->country_id = Request::input('country_id');
            //$newuser->city_id = Request::input('city_id');
            $newuser->password = bcrypt(Request::input('password'));
            $newuser->phone = Request::input('phone');
            $newuser->platform = 'trp';
            //$newuser->zip = Request::input('zip');
            $newuser->address = Request::input('address');
            $newuser->website = Request::input('website');
            
            $newuser->gdpr_privacy = true;
            $newuser->is_dentist = 1;
            $newuser->is_clinic = Request::input('mode')=='clinic' ? 1 : 0;

            if(!empty(session('invited_by'))) {
                $newuser->invited_by = session('invited_by');
            }

            $newuser->save();

            if( Request::input('photo') ) {
                $img = Image::make( User::getTempImagePath( Request::input('photo') ) )->orientate();
                $newuser->addImage($img);
            }
            
            UserCategory::where('user_id', $newuser->id)->delete();
            if(!empty(Request::input('specialization'))) {
                foreach (Request::input('specialization') as $cat) {
                    $newc = new UserCategory;
                    $newc->user_id = $newuser->id;
                    $newc->category_id = $cat;
                    $newc->save();
                }
            }

            if(session('join_clinic') && session('invitation_id')) {
                $approve_join = 0;
                $inv_id = session('invitation_id');
                if($inv_id) {
                    $inv = UserInvite::find($inv_id);
                    if($inv && $inv->user_id == session('join_clinic')) {
                        $inv->invited_id = $user->id;
                        $inv->save();
                        if($inv->join_clinic) {
                            $approve_join = 1;
                        }
                    }
                }

                $newclinic = new UserTeam;
                $newclinic->dentist_id = $user->id;
                $newclinic->user_id = request('clinic_id');
                $newclinic->approved = 0;
                $newclinic->save();

                $clinic->sendTemplate(2, [
                    'dentist-name' => $user->getName(),
                    'profile-link' => $user->getLink()
                ]);

            }


            $mtext = 'New dentist/clinic registration:
            '.$newuser->getName().'
            IP: '.User::getRealIp().'
            '.(!empty(Auth::guard('admin')->user()) ? 'This is a Dentacoin ADMIN' : '').'
            '.url('https://reviews.dentacoin.com/cms/users/edit/'.$newuser->id).'

            ';

            Mail::raw($mtext, function ($message) use ($newuser) {

                $sender = config('mail.from.address');
                $sender_name = config('mail.from.name');

                $message->from($sender, $sender_name);
                $message->to( 'ali.hashem@dentacoin.com' );
                $message->to( 'betina.bogdanova@dentacoin.com' );
                $message->replyTo($newuser->email, $newuser->getName());
                $message->subject('New Dentist/Clinic registration');
            });


            //Auth::login($newuser, Request::input('remember'));

            $this->completeRegistration( Request::input('email') );

            return Response::json( [
                'success' => true,
                'popup' => 'verification-popup',
                'hash' => $newuser->get_token(),
                'id' => $newuser->id,
                'short_description' => $newuser->short_description,
                'is_clinic' => $newuser->is_clinic,
            ] );

        }
    }

    public function register_invite($locale=null) {

        if (request('user_id')) {
            $user = User::find(request('user_id'));

            if( (request('user_hash') == $user->get_token()) && request('clinic_id') && !$user->is_clinic ) {
                $clinic = User::find( request('clinic_id') );

                if(!empty($clinic)) {
                    $team = UserTeam::where('dentist_id', $user->id)->where('user_id', $clinic->id)->first();

                    if (!$team) {
                        $newclinic = new UserTeam;
                        $newclinic->dentist_id = $user->id;
                        $newclinic->user_id = $clinic->id;
                        $newclinic->approved = 0;
                        $newclinic->save();

                        $clinic->sendTemplate(34, [
                            'dentist-name' => $user->getName(),
                            'profile-link' => $user->getLink()
                        ]);
                    }

                    return Response::json( [
                        'success' => true,
                        'message' => trans('trp.popup.verification-popup.join-workplace.success', ['clinic-name' => request('clinic_name')]),
                    ] );
                }
            }
        }
        return Response::json( [
            'success' => false,
            'message' => trans('trp.popup.verification-popup.join-workplace.error'),
        ] );

    }

    public function invite_dentist($locale=null) {

        if (request('user_id')) {
            $user = User::find(request('user_id'));

            if( (request('user_hash') == $user->get_token()) && request('dentist_id') && $user->is_clinic ) {
                $dentist = User::find( request('dentist_id') );

                if(!empty($dentist)) {
                    $team = UserTeam::where('dentist_id', $dentist->id)->where('user_id', $user->id)->first();

                    if (!$team) {
                        $newdentist = new UserTeam;
                        $newdentist->dentist_id = $dentist->id;
                        $newdentist->user_id = $user->id;
                        $newdentist->approved = 1;
                        $newdentist->save();

                        $dentist->sendTemplate(33, [
                            'clinic-name' => $user->getName(),
                            'clinic-link' => $user->getLink()
                        ]);
                    }

                    return Response::json( [
                        'success' => true,
                        'message' => trans('trp.popup.verification-popup.dentist-invite.success', ['dentist-name' => $dentist->getName()]),
                    ] );
                }
            }
        }
        return Response::json( [
            'success' => false,
            'message' => trans('trp.popup.verification-popup.dentist-invite.error'),
        ] );

    }

    public function invite_clinic($locale=null) {

        if (request('user_id')) {
            $user = User::find(request('user_id'));

            $validator = Validator::make(Request::all(), [
                'clinic_name' => array('required', 'min:3'),
                'clinic_email' => array('required', 'email', 'unique:users,email'),
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

                $invitation = new UserInvite;
                $invitation->user_id = $user->id;
                $invitation->invited_email = Request::Input('clinic_email');
                $invitation->invited_name = Request::Input('clinic_name');
                $invitation->save();

                //Mega hack
                $dentist_name = $user->name;
                $dentist_email = $user->email;
                $user->name = Request::Input('clinic_name');
                $user->email = Request::Input('clinic_email');
                $user->save();

                $user->sendTemplate( 42  , [
                    'dentist_name' => $dentist_name,
                ]);

                //Back to original
                $user->name = $dentist_name;
                $user->email = $dentist_email;
                $user->save();

                return Response::json( [
                    'success' => true,
                    'message' => trans('trp.popup.verification-popup.workplace.success', ['clinic-name' => request('clinic_name')]),
                ] );
            }
        }

        return Response::json( [
            'success' => false,
            'message' => trans('trp.popup.verification-popup.workplace.error'),
        ] );

    }


    public function verification_dentist($locale=null) {

        if (request('user_id')) {

            $user = User::find(request('user_id'));

            $validator = Validator::make(Request::all(), [
                'short_description' => array('required', 'max:150'),
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

                $user->short_description = Request::Input('short_description');
                $user->save();

                return Response::json( [
                    'success' => true,
                    'user' => $user->is_clinic ? 'clinic' : 'dentist',
                    'message' => trans('trp.popup.verification-popup.user-info.success'),
                ] );
            }
        }

        return Response::json( [
            'success' => false,
            'message' => trans('trp.popup.verification-popup.user-info.error'),
        ] );

    }

    public function forgot($locale=null) {

		return $this->ShowView('forgot-password',[
            'extra_body_class' => 'white-header',
        ]);
    }

    public function forgot_form($locale=null) {

		$user = User::where([
            ['email','LIKE', Request::input('email') ],
            ['is_dentist', 1 ],
        ])->first();

    	if(empty($user->id)) {
            Request::session()->flash('error-message', trans('trp.popup.registration.email-error'));
            return redirect( getLangUrl('forgot-password') );
        }

        $user->sendTemplate(13);

        Request::session()->flash('success-message', trans('trp.popup.registration.email-success'));
        return redirect( getLangUrl('forgot-password') );
    }

    public function recover($locale=null, $id, $hash) {

        $user = User::find($id);

        if (!empty($user)) {

            if ($hash == $user->get_token()) {

                return $this->ShowView('recover-password', array(
                    'extra_body_class' => 'white-header',
                    'id' => $id,
                    'hash' => $hash,
                ));
            }
        }
        else {
            return redirect('');
        }
    }

    public function recover_form($locale=null, $id, $hash) {

        $user = User::find($id);

        if (!empty($user)) {

            if ($hash == $user->get_token()) {
                $validator = Validator::make(Request::all(), [
                    'password' => array('required', 'min:6'),
                    'password-repeat' => 'required|same:password',
                ]);

                if ($validator->fails()) {
                    return redirect( getLangUrl('recover/'.$id.'/'.$hash))
                    ->withInput()
                    ->withErrors($validator);
                } else {
                    
                    $user->password = bcrypt(Request::input('password'));
                    $user->save();

                    return $this->ShowView('recover-password', array(
                        'extra_body_class' => 'white-header',
                        'id' => $id,
                        'hash' => $hash,
                        'changed' => true
                    ));
                }
            }
        }
    }

    

    public function civic() {
        $ret = [
            'success' => false
        ];

        $jwt = Request::input('jwtToken');
        $civic = Civic::where('jwtToken', 'LIKE', $jwt)->first();
        if(!empty($civic)) {
            $data = json_decode($civic->response, true);
            if(!empty($data['userId'])) {

                //dd($data);
                $email = null;
                $phone = null;

                if(!empty($data['data'])) {
                    foreach ($data['data'] as $dd) {
                        if($dd['label'] == 'contact.personal.email' && $dd['isOwner'] && $dd['isValid']) {
                            $email = $dd['value'];
                        }
                        if($dd['label'] == 'contact.personal.phoneNumber' && $dd['isOwner'] && $dd['isValid']) {
                            $phone = $dd['value'];
                        }
                    }
                }

                if(empty($email) || empty($phone)) {
                    $ret['weak'] = true;
                } else {

                    $user = User::where( 'civic_id','LIKE', $data['userId'] )->withTrashed()->first();
                    if(empty($user) && $email) {
                        $user = User::where( 'email','LIKE', $email )->withTrashed()->first();            
                    }


                    if( $user ) {
                        if($user->deleted_at || $user->isBanned('trp')) {
                            $ret['popup'] = 'banned-popup';
                        } else if( $user->loggedFromBadIp() ) {
                            $ret['popup'] = 'suspended-popup';
                        } else if($user->self_deleted) {
                            return Response::json( [
                                'success' => false, 
                                'message' => 'Unable to sign you up for security reasons.',
                            ] );
                        } else {

                            $existing_phone = User::where('id', '!=', $user->id)->where('phone', 'LIKE', $phone)->first();

                            if ($existing_phone) {
                                return Response::json( [
                                    'success' => false, 
                                    'message' => 'User with this phone number already exists.',
                                ] );
                            }

                            Auth::login($user, true);
                            if(empty($user->civic_id)) {
                                $user->civic_id = $data['userId'];
                                $user->save();      
                            }

                            Request::session()->flash('success-message', trans('trp.popup.registration.have-account'));
                            $ret['success'] = true;
                            $ret['redirect'] = getLangUrl('/');
                        }
                    } else {

                        $existing_phone = User::where('phone', 'LIKE', $phone)->first();

                        if ($existing_phone) {
                            return Response::json( [
                                'success' => false, 
                                'message' => 'User with this phone number already exists.',
                            ] );
                        }

                        $name = explode('@', $email)[0];


                        $is_blocked = User::checkBlocks( $name , $email );
                        if( $is_blocked ) {
                            return Response::json( [
                                'success' => false, 
                                'messages' => [
                                    'name' => $is_blocked
                                ]
                            ] );
                        }                        

                        $password = $name.date('WY');
                        $newuser = new User;
                        $newuser->name = $name;
                        $newuser->email = $email ? $email : '';
                        $newuser->phone = $phone ? $phone : '';
                        $newuser->password = bcrypt($password);
                        $newuser->country_id = $this->country_id;
                        $newuser->is_dentist = 0;
                        $newuser->is_clinic = 0;
                        $newuser->civic_id = $data['userId'];
                        $newuser->gdpr_privacy = true;
                        $newuser->platform = 'trp';
                        $newuser->status = 'approved';
                        
                        if(!empty(session('invited_by'))) {
                            $newuser->invited_by = session('invited_by');
                        }
                        if(!empty(session('invite_secret'))) {
                            $newuser->invite_secret = session('invite_secret');
                        }
                        
                        $newuser->save();

                        if($newuser->invited_by && $newuser->invitor->canInvite('trp') && !empty(session('invitation_id'))) {
                            $inv_id = session('invitation_id');
                            $inv = UserInvite::find($inv_id);

                            if (!empty($inv) && empty($inv->invited_id)) {
                                $inv->invited_id = $newuser->id;

                                if ($inv->invited_email == 'whatsapp') {
                                    $inv->invited_email = $newuser->email;
                                    $inv->invited_name = $newuser->name;
                                }
                                $inv->save();
                                
                                // $newuser->invitor->sendTemplate( $newuser->invitor->is_dentist ? 18 : 19, [
                                //     'who_joined_name' => $newuser->getName()
                                // ] );
                            }
                        }

                        $sess = [
                            'invited_by' => null,
                            'invitation_name' => null,
                            'invitation_email' => null,
                            'invitation_id' => null,
                            'just_registered' => true,
                            'civic_registered' => true,
                        ];
                        session($sess);

                        if( $newuser->loggedFromBadIp() ) {
                            $ret['popup'] = 'suspended-popup';
                            $ret['success'] = false;
                        } else {
                            
                            if( $newuser->email ) {
                                $newuser->sendGridTemplate( 4 );
                            }

                            Auth::login($newuser, true);


                            //
                            //To be deleted
                            //

                            $notifyMe = [
                                'official@youpluswe.com',
                                'petya.ivanova@dentacoin.com',
                                'donika.kraeva@dentacoin.com',
                                //'daria.kerancheva@dentacoin.com',
                                'petar.stoykov@dentacoin.com'
                            ];
                            $mtext = 'New patient registered in TRP: '.$newuser->getName().' (https://reviews.dentacoin.com/cms/users/edit/'.$newuser->id.')';

                            foreach ($notifyMe as $n) {
                                Mail::raw($mtext, function ($message) use ($n) {
                                    $message->from(config('mail.from.address'), config('mail.from.name'));
                                    $message->to( $n );
                                    $message->subject('New TRP registration');
                                });
                            }

                            //
                            //To be deleted
                            //

                            $want_to_invite = false;
                            if(session('want_to_invite_dentist')) {
                                $want_to_invite = true;
                                session([
                                    'want_to_invite_dentist' => null,
                                ]);
                            }

                            $ret['success'] = true;
                            $ret['redirect'] = $newuser->invited_by && $newuser->invitor->is_dentist ? $newuser->invitor->getLink().'?'. http_build_query(['popup'=>'submit-review-popup']) : getLangUrl('/').($want_to_invite ? '?'.http_build_query(['popup'=>'invite-new-dentist-popup']) : '' );
                        }

                    }
                    
                }

            } else {
                $ret['weak'] = true;
            }
        }

        
        return Response::json( $ret );
    }

    public function saveIncompleteRegistration($email, $data) {
        $item = IncompleteRegistration::where('email', 'like', $email)->first();
        if(!$item) {
            $item = new IncompleteRegistration;
            $item->email = $email;
        }

        foreach ($data as $key => $value) {
            $item->$key = $value;
        }
        $item->save();

        session([
            'incomplete-registration' => $item->id
        ]);
    }

    public function completeRegistration($email) {
        IncompleteRegistration::where('email', 'like', $email)->update([
            'completed' => 1,
        ]);
        session([
            'incomplete-registration' => null
        ]);
    }
}