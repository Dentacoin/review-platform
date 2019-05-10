@extends('vox')

@section('content')

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
							@if($question->used_for_stats=='standard')
								<div class="scales flex flex-center">
									{!! trans('vox.page.stats.scale-by') !!}:									
									@foreach( $question->stats_fields as $sk)
										<a {!! $loop->first ? 'class="active"' : '' !!} scale="{{ $sk }}">
											{{ trans('vox.page.stats.group-by-'.$sk) }}
										</a>
									@endforeach
								</div>
							@endif
							<div class="graphs flex">						
								<div class="chart">
									<div class="main-chart"></div>
									<div class="total total-all">
										{!! trans('vox.page.stats.total') !!}: <b></b>
									</div>
									<div class="hint">
									</div>
								</div>
								<div class="chart">
									<div class="second-chart"></div>
									@if($question->used_for_stats=='standard')
										<div class="total total-f">
											{!! trans('vox.page.stats.total-women') !!}: <b></b>
										</div>
										<div class="icon total-f">
										</div>
										<div class="map-hint">
											{!! trans('vox.page.stats.respondents') !!}
											
										</div>
									@endif
								</div>
								<div class="chart">
									<div class="third-chart"></div>
									@if($question->used_for_stats=='standard')
										<div class="total total-m">
											{!! trans('vox.page.stats.total-men') !!}: <b></b>
										</div>
										<div class="icon total-m">
										</div>
									@endif
								</div>
								<div class="legend flex">
								</div>
								
								@if($question->used_for_stats=='related')
									<div class="main-title">
										{{ $question->related->question }}
									</div>
								@endif	

								<div class="share-buttons flex" data-href="{{ $vox->getStatsList() }}">
									<span>
										{!! trans('vox.page.stats.share') !!}
									</span>
									<div class="col fb tac">
										<a class="share" href="javascript:;">
											<i class="fab fa-facebook-f"></i>
										</a>
									</div>
									<div class="col twt tac">
										<a class="share" href="javascript:;">
											<i class="fab fa-twitter"></i>
										</a>
									</div>
								</div>
								@if($question->used_for_stats=='standard')
									<a class="nav nav-left">
									</a>
									<a class="nav nav-right">
									</a>
								@endif
							</div>
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