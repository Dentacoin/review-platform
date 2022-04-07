<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;

use App\Models\DcnTransaction;
use App\Models\SupportContact;
use App\Models\BanAppeal;
use App\Models\AdminIp;
use App\Models\Review;
use App\Models\Order;
use App\Models\User;

use Carbon\Carbon;

use Auth;

class AdminController extends BaseController {

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    
    public $current_subpage;
    public $current_page;
    public $parameters;
    public $request;

    public function __construct(Request $request) {
        
        setlocale(LC_ALL, 'en');
        \App::setLocale('en');

        date_default_timezone_set("Europe/Sofia");

    	$this->request = $request;
    	$path = explode('/', $request->path());

    	$this->current_page = isset($path[1]) ? $path[1] : null;
    	if(!isset( config('admin.pages')[$this->current_page] )) {
			$this->current_page='home';
		}

    	$this->current_subpage = isset($path[2]) ? $path[2] : null;
		if(!isset( config('admin.pages')[$this->current_page]['subpages'][$this->current_subpage] )) {
			$this->current_subpage = isset(config('admin.pages')[$this->current_page]['subpages']) ? key(config('admin.pages')[$this->current_page]['subpages']) : null;
		}
        $this->langs = config('langs')['admin'];
        
        //$this->user = Auth::guard('web')->user();
        $this->middleware(function ($request, $next) {
            $this->user= Auth::guard('admin')->user();

            $safeIp = AdminIp::where('ip', User::getRealIp())->first();

            if(!$safeIp) {
                return redirect('cms/login')
                ->withInput()
                ->with('error-message', 'This IP is not in the whitelist!');
            }
            
            if(
                Auth::guard('admin')->user() 
                && Auth::guard('admin')->user()->password_last_updated_at->toDateTimeString() < Carbon::now()->addDays(-60)->toDateTimeString() 
                &&  $request->path() != 'cms/password-expired'
            ) {
                return redirect('cms/password-expired');
            }

            if(Auth::guard('admin')->user() && Auth::guard('admin')->user()->logged_in && $request->path() != 'cms/admin-authentication') {
                return redirect('cms/admin-authentication');
            }

            return $next($request);
        });
    
        $this->categories = [];
        $clist = config('categories');
        foreach ($clist as $cat) {
            $this->categories[$cat] = trans('admin.categories.'.$cat);
        }
    }

    public function ShowView($page, $params=array()) {

        $params['current_page'] = $this->current_page;
        $params['current_subpage'] = $this->current_subpage;
        $params['request'] = $this->request;
        $params['admin'] = $this->user;
        $params['langs'] = $this->langs;

        $menu = config('admin.pages');
        
        if($params['admin']->role=='translator') {
            foreach ($menu as $key => $value) {
                if($key!='translations' && $key!='export-import' ) {
                    unset($menu[$key]);
                } else {
                    if ($key=='translations') {
                        foreach ($menu[$key]['subpages'] as $sk => $sv) {
                            if(!in_array($sk, explode(',', $params['admin']->text_domain))) {
                                unset( $menu[$key]['subpages'][$sk] );
                            }
                        }
                    }
                }
            }
        }

        if($params['admin']->role=='voxer') {
            foreach ($menu as $key => $value) {
                if($key!='vox' && $key!='users') {
                    unset($menu[$key]);
                } else {
                    if( isset( $menu[$key]['subpages'] ) ) {
                        foreach ($menu[$key]['subpages'] as $sk => $sv) {
                            if($sk !='list' && $sk!='add' && $sk !='scales') {
                                unset( $menu[$key]['subpages'][$sk] );
                            }
                        }
                    }
                }
            }
        }
        
        if($params['admin']->role=='support') {
            foreach ($menu as $key => $value) {
                if(in_array($key, [
                    'users',
                    'ips',
                    'whitelist',
                    'blacklist',
                    'transactions',
                    'email_validations',
                    'trp',
                    'vox',
                    'support',
                    'rewards',
                    'invites',
                    'ban_appeals'
                ]) ) {
                    if(isset($menu[$key]['subpages'])) {
                        foreach ($menu[$key]['subpages'] as $sk => $sv) {
                            if(in_array($sk, [
                                'anonymous_users',
                                'users_stats',
                                'incomplete-registrations',
                                'lead-magnet',
                                'paid-reports',
                                'users_stats',
                                'questions',
                                'faq',
                                'testimonials',
                                'scrape-google-dentists',
                                'add',
                                'scales',
                                'faq-ios',
                                'explorer',
                                'export-survey-data',
                                'polls-explorer',
                                'recommendations',
                                'tests',
                                'history'
                            ]) || ($sk == 'categories' && $key != 'support')) {
                                unset( $menu[$key]['subpages'][$sk] );
                            }
                        }
                    }
                } else {
                    unset($menu[$key]);
                }
            }
        }
        
        if($params['admin']->role=='admin') {
            foreach ($menu as $key => $value) {
                if($key=='admins' || $key=='logs') {
                    unset($menu[$key]);
                }
                if(isset($menu[$key]['subpages'])) {
                    foreach ($menu[$key]['subpages'] as $sk => $sv) {
                        if($sk == 'history') {
                            unset( $menu[$key]['subpages'][$sk] );
                        }
                    }
                }
            }
        }

        if($params['admin']->role == 'super_admin' || !empty($params['admin']->email_template_type)) {
            if($params['admin']->role != 'super_admin') {
                foreach(config('email-templates-platform') as $k => $v) {
                    if(!in_array($k, $params['admin']->email_template_type)) {
                        unset( $menu['emails']['subpages'][$k] );
                    }
                }
            }
        } else {
            unset($menu['emails']);
        }

        if($params['admin']->id!=1) {
            unset($menu['admins']['subpages']['messages']);
        }
        
        config([
            'admin.pages' => $menu
        ]);

        if(!empty($this->user) && $this->user->messages->isNotEmpty()) {
            $params['messages'] = $this->user->messages;
        }

        $video_reviews = Review::where('youtube_id', '!=', '')->where('youtube_approved', 0)->count();
        $support_contacts = SupportContact::whereNull('admin_answer')->whereNull('admin_answer_id')->count();

        //Counts
        $params['counters'] = [];
        $params['counters']['trp'] = $video_reviews;
        $params['counters']['youtube'] = $video_reviews;
        $params['counters']['ban_appeals'] = BanAppeal::where('status', 'new')->whereNull('pending_fields')->count();
        $params['counters']['transactions'] = DcnTransaction::where('status', 'first')->whereHas('user', function($query) {
            $query->where('status', '!=', 'pending')->whereNotIn('patient_status', ['suspicious_admin', 'suspicious_badip']);
        })->count();
        $params['counters']['support'] = $support_contacts;
        $params['counters']['contact'] = $support_contacts;
        $params['counters']['orders'] = Order::whereNull('is_send')->count();

        $params['cache_version'] = '20220228';
        
        if($this->current_page == 'admins' && $this->current_subpage == 'admins') {
        } else if($this->current_page!='home' && !isset($menu[$this->current_page])) {
            return redirect('cms');
        }

		return view('admin.'.$page, $params);
    }
}