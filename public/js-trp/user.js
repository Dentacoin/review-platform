var videoStart, videoLength;
var slider = null;
var sliderTO = null;
var handleReviewEvents;
var showFullReview;
var handleDCNreward;
var galleryFlickty;
var teamFlickity;
var suggestDentist;
var suggestClinic;
var suggestedDentistClick;
var suggestClinicClick;
var aggregated_reviews;
var editor;
var fb_page_error;

$(document).ready(function(){

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
                    galleryFlickty.flickity( 'remove', $(this).closest('.slider-wrapper') );
                }).bind(this)
            });

        } );
   }
   handleGalleryRemoved();

    showFullReview = function(id, d_id) {
        showPopup('view-review-popup');
        $.ajax( {
            url: '/'+lang+'/review/' + id,
            type: 'GET',
            data: {
                d_id: d_id,
            },
            success: (function( data ) {
                $('#the-detailed-review').html(data);
                showPopup('view-review-popup');
                handleReviewEvents();
                attachTooltips();
                $('#view-review-popup .share-popup').attr('share-href', window.location.href.split('?')[0] + '?review_id=' + this);
            }).bind(id)
        });
    }

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

    $('.header-info i').click( function() {
        $('.home-search-form').show();
        $(this).hide();
        $('#search-input').focus();
        $('.blue-background').addClass('extended');
    } );

    $('[role="presenter"] a').click( function() {
        var cls = $(this).closest('[role="presenter"]').attr('class');
        $('.'+cls+'[role="editor"]').show();
        $('.'+cls+'[role="presenter"]').hide();
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

        return false;
    });


    $('.tab').click( function() {
        if( $(this).attr('data-tab')=='about' ) {

            if( $('#profile-map').length && !$('#profile-map').attr('inited') ) {
                $('#profile-map').attr('inited', 'done');

                prepareMapFucntion( function() {
                        
                    var profile_map = new google.maps.Map(document.getElementById('profile-map'), {
                        center: {
                            lat: parseFloat($('#profile-map').attr('lat')), 
                            lng: parseFloat($('#profile-map').attr('lon'))
                        },
                        zoom: 15,
                        backgroundColor: 'none'
                    });

                    var mapMarker = new google.maps.Marker({
                        position: {
                            lat: parseFloat($('#profile-map').attr('lat')), 
                            lng: parseFloat($('#profile-map').attr('lon'))
                        },
                        map: profile_map,
                        icon: images_path+'/map-pin-active.png',
                    });

                    var data = $('.scroll-to-map:visible').attr('map-tooltip');
                    var infowindow = new google.maps.InfoWindow({
                      content: data
                    });
                    infowindow.open(profile_map,mapMarker);

                } );
            }

            if (window.innerWidth > 768) {
                var draggable_gallery = false;
            } else {
                var draggable_gallery = true;
            }

            if (window.innerWidth > 992) {
                var draggable_team = false;
            } else {
                var draggable_team = true;
            }

            galleryFlickty = $('.gallery-flickity').flickity({
                autoPlay: false,
                wrapAround: true,
                cellAlign: 'left',
                pageDots: true,
                freeScroll: true,
                groupCells: 1,
                draggable: draggable_gallery,
            });

            galleryFlickty.resize();

            teamFlickity = $('.flickity').flickity({
                autoPlay: false,
                wrapAround: true,
                cellAlign: 'left',
                pageDots: false,
                freeScroll: true,
                groupCells: 1,
                draggable: draggable_team,
            });

            teamFlickity.resize();
        }
    });


    if( getUrlParameter('tab') ) {
        $('.profile-tabs a[data-tab="'+getUrlParameter('tab')+'"]').trigger('click');
    } else if($('.profile-tabs .force-active').length) {
        $('.profile-tabs .force-active').click();
    } else {
        $('.profile-tabs a').first().click();
    }

    if(getUrlParameter('review_id')) {
        showFullReview( getUrlParameter('review_id'), $('#cur_dent_id').val() );
    }

    $('#clinic_dentists').change( function() {
        $('.hidden-review-question').show();
    });

    $('#dentist_clinics').change( function() {
        $(this).closest('.questions-wrapper').find('input[type="hidden"]').val('');
        $(this).closest('.questions-wrapper').find('.bar').css('width', '0px');

        if ($(this).val() == 'own') {
            $('.questions-wrapper .question:not(.skippable):not(.question-treatments):not(.review-desc)').addClass('do-not-show');
            $('.questions-wrapper .question.skippable').next().hide();
            $('.questions-wrapper .question[q-id="4"]').removeClass('do-not-show');
            $('.questions-wrapper .question[q-id="6"]').removeClass('do-not-show');
            $('.questions-wrapper .question[q-id="7"]').removeClass('do-not-show');
            $('.questions-wrapper .question:not(.skippable):not(.question-treatments):not(.review-desc)').addClass('hidden');
            $('.questions-wrapper .question[q-id="4"]').removeClass('hidden');
            $(this).closest('.questions-wrapper').addClass('team-dentist');
        } else {
            $('.questions-wrapper .question').removeClass('do-not-show');
            $('.questions-wrapper .question:not(.skippable):not(.question-treatments):not(.review-desc)').addClass('hidden');
            $(this).closest('.question').next().show();
            $(this).closest('.questions-wrapper').removeClass('team-dentist');
        }
    });

    handleDCNreward = function() {
        var total = 0;
        var filled = 0;
        
        $('#submit-review-popup .stars input[type="hidden"]').each( function() {
            total++;
            if( parseInt( $(this).val() ) ) {
                filled++;
            }
        } );

        var reward = Math.ceil( filled/total*parseInt($('#review-reward-total').text()) );
        $('#review-reward-so-far').html(reward);
    }

    handleReviewEvents = function() {

        $('.thumbs-up, .thumbs-down').off('click').click( function() {
            
            var type = $(this).hasClass('thumbs-up') ? 'useful' : 'unuseful';
            var that = this;
            $.ajax( {
                url: '/'+lang+'/'+type+'/' + $(this).closest('.review-wrapper').attr('review-id'),
                type: 'GET',
                dataType: 'json',
                success: (function( data ) {
                    if(data.success) {

                        $(that).closest('.review-footer').find('.thumbs-up span').html( data.upvotes );
                        $(that).closest('.review-footer').find('.thumbs-down span').html( data.downvotes );

                        var icon_up = $(that).closest('.review-footer').find('.thumbs-up');
                        var icon_down = $(that).closest('.review-footer').find('.thumbs-down');

                        if (data.upvote_status) {
                            icon_up.addClass('voted');
                        } else {
                            icon_up.removeClass('voted');
                        }

                        if (data.downvote_status) {
                            icon_down.addClass('voted');
                        } else {
                            icon_down.removeClass('voted');
                        }

                        icon_up.find('img').attr('src', data.upvote_image);
                        icon_down.find('img').attr('src', data.downvote_image);
                    }
                }).bind(that)
            }); 
        } );

        $('.reply-button.show-hide').off('click').click( function() {
            var tmp = $(this).attr('alternative');
            $('.reply-button.show-hide').attr('alternative', $(this).text());
            $('.reply-button.show-hide').html(tmp);
            $('.review-replied-wrapper:not(.reply-form)').toggle();
        });

        $('.reply-review').off('click').click( function() {
            $(this).closest('.review-wrapper').find('.reply-form').toggle();
        });

        $('.reply-form-element').off('submit').submit( function(e) {
            e.preventDefault();

            $(this).find('.alert').hide();

            var input = $(this).find('textarea').first();

            if( !input.val().trim().length ) {
                $(this).find('.alert').show();
                $('html, body').animate({
                    scrollTop: $(this).offset().top - 60
                }, 500);
                return;
            }

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
                        that.closest('.review-content').html(data.reply);
                    } else {
                        that.find('.alert').show();

                        $('html, body').animate({
                            scrollTop: that.offset().top - 60
                        }, 500);
                    }
                    ajax_is_running = false;
                }).bind(that), "json"
            );          

            return false;

        } );
        
    }
    handleReviewEvents();

    $('.more').click( function() {
        var id = $(this).closest('.review-wrapper').attr('review-id');
        showFullReview(id, $('#cur_dent_id').val());
    } );

    $('#write-review-form .stars').mousemove( function(e) {
        var rate = e.offsetX;
        rate = Math.ceil( rate*5 / $(this).width() );

        $(this).find('.bar').css('width', (rate*20)+'%' );
    } ).click( function(e) {
        var rate = e.offsetX;
        rate = Math.ceil( rate*5 / $(this).width() );

        $(this).find('input').val(rate).trigger('change');
        $(this).closest('.review-answers').find('.rating-error').hide();
        $(this).find('.bar').css('width', (rate*20)+'%' );
        handleDCNreward();

    } ).mouseout( function(e) {
        var rate = parseInt($(this).find('input').val());
        if(rate) {
            $(this).find('.bar').css('width', (rate*20)+'%' );
        } else {
            $(this).find('.bar').css('width', 0 );
        }
    } );





    $('.review-tabs a').click( function() {
        $('.review-tabs a').removeClass('active');
        $(this).addClass('active');
        $('.review-type-content').hide();
        $('#review-option-'+$(this).attr('data-type')).show();

        if( $(this).attr('data-type')=='video' ) {
            $('#review-reward-total').html( $('#review-reward-total').attr('video') );
        } else {
            $('#review-reward-total').html( $('#review-reward-total').attr('standard') );
        }
        handleDCNreward();
    });
    
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

    //Invites


    $('.invite-patient-form').submit( function(e) {
        e.preventDefault();

        if(ajax_is_running) {
            return;
        }

        ajax_is_running = true;

        $(this).find('.invite-alert').hide().removeClass('alert-warning').removeClass('alert-success');

        $.ajax({
            type: "POST",
            url: $(this).attr('action'),
            data: {
                token: $(this).find('input[name="_token"]').val(),
                name: $(this).find('.invite-name').val(),
                email: $(this).find('.invite-email').val(),
            },
            dataType: 'json',
            success: function(data) {
                if(data.success) {

                    $('.invite-patient-form').find('.invite-email').val('');
                    $('.invite-patient-form').find('.invite-name').val('').focus();
                    $('.invite-patient-form').find('.invite-alert').show().addClass('alert-success').html(data.message);

                    gtag('event', 'AddManually', {
                        'event_category': 'ReviewInvites',
                        'event_label': 'InvitesSent',
                    });
                } else {
                    $('.invite-patient-form').find('.invite-alert').show().addClass('alert-warning').html(data.message);                    
                }
            },
            error: function(ret) {
                console.log(ret);
                console.log('error');
            }
        });
        ajax_is_running = false;
    } );


    if( $('#invite-no-address').length ) {

        $('#invite-no-address').submit( function(e) {
            e.preventDefault();

            if(ajax_is_running) {
                return;
            }

            ajax_is_running = true;

            $('#invite-alert').hide();

            $.post( 
                $(this).attr('action'), 
                $(this).serialize() , 
                function( data ) {
                    if(data.success) {
                        window.location.href = window.location.href.split('?')[0] + '?popup-loged=popup-invite';
                    } else {
                        $('#invite-alert').show().addClass('alert-warning').html(data.message);                    
                    }
                    ajax_is_running = false;
                }, "json"
            );

            return false;
        } );

    }


    $('.whatsapp-button').click( function(e) {
        e.preventDefault();

        if(ajax_is_running) {
            return;
        }

        ajax_is_running = true;

        that = $(this);
        that.closest('.invite-content').find('.invite-alert').hide().removeClass('alert-warning').removeClass('alert-success');

        $.ajax({
            type: "POST",
            url: that.attr('data-url'),
            data: {
                _token: $('input[name="_token"]').val(),
            },
            dataType: 'json',
            success: function(data) {
                if(data.success) {

                    // if ($(window).innerWidth() <= 992) {
                    //     window.open('whatsapp://send?text='+ data.text +'&href='+ data.text +'', '_blank');
                    // } else {
                        window.open('https://api.whatsapp.com/send?text=' + data.text, '_blank');
                    // }

                    that.closest('.invite-content').find('.invite-alert').show().addClass('alert-success').html(data.message);

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


    if( $('.invite-patient-whatsapp-form').length ) {
        $('.invite-patient-whatsapp-form').submit( function(e) {
            e.preventDefault();

            if(ajax_is_running) {
                return;
            }

            ajax_is_running = true;

            $(this).find('.invite-alert').hide().removeClass('alert-warning').removeClass('alert-success');

            var that = $(this);

            $.post( 
                $(this).attr('action'), 
                $(this).serialize() , 
                function( data ) {
                    if(data.success) {

                        if ($(window).innerWidth() <= 992) {
                            window.open('whatsapp://send?text='+ data.text, '_blank');
                        } else {
                            window.open('https://api.whatsapp.com/send?text=' + data.text, '_blank');
                        }                   

                        that.find('.invite-phone').val('');
                        that.find('.invite-alert').show().addClass('alert-success').html(data.message);

                        // gtag('event', 'Send', {
                        //     'event_category': 'Reviews',
                        //     'event_label': 'InvitesSent',
                        // });
                    } else {
                        console.log(data.message);
                        that.find('.invite-alert').show().addClass('alert-warning').html(data.message); 
                                        
                    }
                    ajax_is_running = false;

                }, "json"
            );
        } );
    }

    if ($('#copypaste').length) {

        editor = CodeMirror.fromTextArea(document.getElementById("copypaste"), {
            lineNumbers: true,
            matchBrackets: true,
            mode: "text/x-csharp"
        });
    }

    var inviteRadio = function() {
        $('.invite-input-radio').change( function() {
            $(this).closest('.copypaste-wrapper').find('.checkbox-wrapper').removeClass('active');
            $(this).closest('.checkbox-wrapper').addClass('active');

            if( $(this).closest('#invite-option-copypaste').length) {
                if($(this).closest('form').hasClass('invite-patient-copy-paste-form-emails')) {
                    gtag('event', 'SelectEmails', {
                        'event_category': 'ReviewInvites',
                        'event_label': 'BulkInvites2',
                    });
                } else if($(this).closest('form').hasClass('invite-patient-copy-paste-form-names')) {
                    gtag('event', 'SelectNames', {
                        'event_category': 'ReviewInvites',
                        'event_label': 'BulkInvites3',
                    });
                }
            } else if($(this).closest('#invite-option-file').length) {
                if($(this).closest('form').hasClass('invite-patient-copy-paste-form-emails')) {
                    gtag('event', 'SelectEmails', {
                        'event_category': 'ReviewInvites',
                        'event_label': 'FileImport2',
                    });
                } else if($(this).closest('form').hasClass('invite-patient-copy-paste-form-names')) {
                    gtag('event', 'SelectNames', {
                        'event_category': 'ReviewInvites',
                        'event_label': 'FileImport3',
                    });
                }
            }
            $(this).closest('form').submit();
        });

        $('.bulk-invite-back').click( function() {
            $(this).closest('.copypaste-wrapper').hide();
            $(this).closest('.invite-content').find('.step'+$(this).attr('step')).find('.invite-input-radio').prop('checked', false);
            $(this).closest('.invite-content').find('.step'+$(this).attr('step')).find('.checkbox-wrapper').removeClass('active');
            $(this).closest('.invite-content').find('.step'+$(this).attr('step')).show();
        });
    }

    inviteRadio();

    $('.try-invite-again').click( function() {
        $(this).closest('.copypaste-wrapper').hide();
        $(this).closest('.invite-content').find('.step1').show();
        $(this).closest('.copypaste-wrapper').find('.invite-alert').hide();
    });

    if( $('.invite-patient-copy-paste-form').length ) {
        $('.invite-patient-copy-paste-form').submit( function(e) {
            e.preventDefault();

            if(ajax_is_running) {
                return;
            }

            ajax_is_running = true;

            $(this).find('.invite-alert').hide().removeClass('alert-warning').removeClass('alert-success');

            var that = $(this);
            var unique_id = $(this).closest('.invite-content').attr('radio-id');

            $.post( 
                $(this).attr('action'), 
                $(this).serialize() , 
                function( data ) {
                    if(data.success && data.info) {

                        that.closest('.copypaste-wrapper').next().find('.checkboxes-inner').html('');
                        for (var i in data.info) {

                            that.closest('.copypaste-wrapper').next().find('.checkboxes-inner').append('<div class="checkbox-wrapper" attr="'+i+'"><label class="invite-radio" for="r'+unique_id+i+'"><i class="far fa-square"></i><input type="radio" name="patient-emails" value="'+(parseInt(i) + 1)+'" class="invite-input-radio" id="r'+unique_id+i+'"></label><div class="copypaste-box"></div></div>');

                            for (var u in data.info[i]) {
                                that.closest('.copypaste-wrapper').next().find('.checkboxes-inner').find('.checkbox-wrapper[attr="'+i+'"]').find('.copypaste-box').append('<p>'+(data.info[i][u]? data.info[i][u] : '-')+'</p>');
                            }
                        }

                        var scroll_parent = that.closest('.copypaste-wrapper').next().find('.checkboxes-inner');
                        var children = scroll_parent.children();
                        var total = 0;

                        children.each( function() { 
                            total += $(this).outerWidth() + parseFloat($(this).css('margin-right')) + parseFloat($(this).css('margin-left'));
                        });

                        scroll_parent.css('width', total + parseFloat(scroll_parent.css('padding-left')) + parseFloat(scroll_parent.css('padding-right')));

                        that.closest('.copypaste-wrapper').hide().next().show();

                        inviteRadio();

                        that.closest('.invite-content').find('.step4').find('.final-button').show(); 
                        that.closest('.invite-content').find('.step4').find('.bulk-invite-back').show();
                        that.closest('.invite-content').find('.step4').find('.try-invite-again').hide();

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
        } );
    }

    $('.invite-patient-copy-paste-form-emails').submit( function(e) {
        e.preventDefault();

        if(ajax_is_running) {
            return;
        }

        ajax_is_running = true;

        $(this).find('.invite-alert').hide().removeClass('alert-warning').removeClass('alert-success');

        var that = $(this);
        var unique_id = $(this).closest('.invite-content').attr('radio-id');

        $.post( 
            $(this).attr('action'), 
            $(this).serialize() , 
            function( data ) {
                if(data.success && data.info) {

                    that.closest('.copypaste-wrapper').next().find('.checkboxes-inner').html('');

                    for (var i in data.info) {

                        that.closest('.copypaste-wrapper').next().find('.checkboxes-inner').append('<div class="checkbox-wrapper" attr="a'+i+'"><label class="invite-radio" for="ra'+unique_id+i+'"><i class="far fa-square"></i><input type="radio" name="patient-names" value="'+(parseInt(i) + 1)+'" class="invite-input-radio" id="ra'+unique_id+i+'"></label><div class="copypaste-box"></div></div>');

                        for (var u in data.info[i]) {
                            that.closest('.copypaste-wrapper').next().find('.checkboxes-inner').find('.checkbox-wrapper[attr="a'+i+'"]').find('.copypaste-box').append('<p>'+(data.info[i][u]? data.info[i][u] : '-')+'</p>');
                        }
                    }

                    var scroll_parent = that.closest('.copypaste-wrapper').next().find('.checkboxes-inner');
                    var children = scroll_parent.children();
                    var total = 0;

                    children.each( function() { 
                        total += $(this).outerWidth() + parseFloat($(this).css('margin-right')) + parseFloat($(this).css('margin-left'));
                    });

                    scroll_parent.css('width', total + parseFloat(scroll_parent.css('padding-left')) + parseFloat(scroll_parent.css('padding-right')));

                    that.closest('.invite-content').find('.for-email').html(data.emails);

                    that.closest('.copypaste-wrapper').hide().next().show();

                    inviteRadio();

                } else {
                    that.find('.invite-alert').show().addClass('alert-warning').html(data.message); 
                                    
                }
                ajax_is_running = false;

            }, "json"
        );
    } );

    $('.invite-patient-copy-paste-form-names').submit( function(e) {
        e.preventDefault();

        if(ajax_is_running) {
            return;
        }

        ajax_is_running = true;

        $(this).find('.invite-alert').hide().removeClass('alert-warning').removeClass('alert-success');

        var that = $(this);

        $.post( 
            $(this).attr('action'), 
            $(this).serialize() , 
            function( data ) {
                if(data.success) {

                    that.closest('.copypaste-wrapper').next().find('.for-name').html(data.names);
                    that.closest('.copypaste-wrapper').hide().next().show();

                } else {
                    that.find('.invite-alert').show().addClass('alert-warning').html(data.message); 
                                    
                }
                ajax_is_running = false;

            }, "json"
        );
    } );

    $('.invite-patient-copy-paste-form-final').submit( function(e) {
        e.preventDefault();
        
        if(ajax_is_running) {
            return;
        }

        ajax_is_running = true;

        var that = $(this);

        that.find('.invite-alert').hide().removeClass('alert-warning').removeClass('alert-success').removeClass('alert-orange');
        that.find('button').addClass('waiting');

        $.post( 
            $(this).attr('action'), 
            $(this).serialize() , 
            function( data ) {
                
                that.find('button').removeClass('waiting');

                if(data.success) {
                    that.find('.invite-alert').show().addClass('alert-'+data.color).html(data.message);
                    that.find('.final-button').hide(); 
                    that.find('.bulk-invite-back').hide(); 
                    that.find('.try-invite-again').show();

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
                    that.find('.invite-alert').show().addClass('alert-warning').html(data.message); 
                }
                ajax_is_running = false;

            }, "json"
        );
    } );

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

                    that.closest('.copypaste-wrapper').next().find('.checkboxes-inner').append('<div class="checkbox-wrapper" attr="'+i+'"><label class="invite-radio" for="r'+unique_id+i+'"><i class="far fa-square"></i><input type="radio" name="patient-emails" value="'+(parseInt(i) + 1)+'" class="invite-input-radio" id="r'+unique_id+i+'"></label><div class="copypaste-box"></div></div>');

                    for (var u in data.info[i]) {
                        that.closest('.copypaste-wrapper').next().find('.checkboxes-inner').find('.checkbox-wrapper[attr="'+i+'"]').find('.copypaste-box').append('<p>'+(data.info[i][u]? data.info[i][u] : '-')+'</p>');
                    }
                }

                var scroll_parent = that.closest('.copypaste-wrapper').next().find('.checkboxes-inner');
                var children = scroll_parent.children();
                var total = 0;

                children.each( function() { 
                    total += $(this).outerWidth() + parseFloat($(this).css('margin-right')) + parseFloat($(this).css('margin-left'));
                });

                scroll_parent.css('width', total + parseFloat(scroll_parent.css('padding-left')) + parseFloat(scroll_parent.css('padding-right')));

                that.closest('.copypaste-wrapper').hide().next().show();

                inviteRadio();

                that.closest('.invite-content').find('.step4').find('.final-button').show(); 
                that.closest('.invite-content').find('.step4').find('.bulk-invite-back').show();
                that.closest('.invite-content').find('.step4').find('.try-invite-again').hide();

                gtag('event', 'Upload', {
                    'event_category': 'ReviewInvites',
                    'event_label': 'FileImport1',
                });
            } else {
                that.find('.invite-alert').show().addClass('alert-warning').html(data.message); 
                                
            }
            ajax_is_running = false;

        }).bind(this) ).fail(function (data) {
                console.log('error');
            // $(this).find('.alert').addClass('alert-danger').html('Грешка, моля, опитайте отново.').show();
        });

    } );


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
        var upload = new Upload(file, $(this).attr('upload-url'), function(data) {
            $('.add-gallery-image .image-label').removeClass('loading');

            var html = '<a href="'+data.original+'" data-lightbox="user-gallery" class="slider-wrapper">\
                <div class="slider-image cover" style="background-image: url(\''+data.url+'\')">\
                    <div class="delete-gallery delete-button" sure="'+sure_text+'">\
                        <i class="fas fa-times"></i>\
                    </div>\
                </div>\
            </a>\
            ';

            galleryFlickty.flickity( 'insert', $(html), 1 );
            handleGalleryRemoved();

            if($('body').hasClass('guided-tour')) {
                $('.bubble-guided-tour .skip-step').trigger('click');
            }


            ajax_is_running = false;
        });

        upload.doUpload();
    } );

    //Work hours

    $('#popup-wokring-time form').submit( function(e) {
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
                    $('input[data-popup-logged="popup-wokring-time"]').val(data.value);
                    closePopup();

                    if($('body').hasClass('guided-tour')) {
                        $('.bubble-guided-tour .skip-step').trigger('click');
                    }

                } else {
                    $(this).find('.alert').show();

                    $('html, body').animate({
                        scrollTop: that.offset().top - $('header').height()
                    }, 500);
                }
                ajax_is_running = false;
            }).bind(this), "json"
        );          

        return false;
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

    $('.ask-dentist-submit-review').click( function(e) {
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
                    $('.button-inner-white.button-ask').hide();
                    $('.ask-dentist').closest('.alert').hide();
                    $('.ask-success-alert').show();
                    $('#review-confirmed').hide();                 

                    gtag('event', 'Request', {
                        'event_category': 'Reviews',
                        'event_label': 'InvitesAsk',
                    });
                } else {
                    console.log('error');
                }
            }
        );
    } );

    $('.team-container .delete-invite').click( function(e) {
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

        var id = $(this).closest('.slider-wrapper').attr('invite-id');
        $.ajax( {
            url: lang + '/profile/invites/delete/'+id,
            type: 'GET',
            dataType: 'json',
            success: (function( data ) {
                ajax_is_running = false;
                teamFlickity.flickity( 'remove', $(this).closest('.slider-wrapper') );
            }).bind(this)
        });

    } );



    //Invite teammembers


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

    $('.team-container .approve-buttons div').click( function(e) {
        e.preventDefault();
        e.stopPropagation();

        if( $(this).hasClass('no') ) {
            var r = confirm( $(this).attr('sure') );
            if(!r) {
                return;
            }
        }

        if(ajax_is_running) {
            return;
        }
        ajax_is_running = true;

        $.get( 
            $(this).attr('action'),
            (function( data ) {
                if( $(this).hasClass('yes') ) {
                    $(this).closest('.slider-wrapper').removeClass('pending');
                    $(this).closest('.slider-wrapper').find('.approve-buttons').remove();
                } else {
                    teamFlickity.flickity( 'remove', $(this).closest('.slider-wrapper') );
                }
                ajax_is_running = false;
            }).bind(this), "json"
        );

    } );


    //
    //End Popups
    //

    //Write review 

    $('#write-review-form').submit( function(e) {
        e.preventDefault();

        $('#treatment-error').hide();
        $('#review-crypto-error').hide();
        $('#review-answer-error').hide();
        $('#review-short-text').hide();
        $('#review-error').hide();
        $('#video-not-agree').hide();
        $('#write-review-form .rating-error').hide();

        var allgood = true;

        var coun_vals = 0;

        $(this).find('input[type="hidden"]').each( function() {
            if ($(this).closest('.question').hasClass('hidden-review-question') && !$('#clinic_dentists').val()) {
                console.log('Skip 4th question'); //don't check because it's 4th question and I didn't pick a dentist
            } else if($(this).closest('.questions-wrapper').hasClass('team-dentist')) {
                if($(this).val() != '' && $(this).attr('name')!='_token' && $(this).attr('name')!='youtube_id') {
                    coun_vals++;
                }
            } else {
                if( !parseInt($(this).val()) && $(this).attr('name')!='_token' && $(this).attr('name')!='youtube_id' ) {
                    allgood = false;
                    $(this).closest('.question').find('.rating-error').show();

                    $('html, body').animate({
                        scrollTop: $(this).closest('.question').offset().top - 20
                    }, 500);
                    return false;
                }
            }
        } );

        if ($(this).find('.questions-wrapper').hasClass('team-dentist') && coun_vals != 10) {
            allgood = false;
            return false;
        }

        if( $('#youtube_id').val().trim().length && !$('#video-agree').is(':checked') ) {
            allgood = false;
            $('#video-not-agree').show();
            $('html, body').animate({
                scrollTop: $('.review-tabs').offset().top - 20
            }, 500);
        }

        if( !$('.treatment').is(':checked') ) {
            allgood = false;
            $('#treatment-error').show();
            $('html, body').animate({
                scrollTop: $('.question-treatments').offset().top - 20
            }, 500);

        }

        if( !$('#review-title').val().trim().length || (!$('#review-answer').val().trim().length && !$('#youtube_id').val().trim().length) ) {
            allgood = false;
            $('#review-answer-error').show();
            $('html, body').animate({
                scrollTop: $('.review-tabs').offset().top - 20
            }, 500);

        }

        if(ajax_is_running || !allgood) {
            return;
        }
        ajax_is_running = true;


        var btn = $(this).find('[type="submit"]').first();
        btn.attr('data-old', btn.html());
        btn.html('<i class="fa fa-spinner fa-pulse fa-fw"></i> '+btn.attr('data-loading'));

        var that = $(this);

        $.post( 
            $(this).attr('action'), 
            $(this).serialize() , 
            function( data ) {

                console.log(data, data.imgs_urls);
                if(data.success) {
                    $('#review-confirmed').show();
                    $('#review-submit-button').hide();

                    that.find('.question:not(.review-desc)').hide();
                    that.find('.review-desc').find('.popup-title').hide();
                    that.find('.review-desc').find('.reviews-wrapper').hide();  

                    gtag('event', 'Submit', {
                        'event_category': 'Reviews',
                        'event_label': 'Reviews',
                    });
                } else {
                    if(data.valid_input) {
                        $('#review-crypto-error').show();
                        $('#review-crypto-error span').html(data.message);

                        $('html, body').animate({
                            scrollTop: $('.review-tabs').offset().top - 20
                        }, 500);    
                    } else if(data.short_text) {
                        $('#review-short-text').show();

                        $('html, body').animate({
                            scrollTop: $('.review-tabs').offset().top - 20
                        }, 500);                        
                    } else if(data.ban) {

                        window.location.reload(); 

                    } else if(data.redirect) {

                        $('.sso img').remove();

                        for( var i in data.imgs_urls) {
                            $('body').append('<img class="sso-imgs hide" src="'+data.imgs_urls[i]+'"/>');
                        }

                        var ssoTotal = $('.sso-imgs').length;
                        var ssoLoaded = 0;
                        $('.sso-imgs').each( function() {
                            if( $(this)[0].complete ) {
                                ssoLoaded++;        
                                if(ssoLoaded==ssoTotal) {
                                    window.location.href = data.redirect;
                                }   
                            }
                        } );
                        var ssoLoaded = 0;
                        $('.sso-imgs').on('load error', function() {
                            ssoLoaded++;        
                            if(ssoLoaded==ssoTotal) {
                                window.location.href = data.redirect;
                            }
                        });
                        
                    } else {
                        $('#review-error').show();

                        $('html, body').animate({
                            scrollTop: $('.review-tabs').offset().top - 20
                        }, 500);
                    }
                }
                
                btn.html( btn.attr('data-old') );
                ajax_is_running = false;
            }, "json"
        );          


        return false;

    } );


    //Video reviews
    if($('#myVideo').length) {
        var player = videojs("myVideo", {
            controls: false,
            //width: 720,
            //height: 405,
            fluid: true,
            plugins: {
                record: {
                    audio: true,
                    video: true,
                    maxLength: 736,
                    debug: true
                }
            }
        }, function(){
            // print version information at startup
            videojs.log('Using video.js', videojs.VERSION,
                'with videojs-record', videojs.getPluginVersion('record'),
                'and recordrtc', RecordRTC.version);
        });
        
        $('#init-video').click( function() {
            $('.myVideo-dimensions').show();
            var hm = player.record().getDevice();
        } );
        $('#start-video').click( function() {
            player.record().start();
            $('#start-video').hide();
            $('#stop-video').show();
            $('#review-option-video .alert').hide();
        } );
        $('#stop-video').click( function() {
            player.record().stop();
            $('#wrapper').hide();

        });

        // error handling
        player.on('deviceError', function() {       
            console.log('device error:', player.deviceErrorCode);
            if(player.deviceErrorCode.name && player.deviceErrorCode.name=="NotAllowedError") {
                $('#video-denied').show();
            } else {
                $('#video-error').show();                
            }
        });

        player.on('error', function(error) {
            console.log('error:', error);
            
        });
        player.on('startRecord', function() {
            videoStart = Date.now();
            console.log('started recording!');
        });


        // user clicked the record button and started recording
        player.on('deviceReady', function() {
            $('#init-video').hide();
            $('#video-denied').hide();
            $('#video-error').hide();
            $('#start-video').show();
            console.log('deviceReady!');
        });

        // user completed recording and stream is available
        player.on('finishRecord', function() {
            videoLength = Date.now() - videoStart;
            console.log(videoLength);
            if(videoLength<15000) {
                videoLength = null;
                videoStart = null;
                $('#start-video').show();
                $('#stop-video').hide();
                $('#video-short').show();
                return;
            }
            console.log('finished recording: ', player.recordedData, player.recordedData.video);

            $('#stop-video').hide();
            $('#video-progress').show();


            var fd = new FormData();
            fd.append('qqfile', player.recordedData.video ? player.recordedData.video : player.recordedData);
            $.ajax({
                xhr: function() {
                    var xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener("progress", function(evt) {
                        if (evt.lengthComputable) {
                            var percentComplete = evt.loaded / evt.total * 100;
                            //Do something with upload progress here
                            $('#video-progress-percent').html(Math.ceil(percentComplete));
                            console.log(percentComplete);
                            if( Math.ceil(percentComplete)==100 ) {
                                $('#video-progress').hide();
                                $('#video-youtube').show();
                            }
                        }
                        }, false);

                    xhr.addEventListener("progress", function(evt) {
                       if (evt.lengthComputable) {
                            var percentComplete = evt.loaded / evt.total * 100;
                            //Do something with download progress
                            $('#video-progress-percent').html(Math.ceil(percentComplete));
                            console.log(percentComplete);
                            if( Math.ceil(percentComplete)==100 ) {
                                $('#video-progress').hide();
                                $('#video-youtube').show();
                            }
                       }
                    }, false);

                    return xhr;
                },
                type: 'POST',
                url: lang + '/youtube',
                data: fd,
                processData: false,
                contentType: false,
                dataType: 'json'
            }).done(function(responseJSON) {
                if (responseJSON.url) {
                    $('#video-uploaded').show();
                    $('#video-youtube').hide();
                    $('#youtube_id').val(responseJSON.url);
                } else {
                    $('#video-error').show();
                    $('#start-video').show();
                }
            }).fail( function(error) {
                console.log(error);
                $('#video-error').show();
                $('#start-video').show();
            } );
        });
    }

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
        el.find('.current-social i').removeAttr('class');
        el.find('.current-social i').attr('class', $(this).attr('social-class'));
        el.find('.social-link-input').attr('name', 'socials['+$(this).attr('social-type')+']');
        el.find('.social-dropdown').removeClass('active');

        // el.closest('.address-suggester-wrapper-input').find('.social-dropdown .social-link[social-type="'+ $(this).attr('social-type') +'"]').each( function() {
        //     $(this).addClass('inactive');
        // });

        //get a List of elements that should be hidden
        var hideClasses = [];
        $('.s-wrap .current-social').each( function() {
            if( hideClasses.indexOf( $(this).attr('cur-type') ) == -1 ) {
                hideClasses.push( $(this).attr('cur-type') );
            }
        } )

        $('.s-wrap .social-dropdown').each( function() {
            $(this).find('a').each( function() {
                if( hideClasses.indexOf( $(this).attr('social-type') ) == -1 ) {
                    $(this).removeClass('inactive');
                } else {
                    $(this).addClass('inactive');
                }
            } )
        });

    });

    $('.add-social-profile').click( function() {

        var social_wrapper = $(this).closest('.address-suggester-wrapper-input').find('.social-wrap');
        var cloned = social_wrapper.first().clone(true).insertAfter( $(this).closest('.address-suggester-wrapper-input').find('.social-wrap').last() );

        cloned.find('.social-link-input').val('');
        cloned.find('.social-dropdown .social-link:not(.inactive)').first().trigger('click');

        if((social_wrapper.length +1) == social_wrapper.first().find('.social-dropdown a').length ) {
            $(this).hide();
        }

        if($('body').hasClass('guided-tour')) {
            resizeGuidedTourWindow($('.social-wrapper:visible'), false);
        }
        
    });

    $('input[name="current-email"]').change( function() {
        if ($(this).is(':checked')) {
            $(this).closest('.email-wrapper').find('input[name="email_public"]').val($(this).attr('cur-email'));
            $(this).closest('.email-wrapper').find('input[name="email_public"]').attr('disabled','disabled');
        } else {
            $(this).closest('.email-wrapper').find('input[name="email_public"]').val('');
            $(this).closest('.email-wrapper').find('input[name="email_public"]').removeAttr('disabled');
        }
    }); 

    $('.scroll-to-map').click( function() {
        $('.profile-tabs .tab[data-tab="about"]').trigger('click');
        $('html, body').animate({
            scrollTop: $('.map-container').offset().top - $('header').height() - 40
        }, 500);        
    });

    $('.ask-review-button').click( function(e) {
        $('#popup-ask-dentist').find('.ask-dentist').attr('href', $('#popup-ask-dentist').find('.ask-dentist').attr('original-href')+'/1' );

        gtag('event', 'Request', {
            'event_category': 'Reviews',
            'event_label': 'InvitesAskUnver',
        });
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

    $('.ask-review').click( function() {
        var id = $(this).attr('review-id');
        showFullReview(id, $('#cur_dent_id').val());
    } );


    if ($('#reviews-chart').length) {

        am4core.ready(function() {

        // Themes begin
            am4core.useTheme(am4themes_animated);
            // Themes end

            // Create chart instance
            var chart = am4core.create("reviews-chart", am4charts.XYChart);
            //chart.scrollbarX = new am4core.Scrollbar();
            //console.log(chart.scrollbarX);

            chart.seriesContainer.draggable = false;

            // Add data
            for (var i in aggregated_reviews) {
                var q = i.replace(' ', '\n\r');

                chart.data.push({
                    "question": i,
                    "rating": aggregated_reviews[i].toFixed(2)
                    
                });
            }

            // Create axes
            var categoryAxis = chart.xAxes.push(new am4charts.CategoryAxis());
            categoryAxis.dataFields.category = "question";
            categoryAxis.renderer.grid.template.location = 0;
            categoryAxis.renderer.grid.template.disabled = true;
            categoryAxis.renderer.minGridDistance = 60;
            if ($(window).outerWidth < 769) {
                categoryAxis.renderer.labels.template.fontSize = 12;
            }
            //categoryAxis.fontSize = 0;
            categoryAxis.tooltip.disabled = true;

            var valueAxis = chart.yAxes.push(new am4charts.ValueAxis());
            valueAxis.renderer.minWidth = 50;
            valueAxis.maxPrecision = 0;
            valueAxis.min = 0;
            valueAxis.max = 5;
            valueAxis.cursorTooltipEnabled = false;

            // Create series
            var series = chart.series.push(new am4charts.ColumnSeries());
            series.sequencedInterpolation = true;
            series.dataFields.valueY = "rating";
            series.dataFields.categoryX = "question";
            series.tooltipText = "[{categoryX}: bold]{valueY}[/]";
            series.tooltip.autoTextColor = false;
            series.tooltip.label.fill = am4core.color("#FFFFFF");
            series.columns.template.strokeWidth = 0;

            series.tooltip.pointerOrientation = "vertical";

            series.columns.template.column.cornerRadiusTopLeft = 15;
            series.columns.template.column.cornerRadiusTopRight = 15;
            // series.columns.template.column.fillOpacity = 0.8;

            //tuk
            if ($('#reviews-chart').hasClass('three-columns')) {
                series.columns.template.width = 65;
            } else {
                series.columns.template.width = am4core.percent(35);
            }
            

            // on hover, make corner radiuses bigger
            var hoverState = series.columns.template.column.states.create("hover");
            hoverState.properties.cornerRadiusTopLeft = 0;
            hoverState.properties.cornerRadiusTopRight = 0;
            hoverState.properties.fillOpacity = 1;

            chart.colors.list = [
                am4core.color("#02a5d9"),
                am4core.color("#04a8d8"),
                am4core.color("#09acd6"),
                am4core.color("#0cafd4"),
                am4core.color("#12b4d1"),
                am4core.color("#18bace"),
                am4core.color("#20c2c9"),
                am4core.color("#27c8c6"),
                am4core.color("#2dcec3"),
                am4core.color("#38dbd0")                
            ];
            series.columns.template.events.once("inited", function(event){
                event.target.fill = chart.colors.getIndex(event.target.dataItem.index);
            });

            series.columns.template.events.disableType("toggled");

            // Cursor
            chart.cursor = new am4charts.XYCursor();
            chart.cursor.behavior = "panX";
            chart.cursor.lineY.disabled = true;
            chart.cursor.lineX.disabled = true;

            chart.events.on("ready", function (e) {

                $('#reviews-chart').find('tspan').each( function() {
                    if ($(this).text() == '0' || $(this).text() == '1' || $(this).text() == '2' || $(this).text() == '3' || $(this).text() == '4' || $(this).text() == '5') {
                        $(this).css('font-size', 0);
                    } else {
                        $(this).css('font-weight', 700);
                    }
                });

                $('#reviews-chart').find('g[aria-labelledby="id-66-title"]').hide();

                $('#reviews-chart').append('<div class="chart-overlap"></div>');
            });
            
        }); // end am4core.ready()

        
        if ($(window).outerWidth() < 980) {

            $('.slide-animation').addClass('active');
            $('.review-chart').on('click taphold swipe touchmove', function() {
                $('.slide-animation').removeClass('active');
            });
        }
    }


    $('.treatment').change( function() {
        $(this).closest('label').toggleClass('active');

        var duplicate_treatment = $(this).closest('.treatment-wrapper').find('.treatment[treatment="'+$(this).attr('treatment')+'"][category!="'+$(this).attr('category')+'"]');
        if (duplicate_treatment.length) {
            if (duplicate_treatment.closest('label').hasClass('active')) {
                duplicate_treatment.closest('label').removeClass('active');
                duplicate_treatment.removeAttr('checked');
            } else {
                duplicate_treatment.closest('label').addClass('active');
                duplicate_treatment.attr('checked', 'checked');
            }
            
        }

        if ($(this).closest('.question').next().hasClass('hidden')) {
            $(this).closest('.question').next().removeClass('hidden');
        }

        $('#treatment-error').hide();
    });

    $('.more-treatments').click( function() {
        $(this).toggleClass('active');
        $(this).closest('.treatment-wrapper').find('.treatments-hidden').toggleClass('active');
    });

    if (window.location.hash.length && $('.tab[data-tab="'+window.location.hash.substring(1)+'"]').length) {
        $('.tab[data-tab="'+window.location.hash.substring(1)+'"]').trigger( "click" );
    }

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


    if($('#symbols-count').length) {
        $('#symbols-count').html($('#dentist-description').val().length);
    }

    if($('#symbols-count-short').length) {
        $('#symbols-count-short').html($('#dentist-short-description').val().length);
    }

    $('#dentist-description').keyup(function() {
        var length = $(this).val().length;

        if (length > 512) {
            $('#symbols-count').addClass('red');
        } else {
            $('#symbols-count').removeClass('red');
        }
        $('#symbols-count').html(length);
    });

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


    $('.invite-tabs a').click( function() {
        $('.invite-tabs a').removeClass('active');
        $(this).addClass('active');
        $('.invite-content').hide();
        $('#invite-option-'+$(this).attr('data-invite')).show();
    });

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

                            if(step.action == 'work_hours') {
                                $('#popup-wokring-time').css('z-index', 10000);
                            }

                            if(step.action == 'invite') {
                                $('#popup-invite').css('z-index', 10000);
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


});