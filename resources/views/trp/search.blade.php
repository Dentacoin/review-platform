@extends('trp')

@section('content')

	<div class="search-results-title">
		<div class="container">
			<h1 class="mont">{!! $search_title !!}</h1>
		</div>
	</div>
	
	<form class="filters-section search-get-form" method="get" no-specializations-href="{{ getLangUrl(str_replace(' ', '-', explode('/', $query)[0])) }}" base-href="{{ getLangUrl(str_replace(' ', '-', $query)) }}" action="">
		<div class="container flex flex-mobile flex-center space-between">
			<div class="filters-wrapper">
				<span href="javascript:;" class="filter">
					Speciality
					<div class="caret-down"></div>
			
					<div class="filter-options">
						@foreach( config('categories') as $cat_id => $cat )
							@php
								$active = !empty($searchCategories) && in_array($cat, $searchCategories) ? true : false;
							@endphp
							<label 
								class="checkbox-label{!! $active ? ' active' : '' !!}" 
								for="checkbox-filter-{{ $cat }}"
							>
								<div class="flex flex-mobile flex-center space-between">
									<div>
										<input type="checkbox" 
											class="special-checkbox specializations"
											id="checkbox-filter-{{ $cat }}" 
											value="{{ $cat }}" 
											{!! $active ? 'checked="checked"' : '' !!}
										>
										<div class="checkbox-square">✓</div>
										{{ trans('trp.categories.'.$cat) }}

										@if(in_array($cat, ['orthodontists', 'periodontists', 'pediatric-dentists', 'endodontists']))
											<div class="specialization-info">
												<img class="" src="{{ url('img-trp/info-gray.svg') }}"/>

												@if($cat == 'orthodontists')
													<p class="info-tooltip">Specialized in the prevention, diagnosis, and correction of mal-positioned teeth and misaligned bites.</p>
												@elseif($cat == 'periodontists')
													<p class="info-tooltip">Specialized in the prevention, diagnosis, and treatment of gum disease and the placement of dental implants.</p>
												@elseif($cat == 'pediatric-dentists')
													<p class="info-tooltip short">Specialized in the prevention, diagnosis, and treatment of children’s dental issues.</p>
												@elseif($cat == 'endodontists')
													<p class="info-tooltip">Specialized in the prevention, diagnosis and treatment of diseases related to the pulp / root canal.</p>
												@endif
											</div>
										@endif
									</div>
									<span class="filter-count">
										({{ isset($dentistSpecialications[$cat_id]) ? $dentistSpecialications[$cat_id] : 0 }})
									</span>
								</div>
							</label>
						@endforeach

						<div class="filter-buttons">
							<a class="clear-filters" href="javascript:;">
								{{-- {!! nl2br(trans('trp.page.search.reset')) !!} --}}
								Clear
							</a>
							<button type="submit" href="javascript:;" class="blue-button">
								{!! nl2br(trans('trp.page.search.apply')) !!}
							</button>
						</div>
					</div>
				</span>
				<span href="javascript:;" class="filter active">
					Type
					<div class="caret-down"></div>
			
					<div class="filter-options">
						@foreach($types as $key => $type)
							@php
								$active = empty($requestTypes) && $key == 'all' ? true : (!empty($requestTypes) && in_array($key, $requestTypes) ? true : false);
							@endphp
							<label class="checkbox-label {{ $active ? 'active' : '' }}" for="filter-dentists-{{$key}}">

								<div class="flex flex-mobile flex-center space-between">
									<div>
										<input 
											type="checkbox" 
											class="special-checkbox" 
											name="types[]" 
											id="filter-dentists-{{$key}}" 
											value="{{$key}}" 
											{!! $active ? 'checked="checked"' : '' !!}
										>
										<div class="checkbox-square">✓</div>
										{{$type}}
									</div>
									<span class="filter-count">
										({{ isset($dentistTypes[$key]) ? $dentistTypes[$key] : 0 }})
									</span>
								</div>
							</label>
						@endforeach

						<div class="filter-buttons">
							<a class="clear-filters" href="javascript:;">
								{{-- {!! nl2br(trans('trp.page.search.reset')) !!} --}}
								Clear
							</a>
							<button type="submit" href="javascript:;" class="blue-button">
								{!! nl2br(trans('trp.page.search.apply')) !!}
							</button>
						</div>
					</div>
				</span>
				{{-- <span href="javascript:;" class="filter">
					Insurance
				</a> --}}
				<span href="javascript:;" class="filter {{ !empty($requestRatings) ? 'active' : '' }}">
					Rating
					<div class="caret-down"></div>
			
					<div class="filter-options">

						@foreach($ratings as $key => $rating)
							@php
								$active = !empty($requestRatings) && in_array($key, $requestRatings) ? true : false;
							@endphp
							<label class="checkbox-label {{ $active ? 'active' : '' }}" for="ratings-{{$key}}">

								<div class="flex flex-mobile flex-center space-between">
									<div>
										<input 
											type="checkbox" 
											class="special-checkbox" 
											name="ratings[]" 
											id="ratings-{{$key}}" 
											value="{{$key}}"
											{!! $active ? 'checked="checked"' : '' !!}
										>
										<div class="checkbox-square">✓</div>
										{{$rating}}
									</div>
									<span class="filter-count">
										({{ isset($dentistRatings[$key]) ? $dentistRatings[$key] : 0 }})
									</span>
								</div>
							</label>
						@endforeach

						<div class="filter-buttons">
							<a class="clear-filters" href="javascript:;">
								{{-- {!! nl2br(trans('trp.page.search.reset')) !!} --}}
								Clear
							</a>
							<button type="submit" href="javascript:;" class="blue-button">
								{!! nl2br(trans('trp.page.search.apply')) !!}
							</button>
						</div>
					</div>
				</span>
				<span href="javascript:;" class="filter {{ !empty($requestAvailability) ? 'active' : '' }}">
					More filters
					<div class="caret-down"></div>
			
					<div class="filter-options longer">
						<div class="filter-inner">
							<div class="filter-title">
								Appointment types:
							</div>
							<label class="checkbox-label disabled" for="virtual-visit">
								<input type="checkbox" class="special-checkbox" name="visits[]" id="virtual-visit" value="virtual" disabled="disabled">
								<div class="checkbox-square">✓</div>
								Virtual 
							</label>
							<label class="checkbox-label disabled" for="on--visit">
								<input type="checkbox" class="special-checkbox" name="visits[]" id="on--visit" value="on-site" disabled="disabled">
								<div class="checkbox-square">✓</div>
								On-site visit
							</label>
							<div class="filter-title">
								Languages spoken:
							</div>
							@foreach($languages as $key => $l)
								<label class="checkbox-label disabled" for="lang-{{$key}}">
									<input type="checkbox" class="special-checkbox" name="languages[]" id="lang-{{$key}}" value="{{$key}}" disabled="disabled">
									<div class="checkbox-square">✓</div>
									{{$l}}
								</label>
							@endforeach
							<div class="filter-title">
								Experience:
							</div>
							@foreach($experiences as $key => $experience)
								<label class="checkbox-label disabled" for="experience-{{$key}}">
									<input type="checkbox" class="special-checkbox" name="experience[]" id="experience-{{$key}}" value="{{$key}}" disabled="disabled">
									<div class="checkbox-square">✓</div>
									{{$experience}}
								</label>
							@endforeach
							<div class="filter-title">
								Availability:
							</div>
							@foreach($availabilities as $key => $availability)
								@php
									$active = !empty($requestAvailability) && in_array($key, $requestAvailability) ? true : false;
								@endphp
								<label class="checkbox-label {!! $active ? 'active' : '' !!}" for="availability-{{$key}}">
									<div class="flex flex-mobile flex-center space-between">
										<div>
											<input 
												type="checkbox" 
												class="special-checkbox" 
												name="availability[]" 
												id="availability-{{$key}}" 
												value="{{$key}}" 
												{!! $active ? 'checked="checked"' : '' !!}
											>
											<div class="checkbox-square">✓</div>
											{{$availability}}
										</div>
										<span class="filter-count">
											({{ isset($dentistAvailability[$key]) ? $dentistAvailability[$key] : 0 }})
										</span>
									</div>
								</label>
							@endforeach
						</div>
						<div class="filter-buttons">
							<a class="clear-filters" href="javascript:;">
								{{-- {!! nl2br(trans('trp.page.search.reset')) !!} --}}
								Clear
							</a>
							<button type="submit" href="javascript:;" class="blue-button">
								{!! nl2br(trans('trp.page.search.apply')) !!}
							</button>
						</div>
					</div>

				</span>
				<span class="sort-by-title">
					Sort by:
				</span>
				<span href="javascript:;" class="filter active">
					<span class="filter-order-active-text">{{ empty($requestOrder) ? 'Stars (highest first)' : $orders[$requestOrder] }}</span>
					<div class="caret-down"></div>
			
					<div class="filter-options">
						@foreach($orders as $key => $order)
							@php
								$active = empty($requestOrder) && $key == 'avg_rating_desc' ? true : ($requestOrder == $key ? true : false);
							@endphp

							<label class="checkbox-label {!! $active ? 'active' : '' !!}" for="order-{{$key}}" label-text="{{$order}}">
								<input 
									type="radio" 
									class="special-checkbox filter-order" 
									name="order" 
									id="order-{{$key}}" 
									value="{{$key}}"
									{!! $active ? 'checked="checked"' : '' !!}
								>
								<div class="checkbox-square">✓</div>
								{{$order}}
							</label>
						@endforeach

						<div class="filter-buttons">
							<a class="clear-filters" href="javascript:;">
								{{-- {!! nl2br(trans('trp.page.search.reset')) !!} --}}
								Clear
							</a>
							<button type="submit" href="javascript:;" class="blue-button">
								{!! nl2br(trans('trp.page.search.apply')) !!}
							</button>
						</div>
					</div>
				</span>
			</div>
			<div class="results-count">
				<h4>({{ $items->count() }}) results found</h4>
			</div>
			<div class="mobile-filters-wrapper">
				<a href="javascript:;" class="open-filters"><img src="{{ url('img-trp/filters.svg') }}" />Filters</a>
				<a href="javascript:;" class="open-map"><img src="{{ url('img-trp/map.svg') }}" />Map</a>
			</div>
		</div>
	</form>

	<div class="results-wrapper results flex">
		<div class="col dentist-results">

			@if($items->isNotEmpty())
				@foreach($items as $dentist)
					<a 
						class="result-container dentist flex" 
						href="{{ $dentist->getLink() }}" 
						{!! $dentist->address ? 'lat="'.$dentist->lat.'" lon="'.$dentist->lon.'"' : '' !!} 
						dentist-id="{{ $dentist->id }}"
					>
						<div class="dentist-image-wrapper">
							<div class="avatar{!! $dentist->hasimage ? '' : ' default-avatar' !!}" style="background-image: url('{{ $dentist->getImageUrl(true) }}')">
								@if($dentist->hasimage)
									<img 
										src="{{ $dentist->getImageUrl(true) }}" 
										alt="{{ trans('trp.alt-tags.reviews-for', [ 
											'name' => $dentist->getNames(), 
											'location' => $dentist->getLocation() 
										]) }}"
										style="display: none !important;"/> 
								@endif
							</div>
							@if($dentist->is_partner)
								<div class="partner">
									<img 
										class="tooltip-text" 
										src="{{ url('img-trp/mini-logo-white.svg') }}" 
										text="{!! nl2br(trans('trp.common.partner')) !!} 
										{{ $dentist->is_clinic ? trans('trp.page.user.clinic') : trans('trp.page.user.dentist') }}"
									/>
									DCN Accepted
								</div>
							@endif
							<div class="ratings">
								<div class="stars">
									<div class="bar" style="width: {{ $dentist->avg_rating/5*100 }}%;">
									</div>
								</div>
								<span class="rating">
									({{ trans('trp.common.reviews-count', [ 'count' => intval($dentist->ratings)]) }})
								</span>
							</div>
						</div>

						<div class="dentist-info">
							<div class="dentist-title">
								<h4>
									{{ $dentist->getNames() }}
								</h4>
								@if( $time = $dentist->getWorkHoursText() )
									@if(mb_strpos('Open now', $time) === 0)
										<div class="working-time open">
											<img src="{{ url('img-trp/clock-blue.svg') }}">
											Open now
										</div>
									@else
										<div class="working-time closed">
											<img src="{{ url('img-trp/clock-red.svg') }}">
											Closed now
										</div>
									@endif
								@endif
							</div>
							<p>{{ $dentist->city_name }}, {{ $dentist->country->name }}</p>
							<p>{{ $dentist->address }}</p>
							<p>{{ $dentist->phone }}</p>
							<p href="{{ $dentist->website }}?popup-loged=submit-review-popup" target="_blank" class="text-link">{{ $dentist->website }}</p>

							@if( $dentist->socials )
								<div class="socials">
									@foreach($dentist->socials as $k => $v)
										<span class="social" href="{{ $v }}" target="_blank">
											<img src="{{ url('img-trp/social-network/'.$k.'.svg') }}" height="24"/>
										</span>
									@endforeach
								</div>
							@endif

							@if(!empty($user) && $user->is_dentist)
								<div href="{{ $dentist->getLink() }}" class="button-submit">
									{{-- {!! nl2br(trans('trp.common.see-profile')) !!} --}}
									Check Profile
								</div>
							@else
								<div href="{{ $dentist->getLink() }}?popup-loged=submit-review-popup" class="button-submit">
									{{-- {!! nl2br(trans('trp.common.submit-review')) !!} --}}
									Write a review
								</div>
							@endif
						</div>
					</a>
				@endforeach
			@else
				<div class="alert alert-info">
					{!! nl2br(trans('trp.page.search.no-results')) !!}
				</div>
			@endif
		</div>
		<div class="col maps-results">
			<div id="search-map" lat="{{ $lat }}" lon="{{ $lon }}"></div>
		</div>

		<a id="map-mobile-tooltip" class="result-container"></a>
	</div>

@endsection