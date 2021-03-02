var first_test = {};
var preloadImages;

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

});