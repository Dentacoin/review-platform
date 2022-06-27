<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\FrontController;

use Illuminate\Support\Facades\Input;

use App\Models\UserAnnouncement;
use App\Models\UserGuidedTour;
use App\Models\WalletAddress;
use App\Models\UserCategory;
use App\Models\UserPhoto;
use App\Models\User;

use App\Helpers\GeneralHelper;
use Carbon\Carbon;

use Validator;
use Response;
use Request;
use Image;
use Route;

class ProfileController extends FrontController {

    public function __construct(\Illuminate\Http\Request $request, Route $route, $locale=null) {

        parent::__construct($request, $route, $locale);

        $this->profile_fields = [
            'title' => [
                'type' => 'select',
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
            'phone' => [
                'type' => 'text',
                'required' => true,
                'regex' => 'regex: /^[- +()]*[0-9][- +()0-9]*$/u',
            ],
            'country_id' => [
                'type' => 'country',
                'required' => false,
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
            'email_public' => [
                'type' => 'text',
                'required' => false,
                'is_email' => true,
            ],
            'languages' => [
                'type' => 'text',
                'required' => false,
            ],
            'education_info' => [
                'type' => 'text',
                'required' => false,
            ],
            'experience' => [
                'type' => 'text',
                'required' => false,
            ],
            'founded_at' => [
                'type' => 'text',
                'required' => false,
            ],
        ];
    }
    
    /**
     * dentist profile edit
     */
    public function editUser($locale=null, $branch_id = null) {

        if(
            empty($this->user) 
            || !$this->user->is_dentist 
            || ($this->user->is_dentist && !in_array($this->user->status, config('dentist-statuses.approved_test')))) {

            return Response::json([
                'success' => false,
            ]);
        }

        if($branch_id) {
            $branchClinic = User::find($branch_id);
    
            if(
                !empty($branchClinic) 
                && $this->user->is_clinic 
                && $branchClinic->is_clinic 
                && $this->user->branches->isNotEmpty() 
                && in_array($branchClinic->id, $this->user->branches->pluck('branch_clinic_id')->toArray())
            ) {
                $this->user = $branchClinic;
            }
        }

        if( Request::input('avatar') ) {
            $img = Image::make( GeneralHelper::decode_base64_image(Request::input('avatar')) )->orientate();
            $this->user->addImage($img);
        }

        $validator_arr = [];
        foreach ($this->profile_fields as $key => $value) {
            if( Request::input('field') && $key==Request::input('field') ) {
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
        }

        if (request('website') && mb_strpos(mb_strtolower(request('website')), 'http') !== 0) {
            request()->merge([
                'website' => 'http://'.request('website')
            ]);
        }

        $validator = Validator::make(Request::all(), $validator_arr);
        if ($validator->fails()) {

            $ret = [
                'success' => false
            ];

            $msg = $validator->getMessageBag()->toArray();
            $ret['messages'] = [];

            foreach ($msg as $field => $errors) {
                $ret['messages'][$field] = implode(', ', $errors);
            }
            return Response::json($ret);
        } else {

            if(config('trp.using_google_maps')) {

                $checkAddress = GeneralHelper::validateAddress( $this->user->country_id, request('address') );

                if(
                    is_numeric(request('country_id')) 
                    && empty(Request::input('field')) 
                    && $this->user->is_dentist 
                    && !$checkAddress 
                ) {
                    return Response::json([
                        'success' => false,
                        'messages' => [
                            'address' => trans('trp.common.invalid-address')
                        ]
                    ]);
                }

                if(
                    !empty($checkAddress) 
                    && isset($checkAddress['country_name']) 
                    && $checkAddress['country_name'] != $this->user->country->name
                ) {
                        
                    return Response::json([
                        'success' => false,
                        'messages' => [
                            'address' => trans('trp.page.user.invalid-country')
                        ]
                    ]);
                }
            }

            if (!empty(Request::input('description')) && mb_strlen(Request::input('description')) > 512) {
                return Response::json([
                    'success' => false,
                    'messages' => [
                        'description' => trans('trp.common.invalid-description')
                    ]
                ]);
            }

            if(!empty(Request::input('name')) && (GeneralHelper::validateLatin(Request::input('name')) == false)) {
                return Response::json([
                    'success' => false,
                    'messages' => [
                        'name' => trans('trp.common.invalid-name')
                    ]
                ]);
            }

            foreach ($this->profile_fields as $key => $value) {
                if( 
                    Request::exists($key) 
                    || (Request::input('field')=='specialization' && $key=='specialization') 
                    || $key=='email_public' 
                    || (Request::input('field')=='accepted_payment' && $key=='accepted_payment') ) {

                    if($key=='work_hours') {
                        $wh = Request::input('work_hours');
                        
                        foreach ($wh as $k => $v) {
                            if( empty($wh[$k][0][0]) || empty($wh[$k][0][1]) || empty($wh[$k][1][0]) || empty($wh[$k][1][1]) || !empty(Request::input('day_'.$k))) { 
                                unset($wh[$k]);
                                continue;
                            }

                            if( !empty($wh[$k][0]) && empty(Request::input('day_'.$k))) {
                                $wh[$k][0] = implode(':', $wh[$k][0]);
                            }
                            if( !empty($wh[$k][1]) && empty(Request::input('day_'.$k)) ) {
                                $wh[$k][1] = implode(':', $wh[$k][1]);
                            }
                        }

                        // dd($wh);
                        $this->user->$key = $wh;
                    } else if($key=='languages') {
                        $langField = Request::input('languages');

                        if(in_array($langField, array_keys(config('trp.languages')))) {

                            $current_langs = $this->user->languages ?? [];
                            $current_langs[] = $langField;
                            $this->user->$key = $current_langs;
                        }
                        
                    } else if($key=='phone') {
                        if($this->user->country_id && Request::input('phone')) {
                            $phone = GeneralHelper::validatePhone($this->user->country->phone_code, Request::input('phone'));
                        } else {
                            $phone = Request::input('phone');
                        }
                        $this->user->$key = $phone;
                        
                    } else if($key=='education_info') {
                        $educationInfo = Request::input('education_info');

                        foreach($educationInfo as $k => $ei) {
                            if(!$ei) {
                                unset($educationInfo[$k]);
                            }
                        }

                        $this->user->$key = $educationInfo;
                        
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
                    } else if($key=='founded_at') {
                        $this->user->$key = Carbon::parse(Request::input($key));
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

            foreach ($this->user->reviews_in() as $review_in) {
                $review_in->hasimage_social = false;
                $review_in->save();
            }
            
            $inputs = Request::all();

            unset($inputs['_token']);
            unset($inputs['field']);
            unset($inputs['json']);

            if(isset($inputs['name'])) {
                $inputs['name'] = $this->user->getNames();
            }

            if(isset($inputs['phone'])) {
                $inputs['phone'] = $this->user->getFormattedPhone();
            }

            if(isset($inputs['avatar'])) {
                $inputs['avatar'] = $this->user->getImageUrl(true);
            }

            if(isset($inputs['current-email'])) {
                $inputs['current-email'] = $this->user->email;
            }

            if(isset($inputs['experience'])) {
                $inputs['experience'] = config('trp.experience')[$this->user->experience];
            }

            $ret = [
                'success' => true,
                'href' => getLangUrl('/'),
                'inputs' => $inputs,
            ];

            if( Request::input('field') ) {
                if( Request::input('field')=='specialization' ) {
                    $ret['value'] = implode(', ', $this->user->parseCategories( $this->categories ));
                } else if( Request::input('field')=='work_hours' ) {
                    $ret['value'] = strip_tags( $this->user->getWorkHoursText() );
                } else if( Request::input('field')=='accepted_payment' ) {
                    $ret['value'] = $this->user->parseAcceptedPayment( $this->user->accepted_payment );
                } else if( in_array(Request::input('field'), ['languages', 'education_info']) ) {
                    $ret['value'] = $this->user[Request::input('field')];
                } else {
                    $ret['value'] = nl2br($this->user[ Request::input('field') ]) ;                            
                }
            }
            return Response::json($ret);
        }
    }
    
    /**
     * section TRP in accont.dentacoin.com
     */
    public function trp($locale=null) {
        if(!empty($this->user)) {
            $params = [
                'is_dentist' => $this->user->is_dentist,
                'xframe' => true,
                'reviews' => $this->user->is_dentist ? $this->user->reviews_in() : $this->user->reviews_out,
                'css' => [
                    'trp-profile.css',
                    'trp-reviews.css',
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

            return $this->ShowView('profile-trp', $params);
        }
    }

    /**
     * dentist adds a gallery photos
     */
    public function gallery($locale=null, $branch_id = null) {

        if(Request::file('image') && Request::file('image')->isValid()) {

            $extensions = ['image/jpeg', 'image/png'];

            if (!in_array(Input::file('image')->getMimeType(), $extensions)) {
                return Response::json( [
                    'success' => false,
                ]);
            }

            if($branch_id) {
                $branchClinic = User::find($branch_id);
    
                if(
                    !empty($branchClinic) 
                    && $this->user->is_clinic 
                    && $branchClinic->is_clinic 
                    && $this->user->branches->isNotEmpty() 
                    && in_array($branchClinic->id, $this->user->branches->pluck('branch_clinic_id')->toArray())
                ) {
                    $this->user = $branchClinic;
                }
            }

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
        
        return Response::json( [
            'success' => true
        ]);
    }    

    /**
     * dentist deletes a gallery photo
     */
    public function gallery_delete($locale=null, $id) {
        UserPhoto::destroy($id);

        return Response::json( [
            'success' => true,
        ]);
    }

    /**
     * dentist's strength scale action - check assurance.dentacoin.com
     */
    public function checkAssurance( $locale=null ) {

        if(!empty($this->user) && $this->user->is_dentist) {
            $gt = UserGuidedTour::where('user_id', $this->user->id)->first();

            if(!empty($gt)) {
                $gt->dcn_assurance = true;
                $gt->save();

                return Response::json( [
                    'success' => true,
                ]);
            }
        }
        return redirect(getLangUrl('/'));
    }

    /**
     * dentist's strength scale action - check dentacare.dentacoin.com
     */
    public function checkDentacare( $locale=null ) {

        if(!empty($this->user) && $this->user->is_dentist) {
            $gt = UserGuidedTour::where('user_id', $this->user->id)->first();

            if(!empty($gt)) {
                $gt->dentacare_app = true;
                $gt->save();

                return Response::json( [
                    'success' => true,
                ]);
            }
        }
        return redirect(getLangUrl('/'));
    }

    /**
     * dentist's strength scale action - check reviews
     */
    public function checkReviews( $locale=null ) {

        if(!empty($this->user) && $this->user->is_dentist) {
            $gt = UserGuidedTour::where('user_id', $this->user->id)->first();

            if(!empty($gt)) {
                $gt->check_reviews_on = Carbon::now();
                $gt->save();

                return Response::json( [
                    'success' => true,
                ]);
            }
        }
        return redirect(getLangUrl('/'));
    }

    /**
     * starts guided tour after dentist's registration
     */
    public function firstGuidedTour($locale=null) {
        session()->pull('first_guided_tour');

        if(!empty($this->user) && $this->user->is_dentist) {

            if(empty(session('guided_tour'))) {

                $arr=[];

                if(empty($this->user->socials)) {
                    $arr[] = [
                        'action' => 'edit',
                        'title' => trans('trp.guided-tour.first.edit.title'),
                        'description' => trans('trp.guided-tour.first.edit.description'),
                        'skip' => false,
                    ];

                    if(empty($this->user->socials)) {
                        $arr[] = [
                            'action' => 'socials',
                            'title' => trans('trp.guided-tour.first.socials.title'),
                            'description' => trans('trp.guided-tour.first.socials.description'),
                            'skip' => true,
                            'skip_text' => strtoupper(trans('trp.guided-tour.ok')),
                            'is_button' => true
                        ];
                    }

                    $arr[] = [
                        'action' => 'save',
                        'title' => trans('trp.guided-tour.first.save.title'),
                        'description' => trans('trp.guided-tour.first.save.description'),
                        'skip' => false,
                    ];
                }

                if(!empty(Request::input('full'))) {
                    $arr[] = [
                        'action' => 'invite',
                        'title' => trans('trp.guided-tour.first.invite.title'),
                        'description' => trans('trp.guided-tour.first.invite.description'),
                        'skip' => true,
                        'skip_text' => trans('trp.guided-tour.skip-step'),
                    ];
                }

                if(empty($this->user->description)) {
                    $arr[] = [
                        'action' => 'description',
                        'title' => trans('trp.guided-tour.first.description.title'),
                        'description' => trans('trp.guided-tour.first.description.description'),
                        'skip' => true,
                        'skip_text' => trans('trp.guided-tour.skip-step'),
                    ];
                }

                if($this->user->photos->isEmpty()) {
                    $arr[] = [
                        'action' => 'photos',
                        'title' => trans('trp.guided-tour.first.photos.title'),
                        'description' => trans('trp.guided-tour.first.photos.description'),
                        'skip' => true,
                        'skip_text' => trans('trp.guided-tour.skip-step'),
                    ];
                }

                if(!empty($this->user->is_clinic) && ($this->user->team->isEmpty() || $this->user->notVerifiedTeamFromInvitation->isEmpty() )) {

                    $arr[] = [
                        'action' => 'team',
                        'title' => trans('trp.guided-tour.first.team.title'),
                        'description' => trans('trp.guided-tour.first.team.description'),
                        'skip' => true,
                        'skip_text' => trans('trp.guided-tour.skip-step'),
                    ];
                }

                session(['guided_tour_count' => count($arr)]);
                session(['guided_tour' => $arr]);
            }

            return Response::json([
                'success' => true,
                'steps' => session('guided_tour'),
                'count_all_steps' => session('guided_tour_count'),
            ]);
        }

        return Response::json([
            'success' => false,
        ]);
    }

    /**
     * remove first guided tour
     */
    public function removeFirstGuidedTour($locale=null) {

        session()->pull('guided_tour');
        session()->pull('guided_tour_count');

        return Response::json([
            'success' => false,
        ]);
    }

    /**
     * remove reviews guided tour
     */
    public function removeReviewsGuidedTour($locale=null) {

        session()->pull('reviews_guided_tour');

        return Response::json([
            'success' => false,
        ]);
    }

    /**
     * guided tour after first dentist's review
     */
    public function reviewsGuidedTour($locale=null, $layout=null) {
        session()->pull('reviews_guided_tour');
        
        if(!empty($this->user) && $this->user->is_dentist && $this->user->reviews_in_standard()->isNotEmpty()) {

            $arr = [
                [
                    'action' => 'add',
                    'title' => trans('trp.guided-tour.reviews.add.title'),
                    'description' => trans('trp.guided-tour.reviews.add.description'),
                ],
                [
                    'action' => 'layout',
                    'title' => trans('trp.guided-tour.reviews.layout.title'),
                    'description' => trans('trp.guided-tour.reviews.layout.description'),
                ],
            ];

            if($layout && ($layout == 'list' || $layout == 'carousel')) {

                $arr[] = [
                    'action' => 'reviews_type',
                    'title' => trans('trp.guided-tour.reviews.reviews-type.title'),
                    'description' => trans('trp.guided-tour.reviews.reviews-type.description'),
                    'skip' => true,
                    'skip_text' => trans('trp.guided-tour.ok'),
                ];

                $arr[] = [
                    'action' => 'copy',
                    'title' => trans('trp.guided-tour.reviews.copy.title'),
                    'description' => trans('trp.guided-tour.reviews.copy.description'),
                ];
            }

            if($layout && $layout == 'badge') {

                $arr[] = [
                    'action' => 'copy',
                    'title' => trans('trp.guided-tour.reviews.copy.title'),
                    'description' => trans('trp.guided-tour.reviews.copy.description'),
                ];
            }

            if($layout && $layout == 'fb') {

                $arr[] = [
                    'action' => 'fb_id',
                    'title' => trans('trp.guided-tour.reviews.fb.title'),
                    'description' => trans('trp.guided-tour.reviews.fb.description'),
                    'skip' => true,
                    'skip_text' => trans('trp.guided-tour.ok'),
                ];

                $arr[] = [
                    'action' => 'reviews_type',
                    'title' => trans('trp.guided-tour.reviews.reviews-type.title'),
                    'description' => trans('trp.guided-tour.reviews.reviews-type.description'),
                    'skip' => true,
                    'skip_text' => trans('trp.guided-tour.ok'),
                ];
            }

            return Response::json([
                'success' => true,
                'steps' => $arr,
                'count_all_steps' => $layout == 'badge' ? 3 : 4,
                'image' => url('img-trp/reviews-step-icon.svg'),
            ] );
        }

        return Response::json([
            'success' => false,
        ]);
    }

    /**
     * add dentist wallet address
     */
    public function addWalletAddress() {

        if(!empty($this->user) && $this->user->is_partner && $this->user->wallet_addresses->isEmpty()) {

            $validator_fields = [
                'wallet-address' => ['required', 'max:42', 'min:42']
            ];

            if(Request::input('recieve-address')) {
                $validator_fields['receive-wallet-address'] = ['required', 'max:42', 'min:42'];
            }

            $validator = Validator::make(Request::all(), $validator_fields, [], [
                'wallet-address' => 'wallet address',
                'receive-wallet-address' => 'rewards wallet address',
            ]);

            if ($validator->fails()) {

                $msg = $validator->getMessageBag()->toArray();
                $ret['messages'] = [];

                foreach ($msg as $field => $errors) {
                    $ret['messages'][$field] = implode(', ', $errors);
                }

                return Response::json($ret);

            } else {
                //just for empty the field
                $this->user->partner_wallet_popup = null;
                $this->user->save();

                $new_address = new WalletAddress;
                $new_address->user_id = $this->user->id;
                $new_address->dcn_address = Request::input('wallet-address');
                $new_address->selected_wallet_address = 1;
                $new_address->is_deprecated = 0;
                $new_address->save();

                if(Request::input('recieve-address')) {
                    $new_address = new WalletAddress;
                    $new_address->user_id = $this->user->id;
                    $new_address->dcn_address = Request::input('receive-wallet-address');
                    $new_address->selected_wallet_address = 0;
                    $new_address->is_deprecated = 0;
                    $new_address->save();
                }

                return Response::json( [
                    'success' => true,
                ]);
            }
        }

        return Response::json( [
            'success' => false,
        ]);
    }

    /**
     * close dentist wallet address popup
     */
    public function closePartnerWalletPopup() {

        if(!empty($this->user) && $this->user->is_partner && $this->user->wallet_addresses->isEmpty()) {

            $this->user->partner_wallet_popup = Carbon::now()->addDays(14);
            $this->user->save();

            return Response::json( [
                'success' => true,
            ]);
        }

        return Response::json( [
            'success' => false,
        ]);
    }

    /**
     * delete dentist languages
     */
    public function deleteLanguage($locale=null, $branch_id = null) {

        if($branch_id) {
            $branchClinic = User::find($branch_id);
    
            if(!empty($branchClinic) && $this->user->is_clinic && $branchClinic->is_clinic && $this->user->branches->isNotEmpty() && in_array($branchClinic->id, $this->user->branches->pluck('branch_clinic_id')->toArray())) {
                $this->user = $branchClinic;
            }
        }

        if(!empty($this->user) && $this->user->is_dentist) {

            $current_langs =  $this->user->languages ?? [];
            
            if(in_array(Request::input('language'), array_keys(config('trp.languages'))) && in_array(Request::input('language'), $current_langs)) {
                unset($current_langs[array_search(Request::input('language'), $current_langs)]);
                $this->user->languages = $current_langs;
                $this->user->save();

                return Response::json( [
                    'success' => true,
                ]);
            }
        }

        return Response::json( [
            'success' => false,
        ]);
    }

    /**
     * add dentist announcement
     */
    public function addAnnouncement($locale=null, $branch_id = null) {

        if($branch_id) {
            $branchClinic = User::find($branch_id);
    
            if(!empty($branchClinic) && $this->user->is_clinic && $branchClinic->is_clinic && $this->user->branches->isNotEmpty() && in_array($branchClinic->id, $this->user->branches->pluck('branch_clinic_id')->toArray())) {
                $this->user = $branchClinic;
            }
        }

        if(!empty($this->user) && $this->user->is_dentist) {

            if(empty(Request::input('announcement_title')) && empty(Request::input('announcement_description'))) {
                //remove announcement
                if( $this->user->announcement) {
                    UserAnnouncement::destroy($this->user->announcement->id);
                }

                return Response::json([
                    'success' => true,
                    'inputs' => Request::all()
                ]);
            } else {

                $validator = Validator::make(Request::all(), [
                    'announcement_title' => array('required'),
                    'announcement_description' => array('required'),
                ]);
    
                if ($validator->fails()) {
                    $msg = $validator->getMessageBag()->toArray();
                    $ret['messages'] = [];
                    foreach ($msg as $field => $errors) {
                        $ret['messages'][$field] = implode(', ', $errors);
                    }
                    return Response::json($ret);
                } else {
    
                    $announcement = $this->user->announcement ?? new UserAnnouncement;
                    $announcement->user_id = $this->user->id;
                    $announcement->title = Request::input('announcement_title');
                    $announcement->description = Request::input('announcement_description');
                    $announcement->save();
                    
                    return Response::json([
                        'success' => true,
                        'inputs' => Request::all()
                    ]);
                }
            }
        }

        return Response::json( [
            'success' => false,
        ]);
    }
}