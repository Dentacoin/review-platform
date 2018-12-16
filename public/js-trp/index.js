var slider = null;
var sliderTO = null;
var displaySuggestions;
var sendSuggestions;
var autocomplete;

jQuery(document).ready(function($){

	$('.flickity').flickity({
		autoPlay: false,
		wrapAround: true,
		cellAlign: 'left',
		pageDots: false,
		freeScroll: true,
		groupCells: 1,
		cellAlign: $(window).width()<768 ? 'center' : 'left',
		freeScroll: false,
		contain: true,
		on: {
			ready: fixFlickty,
		}
	});



	$('.slider-wrapper [href]').click( function(e) {
		e.stopPropagation();
		e.preventDefault();
		window.location.href = $(this).attr('href');
	} );
});