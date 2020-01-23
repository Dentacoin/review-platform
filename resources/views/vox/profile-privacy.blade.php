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
				{!! nl2br(trans('vox.page.profile.privacy.title')) !!}
			</h2>

			<div class="privacy-row">
				<div class="flex">
					<img src="{{ url('new-vox-img/gdpr-delete.png') }}" />
					<div>
						<h3>{{ trans('vox.page.profile.privacy.title-delete') }}</h3>
						<p>
							{!! nl2br(trans('vox.page.profile.privacy.hint-delete')) !!}
						</p>
					</div>
				</div>

				<form action="{{ getLangUrl('profile/privacy') }}" method="post" class="clearfix" onsubmit="return confirm('{{ stripslashes(trans('vox.page.profile.privacy.privacy-delete-confirm')) }}')">
	  				{!! csrf_field() !!}
					<div class="form-group">
						<div class="col-md-12">
	                        <button type="submit" name="action" value="delete" class="btn btn-primary form-control"> {{ trans('vox.page.profile.privacy.submit-delete') }} </button>
						</div>
					</div>
	  			</form>
			</div>


			<div class="privacy-row">
				<div class="flex">
					<img src="{{ url('new-vox-img/gdpr-download.png') }}" />
					<div>
						<h3>{{ trans('vox.page.profile.privacy.title-download') }}</h3>
						<p>
							{!! nl2br(trans('vox.page.profile.privacy.hint-download')) !!}
						</p>
					</div>
				</div>

				<div class="clearfix">
					<div class="form-group">
						<div class="col-md-12">
		    				<a href="{{ getLangUrl('profile/privacy-download') }}" target="_blank" class="btn btn-primary form-control">
				    			{{ trans('vox.page.profile.privacy.submit-download') }}
				    		</a>
						</div>
					</div>
	  			</div>
			</div>

		</div>
	</div>


    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>

@endsection