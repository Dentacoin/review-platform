@extends('trp')

@section('content')

	<div class="black-overflow" style="display: none;">
	</div>

	@if(($items->count() == 0 && !empty($user) && !$user->is_dentist) || (empty($user) && $items->count() == 0))
		<div class="green-gradient">
	@else
		<div class="search-gradient-wave green-gradient">
	@endif
		<div class="home-search-form">
		    @include('trp.parts.search-form')
		</div>

		<div class="main-top">
	    </div>

	    <div class="sort-wrapper">
	    	<div class="flex">
	    		<div class="col">
	    			{!! nl2br(trans('trp.page.search.results-count', [
	    				'count' => $items->count()
	    			])) !!}
	    			
	    		</div>
	    		<div class="col">
	    			<a href="javascript:;" class="sort-button" data-popup="sort-popup" >
	    				<img src="{{ url('img-trp/sort.png') }}">
						{!! nl2br(trans('trp.page.search.sort-filter')) !!}
	    				
	    			</a>
	    		</div>
	    	</div>
	    </div>

	    @if(!empty($search_title))
		    <div class="container overflow-container">
		    	<h1>{{ $search_title }}</h1>
		    </div>
		@endif

		@if(($items->count() == 0 && !empty($user) && !$user->is_dentist) || (empty($user) && $items->count() == 0))
		@else
			</div>
		@endif

	    <div class="search-results-wrapper container">

	    	@if($items->count() == 0)
	    		<div class="alert alert-info">
	    			{!! nl2br(trans('trp.page.search.no-results-dentist',[
	    				'link' => '<a href="'.getLangUrl('dentist-listings-by-country').'">',
	    				'endlink' => '</a>',
	    			])) !!}
	    		</div>
	    		<br/>
	    	@endif

	    	@if($items->isNotEmpty() && $staticImageUrl && $mode=='map')
	    		<a href="javascript:;" class="results-static-map" data-popup="map-results-popup">
		    		<img src="{{ $staticImageUrl }}" />
		    	</a>    		
	    	@endif

	    	@if(!empty($user) && $user->is_dentist && !$user->address)
	    		<div class="alert alert-info">
	    			{!! nl2br(trans('trp.page.search.no-address-dentist',[
	    				'link' => '<a href="'.$user->getLink().'?open-edit=1">',
	    				'endlink' => '</a>',
	    			])) !!}
	    		</div>
	    		<br/>
	    	@endif

	    	@foreach($items as $dentist)

				<a href="{{ $dentist->getLink() }}" class="result-container dentist clearfix" full-dentist-id="{{ $dentist->id }}">
					<div class="avatar{!! $dentist->hasimage ? '' : ' default-avatar' !!}"  style="background-image: url('{{ $dentist->getImageUrl(true) }}')">
						@if($dentist->hasimage)
							<img src="{{ $dentist->getImageUrl(true) }}" alt="{{ trans('trp.alt-tags.reviews-for', [ 'name' => $dentist->getName(), 'location' => ($dentist->city_name ? $dentist->city_name.', ' : '').($dentist->state_name ? $dentist->state_name.', ' : '').($dentist->country->name) ]) }}" style="display: none !important;"> 
						@endif
					</div>
					<div class="media-right">
						<h4>
							{{ $dentist->getName() }}
						</h4>
						@if($dentist->is_partner)
							<span class="type">
								<div class="img">
									<img src="{{ url('img-trp/mini-logo.png') }}">
								</div>
								<span>{!! nl2br(trans('trp.page.search.partner')) !!}</span> 
								{{ $dentist->is_clinic ? 'Clinic' : 'Dentist' }}
							</span>
						@endif
						<div class="p">
							<div class="img">
								<img src="{{ url('img-trp/map-pin.png') }}">
							</div>
							{{ $dentist->city_name ? $dentist->city_name.', ' : '' }}
							{{ $dentist->state_name ? $dentist->state_name.', ' : '' }} 
							{{ $dentist->country->name }} 
							<!-- <span>(2 km away)</span> -->
						</div>

				    	@if( $time = $dentist->getWorkHoursText() )
				    		<div class="p">
				    			<div class="img">
				    				<img src="{{ url('img-trp/open.png') }}">
				    			</div>
				    			{!! $time !!}
				    		</div>
				    	@endif
				    	@if( $dentist->website )
				    		<div class="p dentist-website" href="{{ $dentist->getWebsiteUrl() }}" target="_blank">
				    			<div class="img">
					    			<img class="black-filter" src="{{ url('img-trp/website-icon.svg') }}">
					    		</div>
					    		<span>
						    		{{ $dentist->website }}
						    	</span>
				    		</div>
				    	@endif
				    	@if($dentist->top_dentist_month)
							<div class="top-dentist">
								<img src="{{ url('img-trp/top-dentist.png') }}">
				    			<span>
				    				{!! trans('trp.common.top-dentist') !!}
			    				</span>
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
						@if(!empty($user) && $user->is_dentist)
							<div href="{{ $dentist->getLink() }}" class="button button-submit">
								{!! nl2br(trans('trp.common.see-profile')) !!}
							</div>
						@else
							<div href="{{ $dentist->getLink() }}?popup-loged=submit-review-popup" class="button button-submit">
								{!! nl2br(trans('trp.common.submit-review')) !!}
							</div>
						@endif
						<div class="share-button" data-popup="popup-share" share-href="{{ $dentist->getLink() }}">
							<img src="{{ url('img-trp/share.png') }}">
						</div>
					</div>
				</a>

			@endforeach

			<div class="pagination" style="display: none;">
				
			</div>
		</div>

	@if(($items->count() == 0 && !empty($user) && !$user->is_dentist) || (empty($user) && $items->count() == 0))
		</div>
	@endif

	@if($items->count() == 0)
		@if(!empty($user) && !$user->is_dentist )

			<div class="index-invite-dentist patient-invite">
				<div class="container">
					<img src="{{ url('img-trp/dentacoin-dentist-icon.png') }}" alt="{{ trans('trp.alt-tags.dentist-icon') }}">
				</div>
			</div>

			<div class="invite-new-dentist-wrapper white-invite">
				<div class="invite-new-dentist-titles">
					<h2>{!! nl2br(trans('trp.page.invite.title')) !!}</h2>
					<h3 class="gbb">{!! nl2br(trans('trp.page.invite.subtitle')) !!}</h3>
				</div>

				<div class="colorfull-wrapper">
					@include('trp.parts.invite-new-dentist-form')
				</div>
			</div>
		@elseif(empty($user))
			<div class="index-invite-dentist">
				<div class="container">
					<div class="flex flex-mobile">
						<div class="col">
							<img src="{{ url('img-trp/dentacoin-dentist-icon.png') }}" alt="{{ trans('trp.alt-tags.dentist-icon') }}">
						</div>
						<div class="col">
							<h2>{!! nl2br(trans('trp.page.invite.title')) !!}</h2>
							<h3>{!! nl2br(trans('trp.page.invite.subtitle')) !!}</h3>
							<a href="javascript:;" class="button button-yellow button-sign-up-patient button-want-to-add-dentist open-dentacoin-gateway patient-register">{!! nl2br(trans('trp.page.invite.add-dentist')) !!}</a>
						</div>
					</div>
				</div>
			</div>
		@endif
	@endif


	<div class="popup fixed-popup results-popup" id="sort-popup">
		<div class="popup-inner inner-white">
			<div class="popup-pc-buttons">
				<a href="javascript:;" class="close-popup"><i class="fas fa-times"></i></a>
			</div>

			<div class="popup-mobile-buttons">
				<a href="javascript:;" class="close-popup">< back</a>
			</div>
			<h2><img src="{{ url('img-trp/sort-gray.png') }}">
				{!! nl2br(trans('trp.page.search.sort-filter')) !!}
			</h2>

			<form class="search-get-form" method="get" base-href="{{ getLangUrl($query) }}">

				<div class="sort-stars">
					<h4 class="popup-title">
						{!! nl2br(trans('trp.page.search.stars')) !!}
					</h4>

					<div class="ratings average">
						<div class="stars">
							<div class="bar" style="width: {{ intval($stars)/5*100 }}%;">
							</div>
							<input type="hidden" name="stars" value="{{ $stars }}" />
						</div>
					</div>
				</div>

				<div class="sort-category">
					<h4 class="popup-title">
						{!! nl2br(trans('trp.page.search.category')) !!}
					</h4>
					@foreach( config('categories') as $cat_id => $cat )
						<label class="checkbox-label{!! !empty($searchCategories) && in_array($cat, $searchCategories) ? ' active' : '' !!}" for="checkbox-popup-{{ $cat }}">
							<input type="checkbox" class="special-checkbox" id="checkbox-popup-{{ $cat }}" value="{{ $cat }}" {!! !empty($searchCategories) && in_array($cat, $searchCategories) ? 'checked="checked"' : '' !!}>
							<i class="far fa-square"></i>
							{{ trans('trp.categories.'.$cat) }}
						</label>
					@endforeach
				</div>

				<div class="sort-partners">
					<h4 class="popup-title">
						{!! nl2br(trans('trp.page.search.partners')) !!}
						
					</h4>

					<label class="checkbox-label{!! $partner ? ' active' : '' !!}" for="checkbox-partner-popup">
						<input type="checkbox" class="special-checkbox" id="checkbox-partner-popup" name="partner" value="1" {!! $partner ? 'checked="checked"' : '' !!}>
						<i class="far fa-square"></i>
						{!! nl2br(trans('trp.page.search.show-partners')) !!}
						<img src="{{ url('img-trp/mini-logo-black.png') }}">
					</label>
				</div>

				<div class="sort-by">
					<h4 class="popup-title">
						{!! nl2br(trans('trp.page.search.sort-by')) !!}
						
					</h4>

					@foreach($orders as $order)
						<a {!! $sort==$order ? 'class="active"' : '' !!} sort="{{ $order }}">
							<i class="fas fa-sort"></i>
							{{ trans('front.page.'.$current_page.'.order-'.$order) }}
						</a>
					@endforeach
					<input type="hidden" name="sort" value="{{ $sort }}" />
				</div>

				<div class="tac">
					<button type="submit" href="javascript:;" class="button">
						{!! nl2br(trans('trp.page.search.apply')) !!}
					</button>
					
					<a class="clear-filters" href="javascript:;">
						{!! nl2br(trans('trp.page.search.reset')) !!}
					</a>
				</div>
			</form>
		</div>
	</div>


	<div class="popup fixed-popup results-popup" id="map-results-popup">
		<div class="popup-inner inner-white">
			<a href="javascript:;" class="close-popup close-map">
				{!! nl2br(trans('trp.page.search.close-map')) !!}
				<i class="fas fa-times"></i>
			</a>

			<div class="flex">
				<div class="flex-2">
					<h2><img src="{{ url('img-trp/sort-gray-small.png') }}">
						{!! nl2br(trans('trp.page.search.filter')) !!}
					</h2>
    				<form class="search-get-form" method="get" base-href="{{ getLangUrl($query) }}">

						<div class="sort-stars">
							<h4 class="popup-title">{!! nl2br(trans('trp.page.search.stars')) !!}</h4>
							<div class="ratings">
								<div class="stars">
									<div class="bar" style="width: {{ intval($stars)/5*100 }}%;">
									</div>
									<input type="hidden" name="stars" value="{{ $stars }}" />
								</div>
							</div>
						</div>

						<div class="sort-category">
							<h4 class="popup-title">{!! nl2br(trans('trp.page.search.category')) !!}</h4>
							@foreach( config('categories') as $cat_id => $cat )
								<label class="checkbox-label{!! !empty($searchCategories) && in_array($cat, $searchCategories) ? ' active' : '' !!}" for="checkbox-filter-{{ $cat }}">
									<input type="checkbox" class="special-checkbox" id="checkbox-filter-{{ $cat }}" value="{{ $cat }}" {!! !empty($searchCategories) && in_array($cat, $searchCategories) ? 'checked="checked"' : '' !!}>
									<i class="far fa-square"></i>
									{{ trans('trp.categories.'.$cat) }}
								</label>
							@endforeach
						</div>

						<div class="sort-partners">
							<h4 class="popup-title">{!! nl2br(trans('trp.page.search.partners')) !!}</h4>
							<label class="checkbox-label{!! $partner ? ' active' : '' !!}" for="checkbox-partner">
								<input type="checkbox" class="special-checkbox" id="checkbox-partner" name="partner" value="1" {!! $partner ? 'checked="checked"' : '' !!}>
								<i class="far fa-square"></i>
								{!! nl2br(trans('trp.page.search.show-partners')) !!}
								<img src="{{ url('img-trp/mini-logo-black.png') }}">
							</label>
						</div>

						<div class="sort-by">
							<h4 class="popup-title">
								{!! nl2br(trans('trp.page.search.sort-by')) !!}
							</h4>

							@foreach($orders as $order)
								<a {!! $sort==$order ? 'class="active"' : '' !!} sort="{{ $order }}">
									<i class="fas fa-sort"></i>
									{{ trans('front.page.'.$current_page.'.order-'.$order) }}
								</a>
							@endforeach
							<input type="hidden" name="sort" value="{{ $sort }}" />
						</div>

						<input type="hidden" name="popup" value="map-results-popup">

						<button type="submit" href="javascript:;" class="button">
							{!! nl2br(trans('trp.page.search.apply')) !!}
						</button>
						
						<a class="clear-filters" href="javascript:;">
							{!! nl2br(trans('trp.page.search.reset')) !!}
						</a>
					</form>
				</div>
				<div class="flex-3">

					@if($items->isNotEmpty())
				    	@foreach($items as $dentist)

							<a href="{{ $dentist->getLink() }}" class="result-container dentist clearfix" {!! $dentist->address ? 'lat="'.$dentist->lat.'" lon="'.$dentist->lon.'"' : '' !!} dentist-id="{{ $dentist->id }}">
								<div class="avatar{!! $dentist->hasimage ? '' : ' default-avatar' !!}" style="background-image: url('{{ $dentist->getImageUrl(true) }}')">
									@if($dentist->hasimage)
										<img src="{{ $dentist->getImageUrl(true) }}" alt="{{ trans('trp.alt-tags.reviews-for', [ 'name' => $dentist->getName(), 'location' => ($dentist->city_name ? $dentist->city_name.', ' : '').($dentist->state_name ? $dentist->state_name.', ' : '').($dentist->country->name) ]) }}" style="display: none !important;"> 
									@endif
									@if($dentist->is_partner)
										<img class="tooltip-text" src="{{ url('img-trp/mini-logo.png') }}" text="{!! nl2br(trans('trp.common.partner')) !!} {{ $dentist->is_clinic ? 'Clinic' : 'Dentist' }}">
									@endif
								</div>
								<div class="media-right">
									<h4>
										{{ $dentist->getName() }}
									</h4>
									<span class="type">
										{{ $dentist->is_clinic ? 'Clinic' : 'Dentist' }}
									</span>
							    	@if( $time = $dentist->getWorkHoursText() )
							    		
										<div class="p">
											<div class="img">
							    				<img src="{{ url('img-trp/open.png') }}">
							    			</div>
							    			{!! strip_tags($time) !!}
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
							</a>

						@endforeach
						
						<div class="pagination" style="display: none;">
							
						</div>
					@else
						<div class="alert alert-info">
							{!! nl2br(trans('trp.page.search.no-results')) !!}
						</div>
					@endif
				</div>
				<div class="flex-7">
					@if($items->where('address', '')->count())
						<div class="alert alert-info mobile">
							{!! nl2br(trans('trp.page.search.no-address')) !!}
						</div>
					@endif
					<div id="search-map" lat="{{ $lat }}" lon="{{ $lon }}" zoom="{{ $zoom }}" {!! $worldwide ? 'worldwide="worldwide"' : '' !!}>
					</div>
				</div>

				<a id="map-mobile-tooltip" class="result-container">
				</a>
			</div>
		</div>
	</div>

@endsection