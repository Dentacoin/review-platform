jQuery(document).ready(function($){

	$('.signin-form-wrapper form').submit( function(e) {
		e.preventDefault();
		showPopup('popup-register');
		$('.switch-forms').first().click();
		$('#dentist-name').val( $(this).find('input[name="name"]').val() );
		$('#dentist-name_alternative').val( $(this).find('input[name="name_alternative"]').val() );
		$('#dentist-email').val( $(this).find('input[name="email"]').val() );
		$('#dentist-password').val( $(this).find('input[name="password"]').val() );
		$('#dentist-password-repeat').val( $(this).find('input[name="password"]').val() );
		$('#agree-privacyy').prop('checked', $(this).find('input[name="agree"]').prop('checked') );
		if( $(this).find('input[name="agree"]').prop('checked') ) {
			$('#agree-privacyy').closest('.checkbox-label').addClass('active');
		} else {
			$('#agree-privacyy').closest('.checkbox-label').removeClass('active');			
		}
		prepareLoginFucntion( function() {
			$('.go-to-next:visible').click();
		});
		
    } );

    if( $('#agree-privacyy-dentist:checked').length ) {
		showPopup('popup-register');
    }
});
