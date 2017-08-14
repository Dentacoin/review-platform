<select name="padding" class="form-control padding">
	@foreach( config('paddings.block_paddings') as $code => $px )
		<option {!! ((empty($padding) && $code==config('enums.block_padding_default')) || (!empty($padding) && $padding==$code)) ? 'selected="selected"' : '' !!} value="{{ $code }}">{{ trans('admin.common.padding.'.$code) }} ({{ $px }}px)</option>
	@endforeach
</select>

