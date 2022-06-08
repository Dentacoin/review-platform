
var editWorkingHours;
var ajax_is_running;
var suggestClinic;
var suggestTO;

$(document).ready(function() {

    editWorkingHours();

    $('.skip').click( function() {
        $('.step').hide();
        if($('.step[step="'+$(this).attr('to-step')+'"]').length) {
            $('.step[step="'+$(this).attr('to-step')+'"]').show();
        } else {
            $('.step[step="'+(parseInt($(this).attr('to-step')) + 1)+'"]').show();
        }
    });

    $('.invite-manual').click( function() {
        $('.invite-dentist-form').hide();
        $('.add-team-manual').show();
        setTimeout( function() {
            handleTooltip();
        }, 1000);
    });
    
    $('.invite-existing-dentist').click( function() {
        $('.invite-dentist-form').show();
        $('.add-team-manual').hide();
        $('.member-alert').hide();
    });

    $('.team-member-job').change( function() {
        if ($(this).val() == 'dentist') {
            $('.dentist-col').show();
        } else {
            $('.dentist-col').hide();
        }
    });

    $('.edit-working-hours-form').off('submit').submit( function(e) {
        e.preventDefault();

        if(ajax_is_running) {
            return;
        }
        ajax_is_running = true;

        $.post( 
            $(this).attr('action'), 
            $(this).serialize() , 
            (function( data ) {
                if(data.success) {
                    $('.step').hide()
                    if($('.step[step="2"]').length) {
                        $('.step[step="2"]').show();
                    } else {
                        $('.step[step="3"]').show();
                    }
                }
                ajax_is_running = false;
            }).bind(this), "json"
        );
    });

    var suggestDentist = function() {

        if(ajax_is_running) {
            return;
        }
        ajax_is_running = true;

        $.ajax( {
            url: 'suggest-dentist'+(user_id ? '/'+user_id : ''),
            type: 'POST',
            dataType: 'json',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                invitedentist: $(this).val()
            },
            success: (function( data ) {
                var container = $(this).closest('.dentist-suggester-wrapper').find('.suggest-results');
                
                if (data.length) {
                    container.html('').show();
                    
                    for(var i in data) {

                        var is_partner = data[i].is_partner ? '\
							<div class="result-partner-dentist">\
								<div class="result-partner-dentist-wrapper">\
									<img src="'+images_path+'/mini-logo-white.svg">\
									<span>Dentacoin</span> Partner\
								</div>\
							</div>\
						' : '';

                        container.append('<a href="javascript:;" data-id="'+data[i].id+'">\
                            <div class="flex flex-mobile">\
                                <div class="result-image-dentist">\
                                    <img src="'+data[i].avatar+'"/>\
                                </div>\
                                <div class="result-name-dentist">\
                                    <p>'+data[i].name+'</p>\
                                    <span>'+data[i].location+'</span>\
                                </div>\
                                '+is_partner+'\
                            </div>\
                        </a>')
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

    //add registered dentist to team
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

    //add short description
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

                    closePopup();
                } else {
                    $('.popup .descr-error').show();
                    for(var i in ret.messages) {
                        $('[name="'+i+'"]').addClass('has-error');
                    }
                }
                ajax_is_running = false;
            }).bind(this)
        });
    });
});