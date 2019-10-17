<div class="swiper-slide" survey-id="{{ $survey->id }}">
	<div class="slider-inner">
		<div class="slide-padding">
  			<a href="{{ $survey->getLink() }}" class="cover" style="background-image: url('{{ $survey->getImageUrl() }}');" alt='{{ trans("vox.page.stats.title-single", ["name" => $survey->title ]) }}'>
  				@if($survey->featured)
  					<img class="featured-img doublecoin" src="{{ url('new-vox-img/flipping-coin.gif') }}">
  				@endif
  			</a>							
			<div class="vox-header clearfix">
				<div class="flex first-flex">
					<div class="col left">
						<h4 class="survey-title bold">{{ $survey->title }}</h4>
					</div>
					<div class="col right">
						<span class="bold">{{ !empty($survey->complex) ? 'max ' : '' }} {{ $survey->getRewardTotal() }} DCN</span>
						<p>{{ $survey->formatDuration() }}</p>
					</div>					
				</div>
				<div class="survey-cats"> 
					@foreach( $survey->categories as $c)
						<span class="survey-cat" cat-id="{{ $c->category->id }}">{{ $c->category->name }}</span>
					@endforeach
				</div>
				<div class="flex second-flex">
					<div class="col left">
						<p class="vox-description">{{ $survey->description }}</p>
					</div>
					<div class="col right">
						<div class="btns">
							<a class="opinion blue-button" href="{{ $survey->getLink() }}">
								{{ trans('vox.common.take-the-test') }}
							</a>
						</div>
					</div>
				</div>
			</div>
      	</div>
  	</div>
</div>