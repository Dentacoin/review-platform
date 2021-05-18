<div class="section-recent-surveys">
	<p class="h2-bold">{!! nl2br(trans('vox.page.index.most-recent-surveys.title')) !!}</p>
	<h2>{!! nl2br(trans('vox.page.index.most-recent-surveys.subtitle')) !!}</h2>

	<div class="swiper-container">
	    <div class="swiper-wrapper">
	    	@foreach($voxes as $vox)
		      	<div class="swiper-slide">
		      		<div class="slider-inner">
			    		<div class="slide-padding">
			      			<a href="{{ $vox->getLink() }}" class="cover" style="background-image: url('{{ $vox->getImageUrl() }}');">
									<img src="{{ $vox->getImageUrl(true) }}" alt="{{ $vox->title }} - Dental Survey" style="display: none !important;" width="494" height="222"> 
			      				@if($vox->featured)
			      					<img class="featured-img doublecoin" src="{{ url('new-vox-img/dentavox-dentacoin-flipping-coin.gif') }}" alt="Dentavox dentacoin flipping coin" width="50" height="50">
			      				@endif
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
											<a class="opinion blue-button" href="{{ $vox->getLink() }}">
												{{ trans('vox.common.take-the-test') }}
											</a>
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
		<img src="{{ url('new-vox-img/dentavox-dental-statistics-icon.png') }}" alt="Dentavox dental statistics icon" width="148" height="132">
		<h3>
			{!! nl2br(trans('vox.page.index.curious')) !!}
		</h3>
		<a href="{{ getLangUrl('dental-survey-stats') }}" class="check-stats">
			{{ trans('vox.common.check-statictics') }}
		</a>
	</div>
</div>