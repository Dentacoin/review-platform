<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;

use App\Models\Email;
use App\Models\User;
use App\Models\UserLogin;
use App\Models\Vox;
use App\Models\UserBan;
use App\Models\VoxQuestion;
use App\Models\VoxAnswer;
use App\Models\VoxReward;
use App\Models\VoxCrossCheck;
use App\Models\City;
use App\Models\Country;
use App\Models\UserCategory;
use App\Models\Review;
use App\Models\ReviewAnswer;
use App\Models\IncompleteRegistration;

use Carbon\Carbon;

use Request;
use Route;
use Auth;
use DB;
use Excel;

class UsersController extends AdminController
{
    private $fields;
    public function __construct(\Illuminate\Http\Request $request, Route $route, $locale=null) {
        parent::__construct($request, $route, $locale);

        $this->genders = [
            '' => null,
            'm' => trans('admin.common.gender.m'),
            'f' => trans('admin.common.gender.f'),
        ];

        $this->statuses = [
            'new' => 'New',
            'approved' => 'Approved', 
            'pending' => 'Suspicious', 
            'rejected' => 'Rejected',
            'test' => 'Test'
        ];

    	$this->fields = [
            'title' => [
                'type' => 'select',
                'values' => config('titles')
            ],
    		'name' => [
    			'type' => 'text',
    		],
    		'email' => [
    			'type' => 'text',
    		],
    		'phone' => [
    			'type' => 'text',
    		],
    		'type' => [
                'type' => 'select',
                'values' => [
                    'patient' => 'Patient',
                    'dentist' => 'Dentist',
                    'clinic' => 'Clinic'
                ]
    		],
            'is_partner' => [
                'type' => 'select',
                'values' => [
                    0 => 'No',
                    1 => 'Yes',
                ]
            ],
            'website' => [
                'type' => 'text',
            ],
            'country_id' => [
                'type' => 'country',
            ],
            'state_name' => [
                'type' => 'text',
                'disabled' => true,
            ],
            'city_name' => [
                'type' => 'text',
                'disabled' => true,
            ],
            'zip' => [
                'type' => 'text',
            ],
            'gender' => [
                'type' => 'select',
                'values' => $this->genders
            ],
    		'birthyear' => [
    			'type' => 'text'
    		],
    		'address' => [
    			'type' => 'text',
    		],
    		'avg_rating' => [
    			'type' => 'text',
    			'disabled' => true,
    		],
            'ratings' => [
                'type' => 'text',
                'disabled' => true,
            ],
            'category_id' => [
                'type' => 'select',
                'multiple' => true,
                'values' => $this->categories
            ],
    		'avatar' => [
    			'type' => 'avatar'
    		],
            'civic_id' => [
                'type' => 'text',
            ],
            'fb_id' => [
                'type' => 'text',
            ],
            'gdpr_privacy' => [
                'type' => 'bool',
            ],
            'allow_withdraw' => [
                'type' => 'bool',
            ],
            'civic_kyc' => [
                'type' => 'bool',
            ],
            'dcn_address' => [
                'type' => 'text',
            ],
            'status' => [
                'type' => 'select',
                'values' => $this->statuses
            ],
    	];
    }

