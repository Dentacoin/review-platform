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

    VoxTest.handleNextQuestion();

    $('.question-group a.answer').click( function() {
        if(ajax_is_running) {
            return;
        }

        ajax_is_running = true;

        var group = $(this).closest('.question-group');
        var qid = parseInt(group.attr('data-id'));
        var answer = $(this).attr('data-num');
        $('#wrong-control').hide();


        $.post( 
            VoxTest.url, 
            {
                question: qid,
                answer: answer,
            }, 
            function( data ) {
                if(data.success) {
                    group.hide();

                    if(data.ban) {
                        window.location.href = data.ban;
                        return;
                    }

                    if(data.wrong && data.go_back) {
                        vox.current = data.go_back;
                        var go_back_group = $('.question-group').first();
                        for(var i=1;i<vox.current;i++) {
                            go_back_group = go_back_group.next();
                        }
                        go_back_group.show();
                        $('#wrong-control').show();
                    } else {
                        if( group.next().hasClass('question-hints') ) {
                            $("#question-meta").hide();
                            $("#question-done").show();
                        } else {
                            group.next().show();
                            vox.current++;            
                        }
                    }

                    if(data.balance) {
                        $('#header-balance').html(data.balance);
                    }


                    VoxTest.handleNextQuestion();
                } else {
                    console.log(data);
                }
                ajax_is_running = false;
            }, "json"
        );
        
        
    } )

});