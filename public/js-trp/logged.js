jQuery(document).ready(function($){

	$('.search-dentists').click( function() {
		$('.search-results-popup').addClass('active');
	});

	$('.search-results-popup').click( function(e) {
		if( !$(e.target).closest('.search-form').length ) {
			$('.search-results-popup').removeClass('active');
		}
	});

    $('.caret-switch').click( function(e) {
        e.preventDefault();
        e.stopPropagation();

        if($('.user-balance-dcn').is(':visible')) {
            $('.user-balance-dcn').hide();
            $('.user-balance-usd').show();
        } else {
            $('.user-balance-dcn').show();
            $('.user-balance-usd').hide();
        }
    });
});