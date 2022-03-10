var slider = null;
var sliderTO = null;
var displaySuggestions;
var sendSuggestions;
var handlePopups;

jQuery(document).ready(function($){

	if($('.slider-wrapper').length) {
		var max_h = 0;
		var width = 0;
		$('.slider-wrapper').each( function() {
			width+=$(this).outerWidth();
			if($(this).outerHeight() > max_h) {
				max_h = $(this).outerHeight();
			}
		});

		$('.slider-wrapper').each( function() {
			$(this).css('height', max_h);
		});

		$('.index-slider').css('width', width + ($('.slider-wrapper').length * 40));

		$('.slider-right').click( function(e) {
			e.preventDefault();

			var scroll = $('.index-slider');
			var place = (scroll.find('.slider-wrapper').outerWidth(true));
			var left = parseFloat( scroll.css('left') ) - place;
			var newleft = Math.ceil(left / place) * place;
			scroll.animate({
				left:Math.max( -(scroll.outerWidth() - scroll.parent().width()) , newleft)
			}, 300);
		});

		$('.slider-left').click( function() {
			var scroll = $('.index-slider');
			var place = (scroll.find('.slider-wrapper').outerWidth(true));
			var left = parseFloat( scroll.css('left') ) + place;
			// var newleft = Math.ceil(left / place) * place;
			scroll.animate({
				left:Math.min( 0, left)
			}, 300);
		});
	}

	// if( window.location.hash == '#invite-form' ) {
    //     $('html, body').animate({
    //         scrollTop: $(".invite-new-dentist-wrapper").offset().top - $('.header').height()
    //     }, 500);
	// }

	$(window).scroll( function() {
		if (!$('#to-append').hasClass('appended')) {
			$('#to-append').addClass('appended');
			$.ajax({
	            type: "POST",
	            url: lang + '/index-down/',
	            success: function(ret) {
					$('#to-append').append(ret);

					if($('.to-append-image').length) {
						$('.to-append-image').append('<img src="'+$('.to-append-image').attr('data-src')+'"/>');
					}

					handlePopups();	                
	            },
	            error: function(ret) {
	                console.log('error');
	            }
	        });
		}
	});
});