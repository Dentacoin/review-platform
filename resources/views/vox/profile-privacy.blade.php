@extends('vox')

@section('content')

	<div class="container">

		<a href="{{ getLangUrl('/') }}" class="questions-back">
			<i class="fa fa-arrow-left"></i> 
			{{ trans('vox.common.questionnaires') }}
		</a>
	  	
	  	@include('front.errors')

		<div class="col-md-3">
			@include('vox.template-parts.profile-menu')
		</div>
		<div class="col-md-9">

		  	<div class="panel panel-default">
	            <div class="panel-heading">
	                <h3 class="panel-title bold">
	                	{{ trans('vox.page.profile.title-privacy-delete') }}
	                </h3>
	            </div>
	            <div class="panel-body">

		    		<p class="personal-description">
		    			{!! nl2br(trans('vox.page.profile.privacy-delete-hint')) !!}
		    		</p>
		    		<br/>

					<form action="{{ getLangUrl('profile/privacy') }}" method="post" class="form-horizontal" onsubmit="return confirm('{{ stripslashes(trans('vox.page.'.$current_page.'.privacy-delete-confirm')) }}')">
		  				{!! csrf_field() !!}
		  				
						<div class="form-group">
							<div class="col-md-12">
		                        <button type="submit" name="action" value="delete" class="btn btn-primary form-control"> {{ trans('vox.page.'.$current_page.'.privacy-delete-submit') }} </button>
							</div>
						</div>
		    			
		  			</form>
		  		</div>
		  	</div>


		  	<div class="panel panel-default">
	            <div class="panel-heading">
	                <h3 class="panel-title bold">
	                	{{ trans('vox.page.profile.title-privacy-download') }}
	                </h3>
	            </div>
	            <div class="panel-body">

		    		<p class="personal-description">
		    			{!! nl2br(trans('vox.page.profile.privacy-download-hint')) !!}
		    		</p>
		    		<br/>

					<form action="{{ getLangUrl('profile/privacy') }}" method="post" class="form-horizontal">
		  				{!! csrf_field() !!}
		  				
						<div class="form-group">
							<div class="col-md-12">
		                        <button type="submit" name="action" value="download" class="btn btn-primary form-control"> {{ trans('vox.page.'.$current_page.'.privacy-download-submit') }} </button>
							</div>
						</div>
		    			
		  			</form>
		  		</div>
		  	</div>

		</div>
	</div>

@endsection