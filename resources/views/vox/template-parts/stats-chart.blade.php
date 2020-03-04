<div class="flex box">
	<div class="scales flex flex-center">
		@include('vox.template-parts.stats-scale-buttons')
	</div>

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

<div class="graphs flex {!! $question->type=='multiple_choice' ? 'multiple-stat' : '' !!} " >

	@if(false && count(json_decode($question->answers, true)) > 9)
		<div class="legend flex more-q-legend">
			
		</div>
		<div class="flex more-q-content">
	@endif
	<div class="loader-mask stats-mask">
	    <img class="stats-loader" src="{{ url('new-vox-img/dentavox-statistics-loader.gif') }}" alt="Dentavox statistics loader">
  	</div>

	<div class="chart chart-1">
		<div class="main-chart"></div>
		<div class="total total-all">
			{!! trans('vox.page.stats.total') !!}: <b></b>
		</div>
		<div class="hint"></div>
	</div>
	@if($question->type!='multiple_choice')
		<a href="javascript:;" class="mobile-button-legend">
			<img src="{{ url('new-vox-img/stats-legend.svg') }}"><i class="fas fa-arrow-up"></i>Check legend
		</a>
	@endif
	<div class="scales flex flex-center mobile-scales">
		{!! trans('vox.page.stats.scale-by') !!}:
		@if($question->used_for_stats=='dependency')
			<a href="javascript:;" class="active" scale="dependency">Relation</a>
		@endif
		@foreach( $question->stats_fields as $sk)
			<a href="javascript:;" class="{!! $loop->first && $question->used_for_stats!='dependency' ? 'active' : '' !!} {!! array_key_exists($sk, config('vox.details_fields')) || $sk == 'age' || ($sk == 'gender' && $question->type == 'multiple_choice') ? 'with-children' : '' !!}" scale="{{ $sk }}">
				{{ trans('vox.page.stats.group-by-'.$sk) }}

				@if(array_key_exists($sk, config('vox.details_fields')))
					<div class="scales-filter">
						<label for="scale-{{ $sk }}-all-{{ $question->id }}-1" class="select-all-scales-label active">
							<i class="far fa-square"></i>
							<input type="checkbox" name="scale-{{ $sk }}[]" value="all" id="scale-{{ $sk }}-all-{{ $question->id }}-1" class="select-all-scales scale-checkbox" checked="checked">
							Select all
						</label>
						@foreach(config('vox.details_fields')[$sk]['values'] as $skk => $sv)
							<label for="scale-{{ $sk }}-{{ $skk }}-{{ $question->id }}-1" class="active">
								<i class="far fa-square"></i>
								<input type="checkbox" name="scale-{{ $sk }}[]" value="{{ $skk }}" id="scale-{{ $sk }}-{{ $skk }}-{{ $question->id }}-1" class="scale-checkbox" checked="checked">
								{{ $sv }}
							</label>
						@endforeach
					</div>
				@elseif($sk == 'age')
					<div class="scales-filter">
						<label for="scale-age-all-{{ $question->id }}-1" class="select-all-scales-label active">
							<i class="far fa-square"></i>
							<input type="checkbox" name="scale-age[]" value="all" id="scale-age-all-{{ $question->id }}-1" class="select-all-scales scale-checkbox" checked="checked">
							Select all
						</label>
						@foreach(config('vox.age_groups') as $ak => $av)
							<label for="scale-age-{{ $ak }}-{{ $question->id }}-1" class="active">
								<i class="far fa-square"></i>
								<input type="checkbox" name="scale-age[]" value="{{ $ak }}" id="scale-age-{{ $ak }}-{{ $question->id }}-1" class="scale-checkbox" checked="checked">
								{{ $av }}
							</label>
						@endforeach
					</div>
				@elseif($sk == 'gender' && $question->type == 'multiple_choice')
					<div class="scales-filter">
						<label for="scale-gender-all-{{ $question->id }}-1" class="select-all-scales-label active">
							<i class="far fa-square"></i>
							<input type="checkbox" name="scale-gender[]" value="all" id="scale-gender-all-{{ $question->id }}-1" class="select-all-scales scale-checkbox" checked="checked">
							Select all
						</label>
						<label for="scale-gender-f-{{ $question->id }}-1" class="active">
							<i class="far fa-square"></i>
							<input type="checkbox" name="scale-gender[]" value="f" id="scale-gender-f-{{ $question->id }}-1" class="scale-checkbox" checked="checked">
							Women
						</label>
						<label for="scale-gender-m-{{ $question->id }}-1" class="active">
							<i class="far fa-square"></i>
							<input type="checkbox" name="scale-gender[]" value="m" id="scale-gender-m-{{ $question->id }}-1" class="scale-checkbox" checked="checked">
							Men
						</label>
					</div>
				
				@endif
			</a>
		@endforeach
	</div>

	<div class="multiple-gender-nav">
		<a href="javascript:;" class="gender-nav-left"><i class="fas fa-caret-left"></i></a>
		<div class="multiple-gender-nav-content">
			<span class="nav-color"></span> <span class="gender-text">Brushing teeth</span>
		</div>
		<a href="javascript:;" class="gender-nav-right"><i class="fas fa-caret-right"></i></a>
	</div>
	<div class="chart chart-2">
		<div class="dependency-question"></div>
		<div class="second-chart"></div>
		@if($question->used_for_stats=='standard')

			<div class="total-gender">
				<label for="scale-gender-f-{{ $question->id }}" class="total total-f">
					<img src="{{ url('new-vox-img/women-icon.svg') }}" alt="Dentavox statistics woman icon">
					{!! trans('vox.page.stats.total-women') !!}: <b></b>
				</label>
				<label for="scale-gender-m-{{ $question->id }}" class="total total-m">
					<img src="{{ url('new-vox-img/man-icon.svg') }}" alt="Dentavox statistics man icon">
					{!! trans('vox.page.stats.total-men') !!}: <b></b>
				</label>
			</div>
			@if($question->type!='multiple_choice')
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
		<div class="third-chart"></div>
		@if($question->used_for_stats=='standard')
			<div class="total total-m">
				{!! trans('vox.page.stats.total-men') !!}: <b></b>
			</div>
			@if($question->type!='multiple_choice')
				<div class="icon total-m">
				</div>
			@endif
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
	@if($question->used_for_stats=='standard')
		<a class="nav nav-left">
		</a>
		<a class="nav nav-right">
		</a>
	@endif
</div>

<div class="alert alert-info" id="daterange-error" style="display: none;">
	There are no results
	<!-- {!! trans('vox.page.stats.no-results-single') !!} -->
</div>
<!-- @if( $question->type=='multiple_choice' )
	<div class="multiple-hint">
		{!! trans('vox.page.stats.multiple-hint') !!}
	</div>
@endif -->