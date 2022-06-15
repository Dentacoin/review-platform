@extends('trp')

@section('content')

	<div class="container">
		<h2 class="page-title">
			<img src="{{ url('new-vox-img/profile-trp.png') }}" />
			{!! nl2br(trans('trp.page.profile.trp.title')) !!}
		</h2>

		@if($reviews->isEmpty())
			@if(!$user->is_dentist)
				<div class="alert alert-info">
					{!! nl2br(trans('trp.page.profile.trp.no-reviews')) !!}
				</div>

				<a href="https://reviews.dentacoin.com" class="blue-button">
					{!! nl2br(trans('trp.page.profile.trp.find-dentist-button')) !!}
				</a>
			@endif
		@else
			<div class="details-wrapper">
				@foreach($reviews as $review)
					@if($review->user)
						@include('trp.parts.reviews', [
							'review' => $review,
							'is_dentist' => $is_dentist,
							'hidden' => 0,
							'for_profile' => true,
							'current_dentist' => $review->getDentist(),
						])
					@endif
				@endforeach
			</div>
		@endif
	</div>

	@if(!empty($current_ban))
		<div class="popup no-image dont-close-popup active" id="banned-popup">
			<div class="popup-inner inner-white">
				<h2 class="mont">
					{!! nl2br(trans('trp.page.profile.trp.ban-title')) !!}
				</h2>
				<div class="flex flex-mobile">
					<div class="content">
						<h4 class="tac">
							{!! nl2br(trans('trp.page.profile.trp.ban-subtitle')) !!}
						</h4>
					</div>
				</div>
			</div>
		</div>
	@endif
@endsection