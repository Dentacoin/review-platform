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