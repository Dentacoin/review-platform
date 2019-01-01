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
use App\Models\VoxCashout;
use App\Models\Dcn;
use App\Models\Civic;
use App\Models\UserAsk;
use App\Models\UserPhoto;
use App\Models\UserCategory;
use App\Models\UserTeam;
use Carbon\Carbon;


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
                'values' => [
                    '' => '-',
                    'dr' => 'Dr.',
                    'prof' => 'Prof. Dr.'
                ]
            ],
            'name' => [
                'type' => 'text',
                'required' => true,
                'min' => 3,
            ],
            'description' => [
                'type' => 'text',
                'required' => false,
            ],
            'specialization' => [
                'type' => 'specialization',
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
            ],
            'address' => [
                'type' => 'text',
                'required' => true,
            ],
            'country_id' => [
                'type' => 'country',
                'required' => true,
            ],
            'city_id' => [
                'type' => 'city',
                'required' => true,
            ],
            'work_hours' => [
                'required' => false,
                'hide' => true
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
        if($this->user->is_dentist && $this->user->status!='approved') {
            return redirect(getLangUrl('pending-dentist'));
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
            'menu' => $this->menu,
            'currencies' => file_get_contents('/tmp/dcn_currncies'),
            'history' => $this->user->history->where('type', '=', 'vox-cashout'),
            'js' => [
                'profile.js',
            ],
            'css' => [
                'common-profile.css',
            ],
        ];

        if(!$this->user->civic_kyc) {
            $params['js'][] = 'civic.js';
            $params['jscdn'] = [
                'https://hosted-sip.civic.com/js/civic.sip.min.js',
            ];
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
        if($amount > $this->user->getVoxBalance()) {
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
            $cashout = new VoxCashout;
            $cashout->user_id = $this->user->id;
            $cashout->reward = $amount;
            $cashout->address = $this->user->dcn_address;
            $cashout->save();

            $ret = Dcn::send($this->user, $this->user->dcn_address, $amount, 'vox-cashout', $cashout->id);
            $ret['balance'] = $this->user->getVoxBalance();
            
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
        if($this->user->is_dentist && $this->user->status!='approved') {
            return redirect(getLangUrl('pending-dentist'));
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
        if($this->user->is_dentist && $this->user->status!='approved') {
            return redirect(getLangUrl('pending-dentist'));
        }
        $this->handleMenu();

        if(Request::isMethod('post') && $this->user->canInvite('vox') ) {

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
                    $already = UserInvite::where([
                        ['user_id', $this->user->id],
                        ['invited_email', 'LIKE', $email],
                    ])->first();

                    if(!$already) {
                        $invitation = new UserInvite;
                        $invitation->user_id = $this->user->id;
                        $invitation->invited_email = $email;
                        $invitation->invited_name = $name;
                        $invitation->save();

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
                    $already = UserInvite::where([
                        ['user_id', $this->user->id],
                        ['invited_email', 'LIKE', Request::Input('email')],
                    ])->first();

                    if($already) {
                        return Response::json(['success' => false, 'message' => trans('trp.page.profile.invite.already-invited') ] );                    
                    }

                    $invitation = new UserInvite;
                    $invitation->user_id = $this->user->id;
                    $invitation->invited_email = Request::Input('email');
                    $invitation->invited_name = Request::Input('name');
                    $invitation->save();

                    //Mega hack
                    $dentist_name = $this->user->name;
                    $dentist_email = $this->user->email;
                    $this->user->name = Request::Input('name');
                    $this->user->email = Request::Input('email');
                    $this->user->save();

                    $this->user->sendTemplate( $this->user->is_dentist ? 7 : 17 , [
                        'friend_name' => $dentist_name,
                        'invitation_id' => $invitation->id
                    ]);

                    //Back to original
                    $this->user->name = $dentist_name;
                    $this->user->email = $dentist_email;
                    $this->user->save();

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


    
    //
    //Info
    //

    public function upload($locale=null) {
        if($this->user->is_dentist && $this->user->status!='approved') {
            return Response::json(['success' => false ]);
        }
        $this->handleMenu();

        if( Request::file('image') && Request::file('image')->isValid() ) {
            $img = Image::make( Input::file('image') )->orientate();
            $this->user->addImage($img);
            return Response::json(['success' => true, 'thumb' => $this->user->getImageUrl(true), 'name' => '' ]);
        }
    
        
    }
    

    public function info($locale=null) {
        if($this->user->is_dentist && $this->user->status!='approved') {
            return redirect(getLangUrl('pending-dentist'));
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
                if (!empty($value['values'])) {
                    $arr[] = 'in:'.implode(',', array_keys($value['values']) );
                }

                if (!empty($arr)) {
                    $validator_arr[$key] = $arr;
                }
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

                foreach ($this->profile_fields as $key => $value) {
                    if( Request::exists($key) ) {
                        if($key=='work_hours') {
                            $wh = Request::input('work_hours');
                            foreach ($wh as $k => $v) {
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
                $this->user->save();

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
        if($this->user->is_dentist && $this->user->status!='approved') {
            return redirect(getLangUrl('pending-dentist'));
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
        if($this->user->is_dentist && $this->user->status!='approved') {
            return redirect(getLangUrl('pending-dentist'));
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
        if($this->user->is_dentist && $this->user->status!='approved') {
            return redirect(getLangUrl('pending-dentist'));
        }
        if (!$this->user->is_dentist) {
            return redirect( getLangUrl('profile') );
        }
        $this->handleMenu();

        if (!$this->user->is_dentist) {
            return redirect( getLangUrl('profile') );
        }
        $ask = UserAsk::find($ask_id);
        if(!empty($ask) && $ask->dentist_id==$this->user->id && $ask->status=='waiting') {
            $ask->status = 'yes';
            $ask->save();
            $inv = new UserInvite;
            $inv->user_id = $this->user->id;
            $inv->invited_email = $ask->user->email;
            $inv->invited_name = $ask->user->name;
            $inv->invited_id = $ask->user->id;
            $inv->save();
            $ask->user->sendTemplate( 24 ,[
                'dentist_name' => $this->user->getName(),
                'dentist_link' => $this->user->getLink(),
            ]);
        }
        
        Request::session()->flash('success-message', trans('trp.page.profile.asks.accepted'));
        return redirect( getLangUrl('profile/asks'));
    }
    public function asks_deny($locale=null, $ask_id) {
        if($this->user->is_dentist && $this->user->status!='approved') {
            return redirect(getLangUrl('pending-dentist'));
        }
        if (!$this->user->is_dentist) {
            return redirect( getLangUrl('profile') );
        }
        $this->handleMenu();

        if (!$this->user->is_dentist) {
            return redirect( getLangUrl('profile') );
        }
        $ask = UserAsk::find($ask_id);
        if(!empty($ask) && $ask->dentist_id==$this->user->id && $ask->status=='waiting') {
            $ask->status = 'no';
            $ask->save();
        }
        
        Request::session()->flash('success-message', trans('trp.page.profile.asks.denied'));
        return redirect( getLangUrl('profile/asks'));
    }

    //
    //TRP Reviews
    //

     public function trp($locale=null) {
        if ($this->user->is_dentist) {
            return redirect( getLangUrl('profile') );
        }
        $this->handleMenu();

        return $this->ShowView('profile-trp', [
            'menu' => $this->menu,
            'css' => [
                'common-profile.css',
            ],
            'js' => [
                'profile.js',
            ],
        ]);
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
            return Response::json(['success' => true, 'url' => $dapic->getImageUrl(true)]);
        }
        $this->user->updateStrength();
        $ret = [
            'success' => true
        ];
        return Response::json( $ret );
    }


    //
    //Other
    //


    public function jwt($locale=null) {
        $ret = [
            'success' => false
        ];
        if($this->user->isBanned('vox')) {
            $ret['message'] = 'banned';
            return Response::json( $ret );
        }
        
        if($this->user->is_dentist && $this->user->status!='approved') {
            $ret['message'] = 'not-verified';
            return Response::json( $ret );
        }

        $jwt = Request::input('jwtToken');
        $civic = Civic::where('jwtToken', 'LIKE', $jwt)->first();
        if(!empty($civic)) {
            $data = json_decode($civic->response, true);
            $ret['weak'] = true;

            if(!empty($data['data'])) {
                foreach ($data['data'] as $key => $value) {
                    if( mb_strpos( $value['label'], 'documents.' ) !==false ) {
                        unset($ret['weak']);
                        break;
                    }
                }
            } 


            if(empty($ret['weak']) && !empty($data['userId'])) {
                $u = User::where('civic_id', 'LIKE', $data['userId'])->first();
                if(!empty($u) && $u->id != $this->user->id) {
                    $ret['duplicate'] = true;
                } else {
                    $this->user->civic_kyc = 1;
                    $this->user->civic_id = $data['userId'];
                    $this->user->save();
                    $ret['success'] = true;
                    Request::session()->flash('success-message', trans('trp.page.profile.wallet.civic-validated'));                    
                }
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
                    'clinic-name' => $this->user->getName()
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


}