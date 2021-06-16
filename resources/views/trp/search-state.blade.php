@extends('trp')

@section('content')

	<div class="page-c">
		<div class="black-overflow" style="display: none;"></div>
		
		<div class="search-gradient-wave green-gradient">
			<div class="home-search-form">
			    @include('trp.parts.search-form')
			</div>
	    	<!-- <h1 class="white-title">
	    		{!! $main_title !!}
	    	</h1> -->
		</div>

	    <div class="countries-wrapper container">
		    <div class="countries">
		    	@if(count($states_name))

			    	<div class="show-all-dentists">
			    		<a href="{{ getLangUrl('dentists/'.$country->slug) }}" class="show-all-dentists">{!! trans('trp.page.search.show-all') !!}</a>
			    	</div>
			    	<div class="flex">
			    		<div class="col">
					    	@foreach($states_name as $key => $cuser)
					    		@if(!is_object($cuser))
					    			<span class="letter">{{ $cuser }}</span>
					    		@else
					    			<a href="{{ getLangUrl('dentists-in-'.$country->slug.'/'.$cuser->state_slug) }}">{{ $cuser->state_name }}</a>
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