<?php

namespace App\Models;

use Request;

use App\Models\DcnTransaction;
use App\Models\User;

class Dcn
{
    public static function send($user, $address, $amount, $type=null, $reference_id=null, $dontsend=false)
    {
        $ret = [
            'success' => false
        ];

        if($user!='mobident' && !($user->is_verified || $user->fb_id)) {
            $ret['message'] = 'Not verified';
        } else {

            $amount = intval($amount);
            if(empty($amount)) {
                $ret['message'] = trans('front.common.amount-invalid');
            } else if($amount<0) {
                $ret['message'] = trans('front.common.amount-negative');
            } else if(empty($address) || mb_strlen($address)!=42) {
                $ret['message'] = trans('front.common.address-invalid');
            } else if( $user!='mobident' && !$user->civic_id) {
                $ret['message'] = trans('front.common.no-civic');
            } else if( $user!='mobident' && !$user->canIuseAddress($address)) {
                $ret['message'] = trans('front.common.address-used');
            } else {
                $ret['valid_input'] = true;

                $transaction = new DcnTransaction;
                $transaction->user_id = $user!='mobident' ? $user->id : 0;
                $transaction->amount = $amount;
                $transaction->address = $address;
                $transaction->type = $type;
                $transaction->status = 'new';
                $transaction->reference_id = $reference_id;
                $transaction->save();

                if($dontsend) {
                    return true;
                }

                if(User::isGasExpensive()) {
                    return $ret;
                }

                $post = array(
                    "address" => $address,
                    "amount" => $amount,
                    "token" => md5( $address . 'dcn'.$amount.'dentacoin' )
                );
                $ch = curl_init('https://dentacoin.net/dcn');
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt ($ch, CURLOPT_POST, 1);
                curl_setopt ($ch, CURLOPT_POSTFIELDS, http_build_query($post));
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);    
                curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt ($ch, CURLOPT_TIMEOUT, 60);

                $transaction->status = 'failed';
                $response = curl_exec($ch);
                curl_close($ch);
                if($response) {
                    $api_response = json_decode($response, true);
                    if(!empty($api_response)) {
                        $ret['success'] = !empty($api_response['success']) ? $api_response['success'] : false;
                        $ret['message'] = !empty($api_response['message']) ? $api_response['message'] : '';
                        if(!empty($api_response['success'])) {
                            $ret['link'] = $api_response['link'];
                            $transaction->status = 'unconfirmed';
                            $transaction->tx_hash = !empty($api_response['message']) ? $api_response['message'] : '';
                        } else {
                            $transaction->message = !empty($api_response['message']) ? $api_response['message'] : '';
                        }
                    } else {
                        $ret['message'] = trans('front.common.network-error');
                        $transaction->message = 'Response received, but not JSON - '.$response;
                    }
                } else {
                    $ret['message'] = trans('front.common.network-error');
                    $transaction->message = 'No Response';
                }
                
                $transaction->save();
            }
        }

        return $ret;
    }

    public static function retry(&$transaction) {

        $post = array(
            "address" => $transaction->address,
            "amount" => $transaction->amount,
            "token" => md5( $transaction->address . 'dcn' . $transaction->amount . 'dentacoin' )
        );
        $ch = curl_init('https://dentacoin.net/dcn');
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt ($ch, CURLOPT_POST, 1);
        curl_setopt ($ch, CURLOPT_POSTFIELDS, http_build_query($post));
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);    
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($ch, CURLOPT_TIMEOUT, 60);

        $transaction->retries = $transaction->status=='failed' ? intval($transaction->retries)+1 : 0;
        $transaction->status = 'failed';
        $response = curl_exec($ch);
        curl_close($ch);
        if($response) {
            $api_response = json_decode($response, true);
            if(!empty($api_response)) {
                if(!empty($api_response['success'])) {
                    $transaction->status = 'unconfirmed';
                    $transaction->tx_hash = $api_response['message'];
                    $transaction->message = '';
                } else {
                    $transaction->message = $api_response['message'];
                }
            } else {
                $transaction->message = 'Response received, but not JSON - '.$response;
            }
        } else {
            $transaction->message = 'No Response';
        }

        $transaction->save();
    }

}