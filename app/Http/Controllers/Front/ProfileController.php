<?php

namespace App\Http\Controllers\Front;
use App\Http\Controllers\FrontController;

use Validator;
use Response;
use Request;
use Route;
use Hash;
use Image;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\File;
use App\Models\User;
use App\Models\UserCategory;
use App\Models\UserPhoto;


class ProfileController extends FrontController
{

	public function __construct(\Illuminate\Http\Request $request, Route $route, $locale=null) {
        parent::__construct($request, $route, $locale);

        $this->menu_dentist = [
        	'home' => trans('front.page.profile.dentist.home'),
			'info' => trans('front.page.profile.dentist.info'),
			'gallery' => trans('front.page.profile.dentist.gallery'),
			'password' => trans('front.page.profile.dentist.password'),
			//'reviews' => trans('front.page.profile.dentist.reviews'),
            'wallet' => trans('front.page.profile.dentist.wallet'),
			'invite' => trans('front.page.profile.dentist.invite'),
		];

		$this->menu_patient = [
        	'home' => trans('front.page.profile.patient.home'),
			'info' => trans('front.page.profile.patient.info'),
			'password' => trans('front.page.profile.patient.password'),
			'reviews' => trans('front.page.profile.patient.reviews'),
			'wallet' => trans('front.page.profile.patient.wallet'),
		];

		$this->dentist_fields = [
            'title' => [
                'type' => 'select',
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
    		'email' => [
    			'type' => 'text',
    			'required' => true,
    			'is_email' => true,
    		],
    		'country_id' => [
    			'type' => 'country',
    		],
    		'city_id' => [
    			'type' => 'city',
    		],
    		'zip' => [
    			'type' => 'text',
    		],
    		'address' => [
    			'type' => 'text',
    		],
            'phone' => [
                'type' => 'text',
                'subtype' => 'phone'
            ],
            'website' => [
                'type' => 'text',
            ],
            'categories' => [
                'type' => 'checkboxes',
                'values' => $this->categories
            ],
    		'work_hours' => [
    			'type' => 'work_hours'
    		],
    	];

    	$this->patient_fields = [
    		'name' => [
    			'type' => 'text',
    			'required' => true,
    			'min' => 3,
    		],
    		'email' => [
    			'type' => 'text',
    			'required' => true,
    			'is_email' => true,
    		],
    		'phone' => [
    			'type' => 'text',
    		],
    		'country_id' => [
    			'type' => 'country',
    		],
    		'city_id' => [
    			'type' => 'city',
    		],
    	];
    }


    public function home($locale=null) {

        if (empty($this->user->is_verified)) {
         	return $this->ShowView('profile-verified');
        }

		return $this->ShowView('profile', [
			'menu' => $this->user->is_dentist == 1 ? $this->menu_dentist : $this->menu_patient,
            'needs_avatar' => !$this->user->hasimage,
            'no_reviews' => !$this->user->is_dentist && $this->user->reviews_out->isEmpty(),
            'no_address' => $this->user->is_dentist && (!$this->user->city_id || !$this->user->address),
            'my_reviews' => null,
            'my_upvotes' => null,
            'js' => [
                'profile.js',
                'dApp.js'
            ]
		]);
    }

    public function info($locale=null) {

    	if (empty($this->user->is_verified)) {
         	return $this->ShowView('profile-verified');
        }

        $fields =  $this->user->is_dentist == 1 ? $this->dentist_fields : $this->patient_fields;

        if(Request::isMethod('post')) {

        	$validator_arr = [];
        	foreach ($fields as $key => $value) {
        		$arr = [];
        		if (!empty($value['required'])) {
        			$arr[] = 'required';
        		}
        		if (!empty($value['is_email'])) {
        			$arr[] = 'email';
        		}
        		if (!empty($value['min'])) {
        			$arr[] = 'min:'.$value['min'];
        		}

        		if (!empty($arr)) {
        			$validator_arr[$key] = $arr;
        		}
        	}

        	$validator = Validator::make(Request::all(), $validator_arr);

	        if ($validator->fails()) {
	            return redirect( getLangUrl('profile/info') )
	            ->withInput()
	            ->withErrors($validator);
	        } else {

                $phone = '';
                if(Request::Input('phone')) {
                    $phone = ltrim( str_replace(' ', '', Request::Input('phone')), '0');
                    $other = User::where([
                        ['id', '!=', $this->user->id],
                        ['country_id', Request::input('country_id')],
                        ['phone', $phone],
                    ])->first();
                    if(!empty($other)) {
                        return redirect( getLangUrl('profile/info') )
                        ->withInput()
                        ->withErrors(['phone' => trans('front.common.phone-already-used')]);
                    }
                }

                $send_validate_email = $this->user->email != Request::input('email');

                foreach ($fields as $key => $value) {
                    if($key=='categories') {
                        UserCategory::where('user_id', $this->user->id)->delete();
                        if(!empty(Request::input($key))) {
                            foreach (Request::input($key) as $cat) {
                                $newc = new UserCategory;
                                $newc->user_id = $this->user->id;
                                $newc->category_id = $cat;
                                $newc->save();
                            }
                        }
                    } else if($key=='work_hours') {
                        $wh = Request::input($key);
                        if(!empty($wh) && is_array($wh)) {
                            foreach ($wh as $k => $v) {
                                if(is_array($v) && ( empty($v[0]) || empty($v[1]) ) ) {
                                    unset($wh[$k]);
                                }
                            }
                        }
                        $this->user->work_hours = json_encode($wh);
                    } else if($key=='phone') {
                        $this->user->phone = $phone;
                    } else {
        			    $this->user->$key = Request::input($key);
                    }
        		}
        		$this->user->save();

        		// if ($send_validate_email) {
        		// 	$this->user->sendTemplate( $this->user->is_dentist ? 1 : 2 );
        		// 	$this->user->is_verified = null;
        		// 	$this->user->verified_on = null;
        		// 	$this->user->save();
        		// }
                Request::session()->flash('success-message', trans('front.page.profile.info-updated'));
                return redirect( getLangUrl('profile/info') );
	        }
        }

		return $this->ShowView('profile-info', [
			'menu' => $this->user->is_dentist == 1 ? $this->menu_dentist : $this->menu_patient,
			'fields' => $fields,
            'js' => [
                'profile.js'
            ],
		]);
    }

    public function gallery($locale=null, $position=null) {

        if (empty($this->user->is_verified)) {
            return $this->ShowView('profile-verified');
        }
        if (!$this->user->is_dentist) {
            return redirect( getLangUrl('profile') );
        }

        if( Request::file('image') && Request::file('image')->isValid() ) {
            if(!empty($this->user->photos[$position])) {
                $dapic = $this->user->photos[$position];
            } else {
                $dapic = new UserPhoto;
                $dapic->user_id = $this->user->id;
                $dapic->save();
            }

            $img = Image::make( Input::file('image') )->orientate();
            $dapic->addImage($img);
            return Response::json(['success' => true, 'url' => $dapic->getImageUrl(true), 'position' => $position]);
        }

        return $this->ShowView('profile-gallery', [
            'menu' => $this->menu_dentist,
            'js' => [
                'profile.js'
            ],
        ]);
    }

    public function gallery_delete($locale=null, $position=null) {

        if (empty($this->user->is_verified)) {
            return $this->ShowView('profile-verified');
        }
        if (!$this->user->is_dentist) {
            return redirect( getLangUrl('profile') );
        }

        if(!empty($this->user->photos[$position])) {
            $this->user->photos[$position]->delete();
        }

        return Response::json([ 'success' => true ] );
    }

    public function invite($locale=null) {

        if (empty($this->user->is_verified)) {
            return $this->ShowView('profile-verified');
        }
    	if (!$this->user->is_dentist) {
         	return redirect( getLangUrl('profile') );
        }

        if(Request::isMethod('post')) {
            $validator = Validator::make(Request::all(), [
                'email' => ['required', 'email'],
                'invite-secret' => ['required', 'string'],
                'name' => ['required', 'string'],
            ]);

            if ($validator->fails()) {
                return Response::json(['success' => false, 'message' => trans('front.page.profile.'.$this->current_subpage.'.failure') ] );
            } else {
                //Mega hack
                $dentist_name = $this->user->name;
                $dentist_email = $this->user->email;
                $this->user->name = Request::Input('name');
                $this->user->email = Request::Input('email');
                $this->user->save();

                $this->user->sendTemplate(7, [
                    'dentist_name' => $dentist_name,
                    'secret' => Request::Input('invite-secret')
                ]);

                //Back to original
                $this->user->name = $dentist_name;
                $this->user->email = $dentist_email;
                $this->user->save();
                return Response::json(['success' => true, 'message' => trans('front.page.profile.'.$this->current_subpage.'.success') ] );
            }
        }

		return $this->ShowView('profile-invite', [
			'menu' => $this->menu_dentist,
            'js' => [
                'profile.js',
                'dApp.js',
            ],
		]);
    }

    public function password($locale=null) {

    	if (empty($this->user->is_verified)) {
         	return $this->ShowView('profile-verified');
        }

		return $this->ShowView('profile-password', [
			'menu' => $this->user->is_dentist == 1 ? $this->menu_dentist : $this->menu_patient,
            'js' => [
                'profile.js'
            ],
		]);
    }

    public function change_password($locale=null) {

    	if (empty($this->user->is_verified)) {
         	return $this->ShowView('profile-verified');
        }

        $validator = Validator::make(Request::all(), [
			'cur-password' => 'required',
    		'new-password' => 'required|min:6',
            'new-password-repeat' => 'required|same:new-password',
        ]);

        if ($validator->fails()) {
            return redirect( getLangUrl('profile/password') )
            ->withInput()
            ->withErrors($validator);
        } else {
        	if ( !Hash::check(Request::input('cur-password'), $this->user->password) ) {
        		Request::session()->flash('error-message', trans('front.page.profile.wrong-password'));
	    		return redirect( getLangUrl('profile/password') );
        	}
            
            $this->user->password = bcrypt(Request::input('new-password'));
            $this->user->save();
			
			Request::session()->flash('success-message', trans('front.page.profile.password-updated'));
    		return redirect( getLangUrl('profile/password'));
	    }
	}

    public function reviews($locale=null) {

    	if (empty($this->user->is_verified)) {
         	return $this->ShowView('profile-verified');
        }

        return $this->ShowView('profile-reviews', [
			'menu' => $this->user->is_dentist == 1 ? $this->menu_dentist : $this->menu_patient,
            'my_reviews' => null,
            'my_upvotes' => null,
            'js' => [
                'profile.js',
                'dentist.js'
            ],
		]);
    }

    public function wallet($locale=null) {

    	if (empty($this->user->is_verified)) {
         	return $this->ShowView('profile-verified');
        }

		return $this->ShowView('profile-wallet', [
			'menu' => $this->user->is_dentist == 1 ? $this->menu_dentist : $this->menu_patient,
            'js' => [
                'profile.js',
                'dApp.js'
            ],
		]);
    }

    public function avatar($locale=null) {
        if( Request::file('image') && Request::file('image')->isValid() ) {
            $img = Image::make( Input::file('image') )->orientate();
            $this->user->addImage($img);
            return Response::json(['success' => true, 'url' => $this->user->getImageUrl(true)]);
        }

        return Response::json(['success' => false]); 
    }

    public function remove_avatar($locale=null) {
        $this->user->hasimage = false;
        $this->user->save();
        Request::session()->flash('success-message', trans('front.page.profile.avatar-removed'));
        return redirect( getLangUrl('profile'));
    }
}