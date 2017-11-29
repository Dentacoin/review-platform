<?php

namespace App\Http\Controllers\Vox;
use App\Http\Controllers\FrontController;

use Validator;
use Response;
use Request;
use Route;
use App\Models\User;
use App\Models\Country;
use Carbon\Carbon;


class PhoneController extends FrontController
{

    public function save($locale=null) {

        $c = Country::find( Request::Input('phone_country') );
        if(!empty($c)) {
            $phone = ltrim( str_replace(' ', '', Request::Input('phone')), '0');
            $pn = $c->phone_code.' '.$phone;

            $validator = Validator::make(['phone' => $pn], [
                'phone' => ['required','phone:'.$c->code],
            ]);

            if ($validator->fails()) {
                return Response::json( ['success' => false, 'reason' => 'phone_invalid'] );
            } else {
                $this->user->country_id = Request::Input('phone_country');
                $this->user->phone = $phone;


                $other = User::where([
                    ['id', '!=', $this->user->id],
                    ['country_id', $this->user->country_id],
                    ['phone', $this->user->phone],
                ])->first();
                if(!empty($other)) {
                    return Response::json( ['success' => false, 'reason' => 'phone_taken'] );
                }

                //Send SMS
                $vc = rand(100000, 999999);
                $this->user->verification_code = $vc;
                $this->user->save();

                $sms_text = trans( 'vox.common.sms', ['code' => $this->user->verification_code] );
                $this->user->sendSMS($sms_text);

                return Response::json(['success' => true]);
            }
        } else {
            return Response::json( ['success' => false, 'message' => 'country'] );            
        }
    }

    public function check($locale=null) {

        if ($this->user->verification_code && $this->user->phone && Request::Input('code')==$this->user->verification_code) {
            $this->user->phone_verified = true;
            $this->user->phone_verified_on = Carbon::now();
            $this->user->save();
            Request::session()->flash('success-message', trans('vox.page.profile.phone-verified'));
            return Response::json(['success' => true]);
        } else {
            return Response::json( ['success' => false] );
        }
    }
}