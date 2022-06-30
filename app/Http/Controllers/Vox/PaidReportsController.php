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
use Route;
use Mail;

class PaidReportsController extends FrontController {

    public function __construct(\Illuminate\Http\Request $request, Route $route, $locale=null) {
        parent::__construct($request, $route, $locale);

		$this->europeanUnionCountries = [
			15, //Austria
			22, //Belgium
			34, //Bulgaria
			55, //Croatia
			57, //Cyprus
			58, //Czech Republic
			59, //Denmark
			68, //Estonia
			73, //Finland
			74, //France
			81, //Germany
			84, //Greece
			99, //Hungary
			105, //Ireland
			108, //Italy
			120, //Latvia
			126, //Lithuania
			127, //Luxembourg
			135, //Malta
			175, //Poland
			176, //Portugal
			180, //Romania
			199, //Slovakia
			200, //Slovenia
			205, //Spain
			211 //Sweden
		];

		$this->alwaysWithVATCountries = [
			154
		];
    }
    
	/**
     * All paid reports page
     */
	public function home($locale=null) {

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

		$item = PaidReport::whereTranslationLike('slug', $slug)
		->where('status', 'published')
		->first();

		if(empty($item)) {
			return redirect( getLangUrl('page-not-found') );
		}

		$seos = PageSeo::find(37);

		$seo_title = str_replace([':title', ':main_title'], [$item->title, $item->main_title], $seos->seo_title);
        $seo_description = str_replace([':title', ':main_title'], [$item->title, $item->main_title], $seos->seo_description);
        $social_title = str_replace([':title', ':main_title'], [$item->title, $item->main_title], $seos->social_title);
        $social_description = str_replace([':title', ':main_title'], [$item->title, $item->main_title], $seos->social_description);

		$view_params = [
			'item' => $item,
            'css' => [
            	'vox-paid-reports.css'
            ],
            'js' => [
            	'paid-reports.js'
            ],
			'social_image' => $item->getImageUrl('social'),
            'seo_title' => $seo_title,
            'seo_description' => $seo_description,
            'social_title' => $social_title,
            'social_description' => $social_description,
			'canonical' => getLangUrl('dental-industry-reports/'.$slug),
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
		
		$item = PaidReport::whereTranslationLike('slug', $slug)
		->where('status', 'published')
		->first();
		
		if(empty($item)) {
			return redirect( getLangUrl('page-not-found') );
		}
		
		if(Request::isMethod('post')) {

			if(config('trp.without_admins_check') && date('d.m.Y') > '28.06.2022') {
				return Response::json([
					'success' => false,
					'message' => array(
						'agree' => 'This feature is temporaty unavailable. For more information, get in touch with: admin@dentacoin.com'
					)
				]);
			}

			$validator = Validator::make(Request::all(), [
                'name' => array('required', 'min:3'),
                'invoice' => array('required'),
                'company-country' => array('required'),
                'vat' => array('required_if:invoice,yes'),
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
					return Response::json([
						'success' => false,
						'messages' => array(
							'agree' => 'The agree field is required.'
						)
					]);
				}

				

				$withVAT = false;

				if(
					in_array(request('company-country'), $this->alwaysWithVATCountries )
					|| (in_array(request('company-country'), $this->europeanUnionCountries ) && request('invoice') == 'yes' && request('vat') == 'no')
				) {
					$withVAT = true;
				}
				
				$price = null;

				$itemPrice = $withVAT ? ($item->price + ($item->price * 0.21)) : $item->price;

				if(request('payment-method') != 'paypal') {

					$curl = curl_init();
					curl_setopt_array($curl, array(
						CURLOPT_RETURNTRANSFER => 1,
						CURLOPT_URL => "https://api.coingecko.com/api/v3/coins/".(request('payment-method') == 'ether' ? 'ethereum' : request('payment-method')),
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

				$only_price = $price ? sprintf('%.7F', $itemPrice / $price) : $itemPrice;
				$only_price = $only_price > 1 ? round($only_price) : $only_price;
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
				$new_order->country_id = request('company-country');
				$new_order->price = $only_price;
				$new_order->price_with_currency = $price_with_currency;
				$new_order->invoice = request('invoice') == 'yes' ? 1 : null;
				$new_order->company_vat = request('vat') == 'yes' ? 1 : null;
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
						$message->to( 'petya.ivanova@dentacoin.com' );
						$message->subject('New Paid Report Order');
						$message->setBody($mtext, 'text/html'); // for HTML rich messages
					});
				}

			    return Response::json( [
					'success' => true,
					'link' => getLangUrl('dental-industry-reports/'.$slug.'/payment/'.$new_order->id)
				]);
			}
		} else {
			$order = null;
			if(!empty(session('report_order'))) {
				$order = Order::find(session('report_order'));
			}
		}

		$seos = PageSeo::find(38);
		$seo_title = str_replace([':title', ':main_title'], [$item->title, $item->main_title], $seos->seo_title);
        $seo_description = str_replace([':title', ':main_title'], [$item->title, $item->main_title], $seos->seo_description);
        $social_title = str_replace([':title', ':main_title'], [$item->title, $item->main_title], $seos->social_title);
        $social_description = str_replace([':title', ':main_title'], [$item->title, $item->main_title], $seos->social_description);

		return $this->ShowVoxView('research-report-checkout', array(
			'social_image' => $item->getImageUrl('social'),
            'seo_title' => $seo_title,
            'seo_description' => $seo_description,
            'social_title' => $social_title,
            'social_description' => $social_description,
            'countries' => Country::with('translations')->get(),
			'canonical' => getLangUrl('dental-industry-reports/'.$slug),
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
				$order->address = request('address');
				$order->vat = request('vat');
				$order->save();

			    return Response::json( [
					'success' => true,
					// 'link' => url('cms/dental-industry-reports/'.$slug.'/payment/'.$new_order->id)
				] );
			}
		}

		$infoText = '';
		
		if(
			(in_array($order->country_id, $this->alwaysWithVATCountries ) && $order->invoice)
			|| (in_array($order->country_id, $this->europeanUnionCountries ) && $order->invoice && !$order->company_vat)
			) {
			// На следващата страница излиза цена с VAT плюс място за данни за фактура, ако:
			// са избрали Netherlands и искат фактура
			// са избрали някоя друга ЕС държава* и са казали, че искат фактура, НО нямат VAT номер
			$infoText = trans('vox.page.paid-reports.payment-info-with-invoice', [
				'email' => '<b>'.$order->email.'</b>',
			]);

		} else if(in_array($order->country_id, $this->alwaysWithVATCountries ) && !$order->invoice) {

			// На следващата страница излиза цена с VAT без място за данни за фактура, ако:
			// са избрали Netherlands и не искат фактура

			$infoText = trans('vox.page.paid-reports.payment-info-netherlands-without-invoice', [
				'email' => '<b>'.$order->email.'</b>',
			]);

		} else if(
			($order->invoice && !$order->company_vat)
			|| (in_array($order->country_id, $this->europeanUnionCountries ) && $order->invoice && $order->company_vat)
		) {
			// На следващата страница излиза цена без VAT плюс място за данни за фактура, ако:
			// са избрали, която и да е друга държава, искат фактура, но нямат VAT
			// са избрали някоя друга ЕС държава* и са казали, че искат фактура и имат VAT

			$infoText = trans('vox.page.paid-reports.payment-info-with-invoice-no-vat', [
				'email' => '<b>'.$order->email.'</b>',
			]);

		} else if(!$order->invoice) {

			// На следващата страница излиза цена без VAT без място за данни за фактура, ако:
			// са избрали някоя друга ЕС държава* и са казали, че НЕ искат фактура
			// са избрали, която и да е друга държава и не искат фактура

			$infoText = trans('vox.page.paid-reports.payment-info-without-invoice', [
				'email' => '<b>'.$order->email.'</b>',
			]);
		}

		$seos = PageSeo::find(39);
		$seo_title = str_replace([':title', ':main_title'], [$item->title, $item->main_title], $seos->seo_title);
        $seo_description = str_replace([':title', ':main_title'], [$item->title, $item->main_title], $seos->seo_description);
        $social_title = str_replace([':title', ':main_title'], [$item->title, $item->main_title], $seos->social_title);
        $social_description = str_replace([':title', ':main_title'], [$item->title, $item->main_title], $seos->social_description);

		return $this->ShowVoxView('research-report-payment', array(
			'social_image' => $item->getImageUrl('social'),
            'seo_title' => $seo_title,
            'seo_description' => $seo_description,
            'social_title' => $social_title,
            'social_description' => $social_description,
			'canonical' => getLangUrl('dental-industry-reports/'.$slug),
			'infoText' => $infoText,
			'order' => $order,
			'item' => $item,
            'css' => [
            	'vox-paid-reports.css'
            ],
            'js' => [
            	'paid-reports.js'
            ],
		));
	}
}