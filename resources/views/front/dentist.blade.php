@extends('front')

@section('content')

<div class="container">
	@include('front.errors')
	<div class="col-md-3">

		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">
					{{ $item['name'] }}
				</h3>
			</div>
			<div class="panel-body">
				@if(!empty($item->is_partner)) 
					<h3 class="label-partner">
						<span class="label label-success" style="display: block;">
							{{ trans('front.common.partner') }}
						</span>
					</h3>
				@endif

				<div class="avatar">
					<img src="{{ $item->getImageUrl(true) }}" alt="{{ $item->name }}">
				</div>
				@if( $item->invited_by && !$item->verified ) 
					<div class="alert alert-info">
						{{ trans('front.page.dentist.claim-info') }}
						<br/>
						<a class="btn btn-primary btn-block btn-claim" style="margin: 0px;" href="javascript:;">
							{{ trans('front.page.dentist.claim-button') }}
						</a>
					</div>
				@endif

				@if(!empty($item->country)) 
					<p>
						<i class="fa fa-map-marker"></i> 
						@if(!empty($item->city)) 
							{{ $item->city->name }}, 
						@endif
						{{ $item->country->name }}
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
			</div>
			<div class="panel-heading">
				<h3 class="panel-title">
					{{ trans('front.page.dentist.location') }}
				</h3>
			</div>
			<div class="panel-body">
				@if($item->country && $item->city && ($item->address || $item->zip) )
					<div class="map" data-address="{{ $item->country->name.', '.$item->city->name.($item->zip ? ', '.$item->zip : '').($item->address ? ', '.$item->address : '') }}">
					</div>
				@endif
				

				@if($item->country)
					<p>
						<i class="fa fa-map-marker"></i> 
						@if($item->city)
							@if($item->zip)
								{{ $item->zip }}, 
							@endif
							{{ $item->city->name }}, {{ $item->country->name }}
						@else
							{{ $item->country->name }}
						@endif
						@if($item->address)
							{{ $item->address }}
						@endif
					</p>
				@endif

				@if($item->is_verified || $item->fb_id)
					@if($item->phone)
						<p>
							<i class="fa fa-phone"></i> 
							{{ $item->country ? '+'.$item->country->phone_code : '' }} {{ $item->phone }}
						</p>
					@endif
					<p>
						<i class="fa fa-envelope"></i> 
						<a href="mailto:{{ $item->email }}">
							{{ $item->email }}
						</a>
					</p>
				@endif

				@if($item->website)
					<p>
						<i class="fa fa-external-link"></i> 
						<a href="{{ $item->getWebsiteUrl() }}" target="_blank">{{ $item->website }}</a>
					</p>
				@endif

				@if( !empty(json_decode($item->work_hours, true)) )
					<p>
						<i class="fa fa-clock-o"></i> 
						{{ trans('front.page.dentist.work-hours') }}
					</p>

					<table class="table table-striped">
                        @for($day=1;$day<=7;$day++)
                        	<tr>
                        		<td>
                        			{{ date('l', strtotime("Sunday +{$day} days")) }}
                        		</td>
                        		<td>
                        			{{ !empty(json_decode($item->work_hours, true)[$day]) ? json_decode($item->work_hours, true)[$day][0].' - '.json_decode($item->work_hours, true)[$day][1] : trans('front.page.dentist.work-hours-closed') }}
                        		</td>
                        	</tr>
                        @endfor
					</table>
				@endif


			</div>
			@if($item->photos->isNotEmpty())
				<div class="panel-heading">
					<h3 class="panel-title">
						{{ trans('front.page.dentist.gallery') }}
					</h3>
				</div>
				<div class="panel-body">
					@foreach($item->photos as $photo)
						<div class="avatar">
							<a href="{{ $photo->getImageUrl() }}" data-lightbox="user-gallery">
								<img src="{{ $photo->getImageUrl(true) }}" alt="{{ $item->name }}">
							</a>
						</div>
					@endforeach
				</div>
			@endif
		</div>


	</div>
	<div class="col-md-9">


        <div class="panel panel-default">
            <div class="panel-heading">
                <h1 class="panel-title">
                	{{ trans('front.page.dentist.rating-title', ['name' => $item->name ]) }}
                </h1>
            </div>
            @if($item->ratings)
                <div class="panel-body rating-panel">
                	<h2>
	                	{{ trans('front.page.dentist.rating-title-overall') }}
                	</h2>

                	<div class="rating-line clearfix">
	                	<div class="rating-left">
	                		{{ trans('front.page.dentist.rating-title-poor') }}
	                	</div>
						<div class="ratings">
							<div class="stars">
								<div class="bar" style="width: {{ getStarWidth($item->avg_rating) }}px;">
								</div>
							</div>
							<div class="rating">
								{!! trans('front.page.dentist.rating-based-on', ['count' => '<b>'.$item->ratings.'</b>' ]) !!}
							</div>
						</div>
	                	<div class="rating-right">
	                		{{ trans('front.page.dentist.rating-title-excelent') }}
	                	</div>
                	</div>
                </div>

                <div class="whole-review" style="display: none;">
	                @foreach($questions as $question)

		                <div class="panel-body rating-panel">
		                	<h2>{{ $question['label'] }}</h2>

		                	<div class="rating-line pre-aggregation clearfix">
			                	<div class="rating-left">
		                			{{ trans('front.page.dentist.rating-title-poor') }}
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


			                	<div class="rating-right">
			                		{{ trans('front.page.dentist.rating-title-excelent') }}
			                	</div>
		                	</div>

		                	<div class="aggregation" style="display: none;">
			                	@foreach(json_decode($question['options'], true) as $i => $option)
				                	<div class="rating-line clearfix">
					                	<div class="rating-left">
					                		{{ $option[0] }}
					                	</div>

										<div class="ratings">
											<div class="stars">
												<div class="bar" style="width: {{ !empty($aggregated_rates[$question->id][$i]) ? getStarWidth($aggregated_rates[$question->id][$i]) : 0 }}px;">
												</div>
											</div>
										</div>

					                	<div class="rating-right">
					                		{{ $option[1] }}
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
                <div class="panel-body rating-panel">
	                <a href="javascript:;" class="btn btn-primary btn-block" id="show-whole-review" data-alt-text="{{ trans('front.page.'.$current_page.'.hide-whole-review') }}">
	                	{{ trans('front.page.'.$current_page.'.show-whole-review') }}
	                </a>
                </div>
            @else
            	<div class="alert alert-info">
            		{{ trans('front.page.'.$current_page.'.no-reviews', ['name' => $item->getName()]) }}
            	</div>
            @endif

        </div>


		@if($user && $user->is_dentist)
		@elseif($my_review && $my_review->status=='accepted')

            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">
		                {{ trans('front.page.'.$current_page.'.your-review') }}
                    </h3>
                </div>

				@include('front.template-parts.review', [
					'review' => $my_review,
					'user_field' => 'user'
				])
					
            </div>
		@else
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">
                    	{{ trans('front.page.'.$current_page.'.write-review-title', ['name' => $item->name]) }}
                    </h3>
                </div>

					<div class="panel-body" >
					<p>
						{{ trans('front.page.'.$current_page.'.write-review-hint') }}
					</p>
					<a class="btn btn-primary btn-block {{ $user && !($user->phone_verified || $user->fb_id) ? 'verify-phone' : '' }}" id="write-review-btn" href="javascript:;">
						{{ trans('front.page.'.$current_page.'.write-review-button') }}
					</a>
				</div>
				<div id="review-form" style="display: none;">
					@if($user)
						@if($review_limit_reached)
							<div class="panel-body review rating-panel">
								<div class="alert alert-info">
									{{ trans('front.page.'.$current_page.'.write-review-limit-'.$review_limit_reached) }}
								</div>
			                </div>
						@elseif( !empty($user) && !($user->is_verified || $user->fb_id) )
							<div class="panel-body review rating-panel">
			                    @include('front.template-parts.verify-email', [
			                    	'cta' => trans('front.page.'.$current_page.'.not-verified',[
			                    		'email' => '<b>'.$user->email.'</b>'
			                    	])
			                    ])							
			                </div>
						@else
							@if(false)
								<div class="alert alert-info">
									We are currently working on fixing errors related to the Dentacoin transactions. Please come by again tomorrow.
								</div>
							@else
  								@include('front.template-parts.submit-review-form')
							@endif
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
			</div>
		@endif



		@if($item->reviews_in->isNotEmpty() && !(!empty($user) && $my_review && $item->reviews_in->count()==1) )
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">
                    	{{ trans('front.page.'.$current_page.'.reviews-title', [ 'name' => $item->name ]) }}
                    </h3>
                </div>
                <div class="panel-body reviews-panel">

					@foreach($reviews as $review)
						@if(!empty($user) && $review->user_id==$user->id)
						@else
							<div class="panel panel-default">
								@include('front.template-parts.review',[
									'user_field' => 'user'
								])
							</div>
						@endif
					@endforeach

                </div>
            </div>
		@endif

	</div>
</div>

@endsection