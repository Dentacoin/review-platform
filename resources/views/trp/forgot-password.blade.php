@extends('trp')

@section('content')

	<div class="container conatiner-centered">
		<div class="centered-form">
	    	<h2>
	    		{{ trans('trp.page.'.$current_page.'.title') }}
	    	</h2>
	    	<p>
	    		{{ trans('trp.page.'.$current_page.'.hint') }}
	    	</p>

    		<form class="signin-form" action="{{ getLangUrl('forgot-password') }}" method="post">
    			{!! csrf_field() !!}

				<div class="form-inner">
					<input type="email" name="email" placeholder="{{ trans('trp.page.'.$current_page.'.email') }}" class="input">
					<button type="submit" class="button">{{ trans('trp.page.'.$current_page.'.submit') }}</button>
				</div>

    		</form>

			@include('front.errors')
	    </div>
    </div>

@endsection