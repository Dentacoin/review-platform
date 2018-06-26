@extends('front')

@section('content')

<div class="container">
	<div class="col-md-3">
		@include('front.template-parts.profile-menu')
	</div>
	<div class="col-md-9">

        <div class="panel panel-default">
            <div class="panel-heading">
                <h1 class="panel-title">
                    {{ trans('front.page.profile.'.$current_subpage.'.title') }}
                </h1>
            </div>
            <div class="panel-body">
            	<br/>
				<form action="{{ getLangUrl('profile/password') }}" method="post" class="form-horizontal">
	  				{!! csrf_field() !!}
	  				
	  				<div class="form-group">
					  	<label class="control-label col-md-3">{{ trans('front.page.'.$current_page.'.change-password-current') }}</label>
					  	<div class="col-md-9">
					    	<input type="password" name="cur-password" class="form-control" required>
					    </div>
					</div>
	    			<div class="form-group">
					  	<label class="control-label col-md-3">{{ trans('front.page.'.$current_page.'.change-password-new') }}</label>
					  	<div class="col-md-9">
					    	<input type="password" name="new-password" class="form-control" required>
					    </div>
					</div>
				  	<div class="form-group">
					  	<label class="control-label col-md-3">{{ trans('front.page.'.$current_page.'.change-password-repeat') }}</label>
					  	<div class="col-md-9">
					    	<input type="password" name="new-password-repeat" class="form-control" required>
					    </div>
					</div>
					<div class="form-group mrt">
						<div class="col-md-8">
	                        <p>{{ trans('front.page.profile.'.$current_subpage.'.hint') }}</p>
						</div>
						<div class="col-md-4">
	                        <button type="submit" name="update" class="btn btn-primary form-control"> {{ trans('front.page.'.$current_page.'.change-password-submit') }} </button>
						</div>
					</div>
	    			
	  			</form>
	  			@include('front.errors')
	  		</div>
	  	</div>
	</div>
</div>

@endsection