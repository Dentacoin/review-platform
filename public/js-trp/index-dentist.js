var ajax_is_running = false;
var modernFieldsUpdate;
jQuery(document).ready(function($){

	$('.signin-form-wrapper form').submit( function(e) {
		e.preventDefault();
		showPopup('popup-register');
		modernFieldsUpdate();
		$('.switch-forms').first().click();
		$('#dentist-email').val( $(this).find('input[name="email"]').val() );
		$('#dentist-password').val( $(this).find('input[name="password"]').val() );
		$('#dentist-password-repeat').val( $(this).find('input[name="password-repeat"]').val() );
		prepareLoginFucntion( function() {
			$('.go-to-next:visible').click();
		});
		
    } );

    if( $('#dentist-email').val() ) {
		showPopup('popup-register');
    }
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

		$('.mobile-flickity').flickity({
	    	//autoPlay: true,
			wrapAround: true,
			cellAlign: 'left',
			pageDots: true,
			prevNextButtons: false,
			groupCells: 1,
			adaptiveHeight: true,
		});
	}

	$('.lead-magnet-form-step1').submit( function(e) {
        e.preventDefault();

        if(ajax_is_running) {
            return;
        }

        ajax_is_running = true;

        var that = $(this);

        $.post( 
            $(this).attr('action'), 
            $(this).serialize() , 
            function( data ) {
                if(data.success) {

                	that.closest('.magnet-content').next().show();
                	that.closest('.magnet-content').hide();

                	that.closest('.popup-inner').find('.colorful-tabs').find('.col').removeClass('active');
                	that.closest('.popup-inner').find('.colorful-tabs').find('.second-step').addClass('active');

                	var $carousel = $('.flickity-magnet');

					$carousel.flickity({
				    	//wrapAround: true,
						adaptiveHeight: true,
						draggable: false,
						pageDots: true,
					});

                } else {
                    that.find('.ajax-alert').remove();
                    for(var i in data.messages) {
                        // $('#register-error span').append(data.messages[i] + '<br/>');
                        that.find('[name="'+i+'"]').addClass('has-error');
                        that.find('[name="'+i+'"]').closest('.alert-after').after('<div class="alert alert-warning ajax-alert" error="'+i+'">'+data.messages[i]+'</div>'); 

                        if (that.find('[name="'+i+'"]').closest('.agree-label').length) {
                            that.find('[name="'+i+'"]').closest('.agree-label').addClass('has-error');
                       	}  
                    }
                }
                ajax_is_running = false;
            }, "json"
        );

        return false;
    } );

	$('.lead-magnet-form-step2').submit( function(e) {
        e.preventDefault();

        if(ajax_is_running) {
            return;
        }

        ajax_is_running = true;

        var that = $(this);
        $('.loader').fadeIn();
        $('.loader-mask').fadeIn();
        $('.loader-text').fadeIn();
        //$('#magnet-submit').append('<div class="loader"><i class="fas fa-circle-notch fa-spin fa-3x fa-fw"></i></div>');

        $.post( 
            $(this).attr('action'), 
            $(this).serialize() , 
            function( data ) {
                if(data.success) {
                    fbq('track', 'TRPMagnetComplete');

                    gtag('event', 'SeeScore', {
                        'event_category': 'LeadMagnet',
                        'event_label': 'ReplyToReviews',
                    });

                    setTimeout( function() {
                        window.location.href = data.url;
                    }, 8000);
                } else {
                	console.log('error');
                }
                ajax_is_running = false;
            }, "json"
        );

        return false;
    } );

    $('.lead-magnet-radio').change( function() {
    	$(this).closest('.answer-radios-magnet').find('label').removeClass('active');
    	$(this).closest('label').addClass('active');
    });

    $('.lead-magnet-checkbox').change( function() {
    	$(this).closest('label').toggleClass('active');

        if ($(this).hasClass('disabler')) {
            if ($(this).prop('checked')) {

                $(this).closest('.buttons-list').find('.lead-magnet-checkbox').not(this).prop('disabled', true);
                $(this).closest('.buttons-list').find('.lead-magnet-checkbox').not(this).prop('checked', false);
                $(this).closest('.buttons-list').find('.magnet-label:not(.disabler-label)').addClass('disabled-label');
                $(this).closest('.buttons-list').find('.magnet-label:not(.disabler-label)').removeClass('active');
            } else {
                $(this).closest('.buttons-list').find('.lead-magnet-checkbox').not(this).prop('disabled', false);
                $(this).closest('.buttons-list').find('.magnet-label:not(.disabler-label)').removeClass('disabled-label');
            }
        }
    });

    $('.lead-magnet-radio').click( function() {
        if ($(this).attr('name') == 'answer-1') {
            gtag('event', 'Next', {
                'event_category': 'LeadMagnet',
                'event_label': 'Priority',
            });
        } else if ($(this).attr('name') == 'answer-2') {
            gtag('event', 'Next', {
                'event_category': 'LeadMagnet',
                'event_label': 'Tool',
            });
        } else if ($(this).attr('name') == 'answer-4') {
            gtag('event', 'Next', {
                'event_category': 'LeadMagnet',
                'event_label': 'Frequency',
            });
        }

        if ($(this).attr('name') == 'answer-5') {
            $(this).closest('form').find('button').trigger('click');
        } else {

            $('.flickity-magnet').flickity('next');
        }

    });

    $('.magnet-validator').click( function() {
    		
	    if($(this).closest('.answer-radios-magnet').find('input:checked').length) {
            gtag('event', 'Next', {
                'event_category': 'LeadMagnet',
                'event_label': 'AskForReviews',
            });

	    	if ($(this).hasClass('validator-skip')) {
	    		if ($(this).closest('.answer-radios-magnet').find('input:checked').val() == '4') {
	    			$('.flickity-magnet').flickity( 'select', 4 );
	    		} else {
	    			$('.flickity-magnet').flickity('next');
	    		}
	    	} else {
	    		$('.flickity-magnet').flickity('next');
	    	}    		
    	} else {
    		$(this).closest('.flickity-viewport').css('height', $(this).closest('.flickity-viewport').height() + 76);
    		$(this).closest('.answer-radios-magnet').find('.alert-warning').show();
    	}

    });

    $('#open-magnet').click( function() {
        gtag('event', 'Open', {
            'event_category': 'LeadMagnet',
            'event_label': 'Popup',
        });
    });

    $('.first-form-button').click( function(e) {
        e.preventDefault();

        if(ajax_is_running) {
            return;
        }
        ajax_is_running = true;
        var that = $(this);
        $.post( 
            $(this).attr('data-validator'), 
            $('#lead-magnet-form-step2').serialize(), 
            function( data ) {
                if(data.success) {

                    that.closest('.magnet-content').next().show();
                    that.closest('.magnet-content').hide();

                    that.closest('.popup-inner').find('.colorful-tabs').find('.col').removeClass('active');
                    that.closest('.popup-inner').find('.colorful-tabs').find('.second-step').addClass('active');

                    var $carousel = $('.flickity-magnet');

                    $carousel.flickity({
                        //wrapAround: true,
                        adaptiveHeight: true,
                        draggable: false,
                        pageDots: true,
                    });

                    
                    $('#ariticform_input_leadmagnetform_practice_name').val( $('#magnet-name').val() );
                    $('#ariticform_input_leadmagnetform_website').val( $('#magnet-website').val() );
                    $('#ariticform_input_leadmagnetform_country').val( $('#magnet-country option:selected').text() );
                    $('#ariticform_input_leadmagnetform_email').val( $('#magnet-email').val() );
                    $('#ariticform_checkboxgrp_checkbox_gdpr_checkbox').prop('checked', true);

                    $('#ariticform_input_leadmagnetform_submit').trigger('click');

                    fbq('track', 'TRPMagnetStart');

                    gtag('event', 'RunTest', {
                        'event_category': 'LeadMagnet',
                        'event_label': 'ContactDetails',
                    });

                } else {
                    that.closest('form').find('.ajax-alert').remove();
                    for(var i in data.messages) {
                        that.closest('form').find('[name="'+i+'"]').addClass('has-error');
                        that.closest('form').find('[name="'+i+'"]').closest('.alert-after').after('<div class="alert alert-warning ajax-alert" error="'+i+'">'+data.messages[i]+'</div>'); 

                        if (that.closest('form').find('[name="'+i+'"]').closest('.agree-label').length) {
                            that.closest('form').find('[name="'+i+'"]').closest('.agree-label').addClass('has-error');
                        }  
                    }
                }
                ajax_is_running = false;
            }, 
            "json"
        );

    } );

    $('.magnet-popup').click( function() {
        var that = $(this);

        $.ajax({
            type: "GET",
            url: that.attr('data-url'),
            dataType: 'json',
            success: function(ret) {
                if(ret.session) {
                    window.location.href = ret.url;
                } else {
                    showPopup('popup-lead-magnet');
                }
            },
            error: function(ret) {
                console.log('error');
            }
        });
        
    });
    
});
