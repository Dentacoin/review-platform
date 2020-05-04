$(document).ready(function(){

    if ($('.swiper-container').length) {

        if (window.innerWidth > 768) {

            var swiper = new Swiper('.swiper-container', {
                slidesPerView: 3,
                slidesPerGroup: 3,
                spaceBetween: 0,
            });
        } else {
            var swiper = new Swiper('.swiper-container', {
                slidesPerView: 1,
                spaceBetween: 0,
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

    //Bans
    if( $('.popup.banned').length ) {
        hoursCountdown();
    }

    if ($('body').hasClass('sp-vox-iframe')) {

        var content_heigth = $('.popup.active').length ? $('.popup.active').height() + $('.site-content').height() : $('.site-content').height();
        
        function triggerIframeSizeEventForParent() {
            window.parent.postMessage(
                {
                    event_id: 'iframe_size_event',
                    data: {
                        width: $('.site-content').width(),
                        height: content_heigth
                    }
                },
                "*"
            );
        }
        triggerIframeSizeEventForParent();
        $(window).resize(triggerIframeSizeEventForParent);

        $('a').attr('target', '_top');
    }

});