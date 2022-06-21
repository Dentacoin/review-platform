var handleClickToOpenPopups;

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
	if($('#to-append').length) {

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
	
						handleClickToOpenPopups();	                
					},
					error: function(ret) {
						console.log('error');
					}
				});
			}
		});
	}

	//for logged users
	if(user_id) {

		$.getScript(window.location.origin+'/js-trp/address.js');

		$('.address-suggester-input').click( function() {
			$.getScript('https://maps.googleapis.com/maps/api/js?key=AIzaSyCaVeHq_LOhQndssbmw-aDnlMwUG73yCdk&libraries=places&callback=initMap&language=en');
		});

		$('.invite-new-dentist-form').submit( function(e) {
			e.preventDefault();

			$(this).find('.ajax-alert').remove();
			$(this).find('.alert').hide();
			$(this).find('.has-error').removeClass('has-error');
			$(this).find('.blue-button').addClass('waiting');

			if(ajax_is_running) {
				return;
			}
			ajax_is_running = true;

			var that = $(this);

			$.post( 
				$(this).attr('action'), 
				$(this).serialize() , 
				(function( data ) {
					
					that.find('.blue-button').removeClass('waiting');

					if(data.success) {
						that[0].reset();
						that.find('.suggester-map-div').hide();
						that.hide();
						that.find('.mode-dentist-clinic label').removeClass('active');
						that.find('.modern-field').removeClass('active');
						that.find('.blue-button').removeClass('waiting');
						$('.success-invited-dentist').find('.d-name').html(data.dentist_name);
						$('.success-invited-dentist').show();

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
		});

		$('.invite-new-dentist-again').click( function() {
			$('.success-invited-dentist').hide();
			$('.invite-new-dentist-form').show();
		});
	}
});