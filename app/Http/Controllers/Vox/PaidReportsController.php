<?php

namespace App\Http\Controllers\Vox;

use App\Http\Controllers\FrontController;

use App\Models\PaidReport;
use App\Models\PageSeo;
use App\Models\Country;
use App\Models\Order;

use Validator;
use Response;
use Request;

class PaidReportsController extends FrontController {
    
	public function home($locale=null) {

		if(empty($this->admin)) {
			return redirect( getLangUrl('page-not-found') );
		}

		$seos = PageSeo::find(36);

		$item = PaidReport::where('status', 'published')->orderBy('launched_at', 'desc')->first();

		return $this->ShowVoxView('research-reports', array(
			'social_image' => $seos->getImageUrl(),
            'seo_title' => $seos->seo_title,
            'seo_description' => $seos->seo_description,
            'social_title' => $seos->social_title,
            'social_description' => $seos->social_description,
			'item' => $item,
            'css' => [
            	'vox-paid-reports.css'
            ],
		));	
	}
    
	public function singleReport($locale=null, $slug) {

		if(empty($this->admin)) {
			return redirect( getLangUrl('page-not-found') );
		}

		$seos = PageSeo::find(37);

		$item = PaidReport::whereTranslationLike('slug', $slug)->where('status', 'published')->first();
		if(empty($item)) {
			return redirect( getLangUrl('page-not-found') );
		}

		$view_params = [
			'item' => $item,
            'css' => [
            	'vox-paid-reports.css'
            ],
            'js' => [
            	'paid-reports.js'
            ],
			'social_image' => $seos->getImageUrl(),
            'seo_title' => $seos->seo_title,
            'seo_description' => $seos->seo_description,
            'social_title' => $seos->social_title,
            'social_description' => $seos->social_description,
		];

		if($item->photos->isNotEmpty()) {
			$view_params['css'][] = 'lightbox.css';
			$view_params['js'][] = '../js/lightbox.js';
		}

		return $this->ShowVoxView('research-report-single', $view_params);	
	}
    
	public function reportCheckout($locale=null, $slug) {

		if(empty($this->admin)) {
			return redirect( getLangUrl('page-not-found') );
		}

		$seos = PageSeo::find(38);

		$item = PaidReport::whereTranslationLike('slug', $slug)->where('status', 'published')->first();
		if(empty($item)) {
			return redirect( getLangUrl('page-not-found') );
		}

		if(Request::isMethod('post')) {
			$validator = Validator::make(Request::all(), [
                'email' => array('required', 'email'),
                'email-confirm' => array('required', 'email', 'same:email'),
                'payment-method' =>  array('required', 'in:crypto,paypal'),
				'agree' =>  array('required', 'accepted'),
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

				$new_order = new Order;
				if(!empty($this->user)) {
					$new_order->user_id = $this->user->id;
				}
				$new_order->paid_report_id = $item->id;
				$new_order->email = request('email');
				$new_order->payment_method = request('payment-method');
				$new_order->price = $item->price;
				$new_order->save();

			    return Response::json( [
					'success' => true,
					'link' => getLangUrl('dental-industry-reports/'.$slug.'/payment/'.$new_order->id)
				] );
			}
		}

		return $this->ShowVoxView('research-report-checkout', array(
			'social_image' => $seos->getImageUrl(),
            'seo_title' => $seos->seo_title,
            'seo_description' => $seos->seo_description,
            'social_title' => $seos->social_title,
            'social_description' => $seos->social_description,
			'item' => $item,
            'css' => [
            	'vox-paid-reports.css'
            ],
            'js' => [
            	'paid-reports.js'
            ],
		));	
	}

    public function reportPayment($locale=null, $slug, $order_id) {

		if(empty($this->admin)) {
			return redirect( getLangUrl('page-not-found') );
		}

		$item = PaidReport::whereTranslationLike('slug', $slug)->where('status', 'published')->first();
		$order = Order::find($order_id);

		if(empty($item) || empty($order)) {
			return redirect( getLangUrl('page-not-found') );
		}

		if(Request::isMethod('post')) {
			$validator = Validator::make(Request::all(), [
                'company-name' => array('required'),
                'company-number' => array('required'),
                'company-country' =>  array('required'),
				'address' =>  array('required'),
				'vat' =>  array('required'),
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

				$order->company_name = request('company-name');
				$order->company_number = request('company-number');
				$order->country_id = request('company-country');
				$order->address = request('address');
				$order->vat = request('vat');
				$order->save();

				$mtext = 'New order ID '.$order->id.': <br/>
				Link in CMS: https://reviews.dentacoin.com/cms/orders/';

				Mail::send([], [], function ($message) use ($mtext) {
					$sender = config('mail.from.address');
					$sender_name = config('mail.from.name');

					$message->from($sender, $sender_name);
					$message->to( 'dentavox@dentacoin.com' );
					$message->to( 'donika.kraeva@dentacoin.com' );
					$message->subject('New Paid Report Order');
					$message->setBody($mtext, 'text/html'); // for HTML rich messages
				});

			    return Response::json( [
					'success' => true,
					// 'link' => url('cms/dental-industry-reports/'.$slug.'/payment/'.$new_order->id)
				] );
			}
		}

		$seos = PageSeo::find(39);

		return $this->ShowVoxView('research-report-payment', array(
			'social_image' => $seos->getImageUrl(),
            'seo_title' => $seos->seo_title,
            'seo_description' => $seos->seo_description,
            'social_title' => $seos->social_title,
            'social_description' => $seos->social_description,
			'order' => $order,
			'item' => $item,
            'countries' => Country::with('translations')->get(),
            'css' => [
            	'vox-paid-reports.css'
            ],
            'js' => [
            	'paid-reports.js'
            ],
		));	
	}  

    


}