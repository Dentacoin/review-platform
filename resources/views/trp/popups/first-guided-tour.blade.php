<div class="popup fixed-popup first-guided-tour-popup active tour-popup" id="first-guided-tour">
	<div class="popup-inner-tour tac">
		<div class="avatar" style="background-image: url('{{ $item->getImageUrl(true) }}');">
			<img src="{{ $item->getImageUrl(true) }}" alt="{{ trans('trp.alt-tags.reviews-for', [ 'name' => $item->getName(), 'location' => ($item->city_name ? $item->city_name.', ' : '').($item->state_name ? $item->state_name.', ' : '').($item->country->name) ]) }}" style="display: none !important;"> 
		</div>

		<h2>{{ !empty(session('reviews_guided_tour')) ? nl2br(trans('trp.guided-tour.reviews.popup.title')) : nl2br(trans('trp.guided-tour.first.popup.title')) }}</h2>
		<p>{{ !empty(session('reviews_guided_tour')) ? nl2br(trans('trp.guided-tour.reviews.popup.subtitle')) : nl2br(trans('trp.guided-tour.first.popup.subtitle')) }}</p>

		<div class="tour-buttons">
			<a href="javascript:;" class="{{ !empty(session('reviews_guided_tour')) ? 'skip-reviews-tour' : 'skip-first-tour' }} tour-button">
				{{ trans('trp.guided-tour.popup.skip-tour') }}
			</a>
			<a href="javascript:;" class="{{ !empty(session('reviews_guided_tour')) ? 'go-reviews-tour' : 'go-first-tour go-login-tour' }} button-white tour-button">
				{{ trans('trp.guided-tour.popup.lets-go') }} >>
			</a>
		</div>
	</div>
</div>