@extends('vox')

@section('content')

	<div class="page-statistics">
		@if(empty($user))
			<div class="loader-mask" id="main-loader" style="display: none;">
			    <div class="loader">
			      	{{ trans('vox.common.loading') }}
			    </div>
			</div>
		@endif


		<div class="container">
			@if(empty(request('app')))
				<a class="back-home" href="{{ getLangUrl('dental-survey-stats') }}">
					{!! trans('vox.page.stats.go-back-stats') !!}
				</a>
			@endif

			<h1>
				{{ trans('vox.page.stats.title-single', [
					'name' => $vox->title,
				]) }}
			</h1>

			<div class="tac take-test">
				@if(false && !empty($user))
					<a href="javascript:;" class="red-button download-stats-popup-btn" for-stat="all">
						<img src="{{ url('new-vox-img/download.png') }}"/>Download All Stats
					</a>
				@endif
			</div>

			<div class="filters-wrapper" style="{{ !$user ? 'display: none;' : '' }}">
				<div class="filters">
					<b>
						{!! trans('vox.page.stats.period') !!}:
					</b>
					@foreach($filters as $filterkey => $filter)
						<a href="{{ $vox->getStatsList() }}?filter={{ $filterkey }}" filter="{{ $filterkey }}" {!! $active_filter==$filterkey ? 'class="active"' : '' !!}>
							{{ $filter }}
						</a>
					@endforeach
					<a href="javascript:;" filter="custom">
						{!! trans('vox.page.stats.period-custom') !!}
					</a>
					<select name="single-stat-filters">
						@foreach($filters as $filterkey => $filter)
							<option value="{{ $filterkey }}" {!! $active_filter==$filterkey ? 'selected="selected"' : '' !!}>{{ $filter }}</option>
						@endforeach
						<option value="custom" {!! $active_filter=='custom' ? 'selected="selected"' : '' !!}>{!! trans('vox.page.stats.period-custom') !!}</option>
					</select>
				</div>

				<div class="filters-custom tac" style="display: none;">
					<div id="custom-datepicker" launched-date="{{ $launched_date }}">
					</div>
					<div id="datepicker-extras">
						<div class="flex">
							<div>
								{!! trans('vox.page.stats.period-from') !!}:<br/>
								<input type="text" id="date-from" autocomplete="off" placeholder="dd/mm/yyyy">
							</div>
							-
							<div>
								{!! trans('vox.page.stats.period-to') !!}:<br/>
								<input type="text" id="date-to" autocomplete="off" placeholder="dd/mm/yyyy">
							</div>
						</div>
						<div class="button-holder">
							<a href="javascript:;" id="custom-dates-save" class="btn">
								{!! trans('vox.page.stats.period-custom-submit') !!}
							</a>						
							<a class="text">
								{!! trans('vox.page.stats.period-clear') !!}
							</a>
						</div>
					</div>
				</div>
			</div>
		</div>

		@if(empty($user))
			<div class="stats-image-wrapper">
				<div class="container flex flex-center">
					<div class="col">
						<img src="{{ url('new-vox-img/dv-stats-banner-left-img.svg') }}" class="pc-stat-img" width="360" height="282">
						<img src="{{ url('new-vox-img/mobile-dv-stats-banner-left-img.svg') }}" class="mobile-stat-img" width="250" height="134"/>
					</div>
					<div class="col">
						<a href="javascript::" id="load-stats" class="red-button">
							<img src="{{ url('new-vox-img/chart-icon.svg') }}" width="30" height="30"/>
							{!! trans('vox.page.stats.show-stats') !!}
						</a>
					</div>
					<div class="col">
						<img src="{{ url('new-vox-img/dv-stats-banner-right-img.svg') }}" class="pc-stat-img" width="331" height="281">
					</div>
				</div>
			</div>
		@endif
		
		<div class="stats" id="all-stat-wrap">
			@foreach($vox->stats_questions as $question)
				@if(!empty($user) || (empty($user) && $loop->iteration <=3))
					<div class="stat {!! false && count(json_decode($question->answers, true)) > 9 ? 'stat-with-many-qs' : '' !!} {!! $question->stats_top_answers ? 'multipletop_ans' : '' !!} {{ $loop->last ? 'last-stat' : '' }} {!! !empty($question->stats_scale_answers) ? 'has-scales' : '' !!}" question-id="{{ $question->id }}" stat-type="{{ $question->used_for_stats }}" {!! !empty($question->stats_scale_answers) ? 'scale-answer-id="1"' : '' !!}>
						<div class="title" href="javascript:;">
							<h2 class="container">
								{!! nl2br(strip_tags(!empty($question->stats_title_question) ? $question->questionWithoutTooltips() : $question->translateorNew(App::getLocale())->stats_title, ['a'])) !!}
							</h2>
						</div>
						<div class="contents container">
							@if(!empty($question->stats_scale_answers))
								@if(!empty($question->translateorNew(App::getLocale())->stats_subtitle))
									<p class="stats-subtitle">{{ nl2br($question->translateorNew(App::getLocale())->stats_subtitle) }}</p>
								@endif
								@foreach(json_decode($question->{'answers:en'}, true) as $key => $ans)
									@if( in_array(($key + 1), json_decode($question->stats_scale_answers, true)))
										<div class="stat scale-stat-q {!! $loop->iteration == 1 ? 'first-scale-stat' : '' !!}" question-id="{{ $question->id }}" scale-answer-id="{{ $key + 1 }}" stat-type="{{ $question->used_for_stats }}">
											<div class="title" href="javascript:;">
												<h2>
													{!! nl2br(strip_tags($question->removeAnswerTooltip($ans), ['a'])) !!}
												</h2>
											</div>
											<div class="contents scale-contents">
												@include('vox.template-parts.stats-chart')
											</div>
										</div>

										@include('vox.template-parts.download-demographic', [
											'for_scale' => true
										])
									@endif
								@endforeach
							@else
								@if(!empty($question->translateorNew(App::getLocale())->stats_subtitle))
									<p class="stats-subtitle">{{ nl2br($question->translateorNew(App::getLocale())->stats_subtitle) }}</p>
								@endif
								@include('vox.template-parts.stats-chart')
							@endif
						</div>
					</div>

					@if(empty($question->stats_scale_answers))
						@include('vox.template-parts.download-demographic', [
							'for_scale' => false
						])
					@endif
				@endif
			@endforeach
		</div>
	</div>

	@if(!empty($blurred_stats))
		<div class="stats-blurred">
			<div class="blurred-title" href="javascript:;">
				<h2 class="container">
					@foreach($vox->stats_questions as $question)
						@if($loop->iteration == 4)
							{!! nl2br(strip_tags(!empty($question->stats_title_question) ? $question->questionWithoutTooltips() : $question->translateorNew(App::getLocale())->stats_title, ['a'])) !!}
						@endif
					@endforeach
				</h2>
			</div>
			<div class="container">
				<div class="blurred-stat">
					<img class="pc-blurred" src="{{ url('new-vox-img/blurred-stats-1.jpg') }}" width="1140" height="516">
					<img class="mobile-blurred" src="{{ url('new-vox-img/blurred-stats-mobile.jpg') }}" width="320" height="498">
					<div class="blurred-text">
						<div class="free-text">{{ trans('vox.page.stats.blurred.title.1') }}</div>
						<h2>{{ trans('vox.page.stats.blurred.title.2') }}</h2>
						<p>{{ trans('vox.page.stats.blurred.title.3') }}</p>
						<div class="download-functions-wrap">
							<div class="download-functions">
								<p>✓ {{ trans('vox.page.stats.blurred.function.1') }}</p>
								<p>✓ {{ trans('vox.page.stats.blurred.function.2') }}</p>
								<p>✓ {{ trans('vox.page.stats.blurred.function.3') }}</p>
							</div>
						</div>
						<a href="javascript:;" class="blue-button blurred-button">{{ trans('vox.page.stats.blurred.sign-in') }}</a>
						<span>
							{!! trans('vox.page.stats.blurred.login', [
								'link' => '<a class="blurred-button log" href="javascript:;">',
								'endlink' => '</a>'
							]) !!}
						</span>
					</div>
				</div>
			</div>
		</div>
	@endif

	<div class="all-stats-section tac">
		<div class="container">
			<h2>{{ trans('vox.page.stats.summary.title') }}</h2>
			<p>				
				{{ $vox->translateorNew(App::getLocale())->stats_description }}
			</p>

			@if(empty(request('app')))
				<div class="flex flex-text-center">
					<a href="{{ getLangUrl('dental-survey-stats') }}" class="white-button">Back to all stats</a>
					@if(!in_array($vox->id, $taken) && $vox->type != 'hidden')
						<a class="blue-button" href="{!! !empty($user) ? $vox->getLink() : "javascript:showPopup('login-register-popup')" !!}">
							{{ trans('vox.common.take-the-test') }}
						</a>
					@endif
				</div>
			@endif
		</div>
	</div>
	<a style="display: none;" id="download-link" class="{{ session('download_stat') ? 'for-download' : '' }}" href="{{ session('download_stat') ? getLangUrl('download-pdf/'.session('download_stat')) : 'javascript:;' }}">{{ trans('vox.page.stats.download-link') }}</a>
	<a style="display: none;" id="download-link-png" class="{{ session('download_stat_png') ? 'for-download' : '' }}" href="{{ session('download_stat_png') ? getLangUrl('download-png/'.session('download_stat_png')) : 'javascript:;' }}">{{ trans('vox.page.stats.download-png-link') }}</a>

	@if(!empty($user))
		<input type="hidden" name="current-stats-vox" id="current-stats-vox" value="{{ $vox->id }}">
		@include('vox.popups.download-stats')
	@endif

@endsection