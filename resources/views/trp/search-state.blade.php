@extends('trp')

@section('content')

	<div class="search-title">
		<div class="container">
			<h1 class="mont">Find The Best Dental Experts in <br/>
				<span class="mont subtitle">{{ $country->name }}</span>
			</h1>
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
								</a>
							</div>
						@endforeach
					</div>
				</div>
			@endforeach
		</div>
	</div>

@endsection