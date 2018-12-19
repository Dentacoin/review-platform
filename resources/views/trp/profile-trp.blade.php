@extends('trp')

@section('content')

	<div class="blue-background"></div>

	<div class="container flex break-tablet">
		<div class="col">
			@include('trp.parts.profile-menu')
		</div>
		<div class="flex-3">

			<h2 class="page-title">
				<img src="{{ url('new-vox-img/profile-trp.png') }}" />
				{!! nl2br(trans('trp.page.profile.trp.title')) !!}
	            
			</h2>

			@if($user->reviews_out->isEmpty())
				<div class="alert alert-info">
					{!! nl2br(trans('trp.page.profile.trp.no-reviews')) !!}
				</div>
			@else
			    <div class="details-wrapper profile-reviews-space">
		    	@if($user->reviews_out->isNotEmpty() )
					@foreach($user->reviews_out as $review)

				    	<div class="review review-wrapper" review-id="{{ $review->id }}">
							<div class="review-header">
				    			<div class="review-avatar" style="background-image: url('{{ $review->user->getImageUrl(true) }}');"></div>
				    			<span class="review-name">to {{ $review->dentist ? $review->dentist->name : $review->clinic->name }}: </span>
								@if($review->verified)
					    			<div class="trusted-sticker mobile-sticker">
					    				{!! nl2br(trans('trp.common.trusted')) !!}
					    			</div>
				    			@endif
				    			@if($review->title)
				    			<span class="review-title">
				    				“{{ $review->title }}”
				    			</span>
				    			@endif
								@if($review->verified)
					    			<div class="trusted-sticker">
					    				{!! nl2br(trans('trp.common.trusted')) !!}
					    			</div>
				    			@endif
			    			</div>
			    			<div class="review-rating">
			    				<div class="ratings">
									<div class="stars">
										<div class="bar" style="width: {{ $review->rating/5*100 }}%;">
										</div>
									</div>
									<span class="rating">
										({{ $review->rating }})
									</span>
								</div>
								<span class="review-date">
									{{ $review->created_at ? $review->created_at->toFormattedDateString() : '-' }}
								</span>
							</div>
							<div class="review-content">
								{!! nl2br($review->answer) !!}
								<a href="{{ $review->dentist ? $review->dentist->getLink() : $review->clinic->getLink() }}?review_id={{ $review->id }}" class="more">
									{!! nl2br(trans('trp.page.profile.trp.show-entire')) !!}
									
								</a>
							</div>

			    		</div>
			    	@endforeach
				@endif
			@endif
		</div>
	</div>
@endsection