    public function list() {

        if( Auth::guard('admin')->user()->role!='admin' ) {
            return redirect('cms/users/edit/'.Auth::guard('admin')->user()->user_id);            
        }

        $getArr = $_GET;
        if(!is_array($getArr)) {
            $getArr = [];
        }
        $getArrNoSort = $getArr;

        $user_types = [
            '' => 'All user types',
            'patient_dentist_clinic.approved' => 'All Approved',
            'patient' => 'Patients',
            'dentist.all' => 'Dentists (All)',
            'dentist.new' => 'Dentists (New)',
            'dentist.pending' => 'Dentists (Suspicious)',
            'dentist.approved' => 'Dentists (Approved)',
            'dentist.rejected' => 'Dentists (Rejected)',
            'dentist.partners' => 'Dentists (Partners)',
            'clinic.all' => 'Clinics (All)',
            'clinic.new' => 'Clinics (New)',
            'clinic.pending' => 'Clinics (Suspicious)',
            'clinic.approved' => 'Clinics (Approved)',
            'clinic.rejected' => 'Clinics (Rejected)',
            'clinic.partners' => 'Clinics (Partners)',
            'dentist_clinic.all' => 'Dentists & Clinics (All)',
            'dentist_clinic.new' => 'Dentists & Clinics (New)',
            'dentist_clinic.pending' => 'Dentists & Clinics (Suspicious)',
            'dentist_clinic.approved' => 'Dentists & Clinics (Approved)',
            'dentist_clinic.rejected' => 'Dentists & Clinics (Rejected)',
            'dentist_clinic.partners' => 'Dentists & Clinics (Partners)',
        ];

        $user_statuses = [
            '' => 'Normal Users',
            'deleted' => 'Deleted Users',
            'all' => 'Normal & Deleted',
        ];

        $user_platforms = [
            '' => 'All Tools',
            'vox' => 'Dentavox',
            'trp' => 'Trusted Reviews',
        ];

        $users = User::orderBy('id', 'DESC');

        if(!empty($this->request->input('search-name'))) {
            $users = $users->where('name', 'LIKE', '%'.trim($this->request->input('search-name')).'%');
        }
        if(!empty($this->request->input('search-phone'))) {
            $users = $users->where('phone', 'LIKE', '%'.trim($this->request->input('search-phone')).'%');
        }
        if(!empty($this->request->input('search-email'))) {
            $users = $users->where('email', 'LIKE', '%'.trim($this->request->input('search-email')).'%');
        }
        if(!empty($this->request->input('search-address'))) {
            $users = $users->where('dcn_address', 'LIKE', '%'.trim($this->request->input('search-address')).'%');
        }
        if(!empty($this->request->input('search-id'))) {
            $users = $users->where('id', $this->request->input('search-id') );
        }
        if(!empty($this->request->input('search-ip-address'))) {
            $ip = $this->request->input('search-ip-address');
            $users = $users->whereHas('logins', function ($query) use ($ip) {
                $query->where('ip', 'like', $ip);
            });
        }

        // if(!empty($this->request->input('search-platform'))) {
        //     $users = $users->where('platform', $this->request->input('search-platform') );
        // }
        if(!empty($this->request->input('search-country'))) {
            $users = $users->where('country_id', $this->request->input('search-country') );
        }
        if(!empty($this->request->input('search-review'))) {
            $users = $users->has('reviews_in_dentist', '=', $this->request->input('search-review'));
        }
        if(!empty($this->request->input('search-surveys-taken'))) {
            $users = $users->has('vox_rewards', '>=', $this->request->input('search-surveys-taken'));

            $users = $users->whereHas('vox_rewards', function ($query) {
                $query->where('vox_id', '!=', 11);
            }, '>=', $this->request->input('search-surveys-taken'));
        }
        if(!empty($this->request->input('search-register-from'))) {
            $firstday = new Carbon($this->request->input('search-register-from'));
            $users = $users->where('created_at', '>=', $firstday);
        }
        if(!empty($this->request->input('search-register-to'))) {
            $firstday = new Carbon($this->request->input('search-register-to'));
            $users = $users->where('created_at', '<=', $firstday);
        }
        if(!empty($this->request->input('search-login-after'))) {
            $date = new Carbon($this->request->input('search-login-after'));

            $minLogins = max(1, intval($this->request->input('search-login-number')));
            $users = $users->whereHas('logins', function ($query) use ($date) {
                $query->where('created_at', '>=', $date);
            }, '>=', $minLogins);
        }

        if(!empty($this->request->input('search-type'))) {
            $tmp = explode('.', $this->request->input('search-type'));
            $type = $tmp[0];
            $status = isset($tmp[1]) && isset( $this->statuses[ $tmp[1] ] ) ? $tmp[1] : null;
            if( $type=='patient' ) {
                $users = $users->where(function ($query) {
                    $query->where('is_dentist', 0)
                    ->orWhereNull('is_dentist');
                });
            } else if( $type=='clinic' ) {
                $users = $users->where('is_dentist', 1)
                ->where('is_clinic', 1);
            } else if( $type=='dentist_clinic' ) {
                $users = $users->where('is_dentist', 1);
            } else if( $type=='dentist' ) {
                $users = $users->where('is_dentist', 1)->where(function ($query) {
                    $query->where('is_clinic', 0)
                    ->orWhereNull('is_clinic');
                });
            }

            if( $status ) {
                $users = $users->where('status', $status);
            } else if($tmp[1] == 'partners') {
                $users = $users->where('is_partner', 1);
            }

        }


        if(!empty($this->request->input('search-status'))) {
            $status = $this->request->input('search-status');
            if( $status=='all' ) {
                $users = $users->withTrashed();
            }
            if( $status=='deleted' ) {
                $users = $users->onlyTrashed();
            }
        }

        if(!empty($this->request->input('survey-count'))) {
            $order = request()->input( 'survey-count' );
            $users->getQuery()->orders = null;
            $users = $users
            ->select(DB::raw('count(vox_rewards.id) as vox_count, users.*'))
            ->join('vox_rewards', 'users.id', '=', 'vox_rewards.user_id', 'left outer')
            ->where('vox_rewards.vox_id', '!=', 11)
            ->groupBy('vox_rewards.user_id')
            ->orderByRaw('count(vox_rewards.id) '.$order);

            // dd($users->take(20)->get());

            unset($getArrNoSort['survey-count']);
        }

        // dd($users->first());


        if( null !== $this->request->input('results-number')) {
            $results = trim($this->request->input('results-number'));
        } else {
            $results = 50;
        }

        // dd($results);

        $total_count = $users->count();
        if( request()->input('export') ) {
            ini_set("memory_limit",-1);
            $users = $users->select(['title', 'name', 'email', 'platform'])->get();
        } else if(request()->input('export-fb')) {
            ini_set("memory_limit",-1);
            $users = $users->select(['id', 'name', 'email', 'country_id', 'phone', 'zip', 'city_name', 'state_name', 'birthyear', 'gender'])->get();
        } else if($results == 0) {
            $users = $users->take(3000)->get();
        } else {
            $users = $users->take($results)->get();
        }        
        //$total_count = isset( $total_count[0]->cnt ) ? $total_count[0]->cnt : 0;

        if( request()->input('export') ) {

            $flist = [];
            $flist[] = [
                'Title',
                'Name',
                'Email',
                'Platform',
            ];
            foreach ($users as $user) {
                $flist[] = [
                    $user->title ? $user->title : ( $user->gender=='m' ? 'Mr.' : ( $user->gender=='f' ? 'Mrs.' : '' ) ),
                    $user->name,
                    $user->email,
                    $user->platform,
                ];
            }

            $dir = storage_path().'/app/public/xls/';
            if(!is_dir($dir)) {
                mkdir($dir);
            }
            $fname = $dir.'export';

            Excel::create($fname, function($excel) use ($flist) {

                $excel->sheet('Sheet1', function($sheet) use ($flist) {

                    $sheet->fromArray($flist);
                    //$sheet->setWrapText(true);
                    //$sheet->getStyle('D1:E999')->getAlignment()->setWrapText(true); 

                });



            })->export('xls');

        }

        if(request()->input('export-fb')) {
            $export_fb = [];
            foreach ($users as $u) {
                $nameArr = explode(' ', $u->name);
                if(count($nameArr)>1) {
                    $ln = $nameArr[ count($nameArr)-1 ];
                    unset( $nameArr[ count($nameArr)-1 ] );
                    $fn = implode(' ', $nameArr);
                } else {
                    $fn = $u->name;
                    $ln = '';
                }
                $info = [
                    'uid' => $u->id,
                    'email' => $u->email,
                    'fn' => $fn,
                    'ln' => $ln,
                    'country' => '',
                    'phone' => '',
                    'zip' => $u->zip,
                    'ct' => $u->city_name,
                    'st' => $u->state_name,
                    'doby' => $u->birthyear,
                    'age' => '',
                    'gen' => '',
                    'value' => '',
                ];

                if( $u->country_id ) {
                    $info['country'] = mb_strtoupper($u->country->code);

                    if ($u->phone) {
                        $phone = trim(str_replace(' ', '', $u->phone));
                        $info['phone'] = '+'.$u->country->phone_code.$phone;
                    }
                }

                if( $u->birthyear ) {
                    $info['age'] = date('Y') - $u->birthyear;
                }

                if( $u->gender ) {
                    $info['gen'] = mb_strtoupper($u->gender);
                }

                if( $u->logins->isNotEmpty() ) {
                    $info['value'] = $u->logins->count();
                } else {
                    $info['value'] = 0;
                }

                //phone
                //country
                $export_fb[] = $info;
            }

            $csv = [
                array_keys($export_fb[0])
            ];


            foreach ($export_fb as $row) {
                $tmp = array_values($row);
                foreach ($tmp as $key => $value) {
                    $value = preg_replace('/[ ]{2,}|[\t]/', ' ', trim($value));
                    $tmp[$key] = str_replace(',', ' ', trim($value));
                }


                $csv[] = $tmp;
            }

            header("Content-type: text/csv");
            header("Content-Disposition: attachment; filename=export.csv");
            header("Pragma: no-cache");
            header("Expires: 0");

            foreach ($csv as $item) {
                echo implode(',', $item);
                echo '
    ';
            }
            exit;

        }


        $table_fields = [
            'selector' => array('format' => 'selector'),
            'id' => array(),
            'name' => array('template' => 'admin.parts.table-users-name'),
            'email' => array(),
            'login' => array('template' => 'admin.parts.table-users-login', 'label' => 'Frontend' ),
            'type' => array('template' => 'admin.parts.table-users-type'),
            'country_id' => array('format' => 'country'),
            'status' => array('template' => 'admin.parts.table-users-status', 'label' => 'Status'),
            'is_partner' => array('template' => 'admin.parts.table-users-partner', 'label' => 'Partner'),
        ];


        if($this->request->input('search-platform') == 'trp') {
            $table_fields['ratings'] = array('template' => 'admin.parts.table-users-ratings');
            $table_fields['reviews'] = array('template' => 'admin.parts.table-users-reviews', 'label' => 'Reviews');
        }

        if($this->request->input('search-platform') == 'vox') {
            $table_fields['surveys'] = array('template' => 'admin.parts.table-users-surveys', 'label' => 'Surveys','order' => true, 'orderKey' => 'survey-count');
        }

        $table_fields['created_at'] = array('format' => 'datetime', 'label' => 'Registered');
        $table_fields['last_login'] = array('template' => 'admin.parts.table-users-last-login', 'label' => 'Last login');
        $table_fields['delete'] = array('format' => 'delete');

        $vox_hidden = false;
        $trp_hidden = false;

        if (empty($this->request->input('search-platform')) || $this->request->input('search-platform') == 'trp') {
            $vox_hidden = true;
        }

        if (empty($this->request->input('search-platform')) || $this->request->input('search-platform') == 'vox') {
            $trp_hidden = true;
        }

        // dd($getArrNoSort);
        $current_url = url('cms/users/').'?'.http_build_query($getArrNoSort);

        return $this->showView('users', array(
            'users' => $users,
            'total_count' => $total_count,
            'user_types' => $user_types,
            'user_statuses' => $user_statuses,
            'search_register_from' => $this->request->input('search-register-from'),
            'search_register_to' => $this->request->input('search-register-to'),
            'search_email' => $this->request->input('search-email'),
            'search_phone' => $this->request->input('search-phone'),
            'search_name' => $this->request->input('search-name'),
            'search_id' => $this->request->input('search-id'),
            'search_address' => $this->request->input('search-address'),
            'search_tx' => $this->request->input('search-tx'),
            'results_number' => $this->request->input('results-number'),
            'search_ip_address' => $this->request->input('search-ip-address'),
            'search_type' => $this->request->input('search-type'),
            'search_status' => $this->request->input('search-status'),
            'search_platform' => $this->request->input('search-platform'),
            'search_country' => $this->request->input('search-country'),
            'search_review' => $this->request->input('search-review'),
            'search_surveys_taken' => $this->request->input('search-surveys-taken'),
            'search_login_after' => $this->request->input('search-login-after'),
            'search_login_number' => $this->request->input('search-login-number'),
            'user_platforms' => $user_platforms,
            'countries' => Country::get(),
            'trp_hidden' =>  $trp_hidden,
            'vox_hidden' =>  $vox_hidden,
            'table_fields' =>  $table_fields,
            'current_url' => $current_url,
        ));
    }



