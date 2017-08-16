<div class="panel-body review" >
	<div class="media">
		<div class="media-left">
			<img src="{{ $review->$user_field->getImageUrl(true) }}" />
			@if($review->verified)
				<a href="javascript:;" class="label label-success label-trusted" title="{{ trans('front.common.trusted-review') }}">
					{{ trans('front.common.trusted-review') }}
				</a>
			@endif
		</div>
		<div class="media-body">
			<h2 class="media-heading">
				@if(!empty($reviews_out))
					<a href="{{ $review->$user_field->getLink() }}">
				@endif
				{{ $review->$user_field->name }}
				@if(!empty($reviews_out))
					</a>
				@endif
			</h2>

			@if($review->$user_field->country)
				<p>
					<i class="fa fa-map-marker"></i> 
					@if($review->$user_field->city)
						{{ $review->$user_field->city->name }}, {{ $review->$user_field->country->name }}
					@else
						{{ $review->$user_field->country->name }}
					@endif
				</p>
			@endif
			<p>
				<i class="fa fa-calendar"></i> 
				{{ $review->created_at ? trans('front.common.date-on', ['date' => $review->created_at->toFormattedDateString() ]) : '-' }}
			</p>


			<p class="upvote-wrpapper" {!! $review->upvotes ? '' : 'style="display: none;"' !!} >
				<i class="fa fa-heart"></i> 
				{!! trans('front.page.dentist.people-find-useful', [ 'count' => '<span class="upvote-count">'.intval($review->upvotes).'</span>' ]) !!}
			</p>
		</div>


		@if( !($my_upvotes && in_array($review->id, $my_upvotes) ) && !( !empty($user) && $review->user_id == $user->id ) )
			<a class="btn btn-primary useful {{ !$user ? 'needs-login' : '' }} {{ $user && !$user->phone_verified ? 'verify-phone' : '' }}" href="javascript:;" data-review-id="{{ $review->id }}" data-done-text="{{ trans('front.page.dentist.helpful-button-clicked') }}">
				<i class="fa fa-heart"></i> 
				{{ trans('front.page.dentist.helpful-button') }}
			</a>
		@endif


		<div class="sharer" data-href="{{ $item->getLink().'/'.$review->id }}">
			<span>
				{{ trans('front.page.dentist.share') }}
			</span>
			<a class="fb" href="javascript:;">
				<i class="fa fa-facebook"></i>
			</a>
			<a class="tw" href="javascript:;">
				<i class="fa fa-twitter"></i>
			</a>
			<a class="gp" href="javascript:;">
				<i class="fa fa-google-plus"></i>
			</a>
		</div>


	</div>
</div>
@if(!$user)
	<div class="panel-body review login-form user-login-upvote" style="display: none;">
		<h3>
			{{ trans('front.page.dentist.review-login-title') }}
		</h3>
		<p>
			{{ trans('front.page.dentist.review-login-hint') }}
		</p>
		<div class="col-md-6">
			<a class="btn btn-primary btn-block" href="{{ getLangUrl('register') }}">
				{{ trans('front.page.dentist.review-login-register') }}
			</a>
		</div>
		<div class="col-md-6">
			<a class="btn btn-default btn-block" href="{{ getLangUrl('login') }}">
				{{ trans('front.page.dentist.review-login-login') }} 
			</a>
		</div>
	</div>
@endif
<div class="panel-body review" >
	<div class="ratings">
		<div class="stars">
			<div class="bar" style="width: {{ getStarWidth($review->rating) }}px;">
			</div>
		</div>
		<div class="rating">
			<b>
				@if(empty($reviews_out))
					{{ trans('front.page.dentist.review-comment', ['name' => $review->$user_field->getName()]) }}:
				@else
					{{ trans('front.page.dentist.review-comment-out', ['name' => $review->$user_field->getName()]) }}:
				@endif
			</b> 
			{!! nl2br($review->answer) !!}
		</div>
	</div>
</div>
@if($review->reply || ( !empty($user) && $review->dentist_id==$user->id ) )
	<div class="panel-body review" >
		<div class="ratings">
			<div class="rating">
				@if(!$review->reply)
					{!! Form::open(array('url' => $item->getLink().'/reply/'.$review->id, 'method' => 'post', 'class' => 'form-horizontal reply-form')) !!}
						<div class="form-group">
							<div class="col-md-12">
								<p>
									@if(empty($reviews_out))
										{{ trans('front.page.dentist.review-write-reply', [ 'name' => $review->$user_field->getName() ] ) }}
									@else
										{{ trans('front.page.dentist.review-reply-out', [ 'name' => $review->user->getName() ] ) }}
									@endif									
								</p>

				                <div class="alert alert-warning" style="display: none;">
				                	{{ trans('front.page.dentist.review-reply-invalid') }}
				                </div>

				                {{ Form::textarea( 'reply', '', array( 'class' => 'form-control review-reply', 'placeholder' => trans('front.page.dentist.review-reply-placeholder') ) ) }}

								<h3>
					                {{ Form::submit( trans('front.page.dentist.review-reply-submit') , array('class' => 'btn btn-primary btn-block' )) }}
								</h3>
							</div>
						</div>
					{!! Form::close() !!}
				@endif
				<div class="the-reply" {!! !$review->reply ? 'style="display: none;"' : '' !!} >
					<b>
						{{ trans('front.page.dentist.review-reply', ['name' => $review->dentist->getName() ]) }}:
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
	<div style="display: none;">
		@foreach($review->answers as $answer)
			<div class="panel-body rating-panel">
				<h2>{{ $answer->question['label'] }}</h2>
            	@foreach(json_decode($answer->question['options'], true) as $i => $option)
                	<div class="rating-line clearfix">
	                	<div class="rating-left">
	                		{{ $option[0] }}
	                	</div>

						<div class="ratings">
							<div class="stars">
								<div class="bar" style="width: {{ getStarWidth(json_decode($answer->options, true)[$i]) }}px;">
								</div>
							</div>
						</div>

	                	<div class="rating-right">
	                		{{ $option[1] }}
	                	</div>
                	</div>
            	@endforeach
        	</div>
		@endforeach
	</div>
	<a href="javascript:;" class="btn btn-primary btn-block btn-show-review" data-alt-text="{{ trans('front.page.dentist.review-hide-all') }}">
		{{ trans('front.page.dentist.review-show-all') }}
	</a>
</div>