jQuery(document).ready(function($){

    $('#magnet-website').on('keyup keydown', function() {
        $(this).val($(this).val().toLowerCase());
    });

    if($(window).outerWidth() < 999) { //tablet
        $(window).on('scroll', function() {
            if ( ($('header').outerHeight() - 40 < $(window).scrollTop()) ) {
                $('.lead-magnet-image').addClass('hide-image');
            } else {
                $('.lead-magnet-image').removeClass('hide-image');
            }
        });
    }

    $('.lead-magnet-form-survey').submit( function(e) {
        e.preventDefault();

        if(ajax_is_running) {
            return;
        }

        ajax_is_running = true;

        let that = $(this);
        let active_step = that.closest('.popup-inner').find('.step-tabs').find('.step.active');
        active_step.removeClass('active').addClass('completed');
        active_step.next().addClass('active');

        //show loader
        $('.loader-lead-magnet').fadeIn();
        $('.loader-lead-magnet video')[0].play();
        $('.loader-lead-magnet video')[0].removeAttribute("controls");

        setTimeout( function() {
            $.post( 
                that.attr('action'), 
                that.serialize() , 
                function( data ) {
                    if(data.success) {
                        fbq('track', 'TRPMagnetComplete');
                        
                        gtag('event', 'SeeScore', {
                            'event_category': 'LeadMagnet',
                            'event_label': 'ReplyToReviews',
                        });
                        
                        window.location.reload();
                    } else {
                        console.log('error');
                    }
                    ajax_is_running = false;
                }, "json"
            );
        }, 5000);
    });

    $('.lead-magnet-radio').change( function() {
        $(this).closest('.answer-radios-magnet').find('label').removeClass('active');
        $(this).closest('label').addClass('active');
    });

    $('.lead-magnet-checkbox').change( function() {
        $(this).closest('label').toggleClass('active');

        if ($(this).hasClass('disabler')) {
            //disable checkbox options
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
                //skip next question
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

    $('.magnet-user-info-button').click( function(e) {
        e.preventDefault();

        if(ajax_is_running) {
            return;
        }
        ajax_is_running = true;
        var that = $(this);

        $.post( 
            $(this).attr('data-validator'), 
            $('#lead-magnet-form-survey').serialize(), 
            function( data ) {
                if(data.success) {

                    //accept cookies
                    if (!Cookies.get('performance_cookies')) {
                        basic.cookies.set('performance_cookies', 1);
                    }
                    if (!Cookies.get('functionality_cookies')) {
                        basic.cookies.set('functionality_cookies', 1);
                    }
                    if (!Cookies.get('strictly_necessary_policy')) {
                        basic.cookies.set('strictly_necessary_policy', 1);
                    }

                    if ($('.dcn-privacy-policy-cookie').length) {
                        $('.dcn-privacy-policy-cookie').remove();
                    }

                    that.closest('.magnet-content').next().show();
                    that.closest('.magnet-content').hide();

                    //show next step
                    let active_step = that.closest('.popup-inner').find('.step-tabs').find('.step.active');
                    active_step.removeClass('active').addClass('completed');
                    active_step.next().addClass('active');

                    //load flickity
                    var $carousel = $('.flickity-magnet');
                    $carousel.flickity({
                        //wrapAround: true,
                        adaptiveHeight: true,
                        draggable: false,
                        pageDots: true,
                    });

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
    });
});