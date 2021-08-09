<div class="flex box">
	<div class="scales flex flex-center">
		{!! trans('vox.page.stats.scale-by') !!}:
		<a href="javascript:;" class="active" scale="{{ $sk }}" scale-name="{{ trans('vox.page.stats.group-by-'.$sk) }}">
			{{ trans('vox.page.stats.group-by-'.$sk) }}
		</a>
	</div>
</div>

<div class="graphs flex {!! $question->type=='multiple_choice' || $question->type=='rank' ? 'multiple-stat' : '' !!}" style="background-color: #f5f5f53b !important;">

	<div class="loader-mask stats-mask">
	    <img class="stats-loader" src="{{ url('new-vox-img/dentavox-statistics-loader.gif') }}" alt="Dentavox statistics loader">
  	</div>

	<div class="chart chart-1">
		<div style="display: none;" class="chart-img"></div>
		<div class="main-chart" chart></div>
		<div class="total total-all">
			{!! trans('vox.page.stats.total') !!}: <b></b>
		</div>
		<div class="hint"></div>
	</div>
	@if($question->type!='multiple_choice' && $question->type!='rank')
		<a href="javascript:;" class="mobile-button-legend">
			<img class="legend-img" src="{{ url('new-vox-img/stats-legend.svg') }}"/>
			<img class="arrow-up" src="{{ url('img/arrow-up.png') }}"/>
			{!! trans('vox.page.stats.check-legend') !!}
		</a>
	@endif
	<div class="scales flex flex-center mobile-scales">
		{!! trans('vox.page.stats.scale-by') !!}:
		<a href="javascript:;" class="active" scale="{{ $sk }}" scale-name="{{ trans('vox.page.stats.group-by-'.$sk) }}">
			{{ trans('vox.page.stats.group-by-'.$sk) }}
		</a>
	</div>

	<div class="multiple-gender-nav">
		<a href="javascript:;" class="gender-nav-left">
			<img src="{{ url('img/caret-left.png') }}"/>
		</a>
		<div class="multiple-gender-nav-content">
			<span class="nav-color"></span> <span class="gender-text">Brushing teeth</span>
		</div>
		<a href="javascript:;" class="gender-nav-right">
			<img src="{{ url('img/caret-rigth.png') }}"/>
		</a>
	</div>
	<div class="chart chart-2">
		<div style="display: none;" class="chart-img"></div>
		<div class="dependency-question"></div>
		<div class="second-chart" chart></div>
		@if($question->used_for_stats=='standard')

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
		@endif
	</div>
	<div class="chart chart-3">
		<div style="display: none;" class="chart-img"></div>
		<div class="third-chart" chart></div>
		@if($question->used_for_stats=='standard')
			<div class="total total-m">
				{!! trans('vox.page.stats.total-men') !!}: <b></b>
			</div>
			@if($question->type!='multiple_choice' && $question->type!='rank')
				<div class="icon total-m">
				</div>
			@endif
		@endif
	</div>
	@if( true || count(json_decode($question->answers, true)) <= 9)
		<div class="legend flex">
		</div>
	@endif
	
	@if($question->used_for_stats=='related')
		<div class="main-title">
			{{ $question->related->question }}
		</div>
	@endif	
	@if($question->used_for_stats=='standard')
		<a class="nav nav-left">
		</a>
		<a class="nav nav-right">
		</a>
	@endif
</div>