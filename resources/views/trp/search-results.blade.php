@extends('trp')

@section('content')

	<div class="search-results-title">
		<div class="container">
			<h1 class="mont">{!! config('trp.using_google_maps') ? $search_title : str_replace('-', ' ', $search_title) !!}</h1>

			@if($dentistsFromDifferentCountry)
				<h3>
					{{ trans('trp.search-results.results-from-other-countries', [
						'country' => urldecode($searchCity ? (ucwords($searchCity).', ') : '').urldecode($searchCountry)
					]) }}
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
				!empty($searchCategories) ? getLangUrl(str_replace(' ', '-', explode('/', $query)[0] )) : getLangUrl(str_replace(' ', '-', $query ))
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
						<img src="{{ url('img-trp/filters-blue.svg') }}" width="20" height="15"/>
						{{ trans('trp.common.filters') }}
					</h3>
				</div>
				@include('trp.parts.search-dentist-filters', [
					'for_branch' => false
				])
			</div>
			<div class="results-count">
				<h4 class="hidden-mobile">
					{{ trans('trp.common.results-count-desktop', [
						'count' => $items->count()
					]) }}
				</h4>
				<h4 class="show-mobile">
					{{ trans('trp.common.results-count', [
						'count' => $items->count()
					]) }}
				</h4>
			</div>
			<div class="mobile-filters-wrapper {{ $items->isEmpty() ? 'smaller' : '' }}">
				<a href="javascript:;" class="open-filters">
					<img src="{{ url('img-trp/filters.svg') }}" width="20" height="15"/>
					{{ trans('trp.common.filters') }}
				</a>
				@if($items->isNotEmpty() && config('trp.using_google_maps'))
					<a href="javascript:;" class="open-map">
						<img src="{{ url('img-trp/map.svg') }}"/>
						{{ trans('trp.common.map') }}
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
							{{ trans('trp.common.filters-no-results') }}
						</p>
						<a href="javascript:;" class="text-link clear-all-filters">
							{{ trans('trp.common.clear-filters') }}
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
		<input type="text" id="search-in-map" placeholder="{{ trans('trp.search-results.search-in-map') }}"/>
	</div>

	@if($items->isNotEmpty())
		<div id="map-results-popup">
			<a href="javascript:;" class="close-map">
				<img src="{{ url('img-trp/list.svg') }}" width="17"/>
				{{ trans('trp.common.list') }}
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