@extends('vox')

@section('content')

	<div class="page-statistics">
		<div class="container">
			<a class="back-home" href="{{ getLangUrl('dental-survey-stats') }}">
				{!! trans('vox.page.stats.go-back-stats') !!}
			</a> 

			<h1>
				{{ trans('vox.page.stats.title-single', [
					'name' => $vox->title,
				]) }}
			</h1>

			<div class="tac take-test">
				@if(!in_array($vox->id, $taken))
					<a class="blue-button" href="{!! !empty($user) ? $vox->getLink() : "javascript:$('#login-register-popup').addClass('active')" !!}">
						{{ trans('vox.common.take-the-test') }}
					</a>
				@endif
			</div>

			<div class="filters-wrapper">
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
								<input type="text" id="date-from" autocomplete="off">
							</div>
							-
							<div>
								{!! trans('vox.page.stats.period-to') !!}:<br/>
								<input type="text" id="date-to" autocomplete="off">
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
		
		<div class="stats">
			@foreach($vox->stats_questions as $question)
				@if(!empty($user) || (empty($user) && $loop->iteration <=3))
					<div class="stat {!! false && count(json_decode($question->answers, true)) > 9 ? 'stat-with-many-qs' : '' !!} {!! $question->stats_top_answers ? 'multipletop_ans' : '' !!}" question-id="{{ $question->id }}" stat-type="{{ $question->used_for_stats }}" {!! !empty($question->stats_scale_answers) ? 'scale-answer-id="1"' : '' !!}>
						<a class="title" href="javascript:;">
							<h2 class="container">
								{{ $question->translateorNew(App::getLocale())->stats_title }}
							</h2>
						</a>
						<div class="contents container">
							@if(!empty($question->stats_scale_answers))
								@if(!empty($question->translateorNew(App::getLocale())->stats_subtitle))
									<p class="stats-subtitle">{{ nl2br($question->translateorNew(App::getLocale())->stats_subtitle) }}</p>
								@endif
								@foreach(json_decode($question->{'answers:en'}, true) as $key => $ans)
									@if( in_array(($key + 1), json_decode($question->stats_scale_answers, true)))
										<div class="stat scale-stat-q {!! $loop->iteration == 1 ? 'first-scale-stat' : '' !!}" question-id="{{ $question->id }}" scale-answer-id="{{ $key + 1 }}" stat-type="{{ $question->used_for_stats }}">
											<a class="title" href="javascript:;">
												<h2>
													{{ $question->removeAnswerTooltip($ans) }}
												</h2>
											</a>
											<div class="contents scale-contents">
												@include('vox.template-parts.stats-chart')
											</div>
										</div>
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
				@endif
			@endforeach
		</div>
	</div>


	@if(!empty($blurred_stats))
		<div class="stats-blurred">
			<a class="blurred-title" href="javascript:;">
				<h2 class="container">
					This is some blurred statistic question
				</h2>
			</a>
			<div class="container">
				<div class="blurred-stat">
					<img class="pc-blurred" src="{{ url('new-vox-img/blurred-stats-1.jpg') }}">
					<img class="mobile-blurred" src="{{ url('new-vox-img/blurred-stats-mobile.jpg') }}">
					<div class="blurred-text">
						<h2>Curious to learn more?</h2>
						<p>Unlock up-to-date, live dental market statistics!</p>
						<a href="https://vox.dentacoin.com/en/registration/" stat-url="{{ !empty($_SERVER['HTTP_REFERER']) ? ($_SERVER['HTTP_REFERER'].ltrim($_SERVER['REQUEST_URI'], '/')) : '' }}" class="blue-button blurred-button">SIGN UP FOR FREE</a>
						<span>Already have an account? <a class="blurred-button" stat-url="{{ !empty($_SERVER['HTTP_REFERER']) ? ($_SERVER['HTTP_REFERER'].ltrim($_SERVER['REQUEST_URI'], '/')) : '' }}" href="https://vox.dentacoin.com/en/login">Log in</a></span>
					</div>
				</div>
			</div>
		</div>
	@endif

	<div class="all-stats-section tac">
		<div class="container">
			<h2>STATS SUMMARY</h2>

			<p>				
				{{ $vox->translateorNew(App::getLocale())->stats_description }}
			</p>

			<a href="{{ getLangUrl('dental-survey-stats') }}" class="blue-button">Back to all stats</a>
		</div>
	</div>

	@if(empty($user))
		@include('vox.popups.login-register')
	@endif

@endsection