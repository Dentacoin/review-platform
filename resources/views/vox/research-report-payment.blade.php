@extends('vox')

@section('content')
    
    <div class="main-title tac">
        <h1>FINALIZE PAYMENT</h1>
        <p class="order-subtitle">Order #{{ $order->id }}</p>
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
            Thank you for your interest! Once the payment is received, you will get the report via email: <b>{{ $order->email }}</b> within 2 working days.
            @if(!$order->company_name)
                <a href="javascript:;" class="invoice blue-text-link">Do you need a company invoice?</a>
            @endif
        </p>
        @if(!$order->company_name)
            <form class="checkout-form company-form" method="post" action="{{ getLangUrl('dental-industry-reports/'.$item->slug.'/payment/'.$order->id.'/') }}">
                <div class="modern-field alert-after">
                    <input type="text" name="company-name" id="company-name" class="modern-input" autocomplete="off" readonly onfocus="this.removeAttribute('readonly');">
                    <label for="company-name">
                        <span>Company name:</span>
                    </label>
                </div>
                <div class="modern-field alert-after">
                    <input type="text" name="company-number" id="company-number" class="modern-input" autocomplete="off" readonly onfocus="this.removeAttribute('readonly');">
                    <label for="company-number">
                        <span>Reg. No:</span>
                    </label>
                </div>
                
                <div class="modern-field">
                    <select name="company-country" id="company-country" class="modern-input country-select">
                        @if(!$country_id)
                            <option>-</option>
                        @endif
                        @if(!empty($countries))
                            @foreach( $countries as $country )
                                <option value="{{ $country->id }}" code="{{ $country->code }}" {!! $country_id==$country->id ? 'selected="selected"' : '' !!} >{{ $country->name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div class="modern-field alert-after">
                    <input type="text" name="address" id="address" class="modern-input" autocomplete="off" readonly onfocus="this.removeAttribute('readonly');">
                    <label for="address">
                        <span>Address:</span>
                    </label>
                </div>
                <div class="modern-field alert-after">
                    <input type="text" name="vat" id="vat" class="modern-input" autocomplete="off" readonly onfocus="this.removeAttribute('readonly');">
                    <label for="vat">
                        <span>VAT:</span>
                    </label>
                </div>
                <div class="tac">
                    <button type="submit" href="javascript:;" class="blue-button new-style red-button">Submit</button>
                </div>
            </form>
        @endif

        @if($order->payment_method == 'crypto')
            <div class="payment-wrap crypto">
                <p>Amount to transfer: <span class="blue-text-link price">0.245 BTC</span></p>
                <p>To wallet address: <span class="blue-text-link">1FfmbHfnpaZjKFvyi1okTjJJusN455paPH</span></p>

                <div class="alert-crypto flex flex-center">
                    <div>
                        <img src="{{ url('new-vox-img/warning-sign.png') }}" width="51"/>
                    </div>
                    <div>
                        Please send an email with the transaction hash to <span class="blue-text-link">dentavox@dentacoin.com</span> with a subject: <span class="blue-text-link">Order #{{ $order->id }}</span>
                    </div>
                </div>
            </div>
        @elseif($order->payment_method == 'paypal')
            <div class="payment-wrap paypal">
                <p>Amount to transfer: <span class="blue-text-link price">$ 250</span></p>
                <p>Via link: <a href="https://www.paypal.com/paypalme/dentacoinbv" target="_blank" class="blue-text-link">https://www.paypal.com/paypalme/dentacoinbv</a></p>
                
                <div class="tac">
                    <a class="blue-button new-style red-button" href="https://www.paypal.com/paypalme/dentacoinbv" target="_blank">Go to paypal</a>
                </div>
            </div>
        @endif
    </div>

@endsection