@extends('front')

@section('content')

<div class="container">
	<div class="col-md-3">
		@include('front.template-parts.profile-menu')
	</div>
	<div class="col-md-9">


	  	<div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title bold">
                	{{ trans('front.page.profile.title-privacy-delete') }}
                </h3>
            </div>
            <div class="panel-body">

	    		<p class="personal-description">
	    			{!! nl2br(trans('front.page.profile.privacy-delete-hint')) !!}
	    		</p>
	    		<br/>

				<form action="{{ getLangUrl('profile/privacy') }}" method="post" class="form-horizontal" onsubmit="return confirm('{{ stripslashes(trans('front.page.'.$current_page.'.privacy-delete-confirm')) }}')">
	  				{!! csrf_field() !!}
	  				
					<div class="form-group">
						<div class="col-md-12">
	                        <button type="submit" name="action" value="delete" class="btn btn-primary form-control"> {{ trans('front.page.'.$current_page.'.privacy-delete-submit') }} </button>
						</div>
					</div>
	    			
	  			</form>
	  		</div>
	  	</div>


	  	<div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title bold">
                	{{ trans('front.page.profile.title-privacy-download') }}
                </h3>
            </div>
            <div class="panel-body">

	    		<p class="personal-description">
	    			{!! nl2br(trans('front.page.profile.privacy-download-hint')) !!}
	    		</p>
	    		<br/>

				<form action="{{ getLangUrl('profile/privacy') }}" method="post" class="form-horizontal">
	  				{!! csrf_field() !!}
	  				
					<div class="form-group">
						<div class="col-md-12">
	                        <button type="submit" name="action" value="download" class="btn btn-primary form-control"> {{ trans('front.page.'.$current_page.'.privacy-download-submit') }} </button>
						</div>
					</div>
	    			
	  			</form>
	  		</div>
	  	</div>

	</div>
</div>

@endsection