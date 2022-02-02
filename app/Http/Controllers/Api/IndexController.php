<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;

use App\Services\VoxService as ServicesVox;

use App\Models\UserDevice;
use App\Models\PollAnswer;
use App\Models\VoxAnswer;
use App\Models\Country;
use App\Models\User;
use App\Models\Poll;

use App\Helpers\GeneralHelper;
use Carbon\Carbon;

use Validator;
use Response;
use Request;
use Image;
use Auth;
use Lang;

class IndexController extends ApiController {
    
	public function headerStats() {

        $user = Auth::guard('api')->user();
        // $user = User::find(37530);

		$todays_daily_poll = Poll::where('launched_at', date('Y-m-d') )->first();
		$poll_type = null;

		if(!empty($todays_daily_poll)) {

			if($todays_daily_poll->status == 'open') {

		        $restrictions = false;
		            
	            if(!empty($user) && !empty($user->country_id)) {
	                $restrictions = $todays_daily_poll->isPollRestricted($user->country_id);

	            } else {

	                $country_code = strtolower(\GeoIP::getLocation(User::getRealIp())->iso_code);
	                $country_db = Country::where('code', 'like', $country_code)->first();

	                if (!empty($country_db)) {
	                    $restrictions = $todays_daily_poll->isPollRestricted($country_db->id);
	                }
	            }

	            if($restrictions) {
	                $poll_type = 'stats';
	            } else {

					$poll_type = 'current';

		            if(!empty($user)) {
		                $is_taken = PollAnswer::where('poll_id', $todays_daily_poll->id)->where('user_id', $user->id)->first();

		                if ($is_taken) {
		        			$poll_type = 'stats';
		                }
		            }
	            }

			} else if($todays_daily_poll->status == 'scheduled') {

			} else {
				$poll_type = 'stats';
			}
		}

		return Response::json( array(
			'translations_android' => Lang::get('vox', array(), 'en'),
			'translations_ios' => Lang::get('vox-ios', array(), 'en'),
			'users_count' => User::getCount('vox'),
			'answers_count' => VoxAnswer::getCount(),
			'dcn_price' => @file_get_contents('/tmp/dcn_price'),
			'daily_poll' => $todays_daily_poll ? $todays_daily_poll->id : null,
			'poll_type' => $poll_type,
			'user' => $user,
			'app_version_android' => '1.0.0',
			'app_version_ios' => '1.0.0',
		) );
    }

	public function indexVoxes() {

		return Response::json( array(
			'voxes' => ServicesVox::featuredVoxes(),
		) );
    }

	public function getBanTimeLeft() {

        $user = Auth::guard('api')->user();
        // $user = User::find(37530);

		$current_ban = $user->isBanned('vox');

		$time_left = null;

		if($current_ban && $current_ban->expires ) {
            $now = Carbon::now();
            $time_left = $current_ban->expires->diffInHours($now).':'.
            str_pad($current_ban->expires->diffInMinutes($now)%60, 2, '0', STR_PAD_LEFT).':'.
            str_pad($current_ban->expires->diffInSeconds($now)%60, 2, '0', STR_PAD_LEFT);
		}

        return Response::json( [
        	'time_left' => $time_left,
        ]);
	}


