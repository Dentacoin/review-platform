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
    
});
