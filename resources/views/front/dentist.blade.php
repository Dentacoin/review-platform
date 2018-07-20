@extends('front')

@section('content')

<h1 class="main-title">{{ $item['name'] }}</h1>

<div class="container clearfix profile-wrapper">
	<div class="col-md-3 profile-img">
		<img src="{{ $item->getImageUrl(true) }}" alt="{{ $item->name }}">
	</div>
	<div class="col-md-3 insert-map">
		@if(!empty($item->country)) 
			<p>
				<i class="fa fa-map-marker"></i> 
				@if(!empty($item->city)) 
					{{ $item->city->name }}, 
				@endif
				{{ $item->country->name }}
			</p>
		@endif

		@if($item->website)
			<p>
				<i class="fa fa-external-link"></i> 
				<a href="{{ $item->getWebsiteUrl() }}" target="_blank">{{ $item->website }}</a>
			</p>
		@endif

		@if($item->categories->isNotEmpty())
			<p>
				<i class="fa fa-graduation-cap"></i> 
				{{ implode(', ', $item->parseCategories($categories) ) }}
			</p>
		@endif

		@if($admin)
			<p>
				<i class="fa fa-user-circle-o"></i>
				<a href="{{ url('cms/users/loginas/'.$item->id) }}">
					{{ trans('front.page.dentist.login-as') }}
				</a><br/>
			</p>
			<p>
				<i class="fa fa-edit"></i>
				<a href="{{ url('cms/users/edit/'.$item->id) }}">
					{{ trans('front.page.dentist.open-cms') }}
				</a><br/>
			</p>
		@endif

		@if( $item->invited_by && !$item->verified ) 
			<div>
				<a class="btn-claim" style="margin: 0px;" href="javascript:;">
					<i class="fa fa-question-circle"></i>
					{{ trans('front.page.dentist.claim-info') }} {{ trans('front.page.dentist.claim-button') }}
				</a>
			</div>
		@endif

		@if($item->country && $item->city && ($item->address || $item->zip) )
			<a href="javascript:;" class="show-map">
				<!-- <i class="fa fa-map"></i> -->
				{{ trans('front.page.'.$current_page.'.show-map') }}
			</a>

			<a href="javascript:;" class="hide-map">
				<!-- <i class="fa fa-map"></i> -->
				{{ trans('front.page.'.$current_page.'.hide-map') }}
			</a>
		@endif

	</div>

	<div class="col-md-3 rating-panel">
		<h2>
        	{{ trans('front.page.dentist.rating-title-overall') }}
    	</h2>

    	<div class="rating-line clearfix">
			<div class="ratings">
				<div class="stars {{ $item->is_clinic ? 'green-stars' : '' }}">
					<div class="bar" style="width: {{ getStarWidth($item->avg_rating) }}px;">
					</div>
				</div>
				<div class="rating">
					{!! trans('front.page.dentist.rating-based-on', ['count' => '<b>'.$item->ratings.'</b>' ]) !!}
				</div>
				<a href="javascript:;" class="btn-block" id="btn-show-whole-review" data-alt-text="{{ trans('front.page.'.$current_page.'.hide-whole-review') }}">
                	{{ trans('front.page.'.$current_page.'.show-whole-review') }}
                </a>
			</div>
    	</div>
	</div>
	<div class="col-md-3 profile-map">
		@if($item->country && $item->city && ($item->address || $item->zip) )
			<div class="map" data-address="{{ $item->country->name.', '.$item->city->name.($item->zip ? ', '.$item->zip : '').($item->address ? ', '.$item->address : '') }}">
			</div>
		@endif
	</div>

	<div class="strength-line">
		<p>{{ trans('front.page.strength.title') }}: <b>{{ $item->is_dentist ? trans('front.page.strength.dentist.level-'.$item->getStrengthNumber()) : trans('front.page.strength.patient.level-'.$item->getStrengthNumber()) }}</b></p>
		<div class="empty-line level{{ $item->getStrengthNumber() }} {{ $item->is_dentist ? '' : 'patient-strength'}}">
			<div class="cutter">
				<div class="full-line">
				</div>
			</div>
		</div>
	</div>