	public function getBanInfo() {

        $user = Auth::guard('api')->user();
        // $user = User::find(37530);

        $ret = [
    		'ban' => false,
    	];

        if(!empty($user)) {
        	
	        $current_ban = $user->isBanned('vox');

	        if($current_ban) {

	        	if($current_ban->type == 'mistakes') {

					$prev_bans = $user->getPrevBansCount('vox', 'mistakes');

					$days = 0;
			        if($prev_bans==1) {
			            $days = 1;
			        } else if($prev_bans==2) {
			            $days = 3;
			        } else if($prev_bans==3) {
			            $days = 7;
			        }

					$ret['ban'] = true;
					$ret['ban_duration'] = $days;
					$ret['ban_times'] = $prev_bans;
					$ret['img'] = url('new-vox-img/ban'.($prev_bans).'.png');
					$titles = [
						trans('vox.page.bans.ban-mistakes-title-1'),
						trans('vox.page.bans.ban-mistakes-title-2'),
						trans('vox.page.bans.ban-mistakes-title-3'),
						trans('vox.page.bans.ban-mistakes-title-4', [
							'name' => $user->getNames()
						]),
					];
					$ret['title'] = $titles[$prev_bans - 1];
					$contents = [
						trans('vox.page.bans.ban-mistakes-content-1'),
						trans('vox.page.bans.ban-mistakes-content-2'),
						trans('vox.page.bans.ban-mistakes-content-3'),
						trans('vox.page.bans.ban-mistakes-content-4'),
					];
					$ret['content'] = $contents[$prev_bans - 1];

	        	} else if($current_ban->type == 'too-fast') {

					$prev_bans = $user->getPrevBansCount('vox', 'too-fast');

					$days = 0;
			        if($prev_bans==1) {
			            $days = 1;
			        } else if($prev_bans==2) {
			            $days = 3;
			        } else if($prev_bans==3) {
			            $days = 7;
			        }

					$ret['ban'] = true;
					$ret['ban_duration'] = $days;
					$ret['ban_times'] = $prev_bans;
					$ret['img'] = url('new-vox-img/ban'.($prev_bans).'.png');
					$titles = [
						trans('vox.page.bans.ban-too-fast-title-1'),
						trans('vox.page.bans.ban-too-fast-title-2'),
						trans('vox.page.bans.ban-too-fast-title-3'),
						trans('vox.page.bans.ban-too-fast-title-4',[
							'name' => $user->getNames()
						]),
					];
					$ret['title'] = $titles[$prev_bans - 1];
					$contents = [
						trans('vox.page.bans.ban-too-fast-content-1'),
						trans('vox.page.bans.ban-too-fast-content-2'),
						trans('vox.page.bans.ban-too-fast-content-3'),
						trans('vox.page.bans.ban-too-fast-content-4'),
					];
					$ret['content'] = $contents[$prev_bans - 1];
	        	}
	        }
        }

        return Response::json( $ret );
    }

    public function encryptUserToken() {

    	if(!empty(request('token'))) {
    		return Response::json( [
    			'token' => GeneralHelper::encrypt(request('token')),
	        	'success' => true,
	        ] );
    	}

        return Response::json( [
        	'success' => false,
        ] );
    }

    public function isDentacoinDown() {

        $host = 'dentacoin.com';

        if($socket =@ fsockopen($host, 80, $errno, $errstr, 30)) {
            fclose($socket);

            return Response::json( [
                'success' => false,
            ] );
        } else {
            return Response::json( [
                'success' => true,
            ] );
        }
    }

    public function isOnline() {

        return Response::json( [
            'success' => true,
        ] );
    }

    public function saveUserDevice() {

    	if(request('token')) {

    		$token = request('token');
    		$existing_device = UserDevice::where('device_token', $token)->first();

    		if(!empty($existing_device)) {
    			$existing_device->delete();
    		}

			$new_device = new UserDevice;
			$new_device->user_id = Auth::guard('api')->user() ? Auth::guard('api')->user()->id : null;
			$new_device->device_token = $token;
			$new_device->save();

	    	return Response::json( [
	            'success' => true,
	        ] );
    	}

    	return Response::json( [
            'success' => false,
        ] );

    }

    public function socialProfileImage() {

    	if(!empty(request('userId'))) {
	    	$user = GeneralHelper::decrypt(request('userId')) ? User::find(GeneralHelper::decrypt(request('userId'))) : null;

	    	if(!empty($user)) {

				$extensions = ['png', 'jpg', 'jpeg'];

				$path = $_FILES['avatar']['name'];
				$ext = pathinfo($path, PATHINFO_EXTENSION);
		
				if (!in_array($ext, $extensions)) {
					return Response::json( [
						'success' => false,
					]);
				}

				$user->addImage(Image::make( request()->file('avatar') )->orientate());

		    	return Response::json( [
		            'success' => true,
		        ]);
	    	}
    	}

	    return Response::json( [
            'success' => false,
        ]);
    }

    public function socialProfileLink() {

    	if(!empty($this->user)) {

    		request()->merge([
                'link' => strtolower(request('link'))
            ]);

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

                $this->user->website = Request::input('link');
                $this->user->save();

                return Response::json( [
                    'success' => true,
                ] );
            }
        }

	    return Response::json( [
            'success' => false,
        ] );
    }

	public function apiLogUser() {

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_POST => 1,
            CURLOPT_URL => 'https://api.dentacoin.com/api/log-user/',
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_POSTFIELDS => array(
                'slug' => Request::input('slug')
            )
        ));

        $resp = json_decode(curl_exec($curl));
        curl_close($curl);

        if(!empty($resp))   {
			if($resp->success) {
				return Response::json( [
                    'success' => true,
					'user_token' => $resp->token,
                ] );
			}
        }else {
            return Response::json( [
				'success' => false,
			] );
        }
    }

}