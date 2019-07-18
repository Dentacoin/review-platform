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

    $('#claim-profile-form').submit( function(e) {
        e.preventDefault();

        if(ajax_is_running) {
            return;
        }
        ajax_is_running = true;

        $(this).find('.ajax-alert').remove();
        $(this).find('.alert').hide();
        $(this).find('.has-error').removeClass('has-error');

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

				$('#claim-popup').addClass('claimed');
				
			} else {
				for(var i in data.messages) {
                    $(this).find('[name="'+i+'"]').closest('.alert-after').after('<div class="alert alert-warning ajax-alert">'+data.messages[i]+'</div>');

                    $(this).find('[name="'+i+'"]').addClass('has-error');

                    if ($(this).find('[name="'+i+'"]').closest('.modern-file').length) {
                        $(this).find('[name="'+i+'"]').closest('.modern-file').addClass('has-error');
                    }
                }
                $('.popup').animate({
	                scrollTop: $('.ajax-alert:visible').first().offset().top
	            }, 500);
			}
            ajax_is_running = false;

	    }).bind(this) ).fail(function (data) {
			$(this).find('.alert-warning').show();
	    });

	    return;
    } );


    $('#claim-proof-file').change( function() {
    	$(this).closest('label').find('.file-name').html( $('#claim-proof-file')[0].files.item(0).name );
    } )

    $('.claimed-ok').click( function() {
		closePopup();
	});

	$('#claim-proof-file').change( function() {
		if ($(this).val()) {
			$(this).closest('.modern-field').addClass('active');
		}
	});
    
});
