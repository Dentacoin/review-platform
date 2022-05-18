@extends('trp')

@section('content')

	<div class="search-results-title">
		<div class="container">
			{{-- trans('trp.page.branches.title') --}}
			<h1 class="mont">{{ !empty($user) && $user->id == $clinic->id ? 'Manage your branch offices' : 'Check all branches of '.strtoupper($clinic->getNames()) }}</h1>
		</div>
	</div>

	<form 
		method="get" 
		class="filters-section search-get-form" 
		action=""
	>
		<div class="container-filters flex flex-mobile flex-center space-between">
			<div class="filters-wrapper">
				<div class="hidden-mobile-filters">
					<a href="javascript:;" class="close-filter">
						<img src="{{ url('img-trp/blue-arrow-left.png') }}"/>
					</a>
					<h3>
						<img src="{{ url('img-trp/filters-blue.svg') }}"/>Filters
					</h3>
				</div>
				@include('trp.parts.search-dentist-filters', [
					'for_branch' => true
				])
			</div>
			<div class="mobile-filters-wrapper {{ $items->isEmpty() ? 'smaller' : '' }}">
				<a href="javascript:;" class="open-filters">
					<img src="{{ url('img-trp/filters.svg') }}"/>Filters
				</a>
				@if($items->isNotEmpty())
					<a href="javascript:;" class="open-map">
						<img src="{{ url('img-trp/map.svg') }}"/>Map
					</a>
				@endif
			</div>
		</div>
	</form>

	<div class="results-wrapper results flex">
		<div class="col dentist-results">

			@if(!empty($user) && $user->id == $clinic->id)
				<a href="javascript:;" data-popup-logged="popup-branch" class="green-button add-branch tooltip-text" text="Comming soon">
					+ Add new branch
				</a>
				{!! csrf_field() !!}
			@endif

			@if($items->isNotEmpty())
				@foreach($items as $dentist)
					@include('trp.parts.search-dentist', [
						'forMap' => false,
						'dentist' => $dentist,
						'for_branch' => true,
						'clinic' => $clinic,
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
		<div class="col maps-results">
			<div id="search-map" lat="30" lon="0"></div>
		</div>
	</div>

	@if($items->isNotEmpty())
		<div id="map-results-popup">
			<a href="javascript:;" class="close-map">
				<img src="{{ url('img-trp/list.svg') }}" width="17"/>
				List
			</a>

			<div class="maps-results-mobile" style="position: relative;">
				<div id="search-map-mobile" lat="30" lon="0"></div>
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

	@if(!empty($user) && $user->id == $clinic->id)
		@include('trp.popups.add-branch')
	@endif

@endsection