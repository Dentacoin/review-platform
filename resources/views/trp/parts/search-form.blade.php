<form class="front-form search-form">
	{{-- <input 
		id="search-input" 
		type="text" 
		name="location" 
		value="{{ !empty($query) ? $formattedAddress : '' }}" 
		placeholder="{!! nl2br(trans('trp.common.search-placeholder')) !!}" 
		autocomplete="off"
	/> --}}
	<div class="input-wrapper search-dentist-wrapper">
		<img src="{{ url('img-trp/black-search.svg') }}" width="17" height="18"/>
		<input 
			id="search-dentist-name" 
			type="text" 
			name="dentist-name" 
			value="{{ !empty($query) ? $formattedAddress : '' }}" 
			placeholder="Dentist / clinic name" 
			autocomplete="off"
		/>
	</div>
	<div class="input-wrapper">
		<img src="{{ url('img-trp/world.svg') }}" width="16" height="16"/>
		<input 
			id="search-dentist-country" 
			type="text" 
			name="dentist-country" 
			value="{{ !empty($query) ? $formattedAddress : '' }}" 
			placeholder="Country" 
			autocomplete="off"
		/>
	</div>
	<div class="input-wrapper">
		<img src="{{ url('img-trp/pin.svg') }}" width="12" height="15"/>
		<input 
			id="search-dentist-city" 
			type="text" 
			name="dentist-city" 
			value="{{ !empty($query) ? $formattedAddress : '' }}" 
			placeholder="City" 
			autocomplete="off"
		/>
	</div>
	<label for="partner">
		<img src="{{ url('img-trp/mini-logo-black.svg') }}"/>
		DCN Accepted
		<span class="checked-partner">✓</span>
		<input type="checkbox" name="partner" value="1" id="partner"/>
	</label>
	<button type="submit">
		Search
		<img src="{{ url('img-trp/white-search.svg') }}"/>
	</button>
	{{-- <div class="results" style="display: none;">
		<div class="dentists-results results-type">
			<span class="result-title">
				{!! nl2br(trans('trp.common.search-dentists')) !!}
			</span>
			<div class="clearfix list">
			</div>
		</div>
		<div class="locations-results results-type">
			<span class="result-title">
				{!! nl2br(trans('trp.common.search-locations')) !!}
			</span>
			<div class="clearfix list">
			</div>
		</div>
		<div class="global-results results-type">
			<span class="result-title">
				{!! nl2br(trans('trp.common.search-global')) !!}
			</span>
			<div class="clearfix list">
				<a href="{{ getLangUrl('dentists/worldwide') }}" class="special">
					{!! nl2br(trans('trp.common.search-partners')) !!}
				</a>
			</div>
			<div class="clearfix list">
				<a href="{{ getLangUrl('dentist-listings-by-country') }}" class="special">
					{!! nl2br(trans('trp.common.search-dentists-countries')) !!}
				</a>
			</div>
		</div>
	</div> --}}
</form>	