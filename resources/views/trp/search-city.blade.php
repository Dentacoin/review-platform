@extends('trp')

@section('content')

	<div class="page-dentists page-c">
		<div class="black-overflow" style="display: none;">
		</div>

		<div class="search-gradient-wave green-gradient">
			<div class="home-search-form">
			    @include('trp.parts.search-form')			
			</div>

			<div class="main-top">
		    </div>

		    <div class="sort-wrapper">
		    	<h1 class="white-title">
		    		{!! nl2br(trans('trp.page.search.city-title', [
			            'country' => $all_cities->first()->state_name.', '.$country->name,
			        ])) !!}
		    	</h1>
		    </div>
		</div>

	    <div class="countries-wrapper container">
		    <div class="countries">
		    	@if(count($cities_name))
			    	<div class="flex">
			    		<div class="col">
					    	@foreach($cities_name as $key => $cuser)
					    		@if(!is_object($cuser))
					    			<span class="letter">{{ $cuser }}</span>
					    		@else
					    			<a href="{{ getLangUrl( str_replace([' ', "'"], ['-', ''], 'dentists/'.strtolower($cuser->city_name).'-'.$cuser->state_slug.'-'.$cuser->country->slug)) }}">{{ $cuser->city_name }}</a>
					    		@endif
					    		@if( $total_rows > 8 && in_array($key, $breakpoints) && !$loop->last)
					    			</div>
					    			<div class="col">
					    		@endif

					    	@endforeach
					    </div>
				    </div>
			    @else
		    		<div class="alert alert-info">
		    			{!! nl2br(trans('trp.page.search.no-results-dentist',[
		    				'link' => '<a href="'.getLangUrl('dentist-listings-by-country').'">',
		    				'endlink' => '</a>',
		    			])) !!}
		    		</div>
		    		<br/>
			    @endif
		    </div>
		</div>
	</div>

@endsection