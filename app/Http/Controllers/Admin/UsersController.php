<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;

use Illuminate\Support\Facades\Input;
use Maatwebsite\Excel\Facades\Excel;

use App\Models\IncompleteRegistration;
use App\Models\VoxQuestionAnswered;
use App\Models\UserSurveyWarning;
use App\Models\DeletedUserEmails;
use App\Models\DentistBlogpost;
use App\Models\UserGuidedTour;
use App\Models\DcnTransaction;
use App\Models\VoxCrossCheck;
use App\Models\WalletAddress;
use App\Models\ReviewAnswer;
use App\Models\VoxAnswerOld;
use App\Models\UserHistory;
use App\Models\AdminAction;
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
use App\Services\TrpService;
use App\Helpers\AdminHelper;
use App\Exports\Export;
use App\Imports\Import;
use Carbon\Carbon;

use Validator;
use Response;
use Request;
use Image;
use Route;
use Auth;
use Mail;
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
            'name_alternative' => [
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
            'widget_site' => [
                'type' => 'text',
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
            'golden_partner' => [
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
            'featured' => [
                'type' => 'bool',
            ],
            'ip_protected' => [
                'type' => 'bool',
            ],
            'vip_access' => [
                'type' => 'bool',
            ],
            'vip_access_until' => [
                'type' => 'datetimepicker',
            ],
            'withdraw_at' => [
                'type' => 'datetimepicker',
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
            'short_description' => [
                'type' => 'textarea',
            ],
            'description' => [
                'type' => 'textarea',
            ],
        ];

        $this->rewardsPlatforms = [
            'trp','vox','dentacare','assurance','dentacoin','dentists','wallet'
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

        $users = User::with(['country', 'country.translations', 'logins'])->orderBy('id', 'DESC');

        if(!empty(request('search-name'))) {
            $users = $users->where('name', 'LIKE', '%'.trim(request('search-name')).'%');
        }
        if(!empty(request('search-phone'))) {
            $users = $users->where('phone', 'LIKE', '%'.trim(request('search-phone')).'%');
        }
        if(!empty(request('search-email'))) {
            $s_email = request('search-email');

            if(str_contains($s_email, ',')) {
                if(str_contains($s_email, '"')) {
                    $emails = str_replace('"', '', $s_email);
                    $users = $users->whereIn('email', explode(',', $emails ));
                } else {
                    $users = $users->whereIn('email', explode(',', $s_email ));
                }
            } else if(str_contains($s_email, ' ')) {
                if(str_contains($s_email, '"')) {
                    $ids = str_replace('"', '', $s_email);
                    $users = $users->whereIn('email', explode(' ', $ids ));
                } else {
                    $users = $users->whereIn('email', explode(' ', $s_email ));
                }
            } else {
                $users = $users->where( function($query) use ($s_email) {
                    $query->where('email', 'LIKE', '%'.trim($s_email).'%')
                    ->orWhereHas('oldEmails', function ($queryy) use ($s_email) {
                        $queryy->where('email', 'LIKE', $s_email);
                    });
                });
            }
        }
        if(!empty(request('wallet-address'))) {
            $wallet_address = request('wallet-address');
            if($wallet_address == 'with') {
                $users = $users->has('wallet_addresses');
            } else if($wallet_address == 'without') {
                $users = $users->doesntHave('wallet_addresses');
            }
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
            if(str_contains(request('search-id'), ',')) {
                if(str_contains(request('search-id'), '"')) {
                    $ids = str_replace('"', '', request('search-id'));
                    $users = $users->whereIn('id', explode(',', $ids ));
                } else {
                    $users = $users->whereIn('id', explode(',', request('search-id') ));
                }
            } else if(str_contains(request('search-id'), ' ')) {
                if(str_contains(request('search-id'), '"')) {
                    $ids = str_replace('"', '', request('search-id'));
                    $users = $users->whereIn('id', explode(' ', $ids ));
                } else {
                    $users = $users->whereIn('id', explode(' ', request('search-id') ));
                }
            } else {
                $users = $users->where('id', request('search-id') );
            }
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
            $users = $users->has('reviews_in', '=', request('search-review'));
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

        $current_url = url('cms/users/users/').'?'.http_build_query($getArrNoSort);

        if(!empty(request('exclude-countries'))) {
            $users = $users->whereNotIn('country_id', request('exclude-countries') );
        }

        if(!empty(request('with-permaban'))) {
            $users = $users->has('permanentBans' );
        }

        if(!empty(request('without-permaban'))) {
            $users = $users->doesntHave('permanentBans' );
        }

        if(!empty(request('vip-access'))) {
            $users = $users->where('vip_access', 1);
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
            $mtext = 'Admin "'.$this->user->name.'" exports users. Link to the query: '.substr($current_url, 0, -9);

            Mail::raw($mtext, function ($message) {

                $sender = config('mail.from.address');
                $sender_name = config('mail.from.name');

                $message->from($sender, $sender_name);
                $message->to( 'petya.ivanova@dentacoin.com' );
                $message->subject('Admin exports users');
            });

            $new_admin_actions = new AdminAction;
            $new_admin_actions->admin_id = $this->user->id;
            $new_admin_actions->action = 'export';
            $new_admin_actions->info = substr($current_url, 0, -9);
            $new_admin_actions->save();

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

            $export = new Export($flist);
            $file_to_export = Excel::download($export, 'users.xls');
            ob_end_clean();
            return $file_to_export;

        } else if( request()->input('export-fb') ) {
            $mtext = 'Admin "'.$this->user->name.'" exports users. Link to the query: '.substr($current_url, 0, -12);

            Mail::raw($mtext, function ($message) {

                $sender = config('mail.from.address');
                $sender_name = config('mail.from.name');

                $message->from($sender, $sender_name);
                $message->to( 'petya.ivanova@dentacoin.com' );
                $message->subject('Admin exports users');
            });

            $new_admin_actions = new AdminAction;
            $new_admin_actions->admin_id = $this->user->id;
            $new_admin_actions->action = 'export';
            $new_admin_actions->info = substr($current_url, 0, -12);
            $new_admin_actions->save();

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
        ];

        if(!empty(request('show-website'))) {
            $table_fields['website'] = array('label' => 'Website', 'format' => 'link');
        }

        $table_fields['login'] = array('template' => 'admin.parts.table-users-login', 'label' => 'Frontend' );
        $table_fields['type'] = array('template' => 'admin.parts.table-users-type');
        $table_fields['country_id'] = array('template' => 'admin.parts.table-item-country');
        $table_fields['status'] = array('template' => 'admin.parts.table-users-status', 'label' => 'Status');
        $table_fields['is_partner'] = array('template' => 'admin.parts.table-users-partner', 'label' => 'Partner');

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
            'wallet_address' => request('wallet-address'),
            'search_country' => request('search-country'),
            'search_review' => request('search-review'),
            'search_surveys_taken' => request('search-surveys-taken'),
            'search_login_after' => request('search-login-after'),
            'search_login_number' => request('search-login-number'),
            'search_dentist_claims' => request('search-dentist-claims'),
            'exclude_countries' => request('exclude-countries'),
            'without_permaban' => request('without-permaban'),
            'with_permaban' => request('with-permaban'),
            'vip_access' => request('vip-access'),
            'show_website' => request('show-website'),
            'civic_kyc_hash' => request('civic-kyc-hash'),
            'fb_tab' => request('fb-tab'),
            'user_platforms' => $user_platforms,
            'countries' => Country::with('translations')->get(),
            'table_fields' =>  $table_fields,
            'current_url' => $current_url,
        ));
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

            if ($validator->fails()) {

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

                    $allowedExtensions = array('jpg', 'jpeg', 'png');
                    $allowedMimetypes = ['image/jpeg', 'image/png'];

                    $image = GeneralHelper::decode_base64_image(Request::input('avatar'));
                
                    $checkFile = GeneralHelper::checkFile($image, $allowedExtensions, $allowedMimetypes);

                    if(isset($checkFile['success'])) {
                        $img = Image::make( $image )->orientate();
                        $newuser->addImage($img);
                    } else {
                        Request::session()->flash('error-message', $checkFile['error']);
                        return redirect('cms/users/users/edit/'.$newuser->id);
                    }
                }

                $newuser->slug = $newuser->makeSlug();
                $newuser->save();

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

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'support', 'voxer']) && Auth::guard('admin')->user()->user_id != $id) {
            return redirect('cms/users/users/edit/'.Auth::guard('admin')->user()->user_id);
        }

        $item = User::withTrashed()->find($id);

        if(!empty($item)) {

            $already_viewed = AdminAction::where('admin_id', $this->user->id)
            ->where('user_id', $item->id)
            ->where('created_at', '>', Carbon::now()->addHour(-1))
            ->first();

            if(empty($already_viewed)) {
                $new_admin_actions = new AdminAction;
                $new_admin_actions->admin_id = $this->user->id;
                $new_admin_actions->user_id = $item->id;
                $new_admin_actions->action = 'view';
                $new_admin_actions->save();
            }

            $emails = Email::with(['user', 'template', 'template.translations'])
            ->where('user_id', $id )->where( function($query) {
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
                $new_other_fields = '';

                foreach ($this->fields as $key => $value) {
                    if(!in_array($key, ['slug', 'type', 'password'])) {

                        if($this->request->input($key) != $item->$key) {
                            $dont_delete = true;

                            if(in_array($key, UserHistory::$fields)) {
                                $new_field = 'new_'.$key;
                                $user_history->$new_field = $this->request->input($key);
                                $user_history->$key = $item->$key;
                            } else {
                                $other_fields.= 'old '.$key.' = '.$item->$key.'<br/>';
                                $new_other_fields.= 'new '.$key.' = '.$this->request->input($key).'<br/>';
                            }
                        }
                    } else {
                        if($key =='password') {
                            $other_fields.= 'changed password<br/>';
                        }
                        if($key =='slug') {
                            $other_fields.= 'changed slug<br/>';
                        }
                        if($key =='type') {
                            $other_fields.= 'changed type<br/>';
                        }
                    }
                }
                
                if($dont_delete) {
                    $user_history->history = $other_fields;
                    $user_history->new_history = $new_other_fields;
                    $user_history->save();
                }

                // if($item->status == 'clinic_branch' && !empty($this->request->input('dcn_address'))) {
                    //if you change this -> check in api the logic
                //     if(mb_strlen($this->request->input('dcn_address'))!=42) {
                //         Request::session()->flash('error-message', 'Please enter a valid DCN address.');
                //         return redirect('cms/users/users/edit/'.$item->id);
                //     }
        
                //     $existing_address = WalletAddress::where('user_id', $item->id)->first();
        
                //     if (!empty($existing_address)) {
                //         $existing_address->dcn_address = $this->request->input('dcn_address');
                //         $existing_address->selected_wallet_address = 1;
                //         $existing_address->save();
                //     } else {
                //         $new_address = new WalletAddress;
                //         $new_address->user_id = $item->id;
                //         $new_address->dcn_address = $this->request->input('dcn_address');
                //         $new_address->selected_wallet_address = 1;
                //         $new_address->is_deprecated = 0;
                //         $new_address->save();
                //     }
                // }

                if( Request::input('avatar') ) {

                    $allowedExtensions = array('jpg', 'jpeg', 'png');
                    $allowedMimetypes = ['image/jpeg', 'image/png'];

                    $image = GeneralHelper::decode_base64_image(Request::input('avatar'));
                    $checkFile = GeneralHelper::checkFile($image, $allowedExtensions, $allowedMimetypes);
                    
                    if(isset($checkFile['success'])) {
                        $img = Image::make( $image )->orientate();
                        $item->addImage($img);
                    } else {
                        Request::session()->flash('error-message', $checkFile['error']);
                        return redirect('cms/users/users/edit/'.$item->id);
                    }
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
                        } else if($key=='is_partner') {
                            if (!empty($this->request->input($key))) {
                                $substitutions = [
                                    'dentist_name' => $item->getNames(),
                                ];

                                $item->sendGridTemplate(129, $substitutions, 'dentacoin');
                                
                                $acceptedPayments = $item->accepted_payment ?? [];
                                if(!in_array('dentacoin', $acceptedPayments)) {
                                    $acceptedPayments[] = 'dentacoin';
                                    $item->accepted_payment = $acceptedPayments;
                                    $item->save();
                                }

                                if($item->wallet_addresses->isEmpty()) {
                                    $item->partner_wallet_popup = Carbon::now()->addDays(-1);
                                    $item->save();
                                }
                            }
                            $item->$key = $this->request->input($key);
                        } else if($key=='email') {
                            if (empty($this->request->input($key))) {
                                $item->$key = $this->request->input($key);
                            } else {
                                
                                $existing = User::withTrashed()->where('id', '!=', $item->id)->where($key, 'like', $this->request->input($key))->first();

                                if (!empty($existing)) {
                                    if(in_array($existing->status, ['added_by_dentist_new', 'added_new', 'added_by_clinic_new'])) {

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

                                    $this->addedApproveDentist($item);

                                } else if( $this->request->input($key)=='approved' ) {
                                    
                                    $this->approveDentist($item);

                                } else if( $this->request->input($key)=='added_by_dentist_unclaimed' ) {

                                    $this->addedByDentistUnclaimed($item);

                                } else if( $this->request->input($key)=='added_by_clinic_unclaimed' ) {

                                    $this->addedByClinicUnclaimed($item);

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

                            if(in_array($this->request->input($key), ['added_approved', 'added_by_dentist_unclaimed', 'added_by_clinic_unclaimed']) && !$item->hasimage_social) {
                                $item->generateSocialCover();
                            }
                            $item->$key = $this->request->input($key);
                        } else if($key=='vip_access') {
                            
                            if($item->$key!=$this->request->input($key)) {
                                if($this->request->input($key) == 1) {
                                    if(empty($this->request->input('vip_access_until'))) {
                                        $this->request->session()->flash('error-message', 'Please, add vip access expiry date' );
                                        return redirect('cms/users/users/edit/'.$item->id);
                                    }

                                    $substitutions = [
                                        'valid_until' => date('F d, Y, H:i', strtotime(Carbon::parse($this->request->input('vip_access_until'))) ).' GMT',
                                        'days' => Carbon::now()->diffInDays($this->request->input('vip_access_until')),
                                    ];

                                    $item->sendGridTemplate(118, $substitutions, 'vox');
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

                if(!empty(request('badge-year-only'))) {
                    $help_array = [];
                    foreach(request('badge-year-only') as $i => $trg) {
                        if(!empty($trg)) {
                            $help_array[] = $trg;
                        }
                    }
                    $item->top_dentist_year = implode(';', $help_array);
                    
                } else {
                    $item->top_dentist_year = null;
                }

                $item->save();

                foreach ($item->reviews_out as $review_out) {
                    $review_out->hasimage_social = false;
                    $review_out->save();
                }

                foreach ($item->reviews_in() as $review_in) {
                    $review_in->hasimage_social = false;
                    $review_in->save();
                }

                if(in_array($item->status, ['rejected', 'added_rejected', 'added_by_dentist_rejected', 'added_by_clinic_rejected']) && empty($item->deleted_at)) {
                    $this->rejectDentist($item);
                }

                Request::session()->flash('success-message', trans('admin.page.'.$this->current_page.'.updated'));
                return redirect('cms/users/users/edit/'.$item->id);
            }

            $all_questions_answerd = VoxAnswer::where('user_id', $id)
            ->groupBy('vox_id')
            ->orderBy('created_at')
            ->get();

            $all_questions_answerd_old = VoxAnswerOld::where('user_id', $id)
            ->groupBy('vox_id')
            ->orderBy('created_at')
            ->get();

            $all_questions_answerd = $all_questions_answerd->concat($all_questions_answerd_old);

            $unanswerd_questions = array_diff($all_questions_answerd->pluck('vox_id')->toArray(), $item->filledVoxes() );

            $unfinishedVoxes = Vox::with('translations')
            ->whereIn('id', $unanswerd_questions)
            ->where('id', '!=', 11)
            ->get();

            $unfinished = [];
            
            foreach ($unfinishedVoxes as $v) {
                $unfinished[$v->id] = $v;
                $ans = VoxAnswer::where('user_id', $id)
                ->where('vox_id', $v->id)
                ->orderBy('created_at', 'asc')
                ->first();

                if(empty($ans)) {
                    $ans = VoxAnswerOld::where('user_id', $id)
                    ->where('vox_id', $v->id)
                    ->orderBy('created_at', 'asc')
                    ->first();
                }
                $user_log = UserLogin::where('user_id', $id)
                ->where('created_at', '<', $ans->created_at )
                ->orderBy('id', 'desc')
                ->first();

                $unfinished[$v->id]->user_id = $item->id;
                $unfinished[$v->id]->login = $user_log;
                $unfinished[$v->id]->taken_date = $ans->created_at;
            }

            $habits_test_ans = false;
            $habits_tests = [];

            if($item->madeTest(11)) {
                $welcome_survey = Vox::find(11);

                $welcome_questions = VoxQuestion::where('vox_id', $welcome_survey->id)->get();

                foreach ($welcome_questions as $welcome_question) {
                    $welcome_answer = VoxAnswer::where('vox_id', $welcome_survey->id)
                    ->where('user_id', $item->id)
                    ->where('question_id', $welcome_question->id)
                    ->first();

                    if(empty($welcome_answer)) {
                        $welcome_answer = VoxAnswerOld::where('vox_id', $welcome_survey->id)
                        ->where('user_id', $item->id)
                        ->where('question_id', $welcome_question->id)
                        ->first();
                    }
                    if ($welcome_answer) {
                        $habits_test_ans = true;
                    }

                    $welcome_old = VoxCrossCheck::where('user_id', $item->id)
                    ->where('question_id', $welcome_question->id)
                    ->first();

                    if(!empty($welcome_old)) {
                        $oldans= $welcome_old->old_answer;
                        $n = $oldans != 0 ? (($oldans) -1) : 1;
                        $oq = json_decode($welcome_question->answers, true)[$n ];
                    } else {
                        $oq = '';
                    }

                    $updatedWelcomeQuestion = VoxCrossCheck::where('user_id', $item->id)
                    ->where('question_id', $welcome_question->id)
                    ->orderBy('id', 'desc')
                    ->get();

                    $habits_tests[] = [
                        'question' => $welcome_question->question,
                        'old_answer' => $oq ? $oq : ($welcome_answer ? json_decode($welcome_question->answers, true)[($welcome_answer->answer) -1] : ''),
                        'answer' => $oq && $welcome_answer ? ((isset(json_decode($welcome_question->answers, true)[($welcome_answer->answer) -1])) ? json_decode($welcome_question->answers, true)[($welcome_answer->answer) -1] : '' ) : '',
                        'last_updated' => $updatedWelcomeQuestion->isNotEmpty() ? $updatedWelcomeQuestion->first()->created_at : '',
                        'updates_count' => $updatedWelcomeQuestion->isNotEmpty() ? $updatedWelcomeQuestion->count() : '',
                    ];
                }

                $firstAnswerSex = VoxCrossCheck::where('user_id', $item->id)
                ->where('question_id', 'gender')
                ->first();

                $allAnswersSex = VoxCrossCheck::where('user_id', $item->id)
                ->where('question_id','gender')
                ->orderBy('id', 'desc')
                ->get();

                $habits_tests[] = [
                    'question' => 'What is your biological sex?',
                    'old_answer' => !empty($firstAnswerSex) ? ($firstAnswerSex->old_answer == 1 ? 'Male' : 'Female') : (!empty($item->gender) ? ($item->gender == 'm' ? 'Male' : 'Female') : ''),
                    'answer' => !empty($firstAnswerSex) && !empty($item->gender) ? ($item->gender == 'm' ? 'Male' : 'Female') : '',
                    'last_updated' => $allAnswersSex->isNotEmpty() ? $allAnswersSex->first()->created_at : '',
                    'updates_count' => $allAnswersSex->isNotEmpty() ? $allAnswersSex->count() : '',
                ];

                $firstAnswerBirth = VoxCrossCheck::where('user_id', $item->id)
                ->where('question_id', 'birthyear')
                ->first();

                $allAnswersBirth = VoxCrossCheck::where('user_id', $item->id)
                ->where('question_id','birthyear')
                ->orderBy('id', 'desc')
                ->get();

                $habits_tests[] = [
                    'question' => "What's your year of birth?",
                    'old_answer' => !empty($firstAnswerBirth) ? $firstAnswerBirth->old_answer : (!empty($item->birthyear) ? $item->birthyear : ''),
                    'answer' => !empty($firstAnswerBirth) && !empty($item->birthyear) ? $item->birthyear : '',
                    'last_updated' => $allAnswersBirth->isNotEmpty() ? $allAnswersBirth->first()->created_at : '',
                    'updates_count' => $allAnswersBirth->isNotEmpty() ? $allAnswersBirth->count() : '',
                ];

                foreach (config('vox.details_fields') as $k => $v) {
                    if (!empty($item->$k) || $item->$k === '0') {
                        $habits_test_ans = true;
                    }

                    $vcc = VoxCrossCheck::where('user_id', $item->id)->where('question_id', $k)->first();
                    $old_an = !empty($vcc) ? $vcc->old_answer : '';
                    if ($old_an || $old_an === '0') {
                        $i=1;
                        foreach ($v['values'] as $key => $value) {
                            if($i==$old_an) {
                                $old_an = $value;
                                break;
                            }
                            $i++;
                        }
                    }

                    $habits_last = VoxCrossCheck::where('user_id', $item->id)
                    ->where('question_id', $k)
                    ->orderBy('id', 'desc')
                    ->first();

                    $habits_count = VoxCrossCheck::where('user_id', $item->id)
                    ->where('question_id', $k)
                    ->count();

                    $habits_tests[] = [
                        'question' => $v['label'],
                        'old_answer' => $old_an || $old_an === '0' ? $old_an : (!empty($item->$k) || $item->$k === '0' ? $v['values'][$item->$k] : ''),
                        'answer' => ($old_an || $old_an === '0') && (!empty($item->$k) || $item->$k === '0') ? $v['values'][$item->$k] : '',
                        'last_updated' => !empty($habits_last) ? $habits_last->created_at : '',
                        'updates_count' => $habits_count ? $habits_count : '',
                    ];
                }
            }

            $duplicated_mails = collect();
            if( !empty($item->email)) {
                $duplicated_mails = User::where('id', '!=', $item->id)
                ->where('email', 'LIKE', $item->email)
                ->withTrashed()
                ->get();
            }

            $duplicated_names = collect();
            if( !empty($item->name)) {
                $duplicated_names = User::where('id', '!=', $item->id)
                ->where('name', 'LIKE', $item->name)
                ->withTrashed()
                ->get();
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
                'dev_domain' => in_array( Request::getHost(), ['urgent.dentavox.dentacoin.com', 'urgent.reviews.dentacoin.com'] ) ? true : false,
            ));
        } else {
            return redirect('cms/users/users/');
        }
    }

    private function approveDentist($item) {

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

        if(config('trp.add_to_sendgrid_list')) {
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

                        $dent->generateSocialCover();

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
                GeneralHelper::deviceDetector($reward);
                $reward->save();

                $inv->rewarded = true;
                $inv->save();
            }
        }

        $user_history = new UserHistory;
        $user_history->user_id = $item->id;
        $user_history->admin_id = $this->user->id;
        $user_history->status = $item->status;
        $user_history->new_status = 'approved';
        $user_history->save();

        $item->status = 'approved';
        $item->save();
        $item->generateSocialCover();
    }

    private function addedApproveDentist($item) {

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
            $reward = new DcnReward();
            $reward->user_id = $patient->id;
            $reward->reward = Reward::getReward('patient_add_dentist');
            $reward->platform = 'trp';
            $reward->type = 'added_dentist';
            $reward->reference_id = $item->id;
            GeneralHelper::deviceDetector($reward);
            $reward->save();

            $substitutions = [
                'added_dentist_name' => $item->getNames(),
                'trp_added_dentist_prf' => $item->getLink().'?dcn-gateway-type=patient-login',
            ];

            $patient->sendGridTemplate(65, $substitutions, 'trp');
        }

        $user_history = new UserHistory;
        $user_history->user_id = $item->id;
        $user_history->admin_id = $this->user->id;
        $user_history->status = $item->status;
        $user_history->new_status = 'added_approved';
        $user_history->save();

        if( $item->deleted_at ) {
            $item->restore();
        }

        $item->status = 'added_approved';
        $item->save();
        $item->generateSocialCover();
    }

    private function addedByDentistUnclaimed($item) {

        $user_history = new UserHistory;
        $user_history->user_id = $item->id;
        $user_history->admin_id = $this->user->id;
        $user_history->status = $item->status;
        $user_history->new_status = 'added_by_dentist_unclaimed';
        $user_history->save();
        
        if( $item->deleted_at ) {
            $item->restore();
        }

        $item->status = 'added_by_dentist_unclaimed';
        $item->slug = $item->makeSlug();
        $item->save();

        $item->generateSocialCover();

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
    }

    private function addedByClinicUnclaimed($item) {

        $user_history = new UserHistory;
        $user_history->user_id = $item->id;
        $user_history->admin_id = $this->user->id;
        $user_history->status = $item->status;
        $user_history->new_status = 'added_by_clinic_unclaimed';
        $user_history->save();
        
        if( $item->deleted_at ) {
            $item->restore();
        }

        $item->status = 'added_by_clinic_unclaimed';
        $item->slug = $item->makeSlug();
        $item->save();

        $item->generateSocialCover();

        // $dent_name = null;
        // if(!empty($item->invited_by)) {
        //     $dent = User::find($item->invited_by);

        //     if(!empty($dent)) {
        //         $dent_name = $dent->getNames();
        //     }
        // }

        // $item->sendGridTemplate( 93 , [
        //     'dentist_name' => !empty($dent_name) ? $dent_name : 'Name',
        //     "invitation_link" => getLangUrl( 'dentist/'.$item->slug.'/claim/'.$item->id).'?'. http_build_query(['popup'=>'claim-popup']).'&without-info=true',
        // ], 'trp');
    }

    private function rejectDentist($item) {

        $action = new UserAction;
        $action->user_id = $item->id;
        $action->action = 'deleted';
        $action->reason = 'Rejected by admin';
        $action->actioned_at = Carbon::now();
        $action->save();

        $item->deleteActions();
        User::destroy( $item->id );
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

                                        $allowedExtensions = array('jpg', 'jpeg', 'png');
                                        $allowedMimetypes = ['image/jpeg', 'image/png'];
                                    
                                        $checkFile = GeneralHelper::checkFile($row[9], $allowedExtensions, $allowedMimetypes);

                                        if(isset($checkFile['success'])) {
                                            $img = Image::make( $row[9] )->orientate();
                                            $newuser->addImage($img);
                                        }
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

    public function delete( $id ) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'support'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $item = User::find($id);

        if(!empty($item) && $item->id != 3) {

            if (!empty(Request::input('deleted_reason'))) {
                $this->deleteUser( $item );

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
                        User::destroy( $dent->id );
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

        $transactions = DcnTransaction::where('user_id', $item->id)
        ->whereIn('status', ['new', 'failed', 'first', 'not_sent'])
        ->get();

        if ($transactions->isNotEmpty()) {
            foreach ($transactions as $trans) {
                $trans->forceDelete();
            }
        }

        $item->forceDelete();

        $this->request->session()->flash('success-message', 'Deleted forever' );
        return redirect('cms/users');
    }

    public function massdelete() {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'support'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        if( Request::input('ids') ) {
            $delusers = User::whereIn('id', Request::input('ids'))->get();
            foreach ($delusers as $du) {
                if($du->id != 3) {
                    $this->deleteUser( $user );
                }
            }
        }

        $this->request->session()->flash('success-message', 'All selected users are deleted' );
        return redirect(url()->previous());
    }

    private function deleteUser( $user ) {
        $action = new UserAction;
        $action->user_id = $user->id;
        $action->action = 'deleted';
        $action->reason = Request::input('mass-delete-reasons');
        $action->actioned_at = Carbon::now();
        $action->save();

        $user->deleteActions();
        $user->delete();
    }

    public function massReject() {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'support'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        if( Request::input('ids') ) {
            $rejectusers = User::whereIn('id', Request::input('ids'))->get();
            foreach ($rejectusers as $ru) {
                if($ru->is_dentist && empty($ru->deleted_at)) {
                    if(in_array($ru->status, ['added_new', 'added_approved'])) {
                        
                        $user_history = new UserHistory;
                        $user_history->user_id = $ru->id;
                        $user_history->admin_id = $this->user->id;
                        $user_history->status = $ru->status;
                        $user_history->new_status = 'added_rejected';
                        $user_history->save();

                        $ru->status = 'added_rejected';
                        $ru->save();
                        
                        $this->rejectDentist($ru);

                    } else if(in_array($ru->status, ['new', 'pending', 'approved'])) {

                        $user_history = new UserHistory;
                        $user_history->user_id = $ru->id;
                        $user_history->admin_id = $this->user->id;
                        $user_history->status = $ru->status;
                        $user_history->new_status = 'rejected';
                        $user_history->save();

                        $ru->status = 'rejected';
                        $ru->save();

                        $ru->sendTemplate(14);

                        $this->rejectDentist($ru);

                    } else if(in_array($ru->status, ['added_by_clinic_new', 'added_by_clinic_unclaimed', 'added_by_clinic_claimed'])) {

                        $user_history = new UserHistory;
                        $user_history->user_id = $ru->id;
                        $user_history->admin_id = $this->user->id;
                        $user_history->status = $ru->status;
                        $user_history->new_status = 'added_by_clinic_rejected';
                        $user_history->save();

                        $ru->status = 'added_by_clinic_rejected';
                        $ru->save();
                        
                        $this->rejectDentist($ru);

                    } else if(in_array($ru->status, ['added_by_dentist_new', 'added_by_dentist_unclaimed', 'added_by_dentist_claimed'])) {

                        $user_history = new UserHistory;
                        $user_history->user_id = $ru->id;
                        $user_history->admin_id = $this->user->id;
                        $user_history->status = $ru->status;
                        $user_history->new_status = 'added_by_dentist_rejected';
                        $user_history->save();

                        $ru->status = 'added_by_dentist_rejected';
                        $ru->save();
                        
                        $this->rejectDentist($ru);
                    }
                }
            }
        }

        $this->request->session()->flash('success-message', 'All selected dentists are rejected' );
        return redirect(url()->previous());
    }

    public function massApprove() {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'support'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        if( Request::input('ids') ) {
            $approvedUsers = User::whereIn('id', Request::input('ids'))->get();
            foreach ($approvedUsers as $au) {
                if($au->is_dentist) {
                    if(in_array($au->status, ['added_new', 'added_rejected'])) {
                        $this->addedApproveDentist($au);
                    } else if(in_array($au->status, ['new', 'pending', 'rejected'])) {
                        $this->approveDentist($au);
                    } else if(in_array($au->status, ['added_by_clinic_new', 'added_by_clinic_rejected'])) {
                        $this->addedByClinicUnclaimed($au);
                    } else if(in_array($au->status, ['added_by_dentist_new', 'added_by_dentist_rejected'])) {
                        $this->addedByDentistUnclaimed($au);
                    }
                }
            }
        }

        $this->request->session()->flash('success-message', 'All selected dentists are approved' );
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

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'support', 'voxer'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $item = User::withTrashed()->find($id);
        $ban = UserBan::find($ban_id);
        
        if(!empty($ban) && !empty($item) && $ban->user_id == $item->id) {            
            $item->sendgridSubscribeToGroup($ban->domain);
            
            UserBan::destroy( $ban_id );
            UserSurveyWarning::where('user_id', $ban->user_id)->delete();
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
        
            $sg->client->asm()->groups()->_($group_id)->suppressions()->post($request_body);

            $ban->restore();
        }

        $this->request->session()->flash('success-message', 'Ban restored!' );
        return redirect('cms/users/users/edit/'.$id);
    }

    public function delete_vox( $id, $reward_id ) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'support', 'voxer'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $item = User::withTrashed()->find($id);
        $reward = DcnReward::find($reward_id);

        if(!empty($reward) && !empty($item) && $reward->user_id == $item->id) {
            VoxAnswer::where([
                ['user_id', $item->id],
                ['vox_id', $reward->reference_id],
            ])->delete();

            VoxAnswerOld::where([
                ['user_id', $item->id],
                ['vox_id', $reward->reference_id],
            ])->delete();
            
            DcnReward::destroy( $reward_id );
        }

        $this->request->session()->flash('success-message', trans('admin.page.'.$this->current_page.'.reward-deleted') );
        return redirect('cms/users/users/edit/'.$id);
    }

    public function delete_unfinished( $id, $vox_id ) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'support', 'voxer'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $item = User::withTrashed()->find($id);
        
        if(!empty($item)) {
            VoxAnswer::where([
                ['user_id', $item->id],
                ['vox_id', $vox_id],
            ])->delete();

            VoxAnswerOld::where([
                ['user_id', $item->id],
                ['vox_id', $vox_id],
            ])->delete();
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
            TrpService::deleteReview($item);

            $this->request->session()->flash('success-message', 'Review deleted' );
            return redirect(!empty(Request::server('HTTP_REFERER')) ? Request::server('HTTP_REFERER') : ('cms/users/users/edit/'.$item->review_to_id));
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

    public function upload_temp($locale=null) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'support'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        if( Request::file('image') && Request::file('image')->isValid() ) {

            $extensions = ['image/jpeg', 'image/png'];

            if (!in_array(Input::file('image')->getMimeType(), $extensions)) {
                return Response::json([
                    'success' => false,
                ]);
            }

            $img = Image::make( Input::file('image') )->orientate();

            $imageArray = GeneralHelper::addTempImage($img);

            if(count($imageArray)) {
                list($thumb, $full, $name) = $imageArray;

                return Response::json([
                    'success' => true, 
                    'thumb' => $thumb, 
                    'name' => $name 
                ]);
            } else {
                return Response::json([
                    'success' => false,
                ]);
            }
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
            $item->top_dentist_year = null;
            $item->title = null;
            $item->slug = null;
            $item->patient_status = 'new_not_verified';

            $item->product_news = ['dentacoin', 'trp', 'vox'];
            $item->save();

            //create recipients
            $item->removeFromSendgridSubscribes();

            if(config('trp.add_to_sendgrid_list')) {
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
            $duplicated_names = User::where('id', '!=', $user->id)
            ->where('name', 'LIKE', $user->name)
            ->withTrashed()
            ->get();
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

        $from_date = new Carbon('2017-01-01 00:00:00');
        $to_date = Carbon::now();

        if(!empty(request('search_users_to'))) {
            $to_date = new Carbon(request('search-register-to'));
        }

        if(!empty(request('search_users_from'))) {
            $from_date = new Carbon(request('search_users_from'));
        }

        if(!empty(request('search_from'))) {
            if(request('search_from') == 'last-7') {
                $from_date = Carbon::now()->addDays(-7);
            }
            if(request('search_from') == 'this-month') {
                $from_date = Carbon::now()->startOfMonth();
            }
            if(request('search_from') == 'last-month') {
                $from_date = new Carbon('first day of last month');
            }
            if(request('search_from') == 'this-year') {
                $from_date = Carbon::now()->startOfYear();
            }
        }

        $user_types = DB::select("
            SELECT 
                COUNT(*) AS `total`,
                SUM( IF( `is_partner` , 1, 0 ) ) AS `partners`,
                `is_dentist`
            FROM  `users` 
            WHERE `created_at` >= '".$from_date."'
            AND `created_at` <= '".$to_date."'
            AND `deleted_at` is null
            GROUP BY `is_dentist`
            ORDER BY `total` DESC
        ");

        $user_genders = User::groupBy('gender')
        ->select('gender', DB::raw('count(*) as total'))
        ->where('created_at', '>=', $from_date)
        ->where('created_at', '<=', $to_date)
        ->get();

        $countries = DB::select("
            SELECT 
                COUNT(*) AS `total`,
                SUM( IF(  `is_partner` , 1, 0 ) ) AS `partners`,
                SUM( IF(  !`is_dentist` , 1, 0 ) ) AS `patients`,
                SUM( IF(  `is_dentist` AND !`is_clinic`, 1, 0 ) ) AS `dentists`,
                SUM( IF(  `is_dentist` AND `is_clinic`, 1, 0 ) ) AS `clinics`,
                SUM( IF(  `is_dentist` AND !`is_clinic` AND `status` IN ('approved','added_by_clinic_claimed','added_by_dentist_claimed'), 1, 0 ) ) AS `approved_dentists`,
                SUM( IF(  `is_dentist` AND `is_clinic` AND `status` IN ('approved','added_by_clinic_claimed','added_by_dentist_claimed'), 1, 0 ) ) AS `approved_clinics`,
                `country_id`
            FROM  `users` 
            WHERE `created_at` >= '".$from_date."'
            AND `created_at` <= '".$to_date."'
            AND `deleted_at` is null
            GROUP BY `country_id` 
            ORDER BY `total` DESC
        ");

        $marital_statuses=[];
        $children=[];
        $household_children=[];
        $education=[];
        $employment=[];
        $job=[];
        $job_title=[];
        $income=[];

        if(request('show-all')) {

            $marital_statuses = DB::select("
                SELECT 
                    COUNT(*) AS `total`,
                    SUM( IF(  `is_partner` , 1, 0 ) ) AS `partners`,
                    SUM( IF(  !`is_dentist` , 1, 0 ) ) AS `patients`,
                    SUM( IF(  `is_dentist` AND !`is_clinic`, 1, 0 ) ) AS `dentists`,
                    SUM( IF(  `is_dentist` AND `is_clinic`, 1, 0 ) ) AS `clinics`,
                    `marital_status`
                FROM  `users` 
                WHERE `created_at` >= '".$from_date."'
                AND `created_at` <= '".$to_date."'
                AND `deleted_at` is null
                GROUP BY `marital_status` 
                ORDER BY `total` DESC
            ");

            $children = DB::select("
                SELECT 
                    COUNT(*) AS `total`,
                    SUM( IF(  `is_partner` , 1, 0 ) ) AS `partners`,
                    SUM( IF(  !`is_dentist` , 1, 0 ) ) AS `patients`,
                    SUM( IF(  `is_dentist` AND !`is_clinic`, 1, 0 ) ) AS `dentists`,
                    SUM( IF(  `is_dentist` AND `is_clinic`, 1, 0 ) ) AS `clinics`,
                    `children`
                FROM  `users` 
                WHERE `created_at` >= '".$from_date."'
                AND `created_at` <= '".$to_date."'
                AND `deleted_at` is null
                GROUP BY `children` 
                ORDER BY `total` DESC
            ");

            $household_children = DB::select("
                SELECT 
                    COUNT(*) AS `total`,
                    SUM( IF(  `is_partner` , 1, 0 ) ) AS `partners`,
                    SUM( IF(  !`is_dentist` , 1, 0 ) ) AS `patients`,
                    SUM( IF(  `is_dentist` AND !`is_clinic`, 1, 0 ) ) AS `dentists`,
                    SUM( IF(  `is_dentist` AND `is_clinic`, 1, 0 ) ) AS `clinics`,
                    `household_children`
                FROM  `users` 
                WHERE `created_at` >= '".$from_date."'
                AND `created_at` <= '".$to_date."'
                AND `deleted_at` is null
                GROUP BY `household_children` 
                ORDER BY `total` DESC
            ");

            $education = DB::select("
                SELECT 
                    COUNT(*) AS `total`,
                    SUM( IF(  `is_partner` , 1, 0 ) ) AS `partners`,
                    SUM( IF(  !`is_dentist` , 1, 0 ) ) AS `patients`,
                    SUM( IF(  `is_dentist` AND !`is_clinic`, 1, 0 ) ) AS `dentists`,
                    SUM( IF(  `is_dentist` AND `is_clinic`, 1, 0 ) ) AS `clinics`,
                    `education`
                FROM  `users` 
                WHERE `created_at` >= '".$from_date."'
                AND `created_at` <= '".$to_date."'
                AND `deleted_at` is null
                GROUP BY `education` 
                ORDER BY `total` DESC
            ");

            $employment = DB::select("
                SELECT 
                    COUNT(*) AS `total`,
                    SUM( IF(  `is_partner` , 1, 0 ) ) AS `partners`,
                    SUM( IF(  !`is_dentist` , 1, 0 ) ) AS `patients`,
                    SUM( IF(  `is_dentist` AND !`is_clinic`, 1, 0 ) ) AS `dentists`,
                    SUM( IF(  `is_dentist` AND `is_clinic`, 1, 0 ) ) AS `clinics`,
                    `employment`
                FROM  `users` 
                WHERE `created_at` >= '".$from_date."'
                AND `created_at` <= '".$to_date."'
                AND `deleted_at` is null
                GROUP BY `employment` 
                ORDER BY `total` DESC
            ");

            $job = DB::select("
                SELECT 
                    COUNT(*) AS `total`,
                    SUM( IF(  `is_partner` , 1, 0 ) ) AS `partners`,
                    SUM( IF(  !`is_dentist` , 1, 0 ) ) AS `patients`,
                    SUM( IF(  `is_dentist` AND !`is_clinic`, 1, 0 ) ) AS `dentists`,
                    SUM( IF(  `is_dentist` AND `is_clinic`, 1, 0 ) ) AS `clinics`,
                    `job`
                FROM  `users` 
                WHERE `created_at` >= '".$from_date."'
                AND `created_at` <= '".$to_date."'
                AND `deleted_at` is null
                GROUP BY `job` 
                ORDER BY `total` DESC
            ");

            $job_title = DB::select("
                SELECT 
                    COUNT(*) AS `total`,
                    SUM( IF(  `is_partner` , 1, 0 ) ) AS `partners`,
                    SUM( IF(  !`is_dentist` , 1, 0 ) ) AS `patients`,
                    SUM( IF(  `is_dentist` AND !`is_clinic`, 1, 0 ) ) AS `dentists`,
                    SUM( IF(  `is_dentist` AND `is_clinic`, 1, 0 ) ) AS `clinics`,
                    `job_title`
                FROM  `users` 
                WHERE `created_at` >= '".$from_date."'
                AND `created_at` <= '".$to_date."'
                AND `deleted_at` is null
                GROUP BY `job_title` 
                ORDER BY `total` DESC
            ");

            $income = DB::select("
                SELECT 
                    COUNT(*) AS `total`,
                    SUM( IF(  `is_partner` , 1, 0 ) ) AS `partners`,
                    SUM( IF(  !`is_dentist` , 1, 0 ) ) AS `patients`,
                    SUM( IF(  `is_dentist` AND !`is_clinic`, 1, 0 ) ) AS `dentists`,
                    SUM( IF(  `is_dentist` AND `is_clinic`, 1, 0 ) ) AS `clinics`,
                    `income`
                FROM  `users` 
                WHERE `created_at` >= '".$from_date."'
                AND `created_at` <= '".$to_date."'
                AND `deleted_at` is null
                GROUP BY `income` 
                ORDER BY `total` DESC
            ");
        }
        
        $countriesTable = Country::with('translations')->get();

        return $this->showView('users-stats', array(
            'user_genders' => $user_genders,
            'user_types' => $user_types,
            'countries' => $countries,
            'marital_statuses' => $marital_statuses,
            'children' => $children,
            'household_children' => $household_children,
            'education' => $education,
            'employment' => $employment,
            'job' => $job,
            'job_title' => $job_title,
            'income' => $income,
            'answered_questions' => VoxQuestionAnswered::get(),
            'search_users_from' => request('search_users_from'),
            'search_users_to' => request('search_users_to'),
            'countriesArray' => $countriesTable->pluck('name', 'id')->toArray(),
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
            $incomplete = IncompleteRegistration::whereNull('completed')
            ->orderBy('id', 'desc')
            ->get();

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

        $incompletes = IncompleteRegistration::with('country')->orderBy('id', 'desc');

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

        $paginations = AdminHelper::paginationsFunction($total_pages, $adjacents, $page);
        $start = $paginations['start'];
        $end = $paginations['end'];

        $incompletes = $incompletes->skip( ($page-1)*$ppp )->take($ppp)->get();

        $pagination_link = '';

        foreach (Request::all() as $key => $value) {
            if($key != 'page') {
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

        $leads = LeadMagnet::with('country')->orderBy('id', 'desc')->get();

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

        if(!empty(request('search-platform'))) {
            $rewards = $rewards->where('platform', request('search-platform') );
        }

        $total_count = $rewards->count();
        $sum_price = $rewards->sum('reward');
        $page = max(1,intval(request('page')));
        $ppp = 25;
        $adjacents = 2;
        $total_pages = ceil($total_count/$ppp);

        $paginations = AdminHelper::paginationsFunction($total_pages, $adjacents, $page);
        $start = $paginations['start'];
        $end = $paginations['end'];

        $rewards = $rewards->skip( ($page-1)*$ppp )->take($ppp)->get();

        $pagination_link = '';

        foreach (Request::all() as $key => $value) {
            if($key != 'page') {
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
            'search_platform' =>  request('search-platform'),
            'rewardsPlatforms' => $this->rewardsPlatforms,
            'count' =>($page - 1)*$ppp ,
            'start' => $start,
            'end' => $end,
            'total_pages' => $total_pages,
            'page' => $page,
            'pagination_link' => $pagination_link,
        ));
    }

    public function bans() {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'support'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $bans = UserBan::withTrashed()->with(['user', 'vox', 'vox.translations', 'question', 'question.translations'])->orderBy('id', 'desc');

        if(!empty(request('search-user-id'))) {
            $bans = $bans->where('user_id', request('search-user-id'));
        }

        if(!empty(request('search-email'))) {
            $bans = $bans->whereHas('user', function($query) {
                $query->where('email', 'LIKE', '%'.trim(request('search-email')).'%');
            });
        }

        if(!empty(request('search-type'))) {
            $bans = $bans->where('type', request('search-type') );
        }

        $total_count = $bans->count();
        $page = max(1,intval(request('page')));
        $ppp = 25;
        $adjacents = 2;
        $total_pages = ceil($total_count/$ppp);

        $paginations = AdminHelper::paginationsFunction($total_pages, $adjacents, $page);
        $start = $paginations['start'];
        $end = $paginations['end'];

        $bans = $bans->skip( ($page-1)*$ppp )->take($ppp)->get();

        $pagination_link = '';

        foreach (Request::all() as $key => $value) {
            if($key != 'page') {
                $pagination_link .= '&'.$key.'='.($value === null ? '' : $value);
            }
        }

        return $this->showView('users-bans', array(
            'items' => $bans,
            'total_count' => $total_count,
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

    public function lostUsers() {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'support'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $items = DeletedUserEmails::with(['user', 'emailUser']);

        $total_count = $items->count();
        $page = max(1,intval(request('page')));
        $ppp = 100;
        $adjacents = 2;
        $total_pages = ceil($total_count/$ppp);

        $paginations = AdminHelper::paginationsFunction($total_pages, $adjacents, $page);
        $start = $paginations['start'];
        $end = $paginations['end'];

        $items = $items->skip( ($page-1)*$ppp )->take($ppp)->get();

        $pagination_link = '';

        foreach (Request::all() as $key => $value) {
            if($key != 'page') {
                $pagination_link .= '&'.$key.'='.($value === null ? '' : $value);
            }
        }

        return $this->showView('users-lost', array(
            'items' => $items,
            'registered' => DeletedUserEmails::whereNotNull('user_id')->count(),
            'total_count' => $total_count,
            'count' =>($page - 1)*$ppp ,
            'start' => $start,
            'end' => $end,
            'total_pages' => $total_pages,
            'page' => $page,
            'pagination_link' => $pagination_link,
        ));
    }

    public function answeredQuestionsCount() {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'support'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $answered_questions_count = VoxAnswer::where('created_at', '>=', request('search_from'))
        ->where('created_at', '<=', request('search_to'))
        ->count();

        return Response::json([
            'success' => true, 
            'data' => $answered_questions_count,
        ]);
    }

    public function addOrEditHighlight($user_id, $highlight_id=null) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'support'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        if(Request::isMethod('post')) {

            $validator = Validator::make(Request::all(), [
                // 'image' => array('required'),
                'title' => array('required'),
                'link' => array('required'),
            ]);

            if ($validator->fails()) {
                return redirect(url('cms/users/users/edit/'.$user_id.'/add-edit-highlight/'($highlight_id ?? '')))
                ->withInput()
                ->withErrors($validator);
            } else {
                if($highlight_id) {
                    $blogpost = DentistBlogpost::find($highlight_id);
                } else {
                    $blogpost = new DentistBlogpost;
                }
                $blogpost->dentist_id = $user_id;
                $blogpost->title = Request::input('title');
                $blogpost->link = Request::input('link');
                $blogpost->save();

                if($_FILES['image']['name']) {
                    $allowedExtensions = array('jpg', 'jpeg', 'png');
                    $allowedMimetypes = ['image/jpeg', 'image/png'];
                    
                    $checkFile = GeneralHelper::checkFile(Input::file('image'), $allowedExtensions, $allowedMimetypes);
            
                    if(isset($checkFile['success'])) {
                        $img = Image::make( Input::file('image') )->orientate();
                        $filename = explode('.', $_FILES['image']['name'])[0];
                        $blogpost->addImage($img ,$filename);
                    } else {
                        Request::session()->flash('error-message', $checkFile['error']);
                        return redirect('cms/users/users/edit/'.$user_id);
                    }
                }

                return redirect('cms/users/users/edit/'.$user_id);
            }
        }

        return $this->showView('users-add-highlight', array(
            'item' => User::find($user_id),
            'highlight' => $highlight_id ? DentistBlogpost::find($highlight_id) : '',
        ));
    }

    public function removeHighlight($user_id, $id) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'support'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        DentistBlogpost::find($id)->delete();

        Request::session()->flash('success-message', 'Highlights Delete');
        return redirect('cms/users/users/edit/'.$user_id);
    }

    public function reorderHighlight($user_id) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'voxer'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $list = Request::input('list');
        // dd($list);
        $i=1;
        foreach ($list as $hid) {
            $highlight = DentistBlogpost::find($hid);
            $highlight->sort_order = $i;
            $highlight->save();
            $i++;
        }

        return Response::json([
            'success' => true
        ]);
    }

    public function makePartners() {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'support'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $items = User::whereNull('self_deleted')
        ->where(function($query) {
			$query->where('is_partner', 0)
			->orWhereNull('is_partner');
		})->where('accepted_payment', 'LIKE', '%dentacoin%')->get();
        
        return $this->showView('users-make-partners', array(
            'items' => $items,
        ));
    }
}