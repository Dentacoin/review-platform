
$(document).ready(function() {
    
    var hasNumber = function(myString) {
        return /\d/.test(myString);
    }
    var hasLowerCase = function(str) {
        return (/[a-z]/.test(str));
    }
    var hasUpperCase = function(str) {
        return (/[A-Z]/.test(str));
    }
    var validatePassword = function(password) {
        return password.trim().length >= 8 && password.trim().length <= 30 && hasLowerCase(password) && hasUpperCase(password) && hasNumber(password);
    }

    $('#claim-profile-form').submit( function(e) {
        e.preventDefault();

        if(ajax_is_running) {
            return;
        }
        ajax_is_running = true;

        $(this).find('.ajax-alert').remove();
        $(this).find('.alert').hide();
        $(this).find('.has-error').removeClass('has-error');

        if($('#claim-password').val() && !validatePassword($('#claim-password').val()) ) {

        	$('#password-validator').show();
        	ajax_is_running = false;
        	return;
        }

        var formData = new FormData(this);

        $.ajax({
	        url: $(this).attr('action'),
	        type: 'POST',
	        data: formData,
	        cache: false,
	        contentType: false,
	        processData: false
	    }).done( (function (data) {
			if(data.success) {
				if (data.reload) {

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

					window.location.reload();
				} else {
					$('#claim-popup').addClass('claimed');
				}
				
			} else {

				if(data.messages) {
					for(var i in data.messages) {
	                    $(this).find('[name="'+i+'"]').closest('.alert-after').after('<div class="alert alert-warning ajax-alert">'+data.messages[i]+'</div>');

	                    $(this).find('[name="'+i+'"]').addClass('has-error');

	                    if ($(this).find('[name="'+i+'"]').closest('.modern-file').length) {
	                        $(this).find('[name="'+i+'"]').closest('.modern-file').addClass('has-error');
	                    }

	                    if ($(this).find('[name="'+i+'"]').closest('.agree-label').length) {
	                        $(this).find('[name="'+i+'"]').closest('.agree-label').addClass('has-error');
	                        $(this).find('[name="'+i+'"]').closest('.agree-label').after('<div class="alert alert-warning ajax-alert">'+data.messages[i]+'</div>');
	                    }
	                }
	                $('.popup').animate({
		                scrollTop: $('.ajax-alert:visible').first().offset().top
		            }, 500);
				} else {
					$('#claim-err').html(data.message).show();
				}
			}
            ajax_is_running = false;

	    }).bind(this) ).fail(function (data) {
			$('#claim-err').show();
	    });

	    return;
    });
});