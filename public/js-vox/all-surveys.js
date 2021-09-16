var VoxTest = {};
var handleSwiper;
var sendReCaptcha;
var recaptchaCode = null;
var sendValidation;
var preloadImages;
var skip = 0;
var vox_id;
var question_id;

$(document).ready(function(){

    if ($('.mobile-bubble-effect').length && $('.mobile-person-effect').length && window.innerWidth < 768) {
        preloadImages([
            $('.mobile-bubble-effect').attr('src'),
            $('.mobile-person-effect').attr('src'),
        ], function(){
            $('.mobile-welcome-images img').each (function() {
                $(this).addClass('effect-loaded');
            });
        });
    }

    handleSwiper = function() {
        if (window.innerWidth > 768) {

            var swiper_done = new Swiper('.swiper-container', {
                slidesPerView: 3,
                slidesPerGroup: 3,
                spaceBetween: 0,
                pagination: {
                    el: '.swiper-pagination',
                    clickable: true,
                },
                breakpoints: {
                    1130: {
                        slidesPerView: 2,
                    },
                    768: {
                        slidesPerView: 1,
                    },
                },
                autoplay: {
                    delay: 5000,
                },
            });
        } else {
            var swiper_done = new Swiper('.swiper-container', {
                slidesPerView: 1,
                slidesPerGroup: 1,
                spaceBetween: 0,
                pagination: {
                    el: '.swiper-pagination',
                    clickable: true,
                },
                effect: 'coverflow',
                grabCursor: true,
                centeredSlides: true,
                coverflowEffect: {
                    rotate: 50,
                    stretch: 0,
                    depth: 100,
                    modifier: 1,
                    slideShadows : false,
                },
            });
        }
    }

    if ($('.swiper-container').length && typeof Swiper !== 'undefined' ) {
        handleSwiper();
    }

    if(typeof(vox)!='undefined') {
        VoxTest.handleNextQuestion();
    }

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