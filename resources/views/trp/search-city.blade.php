@extends('trp')

@section('content')

	<div class="search-title">
		<div class="container">
			<h1 class="mont">Find The Best Dental Care in
				<span class="mont subtitle">{{ $state_slug ? $stateName.', ' : '' }}{{ $country->name }}</span>
			</h1>
		</div>
	</div>

	<div class="country-all-practices">
		<div class="container flex flex-center flex-mobile space-between">
			<a href="{{ getLangUrl('dentists/'.$country->slug) }}" class="show-all">SHOW ALL PRACTICES</a>
			<p class="country-dentists">({{ $countryCount }} results)</p>
		</div>
	</div>

	<div class="countries-wrapper cities-wrapper">
		<div class="container">
			@foreach($cities as $letter => $city)
				<div class="letters-country-section" letter="{{ $letter }}">
					<p class="letter">{{ $letter }}</p>

					<div class="flex flex-mobile">
						@foreach($city as $c)
							<div class="country">
								<a 
									class="country-button" 
									href="{{ getLangUrl( str_replace([' ', "'"], ['-', ''], 'dentists/'.strtolower($c['city_name']).'-'.($c['state_slug'] == 'ega' ? '' : $c['state_slug'].'-').$country->slug)) }}" 
								>
									{{ $c['city_name'] }}
									<span>({{ $c['cnt'] }} results)</span>
								</a>
							</div>
						@endforeach
					</div>
				</div>
			@endforeach
		</div>
	</div>

@endsection