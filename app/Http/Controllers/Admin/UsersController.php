<?php

namespace App\Http\Controllers\Admin;

use DeviceDetector\DeviceDetector;
use DeviceDetector\Parser\Device\DeviceParserAbstract;

use App\Http\Controllers\AdminController;

use App\Models\Email;
use App\Models\User;
use App\Models\UserLogin;
use App\Models\Reward;
use App\Models\Vox;
use App\Models\UserBan;
use App\Models\VoxQuestion;
use App\Models\VoxAnswer;
use App\Models\DcnReward;
use App\Models\VoxCrossCheck;
use App\Models\City;
use App\Models\Country;
use App\Models\UserCategory;
use App\Models\Review;
use App\Models\ReviewAnswer;
use App\Models\IncompleteRegistration;
use App\Models\UserInvite;
use App\Models\UserAsk;
use App\Models\OldSlug;

use Illuminate\Support\Facades\Input;

use Carbon\Carbon;

use Validator;
use Response;
use Request;
use Route;
use Auth;
use DB;
use Excel;
use Image;

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

        $this->ownership = [
            'unverified' => 'Unverified',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'suspicious' => 'Suspicious',
        ];

    	$this->fields = [
            'title' => [
                'type' => 'select',
                'values' => config('titles')
            ],
            'name' => [
                'type' => 'text',
            ],
            'slug' => [
                'type' => 'text',
            ],
            'email' => [
                'type' => 'text',
            ],
            'email_public' => [
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
                //'disabled' => true,
    		],
    		// 'avg_rating' => [
    		// 	'type' => 'text',
    		// 	'disabled' => true,
    		// ],
      //       'ratings' => [
      //           'type' => 'text',
      //           'disabled' => true,
      //       ],
            // 'category_id' => [
            //     'type' => 'select',
            //     'multiple' => true,
            //     'values' => $this->categories
            // ],
    		'avatar' => [
    			'type' => 'avatar'
    		],
            'civic_id' => [
                'type' => 'text',
            ],
            'fb_id' => [
                'type' => 'text',
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
            'ownership' => [
                'type' => 'select',
                'values' => $this->ownership
            ],
            'status' => [
                'type' => 'select',
                'values' => config('user-statuses')
            ],
            'is_hub_app_dentist' => [
                'type' => 'bool',
            ],
            'fb_recommendation' => [
                'type' => 'bool',
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
            'dentist.added_new' => 'Dentists (Added New)',
            'dentist.added_approved' => 'Dentists (Added Approved)',
            'dentist.added_rejected' => 'Dentists (Added Rejected)',
            'dentist.admin_imported' => 'Dentists (Admin Imported)',
            'clinic.all' => 'Clinics (All)',
            'clinic.new' => 'Clinics (New)',
            'clinic.pending' => 'Clinics (Suspicious)',
            'clinic.approved' => 'Clinics (Approved)',
            'clinic.rejected' => 'Clinics (Rejected)',
            'clinic.partners' => 'Clinics (Partners)',
            'clinic.added_new' => 'Clinics (Added New)',
            'clinic.added_approved' => 'Clinics (Added Approved)',
            'clinic.added_rejected' => 'Clinics (Added Rejected)',
            'clinic.admin_imported' => 'Clinics (Admin Imported)',
            'dentist_clinic.all' => 'Dentists & Clinics (All)',
            'dentist_clinic.new' => 'Dentists & Clinics (New)',
            'dentist_clinic.pending' => 'Dentists & Clinics (Suspicious)',
            'dentist_clinic.approved' => 'Dentists & Clinics (Approved)',
            'dentist_clinic.rejected' => 'Dentists & Clinics (Rejected)',
            'dentist_clinic.partners' => 'Dentists & Clinics (Partners)',
            'dentist_clinic.added_new' => 'Dentists & Clinics (Added New)',
            'dentist_clinic.added_approved' => 'Dentists & Clinics (Added Approved)',
            'dentist_clinic.added_rejected' => 'Dentists & Clinics (Added Rejected)',
            'dentist_clinic.admin_imported' => 'Dentists & Clinics (Admin Imported)',
        ];

        $user_statuses = [
            '' => 'Normal Users',
            'deleted' => 'Deleted Users',
            'self_deleted' => 'Self Deleted Users',
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
        if(!empty($this->request->input('registered-platform'))) {
            $users = $users->where('platform', $this->request->input('registered-platform') );
        }
        if(!empty($this->request->input('search-country'))) {
            $users = $users->where('country_id', $this->request->input('search-country') );
        }
        if(!empty($this->request->input('search-review'))) {
            $users = $users->has('reviews_in_dentist', '=', $this->request->input('search-review'));
        }
        if(!empty($this->request->input('search-surveys-taken'))) {
            $users = $users->whereHas('surveys_rewards', function ($query) {
                $query->where('reference_id', '!=', 11);
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
            $status = isset($tmp[1]) && isset( config('user-statuses')[ $tmp[1] ] ) ? $tmp[1] : null;
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
            } else if(!empty($tmp[1]) && $tmp[1] == 'partners') {
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
            if( $status=='self_deleted' ) {
                $users = $users->whereNotNull('self_deleted')->withTrashed();
            }
        }

        if(!empty($this->request->input('survey-count'))) {
            $order = request()->input( 'survey-count' );
            $users->getQuery()->orders = null;
            $users = $users
            ->select(DB::raw('count(dcn_rewards.id) as vox_count, users.*'))
            ->join('dcn_rewards', 'users.id', '=', 'dcn_rewards.user_id', 'left outer')
            ->where('dcn_rewards.type', 'survey')
            ->where('dcn_rewards.reference_id', '!=', 11)
            ->where('dcn_rewards.platform', 'vox')
            ->groupBy('dcn_rewards.user_id')
            ->orderByRaw('count(dcn_rewards.id) '.$order);

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
            $users = $users->select(['id', 'name', 'email', 'country_id', 'phone', 'zip', 'city_name', 'state_name', 'birthyear', 'gender', 'is_dentist'])->get();
        } else if(request()->input('export-sendgrid')) {
            ini_set("memory_limit",-1);
            $users = $users->whereNull('unsubscribe')->select(['id','slug', 'name', 'email', 'country_id', 'phone', 'zip', 'city_name', 'state_name', 'birthyear', 'gender', 'is_partner', 'is_dentist', 'is_clinic', 'platform'])->get();
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

        } else if( request()->input('export-sendgrid') ) {

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
                $fn = str_replace(["'", '"'], '', $fn);
                $ln = str_replace(["'", '"'], '', $ln);

                $info = [
                    'user_id' => $u->id,
                    'email' => $u->email,
                    'first_name' => $fn,
                    'last_name' => $ln,
                    'country' => '',
                    'city' => $u->city_name,
                    'dob' => '',
                    'age' => $u->zip,
                    'type' => $u->is_dentist ? ( $u->is_clinic ? 'Clinic' : 'Dentist' ) : 'Patient',
                    'partner' => $u->is_partner ? 'partner' : '',
                    'tool' => $u->platform,
                    'trp_link' => $u->getLink(),
                ];

                if( $u->country_id ) {
                    $info['country'] = mb_strtoupper($u->country->code);
                }

                if( $u->dob ) {
                    $info['dob'] = '01/01/'.$u->birthyear;
                    $info['age'] = date('Y') - $u->birthyear;
                } else {
                    $info['dob'] = '';
                    $info['age'] = 0;
                }

                if( $u->logins->isNotEmpty() ) {
                    $info['logins'] = $u->logins->count();
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

        } else if( request()->input('export-fb') ) {
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
                    'surveys' => '',
                    'reviews_patient' => '',
                    'reviews_invites' => '',
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
                } else {
                    $info['age'] = 0;
                }

                if( $u->gender ) {
                    $info['gen'] = mb_strtoupper($u->gender);
                }

                if( $u->logins->isNotEmpty() ) {
                    $info['value'] = $u->logins->count();
                } else {
                    $info['value'] = 0;
                }

                if ($u->vox_rewards->isNotEmpty()) {
                    $info['surveys'] = $u->vox_rewards->count() - 1;
                } else {
                    $info['surveys'] = 0;
                }

                if ($u->reviews_out->isNotEmpty()) {
                    $info['reviews_patient'] = $u->reviews_out->count();
                } else {
                    $info['reviews_patient'] = 0;
                }

                if ($u->is_dentist && $u->invites->isNotEmpty()) {
                    $info['reviews_invites'] = $u->invites->count();
                } else {
                    $info['reviews_invites'] = 0;
                }

                if( request()->input('export-sendgrid') ) {
                    $info['type'] = $u->is_dentist ? ( $u->is_clinic ? 'Clinic' : 'Dentist' ) : 'Patient';
                    $info['partner'] = $u->is_partner ? 'Y' : 'N';
                    $info['link'] = $u->getLink();
                    $info['platform'] = $u->platform;
                    $info['fn'] = str_replace(["'", '"'], '', $info['fn']);
                    $info['ln'] = str_replace(["'", '"'], '', $info['ln']);
                    unset( $info['phone'] );
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
            'registered_platform' => $this->request->input('registered-platform'),
            'search_country' => $this->request->input('search-country'),
            'search_review' => $this->request->input('search-review'),
            'search_surveys_taken' => $this->request->input('search-surveys-taken'),
            'search_login_after' => $this->request->input('search-login-after'),
            'search_login_number' => $this->request->input('search-login-number'),
            'user_platforms' => $user_platforms,
            'countries' => Country::with('translations')->get(),
            'trp_hidden' =>  $trp_hidden,
            'vox_hidden' =>  $vox_hidden,
            'table_fields' =>  $table_fields,
            'current_url' => $current_url,
        ));
    }



    public function delete( $id ) {
        $item = User::find($id);

        if(!empty($item)) {

            if (!empty(Request::input('deleted_reason'))) {
                $item->deleted_reason = Request::input('deleted_reason');
                $item->save();
                $item->deleteActions();
                User::destroy( $id );

                $this->request->session()->flash('success-message', trans('admin.page.'.$this->current_page.'.deleted') );
                return redirect(!empty(Request::server('HTTP_REFERER')) ? Request::server('HTTP_REFERER') : 'cms/'.$this->current_page);

            } else {
                $this->request->session()->flash('error-message', "You have to write a reason why this user has to be deleted" );
                return redirect('cms/users/edit/'.$item->id);
            }
            
        } else {
            return redirect('cms/'.$this->current_page);
        }
        

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

    public function add_avatar( $id ) {
        $user = User::find($id);

        if( Request::file('image') && Request::file('image')->isValid() ) {
            $img = Image::make( Input::file('image') )->orientate();
            $user->addImage($img);

            $user->hasimage_social = false;
            $user->save();

            foreach ($user->reviews_out as $review_out) {
                $review_out->hasimage_social = false;
                $review_out->save();
            }

            foreach ($user->reviews_in_dentist as $review_in_dentist) {
                $review_in_dentist->hasimage_social = false;
                $review_in_dentist->save();
            }

            foreach ($user->reviews_in_clinic as $review_in_clinic) {
                $review_in_clinic->hasimage_social = false;
                $review_in_clinic->save();
            }

            return Response::json(['success' => true, 'thumb' => $user->getImageUrl(true), 'name' => '' ]);
        }
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
        $reward = DcnReward::find($reward_id);

        if(!empty($reward) && !empty($item) && $reward->user_id == $item->id) {
            VoxAnswer::where([
                ['user_id', $item->id],
                ['vox_id', $reward->reference_id],
            ])
            ->delete();
            DcnReward::destroy( $reward_id );
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
            $patient = User::where('id', $uid)->withTrashed()->first();

            ReviewAnswer::where([
                ['review_id', $item->id],
            ])
            ->delete();
            if($item->dentist_id) {
                $dentist = User::find($item->dentist_id);
            } else if($item->clinic_id) {
                $dentist = User::find($item->clinic_id);
            }

            $reward_for_review = DcnReward::where('user_id', $patient->id)->where('platform', 'trp')->where('type', 'review')->where('reference_id', $item->id)->first();
            if (!empty($reward_for_review)) {
                $reward_for_review->delete();
            }
            
            Review::destroy( $review_id );
            if( !empty($dentist) ) {
                $dentist->recalculateRating();
                $substitutions = [
                    'spam_author_name' => $patient->name,
                ];
                
                $dentist->sendGridTemplate(87, $substitutions, 'trp');
            }

            $ban = new UserBan;
            $ban->user_id = $patient->id;
            $ban->domain = 'trp';
            $ban->type = 'spam-review';
            $ban->save();

            $patient->sendGridTemplate(86, null, 'trp');
        }

        $this->request->session()->flash('success-message', trans('admin.page.'.$this->current_page.'.review-deleted') );
        return redirect('cms/'.$this->current_page.'/edit/'.$dentist->id);
    }



    public function restore( $id ) {

        $item = User::onlyTrashed()->find($id);

        if(!empty($item)) {
            $item->restoreActions();
            $item->restore();
        }

        $this->request->session()->flash('success-message', trans('admin.page.'.$this->current_page.'.restored') );
        return redirect(!empty(Request::server('HTTP_REFERER')) ? Request::server('HTTP_REFERER') : 'cms/'.$this->current_page);
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

    public function add() {

        if(Request::isMethod('post')) {

            $validator = Validator::make(Request::all(), [
                'type' => array('required', 'in:dentist,clinic'),
                'name' => array('required', 'min:3'),
                'email' => array('required', 'email', 'unique:users,email'),
                'country_id' => array('required', 'exists:countries,id'),
                'address' =>  array('required', 'string'),
            ]);

            if ($validator->fails()) {;

                return redirect(url('cms/users/add'))
                ->withInput()
                ->withErrors($validator);
            } else {
                
                $newuser = new User;
                $newuser->title = Request::input('title');
                $newuser->name = Request::input('name');
                $newuser->email = Request::input('email');
                $newuser->country_id = Request::input('country_id');
                $newuser->phone = Request::input('phone');
                $newuser->is_partner = Request::input('is_partner');
                $newuser->platform = 'trp';
                $newuser->status = 'admin_imported';
                $newuser->address = Request::input('address');
                $newuser->website = Request::input('website');
                $newuser->is_dentist = 1;
                $newuser->is_clinic = Request::input('type')=='clinic' ? 1 : 0;

                $newuser->save();

                $newuser->slug = $newuser->makeSlug();
                $newuser->save();

                if( Request::file('image') && Request::file('image')->isValid() ) {
                    $img = Image::make( Input::file('image') )->orientate();
                    $newuser->addImage($img);
                }

                $substitutions = [
                    "invitation_link" => getLangUrl( 'welcome-dentist/claim/'.$newuser->id , null, 'https://reviews.dentacoin.com/').'?'. http_build_query(['popup'=>'claim-popup']),
                ];

                $newuser->sendGridTemplate(81, $substitutions);

                Request::session()->flash('success-message', 'Dentist Added');
                return redirect('cms/'.$this->current_page.'/edit/'.$newuser->id);
            }
        }

        return $this->showView('users-add', array(
            'fields' => $this->fields,
            'countries' => Country::with('translations')->get(),
        ));
    }

    public function edit( $id ) {
        $item = User::withTrashed()->find($id);

        if(!empty($item)) {

            $emails = Email::where('user_id', $id )->where('sent', 1)->orderBy('created_at', 'DESC')->get();

            if($item->is_dentist) {
                $this->fields['password'] = [
                    'type' => 'password',
                ];
            } else {
                unset( $this->fields['status'] );
            }

            if(Request::isMethod('post')) {

                foreach ($this->fields as $key => $value) {
                    if(empty($value['disabled']) && $value['type']!='avatar') {
                        if($key=='city_name') {

                        }
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
                        } else if($key=='slug') {
                            if (!empty($this->request->input($key))) {
                                $existing_slug = User::where('id', '!=', $item->id)->where('slug', 'like', $this->request->input($key))->first();

                                if (empty($existing_slug)) {
                                    $existing_slug = OldSlug::where('user_id', '!=', $item->id)->where('slug', 'like', $this->request->input($key))->first();
                                }

                                if (!empty($existing_slug)) {
                                    Request::session()->flash('error-message', 'This slug is already used by another user');
                                    return redirect('cms/'.$this->current_page.'/edit/'.$item->id);
                                } else {
                                    $existed_old_slug = OldSlug::where('user_id', $item->id)->where('slug', 'like', $this->request->input($key))->first();

                                    if (!empty($existed_old_slug)) {
                                        $existed_old_slug->delete();
                                    }

                                    if ($item->$key != $this->request->input($key)) {
                                        
                                        $oldslug = new OldSlug;
                                        $oldslug->user_id = $item->id;
                                        $oldslug->slug = $item->slug;
                                        $oldslug->save();

                                        $item->$key = $this->request->input($key);
                                    }
                                }
                            }
                        } else if($key=='name' || $key=='email') {
                            $existing = User::withTrashed()->where('id', '!=', $item->id)->where($key, 'like', $this->request->input($key))->first();

                            if (!empty($existing)) {
                                Request::session()->flash('error-message', 'This '.$key.' is already used by another user - ID '.$existing->id);
                                return redirect('cms/'.$this->current_page.'/edit/'.$item->id);
                            } else {
                                $item->$key = $this->request->input($key);
                            }
                        } else if($key=='status') {
                            if( $this->request->input($key) && $item->$key!=$this->request->input($key) ) {

                                if ($this->request->input($key)=='added_approved') {
                                    $patient = User::find($item->invited_by);

                                    if (empty($item->slug)) {
                                        $item->slug = $item->makeSlug();
                                        $item->save();
                                    }

                                    if (!empty($patient)) {
                                        $substitutions = [
                                            "invitation_link" => getLangUrl( 'dentist/'.$item->slug.'/claim/'.$item->id , null, 'https://reviews.dentacoin.com/').'?'. http_build_query(['popup'=>'claim-popup']),
                                        ];

                                        $item->sendGridTemplate(43, $substitutions);

                                        // $item->sendTemplate( 43  , [
                                        //     'dentist_name' => $item->name,
                                        //     'patient_name' => $patient->name,
                                        // ]);
                                        $amount = Reward::getReward('patient_add_dentist');
                                        $reward = new DcnReward();
                                        $reward->user_id = $patient->id;
                                        $reward->reward = $amount;
                                        $reward->platform = 'trp';
                                        $reward->type = 'added_dentist';
                                        $reward->reference_id = $item->id;

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

                                        $substitutions = [
                                            'added_dentist_name' => $item->getName(),
                                            'trp_added_dentist_prf' => $item->getLink().'?popup=popup-login',
                                        ];

                                        $patient->sendGridTemplate(65, $substitutions, 'trp');
                                    }
                                } else if( $this->request->input($key)=='approved' ) {
                                    if( $item->deleted_at ) {
                                        $item->restoreActions();
                                        $item->restore();
                                    }

                                    if (empty($item->slug)) {
                                        $item->slug = $item->makeSlug();
                                        $item->save();
                                    }

                                    $platformMails = [
                                        'vox' => 84,
                                        'trp' => 26,
                                        'dentacare' => 83,
                                        'assurance' => 85,
                                        'dentacoin' => 83,
                                        'dentists' => 83,
                                        'wallet' => 83,
                                    ];
                                    
                                    $item->sendGridTemplate($platformMails[$item->platform]);

                                    $olde = $item->email;
                                    $item->email = 'ali.hashem@dentacoin.com';
                                    $item->save();

                                    if ($item->platform == 'trp') {
                                        $to_ali = $item->sendGridTemplate(26);
                                    } else {
                                        $to_ali = $item->sendTemplate(26);
                                    }

                                    $item->email = $olde;
                                    $item->ownership = 'approved';
                                    $item->save();
                                    $to_ali->delete();

                                } else if( $this->request->input($key)=='pending' ) {
                                    $olde = $item->email;
                                    $item->email = 'ali.hashem@dentacoin.com';
                                    $item->save();
                                    $to_ali = $item->sendTemplate(40);
                                    $item->email = $olde;
                                    $item->save();
                                    $item->sendTemplate(40);
                                    $to_ali->delete();
                                } else if( $this->request->input($key)=='rejected' ) {
                                    $item->sendTemplate(14);
                                }
                            }
                            $item->$key = $this->request->input($key);
                        } else if($key=='ownership') {
                            if( $this->request->input($key) && $item->$key!=$this->request->input($key) ) {

                                if ($this->request->input($key)=='rejected') {

                                    $item->sendGridTemplate(66, null, 'trp');
                                    
                                } else if ($this->request->input($key)=='suspicious') {
                                    
                                    $item->sendGridTemplate(67, null, 'trp');
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
                $item->hasimage_social = false;

                $item->save();

                foreach ($item->reviews_out as $review_out) {
                    $review_out->hasimage_social = false;
                    $review_out->save();
                }

                foreach ($item->reviews_in_dentist as $review_in_dentist) {
                    $review_in_dentist->hasimage_social = false;
                    $review_in_dentist->save();
                }

                foreach ($item->reviews_in_clinic as $review_in_clinic) {
                    $review_in_clinic->hasimage_social = false;
                    $review_in_clinic->save();
                }

                //Categories
                // UserCategory::where('user_id', $item->id)->delete();
                // $cats = $this->request->input('categories');
                // if(!empty($cats)) {
                //     foreach ($cats as $cat) {
                //         $newc = new ArticleCategory;
                //         $newc->user_id = $item->id;
                //         $newc->category_id = $cat;
                //         $newc->save();
                //     }
                // }

                if($item->status=='rejected' && empty($item->deleted_at)) {
                    $item->deleteActions();
                    User::destroy( $item->id );
                }

                Request::session()->flash('success-message', trans('admin.page.'.$this->current_page.'.updated'));
                return redirect('cms/'.$this->current_page.'/edit/'.$item->id);
            }

            $all_questions_answerd = VoxAnswer::where('user_id', $id)
            ->groupBy('vox_id')
            ->orderBy('created_at')
            ->get();
            $rewarder_questions = DcnReward::where('user_id', $id)->where('platform' , 'vox')->get();
            $unanswerd_questions = array_diff($all_questions_answerd->pluck('vox_id')->toArray(), $rewarder_questions->pluck('reference_id')->toArray() );
            foreach ($unanswerd_questions as $value) {
                $unfinished[$value] = [];
            }
            $unfinishedVoxes = Vox::whereIn('id', $unanswerd_questions)->get();
            $unfinished = [];
            
            foreach ($unfinishedVoxes as $v) {
                $unfinished[$v->id] = $v;
                $ans = VoxAnswer::where('user_id', $id)->where('vox_id', $v->id)->orderBy('id', 'asc')->first();
                $user_log = UserLogin::where('user_id', $id)->where('created_at', '<', $ans->created_at )->orderBy('id', 'desc')->first();

                $unfinished[$v->id]->user_id = $item->id;
                $unfinished[$v->id]->login = $user_log;
                $unfinished[$v->id]->taken_date = $ans->created_at;
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
                    $n = $oldans != 0 ? (($oldans) -1) : 1;
                    $oq = json_decode($welcome_question->answers, true)[$n ];
                } else {
                    $oq = '';
                }
                $habits_tests[] = [
                    'question' => $welcome_question->question,
                    'old_answer' => $oq ? $oq : ($welcome_answer ? json_decode($welcome_question->answers, true)[($welcome_answer->answer) -1] : ''),
                    'answer' => $oq && $welcome_answer ? ((isset(json_decode($welcome_question->answers, true)[($welcome_answer->answer) -1])) ? json_decode($welcome_question->answers, true)[($welcome_answer->answer) -1] : '' ) : '',
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
                'countries' => Country::with('translations')->get(),
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

    public function import() {

        if(Request::isMethod('post')) {

            if(Input::file('file')) {
                if (Input::file('file')->getMimeType() != 'application/vnd.ms-office') {
                    $this->request->session()->flash('error-message', 'File format not accepted. Upload .xls');
                    return redirect('cms/'.$this->current_page.'/import');
                }

                global $results, $not_imported;

                Excel::load( Input::file('file')->path() , function($reader)  { //

                    global $results, $not_imported;
                    $not_imported = [];
                    $results = [];
                    $reader->each(function($sheet) {
                        global $results;
                        $results[] = $sheet->toArray();
                    });

                    unset($results[0]);
                    //dd($results);
                    if(!empty($results)) {

                        foreach ($results as $k => $row) {

                            //dd($results, $k, $row);
                            if (!empty($row[2]) && !empty($row[0]) && filter_var($row[2], FILTER_VALIDATE_EMAIL) && !empty($row[10])) {
                                $existing_user = User::where('email', 'like', $row[2] )->first();
                                $existing_place = User::where('place_id', $row[10] )->first();

                                if (!empty($existing_user)) {
                                    $not_imported[] = $row[0];
                                } else if(!empty($existing_place)) {
                                    $not_imported[] = 'already imported user '.$row[0].'. Existing user ID: '.$existing_place->id;
                                } else {
                                    $newuser = new User;
                                    $newuser->name = $row[0];
                                    $newuser->name_alternative = $row[1];
                                    $newuser->email = $row[2];
                                    $newuser->phone = $row[3];
                                    $newuser->website = $row[4];
                                    $newuser->work_hours = $row[5];
                                    $newuser->is_dentist = 1;
                                    $newuser->is_clinic = ($row[6] == 'clinic') ? 1 : 0;
                                    $country_n = $row[7];
                                    $country = Country::whereHas('translations', function ($query) use ($country_n) {
                                        $query->where('name', 'LIKE', $country_n);
                                    })->first();
                                    $newuser->country_id = $country->id;
                                    $newuser->address = $row[8];
                                    $newuser->place_id = $row[10];
                                    $newuser->status = 'admin_imported';
                                    $newuser->platform = 'trp';
                                    $newuser->save();

                                    if (!empty($row[9])) {
                                        $img = Image::make( $row[9] )->orientate();
                                        $newuser->addImage($img);
                                    }

                                    $substitutions = [
                                        "invitation_link" => getLangUrl( 'welcome-dentist/claim/'.$newuser->id , null, 'https://reviews.dentacoin.com/').'?'. http_build_query(['popup'=>'claim-popup']),
                                    ];

                                    $newuser->sendGridTemplate(81, $substitutions);
                                }
                            } else {
                                $not_imported[] = $row[0] ? $row[0] : ($row[2] ? $row[2] : 'without name and mail');
                            }

                        }
                    }

                });

                if(!empty($not_imported)) {
                    $this->request->session()->flash('warning-message', 'Dentists were imported successfully. However, there were some invalid or missing dentist emails which were skipped - '.implode(',', $not_imported));
                } else {
                    $this->request->session()->flash('success-message', 'Dentists were imported successfully.');
                }
                
                return redirect('cms/'.$this->current_page.'/import');

            } else {
                return redirect('cms/'.$this->current_page.'/import');
            }
        }

        return $this->showView('users-import');
    }

    public function upload_temp($locale=null) {

        if( Request::file('image') && Request::file('image')->isValid() ) {
            $img = Image::make( Input::file('image') )->orientate();
            list($thumb, $full, $name) = User::addTempImage($img);
            return Response::json(['success' => true, 'thumb' => $thumb, 'name' => $name ]);
        }
    }

}
