<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;

use DeviceDetector\Parser\Device\DeviceParserAbstract;
use DeviceDetector\DeviceDetector;

use Illuminate\Support\Facades\Input;
use Maatwebsite\Excel\Facades\Excel;

use App\Models\IncompleteRegistration;
use App\Models\VoxQuestionAnswered;
use App\Models\UnclaimedDentist;
use App\Models\UserGuidedTour;
use App\Models\DcnTransaction;
use App\Models\VoxCrossCheck;
use App\Models\WalletAddress;
use App\Models\ReviewAnswer;
use App\Models\UserHistory;
use App\Models\VoxQuestion;
use App\Models\UserInvite;
use App\Models\UserAction;
use App\Models\LeadMagnet;
use App\Models\UserLogin;
use App\Models\VoxAnswer;
use App\Models\DcnReward;
use App\Models\UserTeam;
use App\Models\OldEmail;
use App\Models\UserBan;
use App\Models\OldSlug;
use App\Models\Country;
use App\Models\Review;
use App\Models\Reward;
use App\Models\Email;
use App\Models\User;
use App\Models\Vox;

use App\Helpers\GeneralHelper;
use App\Exports\Export;
use App\Imports\Import;
use Carbon\Carbon;

use Validator;
use Response;
use Request;
use Image;
use Route;
use Auth;
use DB;

class UsersController extends AdminController {

