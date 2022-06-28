<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\FrontController;

use App\Models\UserCategory;
use App\Models\UserAction;
use App\Models\UserBranch;
use App\Models\PageSeo;
use App\Models\Country;
use App\Models\User;

use App\Helpers\GeneralHelper;
use App\Helpers\TrpHelper;
use Carbon\Carbon;

use Validator;
use Response;
use Request;
use Image;
use Auth;

class BranchesController extends FrontController {

    public function branchesPage($locale=null, $slug) {

        $clinic = User::where('slug', 'LIKE', $slug)
        ->whereNull('self_deleted')
        ->first();

        if(!empty($clinic) && $clinic->branches->isNotEmpty()) {

            $branches = [$clinic->id];

            foreach($clinic->branches as $branch) {
                $branches[] = $branch->branchClinic->id;
            }

            $items = User::whereIn('id', $branches);

            $requestTypes = Request::input('types');

            if(empty($requestTypes)) {
                $requestTypes = [
                    'all',
                ];
            }

            $filters = TrpHelper::filterDentists($items, $requestTypes, $filter=false, $forBranch=true);

            $dentistSpecialications = $filters['dentistSpecialications'];
            $dentistTypes = $filters['dentistTypes'];
            $dentistRatings = $filters['dentistRatings'];
            $requestRatings = $filters['requestRatings'];
            $dentistAvailability = $filters['dentistAvailability'];
            $requestAvailability = $filters['requestAvailability'];
            $requestOrder = $filters['requestOrder'];
            $orders = $filters['orders'];
            $types = $filters['types'];
            $searchCategories = $filters['searchCategories'];

            $seos = PageSeo::find(35);
            $seo_title = $seos->seo_title;
            $seo_description = $seos->seo_description;
            $social_title = $seos->social_title;
            $social_description = $seos->social_description;

            return $this->ShowView('branches', [
                'noIndex' => true,
                'clinic' => $clinic,
                'items' => $items,
                'countries' => Country::with('translations')->get(),
                'seo_title' => $seo_title,
                'seo_description' => $seo_description,
                'social_title' => $social_title,
                'social_description' => $social_description,

                'searchCategories' => $searchCategories,
                'dentistSpecialications' => $dentistSpecialications,
                'dentistTypes' => $dentistTypes,
                'requestTypes' => $requestTypes,
                'types' => $types,
                'dentistRatings' => $dentistRatings,
                'requestRatings' => $requestRatings,
                'ratings' => [
                    5 => 'Above 4 stars',
                    4 => 'Above 3 stars',
                    3 => 'Above 2 stars',
                    2 => 'Above 1 stars',
                ],
    
                'languages' => [
                    'en' => 'English',
                    'gr' => 'German',
                    'it' => 'Italian',
                    'es' => 'Spanish',
                    'fr' => 'French',
                ],
    
                'experiences' => [
                    'under_five' => 'Less than 5 years',
                    'under_ten' => '5-10 years',
                    'over_ten' => '10+ years',
                ],
                
                'dentistAvailability' => $dentistAvailability,
                'requestAvailability' => $requestAvailability,
                'availabilities' => [
                    'early_morning' => 'Early morning • Starts before 10 am',
                    'morning' => 'Morning • Starts before 12 pm',
                    'afternoon' => 'Afternoon • Starts after 12 pm',
                    'evening' => 'Evening • Starts after 5 pm',
                ],
    
                'requestOrder' => $requestOrder,
                'orders' => $orders,


                'css' => [
                    'trp-search-results.css',
                ],
                'js' => [
                    'search-results.js',
                    'address.js',
                ],
                'jscdn' => [
                    'https://maps.googleapis.com/maps/api/js?key=AIzaSyCaVeHq_LOhQndssbmw-aDnlMwUG73yCdk&libraries=places&callback=initMap&language=en'
                ]
            ]);
        } else {
            return redirect( getLangUrl('page-not-found') );
        }
    }

