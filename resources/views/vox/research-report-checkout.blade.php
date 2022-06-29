@extends('vox')

@section('content')
    

    <div class="checkout-container"> <!-- 800px -->
        <a class="back-home" href="{{ getLangUrl('dental-industry-reports/'.$item->slug) }}">
            {{ trans('vox.daily-polls.popup.back') }}
        </a>
    </div>

    <div class="main-title tac">
        <h1>{{ trans('vox.page.paid-reports.order-title') }}</h1>
    </div>

    <div class="checkout-section">        
        <div class="flex checkout-container"> <!-- 800px -->
            <div class="main-image-wrapper col">
                <img class="main-image" src="{{ $item->getImageUrl('all-reports') }}"/>
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
                <span>{{ trans('vox.page.paid-reports.order-email') }}</span>
            </label>
        </div>

        <div class="modern-field alert-after">
            <input type="email" name="email-confirm" id="email-confirm" value="{{ $order ? $order->email : '' }}" class="modern-input" autocomplete="off" readonly onfocus="this.removeAttribute('readonly');">
            <label for="email-confirm">
                <span>{{ trans('vox.page.paid-reports.order-confirm-email') }}</span>
            </label>
        </div>

        <div class="modern-field alert-after">
            <select name="payment-method" id="payment-method" class="modern-input">
                <option>{{ trans('vox.page.paid-reports.order-select-payment-method') }}</option>
                @foreach(config('payment-methods') as $key => $pm)
                    <option value="{{ $key }}" {!! $order && $order->payment_method == $key ? 'selected="selected"' : '' !!}>{{ $pm }}</option>
                @endforeach
            </select>
        </div>

        <div class="modern-field alert-after">
            <input type="text" name="name" id="name" value="{{ $order ? $order->name : '' }}" class="modern-input" autocomplete="off" readonly onfocus="this.removeAttribute('readonly');">
            <label for="name">
                <span>{{ trans('vox.page.paid-reports.order-name') }}</span>
            </label>
        </div>
        
        <div class="modern-field">
            <select name="company-country" id="company-country" class="modern-input country-select">
                @if(!$country_id)
                    <option>-</option>
                @endif
                @if(!empty($countries))
                    @foreach( $countries as $country )
                        <option 
                            value="{{ $country->id }}" 
                            code="{{ $country->code }}" 
                            {!! $order && $order->country_id ? ($order->country_id == $country->id ? 'selected="selected"' : '') : ($country_id==$country->id ? 'selected="selected"' : '') !!}
                        >
                            {{ $country->name }}
                        </option>
                    @endforeach
                @endif
            </select>
        </div>

        <div class="request-row radios-row alert-after">
            <div class="target-label">
                {{ trans('vox.page.paid-reports.order-need-invoice') }} 
            </div>
            <div class="modern-radios">
                <div class="radio-label">
                      <label for="invoice-yes" class="{{ $order && $order->invoice ? 'active' : '' }}">
                        <span class="modern-radio">
                            <span></span>
                        </span>
                        <input class="type-radio" type="radio" name="invoice" id="invoice-yes" value="yes" {!! $order && $order->invoice ? 'checked="checked"' : '' !!}>
                        {{ trans('vox.page.paid-reports.radio-yes') }}
                      </label>
                </div>
                <div class="radio-label">
                      <label for="invoice-no" class="{{ $order && !$order->invoice ? 'active' : '' }}">
                        <span class="modern-radio">
                            <span></span>
                        </span>
                        <input class="type-radio" type="radio" name="invoice" id="invoice-no" value="no" {!! $order && !$order->invoice ? 'checked="checked"' : '' !!}>
                        {{ trans('vox.page.paid-reports.radio-no') }}
                      </label>
                </div>
            </div>
        </div>

        <div class="request-row radios-row alert-after vat-wrapper {{ $order && $order->invoice ? '' : 'hide' }}">
            <div class="target-label">
                {{ trans('vox.page.paid-reports.order-company-vat') }}
            </div>
            <div class="modern-radios">
                <div class="radio-label">
                      <label for="vat-yes" class="{{ $order && $order->invoice && $order->company_vat ? 'active' : '' }}">
                        <span class="modern-radio">
                            <span></span>
                        </span>
                        <input class="type-radio" type="radio" name="vat" id="vat-yes" value="yes" {!! $order && $order->invoice && $order->company_vat ? 'checked="checked"' : '' !!}>
                        {{ trans('vox.page.paid-reports.radio-yes') }}
                      </label>
                </div>
                <div class="radio-label">
                      <label for="vat-no" class="{{ $order && $order->invoice && !$order->company_vat ? 'active' : '' }}">
                        <span class="modern-radio">
                            <span></span>
                        </span>
                        <input class="type-radio" type="radio" name="vat" id="vat-no" value="no" {!! $order && $order->invoice && !$order->company_vat ? 'checked="checked"' : '' !!}>
                        {{ trans('vox.page.paid-reports.radio-no') }}						    	
                      </label>
                </div>
            </div>
        </div>

        @if(empty($user))
            <div class="checkbox">
                <label class="checkbox-label {!! $order ? 'active' : '' !!}" for="agree">
                    <input type="checkbox" class="special-checkbox" id="agree" name="agree" value="1" {!! $order ? 'checked="checked"' : '' !!}>
                    {!! trans('vox.page.paid-reports.order-agree-privacy', [
                        'link' => '<a href="https://dentacoin.com/privacy-policy" target="_blank">', 
                        'endlink' => '</a>'
                    ]) !!}
                </label>
                <div class="alert alert-warning agree-error" style="display: none;">
                    {{ trans('vox.page.paid-reports.order-agree-privacy-error') }}
                </div>
            </div>
        @endif
            
        <div class="alert alert-warning unavailable-error" style="display: none;">
            This feature is temporaty unavailable. For more information, get in touch with: <a href="mailto:admin@dentacoin.com">admin@dentacoin.com</a>
        </div>
        <div class="tac">
            <button type="submit" href="javascript:;" class="blue-button new-style red-button">
                {{ trans('vox.page.paid-reports.proceed-to-payment') }}
            </button>
        </div>
    </form>

@endsection