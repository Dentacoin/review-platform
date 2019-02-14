@extends('vox')

@section('content')

	<div class="full">
		<div class="first-absolute">
			<h1>{!! nl2br(trans('vox.page.index.title')) !!}</h1>
			<h2>{!! nl2br(trans('vox.page.index.subtitle')) !!}</h2>
			<br/>
			<a class="black-button" href="{{ getLangUrl('welcome-survey') }}">
				{!! nl2br(trans('vox.page.index.start')) !!}
			</a>
		</div>
		<a href="javascript:;" class="second-absolute">
			{!! nl2br(trans('vox.page.index.more')) !!}
		</a>
	</div>
	<div class="container section-work">

		<p class="h2-bold">{!! nl2br(trans('vox.page.index.how-works.title')) !!}</p>
		<h2>{!! nl2br(trans('vox.page.index.how-works.subtitle')) !!}</h2>

		<p class="work-desc">
			{!! nl2br(trans('vox.page.index.how-works.description')) !!}			
		</p>

		<div class="row">
			<div class="col-md-3 tac" style="{{ $user ? 'margin-left: 12%' : '' }}">
				<div class="image-wrapper warm-image">
					<img src="{{ url('new-vox-img/warm-up.png') }}">
				</div>
				<div>
					<h4>
						1. {!! nl2br(trans('vox.page.index.how-works.1.title')) !!}
					</h4>
					<p>
						{!! nl2br(trans('vox.page.index.how-works.1.content')) !!}
					</p>
				</div>
			</div>
			@if(!$user)
				<div class="col-md-3 tac">
					<div class="image-wrapper sign-image">
						<img src="{{ url('new-vox-img/sign-up.png') }}">
					</div>
					<div>
						<h4>
							2. {!! nl2br(trans('vox.page.index.how-works.2.title')) !!}
						</h4>
						<p>
							{!! nl2br(trans('vox.page.index.how-works.2.content')) !!}
						</p>
					</div>
				</div>
			@endif
			<div class="col-md-3 tac">
				<div class="image-wrapper grab-image">
					<img src="{{ url('new-vox-img/grab-reward.png') }}">
				</div>
				<div>
					<h4>
						{{ $user ? '2' : '3' }}. 
						{!! nl2br(trans('vox.page.index.how-works.3.title')) !!}
					</h4>
					@if($user)
						<p>
							{!! nl2br(trans('vox.page.index.how-works.3.content-logged')) !!}
						</p>
					@else
						<p>
							{!! nl2br(trans('vox.page.index.how-works.3.content')) !!}
						</p>
					@endif
				</div>
			</div>
			<div class="col-md-3 tac">
				<div class="image-wrapper no-image">
					<img src="{{ url('new-vox-img/take-surveys.png') }}">
				</div>
				<div>
					<h4>
						{{ $user ? '3' : '4' }}. 
						{!! nl2br(trans('vox.page.index.how-works.4.title')) !!}
					</h4>
					<p>
						{!! nl2br(trans('vox.page.index.how-works.4.content')) !!}
					</p>
				</div>
			</div>
		</div>

		<div class="row tac">
			<div class="col-md-12">
				<a class="black-button" href="{{ getLangUrl('welcome-survey') }}">
					{!! nl2br(trans('vox.page.index.start')) !!}
				</a>
			</div>
		</div>
	</div>

	<div class="section-reasons">
		<div class="container">
			<p class="h2-bold">{!! nl2br(trans('vox.page.index.reasons.title')) !!}</p>
			<h2>{!! nl2br(trans('vox.page.index.reasons.subtitle')) !!}</h2>

			<div class="reasons-wrap flex flex-center">
				<div class="col reason-number">
					<div>01</div>
				</div>
				<div class="col reason-title">
					<h4>{!! nl2br(trans('vox.page.index.reasons.1.title')) !!}</h4>
				</div>
				<div class="col reason-desc">
					<p>
						{!! nl2br(trans('vox.page.index.reasons.1.content')) !!}
					</p>
				</div>
			</div>
			<div class="reasons-wrap flex flex-center">
				<div class="col reason-number">
					<div>02</div>
				</div>
				<div class="col reason-title">
					<h4>{!! nl2br(trans('vox.page.index.reasons.2.title')) !!}</h4>
				</div>
				<div class="col reason-desc">
					<p>
						{!! nl2br(trans('vox.page.index.reasons.2.content',[
							"link" => '<a href="https://dentacoin.com/partner-network" target="_blank">',
							"endlink" => '</a>'
						])) !!}						
					</p>
				</div>
			</div>
			<div class="reasons-wrap flex flex-center">
				<div class="col reason-number">
					<div>03</div>
				</div>
				<div class="col reason-title">
					<h4>{!! nl2br(trans('vox.page.index.reasons.3.title')) !!}</h4>
				</div>
				<div class="col reason-desc">
					<p>
						{!! nl2br(trans('vox.page.index.reasons.3.content')) !!}
					</p>
				</div>
			</div>
			<div class="reasons-wrap flex flex-center">
				<div class="col reason-number">
					<div>04</div>
				</div>
				<div class="col reason-title">
					<h4>{!! nl2br(trans('vox.page.index.reasons.4.title')) !!}</h4>
				</div>
				<div class="col reason-desc">
					<p>
						{!! nl2br(trans('vox.page.index.reasons.4.content')) !!}
					</p>
				</div>
			</div>
			<div class="reasons-wrap flex flex-center">
				<div class="col reason-number">
					<div>05</div>
				</div>
				<div class="col reason-title">
					<h4>{!! nl2br(trans('vox.page.index.reasons.5.title')) !!}</h4>
				</div>
				<div class="col reason-desc">
					<p>
						{!! nl2br(trans('vox.page.index.reasons.5.content')) !!}
					</p>
				</div>
			</div>
		</div>
	</div>

	<div class="section-recent-surveys">
		<p class="h2-bold">{!! nl2br(trans('vox.page.index.recent-surveys.title')) !!}</p>
		<h2>{!! nl2br(trans('vox.page.index.recent-surveys.subtitle')) !!}</h2>

		<div class="swiper-container">
		    <div class="swiper-wrapper">
		    	@foreach($stats_voxes as $stats_vox)
			      	<div class="swiper-slide">
			    		<div class="slide-padding">
			      			<div class="cover" style="background-image: url('{{ $stats_vox->getImageUrl() }}');" alt='{{ trans("vox.page.stats.title-single", ["name" => $stats_vox->title, "respondents" => $stats_vox->respondentsCount(), "respondents_country" => $stats_vox->respondentsCountryCount() ]) }}'>
			      				<img class="featured-img" src="{{ url('new-vox-img/star.png') }}">
			      			</div>							
							<div class="vox-header clearfix">
								<div class="flex">
									<div class="col left">
										<h4 class="survey-title bold">{{ $stats_vox->title }}</h4>
										<div class="survey-cats"> 
											@foreach( $stats_vox->categories as $c)
												<span class="survey-cat" cat-id="{{ $c->category->id }}">{{ $c->category->name }}</span>
											@endforeach
										</div>
									</div>
									<div class="col right">
										<span class="bold">{{ !empty($stats_vox->complex) ? 'max ' : '' }} {{ $stats_vox->getRewardTotal() }} DCN</span>
										<p>{{ $stats_vox->formatDuration() }}</p>
									</div>					
								</div>
								<div class="flex">
									<div class="col left">
										<p class="vox-description">{{ $stats_vox->description }}</p>
									</div>
									<div class="col right">
										<div class="btns">
											@if(empty($user) || (!empty($user) && !in_array($stats_vox->id, $taken)) )
												<a class="opinion blue-button" href="{{ $stats_vox->getLink() }}">
													{{ trans('vox.common.take-the-test') }}
												</a>
											@endif
										</div>
									</div>
								</div>
							</div>
				      	</div>
				    </div>
		      	@endforeach
		    </div>

		    <div class="swiper-pagination"></div>
		</div>
	</div>

	<div class="section-stats">
		<div class="container">
			<img src="{{ url('new-vox-img/stats-front.png') }}">
			<h3>
				{!! nl2br(trans('vox.page.index.curious')) !!}
			</h3>
			<a href="{{ getLangUrl('dental-survey-stats') }}" class="check-stats">
				{{ trans('vox.common.check-statictics') }}
			</a>
		</div>
	</div>
    	
@endsection