var VoxTest = {};
var handleSwiper;
var sendReCaptcha;
var recaptchaCode = null;
var sendValidation;
var preloadImages;
var checkFilledDots;
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
                    900: {
                      slidesPerView: 2,
                    },
                },
                autoplay: {
                    delay: 5000,
                },
            });
        } else {
            var swiper_done = new Swiper('.swiper-container', {
                slidesPerView: 1,
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
    
    checkFilledDots = function( event, index) {
		var goods = new Array;
		var flickity = $('.flickity:visible');
		var missing = false;
		if( flickity.length ) {
			flickity.find('.answer-radios-group').each( function() {
	            if( $(this).find('.answer-radio.active-label').length ) {
	                goods.push(true);
	            } else {
	                goods.push(false);
	                missing = true;
	            }
	        } );
	        var i=0;
	        flickity.find('.flickity-page-dots .dot').each( function() {
	            if(goods[i]) {
	                $(this).addClass('filled');
	            } else {
	                $(this).removeClass('filled');
	            }
	            i++;
	        } );

	        if(!missing) {
				$('.question-group:visible .next-answer').show().trigger('click');
	        }
		}
	}

    if($('#to-append-public').length) {
        console.log('dsfdf');

        $(window).scroll( function() {
            if (!$('#to-append-public').hasClass('appended')) {
                $.ajax({
                    type: "GET",
                    url: lang + '/vox-public-down/',
                    success: function(ret) {
                        if (!$('#to-append-public').hasClass('appended')) {
                            $('#to-append-public').append(ret);
                            $('#to-append-public').addClass('appended');

                            handleSwiper();
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