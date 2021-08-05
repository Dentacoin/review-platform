var ajax_is_running = false;
jQuery(document).ready(function($){

	$('.signin-form-wrapper form').submit( function(e) {
		e.preventDefault();

		if(dentacoin_down && !user_id) {
    		e.stopImmediatePropagation();
    		showPopup('failed-popup');
    	} else {
			$.event.trigger({type: 'openDentistRegister'});

			var that = $(this);
			
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
		
    } );

    if( $('#dentist-email').val() ) {

    	if(dentacoin_down && !user_id) {
    		showPopup('failed-popup');
    	} else {
			$.event.trigger({type: 'openDentistRegister'});
		}
    }

	$(window).scroll( function() {
		if (!$('#to-append').hasClass('appended')) {
			$.ajax({
	            type: "POST",
	            url: lang + '/index-dentist-down/',
	            success: function(ret) {
	            	if (!$('#to-append').hasClass('appended')) {
	            		$.getScript(window.location.origin+'/js/flickity.min.js');
	            		$('head').append('<link rel="stylesheet" type="text/css" href="'+window.location.origin+'/css/flickity.min.css">');
	            		$('#to-append').append(ret);
	            		$('#to-append').addClass('appended');

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

						if ($(window).innerWidth() < 768) {
							$('.mobile-flickity .left').children().appendTo('.mobile-flickity');
							$('.mobile-flickity .left').remove();
							$('.mobile-flickity .right').children().appendTo('.mobile-flickity');
							$('.mobile-flickity .right').remove();

							setTimeout( function() {
		            			var $slider = $('.mobile-flickity');
								$slider.flickity({
							    	//autoPlay: true,
									wrapAround: true,
									cellAlign: 'left',
									pageDots: true,
									prevNextButtons: false,
									groupCells: 1,
									adaptiveHeight: true,
								});
							}, 1000);
						}
	            	}
	                
	            },
	            error: function(ret) {
	                console.log('error');
	            }
	        });
		}
	});
    
});
