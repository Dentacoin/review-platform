<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700;900&family=Montserrat:wght@700;800&display=swap" rel="stylesheet">
<style type="text/css">
	#trp-widget {
		box-sizing: border-box;
		font-family: sans-serif;
		font-size: 14px;
	}

	#trp-widget * {
		box-sizing: inherit;
		font-family: 'Lato', sans-serif;
		font-size: inherit;
	}

	#trp-widget h2 {
		font-family: 'Montserrat', sans-serif;
		border: none;
		background-image: none;
		padding: 10px 0px;
		margin-bottom: 30px;
		font-size: 24px;
		color: #0564c6;
		font-weight: 900;
	}

	#trp-widget h2 a {
		font-family: 'Montserrat', sans-serif;
		text-decoration: underline;
		color: #0564c6;
		font-weight: 900;
	}

	#trp-widget .alert {
		border-radius: 30px;
		background: white;
		border: 2px solid #bbe6ff;
		font-size: 18px;
		padding: 25px 20px 25px 110px;
		text-align: left;
		position: relative;
	    word-break: break-word;
	    margin-top: 20px;
	    background: white;
		color: #332255;
		width: 100%;
	}

	#trp-widget .alert a {
		font-size: 18px;
		color: #332255;
	}

	#trp-widget .alert:before {
		content: "";
		display: inline-block;
		font-style: normal;
		font-variant: normal;
		text-rendering: auto;
		-webkit-font-smoothing: antialiased;
		position: absolute;
		left: 20px;
		top: 50%;
		transform: translateY(-50%);
		font-size: 40px;
		z-index: 10;

		width: 44px;
		height: 24px;
		background: url('https://reviews.dentacoin.com/img/new-alert-info.svg') no-repeat 0px 0px;
	}

	#trp-widget .alert:after {
		content: "";
		position: absolute;
		left: 0px;
		width: 82px;
		top: 0;
		bottom: 0;
		font-size: 40px;
		border-top-left-radius: 26px;
		border-bottom-left-radius: 26px;
		background-color: #bbe6ff;
	}

	#trp-widget .written-review {
    	margin-bottom: 60px;
	}

    #trp-widget .review-header {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
	}

	#trp-widget .review-header .review-avatar {
		max-width: 50px;
		border-radius: 50%;
		margin-right: 9px;
	}

    #trp-widget .review-header .review-name {
		font-size: 18px;
		color: #332255;
		font-weight: bold;
		display: block;
		margin-bottom: 10px;
	}

	#trp-widget .review-header .review-title {
		font-size: 20px;
		color: #332255;
		font-weight: 900;
		display: block;
	}

    #trp-widget .review-header>div {
		flex: 1;
	}

    #trp-widget .review-rating {
        display: flex;
        align-items: center;
        margin-bottom: 18px;
	}

    #trp-widget .review-rating .verify-review {
		padding: 2px 14px 4px;
		font-size: 15px;
		color: #14cab8;
		background-color: white;
		font-weight: normal;
		border-width: 1px;
		margin-right: 5px;
	}

    #trp-widget .review-rating .verify-review:hover {
		background-color: #14cab8;
		color: white;
	}

    #trp-widget .review-rating .trusted-sticker {
		display: inline-flex;
		align-items: center;
		font-size: 14px;
		color: white;
		background-color: #14cab8;
		padding: 3px 7px;
		border-radius: 5px;
		margin-left: 0px;
		margin-right: 12px;
	}

    #trp-widget .review-rating .trusted-sticker img {
		width: 15px;
		height: auto;
		margin-left: 5px;
	}

    #trp-widget .review-rating .ratings {
		display: flex;
		align-items: center;
	}

    #trp-widget .review-rating .ratings .stars {
		display: inline-block;
		margin-right: 5px;
		width: 124px;
		height: 22px;
		background: url('https://reviews.dentacoin.com/img-trp/stars-gray-average.png') 50% 50% no-repeat;
		background-size: contain;
	}

    #trp-widget .review-rating .ratings .stars .bar {
		background: url('https://reviews.dentacoin.com/img-trp/stars-blue-average.png') 0% 50% no-repeat;
		background-size: cover;
		height: 22px;
	}

    #trp-widget .review-rating .ratings .rating {
		font-size: 16px;
		color: #332255;
		margin-right: 10px;
		font-weight: normal;
	}

    #trp-widget .review-rating .review-date {
		font-size: 14px;
		color: #888888;
	}

    #trp-widget .review-content {
        font-size: 18px;
        color: #332255;
	}

    #trp-widget .btn-show-review {
		color: #0084d1;
		font-weight: 700;
		font-size: 18px;
		text-decoration: underline;
		text-transform: lowercase;
    }

	@media (max-width: 768px) {

		#trp-widget .written-review {
			margin-bottom: 40px;
			position: relative;
		}

		#trp-widget .review-header .review-name {
			margin-bottom: 5px;
		}

		#trp-widget .review-header .review-title {
			font-size: 18px;
		}

		#trp-widget .review-rating {
			flex-flow: row wrap;
		}

		#trp-widget .review-content {
			line-height: 29px;
		}
		
		#trp-widget .alert {
			padding: 10px 10px 10px 60px;
			border-radius: 16px;
			font-size: 14px;
		}

		#trp-widget .alert a {
			font-size: 14px;
		}

		#trp-widget .alert:before {
			left: 9px;
			width: 24px;
			height: 14px;
		}

		#trp-widget .alert:after {
			border-top-left-radius: 13px;
			border-bottom-left-radius: 13px;
			width: 44px;
		}
	}
</style>

<div id="trp-widget">
	<h2>
		{!! trans('trp.page.widget.title', [
			'user' => $user->getNames(),
			'link' => '<a target="_top" href="'.getLangUrl('/').'">',
			'endlink' => '</a>',
		]) !!}
	</h2>
	<div class="review-list">
		@if($reviews->isEmpty())
			<div class="alert alert-info">
				{!! trans('trp.page.widget.no-reviews', [
					'user' => $user->getNames(),
					'link' => '<a target="_top" href="'.$user->getLink().'">',
					'endlink' => '</a>',
				]) !!}
			</div>
		@else
			@foreach($reviews as $review)

				<div class="written-review">

					<div class="review-header">
						<img class="review-avatar" src="{{ $review->user->getImageUrl(true) }}"/>
						<div>
							<span class="review-name">
								{{ !empty($review->user->self_deleted) ? ($review->verified ? trans('trp.common.verified-patient') : trans('trp.common.deleted-user')) : $review->user->name }}: 
							</span>

							@if($review->title)
								<span class="review-title">
									“{{ $review->title }}”
								</span>
							@endif
						</div>
					</div>
					<div class="review-rating">
						<div class="trusted-sticker" {!! $review->verified ? '' : 'style="display:none;"' !!}>
							{!! nl2br(trans('trp.common.trusted')) !!}
							<img src="{{ url('img/info-white.svg') }}" width="15" height="15"/>
						</div>
						<div class="ratings average">
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
						{!! nl2br($review->answer) !!}...
						<a target="_top" href="{{ $user->getLink().'?review_id='.$review->id }}" class="btn-show-review">
							show full review
						</a>
					</div>
				</div>
			@endforeach
		@endif
	</div>
</div>
