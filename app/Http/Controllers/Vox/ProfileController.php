<?php

namespace App\Http\Controllers\Vox;

use App\Http\Controllers\FrontController;

use Illuminate\Support\Facades\Input;

use App\Models\DcnReward;
use App\Models\User;
use App\Models\Vox;

use Carbon\Carbon;

use Validator;
use Response;
use Request;
use Image;
use Auth;

class ProfileController extends FrontController {

    /**
     * section DentaVox in accont.dentacoin.com
     */
    public function vox($locale=null) {

        if(empty($this->user) && !empty(request('slug'))) {
            $user_id = User::decrypt(request('slug'));

            if($user_id) {
                $user = User::find($user_id);

                if(!empty($user)) {
                    Auth::login($user);
                }
            }
        }

        if(!empty(Auth::guard('web')->user())) {

            $user = Auth::guard('web')->user();

            if($user->is_dentist && $user->status!='approved' && $user->status!='added_by_clinic_claimed' && $user->status!='added_by_dentist_claimed' && $user->status!='test') {
                return redirect(getLangUrl('/'));
            }

            $current_ban = $user->isBanned('vox');
            $prev_bans = null; 
            $time_left = '';

            $ban_reason = '';
            $ban_alternatives = '';
            $ban_alternatives_buttons = '';

            if( $current_ban ) {

                $prev_bans = $user->getPrevBansCount('vox', $current_ban->type);
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
            $rewards = DcnReward::where('user_id', $user->id)->where('platform', 'vox')->where('type', 'survey')->where('reference_id', '!=', 34)->get();
            
            if ($rewards->count() == 1 && $rewards->first()->vox_id == 11) {
                $more_surveys = true;
            }

            $os = false;
            if(isset($_SERVER['HTTP_USER_AGENT'])) {
                preg_match("/iPhone|Android|iPad|iPod|webOS/", $_SERVER['HTTP_USER_AGENT'], $matches);
                $os = current($matches);
            }

            $ios = false;
            if(Request::input('device-os') && Request::input('device-os') == 'ios') {
                $ios = true;
            }

            $params = [
                'xframe' => true,
                'latest_voxes' => $os ? Vox::where('type', 'normal')->with('translations')->with('categories.category')->with('categories.category.translations')->orderBy('created_at', 'desc')->take(3)->get() : collect(),
                'more_surveys' => $more_surveys,
                'ios' => $ios,
                'prev_bans' => $prev_bans,
                'current_ban' => $current_ban,
                'ban_reason' => $ban_reason,
                'ban_alternatives' => $ban_alternatives,
                'ban_alternatives_buttons' => $ban_alternatives_buttons,
                'time_left' => $time_left,
                'histories' => $rewards,
                'voxBans' => $user->vox_bans,
                'orders' => $user->orders,
                'js' => [
                    'profile.js',
                    '../js/swiper.min.js'
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

    /**
     * Patient uploads a profile photo
     */
    public function upload($locale=null) {

        if( Request::file('image') && Request::file('image')->isValid() ) {
            $img = Image::make( Input::file('image') )->orientate();
            $this->user->addImage($img);
            $this->user->save();

            return Response::json(['success' => true, 'thumb' => $this->user->getImageUrl(true), 'name' => '' ]);
        }
    }

    /**
     * Patient social profile form
     */
    public function socialProfile($locale=null) {

        if(!empty($this->user)) {

            if (request('link') && mb_strpos(mb_strtolower(request('link')), 'http') !== 0) {
                request()->merge([
                    'link' => 'http://'.request('link')
                ]);
            }

            $validator = Validator::make(Request::all(), [
                'link' =>  array('required', 'regex:/^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/'),
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

                if(Request::has('photo') && empty(Request::input('photo'))) {
                    return Response::json( [
                        'success' => false,
                        'without_image' => true,
                    ] );
                }

                $this->user->website = Request::input('link');
                $this->user->save();

                if( Request::input('photo') ) {
                    $img = Image::make( User::getTempImagePath( Request::input('photo') ) )->orientate();
                    $this->user->addImage($img);
                }

                return Response::json( [
                    'success' => true,
                ] );
            }
        }
    }
}