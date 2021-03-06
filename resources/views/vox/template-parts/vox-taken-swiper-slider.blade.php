<div class="slider-inner">
	<div class="slide-padding">
		<a href="{{ !empty($taken) && in_array($survey->id, $taken) ? 'javascript:;' : $survey->getLink() }}" class="cover" style="background-image: url('{{ $survey->getImageUrl(true) }}');">
  			<img src="{{ $survey->getImageUrl(true) }}" alt="{{ $survey->title }} - Dental Survey" style="display: none !important;"> 
			@if($survey->featured)
				<img class="featured-img doublecoin" src="{{ url('new-vox-img/dentavox-dentacoin-flipping-coin.gif') }}" alt="Dentavox dentacoin flipping coin">
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
					<p>Max {{ $survey->getRewardTotal() }} DCN</p>
				</div>
				<div class="col">
					<img src="{{ url('new-vox-img/clock-icon.svg') }}">
					<p><span class="hide-mobile">{{ trans('vox.page.public-questionnaire.time') }}</span> {{ $survey->formatDuration() }}</p>
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