    private $fields;
    public function __construct(\Illuminate\Http\Request $request, Route $route, $locale=null) {
        parent::__construct($request, $route, $locale);

        $this->genders = [
            '' => null,
            'm' => trans('admin.common.gender.m'),
            'f' => trans('admin.common.gender.f'),
        ];

        $this->ban_types = [
            'deleted' => 'Deleted',
            'bad_ip' => 'Bad IP',
            'suspicious_admin' => 'Suspicious (Admin)',
            'manual_verification' => 'Manual verification before withdrawing',
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
            'civic_email' => [
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
            'user_patient_type' => [
                'type' => 'select',
                'values' => [
                    'journalist' => 'Journalist',
                    'supplier' => 'Supplier/ Manufacturer',
                    'researcher' => 'Researcher',
                    'dental_student' => 'Dental/ Medical Student',
                ]
            ],
            'worker_name' => [
                'type' => 'text',
            ],
            'working_position' => [
                'type' => 'select',
                'values' => [
                    'practice_manager' => 'Practice Manager',
                    'dentist' => 'Dentist',
                    'dental_hygienist' => 'Dental Hygienist',
                    'dental_assistant' => 'Dental Assistant',
                    'marketing_specialist' => 'Marketing Specialist',
                    'other' => 'Other',
                ]
            ],
            'working_position_label' => [
                'type' => 'text',
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
            'custom_lat_lon' => [
                'type' => 'bool',
            ],
            'lat' => [
                'type' => 'text',
            ],
            'lon' => [
                'type' => 'text',
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
            'apple_id' => [
                'type' => 'text',
            ],
            'allow_withdraw' => [
                'type' => 'bool',
            ],
            'civic_kyc' => [
                'type' => 'bool',
            ],
            'status' => [
                'type' => 'select',
                'values' => config('user-statuses')
            ],
            'patient_status' => [
                'type' => 'select',
                'values' => config('patient-statuses')
            ],
            'is_hub_app_dentist' => [
                'type' => 'bool',
            ],
            'fb_recommendation' => [
                'type' => 'bool',
            ],
            'unsubscribe' => [
                'type' => 'bool',
            ],
            'featured' => [
                'type' => 'bool',
            ],
            'ip_protected' => [
                'type' => 'bool',
            ],
            'vip_access' => [
                'type' => 'bool',
            ],
            'trusted' => [
                'type' => 'bool',
            ],
            'is_admin' => [
                'type' => 'bool',
            ],
            'skip_civic_kyc_country' => [
                'type' => 'bool',
            ],
        ];
    }

    public function list() {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'support'])) {
            return redirect('cms/users/users/edit/'.Auth::guard('admin')->user()->user_id);            
        }

        $getArr = $_GET;
        if(!is_array($getArr)) {
            $getArr = [];
        }
        $getArrNoSort = $getArr;

        $user_types = [
            '' => 'All user types',
            'patient_dentist_clinic.approved' => 'All Approved',
            'patient.all' => 'Patients (All)',
            'patient.old_verified_no_kyc' => 'Patients (Old verified - no KYC)',
            'patient.old_verified_no_sc' => 'Patients (Old verified - no SC)',
            'patient.new_verified' => 'Patients (New - verified)',
            'patient.new_not_verified' => 'Patients (New - not verified)',
            'patient.suspicious_badip' => 'Patients (Suspicious (Bad IP))',
            'patient.suspicious_admin' => 'Patients (Suspicious (Admin))',
            'patient.deleted' => 'Patients (Deleted)',
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
            'dentist.added_by_clinic_new' => 'Dentists (Added by Clinic New)', 
            'dentist.added_by_clinic_unclaimed' => 'Dentists (Added by Clinic Approved)', 
            'dentist.added_by_clinic_claimed' => 'Dentists (Added by Clinic Claimed)', 
            'dentist.added_by_clinic_rejected' => 'Dentists (Added by Clinic Rejected)', 
            'dentist.dentist_no_email' => 'Dentists (No Email For Team)', 
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
            'clinic.added_by_dentist_new' => 'Clinics (Added by Dentist New)', 
            'clinic.added_by_dentist_unclaimed' => 'Clinics (Added by Dentist Approved)', 
            'clinic.added_by_dentist_claimed' => 'Clinics (Added by Dentist Claimed)', 
            'clinic.added_by_dentist_rejected' => 'Clinics (Added by Dentist Rejected)',
            'clinic.clinic_branch' => 'Clinics (Branches)',
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
            'dentist_clinic.test' => 'Dentists & Clinics (Test)',
            'dentist_clinic.duplicated_email' => 'Dentists & Clinics (Duplicated Email)', 
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

        if(!empty(request('search-name'))) {
            $users = $users->where('name', 'LIKE', '%'.trim(request('search-name')).'%');
        }
        if(!empty(request('search-phone'))) {
            $users = $users->where('phone', 'LIKE', '%'.trim(request('search-phone')).'%');
        }
        if(!empty(request('search-email'))) {
            $s_email = request('search-email');
            $users = $users->where( function($query) use ($s_email) {
                $query->where('email', 'LIKE', '%'.trim($s_email).'%')
                ->orWhereHas('oldEmails', function ($queryy) use ($s_email) {
                    $queryy->where('email', 'LIKE', $s_email);
                });
            });
        }
        if(!empty(request('search-address'))) {
            $dcn_address = request('search-address');
            $users = $users->whereHas('wallet_addresses', function ($query) use ($dcn_address) {
                $query->where('dcn_address', 'like', $dcn_address);
            });
        }
        if(!empty(request('civic-kyc-hash'))) {
            $users = $users->where('civic_kyc_hash', 'LIKE', '%'.trim(request('civic-kyc-hash')).'%');
        }
        if(!empty(request('search-id'))) {
            $users = $users->where('id', request('search-id') );
        }
        if(!empty(request('search-ip-address'))) {
            $ip = request('search-ip-address');
            $users = $users->whereHas('logins', function ($query) use ($ip) {
                $query->where('ip', 'like', $ip);
            });
        }
        if(!empty(request('registered-platform'))) {
            $users = $users->where('platform', request('registered-platform') );
        }
        if(!empty(request('search-country'))) {
            $users = $users->where('country_id', request('search-country') );
        }
        if(!empty(request('search-review'))) {
            $users = $users->has('reviews_in_dentist', '=', request('search-review'));
        }
        if(!empty(request('search-surveys-taken'))) {
            $users = $users->whereHas('surveys_rewards', function ($query) {
                $query->where('reference_id', '!=', 11);
            }, '>=', request('search-surveys-taken'));
        }
        if(!empty(request('search-dentist-claims'))) {
            $users = $users->whereHas('claims', function ($query) {
                $query->where('status', request('search-dentist-claims'));
            });
        }
        if(!empty(request('search-register-from'))) {
            $firstday = new Carbon(request('search-register-from'));
            $users = $users->where('created_at', '>=', $firstday);
        }
        if(!empty(request('search-register-to'))) {
            $firstday = new Carbon(request('search-register-to'));
            $users = $users->where('created_at', '<=', $firstday);
        }
        if(!empty(request('search-login-after'))) {
            $date = new Carbon(request('search-login-after'));

            $minLogins = max(1, intval(request('search-login-number')));
            $users = $users->whereHas('logins', function ($query) use ($date) {
                $query->where('created_at', '>=', $date);
            }, '>=', $minLogins);
        }

        if(!empty(request('search-type'))) {

            $users = $users->where(function ($query) {
                foreach (request('search-type') as $stype) {
                    $tmp = explode('.', $stype);
                    $type = $tmp[0];
                    $status = isset($tmp[1]) && isset( config('user-statuses')[ $tmp[1] ] ) ? $tmp[1] : null;
                    $patient_status = isset($tmp[1]) && isset( config('patient-statuses')[ $tmp[1] ] ) ? $tmp[1] : null;

                    if( $type=='patient' ) {
                        $query = $query->orWhere(function ($subquery) {
                            $subquery->where('is_dentist', 0)
                            ->orWhereNull('is_dentist');
                        });
                    } else if( $type=='clinic' ) {
                        $query = $query->orWhere(function ($subquery) {
                            $subquery->where('is_dentist', 1)
                            ->where('is_clinic', 1);
                        });
                    } else if( $type=='dentist_clinic' ) {

                        $query = $query->orWhere(function ($subquery) {
                            $subquery->where('is_dentist', 1);
                        });
                    } else if( $type=='dentist' ) {
                        $query = $query->orWhere(function ($subquery) {
                            $subquery->where('is_dentist', 1)->where(function ($subsubquery) {
                                $subsubquery->where('is_clinic', 0)
                                ->orWhereNull('is_clinic');
                            });
                        });
                    }

                    if( $status ) {
                        $query = $query->where('status', $status);
                    } else if($patient_status) {

                        $query = $query->where('patient_status', $patient_status);

                        if($patient_status == 'deleted') {
                            Input::merge(['search-status' => 'all']);
                        }
                    } else if(!empty($tmp[1]) && $tmp[1] == 'partners') {

                        $query = $query->where('is_partner', 1);
                    }

                }
            });
        }

        if(!empty(request('search-status'))) {
            $status = request('search-status');
            if( $status=='all' ) {
                $users = $users->withTrashed();
            }
            if( $status=='deleted' ) {
                $users = $users->onlyTrashed();
            }
            if( $status=='self_deleted' ) {
                $users = $users->whereNotNull('self_deleted')->withTrashed();
            }
        } else {
            $users = $users->whereNull('self_deleted');
        }

        if(!empty(request('survey-count'))) {
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

            unset($getArrNoSort['survey-count']);
        }

        if(!empty(request('exclude-countries'))) {
            $users = $users->whereNotIn('country_id', request('exclude-countries') );
        }

        if(!empty(request('exclude-permaban'))) {
            $users = $users->doesntHave('permanentBans' );
        }

        if(!empty(request('exclude-unsubscribed'))) {
            $users = $users->whereNull('unsubscribe');
        }

        if(!empty(request('fb-tab'))) {
            $users = $users->has('dentist_fb_page');
        }

        if( null !== request('results-number')) {
            $results = trim(request('results-number'));
        } else {
            $results = 50;
        }

        $total_count = $users->count();
        if( request()->input('export') ) {
            ini_set("memory_limit",-1);
            $users = $users->select(['title', 'name', 'email', 'platform'])->get();
        } else if(request()->input('export-fb')) {
            ini_set("memory_limit",-1);
            $users = $users->select(['id', 'name', 'email', 'country_id', 'phone', 'zip', 'city_name', 'state_name', 'birthyear', 'gender', 'is_dentist'])->get();
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

            $export = new Export($flist);
            $file_to_export = Excel::download($export, 'users.xls');
            ob_end_clean();
            return $file_to_export;

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

                if ($u->surveys_rewards->isNotEmpty()) {
                    $info['surveys'] = $u->surveys_rewards->count() - 1;
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

        if(request('search-platform') == 'trp') {
            $table_fields['ratings'] = array('template' => 'admin.parts.table-users-ratings');
            $table_fields['reviews'] = array('template' => 'admin.parts.table-users-reviews', 'label' => 'Reviews');
        }

        if(request('search-platform') == 'vox') {
            $table_fields['surveys'] = array('template' => 'admin.parts.table-users-surveys', 'label' => 'Surveys','order' => true, 'orderKey' => 'survey-count');
        }

        $table_fields['created_at'] = array('format' => 'datetime', 'label' => 'Registered');
        $table_fields['last_login'] = array('template' => 'admin.parts.table-users-last-login', 'label' => 'Last login');
        $table_fields['delete'] = array('template' => 'admin.parts.table-users-delete');

        // dd($getArrNoSort);
        $current_url = url('cms/users/users/').'?'.http_build_query($getArrNoSort);

        return $this->showView('users', array(
            'users' => $users,
            'total_count' => $total_count,
            'user_types' => $user_types,
            'user_statuses' => $user_statuses,
            'search_register_from' => request('search-register-from'),
            'search_register_to' => request('search-register-to'),
            'search_email' => request('search-email'),
            'search_phone' => request('search-phone'),
            'search_name' => request('search-name'),
            'search_id' => request('search-id'),
            'search_address' => request('search-address'),
            'search_tx' => request('search-tx'),
            'results_number' => request('results-number'),
            'search_phone' => request('search-phone'),
            'search_ip_address' => request('search-ip-address'),
            'search_type' => request('search-type'),
            'search_status' => request('search-status'),
            'search_platform' => request('search-platform'),
            'registered_platform' => request('registered-platform'),
            'search_country' => request('search-country'),
            'search_review' => request('search-review'),
            'search_surveys_taken' => request('search-surveys-taken'),
            'search_login_after' => request('search-login-after'),
            'search_login_number' => request('search-login-number'),
            'search_dentist_claims' => request('search-dentist-claims'),
            'exclude_countries' => request('exclude-countries'),
            'exclude_permaban' => request('exclude-permaban'),
            'exclude_unsubscribed' => request('exclude-unsubscribed'),
            'civic_kyc_hash' => request('civic-kyc-hash'),
            'fb_tab' => request('fb-tab'),
            'user_platforms' => $user_platforms,
            'countries' => Country::with('translations')->get(),
            'table_fields' =>  $table_fields,
            'current_url' => $current_url,
        ));
    }

    public function delete( $id ) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'support'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $item = User::find($id);

        if(!empty($item) && $item->id != 3) {

            if (!empty(Request::input('deleted_reason'))) {
                $action = new UserAction;
                $action->user_id = $item->id;
                $action->action = 'deleted';
                $action->reason = Request::input('deleted_reason');
                $action->actioned_at = Carbon::now();
                $action->save();

                $item->deleteActions();
                User::destroy( $id );

                $this->request->session()->flash('success-message', trans('admin.page.'.$this->current_page.'.deleted') );
                return redirect(!empty(Request::server('HTTP_REFERER')) ? Request::server('HTTP_REFERER') : 'cms/users/users/');

            } else {
                $this->request->session()->flash('error-message', "You have to write a reason why this user has to be deleted" );
                return redirect('cms/users/users/edit/'.$item->id);
            }
        } else {
            return redirect('cms/users/users/');
        }
    }

    public function deleteDatabase( $id ) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $item = User::withTrashed()->find($id);

        if($item->logins->isNotEmpty()) {
            foreach ($item->logins as $login) {
                $login->forceDelete();
            }
        }

        if($item->allBanAppeals->isNotEmpty()) {
            foreach ($item->allBanAppeals as $ba) {
                $ba->forceDelete();
            }
        }

        $id = $item->id;
        $teams = UserTeam::where(function($query) use ($id) {
            $query->where( 'dentist_id', $id)->orWhere('user_id', $id);
        })->get();

        if (!empty($teams)) {
            foreach ($teams as $team) {
                $dent_id = $team->dentist_id;
                $team->delete();

                $dent = User::find($dent_id);
                if(!empty($dent) && $dent->is_clinic) {

                    if ($dent->status == 'added_by_clinic_new') {
                        $dent->status = 'added_by_clinic_rejected';
                        $dent->save();
                    } else if($dent->status == 'dentist_no_email') {
                        $action = new UserAction;
                        $action->user_id = $dent->id;
                        $action->action = 'deleted';
                        $action->reason = 'his dentist was deleted/rejected';
                        $action->actioned_at = Carbon::now();
                        $action->save();

                        $dent->deleteActions();
                        self::destroy( $dent->id );
                    }
                }
            }
        }

        $user_invites = UserInvite::where(function($query) use ($id) {
            $query->where( 'user_id', $id)->orWhere('invited_id', $id);
        })->get();

        if (!empty($user_invites)) {
           foreach ($user_invites as $user_invite) {
               $user_invite->forceDelete();
           }
        }

        if($item->claims->isNotEmpty()) {
            foreach ($item->claims as $c) {
                $c->forceDelete();
            }
        }

        $transactions = DcnTransaction::where('user_id', $item->id)->whereIn('status', ['new', 'failed', 'first', 'not_sent'])->get();

        if ($transactions->isNotEmpty()) {
            foreach ($transactions as $trans) {
                $trans->forceDelete();
            }
        }

        $item->forceDelete();

        $this->request->session()->flash('success-message', 'Deleted forever' );
        return redirect('cms/users');
    }

    public function massdelete(  ) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'support'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        if( Request::input('ids') ) {
            $delusers = User::whereIn('id', Request::input('ids'))->get();
            foreach ($delusers as $du) {
                if($du->id != 3) {

                    $action = new UserAction;
                    $action->user_id = $du->id;
                    $action->action = 'deleted';
                    $action->reason = Request::input('mass-delete-reasons');
                    $action->actioned_at = Carbon::now();
                    $action->save();

                    $du->deleteActions();
                    $du->delete();
                }
            }
        }

        $this->request->session()->flash('success-message', 'All selected users are deleted' );
        return redirect(url()->previous());
    }

