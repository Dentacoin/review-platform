<style type="text/css">
	#trp-widget {
		box-sizing: border-box;
		font-family: sans-serif;
		font-size: 14px;
	}
	#trp-widget * {
		box-sizing: inherit;
		font-family: inherit;
		font-size: inherit;
	}
	#trp-widget i {
		font-family: 'FontAwesome';
	}
	#trp-widget h2 {
		box-sizing: border-box;
	    box-shadow: 0px 0px 5px 0px rgba(18, 101, 133, 0.5);
	    border: none;
	    background-image: none;
	    background: #126585;
	    color: white;
	    padding: 10px 15px;
	    border-top-right-radius: 5px;
	    border-top-left-radius: 5px;
	    margin-bottom: 0px;
	    font-size: 24px;
	}
	#trp-widget h2 a {
		text-decoration: underline;
		color: white;
	}

	#trp-widget .review-list {
		padding: 15px;
	    border: 1px solid rgba(18, 101, 133, 0.5);
	    border-bottom-right-radius: 5px;
	    border-bottom-left-radius: 5px;
	    border-top: none;
	}

	#trp-widget .panel-default {
	    margin-bottom: 20px;
	    margin-top: 0px;
		box-shadow: 0px 0px 5px 0px rgba(18, 101, 133, 0.5);
	    background: white;
	    border-radius: 5px;
	    border: none;
	}

	#trp-widget .panel-default:last-child {
		margin-bottom: 0px;
	}

	#trp-widget .panel-body {
	    position: relative;
	    box-shadow: 0px 5px 5px -5px rgba(18, 101, 133, 0.5);
    	padding: 15px;
	}

	#trp-widget .panel-body:last-child {
		box-shadow: none;
	}

	#trp-widget .media-left {
		display: table-cell;
    	vertical-align: top;
    	padding-right: 10px;
	}
	
	#trp-widget .media-left img {
		width: 120px;
	}

	#trp-widget .media-left .label {
		text-decoration: none;
		display: block;
	    margin-top: 5px;
	    padding: 5px 0px;
		background-color: #2ab27b;
		font-size: 75%;
	    font-weight: 700;
	    line-height: 1;
	    color: #fff;
	    text-align: center;
	    white-space: nowrap;
	    vertical-align: baseline;
	    border-radius: .25em;
	}

	#trp-widget .media-body {
		display: table-cell;
    	vertical-align: top;
	}

	#trp-widget .media-body p {
		margin: 0 0 5px;
	}

	#trp-widget .media-heading {
		margin-top: 0;
    	margin-bottom: 5px;
    	font-size: 30px;
	    font-weight: 500;
	    line-height: 1.1;
	}

	#trp-widget .ratings {
		text-align: center;
		margin-top: 10px;
	}

	#trp-widget .ratings .stars {
	    width: 222px;
	    height: 30px;
	    display: inline-block;
	    background: url('https://reviews.dentacoin.com/img/star-empty.png') 50% 50% no-repeat;
	    margin-bottom: 10px;
	}
	
	#trp-widget .ratings .stars .bar {
		background: url('https://reviews.dentacoin.com/img/star-full.png') 0% 50% no-repeat;
    	height: 30px;
	}

	#trp-widget .ratings .rating {
		text-align: left;
		margin-bottom: 5px;
	}

	#trp-widget .btn-primary {
		display: block;
		text-align: center;
		text-decoration: none;
		color: #fff;
	    background-color: #126585;
	    border-color: #126585;
		box-shadow: inset 0 1px 0 rgba(255,255,255,.15), 0 1px 1px rgba(0,0,0,.075);
	    text-shadow: 0 -1px 0 rgba(0,0,0,.2);
	    touch-action: manipulation;
	    cursor: pointer;
	    background-image: none;
	    border: 1px solid transparent;
	    white-space: nowrap;
	    padding: 6px 12px;
	    font-size: 14px;
	    line-height: 1.6;
	    border-radius: 4px;
        margin-bottom: 0;
	    font-weight: 400;
	    text-align: center;
	    vertical-align: middle;
	}
	#trp-widget .btn-primary:hover {
		background-color: #2a88bd;
    	border-color: #2a88bd;
	}

	@media (max-width: 480px) {

		#trp-widget .media-left {
		    width: 100%;
		    margin: 0px 0px 10px 0px;
		    display: block;
		}

		#trp-widget .media-left img {
			width: 100%;
		}
	}
