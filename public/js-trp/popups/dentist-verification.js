
var editWorkingHours;
var ajax_is_running;
var suggestClinic;
var suggestTO;
var refreshOnClosePopup;
var chooseExistingDentistActions;

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

    var suggestedDentistClick = function(elm) {
        $(elm).closest('.dentist-suggester-wrapper').find('.suggest-results').hide();
        $(elm).closest('.dentist-suggester-wrapper').find('.suggester-input').val('');

        if(ajax_is_running) {
            return;
        }
        ajax_is_running = true;

        if($('.alert-success-d').length && $('.alert-warning-d').length) {
            $('.alert-success-d').hide();
            $('.alert-warning-d').hide();
        }

        $.ajax( {
            url: lang + '/profile/dentists/invite',
            type: 'POST',
            dataType: 'json',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                invitedentistid: $(elm).attr('data-id'),
                user_id: $('input[name="last_user_id"]').length ? $('input[name="last_user_id"]').val() : '',
                user_hash: $('input[name="last_user_hash"]').length ? $('input[name="last_user_hash"]').val() : '',
            },
            success: (function( data ) {
                if($('.alert-success-d').length && $('.alert-warning-d').length) {
                    if(data.success) {
                        $('.alert-success-d').html(data.message).show();
                    } else {
                        $('.alert-warning-d').html(data.message).show();
                    }
                } else {
                    $('#dentist-add-result').html(data.message).attr('class', 'alert '+(data.success ? 'alert-success' : 'alert-warning')).show();
                }
                
                if(user_id) {
                    refreshOnClosePopup = true;
                }

                ajax_is_running = false;

            }).bind(this)
        });
    }

    var suggestDentist = function() {

        if(ajax_is_running) {
            return;
        }
        ajax_is_running = true;

        $.ajax( {
            url: 'suggest-dentist'+(user_id ? '/'+user_id : ($('input[name="last_user_id"]').length ? '/'+$('input[name="last_user_id"]').val() : '' )),
            type: 'POST',
            dataType: 'json',
            data: {
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
                        suggestedDentistClick(this);
                    });
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
                suggestedDentistClick(activeLink);
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

    $('.add-team-member-form').submit( function(e) {
        e.preventDefault();

        if(ajax_is_running) {
            return;
        }

        ajax_is_running = true;
        $(this).find('.member-alert').hide().removeClass('alert-warning').removeClass('alert-success');
        that = $(this);

        $.post( 
            $(this).attr('action'), 
            $(this).serialize() , 
            function (data) {
                if(data.success) {

                    if(data.dentists) {
                        showPopup('popup-existing-dentist', data.dentists);
                    } else {                		
                        that.find('.check-for-same').val('');
                        that.find('.photo-name-team').val('');
                        that.find('.image-label').css('background-image', 'none');
                        that.find('.image-label').find('.centered-hack').show();
                        that.find('.team-member-email').val('');
                        that.find('.team-member-name').val('').focus();
                        that.find('.member-alert').show().addClass('alert-success').html(data.message);
                        $('.existing-dentists').children().remove();

                        if (data.with_email) {
                            gtag('event', 'Invite', {
                                'event_category': 'DentistRegistration',
                                'event_label': 'ClinicTeam',
                            });
                        } else {
                            gtag('event', 'Add', {
                                'event_category': 'DentistRegistration',
                                'event_label': 'ClinicTeam',
                            });
                        }

                        if(user_id) {
                            refreshOnClosePopup = true;
                        }
                    }
                    
                } else {
                    that.find('.member-alert').show().addClass('alert-warning').html(data.message);                    
                }
                ajax_is_running = false;

            }, "json"
        );
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
                    
                    if(user_id) {
                        refreshOnClosePopup = true;
                    }

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

    chooseExistingDentistActions = function() {
    	$('.close-ex-d').click( function(e) {
			e.preventDefault();
			e.stopPropagation();

	    	if ($(this).closest('.existing-dentists').children().length > 1) {
	    		$(this).closest('.dentist-exists').remove();
	    	} else {
	    		$(this).closest('.popup').remove();
	    		if($('#verification-popup').length) {
	    			$('#verification-popup').addClass('active');
	    		}
	    	}
	    });

	    $('.choose-ex-d').click( function(e) {
			e.preventDefault();
			e.stopPropagation();

	        var ex_d_id = $(this).closest('.dentist-exists').attr('ex-dent-id');
	        var clinic_id = $('input[name="last_user_id"]').length ? $('input[name="last_user_id"]').val() : null;
	        var that = $(this);
	        var form = $('.add-team-member-form');

	        $.ajax({
	            type: "POST",
	            url: window.location.origin+'/en/profile/add-existing-dentist-team/',
	            data: {
	            	clinic_id: clinic_id,
	            	ex_d_id: ex_d_id,
	                _token: $('input[name="_token"]').val(),
	            },
	            dataType: 'json',
	            success: function(ret) {
	                if(ret.success) {

	                	that.closest('.popup').remove();
	                	if($('#verification-popup').length) {
			    			$('#verification-popup').addClass('active');
			    		}

	                	form.find('.check-for-same').val('');
	                	// that.closest('.dentist-exists').find('.ex-d-btns').append('<div class="alert alert-success" style="display:inline-block;">Added</div>');
	                	// that.closest('.dentist-exists').find('.ex-d-btns a').remove();

	                    form.find('.team-member-email').val('');
	                    form.find('.team-member-job').val('dentist');
	                    form.find('.team-member-name').val('').focus();
	                    form.find('.team-member-photo').closest('.image-label').css('background-image', 'none');
	                    form.find('.photo-name-team').val('');
	                    form.find('.member-alert').show().addClass('alert-success').html(ret.message);

                        if(user_id) {
                            refreshOnClosePopup = true;
                        }
	                	
	                } else {
	    				console.log('error');
	                }
	            },
	            error: function(ret) {
	                console.log('error');
	            }
	        });
	    });
    }
});