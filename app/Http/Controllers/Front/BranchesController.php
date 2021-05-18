<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\FrontController;

use App\Models\UserCategory;
use App\Models\UserAction;
use App\Models\UserBranch;
use App\Models\PageSeo;
use App\Models\Country;
use App\Models\User;

use Carbon\Carbon;

use Validator;
use Response;
use Request;
use Image;
use Auth;

class BranchesController extends FrontController {

    public function branchesPage($locale=null, $slug) {

        $clinic = User::where('slug', 'LIKE', $slug)->whereNull('self_deleted')->first();

        if(!empty($clinic) && $clinic->branches->isNotEmpty()) {

            $seos = PageSeo::find(35);

            $seo_title = $seos->seo_title;
            $seo_description = $seos->seo_description;
            $social_title = $seos->social_title;
            $social_description = $seos->social_description;

            $items = [];

            foreach($clinic->branches as $branch) {
                $items[] = $branch->branchClinic;
            }

            return $this->ShowView('branches', [
                'noIndex' => true,
                'clinic' => $clinic,
                'items' => $items,
                'countries' => Country::with('translations')->get(),
                'seo_title' => $seo_title,
                'seo_description' => $seo_description,
                'social_title' => $social_title,
                'social_description' => $social_description,
                'css' => [
                    'trp-search.css',
                ],
                'js' => [
                    'search.js',
                    'branch.js',
                    'upload.js',
                    'address.js',
                ],
            ]);
        } else {
            return redirect( getLangUrl('page-not-found') );
        }
    }

