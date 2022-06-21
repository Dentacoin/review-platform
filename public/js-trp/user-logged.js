var videoStart, videoLength;
var showFullReview;
var handleDCNreward;
var suggestClinic;
var suggestClinicClick;
var suggestTO;
var editWorkingHours;
var editor;
var fb_page_error;
var load_maps = false;
var showPartnerWalletPopup;

$(document).ready(function() {

    if(showPartnerWalletPopup) {
        showPopup('add-wallet-address');
    }

    //button Edit Mode
    $('.turn-on-edit-mode').click( function() {
        $('body').toggleClass('edit-dentist-profile-mode');

        if($('body').hasClass('edit-dentist-profile-mode')) {
            $(this).find('span').html($(this).attr('to-not-edit'));
            $('.tab-title[data-tab="reviews"]').addClass('grayed');
            $('.patients-tab').addClass('grayed');
            $('[data-popup-logged="popup-invite"]').addClass('disabled-button');
            $('[data-popup-logged="popup-widget"]').addClass('disabled-button');
            $('.add-branch').addClass('disabled-button');

            //if my patients tab is active -> deactivate
            $('.tab-title.patients-tab').removeClass('active');
            $('.tab-sections').show();
            $('.asks-section').hide();

        } else {
            $(this).find('span').html($(this).attr('to-edit'));
            $('.edit-mode').removeClass('edit-mode');
            
            if(!$('.tab-title[data-tab="reviews"]').hasClass('fixed-grayed')) {
                $('.tab-title[data-tab="reviews"]').removeClass('grayed');
            }
            $('.patients-tab').removeClass('grayed');
            $('[data-popup-logged="popup-invite"]').removeClass('disabled-button');
            $('[data-popup-logged="popup-widget"]').removeClass('disabled-button');
            $('.add-branch').removeClass('disabled-button');
        }
    });

    $('.edit-field-button').click( function(e) {
        e.preventDefault();
        e.stopPropagation();

        $('.tooltip-window').hide();

        if($(this).closest('.socials-wrapper').length) {

            let editWrap = $(this).closest('.socials-wrapper');
            editWrap.find('.socials').hide();
            editWrap.find('.edit-field').css('display', 'flex');

        } else if($(this).hasClass('toggle-section')) {

            $('.'+$(this).attr('toggle-section')).toggleClass('edit-mode');

        } else if($(this).hasClass('edit-locations')) {

            $('.location-section').toggleClass('edit-mode');
            $('.gallery-flickity').flickity('resize');

            $('.map-container').hide();
            $('.address-flickity').flickity('resize');

            let editWrap = $('#locations').find('.edit-field');
            editWrap.find('.edited-field').hide();
            editWrap.find('.edit-field-button').hide();
            editWrap.find('.edit-wrapper').show();

        } else if($(this).hasClass('edit-description-button')) {

            var cls = $(this).closest('.tab-inner-section').find('[role="presenter"]').attr('class');
            $('.'+cls+'[role="editor"]').show();
            $('.'+cls+'[role="presenter"]').hide();

        } else if($(this).hasClass('scroll-to')) {

            $('html, body').animate({
                scrollTop: $('.'+$(this).attr('scroll')).offset().top - $('header').outerHeight() - $('.tab-titles').outerHeight()
            }, 500);
            
        } else {

            let editWrap = $(this).closest('.edit-field');
            editWrap.find('.edited-field').hide();
            editWrap.find('.edit-field-button').hide();
            editWrap.find('.edit-wrapper').show();
        }
    });

    $('[role="editor"] form').off('submit').submit( function(e) {
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
                    var cls = $(this).closest('[role="editor"]').attr('class');
                    $('.'+cls+'[role="editor"]').hide();
                    $('.'+cls+'[role="presenter"]').show();

                    var value_here = $('.'+cls+'[role="presenter"] .value-here');

                    if(data.value.length) {
                        value_here.html(data.value);
                    } else {
                        value_here.html(value_here.attr('empty-value'));
                    }
                    $(this).find('.alert').hide();
                } else {
                    $(this).find('.alert').show();
                    $(this).find('.alert').html('');
                    for(var i in data.messages) {
                        $(this).find('.alert').append(data.messages[i]);
                    }

                    $('html, body').animate({
                        scrollTop: $(this).offset().top - $('header').height()
                    }, 500);
                }
                ajax_is_running = false;
            }).bind(this), "json"
        );
    });

    $('.edit-wrapper').off('submit').submit( function(e) {
        e.preventDefault();

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

                    for(var i in data.inputs) {

                        if(i == 'name' && typeof data.inputs['title'] != 'undefined') {
                            $('#value-'+i).html(data.inputs['name']);
                        } else {
                            $('#value-'+i).html(data.inputs[i]);
                            if(i == 'address') {
                                $('#value-address-map').html(data.inputs[i]);
                                // $('.map-container').show();
                            }
                        }
                    }

                    if(typeof data.inputs['avatar'] != 'undefined') {
                        that.find('.image-label').css('background-image', 'url('+data.inputs['avatar']+')').show();
                        that.find('.cropper-container').hide();
                        that.find('.avatar-name-wrapper').hide();
                        that.find('.save-avatar').hide();
                    }

                    if(typeof data.inputs['current-email'] != 'undefined') {
                        that.closest('.socials-wrapper').find('.social.email-social').attr('href', 'mailto:'+data.inputs['current-email']);
                    }

                    if(typeof data.inputs['email_public'] != 'undefined') {
                        that.closest('.socials-wrapper').find('.social.email-social').attr('href', 'mailto:'+data.inputs['email_public']);
                    }

                    if(typeof data.inputs['socials'] != 'undefined') {
                        that.closest('.socials-wrapper').find('.socials').show();
                        that.closest('.socials-wrapper').find('.edit-field').hide();
                        that.closest('.socials-wrapper').find('.social:not(.email-social)').remove();

                        for(var i in data.inputs['socials']) {
                            if(data.inputs['socials'][i]) {
                                that.closest('.socials-wrapper').find('.socials').append('<a class="social '+i+'" href="'+data.inputs['socials'][i]+'" target="_blank">\
                                    <img src="'+images_path+'/social-network/'+i+'.svg" height="26"/>\
                                </a>');
                            }
                        }

                        that.closest('.socials-wrapper').find('.edit-field-button').insertAfter(that.closest('.socials-wrapper').find('.social').last());
                    }

                    if(!that.closest('#locations').length) {
                        let editWrap = that.closest('.edit-field');
                        editWrap.find('.edited-field').show();
                        editWrap.find('.edit-field-button').show();
                        editWrap.find('.edit-wrapper').hide();
                    }

                    that.find('.alert').hide();
                } else {
                    let alert = that.find('.alert:not(.secondary-info)')
                    
                    alert.html('');
                    for(var i in data.messages) {
                        alert.append(data.messages[i]);
                    }
                    alert.show();
                }
                ajax_is_running = false;
            }).bind(this), "json"
        );
    });


    //specializations & payment methods
    $('.checkboxes-wrapper input').change( function() {
        if($(this).closest('.edit-mode').length) {
            if($(this).closest('.checkboxes-wrapper').hasClass('not-added')) {
                $(this).closest('form').find('.checkboxes-wrapper:not(.not-added)').append($(this).closest('label'));
            } else {
                $(this).closest('form').find('.checkboxes-wrapper.not-added').append($(this).closest('label'));
            }
        }
    });

    $('.remove-checkbox').click( function() {
        $(this).closest('label').find('input').prop('checked', false);
        $(this).closest('label').find('input').trigger('change');
    });

    $('.edit-checkboxes-form').off('submit').submit( function(e) {
        e.preventDefault();

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
                    that.closest('.checkbox-section').removeClass('edit-mode');
                } else {
                    let alert = that.find('.alert')
                    
                    alert.html('');
                    for(var i in data.messages) {
                        alert.append(data.messages[i]);
                    }
                    alert.show();
                }
                ajax_is_running = false;
            }).bind(this), "json"
        );
    });

    $('.payments-section .open-my-account').click( function() {
        if(!$('body').hasClass('edit-dentist-profile-mode')) {
            window.location.href = 'https://account.dentacoin.com/trusted-reviews?platform=trusted-reviews';
        }
    });
    
    //end specializations & payment methods


    //working hours
    editWorkingHours();

    $('.edit-working-hours-form').off('submit').submit( function(e) {
        e.preventDefault();

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
                    that.closest('.open-hours-section').removeClass('edit-mode');

                    var wh = data.inputs.work_hours;
                    for(var i in wh) {
                        if(!data.inputs['day_'+i] && wh[i][0][0] != null && wh[i][0][1] != null && wh[i][1][0] != null && wh[i][1][1] != null) {
                            $('.open-hours-section .col-'+i+' .working-hours-wrap p').html(wh[i][0][0]+':'+wh[i][0][1]+' - '+wh[i][1][0]+':'+wh[i][1][1]);
                        } else {
                            $('.open-hours-section .col-'+i+' .working-hours-wrap p').html('Closed');
                        }
                    }
                }
                ajax_is_running = false;
            }).bind(this), "json"
        );
    });
    //end working hours

    var removeLanguages = function() {
        $('.remove-lang').click( function() {
    
            if(ajax_is_running) {
                return;
            }
            ajax_is_running = true;
    
            var that = $(this);
    
            $.ajax( {
                url: $('.edit-languages').attr('remove-url'),
                type: 'POST',
                dataType: 'json',
                data: {
                    language: $(this).attr('language'),
                    _token: $('meta[name="csrf-token"]').attr('content'),
                },
                success: (function( data ) {
                    if(data.success) {
                        if(that.closest('.languages-wrapper').find('option:not(.hidden-option)').length == 1) {
                            that.closest('.languages-wrapper').find('select').show();
                        }
                        that.closest('.languages-wrapper').find('option[value="'+that.attr('language')+'"]').removeClass('hidden-option');
                        that.closest('.bubble').remove();
                    }
    
                    ajax_is_running = false;
    
                }).bind(this)
            });
        });
    }
    removeLanguages();

    $('#dentist-languages').change( function() {
        $(this).closest('form').submit();
    });

    $('.edit-languages').off('submit').submit( function(e) {
        e.preventDefault();

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
                    that.find('select').val('');
                    that.find('option[value="'+data.inputs.languages+'"]').addClass('hidden-option');
                    $('<span class="bubble">\
                        '+data.inputs.languages+'\
                        <a href="javascript:;" class="remove-lang" language="'+data.inputs.languages+'">\
                            <img class="close-icon" src="'+images_path+'/close-icon-blue.png" width="10"/>\
                        </a>\
                    </span>').insertBefore(that);
                    removeLanguages();
                    
                    if(that.find('option:not(.hidden-option)').length == 1) {
                        that.find('select').hide();
                    }
                } else {
                    console.log('error');
                }
                ajax_is_running = false;
            }).bind(this), "json"
        );
    });

    $('[name="experience"]').change( function() {
        $('.edit-experience-form').submit();
    });

    $('.edit-experience-form').off('submit').submit( function(e) {
        e.preventDefault();

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
                    if($('.chosen-experience').length) {
                        $('.chosen-experience').find('.bubble').html(data.inputs.experience);
                    } else {
                        $('<div class="chosen-experience">\
                            <span class="bubble">\
                                '+data.inputs.experience+'\
                            </span>\
                        </div>').insertBefore('.edit-experience-form');
                    }
                    that.closest('.experience-wrapper').removeClass('edit-mode');
                } else {
                    console.log('error');
                }
                ajax_is_running = false;
            }).bind(this), "json"
        );
    });

    if($('.datepicker').length) {
        $('.datepicker').datepicker({
            todayHighlight: true,
            autoclose: true,
            changeMonth: true,
            changeYear: true,
        });
    }

    $('.edit-founded-form').off('submit').submit( function(e) {
        e.preventDefault();

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
                    if($('.chosen-founded').length) {
                        $('.chosen-founded').html(data.inputs.founded_at);
                    } else {
                        $('<div class="chosen-founded">\
                            '+data.inputs.founded_at+'\
                        </div>').insertBefore('.edit-founded-form');
                    }
                    that.closest('.founded-wrapper').removeClass('edit-mode');
                } else {
                    console.log('error');
                }
                ajax_is_running = false;
            }).bind(this), "json"
        );
    });

    $('.edit-announcement-form').off('submit').submit( function(e) {
        e.preventDefault();

        if(ajax_is_running) {
            return;
        }
        ajax_is_running = true;

        var that = $(this);
        that.find('.input').removeClass('has-error');

        $.post( 
            $(this).attr('action'), 
            $(this).serialize() , 
            (function( data ) {
                if(data.success) {
                    
                    if(data.inputs.announcement_title) {
                        $('.announcement-wrap h4 span').html(data.inputs.announcement_title);
                    } else {
                        $('.announcement-wrap h4 span').html('Add office update');
                    }
                    
                    if(data.inputs.announcement_description) {
                        $('.announcement-description').html(data.inputs.announcement_description.substring(0,150));
                        $('.announcement-description').attr('short-text', data.inputs.announcement_description.substring(0,150));
                        $('.announcement-description').attr('long-text', data.inputs.announcement_description);

                        if(data.inputs.announcement_description.length >= 150) {
                            $('.show-full-announcement').show();
                        } else {
                            $('.show-full-announcement').hide();
                        }
                        $('.announcement-subtitle').show();
                        $('.announcement-wrapper').removeClass('show-on-edit-mode');

                    } else {
                        $('.announcement-description').html('');
                        $('.show-full-announcement').hide();
                        $('.announcement-subtitle').hide();
                        $('.announcement-wrapper').addClass('show-on-edit-mode');
                    }

                    that.closest('.announcement-wrapper').removeClass('edit-mode');
                } else {
                    if(data.messages) {
                        for(var i in data.messages) {
                            that.find('[name="'+i+'"]').addClass('has-error');
                        }
                    }
                }
                ajax_is_running = false;
            }).bind(this), "json"
        );
    });

    var removeEducationBox = function() {
        $('.remove-education-info').click( function() {
            $(this).closest('.flex').remove();
        });
    }
    removeEducationBox();

    $('.add-education-info').click( function() {
        var cloned = $(this).closest('.education-wrap').find('.flex').first().clone(true).insertBefore( $(this).closest('.education-wrap').find('.add-education-info') );

        cloned.find('input').val('');

        removeEducationBox();
    });

    $('.edit-education-info-form').off('submit').submit( function(e) {
        e.preventDefault();

        if(ajax_is_running) {
            return;
        }
        ajax_is_running = true;

        var that = $(this);

        $.post( 
            $(this).attr('action'), 
            $(this).serialize(), 
            (function( data ) {
                if(data.success) {

                    if(data.inputs.education_info[0] !== null) {
                        let chosenFounded = '';
                        for(let i in data.inputs.education_info) {
                            chosenFounded+= ('•&nbsp;&nbsp;&nbsp;'+data.inputs.education_info[i]+'<br/>');
                        }

                        if($('.chosen-education').length) {
                            $('.chosen-education').html(chosenFounded);
                        } else {
                            $('<div class="chosen-education">\
                                '+chosenFounded+'\
                            </div>').insertBefore('.edit-education-info-form');
                        }
                        $('.chosen-education').show();
                    } else {
                        $('.chosen-education').hide();
                    }

                    $('.education-wrapper').removeClass('edit-mode');
                    
                } else {
                    console.log('error');
                }
                ajax_is_running = false;
            }).bind(this), "json"
        );
    });


    if($('.edit-mode-line').length) {
        $(window).scroll( function() {
            
            if($(window).scrollTop() > 0) {
                $('.edit-mode-line').show();
            } else {
                if(!$('body').hasClass('edit-dentist-profile-mode')) {
                    $('.edit-mode-line').hide();
                }
            }
        });
    }




    //<------------ GALLERY ------------>

    var handleGalleryRemoved = function() {
    
        $('.delete-gallery').off('click').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var r = confirm( $(this).attr('sure') );
            if(!r) {
                return;
            }

            if(ajax_is_running) {
                return;
            }
            ajax_is_running = true;

            var id = $(this).closest('.slider-wrapper').attr('photo-id');
            $.ajax( {
                url: lang + '/profile/gallery/delete/'+id,
                type: 'GET',
                dataType: 'json',
                success: (function( data ) {
                    ajax_is_running = false;
                    $('.gallery-flickity').flickity( 'remove', $(this).closest('.slider-wrapper') );
                }).bind(this)
            });
        });
    }
    handleGalleryRemoved();

    if($(".add-gallery-image").length) {

        $(".add-gallery-image").filedrop({
            fallback_id: 'fallbackFileDrop',
            url: '/api/upload.php',
            paramname: 'fileUpload',
            maxfilesize: 2,
            allowedfiletypes: ['image/jpeg','image/png'],	// filetypes allowed by Content-Type.  Empty array means no restrictions
            allowedfileextensions: ['.jpg','.jpeg','.png'], // file extensions allowed. Empty array means no restrictions
            uploadFinished: function(index, file, json, timeDiff) {
        
                $('.add-gallery-image').addClass('loading');
        
                var sure_text = $('#add-gallery-photo').attr('sure-trans');
                var that = $('#add-gallery-photo');
                var upload = new Upload(file, that.attr('upload-url'), function(data) {
                    
                    $('.add-gallery-image').removeClass('loading');
        
                    var html = '<a href="'+data.original+'" data-lightbox="user-gallery" class="slider-wrapper">\
                        <div class="slider-image cover" style="background-image: url(\''+data.url+'\')">\
                            <div class="delete-gallery delete-button" sure="'+sure_text+'">\
                                <img class="close-icon" src="'+all_images_path+'/close-icon-white.png"/>\
                            </div>\
                        </div>\
                    </a>\
                    ';
        
                    $('.gallery-flickity').flickity({
                        autoPlay: false,
                        wrapAround: true,
                        cellAlign: 'left',
                        freeScroll: true,
                        groupCells: 1,
                        draggable: false,
                    });
        
                    $('.gallery-flickity').flickity( 'insert', $(html), 1 );
                    if($('.gallery-flickity .slider-wrapper').length > 2) {
                        $('.gallery-flickity').flickity( 'selectCell', 1 );
                    }
                    handleGalleryRemoved();
        
                    if($('body').hasClass('guided-tour')) {
                        $('.bubble-guided-tour .skip-step').trigger('click');
                    }
                    ajax_is_running = false;
                });
                upload.doUpload();
            },
        });
    }

    $('#add-gallery-photo').change( function() {
        if(ajax_is_running) {
            return;
        }
        ajax_is_running = true;

        $('.add-gallery-image').addClass('loading');

        var sure_text = $('#add-gallery-photo').attr('sure-trans');
        var file = $(this)[0].files[0];
        var that = $(this);

        var fileExtension = ['jpeg', 'jpg', 'png'];
        if ($.inArray(that.val().split('.').pop().toLowerCase(), fileExtension) == -1) {
            console.log("Only formats are allowed : "+fileExtension.join(', '));
        } else {
            var upload = new Upload(file, $(this).attr('upload-url'), function(data) {
                $('.add-gallery-image').removeClass('loading');

                var html = '<a href="'+data.original+'" data-lightbox="user-gallery" class="slider-wrapper">\
                    <div class="slider-image cover" style="background-image: url(\''+data.url+'\')">\
                        <div class="delete-gallery delete-button" sure="'+sure_text+'">\
                            <img class="close-icon" src="'+all_images_path+'/close-icon-white.png"/>\
                        </div>\
                    </div>\
                </a>\
                ';

                $('.gallery-flickity').flickity({
                    autoPlay: false,
                    wrapAround: true,
                    cellAlign: 'left',
                    freeScroll: true,
                    groupCells: 1,
                    draggable: false,
                });

                $('.gallery-flickity').flickity( 'insert', $(html), 1 );
                if($('.gallery-flickity .slider-wrapper').length > 2) {
                    $('.gallery-flickity').flickity( 'selectCell', 1 );
                }
                handleGalleryRemoved();

                if($('body').hasClass('guided-tour')) {
                    $('.bubble-guided-tour .skip-step').trigger('click');
                }
                ajax_is_running = false;
            });
            upload.doUpload();
        }
    });

    //<------------ END GALLERY ------------>




    $('.team-container .delete-invite').click( function(e) {
        e.preventDefault();
        e.stopPropagation();

        var that = $(this);

        var r = confirm( that.attr('sure') );
        if(!r) {
            return;
        }

        if(ajax_is_running) {
            return;
        }
        ajax_is_running = true;

        var id = that.closest('.team').attr('invite-id');
        $.ajax( {
            url: lang + '/profile/invites/delete/'+id,
            type: 'GET',
            dataType: 'json',
            success: (function( data ) {
                ajax_is_running = false;
                that.closest('.team').remove();
            }).bind(this)
        });
    });

    //Remove team member
    $('.team-container .deleter').click( function(e) {
        e.preventDefault();
        e.stopPropagation();
        var r = confirm( $(this).attr('sure') );
        if(!r) {
            return;
        }

        if(ajax_is_running) {
            return;
        }
        ajax_is_running = true;

        var id = $(this).closest('.slider-wrapper').attr('dentist-id');
        $.ajax( {
            url: lang + '/profile/dentists/delete/'+id,
            type: 'GET',
            dataType: 'json',
            success: (function( data ) {
                ajax_is_running = false;
                teamFlickity.flickity( 'remove', $(this).closest('.slider-wrapper') );
            }).bind(this)
        });
    });

    //
    //Ask clinic to join
    //

    var removeWorkplace = function() {
        $('#workplaces-list .remove-dentist').click( function(e) {
            e.preventDefault();
    
            if(ajax_is_running) {
                return;
            }
            ajax_is_running = true;
    
            $.get( 
                $(this).attr('href'),
                (function( data ) {
                    $(this).closest('.workplace-clinic').remove();
                    ajax_is_running = false;
                }).bind(this), "json"
            );
        });
    }
    removeWorkplace();

    suggestClinicClick = function(elm) {
        var id = $(elm).attr('data-id');

        $(elm).closest('.suggest-results').hide();
        $(elm).closest('.suggester-wrapper').find('.suggester-input').val('');

        if(ajax_is_running) {
            return;
        }
        ajax_is_running = true;

        $.ajax({
            url: lang + '/profile/clinics/invite',
            type: 'POST',
            dataType: 'json',
            data: {
                joinclinicid: $(elm).attr('data-id')
            },
            success: (function( data ) {
                $('#workplaces-list').prepend('\
                    <span class="workplace-clinic">\
                        <a href="'+data.clinic.link+'">'
                            +data.clinic.name+
                        '</a>\
                        <a class="remove-dentist" href="'+window.location.origin+'/'+lang+'/profile/clinics/delete/'+data.clinic.id+'/">\
                            <img class="close-icon" src="'+images_path+'/close-icon-blue.png" width="10">\
                        </a>\
                    </span>\
                ');

                $('#workplaces-list .show-on-edit-mode-inline').remove();

                removeWorkplace();

                ajax_is_running = false;

            }).bind(this)
        });
    }

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
                var container = $(this).closest('.clinic-suggester-wrapper').find('.suggest-results');
                
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
                        suggestClinicClick(this);
                    });
                } else {
                    container.hide();                    
                }

                ajax_is_running = false;

            }).bind(this)
        });
    }

    $('.clinic-suggester').closest('form').on('keyup keypress', function(e) {
        var keyCode = e.keyCode || e.which;
        if (keyCode === 13) { 
            e.preventDefault();
            return false;
        }
    });

    $('.clinic-suggester').on( 'keyup', function(e) {
        
        var container = $(this).closest('.clinic-suggester-wrapper').find('.suggest-results');
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
                suggestClinicClick(activeLink);
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


    $('.team-container .action-buttons div').click( function(e) {
        e.preventDefault();
        e.stopPropagation();

        var that = $(this);

        if( that.hasClass('reject-button') ) {
            var r = confirm( that.attr('sure') );
            if(!r) {
                return;
            }
        }

        if(ajax_is_running) {
            return;
        }
        ajax_is_running = true;

        $.get( 
            that.attr('action'),
            (function( data ) {
                if( that.hasClass('accept-button') ) {
                    that.closest('.team').find('.action-buttons').remove();
                } else {
                    that.remove();
                }
                if(!$('.team-container .accept-button').length) {
                    $('.pending-team').hide();
                }
                ajax_is_running = false;
            }).bind(this), "json"
        );
    });


    //socials
    $('.current-social').click( function(e) {
        var elem = $(this).closest('.social-networks').find('.social-dropdown');
        $('.social-dropdown').each( function() {
            if(!$(this).is(elem)) {
                $(this).removeClass('active');
            }
        })
        elem.toggleClass('active');
    });

    $('.handle-asks').click( function() {
        var that = $(this);

        if(ajax_is_running) {
            return;
        }
        ajax_is_running = true;

        $.ajax( {
            url: that.attr('link-form'),
            type: 'POST',
            dataType: 'json',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
            },
            success: (function( data ) {
                if(data.success) {
                    that.closest('td').html('\
                        <span class="'+data.class+'">\
                            '+data.text+'\
                        </span>\
                    ');

                    if(!data.patientAsksPendingCount) {
                        $('.patientAsksPendingCount').remove();
                    }
                }

                ajax_is_running = false;

            }).bind(this)
        });
    });

    $('body').click( function(e) {
        if (!$(e.target).closest('.social-networks').length) {
            $('.social-dropdown').removeClass('active');
        }
    });

    $('.social-link').click( function() {
        var el = $(this).closest('.social-wrap');
        el.find('.current-social').attr('cur-type', $(this).attr('social-type') );
        el.find('.current-social img').attr('src', el.find('.current-social img').attr('src-attr')+'/'+$(this).attr('social-class')+'.svg');
        el.find('.social-link-input').attr('name', 'socials['+$(this).attr('social-type')+']');
        el.find('.social-dropdown').removeClass('active');

        //get a List of elements that should be hidden
        var hideClasses = [];
        $('.social-wrap .current-social').each( function() {
            if( hideClasses.indexOf( $(this).attr('cur-type') ) == -1 ) {
                hideClasses.push( $(this).attr('cur-type') );
            }
        });

        $('.social-wrap .social-dropdown').each( function() {
            $(this).find('a').each( function() {
                if( hideClasses.indexOf( $(this).attr('social-type') ) == -1 ) {
                    $(this).removeClass('inactive');
                } else {
                    $(this).addClass('inactive');
                }
            });
        });
    });

    $('.add-social-profile').click( function() {

        var social_wrapper = $('.socials-wrapper').find('.social-wrap');
        var cloned = social_wrapper.first().clone(true).insertAfter( $(this).closest('.socials-wrapper').find('.social-wrap').last() );

        cloned.addClass('new');
        cloned.find('.social-link-input').val('');
        cloned.find('.social-dropdown .social-link:not(.inactive)').first().trigger('click');

        if((social_wrapper.length +1) == 9 ) {
            $(this).hide();
            $('.socials-wrapper').find('.save-field').removeClass('with-margin');
        } else {
            $('.socials-wrapper').find('.save-field').addClass('with-margin');
        }

        if($('body').hasClass('guided-tour')) {
            resizeGuidedTourWindow($('.social-wrap:visible'), false);
        }
    });

    $('.remove-social').click( function() {
        $(this).closest('.social-wrap').remove();
    });

    //end socials

    $('input[name="current-email"]').change( function() {
        if ($(this).is(':checked')) {
            $(this).closest('.email-wrapper').find('input[name="email_public"]').val($(this).attr('cur-email'));
            $(this).closest('.email-wrapper').find('input[name="email_public"]').attr('disabled','disabled');
            $(this).closest('.email-wrapper').find('.email-wrap').addClass('disabled-email');
        } else {
            $(this).closest('.email-wrapper').find('input[name="email_public"]').val('');
            $(this).closest('.email-wrapper').find('input[name="email_public"]').removeAttr('disabled');
            $(this).closest('.email-wrapper').find('.email-wrap').removeClass('disabled-email');
        }
    }); 

    $('table.paging').each(function() {
        var table = $(this);
        var currentPage = 0;
        var numPerPage = table.attr('num-paging');
        var numRows = table.find('tbody tr').length;
        var numPages = Math.ceil(numRows / numPerPage);
        var pager = $('<div class="pager"></div>');

        if(numRows > numPerPage) {
            table.bind('repaginate', function() {
                table.find('tbody tr').hide().slice(currentPage * numPerPage, (currentPage + 1) * numPerPage).show();
            });
            table.trigger('repaginate');

            for (var page = 0; page < numPages; page++) {
                $('<span class="page-number"></span>').text(page + 1).bind('click', {
                    newPage: page
                }, function(event) {
                    currentPage = event.data['newPage'];
                    table.trigger('repaginate');
                    $(this).addClass('active').siblings().removeClass('active');
                }).appendTo(pager).addClass('clickable');
            }
            pager.insertAfter(table).find('span.page-number:first').addClass('active');
        }
    });

    //Ask for trusted
    
    $('.ask-dentist').click( function(e) {
        e.preventDefault();

        if(ajax_is_running) {
            return;
        }
        ajax_is_running = true;
        var that = $(this);

        $.get( 
            $(this).attr('href'), 
            function( data ) {
                if(data.success) {
                    if (that.hasClass('ask-dentist-after-submit-review')) {
                        that.remove();
                        closePopup();
                    } else {
                        $(that).remove();
                    }

                    gtag('event', 'Request', {
                        'event_category': 'Reviews',
                        'event_label': 'InvitesAsk',
                    });
                }
            }
        );
    });

    $('.ask-review').click( function() {
        var id = $(this).attr('review-id');
        showFullReview(id, $('#cur_dent_id').val());
    });

    $('.invite-again').click( function(e) {
        e.preventDefault();

        var that = $(this);
        var invite_url = $(this).attr('data-href');
        var invitation_id = $(this).attr('inv-id');

        $.ajax({
            type: "POST",
            url: invite_url,
            data: {
                _token: $('input[name="_token"]').val(),
                id: invitation_id,
            },
            dataType: 'json',
            success: function(ret) {
                if(ret.success) {
                    that.remove();
                } else {
                    console.log('error');
                }
            },
            error: function(ret) {
                console.log('error');
            }
        });
    });


    // <------ Guided TOUR ---------->

    var resizeGuidedTourWindow = function(elm, bubble_at_top) {

        var element_top = elm.offset().top - $(window).scrollTop();
        var element_left = elm.offset().left;
        var element_heigth = elm.outerHeight();
        var element_width = elm.outerWidth();

        if(bubble_at_top) {
            $('.bubble-guided-tour').css('top', element_top - $('.bubble-guided-tour').innerHeight() - 20 );
        } else {
            $('.bubble-guided-tour').css('top', element_top + element_heigth + 20 );
        }

        if($(window).outerWidth() > 768) {
            $('.bubble-guided-tour').css('left', element_left );
        }

        if($(window).outerWidth() <= 768) {
            setTimeout( function() {

                $('.bubble-guided-tour').css('left', element_left + ( ( element_width - $('.bubble-guided-tour').outerWidth() ) / 2) );

                if( $('.bubble-guided-tour').offset().left + $('.bubble-guided-tour').outerWidth() > $(window).width() - 10 ) {
                    $('.bubble-guided-tour').css('left', $(window).width() - $('.bubble-guided-tour').outerWidth() - 10 );
                }

                if( $('.bubble-guided-tour').offset().left < 10 ) {
                    $('.bubble-guided-tour').css('left', 10 );
                }

            }, 1);

            $('.bubble-guided-tour .cap').css('left', element_left + (element_width / 2) - 14);
            if(bubble_at_top) {
                $('.bubble-guided-tour .cap').css('top', element_top - 20 );
            } else {
                $('.bubble-guided-tour .cap').css('top', element_top + element_heigth + 7 );
            }
        }        

        $('.guided-overflow-top').css('height', element_top);

        $('.guided-overflow-right').css('top', element_top);
        $('.guided-overflow-right').css('left', element_left + element_width);
        $('.guided-overflow-right').css('height', element_heigth);
        $('.guided-overflow-right .top').css('top', element_top);
        $('.guided-overflow-right .top').css('left', element_left + element_width - 13);
        $('.guided-overflow-right .bottom').css('top', element_top + element_heigth - 13);
        $('.guided-overflow-right .bottom').css('left', element_left + element_width - 13);

        $('.guided-overflow-left').css('top', element_top);
        $('.guided-overflow-left').css('right', $(window).width() - element_left);
        $('.guided-overflow-left').css('height', element_heigth);
        $('.guided-overflow-left .top').css('top', element_top);
        $('.guided-overflow-left .top').css('left', element_left);
        $('.guided-overflow-left .bottom').css('top', element_top + element_heigth - 13);
        $('.guided-overflow-left .bottom').css('left', element_left);

        $('.guided-overflow-bottom').css('height', $(window).height() - (element_top + element_heigth) );

        $('.guided-overflow-wrapper').show();
    }

    var guidedTour = function(data, step, step_number, tour_item, bubble_at_top) {

        $('body').css('overflow-y', 'auto');

        if(step.action == 'description') {
            $('html, body').animate({
                scrollTop: $('.profile-tabs').offset().top
            }, 0);
            $('.profile-tabs .tab[data-tab="about"]').trigger('click');

        } else if(step.action == 'photos') {
            $('.profile-tabs .tab[data-tab="about"]').trigger('click');
            $('html, body').animate({
                scrollTop: $('.gallery-slider').offset().top - 100
            }, 0);

        } else if(step.action == 'team') {
            $('.profile-tabs .tab[data-tab="about"]').trigger('click');
            $('html, body').animate({
                scrollTop: $('.team-container').offset().top - 100
            }, 0);

        } else {
            if(!$('.popup.active').length) {
                $('html, body').animate({
                    scrollTop: $('['+tour_item+'="'+step.action+'"]:visible').offset().top - 200
                }, 0);
            }
        }

        $('body').css('overflow-y', 'hidden');
        $('.bubble-guided-tour h4').html(step.title);
        $('#cur-step').html(step_number + 1);
        $('#all-steps').html(data.count_all_steps);
        $('.bubble-guided-tour p').html(step.description);
        $('.bubble-guided-tour').attr('step-number', step_number);
        
        if(step.skip) {

            if(tour_item == 'reviews-guided-action') {
                $('.bubble-guided-tour .skip-reviews-step').show();
                
            } else {

                $('.bubble-guided-tour .skip-step').html(step.skip_text);
                $('.bubble-guided-tour .skip-step').show();

                if(step.is_button) {
                    $('.bubble-guided-tour .skip-step').addClass('button');
                } else {
                    $('.bubble-guided-tour .skip-step').removeClass('button');
                }
            }
        } else {
            $('.bubble-guided-tour .skip-step').hide();
            $('.bubble-guided-tour .skip-reviews-step').hide();
        }

        resizeGuidedTourWindow($('['+tour_item+'="'+step.action+'"]:visible'), bubble_at_top);
    }

    $('.skip-step, .go-first-tour, [guided-action]').click( function() {

        if(!$(this).hasClass('dont-count')) {

            if($(this).hasClass('go-first-tour')) {
                $('body').addClass('guided-tour');

                if ('scrollRestoration' in history) {
                    history.scrollRestoration = 'manual';
                }
            }

            if($('body').hasClass('guided-tour')) {

                var that = $(this);

                $.ajax( {
                    url: window.location.origin+'/en/profile/first-guided-tour/'+ ( $(this).hasClass('go-login-tour') ? '?full=true' : ''),
                    type: 'GET',
                    dataType: 'json',
                    success: function( data ) {

                        if(that.hasClass('go-login-tour')) {
                            $('#first-guided-tour').removeClass('active');

                            var step = data.steps[0];
                            var step_number = 0;

                            gtag('event', 'Start', {
                                'event_category': 'GuidedTour',
                                'event_label': 'WelcomeStart',
                            });

                        } else if(that.hasClass('go-tour')) {
                            var step = data.steps[0];
                            var step_number = 0;

                            //if its in edit mode, to return to view profile mode
                            $('.view-profile').addClass('active');
                            $('.edit-profile').removeClass('active');
                            $('.edit-button').removeClass('active');
                            $('body').removeClass('edit-user');

                            gtag('event', 'Start', {
                                'event_category': 'GuidedTour',
                                'event_label': 'WelcomeStart',
                            });

                        } else {
                            var step = data.steps[parseInt($('.bubble-guided-tour').attr('step-number')) + 1];
                            var step_number = parseInt($('.bubble-guided-tour').attr('step-number')) + 1;
                        }

                        if(typeof step !== 'undefined') {

                            if(step.action == 'invite') {
                                $('#popup-invite').css('z-index', 10000);
                                $('#invite-sample').css('z-index', 10000);
                            }

                            if(step.action == 'team') {
                                $('#add-team-popup').css('z-index', 10000);
                            }

                            var cookie_step = null;
                            var n = null

                            //save a cookie if there is refresh on page
                            for (var i in data.steps) {
                                if (data.steps[i].action == 'save') {
                                    n = parseInt(i) + 1;
                                }
                            }

                            if(n && typeof data.steps[n] !== 'undefined') {
                                cookie_step = data.steps[n].action;
                            }

                            if(cookie_step && step.action == cookie_step && !that.hasClass('skip-step')) {
                                if(Cookies.get('functionality_cookies')) {
                                    Cookies.set('save-guided-tour', true, { expires: 1, secure: true });
                                }
                                
                            } else {
                                guidedTour(data, step, step_number, 'guided-action', false);
                            }

                        } else {
                            gtag('event', 'Finish', {
                                'event_category': 'GuidedTour',
                                'event_label': 'WelcomeComplete',
                            });

                            $.ajax( {
                                url: window.location.origin+'/en/profile/first-guided-tour-remove/',
                                type: 'GET',
                            });

                            showPopup('first-guided-tour-done');
                            $('.guided-overflow-wrapper').hide();
                        }
                    },
                    error: function(data) {
                        console.log(data);
                    }
                });
            }
        }
    });


    $('.skip-first-tour').click( function() {
        if(ajax_is_running) {
            return;
        }
        ajax_is_running = true;

        $.ajax( {
            url: window.location.origin+'/en/profile/first-guided-tour/',
            type: 'GET',
            dataType: 'json',
            success: function( data ) {
                window.location.reload();
            },
        });
        ajax_is_running = false;
    });


    $('.skip-reviews-tour').click( function() {
        if(ajax_is_running) {
            return;
        }
        ajax_is_running = true;

        $.ajax( {
            url: window.location.origin+'/en/profile/reviews-guided-tour/',
            type: 'GET',
            dataType: 'json',
            success: function( data ) {
                window.location.reload();
            }
        });
        ajax_is_running = false;
    });


    if(Cookies.get('save-guided-tour')) {

        Cookies.remove('save-guided-tour');

        $('body').addClass('guided-tour');
        
        if ('scrollRestoration' in history) {
            history.scrollRestoration = 'manual';
        }

        $.ajax( {
            url: window.location.origin+'/en/profile/first-guided-tour/',
            type: 'GET',
            dataType: 'json',
            success: function( data ) {
                for (var i in data.steps) {
                    if (data.steps[i].action == 'save') {
                        var n = parseInt(i) + 1;
                    }
                }

                var step = data.steps[n];
                var step_number = n;

                if(step.action == 'invite') {
                    $('#popup-invite').css('z-index', 10000);
                    $('#invite-sample').css('z-index', 10000);
                }
                
                guidedTour(data, step, step_number, 'guided-action', false);
            },
            error: function(data) {
                console.log(data);
            }
        });
    }


    $('.guided-description').click( function() {
        if($('body').hasClass('guided-tour')) {
            resizeGuidedTourWindow($('#edit-descr-container'), false);
        }
    });


    $('.done-tour').click( function() {
        window.location.reload();
        // closePopup();
        // $('body').removeClass('guided-tour');
        // $('body').removeClass('dark');
        // $('body').css('overflow-y', 'auto');
    });


    $('.back-widget, .copy-widget, .fb-tab-submit, .skip-reviews-step, .done-widget, .next-tour-step, .widget-layout-button, .go-reviews-tour, [reviews-guided-action]').click( function() {

        if($(this).hasClass('get-widget-code')) {
            //get widget code
            $('a.hide-fb.get-widget-code').trigger('click');
            return;
        }

        if(!$(this).hasClass('dont-count')) {
            var that = $(this);

            if(that.hasClass('go-reviews-tour')) {
                $('body').addClass('guided-tour reviews-guided-tour');
            }

            if($('body').hasClass('guided-tour')) {

                if ('scrollRestoration' in history) {
                    //to forget scroll on page
                    history.scrollRestoration = 'manual';
                }

                if(that.hasClass('with-layout')) {
                    var layout = $('[name="widget-layout"]:checked').val();
                } else {
                    var layout = null;
                }

                $.ajax( {
                    url: window.location.origin+'/en/profile/reviews-guided-tour/'+(layout ? layout : ''),
                    type: 'GET',
                    dataType: 'json',
                    success: function( data ) {

                        $('.bubble-guided-tour img').attr('src', data.image );

                        if(that.hasClass('go-reviews-tour')) {
                            $('#first-guided-tour').removeClass('active');

                            var step = data.steps[0];
                            var step_number = 0;

                            gtag('event', 'Start', {
                                'event_category': 'GuidedTour',
                                'event_label': 'WidgetsStart',
                            });
                            
                        } else if(that.hasClass('back-widget')) {
                            //back button to return to step one
                            var step = data.steps[1];
                            var step_number = 1;

                        } else {
                            var step = data.steps[parseInt($('.bubble-guided-tour').attr('step-number')) + 1];
                            var step_number = parseInt($('.bubble-guided-tour').attr('step-number')) + 1;
                        }

                        if(typeof step !== 'undefined') {

                            if( step_number) {
                                //tooltip at top of element and triangle at bottom
                                $('.bubble-guided-tour').addClass('bubble-reviews');
                                var bubble_at_top = true;
                            } else {
                                var bubble_at_top = false;
                            }

                            if(step_number == 1) {
                                //if popup was opened on second page to return to first
                                $('.widget-step').hide();
                                $('.widget-step-1').show();
                            }                            

                            if(that.hasClass('add-widget-button')) {
                                $('.popup.active').animate({
                                    scrollTop: $('.widget-step-title').offset().top - 150
                                }, 0);
                            }

                            guidedTour(data, step, step_number, 'reviews-guided-action', bubble_at_top);

                            if(step_number) {

                                $('.popup.active').on('scroll', function() {
                                    resizeGuidedTourWindow($('[reviews-guided-action="'+step.action+'"]'), bubble_at_top);
                                });

                                $('.popup.active').animate({
                                    scrollTop: $('[reviews-guided-action="'+step.action+'"]:visible').position().top - $('.bubble-guided-tour').outerHeight()
                                }, 0);

                                //ok button must get code too
                                if(step_number == 2 && (layout == 'list' || layout == 'carousel') ) {

                                    if($('.widget-last-step:visible').length) {
                                        $('.bubble-guided-tour .skip-reviews-step').removeClass('get-widget-code');
                                    } else {
                                        $('.bubble-guided-tour .skip-reviews-step').addClass('get-widget-code');
                                    }
                                }
                            }

                        } else {
                            gtag('event', 'Finish', {
                                'event_category': 'GuidedTour',
                                'event_label': 'WidgetsComplete',
                            });

                            $.ajax( {
                                url: window.location.origin+'/en/profile/reviews-guided-tour-remove/',
                                type: 'GET',
                            });

                            showPopup('first-guided-tour-done');
                            $('.guided-overflow-wrapper').hide();
                        }
                    },
                    error: function(data) {
                        console.log(data);
                    }
                });
            }
        }
    });

    
    // <------ END Guided TOUR ---------->

    
    //clinic delete its branch
    $('.delete-branch').click( function(e) {
        if(ajax_is_running) {
            return;
        }
        ajax_is_running = true;

        var that = $(this);

        $.ajax({
            type: "POST",
            url: that.attr('delete-url'),
            data: {
                branch_id: that.attr('branch-id'),
                _token: $('input[name="_token"]').val(),
            },
            success: function(ret) {
                window.location.href = ret.url;
            },
            error: function(ret) {
                console.log('error');
            }
        });

        ajax_is_running = false;
    });
});