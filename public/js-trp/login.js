var lastCivicForm = null;
var suggestClinic;

$(document).ready(function(){

    // Step 2: Instantiate instance of civic.sip
    var civicSip = new civic.sip({ appId: 'rkvErCDdf' });


     // Step 3: Start scope request.
    $('.register-civic-button').click(function () {
    	if( ($(this).closest('#signin-form-popup-left').length && ( !$('#register-agree:checked').length || $(this).hasClass('loading')) ) ) {
    		return;
    	}

        if (!Cookies.get('strictly_necessary_policy')) {
            return;
        }
    	$(this).addClass('loading');
        $('#civic-error').hide();
        $('#withdraw-widget .alert').hide();
        lastCivicForm = $(this).closest('form');
        civicSip.signup({ style: 'popup', scopeRequest: civicSip.ScopeRequests.BASIC_SIGNUP });
    });

    var civicError = function() {
    	$('.register-civic-button').removeClass('loading');
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
            url: 'https://civic.dentacoin.net/civic',
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
                                    $('.log-in-button').click( function() {
                                        switchLogins('login');
                                    });
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
        if ($('#dentist-address').length && $('#dentist-address').val()) {
            $('#dentist-address').blur();
        }
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
            $(this).serialize(),
            (function( data ) {
                if (data.hash) {
                    $('input[name="last_user_hash"]').val(data.hash);
                }
                if (data.id) {
                    $('input[name="last_user_id"]').val(data.id);
                }

                if (data.description && $('.verification-form').length) {
                    $('.verification-form').hide();
                }
                if (data.work_hours || data.is_clinic) {
                    $('.wh-btn').hide();
                }

                if(data.description && data.work_hours) {
                    $('.verification-info').hide();
                }

                if (data.is_clinic) {
                    $('#title-clinic').show();
                    $('#title-dentist').hide();
                } else {
                    $('#title-clinic').hide();
                    $('#title-dentist').show();
                    $('#clinic-add-team').remove();
                }

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

    suggestDentist = function() {

        if(ajax_is_running) {
            return;
        }
        ajax_is_running = true;

        $.ajax( {
            url: 'suggest-dentist'+(user_id ? '/'+user_id : ''),
            type: 'POST',
            dataType: 'json',
            data: {
                invitedentist: $(this).val()
            },
            success: (function( data ) {
                console.log(data);
                var container = $(this).closest('.dentist-suggester-wrapper').find('.suggest-results');
                
                if (data.length) {
                    container.html('').show();
                    for(var i in data) {
                        container.append('<a href="javascript:;" data-id="'+data[i].id+'">'+data[i].name+'</a>');
                    }

                    container.find('a').click( function() {
                        $(this).closest('.suggest-results').hide();
                        $(this).closest('.dentist-suggester-wrapper').find('.dentist-suggester').val( $(this).text() ).blur();
                        $(this).closest('.dentist-suggester-wrapper').find('.suggester-hidden').val( $(this).attr('data-id') ).trigger('change');
                    } );
                } else {
                    container.hide();                    
                }

                ajax_is_running = false;

            }).bind(this)
        });
    }

    $('.dentist-suggester').closest('form').on('keyup keypress', function(e) {
        var keyCode = e.keyCode || e.which;
        if (keyCode === 13) { 
            e.preventDefault();
            return false;
        }
    });

    $('.dentist-suggester').on( 'keyup', function(e) {
        
        var container = $(this).closest('.dentist-suggester-wrapper').find('.suggest-results');

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
                $(this).closest('.dentist-suggester-wrapper').find('.suggester-hidden').val( activeLink.attr('data-id') );
                container.hide();
            }
        } else {
            if( $(this).val().length > 3 ) {
                //Show Loding
                if(suggestTO) {
                    clearTimeout(suggestTO);
                }
                suggestTO = setTimeout(suggestDentist.bind(this), 300);
            } else {
                container.hide();
            }
        }
    });


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
                // console.log(data);
                var container = $(this).closest('.cilnic-suggester-wrapper').find('.suggest-results');
                
                if (data.length) {
                    container.html('').show();
                    for(var i in data) {
                        container.append('<a href="javascript:;" data-id="'+data[i].id+'">'+data[i].name+'</a>');
                    }

                    container.find('a').click( function() {
                        $(this).closest('.suggest-results').hide();
                        $(this).closest('.cilnic-suggester-wrapper').find('.cilnic-suggester').val( $(this).text() ).blur();
                        $(this).closest('.cilnic-suggester-wrapper').find('.suggester-hidden').val( $(this).attr('data-id') ).trigger('change');
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
            // $(this).closest('.cilnic-suggester-wrapper').find('.suggester-hidden').trigger('change');
            
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

    $('.invite-clinic-wrap .cancel-invitation').click( function() {
        $(this).closest('.invite-clinic-wrap').hide();
    });

    if ($('.invite-clinic-wrap').length) {
        $('.cilnic-suggester').on( 'keyup', function(e) {

            var container = $(this).closest('.cilnic-suggester-wrapper').find('.suggest-results');
            var keyCode = e.keyCode || e.which;
            if (keyCode === 13) {
                if (!$('.verification-popup').find('input[name="clinic_id"]').val()) {
                    $('.invite-clinic-wrap').show();
                } else {
                    $('.invite-clinic-wrap').hide();
                }
            }
        });

        $('.cilnic-suggester-wrapper .suggester-hidden').on( 'change', function(e) {
            var form = $(this).closest('form');

            $('.popup .alert').hide();

            $.ajax({
                type: "POST",
                url: $(this).attr('url'),
                data: {
                    clinic_name: $('input[name="clinic_name"]').val(),
                    clinic_id: $(this).val(),
                    user_id: $('input[name="last_user_id"]').val(),
                    user_hash: $('input[name="last_user_hash"]').val(),
                    _token: form.find('input[name="_token"]').val(),
                },
                dataType: 'json',
                success: function(ret) {
                    if (ret.success) {
                        $('.popup .alert-success').html(ret.message).show();

                        gtag('event', 'Invite', {
                            'event_category': 'DentistRegistration',
                            'event_label': 'DentistWorkplace',
                        });
                    } else {
                        $('.popup .alert-warning').html(ret.message).show();
                    }
                }
            });

        });
    }

    $('.dentist-suggester-wrapper .suggester-hidden').on( 'change', function(e) {
        var form = $(this).closest('form');

        $('.popup .alert').hide();

        $.ajax({
            type: "POST",
            url: $(this).attr('url'),
            data: {
                dentist_name: $('input[name="invitedentist"]').val(),
                dentist_id: $(this).val(),
                user_id: $('input[name="last_user_id"]').val(),
                user_hash: $('input[name="last_user_hash"]').val(),
                _token: form.find('input[name="_token"]').val(),
            },
            dataType: 'json',
            success: function(ret) {
                if (ret.success) {
                    $('.popup .alert-success-d').html(ret.message).show();

                    gtag('event', 'Invite', {
                        'event_category': 'DentistRegistration',
                        'event_label': 'ClinicTeam',
                    });
                } else {
                    $('.popup .alert-warning-d').html(ret.message).show();
                }
            }
        });

    });

    $('.invite-clinic-form').submit( function(e) {
        e.preventDefault();

        $('.popup .alert').hide();
        $(this).find('.has-error').removeClass('has-error');

        if(ajax_is_running) {
            return;
        }
        ajax_is_running = true;


        $.ajax({
            type: "POST",
            url: $(this).attr('action'),
            data: {
                clinic_name: $('input[name="clinic-name"]').val(),
                clinic_email: $('input[name="clinic-email"]').val(),
                user_id: $('input[name="last_user_id"]').val(),
                user_hash: $('input[name="last_user_hash"]').val(),
                _token: $(this).find('input[name="_token"]').val(),
            },
            dataType: 'json',
            success: (function(ret) {
                if (ret.success) {
                    $(this).hide();
                    $('.popup .alert-success').html(ret.message).show();

                    gtag('event', 'Invite', {
                        'event_category': 'DentistRegistration',
                        'event_label': 'DentistWorkplace',
                    });
                } else {
                    $('.popup .alert-warning').show();
                    $('.popup .alert-warning').html('');
                    for(var i in ret.messages) {
                        $('.popup .alert-warning').append(ret.messages[i] + '<br/>');
                        $('input[name="'+i+'"]').addClass('has-error');
                    }
                }
                ajax_is_running = false;
            }).bind(this)
        });

    } );

    $('.wh-btn').click( function() {
        showPopup('popup-wokring-time-waiting');
    });

    $('.verification-form').submit( function(e) {

        e.preventDefault();

        $('.popup .alert').hide();
        $(this).find('.has-error').removeClass('has-error');

        if(ajax_is_running) {
            return;
        }
        ajax_is_running = true;

        var that = $(this);

        $.ajax({
            type: "POST",
            url: $(this).attr('action'),
            data: {
                description: that.find('[name="description"]').val(),
                user_id: $('input[name="last_user_id"]').val(),
                user_hash: $('input[name="last_user_hash"]').val(),
                _token: that.find('input[name="_token"]').val(),
            },
            dataType: 'json',
            success: (function(ret) {
                if (ret.success) {

                    if (ret.user == 'dentist') {
                        if($('.wh-btn:visible').length) {
                            that.hide();
                        } else {
                            $('.verification-info').hide()
                        }
                    } else {
                        $('.verification-form').hide();
                    }
                    
                    $('.popup .alert-success').html(ret.message).show();

                    if (ret.user == 'dentist') {
                        gtag('event', 'ClickSave', {
                            'event_category': 'DentistRegistration',
                            'event_label': 'DentistDescr',
                        });
                    } else {
                        gtag('event', 'ClickSave', {
                            'event_category': 'DentistRegistration',
                            'event_label': 'ClinicDescr',
                        });
                    }
                } else {
                    $('.popup .descr-error').show();
                    for(var i in ret.messages) {
                        $('[name="'+i+'"]').addClass('has-error');
                    }
                }
                ajax_is_running = false;
            }).bind(this)
        });

    } );

    $('#team-job').change( function() {
        if ($(this).val() == 'dentist') {
            $('.mail-col').show();
        } else {
            $('.mail-col').hide();
        }
    });


    $('input[name="mode"]').change( function() {
        $('.ajax-alert[error="'+$(this).attr('name')+'"]').remove();
        var val = $('#mode-clinic:checked').length;
        if(val) {
            $('.title-wrap').hide();
        } else {
            $('.title-wrap').show();
        }
    } );

    $('.address-suggester').focus(function(e) {
        $('.go-to-next[step-number="3"]').addClass('disabled');
    });

    $('.go-to-next').click( function(e) {
        e.preventDefault();
        if (!$(this).hasClass('disabled')) {

            if(ajax_is_running) {
                return;
            }
            ajax_is_running = true;

            $('.ip-country').hide();
            var that = $(this);

            $.post( 
                $(this).attr('data-validator'), 
                $('#signin-form-popup').serialize(), 
                function( data ) {
                    if(data.success) {

                        gtag('event', 'ClickNext', {
                            'event_category': 'DentistRegistration',
                            'event_label': 'DentistRegistrationStep'+ that.attr('step-number'),
                        });

                        $('.ajax-alert').remove();
                        $('#register-error').hide();

                        var a = $('.sign-in-step.active');
                        a.removeClass('active');
                        a.next().addClass('active');

                    } else {
                        // $('#register-error').show();
                        // $('#register-error span').html('');
                        $('.ajax-alert').remove();
                        for(var i in data.messages) {
                            // $('#register-error span').append(data.messages[i] + '<br/>');
                            $('[name="'+i+'"]').addClass('has-error');
                            $('[name="'+i+'"]').closest('.alert-after').after('<div class="alert alert-warning ajax-alert" error="'+i+'">'+data.messages[i]+'</div>');

                            if ($('[name="'+i+'"]').closest('.modern-radios').length) {
                                $('[name="'+i+'"]').closest('.modern-radios').addClass('has-error');
                            }

                            if ($('[name="'+i+'"]').closest('.agree-label').length) {
                                $('[name="'+i+'"]').closest('.agree-label').addClass('has-error');
                            }                        
                        }
                        grecaptcha.reset();
                    }
                    ajax_is_running = false;
                }, 
                "json"
            );
        }

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

                if(data.success) {
                    fbq('track', 'DentistCompleteRegistration');
                    gtag('event', 'ClickNext', {
                        'event_category': 'DentistRegistration',
                        'event_label': 'DentistRegistrationComplete',
                    });

                    if(data.join_clinic) {
                        window.location.href = data.join_clinic;
                    } else {
                        closePopup();
                        showPopup(data.popup);
                        if (data.hash) {
                            $('input[name="last_user_hash"]').val(data.hash);
                        }
                        if (data.id) {
                            $('input[name="last_user_id"]').val(data.id);
                        }

                        if (data.is_clinic) {
                            $('.wh-btn').hide();
                        }

                        if (data.is_clinic) {
                            $('#title-clinic').show();
                            $('#title-dentist').hide();
                        } else {
                            $('#title-clinic').hide();
                            $('#title-dentist').show();
                            $('#clinic-add-team').remove();
                        }

                        $('.image-label').css('background-image', 'none');
                    }


                } else {
                    // $('#register-error span').html('');
                    // $('#register-error').show();
                    $('.ajax-alert').remove();
                    $('#step-4 .alert-after').after('<div class="alert alert-warning ajax-alert"></div>');
                    for(var i in data.messages) {
                        // $('#register-error span').append(data.messages[i] + '<br/>');
                        $('#step-4 .ajax-alert').append(data.messages[i] + '<br/>');

                        $('[name="'+i+'"]').addClass('has-error');
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

    $('#signin-form-popup input').on('keyup keypress', function(e) {
        var keyCode = e.keyCode || e.which;
        if (keyCode === 13) { 
            e.preventDefault();
            return false;
        }
    });


});