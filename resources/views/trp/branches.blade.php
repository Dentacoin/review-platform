@extends('trp')

@section('content')

	{{-- <div class="black-overflow" style="display: none;"></div>
	<div class="search-gradient-wave green-gradient">
		<div class="main-top branch-top"></div>
	    <div class="container overflow-container">
	    	<h1 class="{{ !empty($user) ? '' : 'small-mt' }}">{{ !empty($user) && $user->id == $clinic->id ? trans('trp.page.branches.title') : $clinic->getNames()."'s Branches" }}</h1>
	    </div>
	</div>

    <div class="search-results-wrapper container">

		@include('trp.parts.search-dentist', [
			'dentist' => $clinic,
			'for_branch' => false,
			'main_clinic' => true,
		])

    	@foreach($items as $dentist)
    		@if(!empty($dentist))
				@include('trp.parts.search-dentist', [
					'dentist' => $dentist,
					'for_branch' => true,
					'clinic' => $clinic,
					'main_clinic' => false,
				])
			@endif
		@endforeach

		@if(!empty($user) && $user->id == $clinic->id)
			<a href="javascript:;" data-popup-logged="popup-branch" class="button add-branch"><img src="{{ url('img-trp/add-new-branch-white.svg') }}"/>{{ trans('trp.page.branches.add-branch') }}</a>
		@endif
		@if(!empty($user) && $user->id == $clinic->id)
			{!! csrf_field() !!}
		@endif
	</div>

	@if(!empty($user) && $user->id == $clinic->id)
		@include('trp.popups.add-branch')
	@endif --}}

	

	<div class="search-results-title">
		<div class="container">
			<h1 class="mont">Check all branches of {{ strtoupper($clinic->getNames()) }}</h1>
		</div>
	</div>

	<div class="results-wrapper results flex">
		<div class="col dentist-results">

			@include('trp.parts.search-dentist', [
				'dentist' => $clinic,
				'for_branch' => false,
				'main_clinic' => true,
				'forMap' => false,
			])
			
			@foreach($items as $dentist)
				@include('trp.parts.search-dentist', [
					'forMap' => false,
					'dentist' => $dentist,
					'for_branch' => true,
					'clinic' => $clinic,
					'main_clinic' => false,
				])
			@endforeach
		</div>
		<div class="col maps-results">
			<div id="search-map" lat="30" lon="0"></div>
		</div>
	</div>

@endsection