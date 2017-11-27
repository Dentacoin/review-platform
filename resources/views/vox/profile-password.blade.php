@extends('vox')

@section('content')

	<div class="container">

		<a href="{{ getLangUrl('/') }}" class="questions-back">
			<i class="fa fa-arrow-left"></i> 
			{{ trans('vox.common.questionnaires') }}
		</a>

		<div class="col-md-3">
			@include('vox.template-parts.profile-menu')
		</div>
		<div class="col-md-9">

		  	<div class="panel panel-default">
	            <div class="panel-heading">
	                <h3 class="panel-title bold">
	                	{{ trans('vox.page.profile.title-password') }}
	                </h3>
	            </div>
	            <div class="panel-body">
					<form action="{{ getLangUrl('profile/password') }}" method="post" class="form-horizontal">
		  				{!! csrf_field() !!}
		  				
		  				<div class="form-group">
						  	<label class="control-label col-md-3">{{ trans('vox.page.'.$current_page.'.change-password-current') }}</label>
						  	<div class="col-md-9">
						    	<input type="password" name="cur-password" class="form-control" required>
						    </div>
						</div>
		    			<div class="form-group">
						  	<label class="control-label col-md-3">{{ trans('vox.page.'.$current_page.'.change-password-new') }}</label>
						  	<div class="col-md-9">
						    	<input type="password" name="new-password" class="form-control" required>
						    </div>
						</div>
					  	<div class="form-group">
						  	<label class="control-label col-md-3">{{ trans('vox.page.'.$current_page.'.change-password-repeat') }}</label>
						  	<div class="col-md-9">
						    	<input type="password" name="new-password-repeat" class="form-control" required>
						    </div>
						</div>
						<div class="form-group">
							<div class="col-md-12">
		                        <button type="submit" name="update" class="btn btn-primary form-control"> {{ trans('vox.page.'.$current_page.'.change-password-submit') }} </button>
							</div>
						</div>
		    			
		  			</form>
	  				@include('front.errors')
		  		</div>
		  	</div>
		</div>
	</div>

@endsection