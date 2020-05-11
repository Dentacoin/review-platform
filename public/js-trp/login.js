var lastCivicForm = null;
var suggestClinic;

$(document).ready(function(){

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

});