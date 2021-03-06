@foreach( $voxes as $vox)
	<div class="swiper-slide home-vox">
		<div class="slider-inner">
			<div class="slide-padding">
				<a href="{{ !empty($taken) && in_array($vox->id, $taken) ? 'javascript:;' : $vox->getLink() }}" class="cover" style="background-image: url('{{ $vox->getImageUrl(true) }}');">
					<img src="{{ $vox->getImageUrl(true) }}" alt="{{ $vox->title }} - Dental Survey" style="display: none !important;" width="520" height="352"> 
					@if($vox->featured)
						<img class="featured-img doublecoin" src="{{ url('new-vox-img/dentavox-dentacoin-flipping-coin.gif') }}" alt="Dentavox dentacoin flipping coin" width="50" height="50">
					@endif
				</a>
				<div class="vox-header clearfix">
					<h4 class="survey-title bold">{{ $vox->title }}</h4>
					<div class="survey-cats"> 
						@foreach( $vox->categories as $c)
							<span class="survey-cat" cat-id="{{ $c->category->id }}">{{ $c->category->name }}</span>
						@endforeach
					</div>
					<div class="survey-time flex">
						<div class="col">
							<img src="{{ url('new-vox-img/coin-icon.png') }}" width="22" height="22">
							@if(false && !empty($user) && !empty($taken) && in_array($vox->id, $taken) && !empty($user->getRewardForSurvey($vox->id)))
								<p>{{ $user->getRewardForSurvey($vox->id)->reward }} DCN</p>
							@else
								<p>{{ !empty($vox->complex) ? 'Max' : '' }} {{ $vox->getRewardTotal() }} DCN</p>
							@endif
						</div>
						<div class="col">
							<img src="{{ url('new-vox-img/clock-icon.svg') }}" width="18" height="22">
							<p><span class="hide-mobile">{{ trans('vox.page.public-questionnaire.time') }}</span> {{ $vox->formatDuration() }}</p>
						</div>
					</div>
					<div class="btns">
						@if($user && $user->is_dentist)
							@if($vox->has_stats)
								<a class="statistics blue-button" href="{{ $vox->getStatsList() }}">
									{{ trans('vox.common.check-statictics') }}
								</a>
							@endif
							@if(!empty($taken) && in_array($vox->id, $taken))
								<a class="gray-button secondary" href="javascript:;">
									<i class="fas fa-check"></i>{{ trans('vox.common.taken') }}
								</a>
							@else
								<a class="opinion blue-button {!! $vox->has_stats ? 'secondary' : '' !!}" href="{{ $vox->getLink() }}">
									{{ trans('vox.common.take-the-test') }}
								</a>
							@endif
						@else
							@if(!empty($taken) && in_array($vox->id, $taken))
								<a class="gray-button" href="javascript:;">
									<i class="fas fa-check"></i>{{ trans('vox.common.taken') }}
								</a>
							@else
								<a class="opinion blue-button" href="{{ $vox->getLink() }}">
									{{ trans('vox.common.take-the-test') }}
								</a>
							@endif
							@if($vox->has_stats)
								<a class="statistics blue-button secondary" href="{{ $vox->getStatsList() }}">
									{{ trans('vox.common.check-statictics') }}
								</a>
							@endif
						@endif
					</div>
				</div>
		  	</div>
		</div>
	</div>
@endforeach