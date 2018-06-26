@extends('front')

@section('content')


@if(!$is_ajax)

<div class="search-wrapper">
	<h1 class="main-title">{{ trans('front.page.'.$current_page.'.find-dentist') }}</h1>

	<div class="container">
		<div class="col-md-8 col-md-offset-2">
			<div class="search-box-before">
				<div class="search-box">
					<a href="javascript:;" class="search-img open-search-box">
						<img src="{{ url('/img/socials/search.png') }}">
					</a>
					<h2>
						@if( $search_location)
							@if($type=='clinic')
								{{ trans('front.page.search.title-clinic', [ 'location' => $search_location ]) }}
							@elseif($type=='dentist')
								{{ trans('front.page.search.title-dentist', [ 'location' => $search_location ]) }}
							@else
								{{ trans('front.page.search.title-all', [ 'location' => $search_location ]) }}
							@endif
						@else
							{{ trans('front.page.search.title-dentists-and-clinics') }}
						@endif
					</h2>
				</div>
			</div>

			<div class="search-box-after">
				<div class="search-box clearfix">
					<a href="javascript:;" class="search-img close-search-box">
						<img src="{{ url('/img/socials/x.png') }}">
					</a>
					{!! Form::open(array('url' => getLangUrl('dentists'), 'method' => 'get', 'class' => 'form-horizontal', 'id' => 'search-form')) !!}
						<div class="form-group col-md-6 col-sm-6">
							<div class="md-12 username-search">
								<label for="name">
									{{ trans('front.page.'.$current_page.'.name') }}
								</label>
								<div class="location">
									{{ Form::text( 'username' , null , array('class' => 'form-control user-input', 'autocomplete' => 'off') ) }}
									<div class="location-suggester">
										<div class="loader">
											<i class="fa fa fa-circle-o-notch fa-spin fa-2x fa-fw">
											</i>
										</div>
										<div class="results">
										</div>
									</div>
								</div>
								
							</div>
						</div>
						<div class="form-group col-md-6 col-sm-6">
							<div class="md-12">
								<label for="type">
									{{ trans('front.page.'.$current_page.'.type') }}
								</label>
								{{ Form::select( 'type' , ['' => 'All types'] + $types ,$type , array('class' => 'form-control', 'id' => 'search_type') ) }}
							</div>
						</div>
						<div class="form-group col-md-6 col-sm-6">
							<div class="md-12">
								<label for="country">
									{{ trans('front.page.'.$current_page.'.location') }}
								</label>
								<div class="location">
									<input type="text" name="location" value="{{ $placeholder }}" class="form-control location-input" autocomplete="off" {!! $all_locations ? 'disabled="disabled"' : '' !!} >
									{{ Form::hidden( 'city' , !empty($city) ? $city->id : null, ['class' => 'city_id']  ) }}
									{{ Form::hidden( 'country' , !empty($country) ? $country->id : null, ['class' => 'country_id']  ) }}
									<div class="location-suggester">
										<div class="loader">
											<i class="fa fa fa-circle-o-notch fa-spin fa-2x fa-fw">
											</i>
										</div>
										<div class="results">
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="form-group col-md-6 col-sm-6">
							<div class="md-12">
								<label for="category">
									{{ trans('front.page.'.$current_page.'.category') }}
								</label>
								{{ Form::select( 'category' , ['' => 'Any dentist'] + $categories , $category ? $category : null , array('class' => 'form-control') ) }}
							</div>
						</div>
						<div class="form-group col-md-12 col-sm-12">
							<div class="md-12">
								<label for="all-locations">
									{{ Form::checkbox( 'all_locations' , 1, $all_locations, ['id' => 'all-locations'] ) }}
									{{ trans('front.page.'.$current_page.'.dentists-all-locations') }}
								</label>
								<label for="partner">
									{{ Form::checkbox( 'partner' , 1, $partner, ['id' => 'partner'] ) }}
									{{ trans('front.page.'.$current_page.'.partners-only') }}
								</label>
							</div>
						</div>
						<div class="form-group col-md-12 col-sm-12">
							<div class="md-12">
								{{ Form::submit( trans('front.page.'.$current_page.'.submit'), ['class' => 'btn btn-primary btn-block'] ) }}
							</div>
						</div>
					{!! Form::close() !!}
				</div>
				<h2>{{ trans('front.page.'.$current_page.'.suggestions') }}</h2>
			</div>
		</div>
	</div>
	
</div>

