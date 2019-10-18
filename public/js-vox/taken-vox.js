$(document).ready(function(){

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
                1120: {
                  slidesPerView: 2,
                },
            },
            // autoplay: {
            //     delay: 5000,
            // },
            resizeReInit: true,
        });
    } else {
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

    $('video')[0].play();
});