</div>


<ul class="nav nav-tabs dentist-tabs container">
	@if($item->description)
        <li>
        	<a href="#about-tab" data-toggle="tab" aria-expanded="true">About us</a>
        </li>
    @endif
    @if($item->photos->isNotEmpty())
        <li class="gallery-tab-btn">
        	<a href="#gallery-tab" data-toggle="tab" aria-expanded="false">Gallery</a>
        </li>
    @endif
    <li class="active">
    	<a href="#review-tab" data-toggle="tab">{{ trans('front.page.'.$current_page.'.write-review-button') }}</a>
    </li>
    @if($item->is_clinic && $item->team->isNotEmpty())
    	<li class="dentists-tab-btn">
        	<a href="#dentists-tab" data-toggle="tab" aria-expanded="false">Dentists</a>
        </li>
    @endif
</ul>
<div class="tab-content dentist-tab-content profile-content">
	<div class="container">
		@if($item->description)
	        <div class="tab-pane fade" id="about-tab">
		    	<p>{!! nl2br($item->description) !!}</p>
	        </div>
	    @endif
	    @if($item->photos->isNotEmpty())
	        <div class="tab-pane fade" id="gallery-tab">
	        	<div class="gallery-slider clearfix">
		            @foreach($item->photos as $photo)
						<div class="avatar">
							<a href="{{ $photo->getImageUrl() }}" data-lightbox="user-gallery">
								<img src="{{ $photo->getImageUrl(true) }}" alt="{{ $item->name }}">
							</a>
						</div>
					@endforeach
				</div>
	    	</div>
	    @endif
        <div class="tab-pane fade active in" id="review-tab">
        	<h3>{{ trans('front.page.'.$current_page.'.write-review-title', ['name' => $item->name]) }}</h3>
    		<p>{{ trans('front.page.'.$current_page.'.write-review-hint') }}</p>

    		@if(!empty($user) && !$user->is_dentist && empty($my_review))
	    		<a class="btn btn-primary write-review" id="write-review-btn" href="javascript:;">
					{{ trans('front.page.'.$current_page.'.write-review-button') }}
				</a>
			@endif
        </div>
	    @if($item->is_clinic && $item->team->isNotEmpty())
	        <div class="tab-pane fade" id="dentists-tab">

	        	@foreach($item->team as $team)
			    	<a class="clinic-dentists-wrapper col-md-8 col-md-offset-2" href="{{ $team->clinicTeam->getLink() }}">
						<div class="media">
							<div class="media-left avatar">
								<img src="{{ $team->clinicTeam->getImageUrl(true) }}" />
							</div>
							<div class="media-body">
								<h2 class="media-heading">
									{{ $team->clinicTeam->getName() }}
								</h2>

								@if($team->clinicTeam->country)
									<div class="location">
										<i class="fa fa-map-marker"></i> 
										@if($team->clinicTeam->city)
											{{ $team->clinicTeam->city->name }}, {{ $team->clinicTeam->country->name }}
										@else
											{{ $team->clinicTeam->country->name }}
										@endif
									</div>
								@endif

								@if($team->clinicTeam->categories->isNotEmpty())
									<div class="categories">
										<i class="fa fa-graduation-cap"></i> 
										{{ implode(', ', $team->clinicTeam->parseCategories($categories) ) }}
									</div>
								@endif

								<div class="ratings">
									@if($team->clinicTeam->ratings)
										<div class="stars">
											<div class="bar" style="width: {{ getStarWidth($team->clinicTeam->avg_rating) }}px;">
											</div>
										</div>
									@endif
									<div class="rating">
										@if($team->clinicTeam->ratings)
											{!! trans('front.page.dentists.rating', [ 'rating' => '<b>'.$team->clinicTeam->avg_rating.'</b>', 'reviews' => '<b>'.$team->clinicTeam->ratings.'</b>' ] ) !!}
										@else
											<b>{{ trans('front.common.no-reviews') }}</b>
										@endif
									</div>
								</div>

							</div>
						</div>
					</a>
				@endforeach
	        </div>
	    @endif
    </div>
