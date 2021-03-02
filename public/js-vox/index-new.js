$(document).ready(function(){

    if($('.inited-video').length) {

        $(window).on('scroll', function() {
            $('.inited-video').each( function() {
                if (!$(this).hasClass('already-inited')) {
                    $(this).attr('src', $(this).attr('video-url') );
                    $(this).addClass('already-inited');
                }
            });
        });

        if($(window).scrollTop() > 0) {
            $('.inited-video').each( function() {
                $(this).attr('src', $(this).attr('video-url') );
            });
        }
    }

    $('.second-absolute').click( function(e) {
        $('html, body').animate({
            scrollTop: $('.section-recent-surveys').offset().top
        }, 500);
    });

    var handleIndexLoad = function() {
        $('.inited-video').each( function() {
            if (!$(this).hasClass('already-inited')) {
                $(this).attr('src', $(this).attr('video-url') );
                $(this).addClass('already-inited');
            }
        });

        if($(window).scrollTop() > 0) {
            $('.inited-video').each( function() {
                $(this).attr('src', $(this).attr('video-url') );
            });
        }
    }

    if($('#to-append').length) {

        $(window).scroll( function() {
            if (!$('#to-append').hasClass('appended')) {
                $.ajax({
                    type: "POST",
                    url: lang + '/index-down/',
                    success: function(ret) {
                        if (!$('#to-append').hasClass('appended')) {
                            $('head').append('<link rel="stylesheet" type="text/css" href="'+window.location.origin+'/css/swiper.min.css">');
                            $('#to-append').append(ret);
                            $('#to-append').addClass('appended');

                            $.getScript(window.location.origin+'/js-vox/swiper.min.js', function() {
                                
                                if ($('.swiper-container').length && typeof Swiper !== 'undefined' ) {
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
                            });
                            handleIndexLoad();
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