var loadMapScript;
var map_loaded = false;

$(document).ready(function() {
    
    $('.clinic_address.address-suggester-input').removeAttr('placeholder');

	$('.country-select').change( function() {
    	$(this).closest('form').find('input[name="address"]').val('');
    	$(this).closest('form').find('.suggester-map-div').hide();
    	$(this).closest('form').find('.geoip-confirmation').hide();
        $(this).closest('form').find('.geoip-hint').hide();
        $(this).closest('form').find('.different-country-hint').hide();

		if(ajax_is_running) {
			return;
		}
		ajax_is_running = true;

    	var that = this;

		$.ajax( {
			url: '/cities/' + $(this).val()+'/',
			type: 'GET',
			dataType: 'json',
			success: function( data ) {
			    $('.phone-code-holder').html(data.code);
			    ajax_is_running = false;
				//city_select
				//$('#modal-message .modal-body').html(data);
				$(that).trigger('changed');
			},
			error: function(data) {
				console.log(data);
			    ajax_is_running = false;
			}
		});
    });

    $('.next-branch-button').click( function() {
        if(ajax_is_running) {
            return;
        }
        ajax_is_running = true;

        var that = $(this);

        $('.ajax-alert').remove();

        $.post( 
            $(this).attr('branch-url'), 
            $('.add-new-branch-form').serialize(), 
            function( data ) {
                if(data.success) {
                    $('.branch-content').hide();
                    $('#branch-option-'+that.attr('to-step')).show();
                } else {
                    for(var i in data.messages) {
                        $('[name="'+i+'"]').addClass('has-error');
                        $('[name="'+i+'"]').closest('.alert-after').after('<div class="alert alert-warning ajax-alert">'+data.messages[i]+'</div>');
                    }
                }
                ajax_is_running = false;
            }, 
            "json"
        );
    });
    
    $('.add-new-branch-form').submit( function(e) {
        e.preventDefault();

        if(ajax_is_running) {
            return;
        }
        ajax_is_running = true;

        var that = $(this);

        $('.ajax-alert').remove();
        $(this).find('.submit-branch-button').addClass('waiting');
        
        $.post( 
            $(this).attr('action'), 
            $(this).serialize() , 
            (function( data ) {
                that.find('.submit-branch-button').removeClass('waiting');
                if(data.success) {
                	window.location.href = data.url;
                } else {
                    $('.last-step-flex').after('<div class="alert alert-warning ajax-alert"></div>');
                    for(var i in data.messages) {
                        $('.add-new-branch-form .ajax-alert').append(data.messages[i] + '<br/>');

                        $('[name="'+i+'"]').addClass('has-error');
                    }
                }
                ajax_is_running = false;
            }).bind(this), "json"
        );          
    });

    $('.prev-branch-button').click( function() {
        $('.branch-content').hide();
        $('#branch-option-'+$(this).attr('to-step')).show();

        if ($('#clinic_address').length && $('#clinic_address').val()) {
            $('#clinic_address').blur();
        }
    });
});