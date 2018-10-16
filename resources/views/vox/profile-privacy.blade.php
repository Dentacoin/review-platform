@extends('vox')

@section('content')

	<div class="container">

	  	@include('front.errors')

		<div class="col-md-3">
			@include('vox.template-parts.profile-menu')
		</div>
		<div class="col-md-9">
			<h2 class="page-title">
				<img src="{{ url('new-vox-img/profile-privacy.png') }}" />
				Manage Privacy
			</h2>

			<div class="privacy-row">
				<div class="flex">
					<img src="{{ url('new-vox-img/gdpr-delete.png') }}" />
					<div>
						<h3>{{ trans('vox.page.profile.title-privacy-delete') }}</h3>
						<p>
							{!! nl2br(trans('vox.page.profile.privacy-delete-hint')) !!}
						</p>
					</div>
				</div>

				<form action="{{ getLangUrl('profile/privacy') }}" method="post" class="clearfix" onsubmit="return confirm('{{ stripslashes(trans('vox.page.'.$current_page.'.privacy-delete-confirm')) }}')">
	  				{!! csrf_field() !!}
					<div class="form-group">
						<div class="col-md-12">
	                        <button type="submit" name="action" value="delete" class="btn btn-primary form-control"> {{ trans('vox.page.'.$current_page.'.privacy-delete-submit') }} </button>
						</div>
					</div>
	  			</form>
			</div>


			<div class="privacy-row">
				<div class="flex">
					<img src="{{ url('new-vox-img/gdpr-download.png') }}" />
					<div>
						<h3>{{ trans('vox.page.profile.title-privacy-download') }}</h3>
						<p>
							{!! nl2br(trans('vox.page.profile.privacy-download-hint')) !!}
						</p>
					</div>
				</div>

				<div class="clearfix">
					<div class="form-group">
						<div class="col-md-12">
		    				<a href="{{ getLangUrl('profile/privacy-download') }}" target="_blank" class="btn btn-primary form-control">
				    			{{ trans('vox.page.'.$current_page.'.privacy-download-submit') }}
				    		</a>
						</div>
					</div>
	  			</div>
			</div>

		</div>
	</div>

@endsection