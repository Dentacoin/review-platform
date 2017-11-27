$(document).ready(function(){

	var shower = function(e){
	    $('#register-div').show();
	    $(this).closest('.btn-group').find('.btn').removeClass('btn-primary').addClass('btn-default');
	    $(this).closest('.btn').addClass('btn-primary').removeClass('btn-default');
	    $(this).closest('.btn').blur();
	    e.stopPropagation();
	};

	$('.register-type').change( shower );
	$('label.btn').click( shower );

	$('.register-social').click( function(e) {
		e.preventDefault();
		window.location.href = $(this).attr('href') + '/' + $('.register-type:checked').val();
	});



	$('#register-form').submit( function(e) {
		e.preventDefault();

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
                	if(data.goon) {
                		$('#phone-verify').show()
                		$('#captcha-group').hide()
                		$('#submit-group').hide()
                		$('#register-action').val('confirm');
                	} else {
                		window.location.href = data.url;
                	}
                } else {
					$('#register-error').show();
					$('#register-error span').html('');
					for(var i in data.messages) {
						$('#register-error span').append(data.messages[i] + '<br/>');
						$('input[name="'+i+'"]').closest('.form-group').addClass('has-error');
					}

	                $('html, body').animate({
	                	scrollTop: $('#register-error').offset().top - 60
	                }, 500);
	                grecaptcha.reset();
                }
                ajax_is_running = false;
            }).bind(that), "json"
        );			


        return false;

    } );

});