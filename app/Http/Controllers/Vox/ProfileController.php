<?php

namespace App\Http\Controllers\Vox;

use App\Http\Controllers\FrontController;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

use Illuminate\Support\Facades\Input;

use App\Models\DcnCashout;
use App\Models\UserInvite;
use App\Models\DcnReward;
use App\Models\Country;
use App\Models\Civic;
use App\Models\User;
use App\Models\Vox;
use App\Models\Dcn;

use Carbon\Carbon;

use Validator;
use Response;
use Request;
use Image;
use Route;
use Hash;
use Mail;
use Auth;
use App;

class ProfileController extends FrontController {

    public function vox($locale=null) {

        if(!empty($this->user)) {

            if($this->user->is_dentist && $this->user->status!='approved' && $this->user->status!='added_by_clinic_claimed' && $this->user->status!='added_by_dentist_claimed' && $this->user->status!='test') {
                return redirect(getLangUrl('/'));
            }

            $current_ban = $this->user->isBanned('vox');
            $prev_bans = null; 
            $time_left = '';

            $ban_reason = '';
            $ban_alternatives = '';
            $ban_alternatives_buttons = '';

            if( $current_ban ) {

                $prev_bans = $this->user->getPrevBansCount('vox', $current_ban->type);
                if($current_ban->type=='mistakes') {
                    $ban_reason = trans('vox.page.bans.banned-mistakes-title-'.$prev_bans);
                } else {
                    $ban_reason = trans('vox.page.bans.banned-too-fast-title-'.$prev_bans);
                }

                if($prev_bans==1) {
                    $ban_alternatives = trans('vox.page.bans.banned-alternative-1');
                    $ban_alternatives_buttons = '
                    <a href="https://dentacare.dentacoin.com/" target="_blank">
                        <img src="'.url('new-vox-img/bans-dentacare.png').'" />
                    </a>';
                } else if($prev_bans==2) {
                    $ban_alternatives = trans('vox.page.bans.banned-alternative-2');
                    $ban_alternatives_buttons = '
                    <a href="https://reviews.dentacoin.com/" target="_blank">
                        <img src="'.url('new-vox-img/bans-trp.png').'" />
                    </a>';
                } else if($prev_bans==3) {
                    $ban_alternatives = trans('vox.page.bans.banned-alternative-3');
                    $ban_alternatives_buttons = '
                    <a href="https://dentacare.dentacoin.com/" target="_blank">
                        <img src="'.url('new-vox-img/bans-dentacare.png').'" />
                    </a>';
                } else {
                    $ban_alternatives = trans('vox.page.bans.banned-alternative-4');
                    $ban_alternatives_buttons = '
                    <a href="https://dentacare.dentacoin.com/" target="_blank">
                        <img src="'.url('new-vox-img/bans-dentacare.png').'" />
                    </a>
                    <a href="https://reviews.dentacoin.com/" target="_blank">
                        <img src="'.url('new-vox-img/bans-trp.png').'" />
                    </a>';
                }

                if( $current_ban->expires ) {
                    $now = Carbon::now();
                    $time_left = $current_ban->expires->diffInHours($now).':'.
                    str_pad($current_ban->expires->diffInMinutes($now)%60, 2, '0', STR_PAD_LEFT).':'.
                    str_pad($current_ban->expires->diffInSeconds($now)%60, 2, '0', STR_PAD_LEFT);
                } else {
                    $time_left = null;
                }
            }

            $more_surveys = false;
            $rewards = DcnReward::where('user_id', $this->user->id)->where('platform', 'vox')->where('type', 'survey')->where('reference_id', '!=', 34)->get();
            
            if ($rewards->count() == 1 && $rewards->first()->vox_id == 11) {
                $more_surveys = true;
            }
            

            $params = [
                'xframe' => true,
                'latest_voxes' => Vox::where('type', 'normal')->with('translations')->with('categories.category')->with('categories.category.translations')->orderBy('created_at', 'desc')->take(3)->get(),
                'more_surveys' => $more_surveys,
                'prev_bans' => $prev_bans,
                'current_ban' => $current_ban,
                'ban_reason' => $ban_reason,
                'ban_alternatives' => $ban_alternatives,
                'ban_alternatives_buttons' => $ban_alternatives_buttons,
                'time_left' => $time_left,
                'histories' => DcnReward::where('user_id', $this->user->id)->where('platform', 'vox')->where('type', 'survey')->where('reference_id', '!=', 34)->paginate(10),
                'js' => [
                    'profile.js',
                    'swiper.min.js'
                ],
                'csscdn' => [
                    'https://fonts.googleapis.com/css?family=Lato:700&display=swap&subset=latin-ext',
                ],
                'css' => [
                    'vox-profile.css',
                    'swiper.min.css'
                ],
            ];

            $path = explode('/', request()->path())[2];
            if ($path == 'vox-iframe') {
                $params['skipSSO'] = true;
            }

            return $this->ShowVoxView('profile-vox', $params);
        }
        
        return null;
    }

    private function paginate($items, $perPage = 10, $page = null, $options = []) {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }
}