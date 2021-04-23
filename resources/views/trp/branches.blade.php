@extends('trp')

@section('content')

	<div class="black-overflow" style="display: none;"></div>
	<div class="search-gradient-wave green-gradient">
		<div class="main-top branch-top"></div>
	    <div class="container overflow-container">
	    	<h1>{{ trans('trp.page.branches.title') }}</h1>
	    </div>
	</div>

    <div class="search-results-wrapper container">

    	<a href="{{ $user->getLink() }}" class="result-container current-branch dentist clearfix" full-dentist-id="{{ $user->id }}">
    		<img class="angle-check" src="{{ url('img-trp/angle-check.png') }}">
			<div class="avatar{!! $user->hasimage ? '' : ' default-avatar' !!}"  style="background-image: url('{{ $user->getImageUrl(true) }}')">
				@if($user->hasimage)
					<img src="{{ $user->getImageUrl(true) }}" alt="{{ trans('trp.alt-tags.reviews-for', [ 'name' => $user->getNames(), 'location' => ($user->city_name ? $user->city_name.', ' : '').($user->state_name ? $user->state_name.', ' : '').($user->country->name) ]) }}" style="display: none !important;"> 
				@endif
			</div>
			<div class="media-right">
				<h4>
					{{ $user->getNames() }}
				</h4>
				@if($user->is_partner)
					<span class="type">
						<div class="img">
							<img src="{{ url('img-trp/mini-logo.png') }}">
						</div>
						<span>{!! nl2br(trans('trp.page.search.partner')) !!}</span> 
						{{ $user->is_clinic ? trans('trp.page.user.clinic') : trans('trp.page.user.dentist') }}
					</span>
				@endif
				<div class="p">
					<div class="img">
						<img src="{{ url('img-trp/map-pin.png') }}">
					</div>
					{{ $user->city_name ? $user->city_name.', ' : '' }}
					{{ $user->state_name ? $user->state_name.', ' : '' }} 
					{{ $user->country->name }} 
				</div>

		    	@if( $time = $user->getWorkHoursText() )
		    		<div class="p">
		    			<div class="img">
		    				<img src="{{ url('img-trp/open.png') }}">
		    			</div>
		    			{!! $time !!}
		    		</div>
		    	@endif
		    	@if( $user->website )
		    		<div class="p dentist-website" href="{{ $user->getWebsiteUrl() }}" target="_blank">
		    			<div class="img">
			    			<img class="black-filter" src="{{ url('img-trp/website-icon.svg') }}">
			    		</div>
			    		<span>
				    		{{ $user->website }}
				    	</span>
		    		</div>
		    	@endif
		    	@if($user->top_dentist_month)
					<div class="top-dentist">
						<img src="{{ url('img-trp/top-dentist.png') }}">
		    			<span>
		    				{!! trans('trp.common.top-dentist') !!}
	    				</span>
	    			</div>
				@endif
			    <div class="ratings">
					<div class="stars">
						<div class="bar" style="width: {{ $user->avg_rating/5*100 }}%;">
						</div>
					</div>
					<span class="rating">
						({{ trans('trp.common.reviews-count', [ 'count' => intval($user->ratings)]) }})
					</span>
				</div>
				<div class="share-button" data-popup="popup-share" share-href="{{ $user->getLink() }}">
					<img src="{{ url('img-trp/share.png') }}">
				</div>
			</div>
		</a>

    	@foreach($items as $dentist)

			<a href="{{ $dentist->getLink() }}" class="result-container branch dentist clearfix" full-dentist-id="{{ $dentist->id }}">
				<div class="avatar{!! $dentist->hasimage ? '' : ' default-avatar' !!}"  style="background-image: url('{{ $dentist->getImageUrl(true) }}')">
					@if($dentist->hasimage)
						<img src="{{ $dentist->getImageUrl(true) }}" alt="{{ trans('trp.alt-tags.reviews-for', [ 'name' => $dentist->getNames(), 'location' => ($dentist->city_name ? $dentist->city_name.', ' : '').($dentist->state_name ? $dentist->state_name.', ' : '').($dentist->country->name) ]) }}" style="display: none !important;"> 
					@endif
				</div>
				<div class="media-right">
					<h4>
						{{ $dentist->getNames() }}
					</h4>
					@if($dentist->is_partner)
						<span class="type">
							<div class="img">
								<img src="{{ url('img-trp/mini-logo.png') }}">
							</div>
							<span>{!! nl2br(trans('trp.page.search.partner')) !!}</span> 
							{{ $dentist->is_clinic ? trans('trp.page.user.clinic') : trans('trp.page.user.dentist') }}
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
					<div href="javascript:;" login-url="{{ getLangUrl('loginas/'.$dentist->id.'/'.App\Models\User::encrypt($user->id)) }}" logout-url="{{ getLangUrl('logoutas') }}" class="button button-submit login-as" cur-user="{{ $user->id }}">
						{!! nl2br(trans('trp.page.user.branch.switch-account')) !!}
					</div>
					<div class="share-button" data-popup="popup-share" share-href="{{ $dentist->getLink() }}">
						<img src="{{ url('img-trp/share.png') }}">
					</div>
				</div>
			</a>

		@endforeach

		@if(!empty($user->email))
			<a href="javascript:;" data-popup-logged="popup-branch" class="button add-branch"><img src="{{ url('img-trp/add-new-branch-white.svg') }}"/>{{ trans('trp.page.branches.add-branch') }}</a>
		@endif
	</div>

	@include('trp.popups.add-branch')

@endsection