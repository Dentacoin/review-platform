<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;

use App\Models\UserCategory;
use App\Models\ReviewAnswer;
use App\Models\VoxAnswer;
use App\Models\UserBan;
use App\Models\Country;
use App\Models\Review;
use App\Models\City;
use App\Models\User;
use App\Models\Vox;

use Carbon\Carbon;

use Request;
use Route;
use Excel;
use Auth;
use DB;

class UsersStatsController extends AdminController {

    public function list() {

        $user_types = User::groupBy('is_dentist')->select('is_dentist', DB::raw('count(*) as total'))->get();

        $dentist_partners = User::where('is_dentist', '1')->where('is_partner' , 1)->select('is_partner', DB::raw('count(*) as total'))->get();

        $user_genders = User::groupBy('gender')->select('gender', DB::raw('count(*) as total'))->get();

        $users_country = User::groupBy('country_id')->select('country_id', DB::raw('count(*) as total'))->orderBy('total', 'DESC')->get();

        return $this->showView('users-stats', array(
            'user_types' => $user_types,
            'dentist_partners' => $dentist_partners,
            'user_genders' => $user_genders,
            'users_country' => $users_country,
        ));
    }


}
