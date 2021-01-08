var sendReCaptcha;
var recaptchaCode = null;
var sendValidation;
var preloadImages;
var skip = 0;
var vox_id;
var question_id;
var handleSwiper;

$(document).ready(function(){

    var checkFilledDots = function( event, index) {
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

                        $('#bot-group').remove();

                        fbq('track', 'SurveyLaunch');
                        gtag('event', 'Take', {
                            'event_category': 'Survey',
                            'event_label': 'SurveyLaunch',
                        });

                        getNextQuestion(data.vox_id);
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

    var getNextQuestion = function(vox_id, question_id=null, token=null) {
        
        $.ajax( {
            url: next_q_url,
            type: 'POST',
            data: {
                vox_id: vox_id,
                question_id: question_id,
                _token: token ? token : $('input[name="_token"]').val(),
            },
            success: function( data ) {
                $('.question-group').find('.loader').remove();
                if(data) {
                    if(data.indexOf("skip-dvq") >= 0) {

                        if(data.indexOf("answer") >= 0) {
                            var arr = data.split(';');
                            var next_q_id = arr[0].split(':')[1];
                            var ans = arr[1].split(':')[1];

                            $('#loader-survey').show();
                            $('.question-group').hide();
                            sendSkipAnswer(next_q_id, ans);
                        } else {

                            var next_q_id = data.split(':')[1];
                            $('#loader-survey').show();
                            $('.question-group').hide();
                            sendSkipAnswer(next_q_id);
                        }
                    } else {
                        $('#loader-survey').hide();
                        $('#questions-box').html('');
                        $('#questions-box').prepend(data);
                        answerOnQuestion();
                        hangleQuestionStructure();
                    }
                }
                if($('.tooltip-window').length) {
                    $('.tooltip-window').remove();
                }
            }
        });
    };

    if(!$('#bot-group').length && $('body').hasClass('page-questionnaire')) {
        getNextQuestion(vox_id, question_id ? question_id : null);
    }

    var answerOnQuestion = function() {

        $('.question-group label.answer').click( function(e) {
            if( $(e.target).closest('.zoom-answer').length ) {
            } else if($(e.target).is( "a" )) {
            } else {
                sendAnswer.bind(this)();
            }
        } );

        $('.question-group a.next-answer').click( sendAnswer );

        $('.question-group input.disabler').change( function() {
            var group = $(this).closest('.question-group');

            group.find('input:not(.disabler)').prop('checked', false).prop('disabled', 'disabled');
            group.find('.answer-checkbox').removeClass('active');
            $(this).closest('.answer-checkbox').addClass('active');
            group.find('.next-answer').trigger('click');
            return;

            if( $(this).is(':checked') ) {
                group.find('input.answer').prop('checked', false).prop('disabled', 'disabled');
                group.find('.answer-checkbox').removeClass('active');
                $(this).prop('disabled', false).prop('checked', 'checked');
                $(this).closest('.answer-checkbox').addClass('active');
            } else {
                group.find('input.answer').prop('disabled', false);
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

            if( $(this).closest('.flickity').length ) {
                $(this).closest('.flickity').flickity('next');
            }
        });

        $('.input-checkbox').change( function() {
            $(this).closest('label').toggleClass('active');
        });

        tooltipsFunction();
    }

    answerOnQuestion();

    var hangleQuestionStructure = function() {

        var group = $('.question-group');
        //rank questions
        if(group.hasClass('rank')) {

            $( ".answers-draggable" ).sortable({
                containment: "parent",
                axis: "y",
                update: function (event, ui) {
                    $( this ).children().each(function (i) {
                        var numbering = i + 1;
                        $(this).find('select').val(numbering);
                        $(this).attr('rank-order', numbering);
                    });
                },
            });
            //}).disableSelection();

            $('.answers-draggable').on('sortupdate',function() {
                $( this ).children().each(function (i) {
                    var numbering = i + 1;
                    $(this).find('select').val(numbering);
                    $(this).attr('rank-order', numbering);
                });
            });

            $('.rank-order').on('touchstart', function(e) {
                e.stopImmediatePropagation();
            });

            $('.rank-order').change( function(e) {
                e.stopImmediatePropagation();
                var child = $(this).val();
                var elm = $(this).closest('.answer-rank');
                var elm_after = $(this).closest('.answers-draggable').find('.answer-rank:nth-child('+child+')');

                if(elm.attr('rank-order') > parseInt(child) ) {
                    elm.insertBefore( elm_after );
                } else {
                    elm.insertAfter( elm_after );
                }

                $(this).closest('.answers-draggable').trigger('sortupdate');
            });
        }

        //scale
        if($('.question-group').hasClass('scale')) {
            $('.question-group .flickity').flickity({
                wrapAround: true,
                adaptiveHeight: true,
                draggable: false
            });

            $('.question-group .flickity').on( 'select.flickity', checkFilledDots);
            $('.question-group .next-answer').hide();
        }

        if($('.question-group').hasClass('shuffle')) {
            var parent = $('.question-group .answers');

            if(parent.hasClass('in-columns')) {
                parent.find('.answers-column').each( function() {
                    var divs = $(this).children().not(".disabler-label");

                    while (divs.length) {
                        $(this).prepend(divs.splice(Math.floor(Math.random() * divs.length), 1)[0]);
                    }
                });
            } else {
                var divs = parent.children().not(".disabler-label");

                while (divs.length) {
                    parent.prepend(divs.splice(Math.floor(Math.random() * divs.length), 1)[0]);
                }
            }
        }
    }

    var sendAnswer = function() {

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
        var next_trigger;

        if(vox.current>=1) {
            $('.questionnaire-description').hide();
        }

        if (group.hasClass('question-group-details')) {
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

        } else if (group.hasClass('number')) {

            var number_input = group.find('.answer-number');
            var minNum = number_input.attr('min');
            var maxNum = number_input.attr('max');

            if ( number_input.val().length && parseInt( number_input.val() ) <= maxNum && parseInt( number_input.val() ) >= minNum ) {
                var answer = number_input.val();
                type = 'number';
                group.find('.answer-number-error').hide();
            } else {
                group.find('.answer-number-error').show();
                ajax_is_running = false;
                return;
            }

        } else if (group.hasClass('rank')) {

            type = 'rank';

            var without_error = true;
            group.find('select').each(function(){
                if(!$(this).val()){
                    without_error = false;
                }
            });

            if (without_error ) {
                var answer = [];
                group.find('.answer-rank').each( function() {
                    answer.push($(this).attr('data-num'));
                });
                group.find('.answer-rank-error').hide();
            } else {
                group.find('.answer-rank-error').show();
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
        //
        $('#wrong-control').hide();
        group.find('.answers').append('<div class="loader"><i class="fas fa-circle-notch fa-spin fa-3x fa-fw"></i></div>');

        //Skip skipped :)
        if (group.attr('cross-check-correct') ) {

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
                    group.find('.answers label.answer').each( function() {
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

        $.post( 
            VoxTest.url, 
            {
                question: qid,
                answer: answer,
                type: type,
                _token: $('input[name="_token"]').val()
            }, 
            function( data ) {
                if(data.success) {

                    if(data.balance) {
                        //test done
                        surveyDone(data);
                    } else {

                        $('input[name="_token"]').val(data.token);

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
                            $('.question-group a:nth-child('+parseInt(answer)+')').addClass('wrong');

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
                            $('.popcircle').addClass('active');

                            $('.popcircle .closer, .popcircle .btn.back-btn.btn-'+data.action).click( function() {
                                window.location.reload();
                            });
                        } else {
                            
                            if(data.warning && data.toofast) {
                                var wpopup = $('.popup.warning');
                                wpopup.find('h2').html(data.title);
                                wpopup.find('p').html(data.content);
                                wpopup.find('img').attr('src', data.img);
                                wpopup.addClass('active');
                                simpleCountDown();
                            }

                            vox.answered_without_skip_count++;
                            vox.current++;
                            VoxTest.handleNextQuestion();
                            getNextQuestion(data.vox_id, data.question_id, data.token);
                        }
                    }
                } else {
                    if (data.restricted) {
                        window.location.reload();
                    }
                    console.log('ERROR');
                    console.log(data);
                }
                ajax_is_running = false;

            }, "json"
        )
        .fail(function(response) {
            console.log('ERROR');
            console.log(response);
            //window.location.reload();
        });;
    }

    var sendSkipAnswer = function(next_q_id, ans=null) {
        $.post( 
            VoxTest.url, 
            {
                question: next_q_id,
                answer: ans ? ans : 0,
                type: ans ? 'previous' : 'skip',
                _token: $('input[name="_token"]').val()
            }, 
            function( data ) {
                if(data.success) {

                    if(data.balance) {
                        //test done
                        surveyDone(data);
                    } else {

                        $('input[name="_token"]').val(data.token);

                        vox.current++;
                        if($('.question-group').length) {
                            $('.question-group').find('.loader').remove();
                        }
                        VoxTest.handleNextQuestion(true);
                        getNextQuestion(data.vox_id, data.question_id, data.token);
                    }
                } else {
                    if (data.restricted) {
                        window.location.reload();
                    }
                    console.log('ERROR');
                    console.log(data);
                }
                ajax_is_running = false;

            }, "json"
        )
        .fail(function(response) {
            console.log('ERROR');
            console.log(response);
            //window.location.reload();
        });;
    }

    var surveyDone = function(data) {
        $('#header-balance').html(data.balance);

        VoxTest.handleNextQuestion();
        $('.coins-test').html( (parseInt($('.coins-test').html()) + vox.reward_single) * parseInt(user_vip_access ));
        $("#question-meta").hide();

        if ($(window).outerWidth() <= 768) {
            $('#myVideoMobile')[0].play();
        } else {
            $('#myVideo')[0].play();
        }
        
        $("#question-done").show();

        if (($('.swiper-container').length || $('.swipe-cont').length) && typeof Swiper !== 'undefined' ) {
            handleSwiper();
        }


        $('html, body').animate({
            scrollTop: $('body').offset().top
        }, 500);
        
        fbq('track', 'SurveyComplete');
        gtag('event', 'Take', {
            'event_category': 'Survey',
            'event_label': 'SurveyComplete',
        });

        if(data.recommend) {
            showPopup('recommend-popup');
        }
    }

    if ($('.loader').length) {
        $('.loader').fadeOut();
        $('.loader-mask').delay(500).fadeOut('slow');
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

    $('.popup .closer-pop').click( function() {

        if($(this).hasClass('inactive')) {
            return;
        }
        if($(this).closest('.popup').hasClass('ban')) {
            window.location.reload();
        }

        $(this).closest('.popup').removeClass('active');
        $('body').removeClass('popup-visible');
    } );

    $('.start-over').click( function() {
        if(ajax_is_running) {
            return;
        }

        ajax_is_running = true;

        var link = $(this).attr('url');
        var user_id = $(this).attr('u-id');
        var vox_id = $(this).attr('vox-id');
        var cur_url = $(this).attr('cur-url');

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
                    window.location.href = cur_url;

                } else {
                    console.log('error');
                }
                
            },
            error: function(ret) {
                console.log('error');
            }
        });
    });

    //to check if this is triggered
    $('.first-test .checkbox input').change( function() {
        $(this).closest('label').toggleClass('active');
    });

    $('.rules-ok').click( function(e) {
        e.preventDefault();

        if($('#agree-faq').is(':checked')) {
            $('.rules-error').hide();
            $(this).closest('.popup').removeClass('active');
            $('body').removeClass('popup-visible');
        } else {
            $('.rules-error').show();
        }
    });
});