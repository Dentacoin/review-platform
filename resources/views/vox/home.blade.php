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
						<p>Surveys Taken</p>
					</div>
					<div class="col">
						<p class="big bold">{{ count($user->filledDailyPolls()) }}</p>
						<p>Daily Polls</p>
					</div>
					<div class="col lifetime-rewards">
						<div class="flex flex-center">
							<div class="circle"></div>
							<div class="rew">
								<p class="bold">{{ number_format($user->all_rewards->sum('reward')) }} DCN</p>
								<p>Lifetime Rewards</p>
							</div>
						</div>
						<div class="flex flex-center">
							<div class="circle"></div>
							<div class="rew">
								<p class="bold">{{ number_format($user->getTotalBalance()) }} DCN</p>
								<p>Currently redeemable</p>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	@endif

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
  			
  			@if(!$all_taken)
				<h1 class="bold">
					{{ trans('vox.page.home.title') }}
				</h1>
			@endif

			@if($all_taken)
				<div class="all-taken-wrapper flex break-mobile">
					<div class="col">
						<img src="{{ url('/new-vox-img/all-surveys-taken.png') }}">
					</div>
					<div class="col">
						<h3>Oops! No surveys available.</h3>
						<p>It seems you have taken all surveys available to users with your demographics. No worries: We upload two new surveys every week. Limits are dynamic, so just make it a daily habit to check for newly available surveys!</p>
						<b>Meanwhile, why don't you:</b>

						<div class="btns">
							@if($user->platform != 'external')
								<a class="opinion blue-button" href="https://account.dentacoin.com/invite?platform=dentavox">
									Invite {{ $user->is_dentist ? 'patients' : 'friends' }}
								</a>
							@endif
							<a class="statistics blue-button {{ $user->platform != 'external' ? 'secondary' : '' }}" href="{{ getLangUrl('dental-survey-stats') }}">
								Browse Stats
							</a>
						</div>
					</div>
				</div>

				<!-- <div class="alert alert-info alert-done-all-surveys">
					@if($user->is_dentist)
						{!! nl2br(trans('vox.page.home.dentist.alert-done-all-surveys', [
							'link' => '<a href="https://account.dentacoin.com/invite?platform=dentavox">',
							'link_stats' => '<a href="'.getLangUrl('dental-survey-stats').'">',
							'endlink' => '</a>',
						])) !!}
					@else
						@if($user->platform == 'external')
							Looks like you have taken all surveys. Good job! While waiting for the next topic, you can browse our <a href=" {{ getLangUrl('dental-survey-stats') }}"> survey statistics</a>. Stay tuned for more updates!
						@else
							{!! nl2br(trans('vox.page.home.patients.alert-done-all-surveys', [
								'link' => '<a href="https://account.dentacoin.com/invite?platform=dentavox">',
								'link_stats' => '<a href="'.getLangUrl('dental-survey-stats').'">',
								'endlink' => '</a>',
							])) !!}
						@endif
					@endif
				</div> -->

				@if($user->id == 37530)
					<div class="slider-posts-inner">

			    		<div class="flickity slider-posts">
							@foreach($latest_blog_posts as $lp)
				    			<div class="post">
				    				<div class="post-inner">
					    				<a href="{{ $lp->guid }}" target="_blank" class="post-image cover" style="background-image: url({{ $lp->img }}); background-position: 50% 50%;"></a>
				    					<div class="hover-top">
						    				<div class="post-info">
				    							<a href="javascript::" class="cat">
													{{ $lp->cat_name }}
												</a>
							    				<span class="date">{{ date("M j, Y (D)", strtotime($lp->post_date)) }}</span> 
							    			</div>
							    			<a href="{{ $lp->guid }}" target="_blank"><h4>{{ $lp->post_title }}</h4></a>
							    		</div>
							    		<div class="bottom-container">
							    			<p>
							    				{{ $lp->post_excerpt }}
							    			</p>
					    					<a href="{{ $lp->guid }}" target="_blank" class="read-more">Read more<img src="https://dentavox.dentacoin.com/blog/img/read-arrow.png"></a>
							    		</div>
						    		</div>
				    			</div>
				    		@endforeach
			    		</div>
			    	</div>
			    @endif

				
			@else
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
			@endif
	        <input type="submit" name="submit" style="display: none;">
		</form>
	</div>
    	
@endsection