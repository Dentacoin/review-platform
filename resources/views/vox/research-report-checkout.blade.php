@extends('vox')

@section('content')
    

    <div class="checkout-container"> <!-- 800px -->
        <a class="back-home" href="{{ getLangUrl('dental-industry-reports/'.$item->slug) }}">
            Back
        </a>
    </div>

    <div class="main-title tac">
        <h1>Your order</h1>
    </div>

    <div class="checkout-section">        
        <div class="flex checkout-container"> <!-- 800px -->
            <div class="main-image-wrapper col">
                <img class="main-image" src="{{ $item->getImageUrl('social') }}"/>
                <img class="small-bubble" src="{{ url('new-vox-img/small-bubble.png') }}" />
            </div>
            <div class="col">
                <div class="main-inner">
                    <h4>{{ $item->main_title }}</h4>
                    <h4 class="gray-title">{{ $item->title }}</h4>

                    <div class="price-wrapper">
                        <p class="real-price">($1000)</p>
                        <p class="discount-price">$ {{ $item->price }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form class="checkout-form checkout-container active" method="post" action="{{ getLangUrl('dental-industry-reports/'.$item->slug.'/checkout/') }}">
        {!! csrf_field() !!}
        <div class="modern-field alert-after">
            <input type="email" name="email" id="email" value="{{ $order ? $order->email : '' }}" class="modern-input" autocomplete="off" readonly onfocus="this.removeAttribute('readonly');">
            <label for="email">
                <span>Add email address to receive the report:</span>
            </label>
        </div>

        <div class="modern-field alert-after">
            <input type="email" name="email-confirm" id="email-confirm" value="{{ $order ? $order->email : '' }}" class="modern-input" autocomplete="off" readonly onfocus="this.removeAttribute('readonly');">
            <label for="email-confirm">
                <span>Confirm email address:</span>
            </label>
        </div>

        <div class="request-row radios-row alert-after">
            <div class="target-label">
                Do you need a company invoice? 
            </div>
            <div class="modern-radios">
                <div class="radio-label">
                      <label for="invoice-yes" class="{{ $order && $order->invoice ? 'active' : '' }}">
                        <span class="modern-radio">
                            <span></span>
                        </span>
                        <input class="type-radio" type="radio" name="invoice" id="invoice-yes" value="yes" {!! $order && $order->invoice ? 'checked="checked"' : '' !!}>
                        Yes
                      </label>
                </div>
                <div class="radio-label">
                      <label for="invoice-no" class="{{ $order && !$order->invoice ? 'active' : '' }}">
                        <span class="modern-radio">
                            <span></span>
                        </span>
                        <input class="type-radio" type="radio" name="invoice" id="invoice-no" value="no" {!! $order && !$order->invoice ? 'checked="checked"' : '' !!}>
                        No						    	
                      </label>
                </div>
            </div>
        </div>

        <div class="request-row radios-row alert-after company-european-union-wrapper {{ $order && $order->invoice ? '' : 'hide' }}">
            <div class="target-label">
                Is your company registered within the European Union?
            </div>
            <div class="modern-radios">
                <div class="radio-label">
                      <label for="european-union-yes" class="{{ $order && $order->invoice && $order->company_european_union ? 'active' : '' }}">
                        <span class="modern-radio">
                            <span></span>
                        </span>
                        <input class="type-radio" type="radio" name="company-european-union" id="european-union-yes" value="yes" {!! $order && $order->invoice && $order->company_european_union ? 'checked="checked"' : '' !!}>
                        Yes
                      </label>
                </div>
                <div class="radio-label">
                      <label for="european-union-no" class="{{ $order && $order->invoice && !$order->company_european_union ? 'active' : '' }}">
                        <span class="modern-radio">
                            <span></span>
                        </span>
                        <input class="type-radio" type="radio" name="company-european-union" id="european-union-no" value="no" {!! $order && $order->invoice && !$order->company_european_union ? 'checked="checked"' : '' !!}>
                        No						    	
                      </label>
                </div>
            </div>
        </div>

        <div class="request-row radios-row alert-after vat-wrapper {{ $order && $order->invoice && $order->company_european_union ? '' : 'hide' }}">
            <div class="target-label">
                Is your company VAT-registered?
            </div>
            <div class="modern-radios">
                <div class="radio-label">
                      <label for="vat-yes" class="{{ $order && $order->invoice && $order->company_european_union && $order->company_vat ? 'active' : '' }}">
                        <span class="modern-radio">
                            <span></span>
                        </span>
                        <input class="type-radio" type="radio" name="vat" id="vat-yes" value="yes" {!! $order && $order->invoice && $order->company_european_union && $order->company_vat ? 'checked="checked"' : '' !!}>
                        Yes
                      </label>
                </div>
                <div class="radio-label">
                      <label for="vat-no" class="{{ $order && $order->invoice && $order->company_european_union && !$order->company_vat ? 'active' : '' }}">
                        <span class="modern-radio">
                            <span></span>
                        </span>
                        <input class="type-radio" type="radio" name="vat" id="vat-no" value="no" {!! $order && $order->invoice && $order->company_european_union && !$order->company_vat ? 'checked="checked"' : '' !!}>
                        No						    	
                      </label>
                </div>
            </div>
        </div>

        <div class="modern-field alert-after">
            <select name="payment-method" id="payment-method" class="modern-input">
                <option>Select payment method:</option>
                @foreach(config('payment-methods') as $key => $pm)
                    <option value="{{ $key }}" {!! $order && $order->payment_method == $key ? 'selected="selected"' : '' !!}>{{ $pm }}</option>
                @endforeach
            </select>
        </div>

        <div class="modern-field alert-after">
            <input type="text" name="name" id="name" value="{{ $order ? $order->name : '' }}" class="modern-input" autocomplete="off" readonly onfocus="this.removeAttribute('readonly');">
            <label for="name">
                <span>Your name:</span>
            </label>
        </div>

        @if(empty($user))
            <div class="checkbox">
                <label class="checkbox-label {!! $order ? 'active' : '' !!}" for="agree">
                    <input type="checkbox" class="special-checkbox" id="agree" name="agree" value="1" {!! $order ? 'checked="checked"' : '' !!}>
                    I agree with the <a href="https://dentacoin.com/privacy-policy" target="_blank">Privacy Policy</a>
                </label>
                <div class="alert alert-warning agree-error" style="display: none;">You must agree with the Privacy Policy</div>
            </div>
        @endif

        <div class="tac">
            <button type="submit" href="javascript:;" class="blue-button new-style red-button">PROCEED TO PAYMENT</button>
        </div>
    </form>

@endsection