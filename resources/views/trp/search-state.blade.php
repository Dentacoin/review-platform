@extends('trp')

@section('content')

	<div class="search-title">
		<div class="container">
			<h1 class="mont">
				{!! nl2br(trans('trp.search-state.title', [
					'the' => in_array($country->id, [232, 230]) ? 'the ' : '',
					'country' => '<span class="mont subtitle">'.$country->name.'</span>'
				])) !!}
			</h1>
		</div>
	</div>

	<div class="country-all-practices">
		<div class="container flex flex-center flex-mobile space-between">
			<a href="{{ getLangUrl('dentists/'.$country->slug) }}" class="show-all">
				{{ trans('trp.common.show-all-practices') }}
			</a>
			<p class="country-dentists">({{ trans('trp.common.results-count', [
				'count' => $countryDentistsCount
			]) }})</p>
		</div>
	</div>
	
	<div class="countries-wrapper">
		<div class="container">
			@foreach($states as $letter => $state)
				<div class="letters-country-section" letter="{{ $letter }}">
					<p class="letter">{{ $letter }}</p>

					<div class="flex flex-mobile">
						@foreach($state as $s)
							<div class="country">
								<a 
									class="country-button" 
									href="{{ getLangUrl('dentists-in-'.$country->slug.'/'.$s['state_slug']) }}" 
								>
									{{ $s['state_name'] }}
									<span>({{ $s['cnt'] }})</span>
								</a>
							</div>
						@endforeach
					</div>
				</div>
			@endforeach
		</div>
	</div>

@endsection