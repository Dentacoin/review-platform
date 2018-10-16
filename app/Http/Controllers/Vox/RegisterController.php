<?php

namespace App\Http\Controllers\Vox;
use App\Http\Controllers\FrontController;

use App\Models\User;
use App\Models\UserInvite;
use App\Models\Blacklist;
use App\Models\Country;
use App\Models\City;
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
                'city_id' => array('required', 'numeric'),
                'password' => array('required', 'min:6'),
                'password-repeat' => 'required|same:password',
                'country_id' => array('required', 'exists:countries,id'),
                'city_id' => array('required', 'exists:cities,id'),
                'zip' => array('required', 'string'),
                'address' =>  array('required', 'string'),
                'privacy' =>  array('required', 'accepted'),
                'photo' =>  array('required'),
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

                
                $newuser = new User;
                $newuser->name = Request::input('name');
                $newuser->email = Request::input('email');
                $newuser->country_id = Request::input('country_id');
                $newuser->city_id = Request::input('city_id');
                $newuser->password = bcrypt(Request::input('password'));
                $newuser->phone = $phone;
                $newuser->zip = Request::input('zip');
                $newuser->address = Request::input('address');
                $newuser->website = Request::input('website');
                
                $newuser->gdpr_privacy = true;
                $newuser->is_dentist = 1;
                $newuser->phone_verified = true;
                $newuser->phone_verified_on = Carbon::now();

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

                    $newuser->invitor->sendTemplate( 26, [
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

                if( Request::input('photo') ) {
                    $img = Image::make( User::getTempImagePath( Request::input('photo') ) )->orientate();
                    $newuser->addImage($img);
                }

                if( $newuser->email ) {
                    $newuser->sendTemplate( 12 );
                }

                $mtext = 'New Dentavox dentist/clinic registration:

                '.url('https://reviews.dentacoin.com/cms/users/edit/'.$newuser->id).'

                ';

                Mail::raw($mtext, function ($message) use ($newuser) {

                    $receiver = 'ali.hashem@dentacoin.com';
                    $sender = config('mail.from.address-vox');
                    $sender_name = config('mail.from.name-vox');

                    $message->from($sender, $sender_name);
                    $message->to( $receiver );
                    //$message->to( 'dokinator@gmail.com' );
                    $message->replyTo($receiver, $newuser->getName());
                    $message->subject('New Dentavox Dentist/Clinic registration');
                });

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

            foreach (Blacklist::get() as $b) {
                if ($b['field'] == 'name') {
                    if (fnmatch(mb_strtolower($b['pattern']), mb_strtolower(Request::input('name'))) == true) {

                        $new_blacklist_block = new BlacklistBlock;
                        $new_blacklist_block->blacklist_id = $b['id'];
                        $new_blacklist_block->name = Request::input('name');
                        $new_blacklist_block->email = Request::input('email');
                        $new_blacklist_block->save();

                        return Response::json( [
                            'success' => false, 
                            'messages' => [
                                'name' => trans('vox.page.registration.error-name')
                            ]
                        ] );
                    }
                } else {
                    if (fnmatch(mb_strtolower($b['pattern']), mb_strtolower(Request::input('email'))) == true) {

                        $new_blacklist_block = new BlacklistBlock;
                        $new_blacklist_block->blacklist_id = $b['id'];
                        $new_blacklist_block->name = Request::input('name');
                        $new_blacklist_block->email = Request::input('email');
                        $new_blacklist_block->save();

                        return Response::json( [
                            'success' => false, 
                            'messages' => [
                                'email' => trans('vox.page.registration.error-email')
                            ]
                        ] );
                    }
                }
            }

            return Response::json( ['success' => true] );
        }
    }

    public function register_success($locale=null) {
        $this->user->checkForWelcomeCompletion();
        return $this->ShowVoxView('register-success');
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

    public function register_verify($locale=null, $id, $hash) {

        $user = User::find($id);

        if (!empty($user) && !$user->is_verified) {

            if ($hash == $user->get_token()) {

                $user->verified_on = Carbon::now();
                $user->is_verified = true;

                $user->save();

                $user->sendTemplate( 12 );

                Auth::login($user, true);

                Request::session()->flash('success-message', trans('vox.page.registration.profile-confirmed'));
                return redirect( getLangUrl('/'));
            }
        }
        else {
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
}