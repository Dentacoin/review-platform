@extends('vox')

@section('content')

	<div class="container all-stats-wrapper">

		@include('vox.errors')
		<a class="back-home" href="{{ getLangUrl('/') }}">
			{!! trans('vox.page.stats.go-back-surveys') !!}
		</a> 
		<h1 class="bold">
			{!! trans('vox.page.stats.title') !!}
		</h1>
		<h2>
			{!! nl2br(trans('vox.page.stats.subtitle')) !!}
		</h2>

		<p class="stats-description">
			{!! trans('vox.page.stats.description') !!}
		</p>

		{!! csrf_field() !!}
		<form class="search-stats-form" method="post" action="{{ getLangUrl('dental-survey-stats') }}">
			{!! csrf_field() !!}
			<div class="search-survey">
				<i class="fas fa-search"></i>
				<input type="text" id="survey-search" name="survey-search" class="tal" value="{{ !empty($name) ? $name : '' }}">
			</div>
		</form>
		<br/>
		<br/>
	</div>

	<div class="stats-list">
		<div class="stats-holder">
			<b template=":what Stats">
				{!! trans('vox.page.stats.featured') !!}
			</b>
			@if(!empty($user) && $user->is_dentist)
				<div class="vox-stat flex request-survey-stat">
					<a href="javascript:;" class="cover" style="background-image: url('{{ url('new-vox-img/request-survey.jpg') }}');"></a>
					<div class="stats-info flex">
						<h3>
							{{ trans('vox.page.home.request-survey.title') }}
						</h3>
						<p>
							{{ trans('vox.page.home.request-survey.description') }}
						</p>
					</div>
					<div class="stats-cta flex">
						<a class="blue-button {!! $user->status != 'approved' && $user->status != 'added_by_clinic_claimed' && $user->status!='added_by_dentist_claimed' && $user->status != 'test' ? 'disabled' : '' !!}" href="javascript:;" data-popup="request-survey-popup">
							{{ trans('vox.page.home.request-survey.request') }}
						</a>
					</div>
				</div>
			@endif

			@foreach($voxes as $vox)
				<div 
					class="vox-stat flex normal-stat" 
					featured="{{ intval($vox->stats_featured) }}" 
					published="{{ $vox->created_at->timestamp }}" 
					updated="{{ $vox->updated_at->timestamp }}" 
					launched="{{ $vox->launched_at? $vox->launched_at->timestamp : 0 }}" 
					popular="{{ intval($vox->rewardsCount()) }}" 
	      			sort-order="{{ $vox->sort_order }}"
	      			>
					@if($vox->stats_featured)
						<img class="featured" src="{{ url('new-vox-img/star.svg') }}" alt="Dentavox featured statistic" width="50" height="48">
					@endif
					<a href="{{ $vox->getStatsList() }}">
						<img class="cover" src="{{ $vox->getImageUrl(true) }}" alt="{{ $vox->title }} - Survey Statistics" width="260" height="176" />
					</a>
					<div class="stats-info flex">
						<h3>
							{{ $vox->title }}
						</h3>
						<p>
							{{ $vox->translateorNew(App::getLocale())->stats_description }}
						</p>
					</div>
					<div class="stats-cta flex">
						<a href="{{ $vox->getStatsList() }}">
							{!! trans('vox.common.check-statictics') !!}
						</a>
						@if(!in_array($vox->id, $taken) && $vox->type != 'hidden' )
							<a class="blue-button secondary" href="{{ $vox->getLink() }}">
								{{ trans('vox.common.take-the-test') }}
							</a>
						@endif
					</div>
				</div>
			@endforeach

			<div class="container">
				<div class="alert alert-info" id="survey-not-found" style="display: none;">
					{!! trans('vox.page.stats.no-results') !!}
				</div>
			</div>
		</div>

		@if(empty($name))
			<div class="tac"> 
				{{ $voxes->render() }}
			</div>
		@endif
		
		@if(false)
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
		@endif
	</div>

	<div class="blog-wrapper">
		<div class="container flex">
			<div class="col">
				<h2>{{ trans('vox.page.stats.dv-blog.title') }}</h2>
				<p>{{ trans('vox.page.stats.dv-blog.description') }}</p>
				<a href="https://dentavox.dentacoin.com/blog" target="_blank" class="white-button">{{ trans('vox.page.stats.dv-blog.button') }}</a>
			</div>
			<div class="col">
				<img src="{{ url('new-vox-img/dentavox-blog-preview.png') }}" alt="Dentavox blog preview" width="500" height="351">
			</div>
		</div>
	</div>

	@if(!empty($user))
		<div class="section-take-surveys">
			<div class="container">
				<img src="{{ url('new-vox-img/custom-survey-vox.png') }}" alt="Dentavox custom survey" width="130" height="131">
				<h3>
					@if($user->is_dentist)
						{!! nl2br(trans('vox.page.stats.request-survey-dentist.title')) !!}
					@else
						{!! nl2br(trans('vox.page.stats.request-survey-patient.title')) !!}
					@endif
				</h3>
				@if($user->is_dentist)
					<a href="javascript:;" data-popup="request-survey-popup" class="white-button">
						{{ trans('vox.page.stats.request-survey-dentist.button') }}
					</a>
				@else
					<a href="javascript:;" data-popup="request-survey-patient-popup" class="white-button">
						{{ trans('vox.page.stats.request-survey-patient.button') }}
					</a>
				@endif
			</div>
		</div>
	@endif

@endsection