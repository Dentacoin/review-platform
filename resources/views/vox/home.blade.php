@extends('vox')

@section('content')

	<div class="container">
		<div class="another-questions">

  			@include('front.errors')
  			
			<h1 class="bold">
				{{ trans('vox.page.home.title') }}
			</h1>

			<div class="search-survey tal">
				<i class="fas fa-search"></i>
				<input type="text" id="survey-search" name="survey-search">
			</div>
			<div class="questions-menu">
				<div class="sort-menu tal"> 
					@foreach($sorts as $key => $val)
						@if($key == 'taken' && empty($taken))

						@else
							<a href="javascript:;" sort="{{ $key }}"  {!! $key == 'featured' ? 'class="active"' : '' !!}>

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
			<div class="questions-wrapper" id="questions-wrapper">
				<div class="questions-inner" id="questions-inner">
					@foreach( $voxes as $vox)
						<div class="another-question" featured="{{ intval($vox->featured) }}" published="{{ $vox->created_at->timestamp }}" popular="{{ intval($vox->rewards()->count()) }}" dcn="{{ intval($vox->getRewardTotal()) }}" duration="{{ ceil( $vox->questions()->count()/6 ) }}" taken="{{ intval(!in_array($vox->id, $taken) ? 0 : 1) }}" {!! $vox->featured && !in_array($vox->id, $taken) ? '' : 'style="display: none;"' !!}>
							@if($vox->featured)
								<img src="{{ url('new-vox-img/star.png') }}">
							@endif
							<div class="another-question-header clearfix">
								<div class="left">
									<h4 class="survey-title bold">{{ $vox->title }}</h4>
									<div class="survey-cats"> 
										@foreach( $vox->categories as $c)
											<span class="survey-cat" cat-id="{{ $c->category->id }}">{{ $c->category->name }}</span>
										@endforeach
									</div>
									<p class="question-description">{{ $vox->description }}</p>										
								</div>
								<div class="right">
									<span class="bold">{{ !empty($vox->complex) ? 'max ' : '' }} {{ $vox->getRewardTotal() }} DCN</span>
									<p>{{ $vox->formatDuration() }}</p>
									<div class="btns">
										@if($vox->has_stats)
										<a class="statistics blue-button secondary" href="{{ $vox->getStatsList() }}">
											{{ trans('vox.common.check-statictics') }}
										</a>
										@endif
										@if(!in_array($vox->id, $taken))
											<a class="opinion blue-button" href="{{ $vox->getLink() }}">
												{{ trans('vox.common.take-the-test') }}
											</a>
										@endif
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