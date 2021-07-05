@extends('vox')

@section('content')
	
	@if(!empty($user) && $user->platform != 'external' && !request()->exists('daily-answer'))
		<div class="level-wrapper">
			<div class="container">
				<div class="flex flex-center">
					<div class="col">
						<img src="{{ url('new-vox-img/vox-'.$user->getVoxLevelName().'-icon.svg') }}" width="76" height="76">
						<p class="bold">{{ trans('vox.page.home.levels.'.$user->getVoxLevelName()) }}</p>
					</div>
					<div class="col">
						<p class="big bold">{{ $user->countAllSurveysRewards() }}</p>
						<p>{{ trans('vox.page.home.levels.surveys-taken') }}</p>
					</div>
					<div class="col">
						<p class="big bold">{{ count($user->filledDailyPolls()) }}</p>
						<p>{{ trans('vox.page.home.levels.daily-polls') }}</p>
					</div>
					<div class="col lifetime-rewards">
						<div class="flex flex-center">
							<div class="circle"></div>
							<div class="rew">
								<p class="bold">{{ number_format($user->all_rewards->sum('reward')) }} DCN</p>
								<p>{{ trans('vox.page.home.levels.lifetime-rewards') }}</p>
							</div>
						</div>
						<div class="flex flex-center">
							<div class="circle"></div>
							<div class="rew">
								<p class="bold">{{ number_format($user_total_balance) }} DCN</p>
								<p>{{ trans('vox.page.home.levels.curently-redeemable') }}</p>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	@endif

	@if($all_taken)
		<div class="all-taken-wrapper flex break-mobile">
			<div class="col">
				<img src="{{ url('/new-vox-img/all-surveys-taken.png') }}">
			</div>
			<div class="col">
				<h3>{{ trans('vox.page.home.all-surveys-done.title') }}</h3>
				<p>{!! nl2br(trans('vox.page.home.all-surveys-done.description')) !!}</p>
				<b>{{ trans('vox.page.home.all-surveys-done.info') }}</b>

				<div class="btns">
					@if($user->platform != 'external')
						<a class="opinion blue-button" href="https://account.dentacoin.com/invite?platform=dentavox">
							{{ $user->is_dentist ? trans('vox.page.home.all-surveys-done.invite-patients') : trans('vox.page.home.all-surveys-done.invite-friends') }}
						</a>
					@endif
					<a class="statistics blue-button {{ $user->platform != 'external' ? 'secondary' : '' }}" href="{{ getLangUrl('dental-survey-stats') }}">
						{{ trans('vox.page.home.all-surveys-done.browse-stats') }}
					</a>
				</div>
			</div>
		</div>

		<div class="section-slider-posts">
			<h3 class="blog-posts-title">{{ trans('vox.page.home.all-surveys-done.blog-posts-title') }}</h3>
			<div class="slider-posts-inner">
	    		<div class="flickity slider-posts">
					@foreach($latest_blog_posts as $lp)
		    			<a href="{{ $lp->guid }}" target="_blank" class="post">
		    				<div class="post-inner">
			    				<div class="post-image cover" style="background-image: url({{ $lp->img }}); background-position: 50% 50%;"></div>
		    					<div class="hover-top">
				    				<div class="post-info">
		    							<div href="javascript::" class="cat">
											{{ $lp->cat_name }}
										</div>
					    				<span class="date">{{ date("M j, Y (D)", strtotime($lp->post_date)) }}</span> 
					    			</div>
					    			<div><h4>{{ $lp->post_title }}</h4></div>
					    		</div>
					    		<div class="bottom-container">
					    			<p>
					    				{{ $lp->post_excerpt }}
					    			</p>
			    					<div class="read-more">{{ trans('vox.page.home.all-surveys-done.blog-posts-more') }}<img src="https://dentavox.dentacoin.com/blog/wp-content/themes/blog/img/read-arrow.png"></div>
					    		</div>
				    		</div>
		    			</a>
		    		@endforeach
	    		</div>
	    	</div>
	    	<div class="tac">
	    		<a href="https://dentavox.dentacoin.com/blog/" target="_blank" class="gray-wp-button">{{ trans('vox.page.home.all-surveys-done.blog-posts-all') }}</a>
	    	</div>
	    </div>
		
	@else
		<div class="container">

			@if($user && $is_warning_message_shown && !request()->exists('daily-answer'))
				<div class="alert alert-warning"> {{ trans('vox.page.home.high-gas-price') }} </div> 
			@endif

			@if(request()->exists('daily-answer'))
				<div class="daily-poll-welcome">
					<div class="flex-mobile">
						<div class="col">
					    	<h3>{{ trans('vox.page.home.daily-poll.title') }}</h3>
					    	<h4>{!! nl2br(trans('vox.page.home.daily-poll.subtitle', ['reward' => '<b>'. $daily_poll_reward.' DCN</b>'])) !!}</h4>
					    </div>
					    <div class="col">
			    			<img class="poll-man" src="{{ url('new-vox-img/welcome-daily-poll.png') }}" width="331" height="370">
			    		</div>
			    	</div>
			    	<h4 class="title-next">{{ trans('vox.page.home.daily-poll.next-title') }}</h4>

			    	<div class="flex doing-next">
			    		<div class="col">
			    			<img src="{{ url('new-vox-img/browse-polls-icon-white.png') }}" width="126" height="126">
			    			<a href="{{ getLangUrl('daily-polls') }}" class="blue-button">
				    			<img src="{{ url('new-vox-img/browse-polls-icon-white.svg') }}" width="60" height="60">
					    		{{ trans('vox.page.home.daily-poll.browse-polls') }}
					    	</a>
			    		</div>
			    		<div class="col">
			    			<img src="{{ url('new-vox-img/take-paid-surveys-white.png') }}" width="126" height="126">
			    			<a href="javascript:;" class="blue-button scroll-to-surveys">
			    				<img src="{{ url('new-vox-img/take-paid-surveys-white.svg') }}" width="60" height="60">
			    				{{ trans('vox.page.home.daily-poll.take-surveys') }}
			    			</a>
			    		</div>
			    		<div class="col">
			    			<img src="{{ url('new-vox-img/check-stats-white.png') }}" width="126" height="126">
			    			<a href="{{ getLangUrl('dental-survey-stats') }}" class="blue-button">
			    				<img src="{{ url('new-vox-img/check-stats-white.svg') }}" width="60" height="60">
			    				{{ trans('vox.page.home.daily-poll.check-stats') }}
			    			</a>
			    		</div>
			    	</div>
			    </div>
			@endif
			
			<form  method="get" class="another-questions">
	  			@include('front.errors')
	  			
				<h1 class="bold">
					{{ trans('vox.page.home.title') }}
				</h1>

				<div class="filters-section">
					<div class="search-survey tal">
						<i class="fas fa-search"></i>
						<input type="text" id="survey-search" name="survey_search" value="{{ request('survey_search') ?? '' }}">
					</div>
					<div class="questions-menu clearfix">
						<div class="sort-menu tal"> 
							@foreach($sorts as $key => $val)
								@if($key == 'taken' && empty($taken))

								@else
									<a href="javascript:;" desktop-val="{{ $val }}" sort="{{ $key }}" class="{!! request('sortable-items') ? (explode('-', request('sortable-items'))[0] == $key ? 'active sortable' : 'sortable') : ($key == 'newest' ? 'active sortable' : 'sortable') !!} {!! request('sortable-items') && explode('-', request('sortable-items'))[1] == 'asc' ? 'order-asc' : '' !!}">

										@if($key == 'featured')
											<i class="fas fa-star"></i>
										@endif

										{{ isset(explode(' ', $val)[1]) ? explode(' ', $val)[1] : explode(' ', $val)[0] }}
									</a>
								@endif
							@endforeach
						</div>
						<input type="hidden" name="sortable_items" value="">
						<div class="sort-category tar"> 
							<span>
								{{ trans('vox.page.home.filter') }}:
							</span>
							{{ Form::select('category', ['all' => 'All'] + $vox_categories, request('category') ?? null , ['id' => 'surveys-categories']) }} 
						</div>
					</div>

					@if(!empty($user))
						<div class="questions-menu clearfix">
							<div class="filter-menu tal"> 
								@foreach($filters as $k => $v)
									@if($k == 'taken' && empty($taken))

									@else
										<label for="filter-{{ $k }}" class="{!! request('filter_item') ? (request('filter_item') == $k ? 'active' : '') : ($k == 'untaken' ? 'active' : '') !!}">
											{{ $v }}
											<input type="radio" value="{{ $k }}" name="filter_item" id="filter-{{ $k }}" class="filter_item" {!! request('filter_item') && request('filter_item') == $k ? 'checked="checked"' : ($k=='untaken' ? 'checked="checked"' : '') !!} style="display: none;">
										</label>
									@endif
								@endforeach
							</div>
						</div>
					@endif
				</div>
				<div class="section-recent-surveys new-style-swiper" id="questions-wrapper">
					<div class="questions-inner" id="questions-inner">
						@if(!empty($user) && $user->is_dentist)
							<div class="swiper-slide request-vox">
								<div class="slider-inner">
									<div class="slide-padding">
										<a href="javascript:;" class="cover" style="background-image: url('{{ url('new-vox-img/request-survey.jpg') }}');"></a>		
										<div class="vox-header clearfix">
											<h4 class="survey-title bold">{{ trans('vox.page.home.request-survey.title') }}</h4>
											<div class="survey-cats"> 
												<span>{{ trans('vox.page.home.request-survey.cat1') }}</span>
												<span>{{ trans('vox.page.home.request-survey.cat2') }}</span>
												<span>{{ trans('vox.page.home.request-survey.cat3') }}</span>
											</div>
											<div class="survey-time flex">
												<p class="vox-description tac">
													{{ trans('vox.page.home.request-survey.description') }}
												</p>
											</div>
											<div class="btns">
												<a class="blue-button" href="javascript:;" data-popup="request-survey-popup">
													{{ trans('vox.page.home.request-survey.request') }}
												</a>
											</div>
										</div>
								  	</div>
								</div>
						    </div>
						@endif
						
					    @include('vox.template-parts.home-voxes')
					</div>

					<div class="tac" style="display: none;"> 
						{{ $voxes->render() }}
					</div>

					<a class="give-me-more" id="survey-more" href="javascript:;">
						{{ trans('vox.common.load-more') }}					
					</a>

					<div class="alert alert-info" id="survey-not-found" style="display: none;">
						{{ trans('vox.page.home.no-results') }}
					</div>
				</div>
		        <input type="submit" name="submit" style="display: none;">
			</form>
		</div>

	@endif
    	
@endsection