</div>

<div class="container single-review">
	<div class="clearfix">
		<div class="col-md-8">
			<h2 class="review-title">{{ trans('front.page.'.$current_page.'.reviews-title', [ 'name' => $item->name ]) }}</h2>
		</div>
		<div class="col-md-4">
    		@if(!empty($user) && !$user->is_dentist && empty($my_review))
				<a class="write-review" id="write-review-btn" href="javascript:;">
					{{ trans('front.page.'.$current_page.'.write-review-button') }}
				</a>
			@endif
		</div>
	</div>  		
    <div class="reviews-panel">

		@if($item->reviews_in()->isNotEmpty() )
			@foreach($reviews as $review)
				
				<div class="profile-review">
					<div class="review main-review">
						<div class="media">
							<div class="col-md-2 hidden-sm hidden-xs ">
								<div class="media-left">
									<img src="{{ $review->user->getImageUrl(true) }}" />
									@if($review->verified)
										<span class="label label-success label-trusted" title="{{ trans('front.common.trusted-review') }}">
											{{ trans('front.common.trusted-review') }}
										</span>
									@endif
								</div>
							</div>
							<div class="col-md-3 hidden-sm hidden-xs ">
								<div class="media-body">
									<h2 class="media-heading">
										@if(!empty($reviews_out))
											<a href="{{ $review->user->getLink() }}">
										@endif
										{{ $review->user->name }}
										@if(!empty($reviews_out))
											</a>
										@endif
									</h2>

									@if($review->user->country)
										<p>
											<i class="fa fa-map-marker"></i>
											<span class="gray">
												@if($review->user->city)
													{{ $review->user->city->name }}, {{ $review->user->country->name }}
												@else
													{{ $review->user->country->name }}
												@endif
											</span>														
										</p>
									@endif
									<p>
										<i class="fa fa-calendar"></i>
										<span class="gray">
											{{ $review->created_at ? trans('front.common.date-on', ['date' => $review->created_at->toFormattedDateString() ]) : '-' }}
										</span>
									</p>


									<p class="upvote-wrpapper" {!! $review->upvotes ? '' : 'style="display: none;"' !!} >
										<i class="fa fa-heart"></i> 
										{!! trans('front.page.dentist.people-find-useful', [ 'count' => '<span class="upvote-count">'.intval($review->upvotes).'</span>' ]) !!}
									</p>
								</div>
							</div>
							<div class="hidden-lg hidden-md col-sm-12 media-left">
								<img src="{{ $review->user->getImageUrl(true) }}" />
								<span class="mobile-user-title">{{ $review->user->name }}</span>
							</div>

							<div class="col-md-7 col-sm-12">
								<div class="media-body review" >
									<div class="ratings">
										<div class="stars">
											<div class="bar" style="width: {{ getStarWidth($review->rating) }}px;">
											</div>
										</div>
										<span class="hidden-lg hidden-md label label-success label-trusted" title="{{ trans('front.common.trusted-review') }}">
											{{ trans('front.common.trusted-review') }}
										</span>
										<div class="rating">
											@if($review->answer)
												<b>
													@if(empty($reviews_out))
														{{ trans('front.page.dentist.review-comment', ['name' => $review->user->getName()]) }}:
													@else
														{{ trans('front.page.dentist.review-comment-out', ['name' => $review->user->getName()]) }}:
													@endif
												</b> 
												{!! nl2br($review->answer) !!}
												<br/>
											@endif
											@if($review->youtube_id)
												<b>
													@if(empty($reviews_out))
														{{ trans('front.page.dentist.review-video', ['name' => $review->user->getName()]) }}:
													@else
														{{ trans('front.page.dentist.review-video-out', ['name' => $review->user->getName()]) }}:
													@endif
												</b> 
												@if($review->youtube_approved || (!empty($user) && $user->id==$review->user_id ) )
													<div class="videoWrapper">
														<iframe width="560" height="315" src="https://www.youtube.com/embed/{{ $review->youtube_id }}" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
													</div>
												@else
													<div class="alert alert-info">
														{{ trans('front.page.dentist.review-video-unapproved') }}
													</div>
												@endif
											@endif

											<a href="javascript:;" class="new-btn-show-review" data-alt-text="{{ trans('front.page.dentist.review-hide-all') }}" data-user-id="{{ $review->user->id }}">
												{{ trans('front.page.dentist.review-show-all') }}
											</a>
										</div>

										<div class="sharer" data-href="{{ $item->getLink().'/'.$review->id }}">
											
											@if( !($my_upvotes && in_array($review->id, $my_upvotes) ) && !( !empty($user) && $review->user_id == $user->id ) )
												<a class="useful {{ !$user ? 'needs-login' : '' }}" href="javascript:;" data-review-id="{{ $review->id }}" data-done-text="{{ trans('front.page.dentist.helpful-button-clicked') }}">
													<i class="fa fa-heart"></i>
												</a>
											@endif
											<a class="fb" href="javascript:;">
												<i class="fa fa-facebook"></i>
											</a>
											<a class="tw" href="javascript:;">
												<i class="fa fa-twitter"></i>
											</a>
											<a class="gp" href="javascript:;">
												<i class="fa fa-google-plus"></i>
											</a>
											<!-- <span>
												{{ trans('front.page.dentist.share') }}
											</span> -->
										</div>
									</div>
								</div>
								@if($review->reply || ( !empty($user) && ($review->dentist_id==$user->id || $review->clinic_id==$user->id) ) )
									<div class="panel-body review" >
										<div class="ratings">
											<div class="rating">
												@if(!$review->reply)
													{!! Form::open(array('url' => $item->getLink().'/reply/'.$review->id, 'method' => 'post', 'class' => 'form-horizontal reply-form')) !!}
														<div class="form-group">
															<div class="col-md-12">
																<p>
																	@if(empty($reviews_out))
																		{{ trans('front.page.dentist.review-write-reply', [ 'name' => $review->user->getName() ] ) }}
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
				</div>
			@endforeach
			@else 
		    	<div class="alert alert-info">
		    		{{ trans('front.page.'.$current_page.'.no-reviews', ['name' => $item->getName()]) }}
		    	</div>
			@endif
    </div>
