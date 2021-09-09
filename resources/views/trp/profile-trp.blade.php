@extends('trp')

@section('content')

	<div class="blue-background"></div>

	<div class="container flex break-tablet">
		<div class="flex-3">

			<h2 class="page-title">
				<img src="{{ url('new-vox-img/profile-trp.png') }}" />
				{!! nl2br(trans('trp.page.profile.trp.title')) !!}
			</h2>

			@if($reviews->isEmpty())
				<div class="alert alert-info">
					{!! nl2br(trans('trp.page.profile.trp.no-reviews')) !!}
				</div>

				<a href="https://reviews.dentacoin.com" class="button" style="color: white;">
					{!! nl2br(trans('trp.page.profile.trp.find-dentist-button')) !!}
				</a>
			@else
			    <div class="details-wrapper profile-reviews-space">
					@foreach($reviews as $review)
						@if($review->user)
							@include('trp.parts.reviews', [
								'review' => $review,
								'is_dentist' => $is_dentist,
								'for_profile' => true,
								'current_dentist' => $review->getDentist(),
								'my_upvotes' => false,
								'my_downvotes' => false,
							])
						@endif
					@endforeach
				</div>
			@endif
		</div>
	</div>

	@if(!empty($current_ban))
		<div class="popup fixed-popup popup-with-background active" id="banned-popup">
			<div class="popup-inner inner-white">
				<div class="flex flex-mobile flex-center break-tablet">
					<div class="icon">
						<img src="{{ url('img-trp/big-x.png') }}">
					</div>
					<div class="content">
						<p class="h1">
							{!! nl2br(trans('trp.page.profile.trp.ban-title')) !!}
						</p>
						<h3>
							{!! nl2br(trans('trp.page.profile.trp.ban-subtitle')) !!}
						</h3>
					</div>
				</div>
			</div>
		</div>
	@endif
@endsection