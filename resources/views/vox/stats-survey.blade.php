@extends('vox')

@section('content')

	<div class="loader-mask">
	    <img class="stats-loader" src="{{ url('new-vox-img/stats-loader.gif') }}">
  	</div>

	<div class="container page-statistics">
		<a class="back-home" href="{{ getLangUrl('dental-survey-stats') }}">
			{!! trans('vox.page.stats.go-back-stats') !!}
		</a> 

		<h1>
			{{ trans('vox.page.stats.title-single', [
				'name' => $vox->title,
				'respondents' => $respondents,
				'respondents_country' => $respondents_country,
			]) }}
		</h1>


		<p class="stat-survey-info">
			{{ $vox->translateorNew(App::getLocale())->stats_description }}
		</p>

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
			</div>

			<div class="filters-custom tac" style="display: none;">
				<div id="custom-datepicker">
				</div>
				<div id="datepicker-extras">
					<div class="flex">
						<div>
							{!! trans('vox.page.stats.period-from') !!}:<br/>
							<input type="text" id="date-from">
						</div>
						-
						<div>
							{!! trans('vox.page.stats.period-to') !!}:<br/>
							<input type="text" id="date-to">
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

		<div class="alert alert-info" id="daterange-error" style="display: none;">
			{!! trans('vox.page.stats.no-results-single') !!}
		</div>

		<div class="stats">
			@foreach($vox->questions as $question)
				@if($question->used_for_stats)
					<div class="stat" question-id="{{ $question->id }}" stat-type="{{ $question->used_for_stats }}">
						<a class="title" href="javascript:;">
							<h2>
								{{ $question->translateorNew(App::getLocale())->stats_title }}
							</h2>
						</a>
						<div class="contents">
							@if(!empty($question->stats_scale_answers))
								@if(!empty($question->translateorNew(App::getLocale())->stats_subtitle))
									<p class="stats-subtitle">{{ nl2br($question->translateorNew(App::getLocale())->stats_subtitle) }}</p>
								@endif
								@foreach(json_decode($question->{'answers:en'}, true) as $key => $ans)
									@if( in_array(($key + 1), json_decode($question->stats_scale_answers, true)))
										<div class="stat" question-id="{{ $question->id }}" scale-answer-id="{{ $key + 1 }}" stat-type="{{ $question->used_for_stats }}">
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

	@if(empty($user))
		@include('vox.popups.login-register')
	@endif

@endsection