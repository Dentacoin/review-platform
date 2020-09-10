@extends('vox')

@section('content')
	
	@if(!empty($user) && $user->platform != 'external')
		<div class="level-wrapper">
			<div class="container">
				<div class="flex flex-center">
					<div class="col">
						<img src="{{ url('new-vox-img/vox-'.$user->getVoxLevelName().'-icon.svg') }}">
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
								<p class="bold">{{ number_format($user->vox_surveys_and_polls->sum('reward')) }} DCN</p>
								<p>Lifetime Rewards</p>
							</div>
						</div>
						<div class="flex flex-center">
							<div class="circle"></div>
							<div class="rew">
								<p class="bold">{{ number_format($user->getTotalBalance('vox')) }} DCN</p>
								<p>Currently redeemable</p>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	@endif

	<div class="container">

		@if($is_warning_message_shown)
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
		    			<img class="poll-man" src="{{ url('new-vox-img/welcome-daily-poll.png') }}">
		    		</div>
		    	</div>
		    	<h4 class="title-next">{{ trans('vox.page.home.daily-poll.next-title') }}</h4>

		    	<div class="flex doing-next">
		    		<div class="col">
		    			<img src="{{ url('new-vox-img/browse-polls-icon-white.png') }}">
		    			<a href="{{ getLangUrl('daily-polls') }}" class="blue-button">
			    			<img src="{{ url('new-vox-img/browse-polls-icon-white.svg') }}">
				    		{{ trans('vox.page.home.daily-poll.browse-polls') }}
				    	</a>
		    		</div>
		    		<div class="col">
		    			<img src="{{ url('new-vox-img/take-paid-surveys-white.png') }}">
		    			<a href="javascript:;" class="blue-button scroll-to-surveys">
		    				<img src="{{ url('new-vox-img/take-paid-surveys-white.svg') }}">
		    				{{ trans('vox.page.home.daily-poll.take-surveys') }}
		    			</a>
		    		</div>
		    		<div class="col">
		    			<img src="{{ url('new-vox-img/check-stats-white.png') }}">
		    			<a href="{{ getLangUrl('dental-survey-stats') }}" class="blue-button">
		    				<img src="{{ url('new-vox-img/check-stats-white.svg') }}">
		    				{{ trans('vox.page.home.daily-poll.check-stats') }}
		    			</a>
		    		</div>
		    	</div>
		    </div>
		@endif
		
		<div class="another-questions">

  			@include('front.errors')
  			
			<h1 class="bold">
				{{ trans('vox.page.home.title') }}
			</h1>

			@if(!empty($user) && $voxes->count() == count($taken))
				<div class="alert alert-info alert-done-all-surveys">
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
				</div>
			@else
				<div class="filters-section">
					<div class="search-survey tal">
						<i class="fas fa-search"></i>
						<input type="text" id="survey-search" name="survey-search">
					</div>
					<div class="questions-menu clearfix">
						<div class="sort-menu tal"> 
							@foreach($sorts as $key => $val)
								@if($key == 'taken' && empty($taken))

								@else
									<a href="javascript:;" sort="{{ $key }}"  class="{!! $key == 'newest' ? 'active sortable' : ( $key == 'featured' || $key == 'untaken' ? 'active' : ($key == 'all' || $key == 'taken' ? '' : 'sortable')) !!}">

										@if($key == 'featured')
											<i class="fas fa-star"></i>
										@endif

										{{ $val }}
									</a>
								@endif
							@endforeach
						</div>
						<div class="sort-category tar"> 
							<span>
								{{ trans('vox.page.home.filter') }}:
							</span>
							{{ Form::select('category', ['all' => 'All'] + $vox_categories, null , ['id' => 'surveys-categories']) }} 
						</div>
					</div>

					@if(!empty($user))
						<div class="questions-menu clearfix">
							<div class="filter-menu tal"> 
								@foreach($filters as $k => $v)
									@if($k == 'taken' && empty($taken))

									@else
										<a href="javascript:;" filter="{{ $k }}"  class="{!! $k == 'untaken' ? 'active' : '' !!}">
											{{ $v }}
										</a>
									@endif
								@endforeach
							</div>
						</div>
					@endif
				</div>
			@endif
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
					@foreach( $voxes as $vox)
				      	<div class="swiper-slide home-vox"
			      			featured="{{ intval($vox->featured) }}" 
			      			published="{{ $vox->launched_at->timestamp }}" 
			      			sort-order="{{ $vox->sort_order ? $vox->sort_order : 0 }}" 
			      			popular="{{ intval($vox->rewardsCount()) }}" 
			      			dcn="{{ intval($vox->getRewardTotal()) }}" 
			      			duration="{{ !empty($vox->manually_calc_reward) && !empty($vox->dcn_questions_count) ? ceil( $vox->dcn_questions_count/6) : ceil( $vox->questionsCount()/6) }}" 
			      			{!! !empty($taken) ? 'taken="'.intval(!in_array($vox->id, $taken) ? 0 : 1).'"' : '' !!}
			      			>

			      			<div class="slider-inner">
								<div class="slide-padding">
									<a href="{{ !empty($taken) && in_array($vox->id, $taken) ? 'javascript:;' : $vox->getLink() }}" class="cover" style="background-image: url('{{ $vox->getImageUrl(true) }}');">
										<img src="{{ $vox->getImageUrl(true) }}" alt="{{ $vox->title }} - Dental Survey" style="display: none !important;"> 
										@if($vox->featured)
											<img class="featured-img doublecoin" src="{{ url('new-vox-img/dentavox-dentacoin-flipping-coin.gif') }}" alt="Dentavox dentacoin flipping coin">
										@endif
									</a>
									<div class="vox-header clearfix">
										<h4 class="survey-title bold">{{ $vox->title }}</h4>
										<div class="survey-cats"> 
											@foreach( $vox->categories as $c)
												<span class="survey-cat" cat-id="{{ $c->category->id }}">{{ $c->category->name }}</span>
											@endforeach
										</div>
										<div class="survey-time flex">
											<div class="col">
												<img src="{{ url('new-vox-img/coin-icon.png') }}">
												@if(!empty($user) && !empty($taken) && in_array($vox->id, $taken) && !empty(\App\Models\DcnReward::where('user_id', $user->id)->where('type', 'survey')->where('platform', 'vox')->where('reference_id', $vox->id)->first()))
													<p>{{ \App\Models\DcnReward::where('user_id', $user->id)->where('type', 'survey')->where('platform', 'vox')->where('reference_id', $vox->id)->first()->reward }} DCN</p>
												@else
													<p>{{ !empty($vox->complex) ? 'Max' : '' }} {{ $vox->getRewardTotal() }} DCN</p>
												@endif
											</div>
											<div class="col">
												<img src="{{ url('new-vox-img/clock-icon.svg') }}">
												<p><span class="hide-mobile">{{ trans('vox.page.public-questionnaire.time') }}</span> {{ $vox->formatDuration() }}</p>
											</div>
										</div>
										<div class="btns">
											@if($user && $user->is_dentist)
												@if($vox->has_stats)
													<a class="statistics blue-button" href="{{ $vox->getStatsList() }}">
														{{ trans('vox.common.check-statictics') }}
													</a>
												@endif
												@if(!empty($taken) && in_array($vox->id, $taken))
													<a class="gray-button secondary" href="javascript:;">
														<i class="fas fa-check"></i>{{ trans('vox.common.taken') }}
													</a>
												@else
													<a class="opinion blue-button {!! $vox->has_stats ? 'secondary' : '' !!}" href="{{ $vox->getLink() }}">
														{{ trans('vox.common.take-the-test') }}
													</a>
												@endif
											@else
												@if(!empty($taken) && in_array($vox->id, $taken))
													<a class="gray-button" href="javascript:;">
														<i class="fas fa-check"></i>{{ trans('vox.common.taken') }}
													</a>
												@else
													<a class="opinion blue-button" href="{{ $vox->getLink() }}">
														{{ trans('vox.common.take-the-test') }}
													</a>
												@endif
												@if($vox->has_stats)
													<a class="statistics blue-button secondary" href="{{ $vox->getStatsList() }}">
														{{ trans('vox.common.check-statictics') }}
													</a>
												@endif
											@endif
										</div>
									</div>
							  	</div>
							</div>
					    </div>
					@endforeach
				</div>

				<a class="give-me-more" id="survey-more" href="javascript:;" style="display: none;">
					{{ trans('vox.common.load-more') }}					
				</a>

				<div class="alert alert-info" id="survey-not-found" style="display: none;">
					{{ trans('vox.page.home.no-results') }}
				</div>
			</div>
	            
		</div>
	</div>

	@if(!empty($user) && $user->is_dentist)
		@include('vox.popups.request-survey')
	@endif
    	
@endsection