    public function massReject(  ) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'support'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        if( Request::input('ids') ) {
            $rejectusers = User::whereIn('id', Request::input('ids'))->get();
            foreach ($rejectusers as $ru) {
                if($ru->is_dentist) {

                    $ru->status = 'rejected';
                    $ru->save();
                    $ru->sendTemplate(14);
                }
            }
        }

        $this->request->session()->flash('success-message', 'All selected dentists are rejected' );
        return redirect(url()->previous());
    }

    public function delete_avatar( $id ) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'support'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $item = User::withTrashed()->find($id);

        if(!empty($item)) {
            $item->hasimage = false;
            $item->save();
        }

        $this->request->session()->flash('success-message', trans('admin.page.'.$this->current_page.'.avatar-deleted') );
        return redirect('cms/users/users/edit/'.$id);
    }

    public function add_avatar( $id ) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'support'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $user = User::find($id);

        if( !empty($user) && Request::file('image') && Request::file('image')->isValid() ) {
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

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'support'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $item = User::withTrashed()->find($id);

        if(!empty($item)) {
            if(!empty($item->photos[$position])) {
                $item->photos[$position]->delete();
            }
        }

        $this->request->session()->flash('success-message', trans('admin.page.'.$this->current_page.'.photo-deleted') );
        return redirect('cms/users/users/edit/'.$id);
    }

    public function delete_ban( $id, $ban_id ) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'support'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $item = User::withTrashed()->find($id);
        $ban = UserBan::find($ban_id);

        if(!empty($ban) && !empty($item) && $ban->user_id == $item->id) {
            $sg = new \SendGrid(env('SENDGRID_PASSWORD'));

            $item->sendgridSubscribeToGroup($ban->domain);

            UserBan::destroy( $ban_id );
        }

        $this->request->session()->flash('success-message', trans('admin.page.'.$this->current_page.'.ban-deleted') );
        return redirect('cms/users/users/edit/'.$id);
    }

    public function restore_ban( $id, $ban_id ) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'support'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $item = User::withTrashed()->find($id);
        $ban = UserBan::withTrashed()->find($ban_id);

        if(!empty($ban) && !empty($item) && $ban->user_id == $item->id) {

            $sg = new \SendGrid(env('SENDGRID_PASSWORD'));
            $request_body = new \stdClass();
            $request_body->recipient_emails = [$item->email];

            if($ban->domain == 'trp') {
                $group_id = config('email-preferences')['product_news']['trp']['sendgrid_group_id'];
            } else {
                $group_id = config('email-preferences')['product_news']['vox']['sendgrid_group_id'];
            }
        
            $response = $sg->client->asm()->groups()->_($group_id)->suppressions()->post($request_body);

            $ban->restore();
        }

        $this->request->session()->flash('success-message', 'Ban restored!' );
        return redirect('cms/users/users/edit/'.$id);
    }

    public function delete_vox( $id, $reward_id ) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'support'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

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
        return redirect('cms/users/users/edit/'.$id);
    }

    public function delete_unfinished( $id, $vox_id ) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'support'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $item = User::withTrashed()->find($id);
        
        if(!empty($item)) {
            VoxAnswer::where([
                ['user_id', $item->id],
                ['vox_id', $vox_id],
            ])
            ->delete();
        }

        $this->request->session()->flash('success-message', 'Survey answers deleted!' );
        return redirect('cms/users/users/edit/'.$id);
    }

    public function delete_review( $review_id ) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'support'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $item = Review::find($review_id);
        
        if(!empty($item)) {
            $uid = $item->user_id;
            $patient = User::where('id', $uid)->withTrashed()->first();

            ReviewAnswer::where([
                ['review_id', $item->id],
            ])
            ->delete();

            $dentist = null;
            $clinic = null;

            if($item->dentist_id) {
                $dentist = User::find($item->dentist_id);
            }

            if($item->clinic_id) {
                $clinic = User::find($item->clinic_id);
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

            if( !empty($clinic) ) {
                $clinic->recalculateRating();
                $substitutions = [
                    'spam_author_name' => $patient->name,
                ];
                
                $clinic->sendGridTemplate(87, $substitutions, 'trp');
            }

            $ban = new UserBan;
            $ban->user_id = $patient->id;
            $ban->domain = 'trp';
            $ban->type = 'spam-review';
            $ban->save();

            $notifications = $patient->website_notifications;

            if(!empty($notifications)) {

                if (($key = array_search('trp', $notifications)) !== false) {
                    unset($notifications[$key]);
                }

                $patient->website_notifications = $notifications;
                $patient->save();
            }


            $sg = new \SendGrid(env('SENDGRID_PASSWORD'));
            $request_body = new \stdClass();
            $request_body->recipient_emails = [$patient->email];
            
            $trp_group_id = config('email-preferences')['product_news']['trp']['sendgrid_group_id'];
            $response = $sg->client->asm()->groups()->_($trp_group_id)->suppressions()->post($request_body);

            $patient->sendGridTemplate(86, null, 'trp');

            $this->request->session()->flash('success-message', trans('admin.page.'.$this->current_page.'.review-deleted') );
            return redirect(!empty(Request::server('HTTP_REFERER')) ? Request::server('HTTP_REFERER') : ('cms/users/users/edit/'.(!empty($item->dentist_id) ? $item->dentist_id : $item->clinic_id )));
        }

        return redirect('cms/users');
    }

    public function restore( $id ) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'support'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $item = User::onlyTrashed()->find($id);

        if(!empty($item)) {

            if (!empty(Request::input('restored_reason'))) {

                $action = new UserAction;
                $action->user_id = $item->id;
                $action->action = 'restored';
                $action->reason = Request::input('restored_reason');
                $action->actioned_at = Carbon::now();
                $action->save();

                $item->restoreActions();
                $item->restore();

                $this->request->session()->flash('success-message', trans('admin.page.'.$this->current_page.'.restored') );
                return redirect(!empty(Request::server('HTTP_REFERER')) ? Request::server('HTTP_REFERER') : 'cms/users/users');

            } else {
                $this->request->session()->flash('error-message', "You have to write a reason why this user has to be restored" );
                return redirect('cms/users/users/edit/'.$item->id);
            }            
        }

        return redirect('cms/users/users/');
    }

    public function restore_self_deleted( $id ) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'support'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $item = User::withTrashed()->find($id);

        if(!empty($item)) {

            if (!empty(Request::input('restored_reason'))) {
                $action = new UserAction;
                $action->user_id = $item->id;
                $action->action = 'restored_self_deleted';
                $action->reason = Request::input('restored_reason');
                $action->actioned_at = Carbon::now();
                $action->save();

                $item->self_deleted = null;
                $item->save();

                $this->request->session()->flash('success-message', trans('admin.page.'.$this->current_page.'.restored') );
                return redirect(!empty(Request::server('HTTP_REFERER')) ? Request::server('HTTP_REFERER') : 'cms/users/users');

            } else {
                $this->request->session()->flash('error-message', "You have to write a reason why this user has to be restored" );
                return redirect('cms/users/users/edit/'.$item->id);
            }            
        }

        return redirect('cms/users/users/');
    }

    public function loginas( $id, $platform=null ) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'support'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

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

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'support'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $item = User::withTrashed()->find($id);

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

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'support'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        if(Request::isMethod('post')) {

            $validator = Validator::make(Request::all(), [
                'type' => array('required', 'in:dentist,clinic'),
                'name' => array('required', 'min:3'),
                'email' => array('required', 'email', 'unique:users,email'),
                'country_id' => array('required', 'exists:countries,id'),
                'address' =>  array('required', 'string'),
            ]);

            if ($validator->fails()) {;

                return redirect(url('cms/users/users/add'))
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
                $newuser->website_notifications = ['dentacoin','trp','vox','assurance','jaws'];
                $newuser->is_dentist = 1;
                $newuser->is_clinic = Request::input('type')=='clinic' ? 1 : 0;

                $newuser->save();

                if( Request::input('avatar') ) {
                    $img = Image::make( GeneralHelper::decode_base64_image(Request::input('avatar')) )->orientate();
                    $newuser->addImage($img);
                }

                $newuser->slug = $newuser->makeSlug();
                $newuser->save();

                if( Request::file('image') && Request::file('image')->isValid() ) {
                    $img = Image::make( Input::file('image') )->orientate();
                    $newuser->addImage($img);
                }

                $newuser->generateSocialCover();

                $substitutions = [
                    "image_unclaimed_profile" => $newuser->getSocialCover(),
                    "invitation_link" => getLangUrl( 'dentist/'.$newuser->slug.'/claim/'.$newuser->id , null, 'https://reviews.dentacoin.com/').'?'. http_build_query(['popup'=>'claim-popup']),
                ];

                $newuser->sendGridTemplate(81, $substitutions, 'trp');

                Request::session()->flash('success-message', 'Dentist Added');
                return redirect('cms/users/users/edit/'.$newuser->id);
            }
        }

        return $this->showView('users-add', array(
            'fields' => $this->fields,
            'countries' => Country::with('translations')->get(),
        ));
    }

    public function edit( $id ) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'support']) && Auth::guard('admin')->user()->user_id != $id) {
            return redirect('cms/users/users/edit/'.Auth::guard('admin')->user()->user_id);
        }

        $item = User::withTrashed()->find($id);

        if(!empty($item)) {

            $emails = Email::where('user_id', $id )->where( function($query) {
                $query->where('sent', 1)
                ->orWhere('invalid_email', 1);
            })->orderBy('created_at', 'DESC')->get();

            if($item->is_dentist) {
                $this->fields['password'] = [
                    'type' => 'password',
                ];
            } else {
                unset( $this->fields['status'] );
            }

            if(Request::isMethod('post')) {

                $user_history = new UserHistory;
                $user_history->user_id = $item->id;
                $user_history->admin_id = $this->user->id;

                $dont_delete = false;
                $other_fields = '';

                foreach ($this->fields as $key => $value) {
                    if(!in_array($key, ['slug', 'type', 'password'])) {

                        if($this->request->input($key) != $item->$key) {
                            $dont_delete = true;

                            if(in_array($key, UserHistory::$fields)) {
                                $user_history->$key = $item->$key;
                            } else {
                                $other_fields.= 'old '.$key.' = '.$item->$key.'<br/>';
                            }
                        }
                    }
                }
                
                if($dont_delete) {
                    $user_history->history = $other_fields;
                    $user_history->save();
                }

                if($item->status == 'clinic_branch' && !empty($this->request->input('dcn_address'))) {

                    if(mb_strlen($this->request->input('dcn_address'))!=42) {
                        Request::session()->flash('error-message', 'Please enter a valid DCN address.');
                        return redirect('cms/users/users/edit/'.$item->id);
                    }
        
                    $existing_address = WalletAddress::where('user_id', $item->id)->first();
        
                    if (!empty($existing_address)) {
                        $existing_address->dcn_address = $this->request->input('dcn_address');
                        $existing_address->selected_wallet_address = 1;
                        $existing_address->save();
                    } else {
                        $new_address = new WalletAddress;
                        $new_address->user_id = $item->id;
                        $new_address->dcn_address = $this->request->input('dcn_address');
                        $new_address->selected_wallet_address = 1;
                        $new_address->save();
                    }
                }

                if( Request::input('avatar') ) {
                    $img = Image::make( GeneralHelper::decode_base64_image(Request::input('avatar')) )->orientate();
                    $item->addImage($img);
                }

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
                                    return redirect('cms/users/users/edit/'.$item->id);
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
                        } else if($key=='email') {
                            if (empty($this->request->input($key))) {
                                $item->$key = $this->request->input($key);
                            } else {
                                
                                $existing = User::withTrashed()->where('id', '!=', $item->id)->where($key, 'like', $this->request->input($key))->first();

                                if (!empty($existing)) {
                                    if($existing->status == 'added_by_dentist_new' || $existing->status == 'added_new' || $existing->status == 'added_by_clinic_new') {

                                        $existing->email = $existing->email.'d';
                                        $existing->status = 'duplicated_email';
                                        $existing->save();

                                        if(empty($existing->deleted_at) && empty($existing->self_deleted) ) {

                                            $action = new UserAction;
                                            $action->user_id = $existing->id;
                                            $action->action = 'deleted';
                                            $action->reason = 'duplicated email';
                                            $action->actioned_at = Carbon::now();
                                            $action->save();

                                            $existing->deleteActions();
                                            User::destroy( $existing->id );
                                        }


                                        $item->$key = $this->request->input($key);
                                    } else {
                                        Request::session()->flash('error-message', 'This '.$key.' is already used by another user - ID '.$existing->id);
                                        return redirect('cms/users/users/edit/'.$item->id);
                                    }
                                    
                                } else {
                                    if ($item->$key != $this->request->input($key)) {
                                        
                                        $oldemail = new OldEmail;
                                        $oldemail->user_id = $item->id;
                                        $oldemail->email = $item->email;
                                        $oldemail->save();
                                    }

                                    $item->$key = $this->request->input($key);
                                }
                            }
                        } else if($key=='status') {
                            if( $this->request->input($key) && $item->$key!=$this->request->input($key) ) {

                                if ($this->request->input($key)=='added_approved') {
                                    $patient = User::find($item->invited_by);

                                    if (empty($item->slug)) {
                                        $item->slug = $item->makeSlug();
                                        $item->save();
                                    }

                                    $substitutions = [
                                        "image_unclaimed_profile" => $item->getSocialCover(),
                                        "invitation_link" => getLangUrl( 'dentist/'.$item->slug.'/claim/'.$item->id , null, 'https://reviews.dentacoin.com/').'?'. http_build_query(['popup'=>'claim-popup']),
                                    ];

                                    if(!empty($item->email)) {
                                        $item->sendGridTemplate(43, $substitutions, 'trp');
                                    }
                                    
                                    if (!empty($patient)) {

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
                                            $reward->os = in_array('name', $dd->getOs()) ? $dd->getOs()['name'] : '';
                                        }
                                        $reward->save();

                                        $substitutions = [
                                            'added_dentist_name' => $item->getNames(),
                                            'trp_added_dentist_prf' => $item->getLink().'?dcn-gateway-type=patient-login',
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

                                    $item->sendGridTemplate($platformMails[$item->platform], null, $item->platform);
                                    $item->verified_on = Carbon::now();
                                    $item->save();

                                    $item->product_news = ['dentacoin', 'trp'];
                                    $item->save();

                                    //add to dcn sendgrid list
                                    $sg = new \SendGrid(env('SENDGRID_PASSWORD'));

                                    $user_info = new \stdClass();
                                    $user_info->email = $item->email;
                                    $user_info->title = $item->title ? config('titles')[$item->title] : '';
                                    $user_info->first_name = explode(' ', $item->name)[0];
                                    $user_info->last_name = isset(explode(' ', $item->name)[1]) ? explode(' ', $item->name)[1] : '';
                                    $user_info->type = 'dentist';
                                    $user_info->partner = $item->is_partner ? 'yes' : 'no';

                                    $request_body = [
                                        $user_info
                                    ];

                                    $response = $sg->client->contactdb()->recipients()->post($request_body);
                                    $recipient_id = isset(json_decode($response->body())->persisted_recipients[0]) ? json_decode($response->body())->persisted_recipients[0] : null;

                                    //add to list
                                    if($recipient_id) {

                                        $sg = new \SendGrid(env('SENDGRID_PASSWORD'));
                                        $list_id = config('email-preferences')['product_news']['dentacoin']['sendgrid_list_id'];
                                        $response = $sg->client->contactdb()->lists()->_($list_id)->recipients()->_($recipient_id)->post();
                                    }

                                    if($item->is_clinic && $item->team_new_clinic->isNotEmpty()) {
                                        foreach ($item->team_new_clinic as $tnc) {
                                            $tnc->approved = true;
                                            $tnc->new_clinic = false;
                                            $tnc->save();

                                            $dent = User::find($tnc->dentist_id);

                                            if( !empty($dent)) {

                                                if ($dent->status == 'added_by_clinic_new') {
                                                    $dent->status = 'added_by_clinic_unclaimed';
                                                    $dent->slug = $dent->makeSlug();
                                                    $dent->save();

                                                    $dent->sendGridTemplate( 92 , [
                                                        'clinic_name' => $item->getNames(),
                                                        "invitation_link" => getLangUrl( 'dentist/'.$dent->slug.'/claim/'.$dent->id).'?'. http_build_query(['popup'=>'claim-popup']).'&without-info=true',
                                                    ], 'trp');
                                                } else {
                                                    $dent->sendTemplate(33, [
                                                        'clinic-name' => $item->getNames(),
                                                        'clinic-link' => $item->getLink()
                                                    ], 'trp');
                                                }
                                            }
                                        }
                                    }

                                    if($item->is_dentist && !$item->is_clinic && $item->my_workplace_unapproved->isNotEmpty() ) {
                                        foreach ($item->my_workplace_unapproved as $wp) {
                                            if($wp->clinic->status == 'approved' || $wp->clinic->status == 'added_by_dentist_claimed') {
                                                    
                                                $wp->clinic->sendTemplate(34, [
                                                    'dentist-name' => $item->getNames()
                                                ], 'trp');
                                            }
                                        }
                                    }

                                    if($item->invited_by && !empty($item->invitor) && !$item->invitor->is_dentist) {

                                        $inv = UserInvite::where('user_id', $item->invited_by)
                                        ->where('invited_id', $item->id)
                                        ->whereNull('rewarded')
                                        ->first();

                                        if(!empty($inv)) {

                                            $reward = new DcnReward();
                                            $reward->user_id = $item->invitor;
                                            $reward->platform = 'trp';
                                            $reward->reward = Reward::getReward('reward_invite');
                                            $reward->type = 'invitation';
                                            $reward->reference_id = $inv->id;

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
                                                $reward->os = in_array('name', $dd->getOs()) ? $dd->getOs()['name'] : '';
                                            }

                                            $reward->save();

                                            $inv->rewarded = true;
                                            $inv->save();
                                        }
                                    }

                                    $item->generateSocialCover();

                                } else if( $this->request->input($key)=='added_by_dentist_unclaimed' ) {
                                    $item->status = 'added_by_dentist_unclaimed';
                                    $item->slug = $item->makeSlug();
                                    $item->save();

                                    $dent_name = null;
                                    if(!empty($item->invited_by)) {
                                        $dent = User::find($item->invited_by);

                                        if(!empty($dent)) {
                                            $dent_name = $dent->getNames();
                                        }
                                    }

                                    $item->sendGridTemplate( 93 , [
                                        'dentist_name' => !empty($dent_name) ? $dent_name : 'Name',
                                        "invitation_link" => getLangUrl( 'dentist/'.$item->slug.'/claim/'.$item->id).'?'. http_build_query(['popup'=>'claim-popup']).'&without-info=true',
                                    ], 'trp');

                                } else if( $this->request->input($key)=='test' ) {
                                    $item->status = 'test';
                                    $item->slug = $item->makeSlug();
                                    $item->save();

                                } else if( $this->request->input($key)=='pending' ) {
                                    $item->sendTemplate(40);
                                } else if( $this->request->input($key)=='rejected' ) {
                                    $item->sendTemplate(14);
                                }
                            }

                            if(($this->request->input($key)=='added_approved' || $this->request->input($key)=='added_by_dentist_unclaimed' || $this->request->input($key)=='added_by_clinic_unclaimed') && !$item->hasimage_social) {
                                $item->generateSocialCover();
                            }
                            $item->$key = $this->request->input($key);
                        } else if($key=='vip_access') {
                            
                            if($item->$key!=$this->request->input($key)) {

                                if($this->request->input($key) == 1) {

                                    $item->sendGridTemplate(118, null, 'vox');
                                } else {

                                    $item->sendGridTemplate(119, null, 'vox');
                                }
                                $item->$key = $this->request->input($key);
                            }

                        } else if($value['type']=='password') {
                            if( $this->request->input($key) ) {
                                $item->$key = bcrypt( $this->request->input($key) );                                
                            }
                        } else if( $key == 'patient_status') {

                            if($item->$key!=$this->request->input($key)) {
                                if($item->$key == 'deleted' && ($this->request->input($key) == 'suspicious_badip' || $this->request->input($key) == 'suspicious_admin')) {
                                    $item->sendTemplate(109, null, 'dentacoin');
                                } else if($item->$key == 'deleted' && ($this->request->input($key) == 'new_verified' || $this->request->input($key) == 'new_not_verified')) {
                                    $item->sendTemplate(111, null, 'dentacoin');
                                } else if($this->request->input($key) == 'suspicious_badip' || $this->request->input($key) == 'suspicious_admin') {
                                    if($this->request->input($key) == 'suspicious_admin' && !empty($this->request->input('suspicious-reason'))) {
                                        $action = new UserAction;
                                        $action->user_id = $item->id;
                                        $action->action = 'suspicious_admin';
                                        $action->reason = $this->request->input('suspicious-reason');
                                        $action->actioned_at = Carbon::now();
                                        $action->save();
                                    }

                                    $item->sendTemplate(110, null, 'dentacoin');
                                    $item->removeTokens();
                                    $item->logoutActions();
                                }
                            }

                            $item->$key = $this->request->input($key);

                        } else if($value['type']=='datepicker') {
                           $item->$key = $this->request->input($key) ? new Carbon( $this->request->input($key) ) : null;
                        } else if($key=='unsubscribe') {

                            $on_invites = UserInvite::where('invited_id', $item->id)->get();

                            if (!empty($on_invites)) {
                                foreach ($on_invites as $inv) {
                                    if (!empty($this->request->input($key))) {
                                        $inv->unsubscribed = true;
                                    } else {
                                        $inv->unsubscribed = false;
                                    }
                                    $inv->save();
                                }
                            }

                            $unclaimed_dentist = UnclaimedDentist::find($item->id);

                            if(!empty($unclaimed_dentist)) {
                                if (!empty($this->request->input($key))) {
                                    $unclaimed_dentist->unsubscribed = true;
                                } else {
                                    $unclaimed_dentist->unsubscribed = false;
                                }
                                $unclaimed_dentist->save();
                            }

                            $item->$key = $this->request->input($key);
                        } else {
                           $item->$key = $this->request->input($key);                            
                        }
                    }
                }
                $item->hasimage_social = false;

                if(!empty(request('badge-months')) && !empty(request('badge-year'))) {
                    $help_array = [];
                    foreach(request('badge-year') as $i => $trg) {
                        if(!empty($trg)) {
                            $help_array[] = $trg.':'.request('badge-months')[$i];
                        }
                    }
                    $item->top_dentist_month = implode(';', $help_array);
                    
                } else {
                    $item->top_dentist_month = null;
                }

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

                if(($item->status=='rejected' || $item->status=='added_rejected' || $item->status=='added_by_dentist_rejected' || $item->status=='added_by_clinic_rejected' ) && empty($item->deleted_at)) {
                    $action = new UserAction;
                    $action->user_id = $item->id;
                    $action->action = 'deleted';
                    $action->reason = 'Rejected by admin';
                    $action->actioned_at = Carbon::now();
                    $action->save();

                    $item->deleteActions();
                    User::destroy( $item->id );
                }

                Request::session()->flash('success-message', trans('admin.page.'.$this->current_page.'.updated'));
                return redirect('cms/users/users/edit/'.$item->id);
            }

            $all_questions_answerd = VoxAnswer::where('user_id', $id)
            ->groupBy('vox_id')
            ->orderBy('created_at')
            ->get();

            $unanswerd_questions = array_diff($all_questions_answerd->pluck('vox_id')->toArray(), $item->filledVoxes() );
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

                $vcc = VoxCrossCheck::where('user_id', $item->id)->where('question_id', $k)->first();
                $old_an = !empty($vcc) ? $vcc->old_answer : '';
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

                $habits_last = VoxCrossCheck::where('user_id', $item->id)->where('question_id', $k)->orderBy('id', 'desc')->first();
                $habits_count = VoxCrossCheck::where('user_id', $item->id)->where('question_id', $k)->count();

                $habits_tests[] = [
                    'question' => $v['label'],
                    'old_answer' => $old_an || $old_an === 0 ? $old_an : (!empty($item->$k) || $item->$k === 0 ? $v['values'][$item->$k] : ''),
                    'answer' => $old_an && !empty($item->$k) ? $v['values'][$item->$k] : '',
                    'last_updated' => !empty($habits_last) ? $habits_last->created_at : '',
                    'updates_count' => $habits_count ? $habits_count : '',
                ];
            }

            $duplicated_mails = collect();
            if( !empty($item->email)) {
                $duplicated_mails = User::where('id', '!=', $item->id)->where('email', 'LIKE', $item->email)->withTrashed()->get();
            }

            $duplicated_names = collect();
            if( !empty($item->name)) {
                $duplicated_names = User::where('id', '!=', $item->id)->where('name', 'LIKE', $item->name)->withTrashed()->get();
            }

            return $this->showView('users-form', array(
                'duplicated_names' => $duplicated_names,
                'duplicated_mails' => $duplicated_mails,
                'habits_test_ans' => $habits_test_ans,
                'item' => $item,
                'ban_types' => $this->ban_types,
                'categories' => $this->categories,
                'fields' => $this->fields,
                'unfinished' => $unfinished,
                'emails' => $emails,
                'habits_tests' => $habits_tests,
                'countries' => Country::with('translations')->get(),
                'dev_domain' => Request::getHost() == 'urgent.dentavox.dentacoin.com' || Request::getHost() == 'urgent.reviews.dentacoin.com' ? true : false,
            ));
        } else {
            return redirect('cms/users/users/');
        }
    }

    public function import() {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'support'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        if(Request::isMethod('post')) {

            if(Input::file('file')) {

                if (Input::file('file')->getMimeType() != 'application/vnd.ms-excel') {
                    $this->request->session()->flash('error-message', 'File format not accepted. Upload .xls');
                    return redirect('cms/users/users/import');
                }

                global $results, $not_imported;

                $newName = '/tmp/'.str_replace(' ', '-', Input::file('file')->getClientOriginalName());
                copy( Input::file('file')->path(), $newName );

                $results = Excel::toArray(new Import, $newName );

                if(!empty($results)) {

                    unset($results[0][0]);

                    $not_imported = [];
                    if(!empty($results)) {

                        foreach ($results[0] as $k => $row) {

                            //dd($results, $k, $row);
                            if (!empty($row[2]) && !empty($row[0]) && filter_var($row[2], FILTER_VALIDATE_EMAIL)) {
                                $existing_user = User::where('email', 'like', $row[2] )->first();
                                $existing_place = !empty($row[10]) ? User::where('place_id', $row[10] )->first() : null;

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

                                    $newuser->slug = $newuser->makeSlug();
                                    $newuser->save();

                                    if (!empty($row[9])) {
                                        $img = Image::make( $row[9] )->orientate();
                                        $newuser->addImage($img);
                                    }

                                    $newuser->generateSocialCover();

                                    $substitutions = [
                                        "invitation_link" => getLangUrl( 'dentist/'.$newuser->slug.'/claim/'.$newuser->id , null, 'https://reviews.dentacoin.com/').'?'. http_build_query(['popup'=>'claim-popup']),
                                    ];

                                    $newuser->sendGridTemplate(81, $substitutions);
                                }
                            } else {
                                $not_imported[] = $row[0] ? $row[0] : ($row[2] ? $row[2] : 'without name and mail');
                            }
                        }
                    }
                }

                unlink($newName);

                if(!empty($not_imported)) {
                    $this->request->session()->flash('warning-message', 'Dentists were imported successfully. However, there were some invalid or missing dentist emails which were skipped - '.implode(',', $not_imported));
                } else {
                    $this->request->session()->flash('success-message', 'Dentists were imported successfully.');
                }
                
                return redirect('cms/users/users/import');

            } else {
                return redirect('cms/users/users/import');
            }
        }

        return $this->showView('users-import');
    }

    public function upload_temp($locale=null) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'support'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        if( Request::file('image') && Request::file('image')->isValid() ) {
            $img = Image::make( Input::file('image') )->orientate();
            list($thumb, $full, $name) = User::addTempImage($img);
            return Response::json(['success' => true, 'thumb' => $thumb, 'name' => $name ]);
        }
    }

    public function resetFirstGudedTour($id) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'support'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $item = UserGuidedTour::where('user_id', $id)->first();

        if(!empty($item)) {
            $item->first_login_trp = null;
            $item->save();
        }

        return redirect('cms/users/users/edit/'.$id);
    }

    public function convertToPatient($id) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'support'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $item = User::find($id);

        if(!empty($item)) {
            $item->status = 'approved';
            $item->is_dentist = 0;
            $item->is_clinic = 0;
            $item->is_partner = null;
            $item->featured = null;
            $item->top_dentist_month = null;
            $item->title = null;
            $item->slug = null;
            $item->patient_status = 'new_not_verified';

            $item->product_news = ['dentacoin', 'trp', 'vox'];
            $item->save();

            //create recipients
            $item->removeFromSendgridSubscribes();
            $sg = new \SendGrid(env('SENDGRID_PASSWORD'));

            $user_info = new \stdClass();
            $user_info->email = $item->email;
            $user_info->first_name = explode(' ', $item->name)[0];
            $user_info->last_name = isset(explode(' ', $item->name)[1]) ? explode(' ', $item->name)[1] : '';
            $user_info->type = 'patient';

            $request_body = [
                $user_info
            ];

            $response = $sg->client->contactdb()->recipients()->post($request_body);
            $recipient_id = isset(json_decode($response->body())->persisted_recipients) ? json_decode($response->body())->persisted_recipients[0] : null;

            //add to list
            if($recipient_id) {
                $sg = new \SendGrid(env('SENDGRID_PASSWORD'));
                $list_id = config('email-preferences')['product_news']['vox']['sendgrid_list_id'];
                $response = $sg->client->contactdb()->lists()->_($list_id)->recipients()->_($recipient_id)->post();
            }
        }

        return redirect('cms/users/users/edit/'.$id);
    }


    public function convertToDentist($id) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'support'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $item = User::find($id);

        if(!empty($item)) {
            $item->status = 'new';
            $item->is_dentist = 1;
            $item->user_patient_type = null;
            $item->is_clinic = 0;
            $item->patient_status = null;
            $item->product_news = null;
            $item->save();
            $item->removeFromSendgridSubscribes();

            $this->request->session()->flash('warning-message', 'Please, check the following fields "user type", "title"! Set a password and send it to the dentist!! Add country and address!');
        }

        return redirect('cms/users/users/edit/'.$id);
    }

    public function userInfo($id) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'support'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $user = User::withTrashed()->find($id);

        $duplicated_names = collect();
        if( !empty($user->name)) {
            $duplicated_names = User::where('id', '!=', $user->id)->where('name', 'LIKE', $user->name)->withTrashed()->get();
        }

        return $this->showView('user-info', [
            'user' => $user,
            'duplicated_names' => $duplicated_names
        ]);
    }

    public function usersStats() {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $user_types = User::groupBy('is_dentist')->select('is_dentist', DB::raw('count(*) as total'))->get();
        $dentist_partners = User::where('is_dentist', '1')->where('is_partner' , 1)->select('is_partner', DB::raw('count(*) as total'))->get();
        $user_genders = User::groupBy('gender')->select('gender', DB::raw('count(*) as total'))->get();
        $users_country = User::groupBy('country_id')->select('country_id', DB::raw('count(*) as total'))->orderBy('total', 'DESC')->get();

        return $this->showView('users-stats', array(
            'user_types' => $user_types,
            'dentist_partners' => $dentist_partners,
            'user_genders' => $user_genders,
            'users_country' => $users_country,
            'search_from' => request('search-from'),
            'search_to' => request('search-to'),
            'answered_questions' => VoxQuestionAnswered::get(),
        ));
    }

    public function registrations( ) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'support'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $weeks = DB::select("
            SELECT 
                CONCAT(YEAR(`created_at`), 'W', REPLACE(LPAD( WEEK( DATE_SUB(  `created_at` , INTERVAL 16 HOUR ) , 5 ) , 2 , '0' ) , '00', '52') ) AS  `week`,
                SUM( IF(  `is_dentist` AND `platform` = 'trp', 1, 0 ) ) AS `dentist`, 
                SUM( IF(  !`is_dentist` AND `platform` = 'trp' , 1, 0 ) ) AS `patient`, 
                SUM( IF(  `platform` = 'vox' , 1, 0 ) ) AS `vox`,
                SUM( IF(  `platform` = 'dentacare' , 1, 0 ) ) AS `dentacare`,
                SUM( IF(  `platform` = 'assurance' , 1, 0 ) ) AS `assurance`,
                SUM( IF(  `platform` = 'dentacoin' , 1, 0 ) ) AS `dentacoin`,
                SUM( IF(  `platform` = 'dentists' , 1, 0 ) ) AS `dentists`,
                SUM( IF(  `platform` = 'external' , 1, 0 ) ) AS `external`
            FROM  `users` 
            GROUP BY `week` 
            ORDER BY `id` DESC
        ");

        $table = [];
        // dd($weeks);
        foreach ($weeks as $w) {
            $s = date('d.m.Y', strtotime($w->week) - 86400*3 );
            $e = date('d.m.Y', strtotime($w->week) + 86400*4 );
            $table[] = [
                'week' => [
                    'value' => $s.'-'.$e,
                    'label' => 'Week',
                ],
                'dentists' => [
                    'value' => $w->dentist,
                    'label' => 'TRP Dentist',
                ],
                'patients' => [
                    'value' => $w->patient,
                    'label' => 'TRP Patient',
                ],
                'voxes' => [
                    'value' => $w->vox,
                    'label' => 'DentaVox',
                ],
                'dentacare' => [
                    'value' => $w->dentacare,
                    'label' => 'Dentacare',
                ],
                'assurance' => [
                    'value' => $w->assurance,
                    'label' => 'Assurance',
                ],
                'dentacoin' => [
                    'value' => $w->dentacoin,
                    'label' => 'Dentacoin',
                ],
                'dentists' => [
                    'value' => $w->dentists,
                    'label' => 'Dentists',
                ],
                'external' => [
                    'value' => $w->external,
                    'label' => 'External',
                ],
            ];
        }

        return $this->showView('users-reg-stats', array(
            'name' => 'registrations',
            'table' => $table,
        ));
    }

    public function incompleteRegs() {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

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

        $incompletes = IncompleteRegistration::orderBy('id', 'desc');

        if(!empty(request('search-name'))) {
            $incompletes = $incompletes->where('name', 'LIKE', '%'.trim(request('search-name')).'%');
        }
        if(!empty(request('search-phone'))) {
            $incompletes = $incompletes->where('phone', 'LIKE', '%'.trim(request('search-phone')).'%');
        }
        if(!empty(request('search-email'))) {
            $incompletes = $incompletes->where('email', 'LIKE', '%'.trim(request('search-email')).'%');
        }
        if(!empty(request('search-country'))) {
            $incompletes = $incompletes->where('country_id', request('search-country') );
        }
        if(!empty(request('search-website'))) {
            $incompletes = $incompletes->where('website', 'LIKE', '%'.trim(request('search-website')).'%');
        }
        if(!empty(request('search-platform'))) {
            $incompletes = $incompletes->where('platform', request('search-platform'));
        }  
        if(!empty(request('search-registered'))) {
            if(request('search-registered') == 'yes') {
                $incompletes = $incompletes->where('completed', 1);
            } else {
                $incompletes = $incompletes->whereNull('completed');
            }
        }

        $total_count = $incompletes->count();

        $page = max(1,intval(request('page')));
        
        $ppp = 25;
        $adjacents = 2;
        $total_pages = ceil($total_count/$ppp);

        //Here we generates the range of the page numbers which will display.
        if($total_pages <= (1+($adjacents * 2))) {
            $start = 1;
            $end   = $total_pages;
        } else {
            if(($page - $adjacents) > 1) { 
                if(($page + $adjacents) < $total_pages) { 
                    $start = ($page - $adjacents);            
                    $end   = ($page + $adjacents);         
                } else {             
                    $start = ($total_pages - (1+($adjacents*2)));  
                    $end   = $total_pages;               
                }
            } else {               
                $start = 1;                                
                $end   = (1+($adjacents * 2));             
            }
        }

        $incompletes = $incompletes->skip( ($page-1)*$ppp )->take($ppp)->get();

        //If you want to display all page links in the pagination then
        //uncomment the following two lines
        //and comment out the whole if condition just above it.
        /*$start = 1;
        $end = $total_pages;*/

        $pagination_link = '';

        foreach (Request::all() as $key => $value) {
            if($key != 'search' && $key != 'page') {
                $pagination_link .= '&'.$key.'='.($value === null ? '' : $value);
            }
        }

        return $this->showView('users-incomplete-regs', array(
            'items' => $incompletes,
            'total_count' => $total_count,
            'search_email' =>  request('search-email'),
            'search_phone' =>  request('search-phone'),
            'search_name' =>  request('search-name'),
            'search_country' =>  request('search-country'),
            'search_website' =>  request('search-website'),
            'search_platform' =>  request('search-platform'),
            'search_registered' =>  request('search-registered'),
            'countries' => Country::with('translations')->get(),
            'count' =>($page - 1)*$ppp ,
            'start' => $start,
            'end' => $end,
            'total_pages' => $total_pages,
            'page' => $page,
            'pagination_link' => $pagination_link,
        ));
    }

    public function leadMagnet() {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        if(request('export-lead')) {
            $leads_export = LeadMagnet::orderBy('id', 'desc')->get();
            $export = [];
            foreach ($leads_export as $u) {

                $third_answer = '';

                if (!empty(!empty($u->answers) && !empty(json_decode($u->answers, true)[3]))) {
                    foreach (json_decode($u->answers, true)[3] as $u_ans) {
                        if (!empty($u_ans)) {
                            if (empty($third_answer)) {
                                $third_answer = config('trp.lead_magnet')[3][$u_ans].'|';
                            } else {
                                $third_answer = $third_answer.config('trp.lead_magnet')[3][$u_ans].'|';
                            }
                        }
                    }
                }

                if (!empty($third_answer)) {
                    $third_answer = substr($third_answer, 0, -1);
                }

                $info = [
                    'recent_conversion_date' => $u->created_at,
                    'firstname' => $u->name,
                    'email' => $u->email,
                    'website' => $u->website,
                    'country' => Country::find($u->country_id)->name,
                    'priority' => !empty($u->answers) ? config('trp.lead_magnet')[1][json_decode($u->answers, true)[1]] : '',
                    'reviews_tool' => !empty($u->answers) ? config('trp.lead_magnet')[2][json_decode($u->answers, true)[2]] : '',
                    'ask_reviews' => $third_answer,
                    'frequently_reviews' => !empty($u->answers) && !empty(json_decode($u->answers, true)[4]) ? config('trp.lead_magnet')[4][json_decode($u->answers, true)[4]] : '',
                    'reviews_reply' => !empty($u->answers) ? config('trp.lead_magnet')[5][json_decode($u->answers, true)[5]] : '',
                    'reviews_score' => !empty($u->total) ? $u->total : ''
                ];

                //phone
                //country
                $export[] = $info;
            }

            $csv = [
                ['recent_conversion_date','firstname','email', 'website', 'country', 'priority', 'reviews_tool', 'ask_reviews', 'frequently_reviews', 'reviews_reply', 'reviews_score']
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


        $leads = LeadMagnet::orderBy('id', 'desc')->get();
        return $this->showView('users-lead-magnet', array(
            'leads' => $leads,
        ));
    }

    public function rewards() {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'support'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $rewards = DcnReward::orderBy('id', 'desc');

        if(!empty(request('search-user-id'))) {
            $rewards = $rewards->where('user_id', request('search-user-id'));
        }

        if(!empty(request('search-email'))) {
            $rewards = $rewards->whereHas('user', function($query) {
                $query->where('email', 'LIKE', '%'.trim(request('search-email')).'%');
            });
        }

        if(!empty(request('search-type'))) {
            $rewards = $rewards->where('type', request('search-type') );
        }

        $total_count = $rewards->count();
        $sum_price = $rewards->sum('reward');

        $page = max(1,intval(request('page')));
        
        $ppp = 25;
        $adjacents = 2;
        $total_pages = ceil($total_count/$ppp);

        //Here we generates the range of the page numbers which will display.
        if($total_pages <= (1+($adjacents * 2))) {
            $start = 1;
            $end   = $total_pages;
        } else {
            if(($page - $adjacents) > 1) { 
                if(($page + $adjacents) < $total_pages) { 
                    $start = ($page - $adjacents);            
                    $end   = ($page + $adjacents);         
                } else {             
                    $start = ($total_pages - (1+($adjacents*2)));  
                    $end   = $total_pages;               
                }
            } else {               
                $start = 1;                                
                $end   = (1+($adjacents * 2));             
            }
        }

        $rewards = $rewards->skip( ($page-1)*$ppp )->take($ppp)->get();

        //If you want to display all page links in the pagination then
        //uncomment the following two lines
        //and comment out the whole if condition just above it.
        /*$start = 1;
        $end = $total_pages;*/

        $pagination_link = '';

        foreach (Request::all() as $key => $value) {
            if($key != 'search' && $key != 'page') {
                $pagination_link .= '&'.$key.'='.($value === null ? '' : $value);
            }
        }

        return $this->showView('users-rewards', array(
            'items' => $rewards,
            'total_count' => $total_count,
            'sum_price' => number_format($sum_price),
            'search_user_id' =>  request('search-user-id'),
            'search_email' =>  request('search-email'),
            'search_type' =>  request('search-type'),
            'count' =>($page - 1)*$ppp ,
            'start' => $start,
            'end' => $end,
            'total_pages' => $total_pages,
            'page' => $page,
            'pagination_link' => $pagination_link,
        ));
    }
}