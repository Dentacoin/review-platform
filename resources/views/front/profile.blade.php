@extends('front')

@section('content')

<div class="container">
	@include('front.errors')

	<div class="col-md-3">
		@include('front.template-parts.profile-menu')
	</div>
	<div class="col-md-9">

		<div class="panel panel-default">
			<div class="panel-heading">
				<h1 class="panel-title">
					{{ trans('front.page.profile.title') }}
				</h1>
			</div>
			<div class="panel-body">
				<p>
					{{ trans('front.page.profile.hint') }}
				</p>

				@if($needs_avatar)
					<div class="alert alert-info">
						{{ trans('front.page.profile.alert-needs-avatar') }}
					</div>
				@endif
				@if($no_reviews)
					<div class="alert alert-info">
						{{ trans('front.page.profile.alert-write-review') }}
					</div>
				@endif
				@if($no_address)
					<div class="alert alert-info">
						{{ trans('front.page.profile.alert-needs-address') }}
					</div>
				@endif
			</div>
		</div>


		@if(!$needs_avatar && !$no_reviews && !$no_address)
			@include('front.template-parts.profile-wallet')
			@include('front.template-parts.profile-reviews')
		@endif
	</div>
</div>

@endsection