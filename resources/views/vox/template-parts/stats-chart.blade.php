<div class="flex box">
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
	<div class="share-buttons flex" data-href="{{ str_replace('dentavox', 'vox', $vox->getStatsList()) }}">
		<span>
			{!! trans('vox.page.stats.share') !!}
		</span>
		<div class="share-button fb tac">
			<a class="share" href="javascript:;">
				<i class="fab fa-facebook-f"></i>
			</a>
		</div>
		<div class="share-button twt tac">
			<a class="share" href="javascript:;">
				<i class="fab fa-twitter"></i>
			</a>
		</div>
	</div>
</div>
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
	@if($question->used_for_stats=='standard')
		<a class="nav nav-left">
		</a>
		<a class="nav nav-right">
		</a>
	@endif
</div>
@if( $question->type=='multiple_choice' )
	<div class="multiple-hint">
		{!! trans('vox.page.stats.multiple-hint') !!}
	</div>
@endif