    public function delete( $id ) {
        $item = User::find($id);

        if(!empty($item)) {
            $item->deleteActions();
            User::destroy( $id );
        }

        $this->request->session()->flash('success-message', trans('admin.page.'.$this->current_page.'.deleted') );
        return redirect('cms/'.$this->current_page);
    }

    public function massdelete(  ) {
        if( Request::input('ids') ) {
            $delusers = User::whereIn('id', Request::input('ids'))->get();
            foreach ($delusers as $du) {
                $du->deleteActions();
                $du->delete();
            }
        }

        $this->request->session()->flash('success-message', 'All selected users and now deleted' );
        return redirect(url()->previous());
    }

    public function delete_avatar( $id ) {
        $item = User::withTrashed()->find($id);

        if(!empty($item)) {
            $item->hasimage = false;
            $item->save();
        }

        $this->request->session()->flash('success-message', trans('admin.page.'.$this->current_page.'.avatar-deleted') );
        return redirect('cms/'.$this->current_page.'/edit/'.$id);
    }

    public function delete_photo( $id, $position ) {
        $item = User::withTrashed()->find($id);

        if(!empty($item)) {
            if(!empty($item->photos[$position])) {
                $item->photos[$position]->delete();
            }
        }

        $this->request->session()->flash('success-message', trans('admin.page.'.$this->current_page.'.photo-deleted') );
        return redirect('cms/'.$this->current_page.'/edit/'.$id);
    }

