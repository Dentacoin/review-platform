jQuery(document).ready(function($){

	$('.search-dentists').click( function() {
		$('.search-results-popup').addClass('active');
	});

	$('.search-results-popup').click( function(e) {
		if( !$(e.target).closest('.search-form').length ) {
			$('.search-results-popup').removeClass('active');
		}
	});

    //INDEX PAGE

    $.getScript(window.location.origin+'/js-trp/address.js');

    $('.address-suggester-input').click( function() {
        $.getScript('https://maps.googleapis.com/maps/api/js?key=AIzaSyCaVeHq_LOhQndssbmw-aDnlMwUG73yCdk&libraries=places&callback=initMap&language=en');
    });

    $('.invite-new-dentist-form').submit( function(e) {
        e.preventDefault();

        if (!$(this).find('.button').hasClass('disabled')) {

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

                        that.find('.blue-button').removeClass('waiting');

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
    });

    $('.invite-new-dentist-again').click( function() {
        $('.success-invited-dentist').hide();
        $('.invite-new-dentist-form').show();
    });

    //END INDEX PAGE
});