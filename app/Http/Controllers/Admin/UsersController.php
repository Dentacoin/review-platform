<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;

use App\Models\User;
use App\Models\UserBan;
use App\Models\VoxAnswer;
use App\Models\VoxReward;
use App\Models\City;
use App\Models\Country;
use App\Models\UserCategory;
use App\Models\Review;
use App\Models\ReviewAnswer;

use Carbon\Carbon;

use Request;
use Route;
use Auth;
use DB;

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

    	$this->fields = [
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
    		],
    		'email' => [
    			'type' => 'text',
    		],
    		'phone' => [
    			'type' => 'text',
    		],
    		'is_dentist' => [
    			'type' => 'bool',
    		],
            'is_partner' => [
                'type' => 'bool',
            ],
            'is_verified' => [
                'type' => 'bool',
            ],
    		'verified_on' => [
    			'type' => 'datetimepicker',
    		],
            'phone_verified' => [
                'type' => 'bool',
            ],
            'phone_verified_on' => [
                'type' => 'datetimepicker',
            ],
            'verification_code' => [
                'type' => 'text',
            ],
            'website' => [
                'type' => 'text',
            ],
    		'country_id' => [
    			'type' => 'country',
    		],
            'city_id' => [
                'type' => 'city',
            ],
            'gender' => [
                'type' => 'select',
                'values' => $this->genders
            ],
    		'birthyear' => [
    			'type' => 'text'
    		],
    		'zip' => [
    			'type' => 'text',
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
            'register_reward' => [
                'type' => 'text',
            ],
            'register_tx' => [
                'type' => 'text',
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
            'is_approved' => [
                'type' => 'bool',
            ],
    	];
    }

    public function list() {

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
        if(!empty($this->request->input('search-id'))) {
            $users = $users->where('id', $this->request->input('search-id') );
        }

        if(!empty($this->request->input('search-register-from'))) {
            $firstday = new Carbon($this->request->input('search-register-from'));
            $users = $users->where('created_at', '>=', $firstday);
        }
        if(!empty($this->request->input('search-register-to'))) {
            $firstday = new Carbon($this->request->input('search-register-to'));
            $users = $users->where('created_at', '<=', $firstday);
        }
        if(!empty($this->request->input('search-address'))) {
            $users = $users->where('register_reward', 'LIKE', '%'.trim($this->request->input('search-address')).'%');
        }
        if(!empty($this->request->input('search-tx'))) {
            $users = $users->where('register_tx', 'LIKE', '%'.trim($this->request->input('search-tx')).'%');
        }

        $users = $users->withTrashed()->take(50)->get();

        return $this->showView('users', array(
            'users' => $users,
            'search_register_from' => $this->request->input('search-register-from'),
            'search_register_to' => $this->request->input('search-register-to'),
            'search_email' => $this->request->input('search-email'),
            'search_phone' => $this->request->input('search-phone'),
            'search_name' => $this->request->input('search-name'),
            'search_id' => $this->request->input('search-id'),
            'search_address' => $this->request->input('search-address'),
            'search_tx' => $this->request->input('search-tx'),
        ));
    }



    public function delete( $id ) {
        $item = User::find($id);

        if(!empty($item)) {
            User::destroy( $id );
        }

        $this->request->session()->flash('success-message', trans('admin.page.'.$this->current_page.'.deleted') );
        return redirect('cms/'.$this->current_page);
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

    public function delete_review( $review_id ) {
        $item = Review::find($review_id);
        
        if(!empty($item)) {
            $uid = $item->user_id;
            ReviewAnswer::where([
                ['review_id', $item->id],
            ])
            ->delete();
            $dentist = User::find($item->dentist_id);
            Review::destroy( $review_id );
            if( $dentist ) {
                $dentist->recalculateRating();
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


    public function loginas( $id ) {
        $item = User::find($id);

        if(!empty($item)) {
            Auth::login($item, true);
        }

        return redirect('/');
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

        //dd(bcrypt('my$tronGpass!'));
        //$2y$10$.AunhByiNrBlkkbAC7pG3.oTp06Lz6Z4aoRIjJmQqr95/c.Hs3akW

        if(!empty($item)) {

            if(Request::isMethod('post')) {
            	foreach ($this->fields as $key => $value) {
            		if(empty($value['disabled']) && $value['type']!='avatar' && $key!='category_id') {
                        if($value['type']=='datepicker') {
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

                Request::session()->flash('success-message', trans('admin.page.'.$this->current_page.'.updated'));
                return redirect('cms/'.$this->current_page.'/edit/'.$item->id);
            }

            return $this->showView('users-form', array(
                'item' => $item,
                'categories' => $this->categories,
                'fields' => $this->fields,
            ));
        } else {
            return redirect('cms/'.$this->current_page);
        }
    }

}