    public function addNewBranch($locale=null, $step=null) {

        if(!empty($step)) {

            $validator = Validator::make(Request::all(), [
                'clinic_name' => array('required', 'min:3'),
                'clinic_country_id' => array('required', 'exists:countries,id'),
                'clinic_address' =>  array('required', 'string'),
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
                if(GeneralHelper::validateLatin(Request::input('clinic_name')) == false) {
                    return Response::json( [
                        'success' => false, 
                        'messages' => [
                            'clinic_name' => trans('trp.common.invalid-name')
                        ]
                    ]);
                }

                if(Request::getHost() != 'urgent.reviews.dentacoin.com') {

                    $info = GeneralHelper::validateAddress( Country::find(request('clinic_country_id'))->name, request('clinic_address') );
                    if(empty($info)) {
                        return Response::json( array(
                            'success' => false,
                            'messages' => array(
                                'clinic_address' => trans('trp.common.invalid-address')
                            )
                        ));
                    }
                }

                $ret = array(
                    'success' => true
                );
            }

            return Response::json( $ret );

        } else {

            //add http to website if missing
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
                'avatar' =>  array('required'),
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

                if(GeneralHelper::validateLatin(Request::input('clinic_name')) == false) {
                    return Response::json([
                        'success' => false, 
                        'messages' => [
                            'name' => trans('trp.common.invalid-name')
                        ]
                    ]);
                }

                if(Request::getHost() != 'urgent.reviews.dentacoin.com') {

	                $info = GeneralHelper::validateAddress( Country::find(request('clinic_country_id'))->name, request('clinic_address') );
	                if(empty($info)) {
	                    return Response::json( array(
	                        'success' => false,
	                        'messages' => array(
	                            'clinic_address' => trans('trp.common.invalid-address')
	                        )
	                    ));
	                }
                }
                
                $country = Country::find( Request::Input('clinic_country_id') );
                $phone = GeneralHelper::validatePhone($country->phone_code, Request::input('phone'));
                
                $newuser = new User;
                $newuser->name = Request::input('clinic_name');
                $newuser->name_alternative = Request::input('clinic_name_alternative');
                $newuser->main_branch_clinic_id = $this->user->status == 'clinic_branch' ? $this->user->main_branch_clinic_id : $this->user->id;
                $newuser->country_id = Request::input('clinic_country_id');
                $newuser->address = Request::input('clinic_address');
                $newuser->phone = $phone;
                $newuser->platform = 'trp';

                $social_network = TrpHelper::detectWebsitePlatform(Request::input('clinic_website'));
                if($social_network) {
                    $newuser->socials = [$social_network => Request::input('clinic_website')];
                } else {
                    $newuser->website = Request::input('clinic_website');
                }
                
                $newuser->status = 'clinic_branch';
                $newuser->gdpr_privacy = true;
                $newuser->is_dentist = 1;
                $newuser->is_clinic = 1;
                $newuser->save();

                $newuser->slug = $newuser->makeSlug();
                $newuser->save();

                if( Request::input('avatar') ) {        
                    
                    $allowedExtensions = array('jpg', 'jpeg', 'png');
                    $allowedMimetypes = ['image/jpeg', 'image/png'];

                    $image = GeneralHelper::decode_base64_image(Request::input('avatar'));
                    $checkFile = GeneralHelper::checkFile($image, $allowedExtensions, $allowedMimetypes);

                    if(isset($checkFile['success'])) {
                        $img = Image::make( $image )->orientate();
                        $newuser->addImage($img);
                    } else {
                        return Response::json( [
                            'success' => false,
                        ]);
                    }
                }
                
                foreach (Request::input('clinic_specialization') as $cat) {
                    $newc = new UserCategory;
                    $newc->user_id = $newuser->id;
                    $newc->category_id = $cat;
                    $newc->save();
                }

                $newuser->generateSocialCover();

                //make branches connection
                
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

                return Response::json([
                    'success' => true,
                    'url' => $newuser->getLink(),
                ]);
            }
        }
    }

    public function deleteBranch($locale=null) {

        $ret['success'] = false;

        //if can delete branch
        if(
            !empty($this->user) 
            && !empty(request('branch_id')) 
            && $this->user->branches->isNotEmpty() 
            && in_array(request('branch_id'), $this->user->branches->pluck('branch_clinic_id')->toArray())
        ) {

            $id = request('branch_id');
            $item = User::find($id);

            if(!empty($item)) {

            	UserBranch::where('clinic_id', $id)
                ->orWhere('branch_clinic_id', $id)
                ->delete();
            	
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
                $ret['url'] = $this->user->getLink();
            }
        }

        return Response::json( $ret );
    }
} ?>