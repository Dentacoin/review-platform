@extends('vox')

@section('content')

	<div class="container">

		@include('front.errors')
		<a class="back-home" href="{{ getLangUrl('/') }}">
			{!! trans('vox.page.stats.go-back-surveys') !!}
		</a> 
		<h1 class="bold">
			{!! trans('vox.page.stats.title') !!}
		</h1>
		<h2>
			{!! nl2br(trans('vox.page.stats.subtitle')) !!}
		</h2>

		<div class="search-survey">
			<i class="fas fa-search"></i>
			<input type="text" id="survey-search" name="survey-search" class="tal">
		</div>

		<div class="questions-menu">
			<div class="sort-menu">
				@foreach($sorts as $key => $val)

					<a href="javascript:;" sort="{{ $key }}"  {!! $key == 'featured' ? 'class="active"' : '' !!}>

						@if($key == 'featured')
							<i class="fas fa-star"></i>
						@endif

						{{ $val }}
					</a>
				@endforeach
			</div>
		</div>

		<div class="flex stats-list">
			<div class="stats-holder">
				<b template=":what Stats">
					{!! trans('vox.page.stats.featured') !!}
				</b>
				@foreach($voxes as $vox)
					@if($vox->has_stats)
						<div class="vox-stat flex" featured="{{ intval($vox->stats_featured) }}" published="{{ $vox->created_at->timestamp }}" popular="{{ intval($vox->rewards()->count()) }}" {!! $vox->stats_featured ? '' : 'style="display: none;"' !!}>
							@if($vox->stats_featured)
								<img class="featured" src="{{ url('new-vox-img/star.png') }}">
							@endif
							<img class="cover" src="{{ $vox->getImageUrl() }}" />
							<div class="stats-info flex">
								<h3>
									{{ $vox->title }}
								</h3>
								@if($vox->stats_main_question)
									<h4>
										{{ $vox->stats_main_question->translateorNew(App::getLocale())->stats_title }}
									</h4>
								@endif
								<p>
									{{ $vox->translateorNew(App::getLocale())->stats_description }}
								</p>
							</div>
							<div class="stats-cta flex">
								<a href="{{ $vox->getStatsList() }}">
									{!! trans('vox.common.check-statictics') !!}
								</a>
								@if(!in_array($vox->id, $taken))
									<a class="blue-button secondary" href="{{ $vox->getLink() }}">
										{{ trans('vox.common.take-the-test') }}
									</a>
								@endif
							</div>
						</div>
					@endif
				@endforeach

				<div class="alert alert-info" id="survey-not-found" style="display: none;">
					{!! trans('vox.page.stats.no-results') !!}
				</div>
			</div>
			<div class="stats-cats">
				<b>
					{!! trans('vox.page.stats.browse-category') !!}
				</b>
				@foreach($cats as $cat)
					@if($cat->stats_voxes->isNotEmpty())
						<a class="cat">
							{{ $cat->name }}
						</a>
						<div class="subcats"> 
							@foreach($cat->stats_voxes as $v)
								<a href="{{ $v->vox->getStatsList() }}">{{ $v->vox->title }}</a>
							@endforeach
						</div>
					@endif
				@endforeach
			</div>
		</div>


	</div>


@endsection