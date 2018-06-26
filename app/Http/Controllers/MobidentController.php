<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use App;
use Request;
use Response;

use Carbon\Carbon;

use App\Models\Dcn;
use App\Models\Mobident;

class MobidentController extends BaseController
{
    public function reward() {
        $ret = [
            'success' => false
        ];

        if(Request::isMethod('post')) {
            if( 
                Request::input('token') && 
                Request::input('dcn_address') && 
                Request::input('name') && 
                Request::input('city') && 
                Request::input('address') && 
                Request::input('email') 
            ) {
                $generated_token = md5( Request::input('dcn_address') . 'dcn' . Request::input('name') . 'dcn' . Request::input('city') . 'dcn' . Request::input('address') . 'dcn' . Request::input('email') );
                if( Request::input('token') == $generated_token ) {

                    $prev = Mobident::where( 'created_at', '>', Carbon::now()->addDays(-10) )->count();
                    if($prev>20) {
                        $ret['message'] = 'Quota exceeded';
                    } else {
                        $md = new Mobident;
                        $md->city = Request::input('city');
                        $md->name = Request::input('name');
                        $md->email = Request::input('email');
                        $md->address = Request::input('address');
                        $md->save();

                        $ret = Dcn::send('mobident', Request::input('dcn_address'), 150, 'mobident', $md->id);          
                    }

                } else {
                    $ret['message'] = 'Invalid token';        
                }

            } else {
                $ret['message'] = 'Missing parameters';    
            }
        } else {
            $ret['message'] = 'Invalid method';
        }

        return Response::json($ret);
    }    

}