@extends('trp')

@section('content')

	<div class="search-title">
		<div class="container">
			<h1 class="mont">Find The Best Dental Providers <br/>
				<span class="mont subtitle">Across The World</span>
			</h1>
		</div>
	</div>

	<div class="search-filters">
		<div class="container">
			<div class="quick-search-country-wrapper">
				<img src="{{ url('img-trp/black-search.svg') }}" width="17" height="18"/>
				<input type="text" name="quick-search-country" id="quick-search-country" placeholder="Quick search"/>
			</div>
			<div class="hidden-mobile-filters">
				<a href="javascript:;" class="close-filter"><img src="{{ url('img-trp/blue-arrow-left.png') }}" /></a>
				<h3><img src="{{ url('img-trp/filters-blue.svg') }}" />Filters</h3>
				<div class="continents-filter">
					<a href="javascript:;" class="continent all-continents active">All continents</a>
					@foreach($continents as $continent)
						<a href="javascript:;" class="continent" id="{{ $continent->id }}" dentists-count="{{ $continentDentists[$continent->id] }}">{{ $continent->name }}</a>
					@endforeach
				</div>
				<div class="countries-letters-wrapper">
					<a href="javascript:;" class="countries-letter all-letters active">All</a>
					@foreach($countriesAlphabetically as $letter => $countryArray)
						<a href="javascript:;" class="countries-letter" letter="{{ $letter }}">{{ $letter }}</a>
					@endforeach
				</div>
			</div>
			<a href="javascript:;" class="open-country-filters"><img src="{{ url('img-trp/filters.svg') }}" />Filters</a>
		</div>
	</div>

	<div class="countries-wrapper">
		<div class="continent-title-wrapper">
			<div class="container flex flex-center flex-mobile space-between">
				<p class="continent-title">
				</p>
				<p class="continent-dentists">
					<span>103</span> dental practices
				</p>
			</div>
		</div>
		<div class="container">
			@foreach($countriesAlphabetically as $letter => $countryArray)
				<div class="letters-country-section" letter="{{ $letter }}">
					<p class="letter">{{ $letter }}</p>

					<div class="flex flex-mobile">
						<div class="country">
							@foreach($countryArray as $countryArr)
								
								@php
									$secondNames = [
										231 => 'uk',
										232 => 'usa',
									];
								@endphp

								<a 
									class="country-button" 
									href="{{ getLangUrl('dentists-in-'.$countryArr['slug']) }}" 
									country-id="{{ $countryArr['id'] }}" 
									country-name="{{ $countryArr['name'] }}" 
									country-code="{{ $countryArr['code'] }}"
									continent-id="{{ $countryArr['continent'] }}"
									{!! array_key_exists($countryArr['id'], $secondNames) ? 'country-second-name="'.$secondNames[$countryArr['id']].'"' : '' !!}
								>
									{{ $countryArr['name'] }} <span>({{ $countryArr['dentist_count'] }})</span>
								</a>
								
								@if($loop->iteration%3 == 0)
									</div>
									<div class="country">
								@endif
							@endforeach
						</div>
					</div>
				</div>
			@endforeach
		</div>
	</div>

	<a href="javascript:;" class="scroll-up">
		<img src="{{ url('img-trp/arrow-up.svg') }}"/>
	</a>

@endsection