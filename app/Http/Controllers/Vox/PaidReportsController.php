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
    
	/**
     * All paid reports page
     */
	public function home($locale=null) {

		if(empty($this->admin)) {
			return redirect( getLangUrl('page-not-found') );
		}

		$seos = PageSeo::find(36);

		$item = PaidReport::where('status', 'published')
		->orderBy('launched_at', 'desc')
		->first();
		
		$items = PaidReport::where('status', 'published')
		->orderBy('launched_at', 'desc')
		->where('id', '!=', $item->id)
		->get();

		return $this->ShowVoxView('research-reports', array(
			'social_image' => $seos->getImageUrl(),
            'seo_title' => $seos->seo_title,
            'seo_description' => $seos->seo_description,
            'social_title' => $seos->social_title,
            'social_description' => $seos->social_description,
			'item' => $item,
			'items' => $items,
            'css' => [
            	'vox-paid-reports.css'
            ],
            'js' => [
            	'paid-reports.js'
            ],
		));	
	}
    
	/**
     * Single report page
     */
	public function singleReport($locale=null, $slug) {

		if(empty($this->admin)) {
			return redirect( getLangUrl('page-not-found') );
		}

		$seos = PageSeo::find(37);

		$item = PaidReport::whereTranslationLike('slug', $slug)
		->where('status', 'published')
		->first();

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
    
	/**
     * Paid report checkout page
     */
	public function reportCheckout($locale=null, $slug) {

		if(empty($this->admin)) {
			return redirect( getLangUrl('page-not-found') );
		}

		$seos = PageSeo::find(38);

		$item = PaidReport::whereTranslationLike('slug', $slug)
		->where('status', 'published')
		->first();

		if(empty($item)) {
			return redirect( getLangUrl('page-not-found') );
		}

		if(Request::isMethod('post')) {
			$validator = Validator::make(Request::all(), [
                'name' => array('required', 'min:3'),
                'email' => array('required', 'email'),
                'email-confirm' => array('required', 'email', 'same:email'),
                'payment-method' =>  array('required', 'in:'.implode(',',array_keys(config('payment-methods')))),
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

				if(empty($this->user) && empty(request('agree'))) {
					$ret['messages']['agree'] = 'The agree field is required.';
				}

                return Response::json( $ret );
            } else {
				
				if(empty($this->user) && empty(request('agree'))) {
					$ret = array(
						'success' => false,
						'messages' => array(
							'agree' => 'The agree field is required.'
						)
					);

					return Response::json( $ret );
				}
					
				$price = null;

				if(request('payment-method') != 'paypal') {

					$curl = curl_init();
					curl_setopt_array($curl, array(
						CURLOPT_RETURNTRANSFER => 1,
						CURLOPT_URL => "https://api.coingecko.com/api/v3/coins/".(request('payment-method') == 'ether' ? 'etherium' : request('payment-method')),
						CURLOPT_SSL_VERIFYPEER => 0
					));
					curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
					$resp = json_decode(curl_exec($curl));
					curl_close($curl);
					if(!empty($resp))   {
						if(!empty($resp->market_data->current_price->usd))  {
							$price = floatval($resp->market_data->current_price->usd);
						}
					}
				}

				$only_price = $price ? sprintf('%.0F', $item->price / $price) : $item->price;
				$price_with_currency = (request('payment-method') == 'paypal' ? '$ ' : '' ).$only_price.(request('payment-method') != 'paypal' ? ' '.strtoupper($resp->symbol) : '' );

				if(empty(session('report_order'))) {
					$new_order = new Order;
				} else {
					$new_order = Order::find(session('report_order'));
				}

				if(!empty($this->user)) {
					$new_order->user_id = $this->user->id;
				}
				$new_order->paid_report_id = $item->id;
				$new_order->email = request('email');
				$new_order->name = request('name');
				$new_order->payment_method = request('payment-method');
				$new_order->price = $only_price;
				$new_order->price_with_currency = $price_with_currency;
				$new_order->save();

				if(empty(session('report_order'))) {
					session([
						'report_order' => $new_order->id
					]);

					$mtext = 'New order ID '.$new_order->id.': <br/>
					Link in CMS: https://dentavox.dentacoin.com/cms/orders/';

					Mail::send([], [], function ($message) use ($mtext) {
						$sender = config('mail.from.address');
						$sender_name = config('mail.from.name');

						$message->from($sender, $sender_name);
						$message->to( 'dentavox@dentacoin.com' );
						$message->to( 'donika.kraeva@dentacoin.com' );
						$message->to( 'petya.ivanova@dentacoin.com' );
						$message->subject('New Paid Report Order');
						$message->setBody($mtext, 'text/html'); // for HTML rich messages
					});
				}

			    return Response::json( [
					'success' => true,
					'link' => getLangUrl('dental-industry-reports/'.$slug.'/payment/'.$new_order->id)
				] );
			}
		} else {
			$order = null;
			if(!empty(session('report_order'))) {
				$order = Order::find(session('report_order'));
			}
		}

		return $this->ShowVoxView('research-report-checkout', array(
			'social_image' => $seos->getImageUrl(),
            'seo_title' => $seos->seo_title,
            'seo_description' => $seos->seo_description,
            'social_title' => $seos->social_title,
            'social_description' => $seos->social_description,
			'item' => $item,
			'order' => $order,
            'css' => [
            	'vox-paid-reports.css'
            ],
            'js' => [
            	'paid-reports.js'
            ],
		));	
	}

	/**
     * Paid report payment page
     */
    public function reportPayment($locale=null, $slug, $order_id) {

		if(empty($this->admin)) {
			return redirect( getLangUrl('page-not-found') );
		}

		$item = PaidReport::whereTranslationLike('slug', $slug)
		->where('status', 'published')
		->first();

		$order = Order::find($order_id);

		if(empty($item) || empty($order) || empty(session('report_order'))) {
			return redirect( getLangUrl('page-not-found') );
		}

		if(Request::isMethod('post')) {
			$validator = Validator::make(Request::all(), [
                'company-name' => array('required'),
                'company-number' => array('required'),
                'company-country' =>  array('required'),
				'address' =>  array('required'),
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