<div class="sort-wrapper clearfix">
	<div class="col-md-5 col-sm-5 left-column">
		<span>{{ trans('front.page.'.$current_page.'.sort-by') }}:</span>
		@foreach($orders as $orderval)
			<a {!! $order==$orderval ? 'class="active"' : '' !!} href="{{ getLangUrl('dentists') }}?category={{ $category }}&city={{ $city ? $city->id : '' }}&country={{ $country ? $country->id : '' }}&order={{ $orderval }}&partner={{ $partner }}&name={{ urlencode($name) }}" data-val="{{ $orderval }}">{{ trans('front.page.'.$current_page.'.order-'.$orderval) }}</a>
		@endforeach
	</div>
	<div class="col-md-7 col-sm-7 right-column">
		<ul>
			<li class="dropdown" >
	            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
	            	<i class="fa fa-caret-down"></i>{{ trans('front.page.search.location') }}
	            </a>
	            <ul class="dropdown-menu location-dropdown">
		        	@foreach($countries as $single_country)
						<li>
							<a href="javascript:;" data-country-id="{{ $single_country->id }}" {!! $country && $country->id==$single_country->id ? 'class="active"' : '' !!} >
								{{ $single_country->name }}
							</a>
						</li>
					@endforeach
		        </ul>
	        </li>
	        @if($country)
		        <li class="dropdown" >
		            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
		            	<i class="fa fa-caret-down"></i>
						{{ trans('front.page.search.best-cities', [ 'location' => $search_location ]) }}
		            </a>
			        <ul class="dropdown-menu">
			        	@foreach($top_cities as $place)
							<li>
								<a href="{{ getLangUrl('dentists') }}?country={{ $place->country_id }}&city={{ $place->id }}">
									{{ $place->name }} {{ $country ? '' : ', '.$place->country->name }} ( {{ number_format($place->avg_rating, 2) }} )
								</a>
							</li>
						@endforeach
			        </ul>
		        </li>
		    @endif
	        <li class="dropdown" >
	            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
	            	<i class="fa fa-caret-down"></i>{{ trans('front.page.search.best-countries') }}
	            </a>
		        <ul class="dropdown-menu">
		        	@foreach($top_countries as $place)
						<li>
							<a href="{{ getLangUrl('dentists') }}?country={{ $place->id }}">
								{{ $place->name }} ( {{ number_format($place->avg_rating, 2) }} )
							</a>
						</li>
					@endforeach
		        </ul>
	        </li>
	    </ul>
	</div>
</div>

<div class="switchers clearfix">
	<div class="col-md-12">
		<label class="switch">
			<input type="checkbox" class="type-toggle type-toggle-dentist" {!! $type=='dentist' || empty($type) ? 'checked="checked"' : '' !!}>
			<span class="slider round"></span>
		</label>
		<span class="switch-text">{{ trans('front.page.search.show-dentists') }}</span>
		<label class="switch">
			<input type="checkbox" class="type-toggle type-toggle-clinic" {!! $type=='clinic' || empty($type) ? 'checked="checked"' : '' !!}>
			<span class="slider round"></span>
		</label>
		<span class="switch-text">{{ trans('front.page.search.show-clinics') }}</span>
	</div>
</div>

<div class="container">
	<div class="col-md-8 col-md-offset-2" id="dentists-list">

		<div class="list-wrapper">
			<!-- <div class="panel-heading">
				<h3 class="panel-title">
					{{ trans('front.page.search.title', [ 'dentists' => $category ? $categories[$category] : 'Dentists', 'location' => $search_location ]) }}
				</h3>
			</div> -->
@endif
		@if($items->isEmpty() && !$is_ajax)
			<div class="alert alert-info">
				{!! trans('front.page.'.$current_page.'.no-results') !!}
			</div>
		@elseif( $items->isNotEmpty() )
			@foreach($items as $item)
				<div class="outher-border">
					<div class="inner-border dentist">
						<a href="{{ $item->getLink() }}">
							<div class="media">
								<div class="type">
									<h4 style="color: #{{ $item->is_clinic ? '2c9385' : '126585' }}">
										@if($item->is_clinic)
											{{ trans('front.common.clinic') }}
										@else
											{{ trans('front.common.dentist') }}
										@endif
									</h4>
								</div>
								<div class="media-left avatar">
									<img src="{{ $item->getImageUrl(true) }}" />
								</div>
								<div class="media-body">
									<h2 class="media-heading">
										{{ $item->name }}
									</h2>
									@if($item->is_partner)
										<div class="label label-success partner">
											{{ trans('front.common.partner') }}
										</div>
									@endif

									@if($item->country)
										<div class="location">
											<i class="fa fa-map-marker"></i> 
											@if($item->city)
												{{ $item->city->name }}, {{ $item->country->name }}
											@else
												{{ $item->country->name }}
											@endif
										</div>
									@endif

									@if($item->categories->isNotEmpty())
										<div class="categories">
											<i class="fa fa-graduation-cap"></i> 
											{{ implode(', ', $item->parseCategories($categories) ) }}
										</div>
									@endif

									<div class="ratings">
										@if($item->ratings)
											<div class="stars {{ $item->is_clinic ? 'green-stars' : '' }}">
												<div class="bar" style="width: {{ getStarWidth($item->avg_rating) }}px;">
												</div>
											</div>
										@endif
										<div class="rating">
											@if($item->ratings)
												{!! trans('front.page.'.$current_page.'.rating', [ 'rating' => '<b>'.$item->avg_rating.'</b>', 'reviews' => '<b>'.$item->ratings.'</b>' ] ) !!}
											@else
												<b>{{ trans('front.common.no-reviews') }}</b>
											@endif
										</div>
									</div>

								</div>
							</div>
						</a>
					</div>
				</div>
			@endforeach
		@endif
@if(!$is_ajax)

		</div>
		<div id="loading" class="alert alert-info" style="display: none;">
			{{ trans('front.common.loading') }}
		</div>
		<div id="end-page" class="alert alert-info" {!! $items->count() < $ppp && $items->isNotEmpty() ? '' : 'style="display: none;"' !!} >
			{!! trans('front.common.no-more') !!}
		</div>
	</div>
</div>

<script type="text/javascript">
	var page_num = {{ $page_num }};
	var end = {{ $items->count() < $ppp ? 'true' : 'false' }};
</script>



@endif
@endsection