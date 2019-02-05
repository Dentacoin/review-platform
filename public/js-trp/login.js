var lastCivicForm = null;
var suggestClinic;

$(document).ready(function(){

    // Step 2: Instantiate instance of civic.sip
    var civicSip = new civic.sip({ appId: 'rkvErCDdf' });


     // Step 3: Start scope request.
    $('.register-civic-button').click(function () {
    	if( $(this).closest('#signin-form-popup-left').length && ( !$('#register-agree:checked').length || $(this).hasClass('loading') ) ) {
    		return;
    	}
    	$(this).addClass('loading');
        $('#civic-error').hide();
        $('#withdraw-widget .alert').hide();
        lastCivicForm = $(this).closest('form');
        civicSip.signup({ style: 'popup', scopeRequest: civicSip.ScopeRequests.BASIC_SIGNUP });
    });

    var civicError = function() {
    	$('.register-civic-button').removeClass('loading')
        $('.register-civic-button').show();
    	lastCivicForm.find('.civic-wait').hide();
        $('html, body').animate({
            scrollTop: $(".register-civic-button").offset().top
        }, 500);
    }

    // Listen for data
    civicSip.on('auth-code-received', function (event) {
        console.log(event);
        var jwtToken = event.response;
        //sendAuthCode(jwtToken);

        $.ajax({
            type: "POST",
            url: 'https://dentacoin.net/civic',
            data: {
                jwtToken: jwtToken
            },
            dataType: 'json',
            success: function(ret) {
                if(!ret.userId) {
                    lastCivicForm.find('.civic-error').show();
                    civicError();
                } else {

    				lastCivicForm.find('.civic-wait').show();
                    console.log(jwtToken);
                    setTimeout(function() {
                        $.post( 
                            lastCivicForm.find('.jwtAddress').val(), 
                            {
                                jwtToken: jwtToken,
                                '_token': lastCivicForm.find('input[name="_token"]').val()
                            }, 
                            function( data ) {
                                if(data.popup) {
                                    closePopup();
                                    showPopup(data.popup);
                                } else if(data.weak) {
                                    lastCivicForm.find('.civic-weak').show();
                                    civicError();
                                } else if(data.success) {
                                	if( data.redirect ) {
                                		window.location.href = data.redirect;	
                                	} else {
                                		window.location.reload();
                                	}
                                } else {
                                    lastCivicForm.find('.civic-error').show();
                                    lastCivicForm.find('.civic-error span').html(data.message);
                                    civicError();
                                }
                            }, "json"
                        )
                        .fail(function(xhr, status, error) {
                            lastCivicForm.find('.civic-error').show();
                            civicError();
                        });
                    }, 3000);
                }
            },
            error: function(ret) {
                lastCivicForm.find('.civic-error').show();
                civicError();
            }
        });

    });

    civicSip.on('user-cancelled', function (event) {
        lastCivicForm.find('.civic-cancelled').show();
        civicError();
    });

    civicSip.on('read', function (event) {
    	lastCivicForm.find('.civic-wait').show();
        console.log('read');
    });

    civicSip.on('civic-sip-error', function (error) {
        lastCivicForm.find('.civic-error').show();
        civicError();
        console.log('   Error type = ' + error.type);
        console.log('   Error message = ' + error.message);
    });


    $('#grace-button').click( function() {
        $.ajax({
            type: "GET",
            url: lang + '/profile/setGrace',
        });
        $('.new-auth').remove();
    } );


    //
    //New Form
    //

    $('.forms .form-wrapper .form-button').click( function() {
        $('.form-wrapper').removeClass('chosen');
        $(this).closest('.form-wrapper').addClass('chosen');
    })

    $('#signin-form-popup .back').click( function() {
        var prev_step = $(this).closest('.sign-in-step');
        prev_step.removeClass('active');
        prev_step.prev().addClass('active');
    });

    $('.switch-forms').click( function() {
        $('.form-wrapper').addClass('chosen');
        $(this).closest('.form-wrapper').removeClass('chosen');
    });

    $('.switch-forms-mobile').click( function() {
        $('.form-wrapper').removeClass('chosen');
        $('#'+$(this).attr('data-form')).addClass('chosen');
    });

    $('.log-in-button').click( function() {
        switchLogins('login');
    });
    $('.sign-in-button').click( function() {
        switchLogins('register');
    });

    $('#login-form-popup').submit( function(e) {
        e.preventDefault();
        if(ajax_is_running) {
            return;
        }
        ajax_is_running = true;
        
        $.post(
            $(this).attr('action'), 
            $(this).serialize() , 
            (function( data ) {
                if(data.popup) {
                    closePopup();
                    showPopup(data.popup);
                } else if(data.success) {
                    window.location.reload();
                } else {
                    $(this).find('.login-error').show().html(data.message);
                }
                ajax_is_running = false;
            }).bind(this), 
            "json"
        )
    } );

    $('#signin-form-popup-left .log-button').click( function(e) {
        if( !$('#register-agree:checked').length ) {
            $('.agree-label').addClass('blink');
            e.preventDefault();
            e.stopPropagation();
        }
    } );

    $('#register-agree').change( function() {
        $('.agree-label').removeClass('blink');
    } );


    //
    //Dentist Registration
    //


    suggestClinic = function() {

        if(ajax_is_running) {
            return;
        }
        ajax_is_running = true;

        $.ajax( {
            url: 'suggest-clinic'+(user_id ? '/'+user_id : ''),
            type: 'POST',
            dataType: 'json',
            data: {
                joinclinic: $(this).val()
            },
            success: (function( data ) {
                console.log(data);
                var container = $(this).closest('.cilnic-suggester-wrapper').find('.suggest-results');
                
                if (data.length) {
                    container.html('').show();
                    for(var i in data) {
                        container.append('<a href="javascript:;" data-id="'+data[i].id+'">'+data[i].name+'</a>');
                    }

                    container.find('a').click( function() {
                        $(this).closest('.suggest-results').hide();
                        $(this).closest('.cilnic-suggester-wrapper').find('.cilnic-suggester').val( $(this).text() ).blur();
                        $(this).closest('.cilnic-suggester-wrapper').find('.suggester-hidden').val( $(this).attr('data-id') );
                    } );
                } else {
                    container.hide();                    
                }

                ajax_is_running = false;

            }).bind(this)
        });
    }

    $('.cilnic-suggester').closest('form').on('keyup keypress', function(e) {
        var keyCode = e.keyCode || e.which;
        if (keyCode === 13) { 
            e.preventDefault();
            return false;
        }
    });

    $('.cilnic-suggester').on( 'keyup', function(e) {
        
        var container = $(this).closest('.cilnic-suggester-wrapper').find('.suggest-results');

        var keyCode = e.keyCode || e.which;
        var activeLink = container.find('a.active');
        if (keyCode === 40 || keyCode === 38) { //Down / Up
            if(activeLink.length) {
                activeLink.removeClass('active');
                if( keyCode === 40 ) { // Down
                    if( activeLink.next().length ) {
                        activeLink.next().addClass('active');
                    } else {
                        container.find('a').first().addClass('active');
                    }
                } else { // UP
                    if( activeLink.prev().length ) {
                        activeLink.prev().addClass('active');
                    } else {
                        container.find('a').last().addClass('active');
                    }
                }
            } else {
                container.find('a').first().addClass('active');
            }
        } else if (keyCode === 13) {
            if( activeLink.length ) {
                $(this).val( activeLink.text() ).blur();
                $(this).closest('.cilnic-suggester-wrapper').find('.suggester-hidden').val( activeLink.attr('data-id') );
                container.hide();
            }
        } else {
            if( $(this).val().length > 3 ) {
                //Show Loding
                if(suggestTO) {
                    clearTimeout(suggestTO);
                }
                suggestTO = setTimeout(suggestClinic.bind(this), 300);
            } else {
                container.hide();
            }
        }
    });

    $('input[name="mode"]').change( function() {
        var val = $('#mode-in-clinic:checked').length;
        if(val) {
            $('#clinic-widget').show();
        } else {
            $('#clinic-widget').hide();
        }
    } );

    $('.go-to-next').click( function(e) {
        e.preventDefault();

        if(ajax_is_running) {
            return;
        }
        ajax_is_running = true;

        $.post( 
            $(this).attr('data-validator'), 
            $('#signin-form-popup').serialize(), 
            function( data ) {
                if(data.success) {
                    $('#register-error').hide();

                    var a = $('.sign-in-step.active');
                    a.removeClass('active');
                    a.next().addClass('active');

                } else {
                    $('#register-error').show();
                    $('#register-error span').html('');
                    for(var i in data.messages) {
                        $('#register-error span').append(data.messages[i] + '<br/>');
                        $('input[name="'+i+'"]').addClass('has-error');
                    }
                    grecaptcha.reset();
                }
                ajax_is_running = false;
            }, 
            "json"
        );

    } );

    $('#signin-form-popup').submit( function(e) {
        e.preventDefault();

        $(this).find('.alert').hide();
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
                if(data.track_registration) {
                    fbq('track', 'CompleteRegistration');
                    ga('send', 'event', 'DentistRegistration', 'ClickNext', 'DentistRegistrationComplete');
                }

                if(data.popup) {
                    closePopup();
                    showPopup(data.popup);
                } else if(data.success) {
                    window.location.href = data.url;
                } else {
                    $('#register-error').show();
                    $('#register-error span').html('');
                    for(var i in data.messages) {
                        $('#register-error span').append(data.messages[i] + '<br/>');
                        $('input[name="'+i+'"]').closest('.form-group').addClass('has-error');
                    }
                    grecaptcha.reset();
                }
                ajax_is_running = false;
            }).bind(that), "json"
        );          


        return false;

    } );

    $('#signin-form-popup input').on('focus', function(e){
        $(this).closest('.form-group').removeClass('has-error');
    });


});