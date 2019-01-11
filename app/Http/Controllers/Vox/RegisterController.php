<?php

namespace App\Http\Controllers\Vox;
use App\Http\Controllers\FrontController;

use App\Models\User;
use App\Models\UserInvite;
use App\Models\Country;
use App\Models\City;
use App\Models\Civic;
use Carbon\Carbon;

use Validator;
use Auth;
use Request;
use Response;
use Image;
use Mail;
use Cookie;
use Illuminate\Support\Facades\Input;

class RegisterController extends FrontController
{
    public function register($locale=null) {
        $this->current_page = 'register';

        if(Request::isMethod('post')) {

            $validator = Validator::make(Request::all(), [
                'name' => array('required', 'min:3'),
                'email' => array('required', 'email', 'unique:users,email'),
                'country_id' => array('required', 'numeric'),
                'password' => array('required', 'min:6'),
                'password-repeat' => 'required|same:password',
                'country_id' => array('required', 'exists:countries,id'),
                'address' =>  array('required', 'string'),
                'privacy' =>  array('required', 'accepted'),
                'photo' =>  array('required'),
                'website' =>  array('required', 'url'),
                'phone' =>  array('required', 'regex: /^[- +()]*[0-9][- +()0-9]*$/u'),
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
                    'remoteip' => Request::ip()
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
                            'captcha' => trans('front.page.registration.captcha')
                        )
                    );

                    return Response::json( $ret );
                }  

                $phone = null;
                $c = Country::find( Request::Input('country_id') );
                if( Request::input('type')=='clinic' ) {
                    $phone = ltrim( str_replace(' ', '', Request::Input('phone')), '0');
                    $pn = $c->phone_code.' '.$phone;

                    $validator = Validator::make(['phone' => $pn], [
                        'phone' => ['required','phone:'.$c->code],
                    ]);


                    if ($validator->fails()) {
                        return Response::json( [
                            'success' => false, 
                            'messages' => [
                                'phone' => trans('front.page.registration.phone')
                            ]
                        ] );
                    }
                }


                $info = User::validateAddress( $c->name, request('address') );
                if(empty($info)) {
                    return Response::json( [
                        'success' => false, 
                        'messages' => [
                            'address' => trans('vox.common.invalid-address')
                        ]
                    ] );
                }

                
                $newuser = new User;
                $newuser->name = Request::input('name');
                $newuser->email = Request::input('email');
                $newuser->country_id = Request::input('country_id');
                $newuser->password = bcrypt(Request::input('password'));
                $newuser->phone = $phone;
                $newuser->platform = 'vox';
                $newuser->address = Request::input('address');
                $newuser->website = Request::input('website');
                
                $newuser->gdpr_privacy = true;
                $newuser->is_dentist = 1;

                if(!empty(session('invited_by'))) {
                    $newuser->invited_by = session('invited_by');
                }
                if(!empty(session('invite_secret'))) {
                    $newuser->invite_secret = session('invite_secret');
                }
                
                $newuser->save();

                if($newuser->invited_by && $newuser->invitor->canInvite('vox')) {
                    $inv_id = session('invitation_id');
                    if($inv_id) {
                        $inv = UserInvite::find($inv_id);
                    } else {
                        $inv = new UserInvite;
                        $inv->user_id = $newuser->invited_by;
                        $inv->invited_email = $newuser->email;
                        $inv->invited_name = $newuser->name;
                        $inv->save();
                    }

                    $inv->invited_id = $newuser->id;
                    $inv->save();

                    // $newuser->invitor->sendTemplate( 26, [
                    //     'who_joined_name' => $newuser->getName()
                    // ] );
                }

                $sess = [
                    'invited_by' => null,
                    'invitation_name' => null,
                    'invitation_email' => null,
                    'invitation_id' => null,
                    'just_registered' => true,
                ];
                session($sess);

                if( Request::input('photo') ) {
                    $img = Image::make( User::getTempImagePath( Request::input('photo') ) )->orientate();
                    $newuser->addImage($img);
                }

                // if( $newuser->email ) {
                //     $newuser->sendTemplate( 12 );
                // }


                if ($newuser->loggedFromBadIp()) {
                    return Response::json( [
                        'success' => false,
                        'popup' => 'suspended-popup',
                    ] );
                }

                Auth::login($newuser, Request::input('remember'));

                Request::session()->flash('success-message', trans('front.page.registration.success-dentist'));
                return Response::json( [
                    'success' => true,
                    'url' => getLangUrl('welcome-to-dentavox'),
                ] );

            }
        } else {
            return $this->ShowVoxView('register', array(
                'countries' => Country::get(),
                'js' => [
                    'register.js',
                    'address.js',
                ],
                'jscdn' => [
                    'https://hosted-sip.civic.com/js/civic.sip.min.js',
                    'https://maps.googleapis.com/maps/api/js?key=AIzaSyCaVeHq_LOhQndssbmw-aDnlMwUG73yCdk&libraries=places&callback=initMap&language=en',
                ],
                'csscdn' => [
                    'https://hosted-sip.civic.com/css/civic-modal.min.css',
                ],

            ));
        }
    }

    public function upload($locale=null) {

        if( Request::file('image') && Request::file('image')->isValid() ) {
            $img = Image::make( Input::file('image') )->orientate();
            list($thumb, $full, $name) = User::addTempImage($img);
            return Response::json(['success' => true, 'thumb' => $thumb, 'name' => $name ]);
        }
    }

    public function check_step_one() {
        $this->current_page = 'register';

        $validator = Validator::make(Request::all(), [
            'name' => array('required', 'min:3'),
            'email' => array('required', 'email', 'unique:users,email'),
            'password' => array('required', 'min:6'),
            'password-repeat' => 'required|same:password',
            'privacy' =>  array('required', 'accepted'),
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
            
            $is_blocked = User::checkBlocks( Request::input('name') , Request::input('email') );
            if( $is_blocked ) {
                return Response::json( [
                    'success' => false, 
                    'messages' => [
                        'name' => $is_blocked
                    ]
                ] );          
            }

            return Response::json( ['success' => true] );
        }
    }

    public function register_success($locale=null) {
        $this->user->checkForWelcomeCompletion();
        if($this->user->is_dentist && $this->user->status!='approved') {
            if(Request::isMethod('post')) {

                $newuser = $this->user;

                $mtext = 'New Dentavox dentist/clinic registration:

                '.url('https://reviews.dentacoin.com/cms/users/edit/'.$newuser->id).'

                ';

                Mail::raw($mtext, function ($message) use ($newuser) {

                    //$receiver = 'official@youpluswe.com';
                    $receiver = 'ali.hashem@dentacoin.com';
                    $sender = config('mail.from.address-vox');
                    $sender_name = config('mail.from.name-vox');

                    $message->from($sender, $sender_name);
                    $message->to( $receiver );
                    //$message->to( 'dokinator@gmail.com' );
                    $message->replyTo($receiver, $newuser->getName());
                    $message->subject('New Dentavox Dentist/Clinic registration');
                });

                session([
                    'approval-request-sent' => true
                ]);
            }


            return $this->ShowVoxView('register-success-dentist',[
                'request_sent' => session('approval-request-sent')
            ]);
        } else {
            return $this->ShowVoxView('register-success');            
        }
    }

    public function invite_accept($locale=null, $id, $hash, $inv_id=null) {

        $user = User::find($id);

        if (!empty($user) && $user->canInvite('vox')) {

            if ($hash == $user->get_invite_token()) {

                if($this->user) {
                    if($this->user->id==$user->id) {
                        Request::session()->flash('success-message', trans('vox.page.registration.invite-yourself'));
                    } else {
                        if(!$this->user->wasInvitedBy($user->id)) {
                            $inv = UserInvite::find($inv_id);
                            if(empty($inv)) {
                                $inv = UserInvite::where('user_id', $user->id)->where('invited_email', 'LIKE', $this->user->email)->first();
                            }
                            if(empty($inv)) {
                                $inv = new UserInvite;
                                $inv->user_id = $user->id;
                            }
                            $inv->invited_name = $this->user->name;
                            $inv->invited_email = $this->user->email;
                            $inv->invited_id = $this->user->id;
                            $inv->save();
                        }
                        Request::session()->flash('success-message', trans('vox.page.registration.invitation-registered', [ 'name' => $user->name ]));
                    }
                    return redirect( getLangUrl('/') );
                } else {
                    $sess = [
                        'invited_by' => $user->id,
                    ];
                    $inv = UserInvite::find($inv_id);
                    if(!empty($inv)) {
                        $sess['invitation_name'] = $inv->invited_name;
                        $sess['invitation_email'] = $inv->invited_email;
                        $sess['invitation_id'] = $inv->id;
                    }
                    session($sess);

                    Request::session()->flash('success-message', trans('vox.page.registration.invitation', [ 'name' => $user->name ]));
                    return redirect( getLangUrl('/').'#register' ); 
                }

            }
        } else {
            return redirect('/');
        }
    }

    public function forgot($locale=null) {
        $this->current_page = 'forgot-password';
        return $this->ShowVoxView('forgot-password');
    }

    public function forgot_form($locale=null) {

        $user = User::where([
            ['email','LIKE', Request::input('email') ]
        ])->first();

        if(empty($user->id)) {
            Request::session()->flash('error-message', trans('vox.page.registration.email-error'));
            return redirect( getLangUrl('recover-password') );
        }

        $user->sendTemplate(13);

        Request::session()->flash('success-message', trans('vox.page.registration.email-success'));
        return redirect( getLangUrl('recover-password') );
    }

    public function recover($locale=null, $id, $hash) {

        $user = User::find($id);

        if (!empty($user)) {

            if ($hash == $user->get_token()) {

                return $this->ShowVoxView('recover-password', array(
                    'id' => $id,
                    'hash' => $hash,
                ));
            }
        }
        else {
            return redirect('/');
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

                    Request::session()->flash('success-message', trans('vox.page.recover.success', [
                        'link' => '<a href="'.getLangUrl('login').'">',
                        'endlink' => '</a>',
                    ]));
                    return redirect( getLangUrl('recover/'.$id.'/'.$hash) );
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

                if(empty($email)) {
                    $ret['weak'] = true;
                } else {

                    $user = User::where( 'civic_id','LIKE', $data['userId'] )->withTrashed()->first();
                    if(empty($user) && $email) {
                        $user = User::where( 'email','LIKE', $email )->withTrashed()->first();            
                    }


                    if( $user ) {
                        if($user->deleted_at) {
                            $ret['message'] = 'You have been permanently banned and cannot return to Dentavox anymore.';
                        } else if( $user->isBanned('vox') ) {
                            $ret['message'] = trans('front.page.login.vox-ban');
                        } else {
                            Auth::login($user, true);
                            if(empty($user->civic_id)) {
                                $user->civic_id = $data['userId'];
                                $user->save();      
                            }

                            Request::session()->flash('success-message', trans('vox.popup.register.have-account'));
                            $ret['success'] = true;
                            $ret['redirect'] = getLangUrl('/');
                        }
                    } else {

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
                        $newuser->password = bcrypt($password);
                        $newuser->is_dentist = 0;
                        $newuser->is_clinic = 0;
                        $newuser->civic_id = $data['userId'];
                        $newuser->gdpr_privacy = true;
                        $newuser->platform = 'vox';
                        $newuser->status = 'approved';
                        
                        if(!empty(session('invited_by'))) {
                            $newuser->invited_by = session('invited_by');
                        }
                        if(!empty(session('invite_secret'))) {
                            $newuser->invite_secret = session('invite_secret');
                        }
                        
                        $newuser->save();

                        if($newuser->invited_by && $newuser->invitor->canInvite('vox')) {
                            $inv_id = session('invitation_id');
                            if($inv_id) {
                                $inv = UserInvite::find($inv_id);
                            } else {
                                $inv = new UserInvite;
                                $inv->user_id = $newuser->invited_by;
                                $inv->invited_email = $newuser->email;
                                $inv->invited_name = $newuser->name;
                                $inv->save();
                            }

                            $inv->invited_id = $newuser->id;
                            $inv->save();

                            $newuser->invitor->sendTemplate( $newuser->invitor->is_dentist ? 18 : 19, [
                                'who_joined_name' => $newuser->getName()
                            ] );
                        }

                        $sess = [
                            'invited_by' => null,
                            'invitation_name' => null,
                            'invitation_email' => null,
                            'invitation_id' => null,
                            'just_registered' => true,
                        ];
                        session($sess);

                        if( $newuser->email ) {
                            $newuser->sendTemplate( 12 );
                        }

                        if ($newuser->loggedFromBadIp()) {
                            $ret['success'] = false;
                            $ret['popup'] = 'suspended-popup';
                        } else {

                            Auth::login($newuser, true);

                            Request::session()->flash('success-message', trans('vox.page.registration.success'));
                            $ret['success'] = true;
                            $ret['redirect'] = getLangUrl('welcome-to-dentavox');
                        }

                    }
                    
                }

            } else {
                $ret['weak'] = true;
            }
        }

        
        return Response::json( $ret );
    }
}