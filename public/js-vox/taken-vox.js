$(document).ready(function(){

    if( typeof Swiper !== 'undefined') {
        
        if (window.innerWidth > 768) {

            var swiper = new Swiper('.swiper-container', {
                slidesPerView: 3,
                slidesPerGroup: 3,
                spaceBetween: 0,
                pagination: {
                    el: '.swiper-pagination',
                    clickable: true,
                },
                breakpoints: {
                    1200: {
                      slidesPerView: 2,
                    },
                },
                // autoplay: {
                //     delay: 5000,
                // },
                resizeReInit: true,
            });

        } else {

            if ($('.swipe-cont').length) {
                $('.swipe-cont').addClass('swiper-container');
                $('.swipe-cont').find('.swiper-wrapper').removeClass('flex');
            }

            var swiper = new Swiper('.swiper-container', {
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
                resizeReInit: true,
            });
        }
    }
});