    public function delete_ban( $id, $ban_id ) {
        $item = User::withTrashed()->find($id);
        $ban = UserBan::find($ban_id);

        if(!empty($ban) && !empty($item) && $ban->user_id == $item->id) {
            UserBan::destroy( $ban_id );
        }

        $this->request->session()->flash('success-message', trans('admin.page.'.$this->current_page.'.ban-deleted') );
        return redirect('cms/'.$this->current_page.'/edit/'.$id);
    }

    public function delete_vox( $id, $reward_id ) {
        $item = User::withTrashed()->find($id);
        $reward = VoxReward::find($reward_id);

        if(!empty($reward) && !empty($item) && $reward->user_id == $item->id) {
            VoxAnswer::where([
                ['user_id', $item->id],
                ['vox_id', $reward->vox_id],
            ])
            ->delete();
            VoxReward::destroy( $reward_id );
        }

        $this->request->session()->flash('success-message', trans('admin.page.'.$this->current_page.'.reward-deleted') );
        return redirect('cms/'.$this->current_page.'/edit/'.$id);
    }

    public function delete_unfinished( $id, $vox_id ) {
        $item = User::withTrashed()->find($id);
        
        if(!empty($item)) {
            VoxAnswer::where([
                ['user_id', $item->id],
                ['vox_id', $vox_id],
            ])
            ->delete();
        }

        $this->request->session()->flash('success-message', 'Survey answers deleted!' );
        return redirect('cms/'.$this->current_page.'/edit/'.$id);
    }

