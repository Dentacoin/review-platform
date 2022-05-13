var slider = null;
var sliderTO = null;
var displaySuggestions;
var sendSuggestions;
var handlePopups;

jQuery(document).ready(function($){

	//set height and width to the slider
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
		
		//button prev slide
		$('.slider-left').click( function(e) {
			e.preventDefault();

			$('.slider-right').addClass('active');
			var scroll = $('.index-slider');
			var place = (scroll.find('.slider-wrapper').outerWidth(true));
			var left = parseFloat( scroll.css('left') ) + place;
			// var newleft = Math.ceil(left / place) * place;

			if(scroll.offset().left > -310) {
				$(this).removeClass('active');
			}

			scroll.animate({
				left:Math.min( 0, left)
			}, 300);
		});

		//button next slide
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

	//load page down sections on sroll (google page speed)
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



	//for logged users
	if(user_id) {

		$.getScript(window.location.origin+'/js-trp/address.js', function() {
		});

		$('.address-suggester-input').click( function() {
			$.getScript('https://maps.googleapis.com/maps/api/js?key=AIzaSyCaVeHq_LOhQndssbmw-aDnlMwUG73yCdk&libraries=places&callback=initMap&language=en', function() {
			});
		});

		$('.invite-new-dentist-form .address-suggester-input').focus(function(e) {
			$('.invite-new-dentist-form .button').addClass('disabled');
		});

		$('.invite-new-dentist-form').submit( function(e) {
			e.preventDefault();

			if (!$(this).find('.button').hasClass('disabled')) {

				$(this).find('.ajax-alert').remove();
				$(this).find('.alert').hide();
				$(this).find('.has-error').removeClass('has-error');

				if(ajax_is_running) {
					return;
				}
				ajax_is_running = true;

				var that = $(this);

				$.post( 
					$(this).attr('action'), 
					$(this).serialize() , 
					(function( data ) {
						if(data.success) {
							
							that[0].reset();
							that.find('.suggester-map-div').hide();
							that.find('.alert-success').html('success').show();
							// showPopup( 'invite-new-dentist-success-popup', data);

							gtag('event', 'Invite', {
								'event_category': 'InviteDentist',
								'event_label': 'InvitedDentists',
							});

						} else {
							for(var i in data.messages) {
								$('[name="'+i+'"]').closest('.alert-after').after('<div class="alert alert-warning ajax-alert" error="'+i+'">'+data.messages[i]+'</div>');
								$('[name="'+i+'"]').addClass('has-error');
								if ($('[name="'+i+'"]').closest('.modern-radios').length) {
									$('[name="'+i+'"]').closest('.modern-radios').addClass('has-error');
								}
							}
							$('.popup').animate({
								scrollTop: $('.has-error').first().offset().top
							}, 500);
						}
						ajax_is_running = false;
					}).bind(that), "json"
				);
				return false;
			}
		} );
	}
});