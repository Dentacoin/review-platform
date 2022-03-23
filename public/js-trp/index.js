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

		$('.index-slider').css('width', width + (
			$('.slider-wrapper').length * (
				parseInt($('.slider-wrapper').css('margin-left')) + parseInt($('.slider-wrapper').css('margin-right'))
			) 
		));

		$('.slider-left').click( function(e) {
			e.preventDefault();

			$('.slider-right').addClass('active');
			var scroll = $('.index-slider');
			var place = (scroll.find('.slider-wrapper').outerWidth(true));
			var left = parseFloat( scroll.css('left') ) + place;
			// var newleft = Math.ceil(left / place) * place;

			console.log(scroll.offset().left);

			if(scroll.offset().left > -310) {
				$(this).removeClass('active');
			}

			scroll.animate({
				left:Math.min( 0, left)
			}, 300);
		});

		$('.slider-right').click( function(e) {
			e.preventDefault();

			$('.slider-left').addClass('active');
			var scroll = $('.index-slider');
			var place = (scroll.find('.slider-wrapper').outerWidth(true));
			var left = parseFloat( scroll.css('left') ) - place;
			var newleft = Math.ceil(left / place) * place;

			console.log(scroll.offset().left);
			if(scroll.offset().left < -2800) {
				$(this).removeClass('active');
			}

			scroll.animate({
				left:Math.max( -(scroll.outerWidth() - scroll.parent().width()) , newleft)
			}, 300);
		});
	}

	$('.scroll-to-search').click( function() {
		$('html, body').animate({
			scrollTop: 0
		}, 500);
	});

	$(window).scroll( function() {
		if (!$('#to-append').hasClass('appended')) {
			$('#to-append').addClass('appended');
			$.ajax({
	            type: "POST",
	            url: lang + '/index-down/',
	            success: function(ret) {
					$('#to-append').append(ret);

					if($('.to-append-image').length) {
						$('.to-append-image').each( function() {
							$(this).append('<img src="'+$(this).attr('data-src')+'" alt="'+$(this).attr('data-alt')+'"/>');
						});
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