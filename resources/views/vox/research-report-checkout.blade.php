@extends('vox')

@section('content')
    
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
        <div class="modern-field alert-after">
            <input type="email" name="email" id="email" class="modern-input" autocomplete="off" readonly onfocus="this.removeAttribute('readonly');">
            <label for="email">
                <span>Add email address to receive the report:</span>
            </label>
        </div>
        <div class="modern-field alert-after">
            <input type="email" name="email-confirm" id="email-confirm" class="modern-input" autocomplete="off" readonly onfocus="this.removeAttribute('readonly');">
            <label for="email-confirm">
                <span>Confirm email address:</span>
            </label>
        </div>

        <div class="modern-field alert-after">
            <select name="payment-method" id="payment-method" class="modern-input">
                <option>Select payment method:</option>
                <option value="crypto">Crypto</option>
                <option value="paypal">PayPal</option>
            </select>
        </div>
        <div class="checkbox">
            <label class="checkbox-label" for="agree">
                <input type="checkbox" class="special-checkbox" id="agree" name="agree" value="1">
                I agree with the <a href="https://dentacoin.com/privacy-policy" target="_blank">Privacy Policy</a>
            </label>
            <div class="alert alert-warning agree-error" style="display: none;margin-top: 8px;">You must agree with the Privacy Policy</div>
        </div>
        <div class="tac">
            <button type="submit" href="javascript:;" class="blue-button new-style red-button">PROCEED TO PAYMENT</button>
        </div>
    </form>

@endsection