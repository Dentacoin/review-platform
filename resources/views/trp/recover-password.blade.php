@extends('trp')

@section('content')

	<div class="container conatiner-centered">
		<div class="centered-form">
	    	<h2>
	    		{{ trans('front.page.'.$current_page.'.title') }}
	    	</h2>
	    	<p>
	    		{{ trans('front.page.'.$current_page.'.hint') }}
	    	</p>


    		<form class="signin-form" action="{{ getLangUrl('recover/'.$id.'/'.$hash) }}" method="post">
    			{!! csrf_field() !!}

				<div class="form-inner">
					<input type="password" name="password" placeholder="Your new password" class="input">
					<input type="password" name="password-repeat" placeholder="Repeat, please" class="input">
					<button type="submit" class="button">Update password</button>
				</div>

    		</form>

    		@if(!empty($changed))
    			<div class="alert alert-success">
			        <strong>Thank you. You can now <a href="javascript:;" data-popup="popup-login">Log in</a> using your new password.</strong>
			    </div>
    		@endif
	    </div>
    </div>

@endsection