var slider = null;
var sliderTO = null;
var displaySuggestions;
var sendSuggestions;

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


	$('.button-want-to-add-dentist').click( function() {
		$.ajax({
            type: "GET",
            url: lang + '/want-to-invite-dentist',
        });
	});

	if( window.location.hash == '#invite-form' ) {
        $('html, body').animate({
            scrollTop: $(".invite-new-dentist-wrapper").offset().top - $('.header').height()
        }, 500);
	}

});