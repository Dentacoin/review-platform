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
});
