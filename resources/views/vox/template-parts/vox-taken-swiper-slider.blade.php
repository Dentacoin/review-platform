<div class="swiper-slide" survey-id="{{ $survey->id }}">
	<div class="slider-inner">
		<div class="slide-padding">
  			<a href="{{ $survey->getLink() }}" class="cover" style="background-image: url('{{ $survey->getImageUrl() }}');" alt='{{ trans("vox.page.stats.title-single", ["name" => $survey->title ]) }}'>
  				@if($survey->featured)
  					<img class="featured-img doublecoin" src="{{ url('new-vox-img/flipping-coin.gif') }}">
  				@endif
  			</a>							
			<div class="vox-header clearfix">
				<h4 class="survey-title bold">{{ $survey->title }}</h4>
				<div class="survey-cats"> 
					@foreach( $survey->categories as $c)
						<span class="survey-cat" cat-id="{{ $c->category->id }}">{{ $c->category->name }}</span>
					@endforeach
				</div>
				<div class="survey-time flex">
					<div class="col">
						<img src="{{ url('new-vox-img/coin-icon.png') }}">
						<p>Max {{ $vox->getRewardTotal() }} DCN</p>
					</div>
					<div class="col">
						<img src="{{ url('new-vox-img/clock-icon.svg') }}">
						<p><span class="hide-mobile">{{ trans('vox.page.public-questionnaire.time') }}</span> {{ $vox->formatDuration() }}</p>
					</div>
				</div>
				<div class="btns">
					@if($survey->has_stats)
						<a class="white-button" href="{{ $survey->getStatsList() }}">
							{!! trans('vox.common.check-statictics') !!}
						</a>
					@endif
					<a class="opinion blue-button" href="{{ $survey->getLink() }}">
						{{ trans('vox.common.take-the-test') }}
					</a>
				</div>
			</div>
      	</div>
  	</div>
</div>