</style>
<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
<div id="trp-widget">
	<h2>
		{!! trans('front.page.widget.title', [
			'user' => $user->getName(),
			'link' => '<a target="_top" href="'.getLangUrl('/').'">',
			'endlink' => '</a>',
		]) !!}
	</h2>
	<div class="review-list">
		@if($reviews->isEmpty())
			<div class="alert alert-info">
				{!! trans('front.page.widget.no-reviews', [
					'user' => $user->getName(),
					'link' => '<a target="_top" href="'.$user->getLink().'">',
					'endlink' => '</a>',
				]) !!}
			</div>
		@else
			@foreach($reviews as $review)
				<div class="panel panel-default">
					<div class="panel-body review" >
						<div class="media">
							<div class="media-left">
								<img src="{{ $review->user->getImageUrl(true) }}" />
								@if($review->verified)
									<a target="_top" href="javascript:;" class="label label-success label-trusted" title="{{ trans('front.common.trusted-review') }}">
										{{ trans('front.common.trusted-review') }}
									</a>
								@endif
							</div>
							<div class="media-body">
								<div class="media-heading">
									{{ $review->user->name }}
								</div>

								@if($review->user->country)
									<p>
										<i class="fa fa-map-marker fa-fw"></i> 
										@if($review->user->city)
											{{ $review->user->city_name ? $review->user->city_name : $review->user->city->name }}, {{ $review->user->country->name }}
										@else
											{{ $review->user->country->name }}
										@endif
									</p>
								@endif
								<p>
									<i class="fa fa-calendar fa-fw"></i> 
									{{ $review->created_at ? trans('front.common.date-on', ['date' => $review->created_at->toFormattedDateString() ]) : '-' }}
								</p>

								@if($review->upvotes)
									<p class="upvote-wrpapper">
										<i class="fa fa-heart fa-fw"></i> 
										{!! trans('front.page.dentist.people-find-useful', [ 'count' => '<span class="upvote-count">'.intval($review->upvotes).'</span>' ]) !!}
									</p>
								@endif
							</div>

						</div>
					</div>
					<div class="panel-body review" >
						<div class="ratings">
							<div class="stars">
								<div class="bar" style="width: {{ getStarWidth($review->rating) }}px;">
								</div>
							</div>
							<div class="rating">
								<b>
									{{ trans('front.page.dentist.review-comment', ['name' => $review->user->getName()]) }}:
								</b> 
								{!! nl2br($review->answer) !!}
							</div>
						</div>
					</div>
					@if($review->reply)
						<div class="panel-body review" >
							<div class="ratings">
								<div class="rating">
									<div class="the-reply" {!! !$review->reply ? 'style="display: none;"' : '' !!} >
										<b>
											{{ trans('front.page.dentist.review-reply', ['name' => $review->dentist_id ? $review->dentist->getName() : $review->clinic->getName() ]) }}:
										</b> 
										<span class="reply-content">
											{!! nl2br($review->reply) !!}
										</span>
									</div>
								</div>
							</div>
						</div>
					@endif
					<div class="panel-body review" >
						<a target="_top" href="{{ $user->getLink() }}" class="btn btn-primary btn-block btn-show-review">
							{{ trans('front.page.dentist.review-show-all') }}
						</a>
					</div>
				</div>
			@endforeach
		@endif
	</div>
</div>
