var videoStart, videoLength;
var slider = null;
var handleReviewEvents;
var showFullReview;
var handleDCNreward;
var suggestDentist;
var suggestClinic;
var suggestedDentistClick;
var suggestClinicClick;
var editor;
var fb_page_error;
var load_maps = false;
var showPartnerWalletPopup;

$(document).ready(function() {

    if(showPartnerWalletPopup) {
        showPopup('add-wallet-address');
    }

    $('.turn-on-edit-mode').click( function() {
        $('body').toggleClass('edit-dentist-profile-mode');

        if($('body').hasClass('edit-dentist-profile-mode')) {
            $(this).find('span').html($(this).attr('to-not-edit'));
        } else {
            $(this).find('span').html($(this).attr('to-edit'));
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

        } else if($(this).closest('#team').length) {

            $(this).closest('#team').find('.team-container').toggleClass('edit-mode');

        } else if($(this).closest('.open-hours-section').length) {

            $(this).closest('.open-hours-section').toggleClass('edit-mode');

        } else if($(this).hasClass('edit-locations')) {

            $('.location-section').toggleClass('edit-mode');
            $('.gallery-flickity').flickity('resize');

            $('.map-container').hide();

            let editWrap = $('#locations').find('.edit-field');
            editWrap.find('.edited-field').hide();
            editWrap.find('.edit-field-button').hide();
            console.log(editWrap.find('.edit-wrapper'));
            editWrap.find('.edit-wrapper').show();

        } else if($(this).hasClass('edit-specializations')) {

            $('.specializations-section').toggleClass('edit-mode');

        } else if($(this).hasClass('edit-payments')) {

            $('.payments-section').toggleClass('edit-mode');

        } else if($(this).hasClass('edit-description-button')) {
            var cls = $(this).closest('.tab-inner-section').find('[role="presenter"]').attr('class');
            $('.'+cls+'[role="editor"]').show();
            $('.'+cls+'[role="presenter"]').hide();
        } else if($(this).hasClass('scroll-to')) {
            
            $('html, body').animate({
                scrollTop: $('.'+$(this).attr('scroll')).offset().top - $('header').height()
            }, 500);
            
        } else {

            let editWrap = $(this).closest('.edit-field');
            editWrap.find('.edited-field').hide();
            editWrap.find('.edit-field-button').hide();
            console.log(editWrap.find('.edit-wrapper'));
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
                    console.log('sdfdf');
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
                                    <img src="https://urgent.reviews.dentacoin.com/img-trp/social-network/'+i+'.svg" height="26">\
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
    //end specializations & payment methods


    //working hours
    $('.work-hour-cb').change( function() {
        var closed = $(this).is(':checked');
        var texts = $(this).closest('.col').find('select');

        if(closed) {
            texts.addClass('grayed');
            // texts.attr('disabled', 'disabled');
        } else {
            texts.removeClass('grayed');
            // texts.prop("disabled", false);
        }
    });

    $('.edit-working-hours-wrap select').on('change click',  function() {
        $(this).closest('.edit-working-hours-wrap').find('input').prop('checked', true);
        $(this).closest('.edit-working-hours-wrap').find('input').closest('label').addClass('active');
        $(this).closest('.edit-working-hours-wrap').find('select').removeClass('grayed');
        $(this).closest('.edit-working-hours-wrapper').find('.work-hour-cb').prop('checked', false);
        $(this).closest('.edit-working-hours-wrapper').find('.work-hour-cb').closest('label').removeClass('active');
    });

    $('.all-days-equal').click( function() {
        for (var i = 2; i<6; i++) {
            $('#day-'+i).click();
                
            $('[name="work_hours['+i+'][0][0]"]').val($('[name="work_hours[1][0][0]"]').val());
            $('[name="work_hours['+i+'][0][1]"]').val($('[name="work_hours[1][0][1]"]').val());
            $('[name="work_hours['+i+'][1][0]"]').val($('[name="work_hours[1][1][0]"]').val());
            $('[name="work_hours['+i+'][1][1]"]').val($('[name="work_hours[1][1][1]"]').val());
        }
    });

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
                console.log(data);
                if(data.success) {
                    that.closest('.open-hours-section').removeClass('edit-mode');

                    var wh = data.inputs.work_hours;
                    for(var i in wh) {
                        console.log(data.inputs['day_'+i]);
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

    $('.alert-edit').click( function() {
        $('html, body').animate({
            scrollTop: $('.edit-profile').offset().top - $('header').height()
        }, 500);
    });

    $('.open-edit').click( function() {
        $('.view-profile').toggle();
        $('.edit-profile').toggle();
        $('.edit-button').toggle();
        $('body').addClass('edit-user');
    } );

    $('.cancel-edit').click( function() {

        $('body').removeClass('edit-user');
    });

    if(getUrlParameter('open-edit')) {
        $('.open-edit').first().trigger('click');
    }
    
    //
    //Popups
    //

    //Widget

    if( $('#widget-carousel').length ) {
        $(".select-me").on("click focus", function () {
            $(this).select();
        });

        $(".copy-widget").click(function(){
            $(this).closest('.widget-code-wrap').find('textarea').select();
            document.execCommand('copy');
        });

        $('.widget-tabs .col, #select-reviews-popup input, #popup-widget input, #popup-widget select:not(#dentist-page)').on('click change keyup keypress', function(e) {
            if(typeof widet_url=='undefined') {
                return;
            }
            var layout = $('[name="widget-layout"]:checked').val();
            var image_url = $('[name="widget-layout"]:checked').closest('.radio-label').find('img').attr('src');
            if ($('[name="widget-layout"]:checked').val() == 'carousel' && parseInt($('[name="slide-results"]').val()) == 3) {
                $('#selected-image-layout').attr('src', 'https://reviews.dentacoin.com/img-trp/widget-carousel-3.png');
            } else if($('[name="widget-layout"]:checked').val() == 'badge' && $('[name="badge"]').val() == 'mini') {
                $('#selected-image-layout').attr('src', 'https://reviews.dentacoin.com/img-trp/widget-badge-min.png');
            } else {
                $('#selected-image-layout').attr('src', image_url);
            }

            if (parseInt($('[name="slide-results"]').val()) == 3) {
                $('#widget-carousel').closest('label').find('img').attr('src', 'https://reviews.dentacoin.com/img-trp/widget-carousel-3.png');
            } else {
                $('#widget-carousel').closest('label').find('img').attr('src', 'https://reviews.dentacoin.com/img-trp/widget-carousel.png');
            }

            if ($('[name="badge"]').val() == 'mini') {
                $('#widget-badge').closest('label').find('img').attr('src', 'https://reviews.dentacoin.com/img-trp/widget-badge-min.png');
            } else {
                $('#widget-badge').closest('label').find('img').attr('src', 'https://reviews.dentacoin.com/img-trp/widget-badge.png');
            }
            
            
            var getParams = '?layout='+$('[name="widget-layout"]:checked').val();
            $('#selected-layout').html($('[name="widget-layout"]:checked').closest('label').find('p').attr('layout-text'));
            var custom_width = false;
            var custom_heigth = false;

            $('.select-reviews').show();
            
            if (layout == 'carousel') {
                if ($('[name="slide-results"][cant-select]').length && parseInt($('[name="slide-results"]').val()) == 3) {
                    getParams += '&slide=1';
                    $('[name="slide-results"]').val('1');
                    $('.slider-alert').show();
                } else {
                    getParams += '&slide='+$('[name="slide-results"]').val();
                }
            } else if(layout == 'list') {
                getParams += '&width='+$('[name="list-width"]').val()+'&height='+$('[name="list-height"]').val();
                custom_heigth = true;

                if (parseInt($('[name="list-width"]').val()) != 100) {
                    custom_width = true;
                }
                $('.slider-alert').hide();
            } else if(layout == 'badge') {
                getParams += '&badge='+$('[name="badge"]').val();
                $('.select-reviews').hide();
                $('.slider-alert').hide();
            }

            if ($('[name="widget-layout"]:checked').val() == 'carousel' && parseInt($('[name="slide-results"]').val()) == 3 && $('#trusted-chosen').attr('trusted-reviews-count') < 4) {
                $('#trusted-chosen').hide();
            } else if($('#trusted-chosen').attr('trusted-reviews-count') < 1) {
                $('#trusted-chosen').hide();
            } else {
                $('#trusted-chosen').show();
            }

            if (layout != 'badge') {
                
                getParams += '&review-type='+$('[name="review-type"]:checked').val();

                if ($('[name="review-type"]:checked').val() == 'all') {
                    getParams += '&review-all-count='+$('[name="all-reviews-option"]:checked').val();
                } else if($('[name="review-type"]:checked').val() == 'trusted') {
                    getParams += '&review-trusted-count='+$('[name="trusted-reviews-option"]:checked').val();
                } else if($('[name="review-type"]:checked').val() == 'custom') {
                    if ($('[name="widget-custom-review"]:checked').length) {
                        $('[name="widget-custom-review"]:checked').each( function() {
                            getParams += '&review-custom[]='+$(this).val();
                        });
                    }
                }

            }

            if (!$('#widget-option-flexible:visible').length && custom_width) {
                $('.widget-tab-alert').show();
            } else {
                $('.widget-tab-alert').hide();
            }

            $('#custom-reviews-length').html($('[name="widget-custom-review"]:checked').length)
            
            var parsedUrl = widet_url+getParams;

            if (!$(e.target).closest('.popup-tabs').length) {
                $('.get-widget-code-wrap').show();
                $('.widget-last-step').hide();
            }

            if(layout == 'badge') {
                $('.get-widget-code-wrap').hide();
                $('.widget-last-step').show();
            }

            if(layout == 'fb') {
                $('.widget-last-step').hide();
            }

            $('.widget-custom-reviews-alert').hide();

            var iframe_url = parsedUrl.replace('&width=','&customwidth=').replace('&height=','&customheight=');
            $('#option-iframe textarea').val('<!--Trusted Reviews Widget-->\n\r<iframe style="width: 100%; height: '+(custom_heigth ? $('[name="list-height"]').val()+'px' : (layout == 'carousel' ? '750px' : '50vh'))+'; border: none; outline: none;" src="'+iframe_url+'"></iframe>\n\r<!--End Trusted Reviews Widget-->');
            $('#option-js textarea').val('<!--Trusted Reviews Widget-->\n\r<div id="trp-widget"></div><script type="text/javascript" src="https://reviews.dentacoin.com/js-trp/widget.js"></script> <script type="text/javascript"> TRPWidget.init("'+parsedUrl+'"); </script>\n\r<!--End Trusted Reviews Widget-->');
        });
        $('#widget-carousel').trigger('change');
    }
    

    $('.get-widget-code').click( function() {
        if($('[name="review-type"]:checked').val() == 'custom' && !$('[name="widget-custom-review"]:checked').length) {
            $('.widget-custom-reviews-alert').show();
        } else if ($('[name="widget-layout"]:checked').val() == 'carousel' && parseInt($('[name="slide-results"]').val()) == 3 && $('[name="review-type"]:checked').val() == 'custom' && $('[name="widget-custom-review"]:checked').length < 4) {
            $('.widget-custom-reviews-alert').show();
        } else {

            $(this).closest('.get-widget-code-wrap').hide();
            $('.widget-last-step').show();

            var selected_layout = $('[name="widget-layout"]:checked').val();
            gtag('event', 'Code', {
                'event_category': 'Widgets',
                'event_label': selected_layout,
            });

            if($('body').hasClass('guided-tour')) {
                $('.next-tour-step').trigger('click');
            }
        }
    });
    

    $('.widget-button').click( function() {
        $(this).closest('.widget-step').hide();
        $('.widget-step-'+$(this).attr('to-step')).show();
        if(!$('body').hasClass('reviews-guided-tour')) {

            $('.popup.active').animate({
                scrollTop: $('.popup.active').offset().top
            }, 200);
        }

        if( $(this).hasClass('widget-layout-button')) {
            var selected_layout = $('[name="widget-layout"]:checked').val();

            gtag('event', 'Layout', {
                'event_category': 'Widgets',
                'event_label': selected_layout,
            });

            if(selected_layout == 'badge') {

                gtag('event', 'Code', {
                    'event_category': 'Widgets',
                    'event_label': selected_layout,
                });
            }

            if (selected_layout == 'fb') {
                $('.show-fb').show();
                $('.hide-fb').hide();
            } else {
                $('.show-fb').hide();
                $('.hide-fb').show();
            }
        }
    });

    $('.open-hidden-option').click( function() {
        $(this).closest('label').find('.hidden-option').toggleClass('active');
        $(this).toggleClass('active');
    });

    $('.type-radio-widget').change( function(e) {
        $(this).closest('.option-checkboxes').find('label').removeClass('active');
        $(this).closest('label').addClass('active');
    });

    $('.type-radio-widget-first').change( function(e) {
        $(this).closest('.select-reviews').find('.hidden-option').removeClass('active');
        $(this).closest('.radio-label').find('.hidden-option').addClass('active');
        $(this).closest('.modern-radios').find('.first-label').removeClass('active');
        $(this).closest('.first-label').addClass('active');
        $(this).closest('.first-label').toggleClass('open');
    });


    //Profile edit
    $('.edit-profile').submit( function(e) {
        e.preventDefault();

        if(ajax_is_running) {
            return;
        }
        ajax_is_running = true;
        $('.edit-error').hide();
        $('.short-descr-error').hide();        

        $.post( 
            $(this).attr('action'), 
            $(this).serialize(), 
            function( data ) {
                if(data.success) {
                    window.location.reload();
                } else {
                    $('.edit-error').show().html('');
                    for(var i in data.messages) {
                        $('.edit-error').append(data.messages[i] + '<br/>');
                        $('input[name="'+i+'"]').addClass('has-error');
                        $('textarea[name="'+i+'"]').addClass('has-error');
                    }
                }
                ajax_is_running = false;
            }, 
            "json"
        );

    } );

    //Gallery upload
    $('#add-gallery-photo').change( function() {
        if(ajax_is_running) {
            return;
        }
        ajax_is_running = true;

        $('.add-gallery-image .image-label').addClass('loading');

        var sure_text = $('#add-gallery-photo').attr('sure-trans');
        var file = $(this)[0].files[0];
        var that = $(this);

        var fileExtension = ['jpeg', 'jpg', 'png'];
        if ($.inArray(that.val().split('.').pop().toLowerCase(), fileExtension) == -1) {
            console.log("Only formats are allowed : "+fileExtension.join(', '));
        } else {
            var upload = new Upload(file, $(this).attr('upload-url'), function(data) {
                $('.add-gallery-image .image-label').removeClass('loading');

                var html = '<a href="'+data.original+'" data-lightbox="user-gallery" class="slider-wrapper">\
                    <div class="slider-image cover" style="background-image: url(\''+data.url+'\')">\
                        <div class="delete-gallery delete-button" sure="'+sure_text+'">\
                            <img class="close-icon" src="'+all_images_path+'/close-icon-white.png"/>\
                        </div>\
                    </div>\
                </a>\
                ';

                $('.gallery-flickity').flickity( 'insert', $(html), 1 );
                handleGalleryRemoved();

                if($('body').hasClass('guided-tour')) {
                    $('.bubble-guided-tour .skip-step').trigger('click');
                }

                ajax_is_running = false;
            });

            upload.doUpload();
        }
    });

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

    } );
    //
    //Add dentist to clinic
    //

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

    } );

    suggestedDentistClick = function(elm) {
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
                refreshOnClosePopup = true;

                ajax_is_running = false;

            }).bind(this)
        });
    }

    suggestDentist = function() {

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
                        container.append('<a href="javascript:;" data-id="'+data[i].id+'">'+data[i].name+'</a>');
                    }

                    container.find('a').click( function() {
                        suggestedDentistClick(this);
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

    //
    //Ask clinic to join
    //

    suggestClinicClick = function(elm) {
        var id = $(elm).attr('data-id');

        $(elm).closest('.suggest-results').hide();
        $(elm).closest('.suggester-wrapper').find('.suggester-input').val('');

        if(ajax_is_running) {
            return;
        }
        ajax_is_running = true;

        $.ajax( {
            url: lang + '/profile/clinics/invite',
            type: 'POST',
            dataType: 'json',
            data: {
                joinclinicid: $(elm).attr('data-id')
            },
            success: (function( data ) {
                $('#clinic-add-result').html(data.message).attr('class', 'alert '+(data.success ? 'alert-success' : 'alert-warning')).show();
                refreshOnClosePopup = true;

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
                        container.append('<a href="javascript:;" data-id="'+data[i].id+'">'+data[i].name+'</a>');
                    }

                    container.find('a').click( function() {
                        suggestClinicClick(this);
                    } );
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

    $('#workplaces-list .remove-dentist').click( function(e) {
        e.preventDefault();

        if(ajax_is_running) {
            return;
        }
        ajax_is_running = true;

        $.get( 
            $(this).attr('href'),
            (function( data ) {
                $(this).closest('.flex').remove();
                refreshOnClosePopup = true;
                ajax_is_running = false;
            }).bind(this), "json"
        );          

    } );

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

        var social_wrapper = $(this).closest('.socials-wrapper').find('.social-wrap');
        var cloned = social_wrapper.first().clone(true).insertAfter( $(this).closest('.socials-wrapper').find('.social-wrap').last() );

        cloned.addClass('new');
        cloned.find('.social-link-input').val('');
        cloned.find('.social-dropdown .social-link:not(.inactive)').first().trigger('click');

        if((social_wrapper.length +1) == social_wrapper.first().find('.social-dropdown a').length ) {
            $(this).hide();
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
                    if (that.parent().hasClass('ask-dentist-alert')) {
                        $('.ask-dentist-alert').find('.ask-dentist').remove();
                        $('.ask-dentist-alert').find('br').remove();
                        that.closest('.popup').removeClass('active');
                    } else {
                        $('.ask-dentist').closest('.alert').hide();
                        $('.ask-success').show();
                        $('.button-ask').remove();
                    }

                    gtag('event', 'Request', {
                        'event_category': 'Reviews',
                        'event_label': 'InvitesAsk',
                    });
                } else {

                }
            }
        );
    } );

    $('.ask-review-button').click( function(e) {
        $('#popup-ask-dentist').find('.ask-dentist').attr('href', $('#popup-ask-dentist').find('.ask-dentist').attr('original-href')+'/1' );

        gtag('event', 'Request', {
            'event_category': 'Reviews',
            'event_label': 'InvitesAskUnver',
        });
    });

    $('.ask-review').click( function() {
        var id = $(this).attr('review-id');
        showFullReview(id, $('#cur_dent_id').val());
    } );

    $('.invite-again').click( function(e) {
        e.preventDefault();

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
                    window.location.href = ret.url;
                } else {
                    console.log('error');
                }
            },
            error: function(ret) {
                console.log('error');
            }
        });
    });


    if($('#symbols-count-short').length) {
        $('#symbols-count-short').html($('#dentist-short-description').val().length);
    }

    $('#dentist-short-description').keyup(function() {
        var length = $(this).val().length;

        if (length > 150) {
            $('#symbols-count-short').addClass('red');
        } else {
            $('#symbols-count-short').removeClass('red');
        }
        $('#symbols-count-short').html(length);
    });

    $('.add-widget-button').click( function() {
        gtag('event', 'Open', {
            'event_category': 'Widgets',
            'event_label': 'Popup',
        });
    });

    // $('.branch-tabs a').click( function() {
    //     $('.branch-tabs a').removeClass('active');
    //     $(this).addClass('active');
    //     $('.branch-content').hide();
    //     console.log($(this).attr('data-branch'));
    //     $('#branch-option-'+$(this).attr('data-branch')).show();
    // });

    $('.widget-tabs a').click( function() {
        $('.widget-tabs a').removeClass('active');
        $(this).addClass('active');
        $('.widget-content').hide();
        $('#widget-option-'+$(this).attr('data-widget')).show();
    });

    if( $('.recommend-dentist-form').length ) {

        $('.recommend-dentist-form').submit( function(e) {
            e.preventDefault();

            if(ajax_is_running) {
                return;
            }

            ajax_is_running = true;

            $(this).find('.recommend-alert').hide().removeClass('alert-warning').removeClass('alert-success');


            var formData = new FormData();

            // add assoc key values, this will be posts values
            formData.append("_token", $(this).find('input[name="_token"]').val());            
            formData.append("name", $(this).find('.recommend-name').val());
            formData.append("email", $(this).find('.recommend-email').val());
            formData.append("dentist-id", $(this).find('.recommend-dentist-id').val());


            $.ajax({
                type: "POST",
                url: $(this).attr('action'),
                success: function (data) {
                    if(data.success) {
                        $('.recommend-dentist-form').find('.recommend-email').val('');
                        $('.recommend-dentist-form').find('.recommend-name').val('').focus();
                        $('.recommend-dentist-form').find('.recommend-alert').show().addClass('alert-success').html(data.message);

                        gtag('event', 'Submit', {
                            'event_category': 'Recommend',
                            'event_label': 'RecommendSent',
                        });
                    } else {
                        $('.recommend-dentist-form').find('.recommend-alert').show().addClass('alert-warning').html(data.message);                    
                    }
                    ajax_is_running = false;
                },
                error: function (error) {
                    console.log('error');
                },
                async: true,
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                timeout: 60000
            });
        } );
    }

    $('.form-fb-tab').submit( function(e) {
        e.preventDefault();

        if($('[name="review-type"]:checked').val() == 'custom' && !$('[name="widget-custom-review"]:checked').length) {
            $('.widget-custom-reviews-fb-alert').show();
            return;
        }

        if(ajax_is_running) {
            return;
        }

        ajax_is_running = true;

        $(this).find('.fbtab-alert').hide().removeClass('alert-warning').removeClass('alert-success');

        var that = $(this);

        var reviews_cust = [];
        if($('[name="review-type"]:checked').val() == 'custom') {
            $('[name="widget-custom-review"]:checked').each( function() {
                reviews_cust.push($(this).val());
            });
        }

        $.ajax({
            type: "POST",
            url: that.attr('action'),
            data: {
                page: $('#dentist-page').val(),
                reviews_type: $('[name="review-type"]:checked').val(),
                all_reviews: $('[name="all-reviews-option"]:checked').val(),
                trusted_reviews: $('[name="trusted-reviews-option"]:checked').val(),
                custom_reviews: reviews_cust,
                _token: $('input[name="_token"]').val(),
            },
            dataType: 'json',
            success: function(data) {
                if(data.success) {
                    $('.widget-step-1').show();
                    $('.widget-step-2').hide();
                    $('#popup-widget').removeClass('active');
                    $('#popup-widget').removeClass('active');
                    $('#facebook-tab-success').addClass('active');

                    // $('.form-fb-tab').find('.fbtab-alert').show().addClass('alert-success').html(data.message);

                    gtag('event', 'Done', {
                        'event_category': 'Widgets',
                        'event_label': 'FB Complete',
                    });
                } else {
                    $('.form-fb-tab').find('.fbtab-alert').show().addClass('alert-warning').html(data.message);                    
                }
                ajax_is_running = false;
            },
            error: function(data) {
                console.log('error');
            }
        });
    });

    $('.fb-tab-submit').click( function(e) {
        e.preventDefault();

        $('.ajax-alert').remove();
        $('.has-error').removeClass('has-error');

        if($('[name="review-type"]:checked').val() == 'custom' && !$('[name="widget-custom-review"]:checked').length) {
            $('.widget-custom-reviews-fb-alert').show();
            return;
        }

        if(!$('#fb-page-id').val()) {
            $('#fb-page-id').closest('.alert-after').after('<div class="alert alert-warning ajax-alert" error="page">'+$('.facebook-tab').attr('error-missing')+'</div>');
            $('#fb-page-id').addClass('has-error');
            return;
        }

        if(!$.isNumeric($('#fb-page-id').val())) {
            $('#fb-page-id').closest('.alert-after').after('<div class="alert alert-warning ajax-alert" error="page">'+$('.facebook-tab').attr('error-not-numeric')+'</div>');
            $('#fb-page-id').addClass('has-error');
            return;
        }

        if(ajax_is_running) {
            return;
        }

        ajax_is_running = true;

        var that = $(this);

        var reviews_cust = [];
        if($('[name="review-type"]:checked').val() == 'custom') {
            $('[name="widget-custom-review"]:checked').each( function() {
                reviews_cust.push($(this).val());
            });
        }

        $.ajax({
            type: "POST",
            url: that.attr('fb-url'),
            data: {
                page: $('#fb-page-id').val(),
                reviews_type: $('[name="review-type"]:checked').val(),
                all_reviews: $('[name="all-reviews-option"]:checked').val(),
                trusted_reviews: $('[name="trusted-reviews-option"]:checked').val(),
                custom_reviews: reviews_cust,
                _token: $('input[name="_token"]').val(),
            },
            dataType: 'json',
            success: function(data) {
                if(data.success) {
                    window.location.href = data.link;
                    // $('.widget-step-1').show();
                    // $('.widget-step-2').hide();
                    // $('#popup-widget').removeClass('active');
                    // $('#popup-widget').removeClass('active');
                    // $('#facebook-tab-success').addClass('active');

                    // $('.form-fb-tab').find('.fbtab-alert').show().addClass('alert-success').html(data.message);

                    gtag('event', 'Done', {
                        'event_category': 'Widgets',
                        'event_label': 'FB Complete',
                    });
                } else {
                    $('.form-fb-tab').find('.fbtab-alert').show().addClass('alert-warning').html(data.message);                    
                }
                ajax_is_running = false;
            },
            error: function(data) {
                console.log('error');
            }
        });
    });


    $('.recommend-button').click( function() {
        gtag('event', 'Open', {
            'event_category': 'Recommend',
            'event_label': 'RecommendPopup',
        });
    });
    
    $('.user-country').click( function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).next().show();
    });

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

    $('.verify-review').click( function() {
        if(ajax_is_running) {
            return;
        }
        ajax_is_running = true;

        var that = $(this);
        var review_id_param = $(this).closest('.written-review').length ? $(this).closest('.written-review').attr('review-id') : $('.details-wrapper').attr('review-id');

        $.ajax({
            type: "POST",
            url: window.location.origin+'/en/verify-review/',
            data: {
                review_id: review_id_param,
                _token: $('input[name="_token"]').val(),
            },
            dataType: 'json',
            success: function(ret) {
                console.log(ret);
                if(ret.success) {

                    if(that.closest('.written-review').length) {
                        that.closest('.written-review').find('.trusted-sticker').show();
                    } else {
                        that.closest('.details-wrapper').find('.review-rating-new').addClass('verified-review');
                        that.closest('.details-wrapper').find('.review-rating-new .trusted').show();
                    }

                    that.hide();

                } else {
                    console.log('error');
                }
                ajax_is_running = false;
            },
            error: function(ret) {
                console.log('error');
                ajax_is_running = false;
            }
        });
    });

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