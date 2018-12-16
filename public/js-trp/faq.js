jQuery(document).ready(function($){

	$('.question .question-title').click( function() {
        if ($(this).closest('.question').hasClass('active')) {
            $('.question').removeClass('active');
        } else {
            $('.question').removeClass('active');
            $(this).closest('.question').addClass('active');
        }
    } );
});
