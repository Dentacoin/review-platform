var sendReCaptcha;
var recaptchaCode = null;
var sendValidation;
var preloadImages;

$(document).ready(function(){

    if ($('.mobile-bubble-effect').length && $('.mobile-person-effect').length && window.innerWidth < 768) {

        // Let's call it:
        preloadImages([
            $('.mobile-bubble-effect').attr('src'),
            $('.mobile-person-effect').attr('src'),
        ], function(){
            $('.mobile-welcome-images img').each (function() {
                $(this).addClass('effect-loaded');
            });
        });
    }
    
    sendValidation = function() {
        if(recaptchaCode) { // && $('#iagree').is(':checked')
            $.post( 
                VoxTest.url, 
                {
                    captcha: recaptchaCode,
                    _token: $('input[name="_token"]').val()
                },
                function( data ) {
                    if(data.success) {
                        $('input[name="_token"]').val(data.token);
                        $('#bot-group').next().show();
                        $('#bot-group').remove();

                        fbq('track', 'SurveyLaunch');
                        gtag('event', 'Take', {
                            'event_category': 'Survey',
                            'event_label': 'SurveyLaunch',
                        });


                    } else {
                        $('#captcha-error').show();
                    }
                }
            );
        }
    }

    $('#iagree').change( sendValidation );

    sendReCaptcha = function(code) {
        $('#captcha-error').hide();
        recaptchaCode = code;
        sendValidation();
    }

    sendAnswer = function() {

        if(ajax_is_running) {
            return;
        }

        ajax_is_running = true;

        if($('.answered-box').length && $('.answered-box:visible')) {
            $('.answered-box').hide();
        }

        var group = $(this).closest('.question-group');
        var qid = parseInt(group.attr('data-id'));
        var type = null;
        var multi_skips = [];
        var next_trigger;

        if(vox.current>=1) {
            $('.questionnaire-description').hide();
        }

        if( group.next().hasClass('question-group-details') || group.next().hasClass('location-question') || (group.next().hasClass('birthyear-question') && !group.next().attr('cross-check-correct')) ) {
            $('.demographic-questionnaire-description').show();
        }

        if( group.attr('skipped') ) {
            var answer = 0;
            type = 'skip';

            var next_trigger = group.next();
            while(next_trigger.length && next_trigger.attr('data-trigger')=='-1') {
                multi_skips.push( next_trigger.attr('data-id') );
                console.log('ADDING SKIPS');
                next_trigger.attr('skipped', 'skipped');
                next_trigger = next_trigger.next();
                vox.current++;                
            }

        } else if (group.hasClass('question-group-details')) {
            if( group.find('select').length ) {
                if( group.find('select').val() ) {
                    var answer = group.find('select').val();
                } else {
                    $('.answer-error').show().insertAfter($(this));
                    ajax_is_running = false;
                    return;
                }
            } else {
                var answer = $(this).attr('data-num');
            }
            type = group.attr('custom-type');

        } else if (group.hasClass('location-question')) {

            if ( $('.country-select option:selected').val() ) {
                var answer = $('.country-select option:selected').val();
                type = 'location-question';
            } else {
                $('.answer-error').show().insertAfter($(this));
                ajax_is_running = false;
                return;
            }

        } else if (group.hasClass('birthyear-question')) {

            var maxYear = new Date().getFullYear() - 18;

            if ( $('#birthyear-answer').val().length && parseInt( $('#birthyear-answer').val() ) <= maxYear ) {
                var answer = $('#birthyear-answer').val();
                type = 'birthyear-question';
            } else {
                $('.answer-error').show().insertAfter($(this));
                ajax_is_running = false;
                return;
            }

        } else if (group.hasClass('single-choice')) {

            var answer = $(this).attr('data-num');
            type = group.hasClass('gender-question') ? 'gender-question' : 'single';

        } else if(group.hasClass('multiple-choice')) {

            type = 'multiple';
            if ( $( group.find('input[name="answer"]:checked') ).length ) {

                var answer = [];
                group.find('input[name="answer"]:checked').each( function() {
                    answer.push($(this).val());
                });
            } else {
                $('.answer-error').show().insertAfter($(this));
                ajax_is_running = false;
                return;
            }

        } else if(group.hasClass('scale')) {

            type = 'scale';
            if (group.find('.answer-radios-group').length == group.find('.answer:checked').length ) {

                var answer = [];
                group.find('.answer-radios-group').each( function() {
                    answer.push($(this).find('.answer:checked').val());
                });
            } else {
                
                group.find('.answer-radios-group').addClass('scale-error');
                group.find('.answer-radios-group').each( function() {
                    if ($(this).find('.answer').is(':checked')) {
                        $(this).removeClass('scale-error');
                    }
                });

                
                $('.answer-scale-error').show().insertAfter($(this));
                ajax_is_running = false;
                return;
            }
        }
        
        $('#wrong-control').hide();
        group.find('.answers').append('<div class="loader"><i class="fas fa-circle-notch fa-spin fa-3x fa-fw"></i></div>');

        //Skip skipped :)
        if (group.attr('cross-check-correct') && !group.attr('skipped') ) {

            var given_answer = group.find('select').length ? group.find('select').val() : $(this).find('input').val();

            if ((parseInt(given_answer) != parseInt(group.attr('cross-check-correct'))) && !testmode) {
                $('.popup.cross-checks .cross-checks-answers').html('');

                if(group.find('select').length) {
                    $('.popup.cross-checks .cross-checks-answers').append('<select class="answer" name="cc-birthyear-answer" id="cc-birthyear-answer"></select>');
                    group.find('select option').each( function() {
                        $('.popup.cross-checks .cross-checks-answers select').append('<option value="'+$(this).val()+'">'+$(this).text()+'</option>');
                    });

                    //Copy value
                    $('.popup.cross-checks .cross-checks-answers select').val( group.find('select').val() );
                } else {
                    group.find('.answers a.answer').each( function() {
                        $('.popup.cross-checks .cross-checks-answers').append('<label for="cc-answer-'+group.attr('data-id')+'-'+$(this).find('input').val()+'">'+$(this).text()+'<i class="popup-check"></i><input id="cc-answer-'+group.attr('data-id')+'-'+$(this).find('input').val()+'" type="radio" name="answer" class="answer" value="'+$(this).find('input').val()+'" style="display:none;"></label>');
                    });
                }

                $('.popup.cross-checks').addClass('active');

                $('.cross-checks-answers label').click( function() {
                    $('.cross-checks-answers label').removeClass('active');
                    $(this).addClass('active');
                    $('.popup.cross-checks').find('.pick-answer').hide();
                });

                $('.update-answer').off('click').click( function() {
                    if ($('.cross-checks-answers label.active').length) {
                        ajax_is_running = false;
                        group.removeAttr('cross-check-correct');
                        var new_answer = $('.cross-checks-answers label.active input').val();
                        group.find('.loader').remove();
                        group.find('input[value="'+new_answer+'"]').parent().trigger('click');
                        $('.popup.cross-checks').removeClass('active');

                    } else if($('.cross-checks-answers select').length && $('.cross-checks-answers select').val()) {
                        ajax_is_running = false;
                        group.removeAttr('cross-check-correct');
                        var new_answer = $('.cross-checks-answers select').val();
                        group.find('select').val(new_answer);
                        group.find('.loader').remove();
                        group.find('.next-answer').trigger('click');
                        $('.popup.cross-checks').removeClass('active');
                        
                    } else {
                        $('.popup.cross-checks').find('.pick-answer').show();
                    }

                });

                return;
            }
        }

        $('.question-group').each( function() {
            if (qid == $(this).attr('cross-check-id')) {
                $(this).attr('cross-check-correct', answer);
            }
        });


        $.post( 
            VoxTest.url, 
            {
                question: qid,
                answer: answer,
                type: type,
                skips: multi_skips,
                _token: $('input[name="_token"]').val()
            }, 
            function( data ) {
                if(data.success) {
                    $('input[name="_token"]').val(data.token);

                    var should_skip = false;

                    if( multi_skips.length ) {
                        console.log('MULTI SKIP');
                        var next_real = group.nextAll(':not([skipped="skipped"])').first().prev();
                        VoxTest.handleNextQuestion();

                        console.log(group);
                        group = next_real;
                        console.log(group);
                    } 

                    if(data.ban) {
                        var wpopup = $('.popup.ban');
                        wpopup.addClass('active');
                        wpopup.find('h2').html(data.title);
                        wpopup.find('h3 span').html( (parseInt(data.ban_duration)*24)+':00:00' );
                        wpopup.find('p').html(data.content);
                        wpopup.find('img').attr('src', data.img);
                        wpopup.addClass('active');
                        if(data.ban_duration) {
                            hoursCountdown();                            
                        } else {
                            $('.hours-countdown').hide();
                        }
                        return;
                    }

                    if(data.wrong) {
                        $('.question-group').find('.loader').remove();
                        $('.question-group:visible a:nth-child('+parseInt(answer)+')').addClass('wrong');


                        var go_back_group = $('.question-group').first();
                        var i = 1;
                        var found = false;
                        do {
                            if( !found && go_back_group.hasClass('question-group-'+data.go_back) ) {
                                vox.current = i;
                                found = true;                         
                            }
                            if( found ) {
                                go_back_group.attr('data-answer', '');                                
                            }
                            go_back_group = go_back_group.next();
                            i++;
                        } while(go_back_group.length);

                        $('.question-group .answer-radio.active-label input').prop('checked', false);
                        $('.question-group .answer-checkbox.active').removeClass('active');
                        $('.question-group .answer-radio.active-label').removeClass('active-label');
                        $('.question-group input.answer').prop('disabled', false);

                        $('.answer-error').hide();

                        $('.popcircle .wrapper').css('background-image', 'url('+data.img+')');
                        $('.popcircle h2').html(data.title);
                        $('.popcircle p').html(data.content);
                        $('.popcircle .zman').attr('src', data.zman);
                        $('.popcircle .btn.back-btn').hide();
                        $('.popcircle .btn.back-btn.btn-'+data.action).show();
                        $('.popcircle .btn.back-btn.btn-'+data.action).off('click').click( (function() {
                            $('.question-group a.wrong').removeClass('wrong');
                            $('.popcircle').removeClass('active');
                            $('.question-group').hide();
                            $('.question-group-'+this.go_back).show();
                            $('#current-question-reward').html( 0 );
                            VoxTest.handleNextQuestion();
                        }).bind(data) );
                        $('.popcircle').addClass('active');

                    } else {
                        

                        if (
                            group.hasClass('gender-question') ||
                            group.hasClass('question-group-details') ||
                            group.hasClass('location-question') ||
                            group.hasClass('birthyear-question')
                        ) {
                            group.attr('data-answer', answer);
                        } else if (group.hasClass('single-choice')) {
                            group.attr('data-answer', answer);
                        } else {
                            group.attr('data-answer', !answer ? '' : answer.join(','));
                        }

                        //hasClass('question-hints')
                        if( !group.next().length ) {
                            if($('.dentacoin-info').length) {
                                if(group.attr('data-answer') == '2') {
                                    $('.dentacoin-info').show();
                                }
                            }
                            VoxTest.handleNextQuestion();
                            $("#question-meta").hide();
                            
                            if (related) {
                                $("#question-related-done").show();
                            } else {
                                $("#question-done").show();
                            }
                            $('html, body').animate({
                                scrollTop: $('body').offset().top
                            }, 500);

                            $("#other-surveys").show();
                            swiper.update();
                            
                            fbq('track', 'SurveyComplete');
                            gtag('event', 'Take', {
                                'event_category': 'Survey',
                                'event_label': 'SurveyComplete',
                            });
                        } else {

                            var trigger = group.next().attr('data-trigger');
                            var trigger_logical_operator = group.next().attr('trigger-type');
                            if(trigger && trigger!='-1') {
                                console.log(group.find('.question').text());
                                console.log(trigger, trigger_logical_operator);
                                var trigger_statuses = [];
                                var trigger_list = trigger.split(';');
                                for(var i in trigger_list) {
                                    var trigger_status = false;
                                    var parts = trigger_list[i].trim().split(':');
                                    var trigger_question = parts[0].trim(); // 15 въпрос
                                    var given_answer = $('.question-group-' + trigger_question).attr('data-answer'); // 5  1,3,6  // [1,3,6]
                                    var trigger_type = $('.question-group-' + trigger_question).hasClass('birthyear-question') ? 'birthyear' : 'standard';
                                    var parsed_given_answer = given_answer && given_answer.length && given_answer!="0" ? given_answer.split(',') : null;
                                    if( parsed_given_answer ) {
                                        if( parts[1] ) {
                                            var trigger_answers = parts[1].split(','); // 2,6 // [2,6]
                                            if( trigger_type=='birthyear' ) {
                                                var age = new Date().getFullYear() - parseInt(parsed_given_answer);
                                                console.log('AGE: '+age);
                                                trigger_status = true;
                                                for(var i in trigger_answers) {
                                                    var ti = trigger_answers[i].trim();
                                                    if( ti.indexOf('-')!=-1 ) {
                                                        var range = ti.split('-');
                                                        console.log('Check: '+range[0]+' < ' + age + ' < ' + range[1]);
                                                        if( parseInt(range[0]) > age || age > parseInt(range[1]) ) {
                                                            console.log('NO!');
                                                            trigger_status = false;
                                                            break;
                                                        }
                                                    } else if( ti.charAt(0)=='<' ) {
                                                        console.log('Check: '+age+' < ' + ti.substring(1));
                                                        if( age > parseInt(ti.substring(1)) ) {
                                                            console.log('NO!');
                                                            trigger_status = false;
                                                            break;
                                                        }
                                                    } else if( ti.charAt(0)=='>' ) {
                                                        console.log('Check: '+age+' > ' + ti.substring(1));
                                                        if( age < parseInt(ti.substring(1)) ) {
                                                            console.log('NO!');
                                                            trigger_status = false;
                                                            break;
                                                        }
                                                    }
                                                }

                                            } else {
                                                for(var i in trigger_answers) {
                                                    //Just answers
                                                    if( trigger_answers[i].indexOf('-')!=-1 ) {
                                                        var range = trigger_answers[i].split('-');
                                                        range[0] = parseInt(range[0]);
                                                        range[1] = parseInt(range[1]);
                                                        for(var qnum=range[0]; qnum<=range[1]; qnum++) {
                                                            if( parsed_given_answer.indexOf(qnum.toString())!=-1 ) {
                                                                trigger_status = true;
                                                                break;
                                                            }    
                                                        }
                                                    } else {
                                                        if( parsed_given_answer.indexOf(trigger_answers[i].trim().toString())!=-1 ) {
                                                            trigger_status = true;
                                                            break;
                                                        }
                                                    }
                                                }
                                            }
                                        } else {
                                            trigger_status = true;
                                        }
                                    }
                                    trigger_statuses.push(trigger_status);
                                }

                                console.log( 'Trigger statuses: ', trigger_statuses );

                                if( trigger_logical_operator=='or' ) {
                                    should_skip = !(trigger_statuses.indexOf(true)!=-1);
                                } else { //and
                                    should_skip = trigger_statuses.indexOf(false)!=-1;                                    
                                }
                                console.log( 'should skip: ', should_skip );
                            }


                            if(data.warning && data.toofast) {
                                var wpopup = $('.popup.warning');
                                wpopup.find('h2').html(data.title);
                                wpopup.find('p').html(data.content);
                                wpopup.find('img').attr('src', data.img);
                                wpopup.addClass('active');
                                simpleCountDown();
                            }

                            vox.current++;
                            if (!should_skip) {
                                $('.question-group').find('.loader').remove();
                                $('.question-group').hide();
                                group.next().show();
                                if( group.next().find('.question').offset().top < $(window).scrollTop() ) {
                                    $('html, body').stop().animate({
                                        scrollTop: parseInt(group.next().find('.question').offset().top)
                                    }, 500);
                                }
                                VoxTest.handleNextQuestion();
                            } else {
                                group.next().attr('skipped', 'skipped');
                            }         
                        }
                    }

                    if(data.balance) {
                        $('#header-balance').html(data.balance);
                    }


                } else {
                    console.log('ERROR');
                    console.log(data);
                }
                ajax_is_running = false;
                
                if (should_skip) {
                    //console.log('SKIP');
                    sendAnswer.bind(group.next().children().first())();
                }
            }, "json"
        )
        .fail(function(response) {
            console.log('ERROR');
            console.log(response);
            window.location.reload();
        });;
    }

    $('.question-group a.answer').click( sendAnswer );
    $('.question-group a.next-answer').click( sendAnswer );

    // $('.question-group .answer-checkbox').click( function() {
    //     if( $(this).find('input').prop('disabled') ) {
    //         $(this).closest('.question-group').find('input.answer').prop('disabled', false).prop('checked', false);
    //         $(this).prop('checked', 'checked');
    //         $(this).closest('.question-group').find('input.disabler').closest('.answer-checkbox').removeClass('active');
    //     }
    // } )

    $('.question-group input.disabler').change( function() {
        $(this).closest('.question-group').find('input:not(.disabler)').prop('checked', false).prop('disabled', 'disabled');
        $(this).closest('.question-group').find('.answer-checkbox').removeClass('active');
        $(this).closest('.answer-checkbox').addClass('active');
        $(this).closest('.question-group').find('.next-answer').trigger('click');
        return;

        if( $(this).is(':checked') ) {
            $(this).closest('.question-group').find('input.answer').prop('checked', false).prop('disabled', 'disabled');
            $(this).closest('.question-group').find('.answer-checkbox').removeClass('active');
            $(this).prop('disabled', false).prop('checked', 'checked');
            $(this).closest('.answer-checkbox').addClass('active');
        } else {
            $(this).closest('.question-group').find('input.answer').prop('disabled', false);
            $(this).closest('.answer-checkbox').removeClass('active');
        }
    } );

    $('.question-group.scale .answer').change( function() {
        $(this).closest('.answer-radios-group').removeClass('scale-error');
        $(this).closest('.answer-radios-group').find('.answer-radio').each( function() {
            if( $(this).find('input').is(':checked') ) {
                $(this).addClass('active-label');
            } else {
                $(this).removeClass('active-label');
            }
        } );

        if (window.innerWidth < 768) {

            if( $(this).closest('.flickity').length ) {
                $(this).closest('.flickity').flickity('next');
            }
        }

    } );

    $('.copy-link').click( function() {
        var $temp = $("<input>");
        $("body").append($temp);
        $temp.val($('#invite-url').val()).select();
        document.execCommand("copy");
        $temp.remove();        
    } );


    $('#invite-button').click( function() {
       
        if (navigator.share !== undefined) {

            $(this).blur()

            navigator.share({
                title: document.title,
                url: window.location.href,
            }).then(() => console.log('Successful share'))
            .catch((error) => console.log('Error sharing:', error));
        } else {
            $(this).next().show();
            $(this).hide();
        }
    });

    if (window.innerWidth > 768) {
        $(window).on('scroll', function() {
            if ( $('.question-group:visible').find('.static-titles').length && $(window).scrollTop() >= $('.question-group:visible').find('.static-titles').offset().top ) {
                $('.question-group:visible').find('.fixed-titles').addClass('active');
            } else {
                $('.question-group:visible').find('.fixed-titles').removeClass('active');
            }
        });
    }

    if (window.innerWidth < 768) {
        $(window).on('scroll', function() {

            if ($('.vox-survey-title').length) {

                if ($(window).scrollTop() >= $('.vox-survey-title').offset().top && !$('.vox-survey-title').hasClass('fixed-t')) {
                    $('.vox-survey-title').addClass('fixed-t');
                }

                if (($(window).scrollTop() <= $('header').outerHeight()) && $('.vox-survey-title').hasClass('fixed-t')) {
                    $('.vox-survey-title').removeClass('fixed-t');
                }
            }
        });
    }

    if ($('#other-surveys .swiper-container').length) {

        if (window.innerWidth > 768) {

            var swiper = new Swiper('#other-surveys .swiper-container', {
                slidesPerView: 3,
                slidesPerGroup: 3,
                spaceBetween: 0,
                pagination: {
                    el: '#other-surveys .swiper-pagination',
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
                resizeReInit: true,
            });
        } else {
            var swiper = new Swiper('#other-surveys .swiper-container', {
                slidesPerView: 1,
                spaceBetween: 0,
                pagination: {
                    el: '#other-surveys .swiper-pagination',
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


    $('#scroll-to-surveys').click( function(e) {
        e.preventDefault();

        $('html, body').animate({
            scrollTop: $('#other-surveys').offset().top
        }, 500);
    });

    if ($('.swiper-container').length) {

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
                    900: {
                      slidesPerView: 2,
                    },
                },
                autoplay: {
                    delay: 5000,
                },
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
            });
        }
    }



    $('.start-over').click( function() {
        if(ajax_is_running) {
            return;
        }

        ajax_is_running = true;

        var link = $(this).attr('url');
        var user_id = $(this).attr('u-id');
        var vox_id = $(this).attr('vox-id');

        $.ajax({
            type: "POST",
            url: link,
            data: {
                user_id: user_id,
                vox_id: vox_id,
                _token: $('input[name="_token"]').val(),
            },
            dataType: 'json',

            success: function(ret) {
                if (ret.success) {
                    window.location.reload();

                } else {
                    console.log('error');
                }
                
            },
            error: function(ret) {
                console.log('error');
            }
        });
    });


});