<div class="flex box">
	<div class="scales flex flex-center">
		@include('vox.template-parts.stats-scale-buttons', [
			'scale_id' => '0'
		])
	</div>

	<div class="share-buttons flex" data-href="{{ str_replace('dentavox', 'vox', $vox->getStatsList()) }}">
		<span>
			{!! trans('vox.page.stats.share') !!}
		</span>
		<div class="share-button fb tac">
			<a class="share" href="javascript:;">
				<img class="fb" src="{{ url('img/fb-footer.png') }}"/>
			</a>
		</div>
		<div class="share-button twt tac">
			<a class="share" href="javascript:;">
				<img class="fb" src="{{ url('img/twitter.png') }}"/>
			</a>
		</div>
		@if(!empty($user))
			<a href="javascript:;" class="red-button download-stats-popup-btn" for-stat="{{ $question->id }}" {!! !empty($question->stats_scale_answers) ? 'for-scale="'.($key + 1).'"' : '' !!}">
		@else
			<a href="javascript:;" class="red-button scroll-to-blurred">
		@endif
			<img src="{{ url('new-vox-img/download.png') }}"/>{!! trans('vox.page.stats.download') !!}
		</a>
	</div>
</div>

<div class="graphs flex {!! $question->type=='multiple_choice' || $question->type=='rank' ? 'multiple-stat' : '' !!}">

	@if(false && count(json_decode($question->answers, true)) > 9)
		<div class="legend flex more-q-legend">
			
		</div>
		<div class="flex more-q-content">
	@endif
	<div class="loader-mask stats-mask">
		@if(!empty($user))
		    <img class="stats-loader" src="{{ url('new-vox-img/dentavox-statistics-loader.gif') }}" alt="Dentavox statistics loader">
		@endif
	</div>

	<div class="chart chart-1">
		<div class="main-chart" chart></div>
		<div class="total total-all">
			{!! trans('vox.page.stats.total') !!}: <b></b>
		</div>
		<div class="hint"></div>
		<div class="relation-hint" style="display: none;" for-single="{!! trans('vox.page.stats.click-on-chart-single') !!}" for-multiple="{!! trans('vox.page.stats.click-on-chart-multiple') !!}">{!! trans('vox.page.stats.click-on-chart-single') !!}</div>
	</div>
	@if($question->type!='multiple_choice' && $question->type!='rank')
		<a href="javascript:;" class="mobile-button-legend">
			<img class="legend-img" src="{{ url('new-vox-img/stats-legend.svg') }}"/>
			<img class="arrow-up" src="{{ url('img/arrow-up.png') }}"/>
			{!! trans('vox.page.stats.check-legend') !!}
		</a>
	@endif
	<div class="scales flex flex-center mobile-scales">
		@include('vox.template-parts.stats-scale-buttons', [
			'scale_id' => '1'
		])
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
		@if($question->type!='multiple_choice' && $question->type != 'rank')
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
		@if($question->type!='multiple_choice' && $question->type != 'rank')
			<div class="icon total-m">
			</div>
		@endif
	</div>
	@if( true || count(json_decode($question->answers, true)) <= 9)
		<div class="legend flex">
		</div>
	@else
		</div>
	@endif
	
	@if($question->used_for_stats=='related')
		<div class="main-title">
			{{ $question->related->question }}
		</div>
	@endif	
	<a class="nav nav-left">
	</a>
	<a class="nav nav-right">
	</a>
</div>

<div class="alert alert-info" id="daterange-error" style="display: none;">
	{{ trans('vox.page.stats.no-results-query') }}
	<!-- {!! trans('vox.page.stats.no-results-single') !!} -->
</div>
<!-- @if( $question->type=='multiple_choice' )
	<div class="multiple-hint">
		{!! trans('vox.page.stats.multiple-hint') !!}
	</div>
@endif -->