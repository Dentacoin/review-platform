var ajax_is_running = false;

jQuery(document).ready(function($){

	$('.signin-form-wrapper form').submit( function(e) {
		e.preventDefault();

		//if no connection with dcn server
		if(dentacoin_down && !user_id) {
    		e.stopImmediatePropagation();
    		showPopup('failed-popup');
    	} else {
			//open register popup
			$.event.trigger({type: 'openDentistRegister'});

			var that = $(this);
			
			//add fields to register popup form
			$(document).on('dentacoinLoginGatewayLoaded', function (event) {
				$('#dentist-register-email').val( that.find('input[name="email"]').val() );
				$('#dentist-register-email').addClass('gateway-platform-border-color-important');
				$('label[for="dentist-register-email"]').addClass('active-label gateway-platform-color-important');

				$('#dentist-register-password').val( that.find('input[name="password"]').val() );
				$('#dentist-register-password').addClass('gateway-platform-border-color-important');
				$('label[for="dentist-register-password"]').addClass('active-label gateway-platform-color-important');

				$('#dentist-register-repeat-password').val( that.find('input[name="password-repeat"]').val() );
				$('#dentist-register-repeat-password').addClass('gateway-platform-border-color-important');
				$('label[for="dentist-register-repeat-password"]').addClass('active-label gateway-platform-color-important');

	        	$('.form-register .next-step[data-current-step="first"]').trigger('click');
	        });
    	}
    });

	//load page down sections on sroll (google page speed)
	$(window).scroll( function() {
		if (!$('#to-append').hasClass('appended')) {

			$('#to-append').addClass('appended');

			$.ajax({
	            type: "POST",
	            url: lang + '/index-dentist-down/',
	            success: function(ret) {

					$.getScript(window.location.origin+'/js/flickity.min.js');
					$('head').append('<link rel="stylesheet" type="text/css" href="'+window.location.origin+'/css/flickity.min.css">');
					$('#to-append').append(ret);
					
					handlePopups();
					
					var $carousel = $('.flickity-testimonial');

					$('.testimonial img').on('load', function() {
						$carousel.flickity({
							autoPlay: true,
							wrapAround: true,
							cellAlign: 'left',
							pageDots: false,
							groupCells: 1,
							adaptiveHeight: true,
						});
					});

					setTimeout( function() {
						$carousel.flickity({
							autoPlay: true,
							wrapAround: true,
							cellAlign: 'left',
							pageDots: false,
							groupCells: 1,
							adaptiveHeight: true,
						});
					}, 1000);
	            },
	            error: function(ret) {
	                console.log('error');
	            }
	        });
		}
	});
});