jQuery(document).ready(function($){

	$('.question .question-title').click( function() {
        if ($(this).closest('.question').hasClass('active')) {
            $('.question').removeClass('active');
        } else {
            $('.question').removeClass('active');
            $(this).closest('.question').addClass('active');
        }
    });

    $('.faq-section').click( function() {
        $('.faq-section').removeClass('active');
        $(this).addClass('active');
        $('.questions').removeClass('active');
        $('.'+$(this).attr('id')).addClass('active');

        if($(window).outerWidth() <= 768) {
            $('html, body').animate({
                scrollTop: $('.questions.active').offset().top
            }, 300);

            let left = 0;
            let prev = $(this).prev();
            while(prev.length) {
                left += prev.outerWidth();
                prev = prev.prev();
            }

            $('.faq-sections-title').animate({
                scrollLeft: left
            }, 500);
        }
    });
});
