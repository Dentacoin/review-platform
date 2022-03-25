@extends('vox')

@section('content')

    <div class="checkout-container"> <!-- 800px -->
        <a class="back-home" href="{{ getLangUrl('dental-industry-reports/'.$item->slug.'/checkout') }}">
            {{ trans('vox.daily-polls.popup.back') }}
        </a>
    </div>

    <div class="main-title tac">
        <h1>{{ trans('vox.page.paid-reports.payment-title') }}</h1>
        <p class="order-subtitle">{{ trans('vox.page.paid-reports.order-number', [
            'number' => $order->id
        ]) }}</p>
    </div>

    <div class="checkout-section">        
        <div class="flex flex-center checkout-container"> <!-- 800px -->
            <div class="main-image-wrapper col">
                <img class="main-image" src="{{ $item->getImageUrl() }}"/>
                <img class="small-bubble" src="{{ url('new-vox-img/small-bubble.png') }}" />
            </div>
            <div class="col">
                <div class="main-inner">
                    <h4>{{ $item->main_title }}</h4>
                    <h4 class="gray-title">{{ $item->title }}</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="checkout-container">
        <p class="order-description">
            @if(!$order->invoice)
                {!! trans('vox.page.paid-reports.payment-description-without-invoice', [
                    'email' => '<b>'.$order->email.'</b>',
                ]) !!}
                {{-- <br/><a href="javascript:;" class="invoice blue-text-link">{{ trans('vox.page.paid-reports.button-invoice') }}</a> --}}
            @else
                {!! trans('vox.page.paid-reports.payment-description-with-invoice', [
                    'email' => '<b>'.$order->email.'</b>',
                ]) !!}
                <br/><a href="javascript:;" class="invoice blue-text-link">{{ trans('vox.page.paid-reports.button-invoice-see') }}</a>
            @endif
        </p>
        <form class="checkout-form company-form {!! $order->invoice ? 'active' : '' !!}" method="post" action="{{ getLangUrl('dental-industry-reports/'.$item->slug.'/payment/'.$order->id.'/') }}">
            {!! csrf_field() !!}
            <div class="modern-field alert-after">
                <input type="text" name="company-name" id="company-name" value="{{ $order->company_name ?? '' }}" class="modern-input" autocomplete="off" readonly onfocus="this.removeAttribute('readonly');">
                <label for="company-name">
                    <span>{{ trans('vox.page.paid-reports.invoice.company-name') }}</span>
                </label>
            </div>
            <div class="modern-field alert-after">
                <input type="text" name="company-number" id="company-number" value="{{ $order->company_number ?? '' }}" class="modern-input" autocomplete="off" readonly onfocus="this.removeAttribute('readonly');">
                <label for="company-number">
                    <span>{{ trans('vox.page.paid-reports.invoice.reg-number') }}</span>
                </label>
            </div>
            <div class="modern-field alert-after">
                <input type="text" name="address" id="address" value="{{ $order->address ?? '' }}" class="modern-input" autocomplete="off" readonly onfocus="this.removeAttribute('readonly');">
                <label for="address">
                    <span>{{ trans('vox.page.paid-reports.invoice.address') }}</span>
                </label>
            </div>
            @if($order->invoice && !$order->company_vat)
            @else
                <div class="modern-field alert-after">
                    <input type="text" name="vat" id="vat" value="{{ $order->vat ?? '' }}" class="modern-input" autocomplete="off" readonly onfocus="this.removeAttribute('readonly');">
                    <label for="vat">
                        <span>{{ trans('vox.page.paid-reports.invoice.vat') }}</span>
                    </label>
                </div>
            @endif
            <div class="tac">
                <button type="submit" href="javascript:;" class="blue-button new-style red-button">
                    {{ trans('vox.page.paid-reports.invoice.submit') }}
                </button>
            </div>

        </form>
        <div class="alert alert-success" id="checkout-form-success" style="display: none; margin-top: 20px;">
            {{ trans('vox.page.paid-reports.invoice.success') }}
        </div>

        @if($order->payment_method == 'paypal')
            <div class="payment-wrap paypal">
                <p>{{ trans('vox.page.paid-reports.amount-to-transfer') }} <span class="blue-text-link price">{{ $order->price_with_currency }}</span></p>
                <p>{{ trans('vox.page.paid-reports.paypal-link-text') }} <a href="{{ trans('vox.page.paid-reports.paypal-link') }}" target="_blank" class="blue-text-link">
                    {{ trans('vox.page.paid-reports.paypal-link') }}</a>
                </p>
                
                <div class="tac">
                    <a class="blue-button new-style red-button" href="{{ trans('vox.page.paid-reports.paypal-link') }}" target="_blank">{{ trans('vox.page.paid-reports.paypal-button') }}</a>
                </div>

                <div class="alert-crypto flex flex-center">
                    <div>
                        <img src="{{ url('new-vox-img/warning-sign.png') }}" width="51"/>
                    </div>
                    <div>
                        {!! trans('vox.page.paid-reports.paypal-payment-info-text', [
                            'email' => '<span class="blue-text-link">dentavox@dentacoin.com</span>',
                            'order_start_text' => '<span class="blue-text-link">',
                            'order_end_text' => $order->id.'</span>'
                        ]) !!}
                    </div>
                </div>
            </div>
        @else
            <div class="payment-wrap crypto">
                <p>{{ trans('vox.page.paid-reports.amount-to-transfer') }} <span class="blue-text-link price">{{ $order->price_with_currency }}</span></p>
                <p>{{ trans('vox.page.paid-reports.crypto-link-text') }} <span class="blue-text-link">{{ trans('vox.paid-reports.payment.to-address.'.$order->payment_method) }}</span></p>

                <div class="alert-crypto flex flex-center">
                    <div>
                        <img src="{{ url('new-vox-img/warning-sign.png') }}" width="51"/>
                    </div>
                    <div>
                        {!! trans('vox.page.paid-reports.crypto-payment-info-text', [
                            'email' => '<span class="blue-text-link">dentavox@dentacoin.com</span>',
                            'order_start_text' => '<span class="blue-text-link">',
                            'order_end_text' => $order->id.'</span>'
                        ]) !!}
                    </div>
                </div>
            </div>
        @endif
    </div>

@endsection