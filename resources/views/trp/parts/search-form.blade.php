<form class="search-form" method="POST" action="{{ url('search-dentists') }}">
	{!! csrf_field() !!}
	<div class="input-wrapper search-dentist-wrapper">
		<img src="{{ url('img-trp/black-search.svg') }}" width="17" height="18"/>
		<input 
			id="search-dentist-name" 
			type="text" 
			name="dentist_name" 
			value="{{ !empty($query) ? $formattedAddress : '' }}" 
			placeholder="Dentist / clinic name" 
			autocomplete="off"
		/>
		<input type="hidden" name="search-dentist-id" id="search-dentist-id"/>
	</div>
	<div class="input-wrapper">
		<img src="{{ url('img-trp/world.svg') }}" width="16" height="16"/>
		<input 
			id="search-dentist-country" 
			type="text" 
			name="dentist_country_name" 
			value="{{ !empty($query) ? $formattedAddress : '' }}" 
			placeholder="Country" 
			autocomplete="off"
		/>
		<input type="hidden" name="dentist_country_id" id="search-country-id"/>
	</div>
	<div class="input-wrapper">
		<img src="{{ url('img-trp/pin.svg') }}" width="12" height="15"/>
		<input 
			id="search-dentist-city" 
			type="text" 
			name="dentist_city" 
			value="{{ !empty($query) ? $formattedAddress : '' }}" 
			placeholder="City, State" 
			autocomplete="off"
		/>
	</div>
	<label class="green-checkbox" for="partner">
		<img src="{{ url('img-trp/mini-logo-black.svg') }}"/>
		DCN Accepted
		<span class="checked-partner">✓</span>
		<input class="checkbox" type="checkbox" name="is_partner" value="1" id="partner"/>
	</label>
	<button type="submit">
		Search
		<img src="{{ url('img-trp/white-search.svg') }}"/>
	</button>
	<input type="hidden" name="submit-form" value="1"/>
	<div class="dentists-names-results" style="display: none;">
		<div class="dentists-results results-type">
			<div class="list">
			</div>
		</div>
	</div>
	<div class="dentists-countries-results">
		<p class="info">Sorry, we couldn’t find any matches. Check your search query for typos and try again.</p>
		<div class="dentists-countries-results-wrapper">
			@foreach($countriesAlphabetically as $letter => $countryArray)
				<div class="letters-country-section">
					<p class="letter">{{ $letter }}</p>

					<div class="flex flex-mobile">
						@foreach($countryArray as $countryArr)
							@php
								$secondNames = [
									231 => 'uk',
									232 => 'usa',
								];
							@endphp
							<div 
								class="country" 
								country-name="{{$countryArr['name']}}" 
								{!! array_key_exists($countryArr['id'], $secondNames) ? 'country-second-name="'.$secondNames[$countryArr['id']].'"' : '' !!}
							>
								<a 
									class="country-button" 
									href="javascript:;" 
									country-id="{{ $countryArr['id'] }}" 
									country-name="{{ $countryArr['name'] }}" 
									country-code="{{ $countryArr['code'] }}"
								>
									{{ $countryArr['name'] }} <span>({{ $countryArr['dentist_count'] }})</span>
								</a>
							</div>
						@endforeach
					</div>
				</div>
			@endforeach
		</div>
		<a href="{{ getLangUrl('dentist-listings-by-country') }}" class="browse-country">
			{{-- {!! nl2br(trans('trp.common.search-dentists-countries')) !!} --}}
			Browse providers by country >
		</a>
	</div>
	<div class="dentists-cities-results">
		<p class="info">Sorry, we couldn’t find any matches. Check your search query for typos and try again.</p>
		<div class="dentists-cities-results-wrapper">
			<div class="locations-results results-type">
			</div>
		</div>
		<a href="{{ getLangUrl('dentist-listings-by-country') }}" class="browse-city">
			{{-- {!! nl2br(trans('trp.common.search-dentists-countries')) !!} --}}
			Browse providers by city >
		</a>
	</div>
</form>	