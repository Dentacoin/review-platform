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

				<a href="https://reviews.dentacoin.com" class="button" style="color: white;">{!! nl2br(trans('trp.page.profile.trp.find-dentist-button')) !!}</a>
			@else
			    <div class="details-wrapper profile-reviews-space">
				@foreach($reviews as $review)

			    	<div class="review review-wrapper" review-id="{{ $review->id }}">
						<div class="review-header">
			    			<div class="review-avatar" style="background-image: url('{{ $review->user->getImageUrl(true) }}');"></div>
			    			@if($user->is_dentist)
			    				<span class="review-name">{{ !empty($review->user->self_deleted) ? ($review->verified ? trans('trp.common.verified-patient') : trans('trp.common.deleted-user')) : $review->user->name }}: </span>
			    			@else
			    				<span class="review-name">to {{ !empty($review->review_to_id) ? $review->original_dentist->name : ($review->dentist ? $review->dentist->name : $review->clinic->name) }}: </span>
			    			@endif
							@if($review->verified)
				    			<div class="trusted-sticker mobile-sticker tooltip-text" text="{!! nl2br(trans('trp.common.trusted-tooltip', ['name' => !empty($review->review_to_id) ? $review->original_dentist->name : ($review->dentist ? $review->dentist->name : $review->clinic->name) ])) !!}">
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
				    			<div class="trusted-sticker tooltip-text" text="{!! nl2br(trans('trp.common.trusted-tooltip', ['name' => !empty($review->review_to_id) ? $review->original_dentist->name : ($review->dentist ? $review->dentist->name : $review->clinic->name) ])) !!}">
				    				{!! nl2br(trans('trp.common.trusted')) !!}
			    					<i class="fas fa-info-circle"></i>
				    			</div>
			    			@endif
		    			</div>
		    			<div class="review-rating">
		    				<div class="ratings">
								<div class="stars">
									<div class="bar" style="width: {{ !empty($review->team_doctor_rating) && ($review->review_to_id == $review->dentist_id) ? $review->team_doctor_rating/5*100 : $review->rating/5*100 }}%;">
									</div>
								</div>
								<span class="rating">
									({{ !empty($review->team_doctor_rating) && ($review->review_to_id == $review->dentist_id) ? $review->team_doctor_rating : $review->rating }})
								</span>
							</div>
							<span class="review-date">
								{{ $review->created_at ? $review->created_at->toFormattedDateString() : '-' }}
							</span>
						</div>
						<div class="review-content">
							{!! nl2br($review->answer) !!}
							<a href="{{ !empty($review->review_to_id) ? $review->original_dentist->getLink() : ($review->dentist ? $review->dentist->getLink() : $review->clinic->getLink()) }}?review_id={{ $review->id }}" class="more">
								{!! nl2br(trans('trp.page.profile.trp.show-entire')) !!}
								
							</a>
						</div>
						@if(!empty($review->review_to_id) && !empty($review->original_dentist))
							<a href="{{ $review->original_dentist->getLink() }}?popup=recommend-dentist" class="recommend-button">
								<img src="https://reviews.dentacoin.com/img-trp/thumb-up.svg">
								{!! nl2br(trans('trp.page.profile.trp.recommend-dentist')) !!}
							</a>
						@elseif(!empty($review->dentist) && !$user->is_dentist)
							<a href="{{ $review->dentist->getLink() }}?popup=recommend-dentist" class="recommend-button">
								<img src="https://reviews.dentacoin.com/img-trp/thumb-up.svg">
								{!! nl2br(trans('trp.page.profile.trp.recommend-dentist')) !!}
							</a>
						@elseif(!empty($review->clinic))
							<a href="{{ $review->clinic->getLink() }}?popup=recommend-dentist" class="recommend-button">
								<img src="https://reviews.dentacoin.com/img-trp/thumb-up.svg">
								{!! nl2br(trans('trp.page.profile.trp.recommend-dentist')) !!}
							</a>
						@endif
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