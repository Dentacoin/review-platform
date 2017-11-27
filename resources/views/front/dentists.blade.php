@extends('front')

@section('content')
@if(!$is_ajax)
<div class="container">
	<div class="col-md-3">

		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">
					{{ trans('front.page.'.$current_page.'.search') }}
				</h3>
			</div>
			<div class="panel-body">
				{!! Form::open(array('url' => getLangUrl('dentists'), 'method' => 'get', 'class' => 'form-horizontal')) !!}
					<div class="form-group">
						<div class="md-12">
							<label for="name">
								{{ trans('front.page.'.$current_page.'.name') }}
							</label>
							{{ Form::text( 'name' , $name ? $name : null , array('class' => 'form-control', 'id' => 'name') ) }}
						</div>
					</div>
					<div class="form-group">
						<div class="md-12">
							<label for="category">
								{{ trans('front.page.'.$current_page.'.category') }}
							</label>
							{{ Form::select( 'category' , ['' => 'Any dentist'] + $categories , $category ? $category : null , array('class' => 'form-control') ) }}
						</div>
					</div>
					<div class="form-group">
						<div class="md-12">
							<label for="country">
								Location
							</label>
							<div class="location">
								{{ Form::text( 'location' , $placeholder , array('class' => 'form-control location-input', 'autocomplete' => 'off', ($all_locations ? 'disabled' : 'something') => 'disabled' ) ) }}
								{{ Form::hidden( 'country' , !empty($country) ? $country->id : null, ['class' => 'country_id']  ) }}
								{{ Form::hidden( 'city' , !empty($city) ? $city->id : null, ['class' => 'city_id']  ) }}
								<div class="location-suggester">
									<div class="loader">
										<i class="fa fa fa-circle-o-notch fa-spin fa-2x fa-fw">
										</i>
									</div>
									<div class="results">
									</div>
								</div>
							</div>
							<label for="all-locations">
								{{ Form::checkbox( 'all_locations' , 1, $all_locations, ['id' => 'all-locations'] ) }}
								Show dentists from all locations								
							</label>
						</div>
					</div>
					<div class="form-group">
						<div class="md-12">
							<label for="partner">
								{{ Form::checkbox( 'partner' , 1, $partner, ['id' => 'partner'] ) }}
								{{ trans('front.page.'.$current_page.'.partners-only') }}
							</label>
						</div>
					</div>
					<div class="form-group">
						<div class="md-12">
							{{ Form::submit( trans('front.page.'.$current_page.'.submit'), ['class' => 'btn btn-primary btn-block'] ) }}
						</div>
					</div>
				{!! Form::close() !!}
			</div>
			<div class="panel-heading subheading">
				<h3 class="panel-title">
					{{ trans('front.page.'.$current_page.'.order') }}
				</h3>
			</div>
			<div class="panel-body">
				<ul class="nav nav-pills nav-stacked order-pills">
					@foreach($orders as $orderval)
						<li role="presentation" {!! $order==$orderval ? 'class="active"' : '' !!} >
							<a href="{{ getLangUrl('dentists') }}?category={{ $category }}&city={{ $city ? $city->id : '' }}&country={{ $country ? $country->id : '' }}&order={{ $orderval }}&partner={{ $partner }}&name={{ urlencode($name) }}" data-val="{{ $orderval }}">{{ trans('front.page.'.$current_page.'.order-'.$orderval) }}</a>
						</li>
					@endforeach
				</ul>
			</div>
			@if( $top_cities->isNotEmpty() )
				<div class="panel-heading subheading hidden-sm hidden-xs">
					<h3 class="panel-title">
						@if($country)
							{{ trans('front.page.'.$current_page.'.best-cities-in', [ 'country' => $country->name ]) }}
						@else
							{{ trans('front.page.'.$current_page.'.best-cities') }}
						@endif
					</h3>
				</div>
				<div class="panel-body hidden-sm hidden-xs">
					<ul>
						@foreach($top_cities as $place)
							<li {!! $loop->iteration>3 ? 'class="hidden"' : '' !!} >
								<a href="{{ getLangUrl('dentists') }}?country={{ $place->country_id }}&city={{ $place->id }}">
									{{ $place->name }} {{ $country ? '' : ', '.$place->country->name }} ( {{ number_format($place->avg_rating, 2) }} )
								</a>
							</li>
							@if($loop->iteration==3 && !$loop->last)
							<li>
								<a class="search-more" href="javascript:;">
									{{ trans('front.page.'.$current_page.'.show-more') }}
								</a>
							</li>
							@endif
						@endforeach
					</ul>
				</div>
			@endif
			<div class="panel-heading subheading hidden-sm hidden-xs">
				<h3 class="panel-title">
					{{ trans('front.page.'.$current_page.'.best-countries') }}
				</h3>
			</div>
			<div class="panel-body hidden-sm hidden-xs">
				<ul>
					@foreach($top_countries as $place)
						<li {!! $loop->iteration>3 ? 'class="hidden"' : '' !!} >
							<a href="{{ getLangUrl('dentists') }}?country={{ $place->id }}">
								{{ $place->name }} ( {{ number_format($place->avg_rating, 2) }} )
							</a>
						</li>
						@if($loop->iteration==3 && !$loop->last)
						<li>
							<a class="search-more" href="javascript:;">
								{{ trans('front.page.'.$current_page.'.show-more') }}
							</a>
						</li>
						@endif
					@endforeach
				</ul>
			</div>
		</div>

	</div>
	<div class="col-md-9" id="dentists-list">

		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">
					{{ trans('front.page.search.title', [ 'dentists' => $category ? $categories[$category] : 'Dentists', 'location' => $search_location ]) }}
				</h3>
			</div>
			<div class="panel-body main-panel-body">
@endif
		@if($items->isEmpty() && !$is_ajax)
			<div class="alert alert-info">
				{!! trans('front.page.'.$current_page.'.no-results', [ 'link' => '<a href="'.getLangUrl('add').'">', 'endlink' => '</a>' ]) !!}
			</div>
		@elseif( $items->isNotEmpty() )
			@foreach($items as $item)
				<div class="panel panel-default">
						 <div class="panel-body dentist" >
						<a href="{{ $item->getLink() }}">
							<div class="media">
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


									@if($item->categories->isNotEmpty())
										<div class="categories">
											<i class="fa fa-graduation-cap"></i> 
											{{ implode(', ', $item->parseCategories($categories) ) }}
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

									<div class="ratings">
										<div class="rating">
											@if($item->ratings)
												{!! trans('front.page.'.$current_page.'.rating', [ 'rating' => '<b>'.$item->avg_rating.'</b>', 'reviews' => '<b>'.$item->ratings.'</b>' ] ) !!}
											@else
												<b>{{ trans('front.common.no-reviews') }}</b>
											@endif
										</div>
										@if($item->ratings)
											<div class="stars">
												<div class="bar" style="width: {{ getStarWidth($item->avg_rating) }}px;">
												</div>
											</div>
										@endif
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

		</div>
		<div id="loading" class="alert alert-info" style="display: none;">
			{{ trans('front.common.loading') }}
		</div>
		<div id="end-page" class="alert alert-info" {!! $items->count() < $ppp && $items->isNotEmpty() ? '' : 'style="display: none;"' !!} >
			{!! trans('front.common.no-more', [
				'link' => '<a href="'.getLangUrl('add').'">',
				'endlink' => '</a>',
			]) !!}
		</div>
	</div>
</div>

<script type="text/javascript">
	var page_num = {{ $page_num }};
	var end = {{ $items->count() < $ppp ? 'true' : 'false' }};
</script>



@endif
@endsection