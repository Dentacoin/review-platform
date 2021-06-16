@extends('trp')

@section('content')

	<div class="page-c">
		<div class="black-overflow" style="display: none;"></div>
		
		<div class="search-gradient-wave green-gradient">
			<div class="home-search-form">
			    @include('trp.parts.search-form')			
			</div>
		    <h1 class="white-title">{!! nl2br(trans('trp.page.search.country-title')) !!}</h1>
		</div>

	    <div class="countries-wrapper container">
		    <div class="countries">
		    	<div class="flex">
		    		<div class="col">
				    	@foreach($countries_groups as $key => $country)
				    		@if(is_string($country))
				    			<span class="letter">{{ $country }}</span>
				    		@else
				    			<a href="{{ getLangUrl('dentists-in-'.$country->slug) }}">{{ $country->name }}</a>
				    		@endif

				    		@if( in_array($key, $breakpoints) )
				    			</div>
				    			<div class="col">
				    		@endif

				    	@endforeach
				    </div>
			    </div>
		    </div>
		</div>
	</div>

@endsection