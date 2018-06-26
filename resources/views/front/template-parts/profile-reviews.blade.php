<div class="panel panel-default">
	<div class="panel-heading">
		<h1 class="panel-title">
			{{ trans('front.page.profile.reviews.title') }}
		</h1>
	</div>
	<div class="panel-body">
		@if($user->reviews_out->isEmpty())
			<div class="alert alert-info">
				{{ trans('front.page.profile.reviews.no-reviews') }}
			</div>
		@else
			@foreach($user->reviews_out as $review)
				<div class="panel panel-default">
					@include('front.template-parts.review-new', [
						'item' => $review->dentist,
						'user_field' => 'dentist',
						'reviews_out' => true
					])
				</div>
			@endforeach
		@endif
	</div>
</div>