    public function delete_review( $review_id ) {
        $item = Review::find($review_id);
        
        if(!empty($item)) {
            $uid = $item->user_id;
            ReviewAnswer::where([
                ['review_id', $item->id],
            ])
            ->delete();
            if($item->dentist_id) {
                $dentist = User::find($item->dentist_id);
            }
            if($item->clinic_id) {
                $clinic = User::find($item->clinic_id);
            }
            Review::destroy( $review_id );
            if( !empty($dentist) ) {
                $dentist->recalculateRating();
            }
            if( !empty($clinic) ) {
                $clinic->recalculateRating();
            }
        }

        $this->request->session()->flash('success-message', trans('admin.page.'.$this->current_page.'.review-deleted') );
        return redirect('cms/'.$this->current_page.'/edit/'.$uid);
    }



    public function restore( $id ) {
        $item = User::onlyTrashed()->find($id);

        if(!empty($item)) {
            $item->restore();
        }

        $this->request->session()->flash('success-message', trans('admin.page.'.$this->current_page.'.restored') );
        return redirect('cms/'.$this->current_page);
    }


    public function loginas( $id, $platform=null ) {

        $item = User::find($id);

        if(!empty($item)) {
            Auth::login($item, true);
        }

        if(!empty($platform)) {
            $platform_urls = [
                'vox' => 'https://dentavox.dentacoin.com/',
                'trp' => 'https://reviews.dentacoin.com/',
            ];
            return redirect($platform_urls[$platform]);

        } else {
            return redirect('/');
        }
    }