    public function addNewBranch($locale=null, $step=null) {
        if(!empty($step)) {

            if($step == 1) {
                $validator = Validator::make(Request::all(), [
                    'clinic_name' => array('required', 'min:3'),
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

                    if(User::validateLatin(Request::input('clinic_name')) == false) {
                        return Response::json( [
                            'success' => false, 
                            'messages' => [
                                'clinic_name' => trans('trp.common.invalid-name')
                            ]
                        ] );
                    }

                    $ret = array(
                        'success' => true
                    );

                }

                return Response::json( $ret );

            } else if($step == 2) {
                if (request('clinic_website') && mb_strpos(mb_strtolower(request('clinic_website')), 'http') !== 0) {
                    request()->merge([
                        'clinic_website' => 'http://'.request('clinic_website')
                    ]);
                }

                $validator = Validator::make(Request::all(), [
                    'clinic_country_id' => array('required', 'exists:countries,id'),
                    'clinic_address' =>  array('required', 'string'),
                    'clinic_website' =>  array('required', 'regex:/^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/'),
                    'clinic_phone' =>  array('required', 'regex: /^[- +()]*[0-9][- +()0-9]*$/u'),
                ]);

                if ($validator->fails()) {

                    $msg = $validator->getMessageBag()->toArray();
                    $ret = array(
                        'success' => false,
                        'messages' => array()
                    );

                    foreach ($msg as $field => $errors) {
                        if($field=='clinic_website') {
                            $ret['messages'][$field] = trans('trp.common.invalid-website');
                        } else {
                            $ret['messages'][$field] = implode(', ', $errors);
                        }
                    }

                    return Response::json( $ret );
                } else {

	                if(Request::getHost() != 'urgent.reviews.dentacoin.com') {

		                $info = User::validateAddress( Country::find(request('clinic_country_id'))->name, request('clinic_address') );
		                if(empty($info)) {
		                    return Response::json( array(
		                        'success' => false,
		                        'messages' => array(
		                            'clinic_address' => trans('trp.common.invalid-address')
		                        )
		                    ));
		                }
	                }

                    $phone = null;
                    $c = Country::find( Request::Input('clinic_country_id') );
                    $phone = ltrim( str_replace(' ', '', Request::Input('clinic_phone')), '0');
                    $pn = $c->phone_code.' '.$phone;

                    $validator = Validator::make(['clinic_phone' => $pn], [
                        'clinic_phone' => ['required','phone:'.$c->code],
                    ]);

                    if ($validator->fails()) {
                        return Response::json( [
                            'success' => false, 
                            'messages' => [
                                'clinic_phone' => trans('trp.popup.registration.phone')
                            ]
                        ] );
                    }

                    return Response::json( ['success' => true] );
                }
            }
        } else {
            if (request('website') && mb_strpos(mb_strtolower(request('website')), 'http') !== 0) {
                request()->merge([
                    'website' => 'http://'.request('website')
                ]);
            }

            $validator = Validator::make(Request::all(), [
                'clinic_name' => array('required', 'min:3'),
                'clinic_address' =>  array('required', 'string'),
                'clinic_website' =>  array('required', 'regex:/^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/'),
                'clinic_phone' =>  array('required', 'regex: /^[- +()]*[0-9][- +()0-9]*$/u'),
                'clinic_country_id' => array('required', 'exists:countries,id'),
                'photo' =>  array('required'),
                'clinic_specialization' =>  array('required', 'array'),
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

                if(User::validateLatin(Request::input('clinic_name')) == false) {
                    return Response::json( [
                        'success' => false, 
                        'messages' => [
                            'name' => trans('trp.common.invalid-name')
                        ]
                    ] );
                }

                if(Request::getHost() != 'urgent.reviews.dentacoin.com') {

	                $info = User::validateAddress( Country::find(request('clinic_country_id'))->name, request('clinic_address') );
	                if(empty($info)) {
	                    return Response::json( array(
	                        'success' => false,
	                        'messages' => array(
	                            'clinic_address' => trans('trp.common.invalid-address')
	                        )
	                    ));
	                }
                }

                $phone = null;
                $c = Country::find( Request::Input('clinic_country_id') );
                $phone = ltrim( str_replace(' ', '', Request::Input('clinic_phone')), '0');
                $pn = $c->phone_code.' '.$phone;

                $validator = Validator::make(['clinic_phone' => $pn], [
                    'clinic_phone' => ['required','phone:'.$c->code],
                ]);

                if ($validator->fails()) {
                    return Response::json( [
                        'success' => false, 
                        'messages' => [
                            'clinic_phone' => trans('trp.popup.registration.phone')
                        ]
                    ] );
                }
                
                $newuser = new User;
                $newuser->name = Request::input('clinic_name');
                $newuser->name_alternative = Request::input('clinic_name_alternative');
                $newuser->main_branch_clinic_id = $this->user->status == 'clinic_branch' ? $this->user->main_branch_clinic_id : $this->user->id;
                $newuser->country_id = Request::input('clinic_country_id');
                $newuser->address = Request::input('clinic_address');
                $newuser->phone = $phone;
                $newuser->platform = 'trp';
                $newuser->website = Request::input('clinic_website');
                $newuser->status = 'clinic_branch';
                
                $newuser->gdpr_privacy = true;
                $newuser->is_dentist = 1;
                $newuser->is_clinic = 1;

                $newuser->save();

                $newuser->slug = $newuser->makeSlug();
                $newuser->save();

                if( Request::input('photo') ) {
                    $img = Image::make( User::getTempImagePath( Request::input('photo') ) )->orientate();
                    $newuser->addImage($img);
                }
                
                if(!empty(Request::input('clinic_specialization'))) {
                    foreach (Request::input('clinic_specialization') as $cat) {
                        $newc = new UserCategory;
                        $newc->user_id = $newuser->id;
                        $newc->category_id = $cat;
                        $newc->save();
                    }
                }

                $newuser->generateSocialCover();

                if($this->user->branches->isNotEmpty()) {
                    foreach($this->user->branches as $branch) {
                        $newbranch = new UserBranch;
                        $newbranch->clinic_id = $newuser->id;
                        $newbranch->branch_clinic_id = $branch->branch_clinic_id;
                        $newbranch->save();

                        $newbranch = new UserBranch;
                        $newbranch->clinic_id = $branch->branch_clinic_id;
                        $newbranch->branch_clinic_id = $newuser->id;
                        $newbranch->save();
                    }
                } else {
                    $this->user->main_branch_clinic_id = $this->user->id;
                    $this->user->save();
                }

                $newbranch = new UserBranch;
                $newbranch->clinic_id = $this->user->id;
                $newbranch->branch_clinic_id = $newuser->id;
                $newbranch->save();

                $newbranch = new UserBranch;
                $newbranch->clinic_id = $newuser->id;
                $newbranch->branch_clinic_id = $this->user->id;
                $newbranch->save();

                return Response::json( [
                    'success' => true,
                ] );

            }
        }
    }

    // public function logoutas($locale=null) {

    //     $encrypted_user_id = User::encrypt($this->user->id);

    //     // Auth::guard('web')->user()->logoutActions();
    //     // Auth::guard('web')->user()->removeTokens();
    //     // Auth::guard('web')->logout();

    //     $ret['success'] = true;

    //     $token = User::encrypt(session('login-logged-out'));
    //     $imgs_urls = [];
    //     foreach( config('platforms') as $k => $platform ) {
    //         if( !empty($platform['url']) && ( mb_strpos(request()->getHttpHost(), $platform['url'])===false || $platform['url']=='dentacoin.com' )  ) {
    //             if($k !== 'vox' && $k !== 'account') {
    //                 $imgs_urls[] = '//'.$platform['url'].'/custom-cookie?logout-token='.urlencode($token);
    //             }
    //         }
    //     }
    //     $imgs_urls[] = '//vox.dentacoin.com/custom-cookie?logout-token='.urlencode($token);

    //     $ret['imgs_urls'] = $imgs_urls;
    //     $ret['encrypted_user_id'] = $encrypted_user_id;

    //     return Response::json( $ret );
    // }

    public function loginas( $locale=null) {
        $ret['success'] = false;

        if(!empty($this->user) && !empty(request('branch_id')) && $this->user->branches->isNotEmpty() && in_array(request('branch_id'), $this->user->branches->pluck('branch_clinic_id')->toArray())) {

            $id = request('branch_id');
            $item = User::find($id);

            if(!empty($item)) {

                Auth::login($item, true);

                // $tokenobj = $item->createToken('LoginToken');
                // $tokenobj->token->platform = 'trp';
                // $tokenobj->token->save();

                // $token = User::encrypt($tokenobj->accessToken);
                // $imgs_urls = [];
                // foreach( config('platforms') as $k => $platform ) {
                //     if( !empty($platform['url']) && ( mb_strpos(request()->getHttpHost(), $platform['url'])===false || $platform['url']=='dentacoin.com' )  ) {
                //         if($k !== 'vox' && $k !== 'account') {
                //             $imgs_urls[] = '//'.$platform['url'].'/custom-cookie?slug='.urlencode(User::encrypt($item->id)).'&type='.urlencode(User::encrypt('dentist')).'&token='.urlencode($token);
                //         }
                //     }
                // }

                // $ret['imgs_urls'] = $imgs_urls;
                $ret['success'] = true;
            }
        }

        return Response::json( $ret );
    }

    public function deleteBranch($locale=null) {

        $ret['success'] = false;

        if(!empty($this->user) && !empty(request('branch_id')) && $this->user->branches->isNotEmpty() && in_array(request('branch_id'), $this->user->branches->pluck('branch_clinic_id')->toArray())) {

            $id = request('branch_id');
            $item = User::find($id);

            if(!empty($item)) {

            	UserBranch::where('clinic_id', $id)->orWhere('branch_clinic_id', $id)->delete();
            	
            	if(!$item->email) {

		            $action = new UserAction;
		            $action->user_id = $item->id;
		            $action->action = 'deleted';
		            $action->reason = 'Clinic '.$this->user->getNames().' remove from his branches this clinic';
		            $action->actioned_at = Carbon::now();
		            $action->save();

		            $item->deleteActions();
		            User::destroy( $item->id );
            	}

                $ret['success'] = true;
            }
        }

        return Response::json( $ret );
    }
} ?>