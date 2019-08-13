var slider = null;
var sliderTO = null;
var displaySuggestions;
var sendSuggestions;

jQuery(document).ready(function($){

	if ($('.slider-wrapper').length <= 4 && $(window).outerWidth() > 998) {
		$('.flickity').addClass('flex');
		
	} else {
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
	}

	var fixFlicktyInner = function() {
		$('.flickity-slider').each( function() {
			var mh = 0;
			$(this).find('.slider-container').css('height', 'auto');
			$(this).find('.slider-container').each( function() {
				if( $(this).height() > mh ) {
					mh = $(this).height();
				}
			} );
			$(this).find('.slider-container').css('height', mh+'px');
		} );


	}
	fixFlicktyInner();


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