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

    		<form class="signin-form" action="{{ getLangUrl('recover/'.$id.'/'.$hash) }}" method="post">
    			{!! csrf_field() !!}

				<div class="form-inner">
					<input type="password" name="password" placeholder="{{ trans('trp.page.'.$current_page.'.password') }}" class="input">
					<input type="password" name="password-repeat" placeholder="{{ trans('trp.page.'.$current_page.'.repeat') }}" class="input">
					<button type="submit" class="button">{{ trans('trp.page.'.$current_page.'.update') }}</button>
				</div>
    		</form>

    		@if(!empty($changed))
    			<div class="alert alert-success">
			        <strong>
			        	{!! trans('trp.page.'.$current_page.'.success',[
			        		'link' => '<a href="javascript:;" data-popup="popup-login">',
			        		'endlink' => '</a>',
			        	]) !!}
			        </strong>
			    </div>
    		@endif
	    </div>
    </div>

@endsection