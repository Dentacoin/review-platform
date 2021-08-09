{!! trans('vox.page.stats.scale-by') !!}:
@if($question->used_for_stats=='dependency')
	<a href="javascript:;" class="active" scale="dependency" scale-name="{{ trans('vox.page.stats.relation') }}">{{ trans('vox.page.stats.relation') }}</a>
@endif
@foreach( $question->stats_fields as $sk)
	<a href="javascript:;" class="{!! $loop->first && $question->used_for_stats!='dependency' ? 'active' : '' !!} {!! $sk == 'gender' && $question->used_for_stats=='dependency' ? '' : (array_key_exists($sk, config('vox.details_fields')) || $sk == 'age' || ($sk == 'gender' && ($question->type == 'multiple_choice' || $question->type == 'rank' )) ? 'with-children' : '') !!}" scale="{{ $sk }}"  scale-name="{{ trans('vox.page.stats.group-by-'.$sk) }}">
		{{ trans('vox.page.stats.group-by-'.$sk) }}
		<div class="caret-down"></div>

		@if(array_key_exists($sk, config('vox.details_fields')))
			<div class="scales-filter">
				<label for="scale-{{ $sk }}-all-{{ $question->id }}-{{ $scale_id }}" class="select-all-scales-label active">
					<div class="checkbox-square">✓</div>
					<input type="checkbox" name="scale-{{ $sk }}[]" value="all" id="scale-{{ $sk }}-all-{{ $question->id }}-{{ $scale_id }}" class="select-all-scales scale-checkbox" checked="checked">
					{{ trans('vox.page.stats.select-all') }}
				</label>
				@foreach(config('vox.details_fields')[$sk]['values'] as $skk => $sv)
					<label for="scale-{{ $sk }}-{{ $skk }}-{{ $question->id }}-{{ $scale_id }}" class="active">
						<div class="checkbox-square">✓</div>
						<input type="checkbox" name="scale-{{ $sk }}[]" value="{{ $skk }}" id="scale-{{ $sk }}-{{ $skk }}-{{ $question->id }}-{{ $scale_id }}" class="scale-checkbox" checked="checked">
						{{ $sv }}
					</label>
				@endforeach
			</div>
		@elseif($sk == 'age')
			<div class="scales-filter">
				<label for="scale-age-all-{{ $question->id }}-{{ $scale_id }}" class="select-all-scales-label active">
					<div class="checkbox-square">✓</div>
					<input type="checkbox" name="scale-age[]" value="all" id="scale-age-all-{{ $question->id }}-{{ $scale_id }}" class="select-all-scales scale-checkbox" checked="checked">
					{{ trans('vox.page.stats.select-all') }}
				</label>
				@foreach(config('vox.age_groups') as $ak => $av)
					<label for="scale-age-{{ $ak }}-{{ $question->id }}-{{ $scale_id }}" class="active">
						<div class="checkbox-square">✓</div>
						<input type="checkbox" name="scale-age[]" value="{{ $ak }}" id="scale-age-{{ $ak }}-{{ $question->id }}-{{ $scale_id }}" class="scale-checkbox" checked="checked">
						{{ $av }}
					</label>
				@endforeach
			</div>
		@elseif($sk == 'gender' && ($question->type == 'multiple_choice' || $question->type == 'rank' ) && $question->used_for_stats!='dependency')
			<div class="scales-filter">
				<label for="scale-gender-all-{{ $question->id }}-{{ $scale_id }}" class="select-all-scales-label active">
					<div class="checkbox-square">✓</div>
					<input type="checkbox" name="scale-gender[]" value="all" id="scale-gender-all-{{ $question->id }}-{{ $scale_id }}" class="select-all-scales scale-checkbox" checked="checked">
					{{ trans('vox.page.stats.select-all') }}
				</label>
				<label for="scale-gender-f-{{ $question->id }}-{{ $scale_id }}" class="active">
					<div class="checkbox-square">✓</div>
					<input type="checkbox" name="scale-gender[]" value="f" id="scale-gender-f-{{ $question->id }}-{{ $scale_id }}" class="scale-checkbox" checked="checked">
					{{ trans('vox.page.stats.sex.women') }}
				</label>
				<label for="scale-gender-m-{{ $question->id }}-{{ $scale_id }}" class="active">
					<div class="checkbox-square">✓</div>
					<input type="checkbox" name="scale-gender[]" value="m" id="scale-gender-m-{{ $question->id }}-{{ $scale_id }}" class="scale-checkbox" checked="checked">
					{{ trans('vox.page.stats.sex.men') }}
				</label>
			</div>
		
		@endif
	</a>
@endforeach