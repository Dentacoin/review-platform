<?php

namespace App\Http\Controllers\Vox;
use App\Http\Controllers\FrontController;

use App\Models\User;
use App\Models\UserCategory;
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

        $test_country_id = null;
        $has_test = !empty($_COOKIE['first_test']) ? json_decode($_COOKIE['first_test'], true) : null;
        if ($has_test && !empty($has_test['location'])) {
            $test_country_id = $has_test['location'];
        }

        // if (empty($_COOKIE['first_test'])) {
        //     return redirect(getLangUrl('/'));
        // }

        if(Request::isMethod('post')) {

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
                'address' =>  array('required', 'string'),
                'privacy' =>  array('required', 'accepted'),
                'photo' =>  array('required'),
                'website' =>  array('required', 'regex:/^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/'),
                'phone' =>  array('required', 'regex: /^[- +()]*[0-9][- +()0-9]*$/u'),
                'specialization' =>  array('required', 'array'),
            ]);

            if ($validator->fails()) {

                $msg = $validator->getMessageBag()->toArray();
                $ret = array(
                    'success' => false,
                    'messages' => array()
                );

                return redirect(getLangUrl('registration'))
                ->withInput()
                ->withErrors($validator);
            } else {

                // $captcha = false;
                // $cpost = [
                //     'secret' => env('CAPTCHA_SECRET'),
                //     'response' => Request::input('g-recaptcha-response'),
                //     'remoteip' => User::getRealIp()
                // ];
                // $ch = curl_init('https://www.google.com/recaptcha/api/siteverify');
                // curl_setopt($ch, CURLOPT_HEADER, 0);
                // curl_setopt ($ch, CURLOPT_POST, 1);
                // curl_setopt ($ch, CURLOPT_POSTFIELDS, http_build_query($cpost));
                // curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);    
                // curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
                // $response = curl_exec($ch);
                // curl_close($ch);
                // if($response) {
                //     $api_response = json_decode($response, true);
                //     if(!empty($api_response['success'])) {
                //         $captcha = true;
                //     }
                // }

                // if( !$captcha ) {
                //     $ret = array(
                //         'success' => false,
                //         'messages' => array(
                //             'captcha' => trans('front.page.registration.captcha')
                //         )
                //     );

                //     return Response::json( $ret );
                // }

                $info = User::validateAddress( Country::find(request('country_id'))->name, request('address') );
                if(empty($info)) {
                    Request::session()->flash('error-message', trans('vox.common.invalid-address'));
                    return redirect( getLangUrl('registration'));
                }

                if(User::validateLatin(Request::input('name')) == false) {
                    Request::session()->flash('error-message', trans('vox.common.invalid-name'));
                    return redirect( getLangUrl('registration'));
                }

                if(User::validateEmail(Request::input('email')) == true) {
                    Request::session()->flash('error-message', trans('vox.common.invalid-email'));
                    return redirect( getLangUrl('registration'));
                }

                
                $newuser = new User;
                $newuser->title = Request::input('title');
                $newuser->name = Request::input('name');
                $newuser->name_alternative = Request::input('name_alternative');
                $newuser->email = Request::input('email');
                $newuser->country_id = Request::input('country_id');
                $newuser->password = bcrypt(Request::input('password'));
                $newuser->phone = Request::input('phone');
                $newuser->platform = 'vox';
                $newuser->address = Request::input('address');
                $newuser->website = Request::input('website');
                
                $newuser->gdpr_privacy = true;
                $newuser->is_dentist = 1;
                $newuser->is_clinic = Request::input('mode')=='clinic' ? 1 : 0;

                if(!empty(session('invited_by'))) {
                    $newuser->invited_by = session('invited_by');
                }
                if(!empty(session('invite_secret'))) {
                    $newuser->invite_secret = session('invite_secret');
                }
                
                $newuser->save();

                // $newuser->slug = $newuser->makeSlug();
                // $newuser->save();

                UserCategory::where('user_id', $newuser->id)->delete();
                if(!empty(Request::input('specialization'))) {
                    foreach (Request::input('specialization') as $cat) {
                        $newc = new UserCategory;
                        $newc->user_id = $newuser->id;
                        $newc->category_id = $cat;
                        $newc->save();
                    }
                }

                $sess = [
                    'invited_by' => null,
                    'invitation_name' => null,
                    'invitation_email' => null,
                    'invitation_id' => null,
                    'just_registered' => true,
                    'just_registered_dentist_vox' => true,
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
                    Request::session()->flash('error-message', trans('vox.common.invalid-email'));
                    return redirect( getVoxUrl('/').'?suspended-popup' );
                }

                $mtext = 'New Dentavox dentist/clinic registration:
                '.$newuser->getName().'
                IP: '.User::getRealIp().'
                '.(!empty(Auth::guard('admin')->user()) ? 'This is a Dentacoin ADMIN' : '').'
                '.url('https://dentavox.dentacoin.com/cms/users/edit/'.$newuser->id).'
                ';

                Mail::raw($mtext, function ($message) use ($newuser) {

                    $sender = config('mail.from.address-vox');
                    $sender_name = config('mail.from.name-vox');

                    $message->from($sender, $sender_name);
                    $message->to( 'ali.hashem@dentacoin.com' );
                    $message->to( 'betina.bogdanova@dentacoin.com' );
                    $message->replyTo($newuser->email, $newuser->getName());
                    $message->subject('New Dentavox Dentist/Clinic registration');
                });

                session([
                    'success_registered_dentist_vox' => true,
                ]);

                Auth::login($newuser, Request::input('remember'));

                Request::session()->flash('success-message', trans('front.page.registration.success-dentist'));
                return redirect(getVoxUrl('welcome-to-dentavox'));

            }
        } else {

            if (request()->getHost() == 'dentavox.dentacoin.com') {
                return redirect('https://vox.dentacoin.com/en/registration');
            }

            return $this->ShowVoxView('register', array(
                'test_country_id' => $test_country_id,
                'noindex' => ' ',
                'countries' => Country::with('translations')->get(),
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

            if(User::validateEmail(Request::input('email')) == true) {
                $ret = array(
                    'success' => false,
                    'messages' =>[
                        'email' => trans('vox.common.invalid-email')
                    ]
                );
                return Response::json( $ret );
            }

            return Response::json( ['success' => true] );
        }
    }

    public function check_step_two() {
        $this->current_page = 'register';

        $validator = Validator::make(Request::all(), [
            'mode' => array('required', 'in:dentist,clinic'),
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

            if(User::validateLatin(Request::input('name')) == false) {
                return Response::json( [
                    'success' => false, 
                    'messages' => [
                        'name' => trans('vox.common.invalid-name')
                    ]
                ] );
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

            return Response::json( ['success' => true] );
        }
    }

    public function check_step_three() {
        $this->current_page = 'register';

        if (request('website') && mb_strpos(mb_strtolower(request('website')), 'http') !== 0) {
            request()->merge([
                'website' => 'http://'.request('website')
            ]);
        }

        $validator = Validator::make(Request::all(), [
            'country_id' => array('required', 'exists:countries,id'),
            'address' =>  array('required', 'string'),
            'privacy' =>  array('required', 'accepted'),
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
                $ret['messages'][$field] = implode(', ', $errors);
            }

            return Response::json( $ret );
        } else {

            $info = User::validateAddress( Country::find(request('country_id'))->name, request('address') );
            if(empty($info)) {
                return Response::json( [
                    'success' => false, 
                    'messages' => [
                        'address' => trans('vox.common.invalid-address')
                    ]
                ] );
            }

            return Response::json( ['success' => true] );
        }
    }

    public function check_step_four() {
        $this->current_page = 'register';

        $validator = Validator::make(Request::all(), [
            'photo' =>  array('required'),
            'specialization' =>  array('required', 'array'),
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
                        'captcha' => trans('front.page.registration.captcha')
                    )
                );

                return Response::json( $ret );
            }

            return Response::json( ['success' => true] );
        }
    }

    public function register_success($locale=null) {
        $this->user->checkForWelcomeCompletion();
        if($this->user->is_dentist && $this->user->status!='approved' && $this->user->status!='added_approved' && $this->user->status!='admin_imported' && $this->user->status!='test') {
            if(Request::isMethod('post')) {

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

                    $this->user->short_description = Request::Input('short_description');
                    $this->user->save();

                    return Response::json( [
                        'success' => true,
                        'message' => trans('trp.popup.verification-popup.user-info.success'),
                    ] );
                }
            }


            return $this->ShowVoxView('register-success-dentist');
        } else {
            return redirect( getLangUrl('/') );
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

                if (!empty($user) && $user->canInvite('vox')) {

                    if ($hash == $user->get_invite_token()) {
                        // check for GET variables and build query string
                        $get = count($_GET) ? ('?' . http_build_query($_GET)) : '';

                        if($this->user) {
                            if($this->user->id==$user->id) {
                                Request::session()->flash('error-message', trans('vox.page.registration.invite-yourself'));
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
                            return redirect( getLangUrl('/').$get );
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
                            return redirect( getLangUrl('paid-dental-surveys').'#register'.$get ); 
                        }

                    }
                }
            }
        }
        return redirect('/');
    }

    public function forgot($locale=null) {
        $this->current_page = 'forgot-password';
        return $this->ShowVoxView('forgot-password', array(
            'canonical' => getLangUrl('recover-password'),
            'noindex' => ' ',
        ));
    }

    public function forgot_form($locale=null) {

        if (!empty(Request::input('email'))) {
            $user = User::where([
                ['email','LIKE', Request::input('email') ],
                ['is_dentist', 1 ],
            ])->first();

            if(empty($user->id)) {
                Request::session()->flash('error-message', trans('vox.page.recover-password.email-error'));
                return redirect( getLangUrl('recover-password') );
            }

            $user->sendTemplate(13);

            Request::session()->flash('success-message', trans('vox.page.recover-password.email-success'));
            return redirect( getLangUrl('recover-password') );
        } else {
            Request::session()->flash('error-message', 'Please enter valid email');
            return redirect( getLangUrl('recover-password') );
        }
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
        return redirect('/');
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

        return redirect('/');
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
                } else if(!empty(User::where( 'email','LIKE', $email )->withTrashed()->first())) {
                    $ret['message'] = 'User with this email already exists';
                } else {

                    $user = User::where( 'civic_id','LIKE', $data['userId'] )->withTrashed()->first();
                    if(empty($user)) {
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

                        $has_test = !empty($_COOKIE['first_test']) ? json_decode($_COOKIE['first_test'], true) : null;

                        $password = $name.date('WY');
                        $newuser = new User;
                        $newuser->name = $name;
                        $newuser->email = $email ? $email : '';
                        $newuser->phone = $phone ? $phone : '';
                        $newuser->password = bcrypt($password);
                        $newuser->country_id = $has_test ? $has_test['location'] : $this->country_id;
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

                        $newuser->slug = $newuser->makeSlug();
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

                            // $newuser->invitor->sendTemplate( $newuser->invitor->is_dentist ? 18 : 19, [
                            //     'who_joined_name' => $newuser->getName()
                            // ] );
                        }

                        $sess = [
                            'invited_by' => null,
                            'invitation_name' => null,
                            'invitation_email' => null,
                            'invitation_id' => null,
                            'just_registered' => true,
                            'just_registered_patient_vox' => true,
                        ];
                        session($sess);

                        if( $newuser->email ) {
                            $newuser->sendGridTemplate( 12 );
                        }

                        if ($newuser->loggedFromBadIp()) {
                            $ret['success'] = false;
                            $ret['popup'] = 'suspended-popup';
                        } else {

                            Auth::login($newuser, true);

                            Request::session()->flash('success-message', trans('vox.page.registration.success'));
                            $ret['success'] = true;
                            $ret['redirect'] = getVoxUrl('/');
                        }

                    }
                    
                }

            } else {
                $ret['weak'] = true;
            }
        }

        
        return Response::json( $ret );
    }

    public function new_civic_register($locale=null) {

        $jwt = Request::input('jwttoken');
        $civic = Civic::where('jwttoken', 'LIKE', $jwt)->first();
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
                    return redirect(getVoxUrl('/').'?error-message='.urlencode('Please add an email address to your Civic account and try again.'));
                } else if(empty($phone)) {
                    return redirect(getVoxUrl('/').'?error-message='.urlencode('Please add a phone number to your Civic account and try again.'));
                } else {

                    $user = User::where( 'civic_id','LIKE', $data['userId'] )->withTrashed()->first();
                    if(empty($user) && $email) {
                        $user = User::where( 'email','LIKE', $email )->withTrashed()->first();            
                    }


                    if( $user ) {
                        if($user->deleted_at || $user->isBanned('vox')) {
                            return redirect(getVoxUrl('/').'?error-message='.urlencode('You have been permanently banned and cannot return to Dentavox anymore.' ));
                        } else if($user->loggedFromBadIp()) {
                            return redirect(getVoxUrl('/').'?error-message='.urlencode('We have detected a suspicious activity from your IP address.'));
                        } else if($user->self_deleted) {
                            return redirect(getVoxUrl('/').'?error-message='.urlencode('Unable to sign you up for security reasons.'));
                        } else {
                            $existing_phone = User::where('id', '!=', $user->id)->where('phone', 'LIKE', $phone)->first();

                            if ($existing_phone) {
                                return redirect(getVoxUrl('/').'?error-message='.urlencode('User with this phone number already exists'));
                            }

                            Auth::login($user, true);
                            if(empty($user->civic_id)) {
                                $user->civic_id = $data['userId'];
                                $user->save();      
                            }

                            return redirect(getVoxUrl('/').'?success-message='.urlencode(trans('vox.popup.register.have-account') ));
                        }
                    } else {

                        $existing_phone = User::where('phone', 'LIKE', $phone)->first();

                        if ($existing_phone) {
                            return redirect(getVoxUrl('/').'?error-message='.urlencode('User with this phone number already exists'));
                        }

                        $name = explode('@', $email)[0];


                        $is_blocked = User::checkBlocks( $name , $email );
                        if( $is_blocked ) {
                            return redirect(getVoxUrl('/').'?error-message='.urlencode(trans('front.common.civic.error') ));
                        }

                        $has_test = !empty($_COOKIE['first_test']) ? json_decode($_COOKIE['first_test'], true) : null;

                        $password = $name.date('WY');
                        $newuser = new User;
                        $newuser->name = $name;
                        $newuser->email = $email ? $email : '';
                        $newuser->phone = $phone ? $phone : '';
                        $newuser->password = bcrypt($password);
                        $newuser->country_id = $has_test ? $has_test['location'] : $this->country_id;
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

                        $newuser->slug = $newuser->makeSlug();
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

                            // $newuser->invitor->sendTemplate( $newuser->invitor->is_dentist ? 18 : 19, [
                            //     'who_joined_name' => $newuser->getName()
                            // ] );
                        }

                        $sess = [
                            'invited_by' => null,
                            'invitation_name' => null,
                            'invitation_email' => null,
                            'invitation_id' => null,
                            'just_registered' => true,
                            'just_registered_patient_vox' => true,
                        ];
                        session($sess);

                        if ($newuser->loggedFromBadIp()) {
                            return redirect( getVoxUrl('/').'?suspended-popup' );
                        } else {
                            if( $newuser->email ) {
                                $newuser->sendGridTemplate( 12 );
                            }
                            
                            Auth::login($newuser, true);
                            return redirect(getVoxUrl('/').'?success-message='.urlencode(trans('vox.page.registration.success')) );
                        }

                    }
                    
                }

            } else {
                $ret['weak'] = true;
            }
        }
    }
}