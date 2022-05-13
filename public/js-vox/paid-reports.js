var ajax_is_running = false;

$(document).ready(function(){

    $('.accordion-title').click( function() {
        $(this).closest('.accordion').toggleClass('active');
    });

    $('.invoice').click( function(e) {
        console.log('ree');
        e.preventDefault();
        $('.checkout-form').toggleClass('active');
    });

    $('.checkout-form').submit( function(e) {
        e.preventDefault();

        $(this).find('.ajax-alert').remove();
        $('.agree-error').hide();
        // $(this).find('.alert').hide();
        $(this).find('.has-error').removeClass('has-error');

        if(ajax_is_running) {
            return;
        }
        ajax_is_running = true;

        var that = $(this);
        
        $.post( 
            $(this).attr('action'), 
            $(this).serialize() , 
            (function( data ) {
                if(data.success) {
                    if(typeof data.link !== 'undefined') {

                        window.location.href = data.link;
                    } else {
                        $('#checkout-form-success').show();
                        $('.company-form').removeClass('active');
                    }
                } else {
                    for(var i in data.messages) {
                        $('[name="'+i+'"]').closest('.alert-after').after('<div class="alert alert-warning ajax-alert" error="'+i+'">'+data.messages[i]+'</div>');

                        $('[name="'+i+'"]').addClass('has-error');

                        if ($('[name="'+i+'"]').closest('.modern-radios').length) {
                            $('[name="'+i+'"]').closest('.modern-radios').addClass('has-error');
                        }
                        
                        console.log(i);
                        if(i == 'agree') {
                            $('.agree-error').show();
                        }
                    }

                    $('html, body').animate({
                        scrollTop: $('.ajax-alert:visible').first().offset().top - $('header').height() - 150
                    }, 500);
                }
                ajax_is_running = false;
            }).bind(that), "json"
        );

    });
    
    var handleHorizontalScrolls = function() {
        var scrollableElement = $('.sample-pages .flex');
        var children = scrollableElement.children();
        var total = 0;

        children.each( function() { 
            total += $(this).outerWidth() + parseFloat($(this).css('margin-right')) + parseFloat($(this).css('margin-left'));
        });

        scrollableElement.css('width', total + parseFloat(scrollableElement.css('padding-left')) + parseFloat(scrollableElement.css('padding-right')) + 60);
    }

    if($(window).outerWidth() <= 768 && $('.sample-pages').length) {
        setTimeout(handleHorizontalScrolls , 10);
    }

    $('.go-to-reports').click( function (e) {
        e.preventDefault();

        console.log('sdfdsfsdf');

        $('html, body').animate({
            scrollTop: $('.main-section').offset().top
        }, 500);
    });

    
    var handleHorizontalScrollReports = function() {
        var scrollableElement = $('.swiper-wrapper');
        var children = scrollableElement.children();
        var total = 0;

        children.each( function() { 
            total += $(this).outerWidth() + parseFloat($(this).css('margin-right')) + parseFloat($(this).css('margin-left'));
        });

        scrollableElement.css('width', total + parseFloat(scrollableElement.css('padding-left')) + parseFloat(scrollableElement.css('padding-right')));
    }

    if($('.swiper-slide').length ) {
        if($('.swiper-slide').length > 3 ) {
            $('head').append('<link rel="stylesheet" type="text/css" href="'+window.location.origin+'/css/swiper.min.css">');

            $.getScript(window.location.origin+'/js/swiper.min.js', function() {
                
                if (typeof Swiper !== 'undefined' ) {
                    if (window.innerWidth > 1150) {

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
                    } else if(window.innerWidth > 768) {
                        var swiper_done = new Swiper('.swiper-container', {
                            slidesPerView: 2,
                            slidesPerGroup: 2,
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
        } else {
            if(window.innerWidth < 768) {
                setTimeout(handleHorizontalScrollReports , 10);
            }
        }
    }

    $('[name="invoice"]').change( function() {
        if($(this).val() == 'yes') {
            $('.vat-wrapper').removeClass('hide');
        } else {
            $('[name="vat"]').prop('checked', false);
            $('[name="vat"]').removeAttr('checked');
            $('[name="vat"]').closest('label').removeClass('active');
            $('.vat-wrapper').addClass('hide');
        }
    });

});