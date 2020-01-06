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

        $.post( 
            $(this).attr('action'), 
            $(this).serialize() , 
            function( data ) {
                if(data.success) {
                	window.location.href = data.url;
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
    });

    $('.magnet-validator').click( function() {
    		
	    if($(this).closest('.answer-radios-magnet').find('input:checked').length) {
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
