
$(document).ready(function() {

    $('.whatsapp-button').click( function(e) {
        e.preventDefault();

        if(ajax_is_running) {
            return;
        }

        ajax_is_running = true;

        that = $(this);

        $.ajax({
            type: "POST",
            url: that.attr('data-url'),
            data: {
                _token: $('input[name="_token"]').val(),
            },
            dataType: 'json',
            success: function(data) {
                if(data.success) {

                    window.open('https://api.whatsapp.com/send?text=' + data.text, '_blank');

                    $('.new-invite').hide();
                    $('.success-invite').show();

                    gtag('event', 'Whatsapp', {
                        'event_category': 'ReviewInvites',
                        'event_label': 'InvitesSent',
                    });
                }
            },
            error: function(ret) {
                console.log('error');
            }
        });
        ajax_is_running = false;
    });

    editor = CodeMirror.fromTextArea(document.getElementById("copypaste"), {
        lineNumbers: true,
        matchBrackets: true,
        mode: "text/x-csharp"
    });

    var inviteRadio = function() {
        $('.invite-input-radio').change( function() {
            $('label.invite-radio').removeClass('active');
            $('label.invite-radio[for="'+$(this).attr('id')+'"]').addClass('active');
        });

        $('.bulk-invite-back').click( function() {
            $(this).closest('.copypaste-wrapper').hide();
            $(this).closest('.invite-content').find('.step'+$(this).attr('step')).find('.invite-input-radio').prop('checked', false);
            $(this).closest('.invite-content').find('.step'+$(this).attr('step')).find('.checkbox-wrapper').removeClass('active');
            $(this).closest('.invite-content').find('.step'+$(this).attr('step')).show();
        });
    }

    inviteRadio();

    $('.invite-tabs a').click( function() {
        $('.invite-tabs a').removeClass('active');
        $(this).addClass('active');
        $('.invite-content').hide();
        $('#invite-option-'+$(this).attr('data-invite')).show();
    });

    $('.try-invite-again').click( function() {
        $('.copypaste-wrapper').hide();
        $('.copypaste-wrapper.step1').show();
        $('.copypaste-wrapper.step1').find('.invite-alert').hide();

        $('.new-invite').show();
        $('.success-invite').hide();
    });

    $('.invite-patient-copy-paste-form').submit( function(e) {
        e.preventDefault();

        if(ajax_is_running) {
            return;
        }

        ajax_is_running = true;

        $('.invite-alert').hide().removeClass('alert-warning').removeClass('alert-success');

        var that = $(this);

        $.post( 
            $(this).attr('action'),
            $(this).serialize(),
            function( data ) {

                if(data.success && data.show_popup) {
                    if(data.color != 'warning') {
                        $('.new-invite').hide();
                        $('.success-invite').show();
                        if(data.message) {
                            $('.success-invite').find('.invite-alert').show().addClass('alert-'+data.color).html(data.message);
                        }
    
                        if (data.gtag_tracking) {
                            if( that.closest('#invite-option-copypaste').length) {
                                gtag('event', 'Copy-PasteBulk', {
                                    'event_category': 'ReviewInvites',
                                    'event_label': 'InvitesSent',
                                });
                            } else if(that.closest('#invite-option-file').length) {
                                gtag('event', 'FileImport', {
                                    'event_category': 'ReviewInvites',
                                    'event_label': 'InvitesSent',
                                });
                            }
                        }
                    } else {
                        $('.copypaste-wrapper').hide();
                        $('.copypaste-wrapper.step1').find('.invite-alert').show().addClass('alert-warning').html(data.message); 
                        $('.copypaste-wrapper.step1').show();
                    }
                } else if(data.success && data.info) {

                    that.closest('.copypaste-wrapper').next().find('.checkboxes-inner').html('');
                    for (var i in data.info) {
                        if(i<2) {

                            if(i == 0) {
                                that.closest('.copypaste-wrapper').next().find('.checkboxes-inner').append('<div class="checkbox-wrapper" attr="'+i+'">\
                                    <div class="invite-checkboxes-flex flex flex-mobile">\
                                        <label class="invite-radio active" for="r1">\
                                            Emails\
                                        </label>\
                                        <label class="invite-radio" for="r2">\
                                            Names\
                                        </label>\
                                    </div>\
                                    <input type="radio" name="patient-emails" value="1" class="invite-input-radio" id="r1" checked="checked"/>\
                                    <div class="copypaste-box"></div>\
                                </div>');
                            } else {
                                that.closest('.copypaste-wrapper').next().find('.checkboxes-inner').append('<div class="checkbox-wrapper" attr="'+i+'">\
                                    <div class="invite-checkboxes-flex flex flex-mobile">\
                                        <label class="invite-radio" for="r2">\
                                            Emails\
                                        </label>\
                                        <label class="invite-radio active" for="r1">\
                                            Names\
                                        </label>\
                                    </div>\
                                    <input type="radio" name="patient-emails" value="2" class="invite-input-radio" id="r2"/>\
                                    <div class="copypaste-box"></div>\
                                </div>');
                            }

                            for (var u in data.info[i]) {
                                that.closest('.copypaste-wrapper').next().find('.checkboxes-inner').find('.checkbox-wrapper[attr="'+i+'"]').find('.copypaste-box').append('<p>'+(data.info[i][u]? data.info[i][u] : '-')+'</p>');
                            }
                        }
                    }

                    that.closest('.copypaste-wrapper').hide().next().show();

                    inviteRadio();

                    if( that.closest('#invite-option-copypaste').length) {

                        gtag('event', 'Paste', {
                            'event_category': 'ReviewInvites',
                            'event_label': 'BulkInvites1',
                        });
                    }
                } else {
                    that.find('.invite-alert').show().addClass('alert-warning').html(data.message); 
                                    
                }
                ajax_is_running = false;

            }, "json"
        );
    });

    $('.invite-patient-copy-paste-form-final').submit( function(e) {
        e.preventDefault();
        
        if(ajax_is_running) {
            return;
        }

        ajax_is_running = true;

        var that = $(this);

        that.find('.invite-alert').hide().removeClass('alert-warning').removeClass('alert-success');
        that.find('button').addClass('waiting');

        $.post( 
            $(this).attr('action'), 
            $(this).serialize() , 
            function( data ) {
                
                that.find('button').removeClass('waiting');

                if(data.success && data.color != 'warning') {
                    $('.new-invite').hide();
                    $('.success-invite').show();
                    if(data.message) {
                        $('.success-invite').find('.invite-alert').show().addClass('alert-'+data.color).html(data.message);
                    }

                    if (data.gtag_tracking) {
                        
                        if( that.closest('#invite-option-copypaste').length) {
                            
                            gtag('event', 'Copy-PasteBulk', {
                                'event_category': 'ReviewInvites',
                                'event_label': 'InvitesSent',
                            });
                        } else if(that.closest('#invite-option-file').length) {

                            gtag('event', 'FileImport', {
                                'event_category': 'ReviewInvites',
                                'event_label': 'InvitesSent',
                            });
                        }
                    }
                } else {
                    $('.copypaste-wrapper').hide();
                    $('.copypaste-wrapper.step1').find('.invite-alert').show().addClass('alert-warning').html(data.message); 
                    $('.copypaste-wrapper.step1').show();
                }
                ajax_is_running = false;

            }, "json"
        );
    });

    $('#invite-file').change(function() {
        var file = $('#invite-file')[0].files[0].name;
        $(this).closest('label').find('span').text(file);
    });

    $('.invite-patient-file-form').submit( function(e) {
        e.preventDefault();

        if(ajax_is_running) {
            return;
        }

        ajax_is_running = true;

        $(this).find('.invite-alert').hide().removeClass('alert-warning').removeClass('alert-success');
        var that = $(this);
        var unique_id = $(this).closest('.invite-content').attr('radio-id');

        var formData = new FormData(this);

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            cache: false,
            contentType: false,
            processData: false
        }).done( (function (data) {
            if(data.success && data.info) {

                that.closest('.copypaste-wrapper').next().find('.checkboxes-inner').html('');
                for (var i in data.info) {
                    if(i<2) {

                        if(i == 0) {
                            that.closest('.copypaste-wrapper').next().find('.checkboxes-inner').append('<div class="checkbox-wrapper" attr="'+i+'">\
                                <div class="invite-checkboxes-flex flex flex-mobile">\
                                    <label class="invite-radio active" for="r1">\
                                        Emails\
                                    </label>\
                                    <label class="invite-radio" for="r2">\
                                        Names\
                                    </label>\
                                </div>\
                                <input type="radio" name="patient-emails" value="1" class="invite-input-radio" id="r1" checked="checked"/>\
                                <div class="copypaste-box"></div>\
                            </div>');
                        } else {
                            that.closest('.copypaste-wrapper').next().find('.checkboxes-inner').append('<div class="checkbox-wrapper" attr="'+i+'">\
                                <div class="invite-checkboxes-flex flex flex-mobile">\
                                    <label class="invite-radio" for="r2">\
                                        Emails\
                                    </label>\
                                    <label class="invite-radio active" for="r1">\
                                        Names\
                                    </label>\
                                </div>\
                                <input type="radio" name="patient-emails" value="2" class="invite-input-radio" id="r2"/>\
                                <div class="copypaste-box"></div>\
                            </div>');
                        }

                        for (var u in data.info[i]) {
                            that.closest('.copypaste-wrapper').next().find('.checkboxes-inner').find('.checkbox-wrapper[attr="'+i+'"]').find('.copypaste-box').append('<p>'+(data.info[i][u]? data.info[i][u] : '-')+'</p>');
                        }
                    }
                }

                that.closest('.copypaste-wrapper').hide().next().show();

                inviteRadio();

                if( that.closest('#invite-option-copypaste').length) {

                    gtag('event', 'Paste', {
                        'event_category': 'ReviewInvites',
                        'event_label': 'BulkInvites1',
                    });
                }
            } else {
                that.find('.invite-alert').show().addClass('alert-warning').html(data.message); 
                                
            }
            ajax_is_running = false;

        }).bind(this) ).fail(function (data) {
                console.log('error');
            // $(this).find('.alert').addClass('alert-danger').html('Грешка, моля, опитайте отново.').show();
        });

    });
});