<?php

namespace App\Http\Controllers\Front;
use App\Http\Controllers\FrontController;

use Validator;
use Response;
use Request;
use Route;
use Hash;
use Mail;
use Auth;
use Image;
use Illuminate\Support\Facades\Input;
use App\Models\User;
use App\Models\UserInvite;
use App\Models\DcnCashout;
use App\Models\Dcn;
use App\Models\Civic;
use App\Models\Country;
use App\Models\UserAsk;
use App\Models\UserPhoto;
use App\Models\UserCategory;
use App\Models\UserTeam;
use App\Models\DcnReward;
use App\Models\Reward;
use App\Models\Review;
use Carbon\Carbon;


use DeviceDetector\DeviceDetector;
use DeviceDetector\Parser\Device\DeviceParserAbstract;


class ProfileController extends FrontController
{

    public function __construct(\Illuminate\Http\Request $request, Route $route, $locale=null) {
        parent::__construct($request, $route, $locale);

        $this->menu = [
            'home' => trans('trp.page.profile.menu.wallet'),
            'info' => trans('trp.page.profile.menu.info'),
            'privacy' => trans('trp.page.profile.menu.privacy'),
            'invite' => trans('trp.page.profile.menu.invite-patient'),
        ];

        $this->profile_fields = [
            'title' => [
                'type' => 'select',
                'required' => true,
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
            'email' => [
                'type' => 'text',
                'required' => true,
                'is_email' => true,
            ],
            'phone' => [
                'type' => 'text',
                'required' => true,
                'regex' => 'regex: /^[- +()]*[0-9][- +()0-9]*$/u',
            ],
            'country_id' => [
                'type' => 'country',
                'required' => true,
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
                'required' => true,
                'max' => 150
            ],
            'email_public' => [
                'type' => 'text',
                'required' => false,
                'is_email' => true,
            ],
        ];

        $this->genders = [
            '' => null,
            'm' => trans('admin.common.gender.m'),
            'f' => trans('admin.common.gender.f'),
        ];
    }

    public function handleMenu() {
        if($this->user->is_dentist) {
            $this->menu['invite'] = trans('trp.page.profile.menu.invite-dentist');
            
            if( $this->user->is_clinic ) {
                unset($this->profile_fields['title']);
            }

            if($this->user->asks->isNotEmpty()) {
                $this->menu['asks'] = trans('trp.page.profile.menu.asks');
            }
        } else {
            $this->menu['trp'] = trans('trp.page.profile.menu.trp');
            unset($this->profile_fields['title']);
            unset($this->profile_fields['phone']);
            unset($this->profile_fields['address']);
            unset($this->profile_fields['specialization']);
            unset($this->profile_fields['description']);
            unset($this->profile_fields['short_description']);
            unset($this->profile_fields['website']);
            unset($this->profile_fields['socials']);
            unset($this->profile_fields['name_alternative']);
            unset($this->profile_fields['email_public']);     
            unset($this->profile_fields['accepted_payment']);            
        }
    }


    public function setGrace($locale=null) {
        if(empty($this->user->grace_end)) {
            $this->user->grace_end = Carbon::now();
            $this->user->save();
        }
        session(['new_auth' => null]);
    }

    //
    //Home
    //

    public function home($locale=null) {
        if($this->user->is_dentist && $this->user->status!='approved' && $this->user->status!='added_approved' && $this->user->status!='test') {
            return redirect(getLangUrl('/'));
        }
        $this->handleMenu();

        if(Request::isMethod('post')) {
            $va = trim(Request::input('vox-address'));
            $error = null;
            if(empty($va) || mb_strlen($va)!=42) {
                $error = trans('trp.page.profile.home.address-invalid');
            } else if(!$this->user->canIuseAddress($va)) {
                $error = trans('trp.page.profile.home.address-used');
            } else {
                $this->user->dcn_address = Request::input('vox-address');
                $this->user->save();
            }
            
            if(Request::input('json')) {
                $ret = [
                    'success' => !$error,
                    'message' => $error ? $error : trans('trp.page.profile.home.address-saved')
                ];
                return Response::json( $ret );
            } else {
                if($error) {
                    Request::session()->flash('error-message', $error);                    
                } else {
                    Request::session()->flash('success-message', trans('trp.page.profile.home.address-saved'));
                }
                
                return redirect( getLangUrl('profile'));
            }


        }

        $params = [
            'countries' => Country::get(),
            'menu' => $this->menu,
            'currencies' => file_get_contents('/tmp/dcn_currncies'),
            'history' => $this->user->history->where('type', '=', 'vox-cashout'),
            'js' => [
                'profile.js',
                'address.js',
            ],
            'css' => [
                'common-profile.css',
            ],
            'jscdn' => [
                'https://maps.googleapis.com/maps/api/js?key=AIzaSyCaVeHq_LOhQndssbmw-aDnlMwUG73yCdk&libraries=places&callback=initMap&language=en'
            ]
        ];

        if(!$this->user->civic_kyc) {
            $params['js'][] = 'civic.js';
            if(empty($params['jscdn'])) {
                $params['jscdn'] = [];
            }
            $params['jscdn'][] = 'https://hosted-sip.civic.com/js/civic.sip.min.js';
            $params['csscdn'] = [
                'https://hosted-sip.civic.com/css/civic-modal.min.css',
            ];
        }

        return $this->ShowView('profile', $params);
    }


    public function withdraw($locale=null) {
        if(!$this->user->canWithdraw('trp') ) {
            return;
        }

        $va = trim(Request::input('vox-address'));
        if(empty($va) || mb_strlen($va)!=42) {
            $ret = [
                'success' => false,
                'message' => trans('trp.page.profile.home.address-invalid')
            ];
            return Response::json( $ret );
        } else if(!$this->user->canIuseAddress($va)) {
            $ret = [
                'success' => false,
                'message' => trans('trp.page.profile.home.address-used')
            ];
            return Response::json( $ret );
        } else {
            $this->user->dcn_address = Request::input('vox-address');
            $this->user->save();
        }
        

        $amount = intval(Request::input('wallet-amount'));
        if($amount > $this->user->getTotalBalance('vox')) {
            $ret = [
                'success' => false,
                'message' => trans('trp.page.profile.home.amount-too-high')
            ];
        } else if($amount<env('VOX_MIN_WITHDRAW')) {
            $ret = [
                'success' => false,
                'message' => trans('trp.page.profile.home.amount-too-low', [
                    'minimum' => '<b>'.env('VOX_MIN_WITHDRAW').'</b>'
                ])
            ];
        } else {
            $cashout = new DcnCashout;
            $cashout->user_id = $this->user->id;
            $cashout->platform = 'trp';
            $cashout->reward = $amount;
            $cashout->address = $this->user->dcn_address;
            $cashout->save();

            $ret = Dcn::send($this->user, $this->user->dcn_address, $amount, 'vox-cashout', [$cashout->id]);
            $ret['balance'] = $this->user->getTotalBalance('trp');
            
            if($ret['success']) {
                $cashout->tx_hash = $ret['message'];
                $cashout->save();
            } else if (!empty($ret['valid_input'])) {
                $ret['success'] = true;
            }

            if( empty($ret['success']) ) {
                $cashout->delete();
            }
        }

        return Response::json( $ret );
    }

    //
    //Privacy
    //


    public function privacy($locale=null) {
        if($this->user->is_dentist && $this->user->status!='approved' && $this->user->status!='added_approved' && $this->user->status!='test') {
            return redirect(getLangUrl('/'));
        }
        $this->handleMenu();
        


        if(Request::isMethod('post')) {
            if( Request::input('action') ) {
                if( Request::input('action')=='delete' ) {
                    $this->user->sendTemplate( 30 );
                    $this->user->self_deleted = 1;
                    $this->user->save();
                    $this->user->deleteActions();
                    User::destroy( $this->user->id );
                    session(['login-logged' => true]);
                    Auth::guard('web')->logout();
                    return redirect( getLangUrl('/') );
                }
            }
        }

        return $this->ShowView('profile-privacy', [
            'menu' => $this->menu,
            'css' => [
                'common-profile.css',
            ],
            'js' => [
                'profile.js',
            ],
        ]);
    }

    public function privacy_download($locale=null) {

        $html = $this->showView('users-data', array(
            'genders' => $this->genders,
        ))->render();

        $tmp_path = '/tmp/'.$this->user->id;
        if(!is_dir($tmp_path)) {
            mkdir($tmp_path);
        }

        file_put_contents($tmp_path.'/my-private-info.html', $html);

        if($this->user->hasimage) {
            copy( $this->user->getImagePath(), $tmp_path.'/'.$this->user->id.'.jpg' );
        }

        if($this->user->photos->isNotEmpty()) {
            foreach ($this->user->photos as $photo) {
                copy( $photo->getImagePath(), $tmp_path.'/'.$photo->id.'.jpg' );
            }
        }

        exec('zip -rj0 '.$tmp_path.'.zip '.$tmp_path.'/*');
        exec('rm -rf '.$tmp_path);

        $mtext = $this->user->getName().' just requested his/hers personal information<br/>
User\'s email is: '.$this->user->email.'<br/>
Link to user\'s profile in CMS: https://reviews.dentacoin.com/cms/users/edit/'.$this->user->id;

        Mail::send([], [], function ($message) use ($mtext) {
            $sender = config('mail.from.address');
            $sender_name = config('mail.from.name');

            $message->from($sender, $sender_name);
            $message->to( 'privacy@dentacoin.com' ); //$sender
            //$message->to( 'dokinator@gmail.com' );
            $message->replyTo($sender, $sender_name);
            $message->subject('New Personal Data Download Request');
            $message->attach('/tmp/'.$this->user->id.'.zip');
            $message->setBody($mtext, 'text/html'); // for HTML rich messages
        });

        return Response::download($tmp_path.'.zip', 'your-private-info.zip');
    }

    //
    //Invites
    //

    public function invite($locale=null) {
        if($this->user->is_dentist && $this->user->status!='approved' && $this->user->status!='added_approved' && $this->user->status!='test') {
            return redirect(getLangUrl('/'));
        }
        $this->handleMenu();

        if(Request::isMethod('post') && $this->user->canInvite('trp') ) {

            if(Request::Input('is_contacts')) {
                if(empty(Request::Input('contacts')) || !is_array( Request::Input('contacts') ) ) {
                    return Response::json(['success' => false, 'message' => trans('trp.page.profile.invite.contacts-none-selected') ] );
                }

                foreach (Request::Input('contacts') as $inv_info) {
                    $inv_arr = explode('|', $inv_info);
                    $email = $name = '';
                    if(count($inv_arr)>1) {
                        $email = $inv_arr[ count($inv_arr)-1 ];
                        $name = $inv_arr[0];
                    } else {
                        $email = $inv_arr[0];
                    }
                    $invitation = UserInvite::where([
                        ['user_id', $this->user->id],
                        ['invited_email', 'LIKE', $email],
                    ])->first();

                    if($invitation) {
                        if($invitation->created_at->timestamp > Carbon::now()->subMonths(1)->timestamp) {
                            return Response::json(['success' => false, 'message' => trans('trp.page.profile.invite.already-invited') ] );
                        }
                        $invitation->invited_name = $name;
                        $invitation->created_at = Carbon::now();
                        $invitation->save();
                    } else {
                        $invitation = new UserInvite;
                        $invitation->user_id = $this->user->id;
                        $invitation->invited_email = $email;
                        $invitation->invited_name = $name;
                        $invitation->save();
                    }
                    //Mega hack
                    $dentist_name = $this->user->name;
                    $dentist_email = $this->user->email;
                    $this->user->name = '';
                    $this->user->email = $email;
                    $this->user->save();

                    $this->user->sendTemplate( $this->user->is_dentist ? 7 : 17, [
                        'friend_name' => $dentist_name,
                        'invitation_id' => $invitation->id
                    ]);

                    //Back to original
                    $this->user->name = $dentist_name;
                    $this->user->email = $dentist_email;
                    $this->user->save();

                }

                return Response::json(['success' => true, 'message' => trans('trp.page.profile.invite.contacts-success') ] );
            } else {
                $validator = Validator::make(Request::all(), [
                    'email' => ['required', 'email'],
                    'name' => ['required', 'string'],
                ]);

                if ($validator->fails()) {
                    return Response::json(['success' => false, 'message' => trans('trp.page.profile.invite.failure') ] );
                } else {
                    $invitation = UserInvite::where([
                        ['user_id', $this->user->id],
                        ['invited_email', 'LIKE', Request::Input('email')],
                    ])->first();

                    $existing_patient = User::where('email', 'LIKE', Request::Input('email') )->where('is_dentist', 0)->first();

                    if($invitation) {

                        if(!empty($existing_patient)) {
                            $d_id = $this->user->id;

                            $patient_review = Review::where('user_id', $existing_patient->id )->where(function($query) use ($d_id) {
                                $query->where( 'dentist_id', $d_id)->orWhere('clinic_id', $d_id);
                            })->orderBy('id', 'desc')->first();
                            
                            if($invitation->created_at->timestamp > Carbon::now()->subMonths(1)->timestamp) {
                                return Response::json(['success' => false, 'message' => 'This patient already submitted a review for your dental practice recently. Try again next month.' ] );
                            }
                        }

                        if($invitation->created_at->timestamp > Carbon::now()->subMonths(1)->timestamp) {
                            return Response::json(['success' => false, 'message' => trans('trp.page.profile.invite.already-invited') ] );
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
                        $invitation->save();

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
                    } else {
                        $invitation = new UserInvite;
                        $invitation->user_id = $this->user->id;
                        $invitation->invited_email = Request::Input('email');
                        $invitation->invited_name = Request::Input('name');
                        $invitation->review = true;
                        $invitation->save();
                    }

                    if(!empty($existing_patient)) {

                        $substitutions = [
                            'type' => $this->user->is_clinic ? 'dental clinic' : ($this->user->is_dentist ? 'your dentist' : ''),
                            'inviting_user_name' => ($this->user->is_dentist && !$this->user->is_clinic && $this->user->title) ? config('titles')[$this->user->title].' '.$this->user->name : $this->user->name,
                            'invited_user_name' => Request::Input('name'),
                            "invitation_link" => getLangUrl('invite/'.$this->user->id.'/'.$this->user->get_invite_token().'/'.$invitation->id, null, 'https://reviews.dentacoin.com/'),
                        ];

                        $existing_patient->sendGridTemplate(68, $substitutions);

                    } else {

                        if(Request::Input('email') != $this->user->email) {

                            //Mega hack
                            $dentist_name = $this->user->name;
                            $dentist_email = $this->user->email;
                            $this->user->name = Request::Input('name');
                            $this->user->email = Request::Input('email');
                            $this->user->save();


                            if ( $this->user->is_dentist) {
                                $substitutions = [
                                    'type' => $this->user->is_clinic ? 'dental clinic' : ($this->user->is_dentist ? 'your dentist' : ''),
                                    'inviting_user_name' => ($this->user->is_dentist && !$this->user->is_clinic && $this->user->title) ? config('titles')[$this->user->title].' '.$dentist_name : $dentist_name,
                                    'invited_user_name' => $this->user->name,
                                    "invitation_link" => getLangUrl('invite/'.$this->user->id.'/'.$this->user->get_invite_token().'/'.$invitation->id, null, 'https://reviews.dentacoin.com/'),
                                ];


                                $this->user->sendGridTemplate(59, $substitutions);
                            } else {
                                $this->user->sendTemplate( 17 , [
                                    'friend_name' => $dentist_name,
                                    'invitation_id' => $invitation->id
                                ]);
                            }

                            // $this->user->sendTemplate( $this->user->is_dentist ? 7 : 17 , [
                            //     'friend_name' => $dentist_name,
                            //     'invitation_id' => $invitation->id
                            // ]);

                            //Back to original
                            $this->user->name = $dentist_name;
                            $this->user->email = $dentist_email;
                            $this->user->save();

                        } else {
                            return Response::json(['success' => false, 'message' => 'You can\'t invite yourself' ] );
                        }

                    }


                    return Response::json(['success' => true, 'message' => trans('trp.page.profile.invite.success') ] );
                }
            }

        }

        return $this->ShowView('profile-invite', [
            'menu' => $this->menu,
            'css' => [
                'common-profile.css',
            ],
            'js' => [
                'profile.js',
                'hello.all.js',
            ],
        ]);
    }


    public function invite_patient_again($locale=null) {
        $id = Request::input('id');

        if (!empty($id)) {
            if($this->user->is_dentist && $this->user->status!='approved' && $this->user->status!='added_approved' && $this->user->status!='test') {
                return redirect(getLangUrl('/'));
            }

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
                        "invitation_link" => getLangUrl('invite/'.$this->user->id.'/'.$this->user->get_invite_token().'/'.$last_invite->id, null, 'https://reviews.dentacoin.com/'),
                    ];

                    $existing_patient->sendGridTemplate(68, $substitutions);

                    return Response::json(['success' => true, 'url' => getLangUrl('/').'?tab=asks' ] );
                } else {
                    return Response::json(['success' => false ] );
                }
            }
        }

    }


    public function invite_new($locale=null) {

        if($this->user->is_dentist && $this->user->status!='approved' && $this->user->status!='added_approved' && $this->user->status!='test') {
            return Response::json(['success' => false, 'message' => trans('trp.page.profile.invite.failure') ] );
        }

        if( $this->user->canInvite('trp') ) {

            $validator = Validator::make(Request::all(), [
                'email' => ['required', 'email'],
                'name' => ['required', 'string'],
            ]);

            if ($validator->fails()) {
                return Response::json(['success' => false, 'message' => trans('trp.page.profile.invite.failure') ] );
            } else {
                $invitation = UserInvite::where([
                    ['user_id', $this->user->id],
                    ['invited_email', 'LIKE', Request::Input('email')],
                ])->first();

                if($invitation) {
                    if($invitation->created_at->timestamp > Carbon::now()->subMonths(1)->timestamp) {
                        return Response::json(['success' => false, 'message' => trans('trp.page.profile.invite.already-invited') ] );
                    }
                    $invitation->invited_name = Request::Input('name');
                    $invitation->created_at = Carbon::now();
                    $invitation->for_team = true;
                    $invitation->save();
                } else {

                    $invitation = new UserInvite;
                    $invitation->user_id = $this->user->id;
                    $invitation->invited_email = Request::Input('email');
                    $invitation->invited_name = Request::Input('name');
                    $invitation->join_clinic = true;
                    $invitation->for_team = true;
                    $invitation->save();
                }

                if( Request::file('image') && Request::file('image')->isValid() ) {
                    $img = Image::make( Input::file('image') )->orientate();
                    $invitation->addImage($img);
                }

                //Mega hack
                $dentist_name = $this->user->name;
                $dentist_email = $this->user->email;
                $this->user->name = Request::Input('name');
                $this->user->email = Request::Input('email');
                $this->user->save();

                $this->user->sendTemplate( 1 , [
                    'clinic_name' => $dentist_name,
                    'invitation_id' => $invitation->id
                ]);

                //Back to original
                $this->user->name = $dentist_name;
                $this->user->email = $dentist_email;
                $this->user->save();

                return Response::json(['success' => true, 'message' => trans('trp.page.profile.invite.success') ] );
            }
        }
        
        return Response::json(['success' => false, 'message' => trans('trp.page.profile.invite.failure') ] );
    }


    
    //
    //Info
    //

    public function upload($locale=null) {
        if($this->user->is_dentist && $this->user->status!='approved' && $this->user->status!='added_approved' && $this->user->status!='test') {
            return Response::json(['success' => false ]);
        }
        $this->handleMenu();

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
    

    public function info($locale=null) {
        if($this->user->is_dentist && $this->user->status!='approved' && $this->user->status!='added_approved' && $this->user->status!='test') {
            return redirect(getLangUrl('/'));
        }
        $this->handleMenu();

        if(Request::isMethod('post')) {

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

                return redirect( getLangUrl('profile/info') )
                ->withInput()
                ->withErrors($validator);
            } else {

                if(empty(Request::input('field')) && $this->user->is_dentist && !User::validateAddress( Country::find( request('country_id') )->name, request('address') ) ) {
                    if( Request::input('json') ) {
                        $ret = [
                            'success' => false,
                            'messages' => [
                                'address' => trans('trp.common.invalid-address')
                            ]
                        ];
                        return Response::json($ret);
                    }

                    return redirect( getLangUrl('profile/info') )
                    ->withInput()
                    ->withErrors([
                        'address' => trans('trp.common.invalid-address')
                    ]);
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

                    return redirect( getLangUrl('profile/info') )
                    ->withInput()
                    ->withErrors([
                        'name' => trans('trp.common.invalid-name')
                    ]);
                }

                if($this->user->validateMyEmail() == true) {
                    return redirect( getLangUrl('profile/info') )
                    ->withInput()
                    ->withErrors([
                        'email' => trans('trp.common.invalid-email')
                    ]);
                }

                foreach ($this->profile_fields as $key => $value) {
                    if( Request::exists($key) || (Request::input('field')=='specialization' && $key=='specialization') || $key=='email_public' || (Request::input('field')=='accepted_payment' && $key=='accepted_payment') ) {
                        if($key=='work_hours') {
                            $wh = Request::input('work_hours');
                            foreach ($wh as $k => $v) {
                                if( empty($wh[$k][0][0]) || empty($wh[$k][0][1]) || empty($wh[$k][1][0]) || empty($wh[$k][1][1]) ) { 
                                    unset($wh[$k]);
                                    continue;
                                }


                                if( !empty($wh[$k][0]) ) {
                                    $wh[$k][0] = implode(':', $wh[$k][0]);
                                }
                                if( !empty($wh[$k][1]) ) {
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
                        'href' => $this->user->getLink()
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
                return redirect( getLangUrl('profile/info') );

            }
        }

        return $this->ShowView('profile-info', [
            'menu' => $this->menu,
            'fields' => $this->profile_fields,
            'css' => [
                'common-profile.css',
            ],
            'js' => [
                'profile.js',
                'upload.js',
            ],

        ]);
    }


    public function change_password($locale=null) {
        if($this->user->is_dentist && $this->user->status!='approved' && $this->user->status!='added_approved' && $this->user->status!='test') {
            return redirect(getLangUrl('/'));
        }

        $validator = Validator::make(Request::all(), [
            'cur-password' => 'required',
            'new-password' => 'required|min:6',
            'new-password-repeat' => 'required|same:new-password',
        ]);

        if ($validator->fails()) {
            return redirect( getLangUrl('profile/info') )
            ->withInput()
            ->withErrors($validator);
        } else {
            if ( !Hash::check(Request::input('cur-password'), $this->user->password) ) {
                Request::session()->flash('error-message', trans('trp.page.profile.wrong-password'));
                return redirect( getLangUrl('profile/info') );
            }
            
            $this->user->password = bcrypt(Request::input('new-password'));
            $this->user->save();
            
            Request::session()->flash('success-message', trans('trp.page.profile.info.password-updated'));
            return redirect( getLangUrl('profile/info'));
        }
    }

    //
    //Patients ask dentist to confirm they are patients
    //

    public function asks($locale=null) {
        if($this->user->is_dentist && $this->user->status!='approved' && $this->user->status!='added_approved' && $this->user->status!='test') {
            return redirect(getLangUrl('/'));
        }
        if (!$this->user->is_dentist) {
            return redirect( getLangUrl('profile') );
        }
        $this->handleMenu();

        return $this->ShowView('profile-ask', [
            'menu' => $this->menu,
            'css' => [
                'common-profile.css',
            ],
            'js' => [
                'profile.js',
            ],
        ]);
    }
    public function asks_accept($locale=null, $ask_id) {
        if($this->user->is_dentist && $this->user->status!='approved' && $this->user->status!='added_approved' && $this->user->status!='test') {
            return redirect(getLangUrl('/'));
        }
        if (!$this->user->is_dentist) {
            return redirect( getLangUrl('profile') );
        }
        $this->handleMenu();

        $ask = UserAsk::find($ask_id);
        if(!empty($ask) && $ask->dentist_id==$this->user->id && $ask->status=='waiting') {
            $ask->status = 'yes';
            $ask->save();

            $last_invite = UserInvite::where('user_id', $this->user->id)->where('invited_id', $ask->user->id)->first();
            if (!empty($last_invite)) {
                $last_invite->created_at = Carbon::now();
                $last_invite->save();   
            } else {
                $inv = new UserInvite;
                $inv->user_id = $this->user->id;
                $inv->invited_email = $ask->user->email;
                $inv->invited_name = $ask->user->name;
                $inv->invited_id = $ask->user->id;
                $inv->save();                    
            }

            if ($ask->on_review) {
                $ask->user->sendTemplate( $ask->on_review ? 64 : 24 ,[
                    'dentist_name' => $this->user->getName(),
                    'dentist_link' => $this->user->getLink(),
                ]);


                $d_id = $this->user->id;
                $reviews = Review::where(function($query) use ($d_id) {
                    $query->where( 'dentist_id', $d_id)->orWhere('clinic_id', $d_id);
                })->where('user_id', $ask->user->id)
                ->get();

                if ($reviews->count()) {
                    
                    foreach ($reviews as $review) {
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
                            $reward->os = $dd->getOs()['name'];
                        }

                        $reward->save();
                    }
                }
            }
        }
        
        Request::session()->flash('success-message', trans('trp.page.profile.asks.accepted'));
        return redirect( getLangUrl('/').'?tab=asks');
    }
    public function asks_deny($locale=null, $ask_id) {
        if($this->user->is_dentist && $this->user->status!='approved' && $this->user->status!='added_approved' && $this->user->status!='test') {
            return redirect(getLangUrl('/'));
        }
        if (!$this->user->is_dentist) {
            return redirect( getLangUrl('profile') );
        }
        $this->handleMenu();

        $ask = UserAsk::find($ask_id);
        if(!empty($ask) && $ask->dentist_id==$this->user->id && $ask->status=='waiting') {
            $ask->status = 'no';
            $ask->save();
        }
        
        Request::session()->flash('success-message', trans('trp.page.profile.asks.denied'));
        return redirect( getLangUrl('/').'?tab=asks');
    }

    //
    //TRP Reviews
    //

     public function trp($locale=null) {
                
        $params = [
            'reviews' => $this->user->is_dentist ? $this->user->reviews_in() : $this->user->reviews_out,
            'menu' => $this->menu,
            'css' => [
                'common-profile.css',
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

        $this->handleMenu();

        return $this->ShowView('profile-trp', $params);
    }

    //
    //Gallery
    //


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
        $this->user->updateStrength();
        $ret = [
            'success' => true
        ];
        return Response::json( $ret );
    }    

    public function gallery_delete($locale=null, $id) {
        UserPhoto::destroy($id);

        return Response::json( [
            'success' => true,
        ] );
    }


    //
    //Other
    //


    public function jwt($locale=null) {
        $ret = [
            'success' => false
        ];
        if($this->user->isBanned('trp')) {
            $ret['message'] = 'banned';
            return Response::json( $ret );
        }
        
        if($this->user->is_dentist && $this->user->status!='approved' && $this->user->status!='added_approved' && $this->user->status!='test') {
            $ret['message'] = 'not-verified';
            return Response::json( $ret );
        }

        $jwt = Request::input('jwtToken');
        $civic = Civic::where('jwtToken', 'LIKE', $jwt)->first();
        if(!empty($civic)) {
            $ret = $this->user->validateCivicKyc($civic);
            if($ret['success']) {
                Request::session()->flash('success-message', trans('trp.page.profile.wallet.civic-validated'));        
            }
        }

        
        return Response::json( $ret );
    }

    public function balance($locale=null) {
        return Response::json( User::getBalance( Request::input('vox-address') ) );
    }

    public function gdpr($locale=null) {
        $this->user->gdpr_privacy = true;
        $this->user->save();
        Request::session()->flash('success-message', trans('trp.page.profile.gdpr-done'));
        return redirect( getLangUrl('profile'));
    }



    //
    //Dentist <-> Clinic relationship
    //

    public function dentists_delete( $locale=null, $id ) {
        $res = UserTeam::where('user_id', $this->user->id)->where('dentist_id', $id)->delete();

        if( $res ) {
            $dentist = User::find( $id );

            // $dentist->sendTemplate(37, [
            //     'clinic-name' => $this->user->getName()
            // ]);
        }
        return Response::json( [
            'success' => true,
        ] );
    }

    public function dentists_reject( $locale=null, $id ) {

        $res = UserTeam::where('user_id', $this->user->id)->where('dentist_id', $id)->delete();

        if( $res ) {
            $dentist = User::find( $id );

            $dentist->sendTemplate(36, [
                'clinic-name' => $this->user->getName()
            ]);
        }
        
        return Response::json( [
            'success' => true,
        ] );
    }

    public function dentists_accept( $locale=null, $id ) {

        $item = UserTeam::where('dentist_id', $id)->where('user_id', $this->user->id)->first();

        if ($item) {
            
            $item->approved = 1;
            $item->save();

            $dentist = User::find( $id );

            $dentist->sendTemplate(35, [
                'clinic-name' => $this->user->getName()
            ]);
        }

        return Response::json( [
            'success' => true,
        ] );
    }


    
    public function clinics_delete( $locale=null, $id ) {
        $res = UserTeam::where('dentist_id', $this->user->id)->where('user_id', $id)->delete();

        if( $res ) {
            $clinic = User::find( $id );

            $clinic->sendTemplate(38, [
                'dentist-name' => $this->user->getName()
            ]);
        }

        return Response::json( [
            'success' => true,
        ] );
    }

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
                    'dentist-name' =>$this->user->getName()
                ]);

                return Response::json( [
                    'success' => true,
                    'message' => trans('trp.page.user.clinic-invited', ['name' => $clinic->getName() ])
                ] );
            }
        } 
            
        return Response::json( [
            'success' => false,
            'message' => trans('trp.page.user.clinic-invited-error')
        ] );

    }

    public function inviteDentist() {

        if(!empty(Request::input('invitedentistid'))) {

            $dentist = User::find( Request::input('invitedentistid') );

            if(!empty($dentist)) {

                $newdentist = new UserTeam;
                $newdentist->dentist_id = Request::input('invitedentistid');
                $newdentist->user_id = $this->user->id;
                $newdentist->approved = 1;
                $newdentist->save();

                $dentist->sendTemplate(33, [
                    'clinic-name' => $this->user->getName(),
                    'clinic-link' => $this->user->getLink()
                ]);

                return Response::json( [
                    'success' => true,
                    'message' => trans('trp.page.user.dentist-invited', ['name' => $dentist->getName() ])
                ] );
            }
        }

        return Response::json( [
            'success' => false,
            'message' => trans('trp.page.user.dentist-invited-error')
        ] );

    }

    public function invites_delete( $locale=null, $id ) {

        UserInvite::destroy($id);

        return Response::json( [
            'success' => true,
        ] );
    }


}