@extends('vox')

@section('content')

	<div class="container">
		@if($user && !empty($admin))
			<div class="strength-parent">
				@include('vox.template-parts.strength-scale')
			</div>
		@endif
		
		<div class="another-questions">

  			@include('front.errors')
  			
			<h1 class="bold">
				{{ trans('vox.page.home.title') }}
			</h1>

			@if(!empty($user) && $voxes->count() == count($taken))
				<div class="alert alert-info alert-done-all-surveys">
					@if($user->is_dentist)
						{!! nl2br(trans('vox.page.home.dentist.alert-done-all-surveys', [
							'link' => '<a href="'.getLangUrl('profile/invite').'">',
							'link_stats' => '<a href="'.getLangUrl('dental-survey-stats').'">',
							'endlink' => '</a>',
						])) !!}
					@else
						{!! nl2br(trans('vox.page.home.patients.alert-done-all-surveys', [
							'link' => '<a href="'.getLangUrl('profile/invite').'">',
							'link_stats' => '<a href="'.getLangUrl('dental-survey-stats').'">',
							'endlink' => '</a>',
						])) !!}
					@endif
				</div>
			@else
				<div class="filters-section">
					<div class="search-survey tal">
						<i class="fas fa-search"></i>
						<input type="text" id="survey-search" name="survey-search">
					</div>
					<div class="questions-menu clearfix">
						<div class="sort-menu tal"> 
							@foreach($sorts as $key => $val)
								@if($key == 'taken' && empty($taken))

								@else
									<a href="javascript:;" sort="{{ $key }}"  class="{!! $key == 'newest' ? 'active sortable' : ( $key == 'featured' || $key == 'untaken' ? 'active' : ($key == 'all' || $key == 'taken' ? '' : 'sortable')) !!}">

										@if($key == 'featured')
											<i class="fas fa-star"></i>
										@endif

										{{ $val }}
									</a>
								@endif
							@endforeach
						</div>
						<div class="sort-category tar"> 
							<span>
								{{ trans('vox.page.home.filter') }}:
							</span>
							{{ Form::select('category', ['all' => 'All'] + $vox_categories, null , ['id' => 'surveys-categories']) }} 
						</div>
					</div>

					@if(!empty($user))
						<div class="questions-menu clearfix">
							<div class="filter-menu tal"> 
								@foreach($filters as $k => $v)
									@if($k == 'taken' && empty($taken))

									@else
										<a href="javascript:;" filter="{{ $k }}"  class="{!! $k == 'untaken' ? 'active' : '' !!}">
											{{ $v }}
										</a>
									@endif
								@endforeach
							</div>
						</div>
					@endif
				</div>
			@endif
			<div class="section-recent-surveys" id="questions-wrapper">
				<div class="questions-inner" id="questions-inner">
					@foreach( $voxes as $vox)
				      	<div class="swiper-slide"
			      			featured="{{ intval($vox->featured) }}" 
			      			published="{{ $vox->launched_at->timestamp }}" 
			      			sort-order="{{ $vox->sort_order ? $vox->sort_order : 0 }}" 
			      			popular="{{ intval($vox->rewards()->count()) }}" 
			      			dcn="{{ intval($vox->getRewardTotal()) }}" 
			      			duration="{{ ceil( $vox->questions()->count()/6 ) }}" 
			      			taken="{{ !empty($taken) && intval(!in_array($vox->id, $taken) ? 0 : 1) }}"
			      			>
				      		<div class="slider-inner">
					    		<div class="slide-padding">
					      			<a href="{{ !empty($taken) && in_array($vox->id, $taken) ? 'javascript:;' : $vox->getLink() }}" class="cover" style="background-image: url('{{ $vox->getImageUrl() }}');" alt='{{ trans("vox.page.stats.title-single", ["name" => $vox->title, "respondents" => $vox->respondentsCount(), "respondents_country" => $vox->respondentsCountryCount() ]) }}'>
					      				@if($vox->featured)
					      					<img class="featured-img doublecoin" src="{{ url('new-vox-img/flipping-coin.gif') }}">
					      				@endif
					      				<!-- @if(!empty($taken) && in_array($vox->id, $taken))
					      					<img class="done-img" src="{{ url('new-vox-img/vox-done.png') }}">
					      				@endif -->
					      			</a>							
									<div class="vox-header clearfix">
										<div class="flex first-flex">
											<div class="col left">
												<h4 class="survey-title bold">{{ $vox->title }}</h4>
											</div>
											<div class="col right">
												<span class="bold">{{ !empty($vox->complex) ? 'max ' : '' }} {{ $vox->getRewardTotal() }} DCN</span>
												<p>{{ $vox->formatDuration() }}</p>
											</div>					
										</div>
										<div class="survey-cats"> 
											@foreach( $vox->categories as $c)
												<span class="survey-cat" cat-id="{{ $c->category->id }}">{{ $c->category->name }}</span>
											@endforeach
										</div>
										<div class="flex second-flex">
											<div class="col left">
												<p class="vox-description">{{ $vox->description }}</p>
											</div>
											<div class="col right">
												<div class="btns">
													@if($user && $user->is_dentist)
														@if($vox->has_stats)
															<a class="statistics blue-button" href="{{ $vox->getStatsList() }}">
																{{ trans('vox.common.check-statictics') }}
															</a>
														@endif
														@if(!empty($taken) && in_array($vox->id, $taken))
															<a class="gray-button secondary" href="javascript:;">
																<i class="fas fa-check"></i>{{ trans('vox.common.taken') }}
															</a>
														@else
															<a class="opinion blue-button {!! $vox->has_stats ? 'secondary' : '' !!}" href="{{ $vox->getLink() }}">
																{{ trans('vox.common.take-the-test') }}
															</a>
														@endif
													@else
														@if(!empty($taken) && in_array($vox->id, $taken))
															<a class="gray-button" href="javascript:;">
																<i class="fas fa-check"></i>{{ trans('vox.common.taken') }}
															</a>
														@else
															<a class="opinion blue-button" href="{{ $vox->getLink() }}">
																{{ trans('vox.common.take-the-test') }}
															</a>
														@endif
														@if($vox->has_stats)
															<a class="statistics blue-button secondary" href="{{ $vox->getStatsList() }}">
																{{ trans('vox.common.check-statictics') }}
															</a>
														@endif
													@endif
												</div>
											</div>
										</div>
									</div>
						      	</div>
					      	</div>
					    </div>
					@endforeach
				</div>

				<a class="give-me-more" id="survey-more" href="javascript:;" style="display: none;">
					{{ trans('vox.common.load-more') }}					
				</a>

				<div class="alert alert-info" id="survey-not-found" style="display: none;">
					{{ trans('vox.page.home.no-results') }}
				</div>
			</div>
	            
		</div>
	</div>
    	
    	
@endsection