var first_test = {};
var preloadImages;

$(document).ready(function(){

    if(typeof(vox)!='undefined') {
        VoxTest.handleNextQuestion();        
    }
    
    $('.question-group label.answer, .question-group a.next-answer').click( function() {
        var group = $(this).closest('.question-group');
        var qid = group.attr('data-id');
        var answer = null;

        if( group.next().hasClass('birthyear-question') || group.hasClass('location-question') ) {
            $('.questionnaire-description').hide();
            $('.demographic-questionnaire-description').show();
        }

        if( group.hasClass('birthyear-question') ) {

            var maxYear = new Date().getFullYear() - 18;
            if (!( $('#birthyear-answer').val().length && parseInt( $('#birthyear-answer').val() ) <= maxYear) ) {
                $('.answer-error').show().insertAfter($(this));
                return;
            }
            if( parseInt( $('#birthyear-answer').val() ) > (new Date()).getFullYear()-18 ) {
                $('.birthday-answer-error').show().insertAfter($(this));
                return;   
            }
            
            answer = $('#birthyear-answer').val();

        } else if (group.hasClass('location-question')) {

            if ( !$('.country-select').val() ) {
                $('.answer-error').show().insertAfter($(this));
                return;
            }
            answer = $('.country-select option:selected').val();
        } else {
            answer = $(this).attr('data-num');
        }

        first_test[ qid ] = answer;

        if( group.next().hasClass('question-done') ) {
            Cookies.set('first_test', first_test, { expires: 1, secure: true });

            $.ajax( {
                url: lang,
                type: 'GET'
            } );

            gtag('event', 'Take', {
                'event_category': 'Survey',
                'event_label': 'WelcomeSurveyComplete',
            });
            fbq('track', 'WelcomeSurveyComplete');

            if (user_id) {
                $('.question-hints').hide();
                $('.section-welcome').hide();
                $('.section-welcome-done').show();
            } else {
                if (window.innerWidth < 768) {
                    if ($('.finish-test .mobile-bubble-effect').length && $('.finish-test .mobile-person-effect').length) {

                        $('.finish-test').show();
                        preloadImages([
                            $('.finish-test .mobile-bubble-effect').attr('src'),
                            $('.finish-test .mobile-person-effect').attr('src'),
                        ], function(){
                            $('.finish-test .mobile-welcome-images img').each (function() {
                                $(this).addClass('effect-loaded');
                            });
                            
                            if(dentacoin_down) {
                                showPopup('failed-popup');
                            } else {
                                setTimeout( function() {
                                    $.event.trigger({type: 'openPatientRegister'});
                                }, 2000 );
                            }
                        });
                    }

                } else {
                    if(dentacoin_down) {
                        showPopup('failed-popup');
                    } else {
                        setTimeout( function() {
                            $.event.trigger({type: 'openPatientRegister'});
                        }, 1000 );
                    }
                }
            }
            // $("#first-test-done").modal({backdrop: 'static', keyboard: false});

        } else {
            group.hide();
            group.next().show();
            vox.current++;            
        }

        VoxTest.handleNextQuestion();
        
    } );

    $('.country-select').change( function() {
        var city_select = $(this).closest('.answers').find('.city-select').first();
        city_select.attr('disabled', 'disabled');
        $.ajax( {
            url: '/cities/' + $(this).val(),
            type: 'GET',
            dataType: 'json',
            success: function( data ) {
                city_select.attr('disabled', false)
                .find('option')
                .remove();
                for(var i in data.cities) {
                    city_select.append('<option value="'+i+'" '+(fb_city_id && fb_city_id==data.cities[i] ? 'selected="selected"' : '' )+'>'+data.cities[i]+'</option>');
                }
                //city_select
                //$('#modal-message .modal-body').html(data);
            }
        });
    } );

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
                    type: "GET",
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