    public function personal_data( $id ) {
        $item = User::withTrashed()->find($id);

        // dd($item->vox_rewards);

        if(!empty($item)) {

            return $this->showView('users-data', array(
                'item' => $item,
                'genders' => $this->genders,
            ));
        } else {
            return redirect('cms/users');
        }
    }

    public function edit( $id ) {
        $item = User::withTrashed()->find($id);
        $emails = Email::where('user_id', $id )->orderBy('created_at', 'DESC')->get();

        if(!empty($item)) {

            if($item->is_dentist) {
                $this->fields['password'] = [
                    'type' => 'password',
                ];
            } else {
                unset( $this->fields['status'] );
            }

            if(Request::isMethod('post')) {
            	foreach ($this->fields as $key => $value) {
            		if(empty($value['disabled']) && $value['type']!='avatar' && $key!='category_id') {
                        if($key=='type') {
                            if( $this->request->input($key)=='dentist' ) {
                                $item->is_dentist = true;
                                $item->is_clinic = false;
                            } else if( $this->request->input($key)=='clinic' ) {
                                $item->is_dentist = true;
                                $item->is_clinic = true;
                            } else {
                                $item->is_dentist = false;
                                $item->is_clinic = false;
                            }
                        } else if($key=='status') {
                            if( $this->request->input($key) && $item->$key!=$this->request->input($key) ) {
                                if( $this->request->input($key)=='approved' ) {
                                    if( $item->deleted_at ) {
                                        $item->restore();
                                    }
                                    $item->sendTemplate(26);

                                    $olde = $item->email;
                                    $item->email = 'ali.hashem@dentacoin.com';
                                    $item->save();
                                    $to_ali = $item->sendTemplate(26);
                                    $item->email = $olde;
                                    $item->save();
                                    $to_ali->delete();
                                } else if( $this->request->input($key)=='pending' ) {
                                    $item->sendTemplate(40);

                                    $olde = $item->email;
                                    $item->email = 'ali.hashem@dentacoin.com';
                                    $item->save();
                                    $to_ali = $item->sendTemplate(40);
                                    $item->sendTemplate(40);
                                    $item->email = $olde;
                                    $item->save();
                                    $to_ali->delete();
                                } if( $this->request->input($key)=='rejected' ) {
                                    $item->sendTemplate(14);
                                }
                            }
                            $item->$key = $this->request->input($key);
                        } else if($value['type']=='password') {
                            if( $this->request->input($key) ) {
                                $item->$key = bcrypt( $this->request->input($key) );                                
                            }
                        } else if($value['type']=='datepicker') {
                	       $item->$key = $this->request->input($key) ? new Carbon( $this->request->input($key) ) : null;
                        } else {
                           $item->$key = $this->request->input($key);                            
                        }
            		}
            	}
                $item->save();


                //Categories
                UserCategory::where('user_id', $item->id)->delete();
                $cats = $this->request->input('categories');
                if(!empty($cats)) {
                    foreach ($cats as $cat) {
                        $newc = new ArticleCategory;
                        $newc->user_id = $item->id;
                        $newc->category_id = $cat;
                        $newc->save();
                    }
                }

                if($item->status=='rejected' && empty($item->deleted_at)) {
                    $item->deleteActions();
                    User::destroy( $item->id );
                }

                Request::session()->flash('success-message', trans('admin.page.'.$this->current_page.'.updated'));
                return redirect('cms/'.$this->current_page.'/edit/'.$item->id);
            }

            $all_questions_answerd = VoxAnswer::where('user_id', $id)
            ->groupBy('vox_id')
            ->get();
            $rewarder_questions = VoxReward::where('user_id', $id)->get();
            $unanswerd_questions = array_diff($all_questions_answerd->pluck('vox_id')->toArray(), $rewarder_questions->pluck('vox_id')->toArray() );
            $unfinished = Vox::whereIn('id', $unanswerd_questions)->get();

            foreach ($unfinished as $k => $v) {
                $ans = VoxAnswer::where('user_id', $id)->where('vox_id', $v->id)->orderBy('id', 'asc')->first();
                $user_log = UserLogin::where('user_id', $id)->where('created_at', '<', $ans->created_at )->orderBy('id', 'desc')->first();

                $unfinished[$k]->user_id = $item->id;
                $unfinished[$k]->login = $user_log;
            }



            $habits_test_ans = false;
            $habits_tests = [];
            $welcome_survey = Vox::find(11);

            $welcome_questions = VoxQuestion::where('vox_id', $welcome_survey->id)->get();

            foreach ($welcome_questions as $welcome_question) {
                $welcome_answer = VoxAnswer::where('vox_id', $welcome_survey->id)->where('user_id', $item->id)->where('question_id', $welcome_question->id)->first();
                if ($welcome_answer) {
                     $habits_test_ans = true;
                }

                $welcome_old = VoxCrossCheck::where('user_id', $item->id)->where('question_id', $welcome_question->id)->first();
                if(!empty($welcome_old)) {
                    $oldans= $welcome_old->old_answer;
                    $oq = json_decode($welcome_question->answers, true)[($oldans) -1];
                } else {
                    $oq = '';
                }
                $habits_tests[] = [
                    'question' => $welcome_question->question,
                    'old_answer' => $oq ? $oq : ($welcome_answer ? json_decode($welcome_question->answers, true)[($welcome_answer->answer) -1] : ''),
                    'answer' => $oq && $welcome_answer ? json_decode($welcome_question->answers, true)[($welcome_answer->answer) -1] : '',
                    'last_updated' => !empty(VoxCrossCheck::where('user_id', $item->id)->where('question_id', $welcome_question->id)->orderBy('id', 'desc')->first()) ? VoxCrossCheck::where('user_id', $item->id)->where('question_id', $welcome_question->id)->orderBy('id', 'desc')->first()->created_at : '',
                    'updates_count' => VoxCrossCheck::where('user_id', $item->id)->where('question_id', $welcome_question->id)->count() ? VoxCrossCheck::where('user_id', $item->id)->where('question_id', $welcome_question->id)->count() : '',
                ];
            }

            $habits_tests[] = [
                'question' => 'What is your biological sex?',
                'old_answer' => !empty(VoxCrossCheck::where('user_id', $item->id)->where('question_id', 'gender')->first()) ? (VoxCrossCheck::where('user_id', $item->id)->where('question_id', 'gender')->first()->old_answer == 1 ? 'Male' : 'Female') : (!empty($item->gender) ? ($item->gender == 'm' ? 'Male' : 'Female') : ''),
                'answer' => !empty(VoxCrossCheck::where('user_id', $item->id)->where('question_id', 'gender')->first()) && !empty($item->gender) ? ($item->gender == 'm' ? 'Male' : 'Female') : '',
                'last_updated' => !empty(VoxCrossCheck::where('user_id', $item->id)->where('question_id','gender')->orderBy('id', 'desc')->first()) ? VoxCrossCheck::where('user_id', $item->id)->where('question_id', 'gender')->orderBy('id', 'desc')->first()->created_at : '',
                'updates_count' => VoxCrossCheck::where('user_id', $item->id)->where('question_id', 'gender')->count() ? VoxCrossCheck::where('user_id', $item->id)->where('question_id', 'gender')->count() : '',
            ];

            $habits_tests[] = [
                'question' => "What's your year of birth?",
                'old_answer' => !empty(VoxCrossCheck::where('user_id', $item->id)->where('question_id', 'birthyear')->first()) ? VoxCrossCheck::where('user_id', $item->id)->where('question_id', 'birthyear')->first()->old_answer : (!empty($item->birthyear) ? $item->birthyear : ''),
                'answer' => !empty(VoxCrossCheck::where('user_id', $item->id)->where('question_id', 'birthyear')->first()) && !empty($item->birthyear) ? $item->birthyear : '',
                'last_updated' => !empty(VoxCrossCheck::where('user_id', $item->id)->where('question_id','birthyear')->orderBy('id', 'desc')->first()) ? VoxCrossCheck::where('user_id', $item->id)->where('question_id', 'birthyear')->orderBy('id', 'desc')->first()->created_at : '',
                'updates_count' => VoxCrossCheck::where('user_id', $item->id)->where('question_id', 'birthyear')->count() ? VoxCrossCheck::where('user_id', $item->id)->where('question_id', 'birthyear')->count() : '',
            ];

            foreach (config('vox.details_fields') as $k => $v) {
                if (!empty($item->$k)) {
                    $habits_test_ans = true;
                }

                $old_an = !empty(VoxCrossCheck::where('user_id', $item->id)->where('question_id', $k)->first()) ? VoxCrossCheck::where('user_id', $item->id)->where('question_id', $k)->first()->old_answer : '';
                if ($old_an) {
                    $i=1;
                    foreach ($v['values'] as $key => $value) {
                        if($i==$old_an) {
                            $old_an = $value;
                            break;
                        }
                        $i++;
                    }
                }

                $habits_tests[] = [
                    'question' => $v['label'],
                    'old_answer' => $old_an ? $old_an : (!empty($item->$k) ? $v['values'][$item->$k] : ''),
                    'answer' => $old_an && !empty($item->$k) ? $v['values'][$item->$k] : '',
                    'last_updated' => !empty(VoxCrossCheck::where('user_id', $item->id)->where('question_id', $k)->orderBy('id', 'desc')->first()) ? VoxCrossCheck::where('user_id', $item->id)->where('question_id', $k)->orderBy('id', 'desc')->first()->created_at : '',
                    'updates_count' => VoxCrossCheck::where('user_id', $item->id)->where('question_id', $k)->count() ? VoxCrossCheck::where('user_id', $item->id)->where('question_id', $k)->count() : '',
                ];
            }

            return $this->showView('users-form', array(
                'habits_test_ans' => $habits_test_ans,
                'item' => $item,
                'categories' => $this->categories,
                'fields' => $this->fields,
                'unfinished' => $unfinished,
                'emails' => $emails,
                'habits_tests' => $habits_tests,
            ));
        } else {
            return redirect('cms/'.$this->current_page);
        }
    }

