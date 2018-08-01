var sendReCaptcha;
var recaptchaCode = null;
var sendValidation;

$(document).ready(function(){
    
    sendValidation = function() {
        if(recaptchaCode) { // && $('#iagree').is(':checked')
            $.post( 
                VoxTest.url, 
                {
                    captcha: recaptchaCode
                },
                function( data ) {
                    console.log(data);
                    if(data.success) {
                        $('#bot-group').next().show();
                        $('#bot-group').remove();
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

        var group = $(this).closest('.question-group');
        var qid = parseInt(group.attr('data-id'));
        var type = null;

        if(vox.current>=1) {
            $('.questionnaire-description').hide();
        }

        if( group.attr('skipped') ) {
            var answer = 0;
            type = 'skip';

        } else if (group.hasClass('location-question')) {

            if ($('select').length == $('select option:selected').length) {
                var answer = $('.country-select option:selected').val() + ',' + $('.city-select option:selected').val();
                type = 'location-question';
            } else {
                $('.answer-error').show().insertAfter($(this));
                ajax_is_running = false;
                return;
            }

        } else if (group.hasClass('birthyear-question')) {

            if ( $('#birthyear-answer').val().length && parseInt( $('#birthyear-answer').val() ) > 1900 && parseInt( $('#birthyear-answer').val() ) < 2000 ) {
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
                $('.answer-error').show().insertAfter($(this));
                ajax_is_running = false;
                return;
            }
        }
        
        $('#wrong-control').hide();
        group.find('.answers').append('<div class="loader"><i class="fa fa-circle-o-notch fa-spin fa-3x fa-fw"></i></div>');

        $.post( 
            VoxTest.url, 
            {
                question: qid,
                answer: answer,
                type: type,
            }, 
            function( data ) {
                if(data.success) {
                    var should_skip = false;

                    if(data.ban) {
                        if(data.ban_type=='permanent') {                  
                            $('.popup-permanent-ban').addClass('active');                            
                            $('.popup-permanent-ban a').attr('href', data.ban);          
                        } else {
                            $('.popup-temporary-ban').addClass('active');
                            $('.popup-temporary-ban a').attr('href', data.ban);                            
                        }
                        return;
                    }

                    if(data.wrong && data.go_back) {
                        $('.question-group').find('.loader').remove();
                        $('.question-group').hide();
                        
                        var go_back_group = $('.question-group').first();
                        var i = 1;
                        do {
                            if( go_back_group.hasClass('question-group-'+data.go_back) ) {
                                vox.current = i;
                                break;                                
                            }
                            go_back_group = go_back_group.next();
                            i++;
                        } while(go_back_group.length);

                        go_back_group.show();
                        
                        $('.popup-mistakes').addClass('active');
                        $('#mistakes-left').html(data.mistakes_left);
                        $('.popup-mistakes a').click( function() {
                            $('.popup-mistakes').removeClass('active');
                        } );

                        VoxTest.handleNextQuestion();

                    } else {
                        
                        if (group.hasClass('single-choice')) {
                            group.attr('data-answer', answer);
                        } else if(group.hasClass('multiple-choice')) {
                            console.log(answer);
                            group.attr('data-answer', !answer ? '' : answer.join(','));
                        }

                        //hasClass('question-hints')
                        if( !group.next().length ) {
                            if($('.dentacoin-info').length) {
                                if(group.attr('data-answer') == '2') {
                                    $('.dentacoin-info').show();
                                }
                            }
                            $("#question-meta").hide();
                            $("#question-done").show();
                        } else {

                            var trigger = group.next().attr('data-trigger');
                            if(trigger) {
                                var trigger_statuses = [];
                                var trigger_list = trigger.split(';');
                                for(var i in trigger_list) {
                                    var trigger_status = false;
                                    var parts = trigger_list[i].trim().split(':');
                                    var trigger_question = parts[0].trim(); // 15 въпрос
                                    var given_answer = $('.question-group-' + trigger_question).attr('data-answer'); // 5  1,3,6  // [1,3,6]
                                    var parsed_given_answer = given_answer && given_answer.length && given_answer!="0" ? given_answer.split(',') : null;
                                    console.log(parsed_given_answer);
                                    if( parsed_given_answer ) {
                                        console.log(parts);
                                        if( parts[1] ) {
                                            var trigger_answers = parts[1].split(','); // 2,6 // [2,6]
                                            console.log('trigger answers', trigger_answers);
                                            for(var i in trigger_answers) {
                                                if( given_answer.indexOf(trigger_answers[i].trim())!=-1 ) {
                                                    console.log('tuk!111');
                                                    trigger_status = true;
                                                    break;
                                                }
                                            }
                                        } else {
                                            console.log('tuk!');
                                            trigger_status = true;
                                        }
                                    }
                                    trigger_statuses.push(trigger_status);
                                }
                                should_skip = trigger_statuses.indexOf(false)!=-1;
                            }

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
                                console.log('SKIP IT!');
                                group.next().attr('skipped', 'skipped');
                            }
                            vox.current++;            
                        }
                    }

                    if(data.balance) {
                        $('#header-balance').html(data.balance);
                    }


                } else {
                    console.log(data);
                }
                ajax_is_running = false;
                if (should_skip) {
                    console.log('skipvame li?');
                    sendAnswer.bind(group.next().children().first())();
                }
            }, "json"
        );
    }

    $('.question-group a.answer').click( sendAnswer );
    $('.question-group a.next-answer').click( sendAnswer );

    $('.question-group .answer-checkbox').click( function() {
        if( $(this).find('input').prop('disabled') ) {
            $(this).closest('.question-group').find('input.answer').prop('disabled', false).prop('checked', false);
            $(this).prop('checked', 'checked');
        }
    } )

    $('.question-group input.disabler').change( function() {
        if( $(this).is(':checked') ) {
            $(this).closest('.question-group').find('input.answer').prop('checked', false).prop('disabled', 'disabled');
            $(this).prop('disabled', false).prop('checked', 'checked');
        } else {
            $(this).closest('.question-group').find('input.answer').prop('disabled', false);               
        }
    } );

    $('.question-group.scale .answer').change( function() {
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

    } );


    if (navigator.share !== undefined) {
        $('.has-mobile-share').show();

        navigator.share({
            title: document.title,
            url: shareUrl
        }).then(() => console.log('Successful share'))
        .catch((error) => console.log('Error sharing:', error));
    } else {
        $('.no-mobile-share').show();
    }

    $('.country-select').change( function() {
        var city_select = $(this).closest('.answers').find('.city-select').first();
        city_select.attr('disabled', 'disabled');
        $.ajax( {
            url: '/cities/' + $(this).val(),
            type: 'GET',
            dataType: 'json',
            success: function( data ) {
                console.log(data);
                city_select.attr('disabled', false)
                .find('option')
                .remove();
                for(var i in data.cities) {
                    console.log( fb_city_id, data.cities[i] );
                    city_select.append('<option value="'+i+'" '+(fb_city_id && fb_city_id==data.cities[i] ? 'selected="selected"' : '' )+'>'+data.cities[i]+'</option>');
                }
                //city_select
                //$('#modal-message .modal-body').html(data);
            }
        });
    } );

});