</div>
			

<div class="new-popup popup-tutorial" id="review-form">
	<div class="new-popup-wrapper">
		<div class="inner">
			@if($user)
				@if($dentist_limit_reached)
					<div class="panel-body review rating-panel">
						<div class="alert alert-info">
							@if($has_asked_dentist)
								@if($has_asked_dentist->status=='no')
									{{ trans('front.page.'.$current_page.'.write-review-limit-dentists-denied', ['name' => $item->getName()]) }}
								@else
									{{ trans('front.page.'.$current_page.'.write-review-limit-dentists-waiting', ['name' => $item->getName()]) }}
								@endif
							@else
								{{ trans('front.page.'.$current_page.'.write-review-limit-dentists', ['name' => $item->getName()]) }}
								<br/>
								<br/>
								<a href="{{ $item->getLink().'/ask' }}" class="btn btn-primary btn-block">
									{!! trans('front.page.'.$current_page.'.write-review-limit-dentists-ask', [
										'name' => $item->getName(),
									]) !!}
								</a>
							@endif
						</div>
	                </div>
				@elseif($review_limit_reached)
					<div class="panel-body review rating-panel">
						<div class="alert alert-info">
							{{ trans('front.page.'.$current_page.'.write-review-limit-'.$review_limit_reached) }}
						</div>
	                </div>
				@elseif( !empty($user) && !($user->is_verified && $user->email) )
					<div class="panel-body review rating-panel">
	                    @include('front.template-parts.verify-email', [
	                    	'cta' => trans('front.page.'.$current_page.'.not-verified',[
	                    		'email' => '<b>'.$user->email.'</b>'
	                    	])
	                    ])							
	                </div>
				@elseif(!empty($user) && !$user->is_dentist && empty($my_review))
					@include('front.template-parts.submit-review-form')
				@endif
			@else
				<div class="panel-body review login-form">
					<h3>
						{{ trans('front.page.'.$current_page.'.write-review-login-title') }}
					</h3>
					<p>
						{{ trans('front.page.'.$current_page.'.write-review-login-hint') }}
					</p>
					<div class="col-md-6">
						<a class="btn btn-primary btn-block" href="{{ getLangUrl('register') }}">
							{{ trans('front.page.'.$current_page.'.write-review-login-register') }}
						</a>
					</div>
					<div class="col-md-6">
						<a class="btn btn-default btn-block" href="{{ getLangUrl('login') }}">
							{{ trans('front.page.'.$current_page.'.write-review-login-login') }}
						</a>
					</div>
				</div>
			@endif

		</div>
		<a href="javascript:;" class="closer">
			<i class="fa fa-remove"></i>
		</a>
	</div>
