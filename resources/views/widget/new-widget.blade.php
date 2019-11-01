<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
<link rel="stylesheet" type="text/css" href="https://reviews.dentacoin.com/css/widget.css" />

@if($layout == 'carousel')
	<link rel="stylesheet" href="https://unpkg.com/flickity@2/dist/flickity.min.css">
@endif

@if(!empty($height))
	<style type="text/css">
		#trp-widget-new {
			height: {!! $height !!}px;
		}
	</style>
@endif
@if(!empty($width))
	<style type="text/css">
		#trp-widget-new {
			width: {!! $width !!}%;
		}
	</style>
@endif

<div id="trp-widget-new" class="{!! $layout == 'badge' ? 'badge-wrapper' : '' !!}">
	@if($layout != 'badge')
		<h2>
			We Value Patient Feedback
		</h2>
	@endif

	@if($layout == 'carousel')
		<div class="widget-flickity" id="widget-flickity">
			@foreach($reviews as $review)
				<div class="widget-slide">
					<a href="{{ $user->getLink().'?review_id='.$review->id }}" target="_blank" class="review-avatar" style="background-image: url('{{ $review->user->getImageUrl(true) }}');"></a>
					@if($review->title)
		    			<a href="{{ $user->getLink().'?review_id='.$review->id }}" target="_blank" class="review-title">
		    				“{{ $review->title }}”
		    			</a>
	    			@endif
	    			<div class="ratings average">
						<div class="stars">
							<div class="bar" style="width: {{ $review->rating/5*100 }}%;">
							</div>
						</div>
					</div>
					<div class="review-content">
						{!! nl2br($review->answer) !!}
					</div>
					<p class="review-name">{{ !empty($review->user->self_deleted) ? ($review->verified ? 'Verified Patient' : 'Deleted User') : $review->user->name }}</p>
					<p class="review-date">
						{{ $review->created_at ? date('d/m/Y', $review->created_at->timestamp) : '-' }}
					</p>
				</div>
			@endforeach
		</div>

		<style type="text/css">
			@if(!empty($slide) && $slide == 3)
				#trp-widget-new .widget-flickity .widget-slide {
					width: calc(100%/3);
					padding: 0px 30px;
					border-right: 2px solid #eeeeee;
				}

				#trp-widget-new .widget-flickity {
					padding-left: 40px;
					padding-right: 40px;
				}

				@media screen and (max-width: 992px) {
					#trp-widget-new .widget-flickity .widget-slide {
						width: width: calc(100%/2);
						padding: 0px 20px;
					}

					#trp-widget-new .widget-flickity {
						padding-left: 20px;
						padding-right: 20px;
					}
				}

				@media screen and (max-width: 768px) {
					#trp-widget-new .widget-flickity .widget-slide {
						width: 100%;
						border-right: none;
					}
				}
			@else
				#trp-widget-new .widget-flickity .widget-slide {
					width: 100%;
				}

				@media screen and (max-width: 992px) {
					#trp-widget-new .widget-flickity .widget-slide {
						width: 100%;
						padding-left: 40px;
						padding-right: 40px;
					}
				}
			@endif
		</style>

	@elseif($layout == 'badge')

		<style type="text/css">
			body {
				background: transparent;
			}
		</style>

		<div class="tac">
			@if(!empty($badge) && $badge == 'mini')
				<a href="{{ $user->getLink() }}" target="_blank" class="badge-mini">
					<div class="mini-wrap">
						<div class="rating">
							{{ $user->avg_rating }}
						</div>
						<div class="ratings">
							<div class="stars">
								<div class="bar" style="width: {{ $user->avg_rating/5*100 }}%;">
								</div>
							</div>
						</div>
					</div>
					<div class="logo">
						<img src="https://reviews.dentacoin.com/img-trp/logo.png">
					</div>
				</a>
			@else
				<a href="{{ $user->getLink() }}" target="_blank" class="badge">
					<img src="https://reviews.dentacoin.com/img-trp/logo-blue.png">
					<div class="ratings average">
						<div class="stars">
							<div class="bar" style="width: {{ $user->avg_rating/5*100 }}%;">
							</div>
						</div>
						<div class="rating">
							{{ $user->avg_rating }}
						</div>
					</div>
				</a>
			@endif
		</div>

	@else
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

	@endif

	@if($layout != 'badge')
		<a href="https://reviews.dentacoin.com" class="widget-logo-wrap" target="_blank">
			<img src="https://reviews.dentacoin.com/img-trp/logo-blue.png">
		</a>
	@endif
</div>


@if($layout == 'carousel')
	<script src="https://code.jquery.com/jquery-3.3.1.min.js"  integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="  crossorigin="anonymous"></script>
	<script src="https://unpkg.com/flickity@2/dist/flickity.pkgd.min.js"></script>
	<script src="https://reviews.dentacoin.com/js-trp/widget.js"></script>
@endif