@extends('trp')

@section('content')

	<div class="black-overflow" style="display: none;"></div>
	<div class="search-gradient-wave green-gradient">
		<div class="main-top branch-top"></div>
	    <div class="container overflow-container">
	    	<h1 class="{{ !empty($user) ? '' : 'small-mt' }}">{{ !empty($user) && $user->id == $clinic->id ? trans('trp.page.branches.title') : $clinic->getNames()."'s Branches" }}</h1>
	    </div>
	</div>

    <div class="search-results-wrapper container">

    	<a href="{{ $clinic->getLink() }}" class="result-container current-branch dentist clearfix" full-dentist-id="{{ $clinic->id }}">
    		<img class="angle-check" src="{{ url('img-trp/angle-check.png') }}">
			<div class="avatar{!! $clinic->hasimage ? '' : ' default-avatar' !!}"  style="background-image: url('{{ $clinic->getImageUrl(true) }}')">
				@if($clinic->hasimage)
					<img src="{{ $clinic->getImageUrl(true) }}" alt="{{ trans('trp.alt-tags.reviews-for', [ 'name' => $clinic->getNames(), 'location' => ($clinic->city_name ? $clinic->city_name.', ' : '').($clinic->state_name ? $clinic->state_name.', ' : '').($clinic->country->name) ]) }}" style="display: none !important;"> 
				@endif
				@if($clinic->id == $clinic->mainBranchClinic->id)
					<div class="main-clinic">{!! nl2br(trans('trp.common.primary-account')) !!}</div>
				@endif
			</div>
			<div class="media-right">
				<h4>
					{{ $clinic->getNames() }}
				</h4>
				@if($clinic->is_partner)
					<span class="type">
						<div class="img">
							<img src="{{ url('img-trp/mini-logo.png') }}">
						</div>
						<span>{!! nl2br(trans('trp.page.search.partner')) !!}</span> 
						{{ $clinic->is_clinic ? trans('trp.page.user.clinic') : trans('trp.page.user.dentist') }}
					</span>
				@endif
				<div class="p">
					<div class="img">
						<img src="{{ url('img-trp/map-pin.png') }}">
					</div>
					{{ $clinic->city_name ? $clinic->city_name.', ' : '' }}
					{{ $clinic->state_name ? $clinic->state_name.', ' : '' }} 
					{{ $clinic->country->name }} 
				</div>

		    	@if( $time = $clinic->getWorkHoursText() )
		    		<div class="p">
		    			<div class="img">
		    				<img src="{{ url('img-trp/open.png') }}">
		    			</div>
		    			{!! $time !!}
		    		</div>
		    	@endif
		    	@if( $clinic->website )
		    		<div class="p dentist-website">
		    			<div class="img">
			    			<img class="black-filter" src="{{ url('img-trp/website-icon.svg') }}">
			    		</div>
			    		<span>
				    		{{ $clinic->website }}
				    	</span>
		    		</div>
		    	@endif
		    	@if($clinic->top_dentist_month)
					<div class="top-dentist">
						<img src="{{ url('img-trp/top-dentist.png') }}">
		    			<span>
		    				{!! trans('trp.common.top-dentist') !!}
	    				</span>
	    			</div>
				@endif
			    <div class="ratings">
					<div class="stars">
						<div class="bar" style="width: {{ $clinic->avg_rating/5*100 }}%;">
						</div>
					</div>
					<span class="rating">
						({{ trans('trp.common.reviews-count', [ 'count' => intval($clinic->ratings)]) }})
					</span>
				</div>
				@if(!empty($user) && $user->id == $clinic->id)
				@else
					<div href="{{ $clinic->getLink() }}" class="button button-submit">
						{!! nl2br(trans('trp.common.see-profile')) !!}
					</div>
				@endif
				<div class="share-button" data-popup="popup-share" share-href="{{ $clinic->getLink() }}">
					<img src="{{ url('img-trp/share.png') }}">
				</div>
			</div>
		</a>

    	@foreach($items as $dentist)
    		@if(!empty($dentist))
				<a href="{{ !empty($user) && $user->id == $clinic->id ? 'javascript:;' : $dentist->getLink() }}" class="result-container branch dentist clearfix {{ !empty($user) && $user->id == $clinic->id ? 'login-as' : '' }}" {!! !empty($user) && $user->id == $clinic->id ? 'login-url="'.getLangUrl('loginas').'" branch-id="'.$dentist->id.'" redirect-url="'.getLangUrl('branches/'.$dentist->slug).'"' : '' !!} full-dentist-id="{{ $dentist->id }}">
					<div class="avatar{!! $dentist->hasimage ? '' : ' default-avatar' !!}"  style="background-image: url('{{ $dentist->getImageUrl(true) }}')">
						@if($dentist->hasimage)
							<img src="{{ $dentist->getImageUrl(true) }}" alt="{{ trans('trp.alt-tags.reviews-for', [ 'name' => $dentist->getNames(), 'location' => ($dentist->city_name ? $dentist->city_name.', ' : '').($dentist->state_name ? $dentist->state_name.', ' : '').($dentist->country->name) ]) }}" style="display: none !important;"> 
						@endif
						@if($dentist->id == $dentist->mainBranchClinic->id)
							<div class="main-clinic">{!! nl2br(trans('trp.common.primary-account')) !!}</div>
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
				    		<div class="p dentist-website">
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
						@if(!empty($user) && $user->id == $clinic->id)
							<div href="javascript:;" login-url="{{ getLangUrl('loginas') }}" branch-id="{{ $dentist->id }}" class="button button-submit login-as {{ $dentist->id != $dentist->mainBranchClinic->id ? 'mt' : '' }}">
								{!! nl2br(trans('trp.page.user.branch.switch-account')) !!}
							</div>

							@if($dentist->id != $dentist->mainBranchClinic->id)
								<div href="javascript:;" delete-url="{{ getLangUrl('delete-branch') }}" branch-id="{{ $dentist->id }}" class="delete-branch">
									Delete Branch X
								</div>
							@endif
						@else
							<div href="{{ $dentist->getLink() }}" class="button button-submit">
								{!! nl2br(trans('trp.common.see-profile')) !!}
							</div>
						@endif
						<div class="share-button" data-popup="popup-share" share-href="{{ $dentist->getLink() }}">
							<img src="{{ url('img-trp/share.png') }}">
						</div>
					</div>
				</a>
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
	@endif

@endsection