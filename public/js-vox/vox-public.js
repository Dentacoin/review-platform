var handleSwiper;

$(document).ready(function(){

    if($('#to-append-public').length) {
        $(window).scroll( function() {
            if (!$('#to-append-public').hasClass('appended')) {
                $('#to-remove-public').remove();
                $.ajax({
                    type: "POST",
                    url: lang + '/vox-public-down/',
                    success: function(ret) {
                        if (!$('#to-append-public').hasClass('appended')) {

                            $('head').append('<link rel="stylesheet" type="text/css" href="'+window.location.origin+'/css/swiper.min.css">');
                            $('#to-append-public').append(ret);
                            $('#to-append-public').addClass('appended');

                            $.getScript(window.location.origin+'/js/swiper.min.js', function() {
                                handleSwiper();
                                doubleCoinTooltip();

                                $(window).scroll( function(e) {
                                    if(!$('.make-money-wrapper img').hasClass('animation-activated') && $(window).scrollTop() + $(window).height() / 2 > $('.make-money-wrapper').offset().top) {
                                        $('.make-money-wrapper img').addClass('animation-activated');
                                    }
                                });
                            });
                        }
                    },
                    error: function(ret) {
                        console.log('error');
                    }
                });
            }
        });
    }
});