</div>

<div class="new-popup popup-tutorial" id="show-review">
	<div class="new-popup-wrapper">
		<div class="inner">

		</div>
		<a href="javascript:;" class="closer">
			<i class="fa fa-remove"></i>
		</a>
	</div>
</div>


<div class="new-popup popup-tutorial" id="show-whole-review-form">
	<div class="new-popup-wrapper">
		<div class="inner">
			<div class="whole-review">
                @foreach($questions as $question)

	                <div class="panel-body rating-panel">
	                	<h2>{{ $question['label'] }}</h2>

	                	<div class="rating-line pre-aggregation clearfix">
		                	<div class="rating-left">
	                			{{ trans('front.page.dentist.rating-title-poor') }}
		                	</div>

		                	<div class="rating-right">
		                		{{ trans('front.page.dentist.rating-title-excelent') }}
		                	</div>
							<div class="ratings">
								<div class="stars">
									<div class="bar" style="width: {{ !empty($aggregated_rates_total[$question->id]) ? getStarWidth($aggregated_rates_total[$question->id]) : 0 }}px;">
									</div>
								</div>
								<div class="rating">
									<a href="javascript:;" class="show-entire-aggregation">
	                					&#9660; {{ trans('front.page.dentist.rating-title-more-info') }} &#9660;
									</a>
								</div>
							</div>
		                	
	                	</div>

	                	<div class="aggregation" style="display: none;">
		                	@foreach(json_decode($question['options'], true) as $i => $option)
			                	<div class="rating-line clearfix">
				                	<div class="rating-left">
				                		{{ $option[0] }}
				                	</div>
				                	<div class="rating-right">
				                		{{ $option[1] }}
				                	</div>

									<div class="ratings">
										<div class="stars">
											<div class="bar" style="width: {{ !empty($aggregated_rates[$question->id][$i]) ? getStarWidth($aggregated_rates[$question->id][$i]) : 0 }}px;">
											</div>
										</div>
									</div>

			                	</div>
		                	@endforeach

							<div class="ratings" style="text-align: center;">
								<div class="rating">
									<a href="javascript:;" class="hide-entire-aggregation">
	                					&#9650; {{ trans('front.page.dentist.rating-title-less-info') }} &#9650;
									</a>
								</div>
							</div>
	                	</div>
	                </div>
                	
                @endforeach
            </div>

		</div>
		<a href="javascript:;" class="closer">
			<i class="fa fa-remove"></i>
		</a>
	</div>
</div>


@endsection