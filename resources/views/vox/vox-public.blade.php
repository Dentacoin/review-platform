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
							<img src="{{ url('new-vox-img/clock-icon.svg') }}" width="42" height="51">
							<p>{{ trans('vox.page.public-questionnaire.time') }}</p>
							<p>{{ $vox->formatDuration() }}</p>
						</div>
					</div>
					<div class="wing right-wing">
						<div class="wing-box">
							<img src="{{ url('new-vox-img/coin-icon.png') }}" width="42" height="42">
							<p>{{ trans('vox.page.public-questionnaire.reward') }}</p>
							<p>{{ $vox->getRewardTotal() }} DCN</p>
						</div>
					</div>
		      		<div class="slider-inner">
			    		<div class="slide-padding">
			      			<a href="javascript:;" class="cover" style="background-image: url('{{ !empty($phone) ? $vox->getImageUrl(true) : $vox->getImageUrl() }}');" alt='{{ trans("vox.page.stats.title-single", ["name" => $vox->title]) }}'>
  								<img src="{{ $vox->getImageUrl(true) }}" alt="{{ $vox->title }} - Dental Survey" style="display: none !important;" width="520" height="352"> 
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
										<a href="javascript:;" class="blue-button open-dentacoin-gateway patient-register">{{ trans('vox.page.questionnaire.not-logged-register-button') }}</a>
									</div>
									<div class="col">
										<p>{{ trans('vox.page.questionnaire.not-logged-login-title') }}</p>
										<a href="javascript:;" class="white-button open-dentacoin-gateway patient-login"><img src="{{ url('new-vox-img/log-in-icon.svg') }}" width="25" height="25">{{ trans('vox.page.questionnaire.not-logged-login-button') }}</a>
									</div>
								</div>
							</div>
				      	</div>
			      	</div>
			    </div>
		    </div>
		</div>
	</div>

	<div class="make-money-wrapper index-container tac" id="to-remove-public">
		<p class="h2-bold">{!! nl2br(trans('vox.page.index.make-money.title')) !!}</p>
		<h2>{!! nl2br(trans('vox.page.index.make-money.subtitle')) !!}</h2>
	</div>

	<div id="to-append-public"></div>

@endsection