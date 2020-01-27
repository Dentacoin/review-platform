<link rel="stylesheet" type="text/css" href="https://urgent.reviews.dentacoin.com/css/fb-tab.css" />

<div id="trp-facebook-tab">
	<div class="list-reviews">
		@foreach($reviews as $review)
			<div class="list-review">
				<div class="list-review-left">
					<a href="{{ $user->getLink().'?review_id='.$review->id }}" target="_blank" class="review-avatar" style="background-image: url('{{ $review->user->getImageUrl(true) }}');"></a>
					<span class="review-date">
						{{ $review->created_at ? date('d/m/Y', $review->created_at->timestamp) : '-' }}
					</span>
				</div>
				<div class="list-review-right">
					@if($review->title)
		    			<a href="{{ $user->getLink().'?review_id='.$review->id }}" target="_blank" class="review-title">
		    				“{{ $review->title }}”
		    			</a>
	    			@endif
	    			<div class="ratings">
						<div class="stars">
							<div class="bar" style="width: {{ $review->rating/5*100 }}%;">
							</div>
						</div>
					</div>
					<div class="review-content">
						{!! nl2br($review->answer) !!}
					</div>
					<span class="review-name">{{ !empty($review->user->self_deleted) ? ($review->verified ? 'Verified Patient' : 'Deleted User') : $review->user->name }}</span>
					<span class="mobile-review-date">
						{{ $review->created_at ? date('d/m/Y', $review->created_at->timestamp) : '-' }}
					</span>
				</div>
			</div>
    	@endforeach
    </div>
</div>