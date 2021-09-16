<!DOCTYPE html>
<html class="downl-stats">
    <head>
        <base href="{{ url('/') }}">
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="robots" content="noindex">
        <meta name="google-site-verification" content="b0VE72mRJqqUuxWJZklHQnvRZV4zdJkDymC0RD9hPhE" />

        <title>{!! $seo_title !!}</title>

        <link href="https://fonts.googleapis.com/css?family=Nunito:300,400,600,700,900&amp;subset=latin-ext" rel="stylesheet">
		<link rel="stylesheet" type="text/css" href="{{ url('css/new-style-vox.css') }}" />
        <link rel="stylesheet" type="text/css" href="{{ url('css/vox-stats-single.css') }}">
        <link rel="stylesheet" type="text/css" href="{{ url('css/vox-stats-single-loaded.css') }}">
        <link rel="stylesheet" type="text/css" href="{{ url('css/vox-download-stats.css') }}">

        <style type="text/css">
        	.hint {
        		display: none !important;
        	}

			.page-statistics .stats .stat .contents .graphs .main-chart {
				padding-top: 0px !important;
			}

			.page-statistics .stats .stat.active a.title h2 {
			    background-image: url('new-vox-img/stats-empty.png');
			}

			.loader-mask {
				display: none !important;
			}
        </style>
    </head>

    <div class="loader-mask">
	    <img src="{{ url('new-vox-img/drawing-chart-animation.gif') }}">
	</div>

    <body class="page-stats ltr logged-in">
		
		<div class="above-fold">
			<div class="site-content" id="download-html">
				<div class="page-statistics download-statistics">

					@if($format == 'pdf')
						<div class="st-title">
							<div class="container">
								<h1>
									{{ $vox->title }}
								</h1>
							</div>
							<div class="fake-margin"></div>
						</div>
					@endif

					<div class="stats st-download" id="all-stat-wrap" count-dems="{{ $demographics_count }}">
					 	@if(in_array('relation', $demographics))
					 		<div class="stat active {!! $question->stats_top_answers ? 'multipletop_ans' : '' !!}" question-id="{{ $question->id }}" stat-type="dependency">
								<div class="echo {{ $format == 'png' ? 'echo-png' : '' }}">
									<a class="title" href="javascript:;">
										<h2 class="container">
											{!! strip_tags(!empty($question->stats_title_question) ? $question->questionWithoutTooltips() : $question->translateorNew(App::getLocale())->stats_title) !!}
										</h2>
									</a>
									<div class="echo-inner">
										<div class="echo-wings"></div>
										<div class="contents container">
											@if(!empty($question->translateorNew(App::getLocale())->stats_subtitle))
												<p class="stats-subtitle">{{ nl2br($question->translateorNew(App::getLocale())->stats_subtitle) }}</p>
											@endif
											<div class="flex box">
												<div class="scales flex flex-center">
													{!! $format == 'pdf' ? trans('vox.page.stats.scale-by') : 'Results by' !!}:
													<a href="javascript:;" class="active" scale="dependency">Relation</a>
												</div>
											</div>

											<div class="graphs flex {!! $question->type=='multiple_choice' || $question->type=='rank' ? 'multiple-stat' : '' !!}" style="background-color: #f5f5f53b !important;">
												<div class="chart chart-1">
													<div class="main-chart" chart></div>
													<div class="total total-all">
														{!! trans('vox.page.stats.total') !!}: <b></b>
													</div>
													<div class="hint"></div>
												</div>
												<div class="chart chart-2">
													<div class="dependency-question"></div>
													<div class="second-chart" chart></div>
												</div>
												<div class="chart chart-3">
													<div class="third-chart" chart></div>
												</div>
												<div class="legend flex">
												</div>
											</div>
											<div class="alert alert-info st-daterange-error" style="display: none;">
												There are no results for this period.
											</div>
										</div>
										<div class="echo-wings"></div>
									</div>
									@if($format == 'png')
										<div class="footer-png">
											<p>
												Source survey: "{!! $vox->title !!}" <br/>
												Base: {{ $respondents }} respondents, {{ $all_period }}
											</p>
											<img src="{{ url('new-vox-img/footer-logo-png.png') }}">
										</div>
									@endif
								</div>
							</div>
					 	@endif

						@foreach( $demographics as $sk)
							@if(in_array($sk,  $question->stats_fields ))
								<div class="stat active {!! $question->stats_top_answers ? 'multipletop_ans' : '' !!} {!! !empty(request()->input('scale-for')) ? 'scale-stat-q first-scale-stat' : '' !!}" question-id="{{ $question->id }}" stat-type="standart" {!! array_key_exists($sk, $dem_options) ? 'stat-dem-option="'.implode(',', $dem_options[$sk]).'"' : '' !!}"  {!! !empty(request()->input('scale-for')) ? 'scale-answer-id="'.request()->input('scale-for').'"' : ''!!}">
									<div class="echo {{ $format == 'png' ? 'echo-png' : '' }}">
										<a class="title" href="javascript:;">
											<h2 class="container">
												{!! strip_tags(!empty($question->stats_title_question) ? $question->questionWithoutTooltips() : $question->translateorNew(App::getLocale())->stats_title) !!}
											</h2>
										</a>
										<div class="echo-inner">
											<div class="echo-wings"></div>
											<div class="contents container {!! !empty(request()->input('scale-for')) ? 'scale-contents' : '' !!}">
																	
												@if(!empty($question->translateorNew(App::getLocale())->stats_subtitle))
													<p class="stats-subtitle">{{ nl2br($question->translateorNew(App::getLocale())->stats_subtitle) }}</p>
												@endif
												<div class="flex box">
													<div class="scales flex flex-center">
														{!! $format == 'pdf' ? trans('vox.page.stats.scale-by') : 'Results by' !!}:
														<a href="javascript:;" class="active" scale="{{ $sk }}" scale-name="{{ trans('vox.page.stats.group-by-'.$sk) }}">
															{{ trans('vox.page.stats.group-by-'.$sk) }}
														</a>
													</div>
												</div>

												<div class="graphs flex {!! $question->type=='multiple_choice' || $question->type=='rank' ? 'multiple-stat' : '' !!}" style="background-color: #f5f5f53b !important;">
													<div class="chart chart-1">
														<div class="main-chart" chart></div>
														<div class="total total-all">
															{!! trans('vox.page.stats.total') !!}: <b></b>
														</div>
														<div class="hint"></div>
													</div>
													<div class="chart chart-2">
														<div class="dependency-question"></div>
														<div class="second-chart" chart></div>

														<div class="total-gender">
															<label for="scale-gender-m-{{ $question->id }}" class="total total-f" custom-for="scale-gender-m-{{ $question->id }}">
																<img src="{{ url('new-vox-img/women-icon.svg') }}" alt="Dentavox statistics woman icon">
																{!! trans('vox.page.stats.total-women') !!}: <b></b>
															</label>
															<label for="scale-gender-f-{{ $question->id }}" class="total total-m" custom-for="scale-gender-f-{{ $question->id }}">
																<img src="{{ url('new-vox-img/man-icon.svg') }}" alt="Dentavox statistics man icon">
																{!! trans('vox.page.stats.total-men') !!}: <b></b>
															</label>
														</div>
														@if($question->type!='multiple_choice' && $question->type!='rank')
															<div class="total total-f">
																{!! trans('vox.page.stats.total-women') !!}: <b></b>
															</div>
															<div class="icon total-f"></div>
														@endif
														<div class="map-hint">
															{!! trans('vox.page.stats.respondents') !!}
														</div>
													</div>
													<div class="chart chart-3">
														<div class="third-chart" chart></div>
														<div class="total total-m">
															{!! trans('vox.page.stats.total-men') !!}: <b></b>
														</div>
														@if($question->type!='multiple_choice' && $question->type!='rank')
															<div class="icon total-m">
															</div>
														@endif
													</div>
													<div class="legend flex"></div>
												</div>
												<div class="alert alert-info st-daterange-error" style="display: none;">
													There are no results for this period.
												</div>
											</div>
											<div class="echo-wings"></div>
										</div>
										@if($format == 'png')
											<div class="footer-png">
												<p>
													Source survey: "{!! $vox->title !!}" <br/>
													Base: {{ $respondents }} respondents, {{ $all_period }}
												</p>
												<img src="{{ url('new-vox-img/footer-logo-png.png') }}">
											</div>
										@endif
									</div>
								</div>
							@endif
						@endforeach
					</div>
				</div>

			</div>

			<div style="display: none;">

				<a id="make-stat-image-btn" href="javascript:;">make img</a>

				<form method="post" id="download-form-pdf" action="{{ getLangUrl('create-stat-pdf') }}">
					{!! csrf_field() !!}
					<input type="hidden" name="hidden_html" id="hidden_html" />
					<input type="hidden" name="stats-title" value="{{ trans('vox.page.stats.title-single', ['name' => $vox->title]) }}">
					<input type="hidden" name="stats-original-title" value="{{ $vox->title }}">
					<input type="hidden" name="stats-respondents" value="{{ $respondents }}">
					<input type="hidden" name="period" value="{{ $all_period }}">
					<input type="hidden" name="hidden_heigth" id="hidden_heigth" value="">
					<input type="hidden" name="stat_url" id="stat_url" value="{{ $vox->getStatsList() }}">
	    			<button type="submit" class="btn btn-danger btn-xs">Make PDF</button>
				</form>
			</div>
			<!-- tova da e display none -->
			<div id="stats-imgs"></div> 


			<form method="post" id="download-form-png" action="{{ getLangUrl('create-stat-png') }}">
				{!! csrf_field() !!}
				<input type="hidden" name="stat_url_png" id="stat_url_png" value="{{ $vox->getStatsList() }}">
				<input type="hidden" name="stat_title_png" id="stat_title_png" value="{{ $vox->title }}">
			</form>

			<!-- tova da e display none -->
			<div id="stats-png-imgs"></div> 
		</div>

		<link rel="stylesheet" type="text/css" href="{{ url('/font-awesome/css/all.min.css') }}" />

		<script src="{{ url('/js/jquery-3.4.1.min.js') }}"></script>
		<script src="{{ url('/js/cookie.min.js') }}"></script>
		<script src="{{ url('/js/dom-to-image.min.js') }}"></script>
		<script src="{{ url('/js-vox/main-new.js').'?ver='.$cache_version }}"></script>
		<script src="{{ url('/js-vox/stats-single.js').'?ver='.$cache_version }}"></script>
		<script src="{{ url('/js-vox/stats-single-loaded.js').'?ver='.$cache_version }}"></script>
		<script src="{{ url('/js/moment.js') }}"></script>
		<script src="{{ url('/js/amcharts-core.js') }}"></script>
		<script src="{{ url('/js/amcharts-maps.js') }}"></script>
		<script src="{{ url('/js/amcharts-worldLow.js') }}"></script>
		<script src="{{ url('/js/gstatic-charts-loader.js') }}"></script>
		<script type="text/javascript">
        	var images_path = '{{ url('img-trp') }}'; //Map pins
        	var lang = '{{ App::getLocale() }}';
        	var user_id = {{ !empty($user) ? $user->id : 'null' }};
        	var user_type = '{{ !empty($user) ? ($user->is_dentist ? 'dentist' : 'patient') : 'null' }}';
        	var featured_coin_text = '{!! nl2br( trans('vox.common.featured-tooltip') ) !!}';
        </script>
    </body>
</html>