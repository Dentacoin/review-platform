<div class="popup fixed-popup first-guided-tour-popup active" id="first-guided-tour">
	<div class="popup-inner-tour tac">
		<div class="avatar" style="background-image: url('{{ $item->getImageUrl(true) }}');">
			<img src="{{ $item->getImageUrl(true) }}" alt="{{ trans('trp.alt-tags.reviews-for', [ 'name' => $item->getName(), 'location' => ($item->city_name ? $item->city_name.', ' : '').($item->state_name ? $item->state_name.', ' : '').($item->country->name) ]) }}" style="display: none !important;"> 
		</div>

		<h2>Welcome!</h2>
		<p>Let’s get your profile page set up! It will take only a minute.</p>

		<div class="tour-buttons">
			<a href="javascript::" class="skip-first-tour tour-button">
				Skip guided tour
			</a>
			<a href="javascript::" class="go-first-tour button-white tour-button">
				LETS GO >>
			</a>
		</div>
	</div>
</div>