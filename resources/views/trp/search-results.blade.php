@extends('trp')

@section('content')

	<div class="search-results-title">
		<div class="container">
			<h1 class="mont">{!! config('trp.using_google_maps') ? $search_title : str_replace('-', ' ', $search_title) !!}</h1>

			@if($dentistsFromDifferentCountry)
				<h3>
					Unfortunately, we couldn't find any dental practices containing your search term in {{ urldecode($searchCity ? (ucwords($searchCity).', ') : '')}}{{urldecode($searchCountry)}}. You might want to check these, too:
				</h3>
			@endif
		</div>
	</div>

	@php 
		$noSpecializationsHref = '';

		if($filter =='all-results') {
			// $noSpecializationsHref = getLangUrl(str_replace('/all-results', '', $query));
			$noSpecializationsHref = getLangUrl($query);
		} else {
			if(!empty($searchCategories)) {
				
				if(session('results-url')) {
					$noSpecializationsHref = getLangUrl(session('results-url'));
				} else {
					$noSpecializationsHref = getLangUrl(str_replace(' ', '-', 'dentists/'.explode('/', $query)[0] ));
				}
			} else {
				$noSpecializationsHref = getLangUrl(str_replace(' ', '-', $query ));
			}
		}

		$specializationsHref = '';

		if($filter =='all-results') {
			$specializationsHref = getLangUrl(str_replace('/all-results', '', $query));
		} else {
			$specializationsHref = str_replace('/dentists', '', 
				!empty($searchCategories) ? 
				getLangUrl(str_replace(' ', '-', explode('/', $query)[0] )) 
				: getLangUrl(str_replace(' ', '-', $query ))
			);
		}
	@endphp
	
	<form 
		method="get" 
		class="filters-section search-get-form" 
		no-specializations-href="{{$noSpecializationsHref}}" 
		specializations-href="{{$specializationsHref}}" 
		action=""
	>
		<input type="hidden" name="country" value="{{ request('country') }}"/>
		<input type="hidden" name="city" value="{{ request('city') }}"/>
		<div class="container-filters flex flex-mobile flex-center space-between">
			<div class="filters-wrapper">
				<div class="hidden-mobile-filters">
					<a href="javascript:;" class="close-filter">
						<img src="{{ url('img-trp/blue-arrow-left.png') }}"/>
					</a>
					<h3>
						<img src="{{ url('img-trp/filters-blue.svg') }}" width="20" height="15"/>Filters
					</h3>
				</div>
				@include('trp.parts.search-dentist-filters', [
					'for_branch' => false
				])
			</div>
			<div class="results-count">
				<h4 class="hidden-mobile">{{ $items->count() }} results found</h4>
				<h4 class="show-mobile">{{ $items->count() }} results</h4>
			</div>
			<div class="mobile-filters-wrapper {{ $items->isEmpty() ? 'smaller' : '' }}">
				<a href="javascript:;" class="open-filters">
					<img src="{{ url('img-trp/filters.svg') }}"/>Filters
				</a>
				@if($items->isNotEmpty() && config('trp.using_google_maps'))
					<a href="javascript:;" class="open-map">
						<img src="{{ url('img-trp/map.svg') }}"/>Map
					</a>
				@endif
			</div>
		</div>
	</form>

	<div class="results-wrapper results flex">
		<div class="col dentist-results">
			@if($items->isNotEmpty())
				@foreach($items as $dentist)
					@include('trp.parts.search-dentist', [
						'forMap' => false,
						'for_branch' => false,
					])
				@endforeach
			@else
				<div class="no-dentist-found">
					<div>
						<img src="{{ url('img-trp/no-dentist-found.png') }}"/>
					</div>
					<div>
						<p>
							We couldn't find any dental practices matching your search criteria. Please, try to expand your search.
						</p>
						<a href="javascript:;" class="text-link clear-all-filters">
							Clear Filters
						</a>
					</div>
				</div>
			@endif
		</div>
		@if(config('trp.using_google_maps'))
			<div class="col maps-results">
				<div id="search-map" lat="{{ $lat }}" lon="{{ $lon }}"></div>
			</div>
		@endif
	</div>
	<div style="display: none;">
		<input type="text" id="search-in-map" placeholder="Search in map"/>
	</div>

	@if($items->isNotEmpty())
		<div id="map-results-popup">
			<a href="javascript:;" class="close-map">
				<img src="{{ url('img-trp/list.svg') }}" width="17"/>
				List
			</a>

			<div class="maps-results-mobile" style="position: relative;">
				<div id="search-map-mobile" lat="{{ $lat }}" lon="{{ $lon }}"></div>
			</div>

			<div class="mobile-map-results">
				<div class="mobile-map-inner">
					@foreach($items as $dentist)
						@include('trp.parts.search-dentist', [
							'forMap' => true,
							'for_branch' => false,
						])
					@endforeach
				</div>
			</div>
		</div>
	@endif

@endsection