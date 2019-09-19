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

			@if($reviews->isEmpty())
				<div class="alert alert-info">
					{!! nl2br(trans('trp.page.profile.trp.no-reviews')) !!}
				</div>
			@else
			    <div class="details-wrapper profile-reviews-space">
				@foreach($reviews as $review)

			    	<div class="review review-wrapper" review-id="{{ $review->id }}">
						<div class="review-header">
			    			<div class="review-avatar" style="background-image: url('{{ $review->user->getImageUrl(true) }}');"></div>
			    			@if($user->is_dentist)
			    				<span class="review-name">{{ $review->user->getName() }}: </span>
			    			@else
			    				<span class="review-name">to {{ $review->dentist ? $review->dentist->name : $review->clinic->name }}: </span>
			    			@endif
							@if($review->verified)
				    			<div class="trusted-sticker mobile-sticker tooltip-text" text="{!! nl2br(trans('trp.common.trusted-tooltip', ['name' => $review->dentist ? $review->dentist->name : $review->clinic->name ])) !!}">
				    				{!! nl2br(trans('trp.common.trusted')) !!}
			    					<i class="fas fa-info-circle"></i>
				    			</div>
			    			@endif
			    			@if($review->title)
			    			<span class="review-title">
			    				“{{ $review->title }}”
			    			</span>
			    			@endif
							@if($review->verified)
				    			<div class="trusted-sticker tooltip-text" text="{!! nl2br(trans('trp.common.trusted-tooltip', ['name' => $review->dentist ? $review->dentist->name : $review->clinic->name ])) !!}">
				    				{!! nl2br(trans('trp.common.trusted')) !!}
			    					<i class="fas fa-info-circle"></i>
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
							You've been banned for suspicious activity.
						</p>
						<h3>
							You will no longer be able to submit feedback on Dentacoin Trusted Reviews.
						</h3>
					</div>
				</div>
			</div>
		</div>
	@endif
@endsection