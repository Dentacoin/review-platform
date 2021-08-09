<div class="demogr-inner" style="display: none" inner="{{ $question->id }}" {{ $for_scale ? 'scale="'.($key + 1).'"' : '' }}>
    @if($question->used_for_stats=='dependency')
        <label for="format-relation-{{ $for_scale ? ($question->id.'-'.($key + 1)) : $question->id }}" class="active dem-label">
            <input type="checkbox" name="download-demographic[]" value="relation" id="format-relation-{{ $for_scale ? $question->id.'-'.($key + 1) : $question->id }}" class="download-demographic-checkbox" checked="checked">
            {{ trans('vox.page.stats.relation') }}
            <div class="active-removal"><span>x</span></div>
        </label>
    @endif
    @foreach( $question->stats_fields as $sk)
        @if($sk == 'gender')
            <label for="format-gender-{{ $for_scale ? ($question->id.'-'.($key + 1)) : $question->id }}" class="{{ !$for_scale ? ($loop->first && $question->used_for_stats!='dependency' ? 'active' : '') : ($loop->first ? 'active' : '') }} dem-label">
                <input type="checkbox" name="download-demographic[]" value="gender" id="format-gender-{{ $for_scale ? ($question->id.'-'.($key + 1)) : $question->id }}" class="download-demographic-checkbox" {{ !$for_scale ? ($question->used_for_stats!='dependency' ? 'checked="checked"' : '') : 'checked="checked"' }}>
                {{ trans('vox.page.stats.sex') }}
        @elseif($sk == 'country_id')
            <label for="format-country_id-{{ $for_scale ? ($question->id.'-'.($key + 1)) : $question->id }}" class="{{ $loop->first ? 'active' : '' }} dem-label" style="display: none;">
                <input type="checkbox" name="download-demographic[]" value="country_id" id="format-country_id-{{ $for_scale ? ($question->id.'-'.($key + 1)) : $question->id }}" class="download-demographic-checkbox">
                {{ trans('vox.page.stats.location') }}
        @elseif($sk == 'age')
            <label for="format-age-{{ $for_scale ? ($question->id.'-'.($key + 1)) : $question->id }}" class="{{ $loop->first ? 'active' : '' }} dem-dropdown dem-label">
                <input type="checkbox" name="download-demographic[]" value="age" id="format-age-{{ $for_scale ? ($question->id.'-'.($key + 1)) : $question->id }}" class="download-demographic-checkbox">
                {{ trans('vox.page.stats.age') }}
                <div class="dem-arrow">
                </div>
                <div class="demogr-options">
                    <div class="close-dem-options">x</div>
                    <label for="download-age-all-{{ $for_scale ? ($question->id.'-'.($key + 1)) : $question->id }}" class="select-all-dem-label active">
                        <div class="checkbox-square">✓</div>
                        <input type="checkbox" name="download-age[]" value="all" id="download-age-all-{{ $for_scale ? ($question->id.'-'.($key + 1)) : $question->id }}" class="select-all-dem dem-checkbox" checked="checked">
                        {{ trans('vox.page.stats.select-all') }}
                    </label>
                    @foreach(config('vox.age_groups') as $ak => $av)
                        <label for="download-age-{{ $ak }}-{{ $for_scale ? ($question->id.'-'.($key + 1)) : $question->id }}" class="active">
                            <div class="checkbox-square">✓</div>
                            <input type="checkbox" name="download-age[]" value="{{ $ak }}" id="download-age-{{ $ak }}-{{ $for_scale ? ($question->id.'-'.($key + 1)) : $question->id }}" class="dem-checkbox" checked="checked">
                            {{ $av }}
                        </label>
                    @endforeach
                </div>
        @else
            <label for="format-{{ $sk }}-{{ $for_scale ? ($question->id.'-'.($key + 1)) : $question->id }}" class="{{ $loop->first ? 'active' : '' }} dem-dropdown dem-label">
                <input type="checkbox" name="download-demographic[]" value="{{ $sk }}" id="format-{{ $sk }}-{{ $for_scale ? ($question->id.'-'.($key + 1)) : $question->id }}" class="download-demographic-checkbox">
                {{ trans('vox.page.stats.group-by-'.$sk) }}
                <div class="dem-arrow">
                </div>
                <div class="demogr-options">
                    <div class="close-dem-options">x</div>
                    <label for="download-{{ $sk }}-all-{{ $for_scale ? ($question->id.'-'.($key + 1)) : $question->id }}" class="select-all-dem-label active">
                        <div class="checkbox-square">✓</div>
                        <input type="checkbox" name="download-{{ $sk }}[]" value="all" id="download-{{ $sk }}-all-{{ $for_scale ? ($question->id.'-'.($key + 1)) : $question->id }}" class="select-all-dem dem-checkbox" checked="checked">
                        {{ trans('vox.page.stats.select-all') }}
                    </label>
                    @foreach(config('vox.details_fields.'.$sk.'.values') as $skk => $sv)
                        <label for="download-{{ $sk }}-{{ $skk }}-{{ $for_scale ? ($question->id.'-'.($key + 1)) : $question->id }}" class="active">
                            <div class="checkbox-square">✓</div>
                            <input type="checkbox" name="download-{{ $sk }}[]" value="{{ $skk }}" id="download-{{ $sk }}-{{ $skk }}-{{ $for_scale ? ($question->id.'-'.($key + 1)) : $question->id }}" class="dem-checkbox" checked="checked">
                            {{ $sv }}
                        </label>
                    @endforeach
                </div>
        @endif
            <div class="active-removal"><span>x</span></div>
        </label>
    @endforeach
</div>