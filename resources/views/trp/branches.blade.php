@extends('trp')

@section('content')

	<div class="search-results-title">
		<div class="container">			
			<h1 class="mont">{{ !empty($user) && $user->id == $clinic->id ? trans('trp.page.branches.title-logged') : trans('trp.page.branches.title', [
				'clinic_name' => strtoupper($clinic->getNames())
			])  }}</h1>
		</div>
	</div>

	<form method="get" class="filters-section search-get-form" action="">
		<div class="container-filters flex flex-mobile flex-center space-between">
			<div class="filters-wrapper">
				<div class="hidden-mobile-filters">
					<a href="javascript:;" class="close-filter">
						<img src="{{ url('img-trp/blue-arrow-left.png') }}"/>
					</a>
					<h3>
						<img src="{{ url('img-trp/filters-blue.svg') }}"/>{{ trans('trp.common.filters') }}
					</h3>
				</div>
				@include('trp.parts.search-dentist-filters', [
					'for_branch' => true
				])
			</div>
			<div class="mobile-filters-wrapper {{ $items->isEmpty() ? 'smaller' : '' }}">
				<a href="javascript:;" class="open-filters">
					<img src="{{ url('img-trp/filters.svg') }}" width="20" height="15"/>{{ trans('trp.common.filters') }}
				</a>
				@if($items->isNotEmpty())
					<a href="javascript:;" class="open-map">
						<img src="{{ url('img-trp/map.svg') }}"/>{{ trans('trp.common.map') }}
					</a>
				@endif
			</div>
		</div>
	</form>

	<div class="results-wrapper results flex">
		<div class="col dentist-results">

			@if(!empty($user) && $user->id == $clinic->id)
				<a href="javascript:;" data-popup-logged="popup-branch" class="green-button add-branch">
					{{ trans('trp.page.branches.add-branch') }}
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
				<div id="search-map" lat="30" lon="0"></div>
			</div>
		@endif
	</div>

	@if($items->isNotEmpty())
		<div id="map-results-popup">
			<a href="javascript:;" class="close-map">
				<img src="{{ url('img-trp/list.svg') }}" width="17"/>
				{{ trans('trp.common.list') }}
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