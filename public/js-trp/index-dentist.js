jQuery(document).ready(function($){

	$('.signin-form-wrapper form').submit( function(e) {
		e.preventDefault();
		showPopup('popup-register');
		$('.switch-forms').first().click();
		$('#dentist-name').val( $(this).find('input[name="name"]').val() );
		$('#dentist-email').val( $(this).find('input[name="email"]').val() );
		$('#dentist-password').val( $(this).find('input[name="password"]').val() );
		$('#dentist-password-repeat').val( $(this).find('input[name="password"]').val() );
		prepareLoginFucntion( function() {
			$('.go-to-next:visible').click();
		});
		
    } );
});
