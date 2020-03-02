@extends('vox')

@section('content')

	<div class="section-recent-surveys section-vox-not-login">
		<div class="container">
			<h2 class="vox-title">"{{ $vox->title }}"</h2>
			<h2>{{ trans('vox.page.public-questionnaire.title') }}</h2>

			<div class="swiper-wrapper">

		      	<div class="swiper-slide">
					<div class="wing left-wing">
						<div class="wing-box">
							<img src="{{ url('new-vox-img/clock-icon.svg') }}">
							<p>{{ trans('vox.page.public-questionnaire.time') }}</p>
							<p>{{ $vox->formatDuration() }}</p>
						</div>
					</div>
					<div class="wing right-wing">
						<div class="wing-box">
							<img src="{{ url('new-vox-img/coin-icon.png') }}">
							<p>{{ trans('vox.page.public-questionnaire.reward') }}</p>
							<p>{{ $vox->getRewardTotal() }} DCN</p>
						</div>
					</div>
		      		<div class="slider-inner">
			    		<div class="slide-padding">
			      			<a href="javascript:;" class="cover" style="background-image: url('{{ $vox->getImageUrl() }}');" alt='{{ trans("vox.page.stats.title-single", ["name" => $vox->title]) }}'>
  								<img src="{{ $vox->getImageUrl(true) }}" alt="{{ $vox->title }} - Dental Survey" style="display: none !important;"> 
			      			</a>							
							<div class="vox-header clearfix">
								<div class="survey-cats"> 
									@foreach( $vox->categories as $c)
										<span class="survey-cat" cat-id="{{ $c->category->id }}">{{ $c->category->name }}</span>
									@endforeach
								</div>
								<div class="flex second-flex">
									<div class="col left">
										<p class="vox-description">{{ $vox->description }}</p>
									</div>
								</div>
								<div class="flex login-buttons">
									<div class="col">
										<p>{{ trans('vox.page.questionnaire.not-logged-register-title') }}</p>
										<a href="{{ getLangUrl('registration') }}" class="blue-button">{{ trans('vox.page.questionnaire.not-logged-register-button') }}</a>
									</div>
									<div class="col">
										<p>{{ trans('vox.page.questionnaire.not-logged-login-title') }}</p>
										<a href="{{ getLangUrl('login') }}" class="white-button"><img src="{{ url('new-vox-img/log-in-icon.svg') }}">{{ trans('vox.page.questionnaire.not-logged-login-button') }}</a>
									</div>
								</div>
							</div>
				      	</div>
			      	</div>
			    </div>
		    </div>
		</div>
	</div>

	@include('vox.template-parts.index-part')

	<div class="section-recent-surveys">
		<p class="h2-bold">{!! nl2br(trans('vox.page.index.recent-surveys.title')) !!}</p>
		<h2>{!! nl2br(trans('vox.page.index.recent-surveys.subtitle')) !!}</h2>

		<div class="swiper-container">
		    <div class="swiper-wrapper">
		    	@foreach($voxes as $vox)
			      	<div class="swiper-slide">
			      		<div class="slider-inner">
				    		<div class="slide-padding">
				      			<a href="{{ $vox->getLink() }}" class="cover" style="background-image: url('{{ $vox->getImageUrl() }}');">
  									<img src="{{ $vox->getImageUrl(true) }}" alt="{{ $vox->title }} - Dental Survey" style="display: none !important;"> 
				      				@if($vox->featured)
				      					<img class="featured-img doublecoin" src="{{ url('new-vox-img/dentavox-dentacoin-flipping-coin.gif') }}" alt="Dentavox dentacoin flipping coin">
				      				@endif
				      			</a>							
								<div class="vox-header clearfix">
									<div class="flex first-flex">
										<div class="col left">
											<h4 class="survey-title bold">{{ $vox->title }}</h4>
										</div>
										<div class="col right">
											<span class="bold">{{ !empty($vox->complex) && empty($vox->manually_calc_reward) && empty($vox->dcn_questions_count) ? 'max ' : '' }} {{ $vox->getRewardTotal() }} DCN</span>
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
												@if(empty($user) || (!empty($user) && !in_array($vox->id, $taken)) )
													<a class="opinion blue-button" href="{{ $vox->getLink() }}">
														{{ trans('vox.common.take-the-test') }}
													</a>
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

		    <div class="swiper-pagination"></div>
		</div>
	</div>

	<div class="section-stats">
		<div class="container clearfix">
			<img src="{{ url('new-vox-img/dentavox-dental-statistics-icon.png') }}" alt="Dentavox dental statistics icon">
			<h3>
				{!! nl2br(trans('vox.page.index.curious')) !!}
			</h3>
			<a href="{{ getLangUrl('dental-survey-stats') }}" class="check-stats">
				{{ trans('vox.common.check-statictics') }}
			</a>
		</div>
	</div>

@endsection