    public function incomplete() {

        if(request('export')) {
            $incomplete = IncompleteRegistration::whereNull('completed')->orderBy('id', 'desc')->get();
            $export = [];
            foreach ($incomplete as $u) {
                $nameArr = explode(' ', $u->name);
                if(count($nameArr)>1) {
                    $ln = $nameArr[ count($nameArr)-1 ];
                    unset( $nameArr[ count($nameArr)-1 ] );
                    $fn = implode(' ', $nameArr);
                } else {
                    $fn = $u->name;
                    $ln = '';
                }
                $info = [
                    'email' => $u->email,
                    'fn' => $fn,
                    'ln' => $ln,
                    'country' => '',
                    'phone' => '',
                ];

                if( $u->country_id ) {
                    $country = Country::find($u->country_id);
                    $info['country'] = mb_strtoupper($country->code);
                }


                if( !empty($country) && $u->phone ) {
                    $phone = trim(str_replace(' ', '', $u->phone));
                    $info['phone'] = '+'.$country->phone_code.$phone;
                }

                //phone
                //country
                $export[] = $info;
            }

            $csv = [
                ['email','fn','ln','country','phone']
            ];


            foreach ($export as $row) {
                $tmp = array_values($row);
                foreach ($tmp as $key => $value) {
                    $value = preg_replace('/[ ]{2,}|[\t]/', ' ', trim($value));
                    $tmp[$key] = str_replace(',', ' ', trim($value));
                }


                $csv[] = $tmp;
            }

            header("Content-type: text/csv");
            header("Content-Disposition: attachment; filename=export-incompletes.csv");
            header("Pragma: no-cache");
            header("Expires: 0");

            foreach ($csv as $item) {
                echo implode(',', $item);
                echo '
    ';
            }
            exit;

        }

        $incomplete = IncompleteRegistration::orderBy('id', 'desc')->take(50)->get();
        return $this->showView('users-incomplete', array(
            'items' => $incomplete,
        ));
    }

}
