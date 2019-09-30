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

    showFullReview = function(id) {
        showPopup('view-review-popup');
        $.ajax( {
            url: '/'+lang+'/review/' + id,
            type: 'GET',
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
                    var cls = $(this).closest('[role="editor"]').attr('class');
                    $('.'+cls+'[role="editor"]').hide();
                    $('.'+cls+'[role="presenter"]').show();
                    console.log( data.value );
                    var value_here = $('.'+cls+'[role="presenter"] .value-here');

                    if(data.value.length) {
                        value_here.html(data.value);
                        console.log('has value');
                    } else {
                        value_here.html(value_here.attr('empty-value'));
                        console.log('empty value');
                    }
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
        showFullReview( getUrlParameter('review_id') );
    }

    $('#clinic_dentists').change( function() {
        $('.hidden-review-question').show();
    });

    $('#dentist_clinics').change( function() {
        $(this).closest('.question').next().show();
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
        showFullReview(id);
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
    if( $('.widget-options input').length ) {
        $(".select-me").on("click focus", function () {
            $(this).select();
        });

        var refreshWidgetCode = function() {
            if(typeof widet_url=='undefined') {
                return;
            }
            var wmode = parseInt($('.widget-options input:checked').val());
            wmode = isNaN(wmode) ? 0 : wmode;
            var parsedUrl = widet_url.replace('{mode}', wmode);
            $('#option-iframe textarea').val('<iframe style="width: 100%; height: 50vh; border: none; outline: none;" src="'+parsedUrl+'"></iframe>');
            $('#option-js textarea').val('<div id="trp-widget"></div><script type="text/javascript" src="https://reviews.dentacoin.com/js/widget.js"></script> <script type="text/javascript"> TRPWidget.init("'+parsedUrl+'"); </script>');
        }

        $('.widget-options input').change(refreshWidgetCode);
        refreshWidgetCode();
    }

    //Invites

    if( $('.invite-patient-form').length ) {
        $('.invite-patient-form').submit( function(e) {
            e.preventDefault();

            if(ajax_is_running) {
                return;
            }

            ajax_is_running = true;

            $(this).find('.invite-alert').hide().removeClass('alert-warning').removeClass('alert-success');


            var formData = new FormData();

            // add assoc key values, this will be posts values
            formData.append("_token", $(this).find('input[name="_token"]').val());
            if ($(this).find('input[name="image"]').length) {
                var this_file = $(this).find('input[name="image"]')[0].files[0];
                formData.append("image", this_file, this_file.name);
            }
            
            formData.append("name", $(this).find('.invite-name').val());
            formData.append("email", $(this).find('.invite-email').val());


            $.ajax({
                type: "POST",
                url: $(this).attr('action'),
                success: function (data) {
                    if(data.success) {
                        $('.invite-patient-form').find('.invite-email').val('');
                        $('.invite-patient-form').find('.invite-name').val('').focus();
                        $('.invite-patient-form').find('.invite-alert').show().addClass('alert-success').html(data.message);

                        gtag('event', 'Send', {
                            'event_category': 'Reviews',
                            'event_label': 'InvitesSent',
                        });
                    } else {
                        $('.invite-patient-form').find('.invite-alert').show().addClass('alert-warning').html(data.message);                    
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

                    // gtag('event', 'Send', {
                    //     'event_category': 'Reviews',
                    //     'event_label': 'InvitesSent',
                    // });
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
                    console.log(data.message);
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
                    console.log(data.message);
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
                } else {
                    that.find('.invite-alert').show().addClass('alert-warning').html(data.message); 
                }
                ajax_is_running = false;

            }, "json"
        );
    } );

    $('#invite-file').change(function() {
        var file = $('#invite-file')[0].files[0].name;
        console.log(file);
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
                    window.location.href = data.href;
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

            ajax_is_running = false;
        });

        upload.doUpload();

    } );

    //Work hours
    $('.work-hour-cb').change( function() {
        var active = $(this).is(':checked');
        var texts = $(this).closest('.popup-desc').find('select');
        if(active) {
            texts.prop("disabled", false);
        } else {
            texts.attr('disabled', 'disabled');
        }
    } );

    $('#popup-wokring-time form').off('submit').submit( function(e) {
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

        $.ajax( {
            url: lang + '/profile/dentists/invite',
            type: 'POST',
            dataType: 'json',
            data: {
                invitedentistid: $(elm).attr('data-id')
            },
            success: (function( data ) {
                $('#dentist-add-result').html(data.message).attr('class', 'alert '+(data.success ? 'alert-success' : 'alert-warning')).show();
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
        console.log(elm, id);

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
                console.log(data);
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
                console.log($(this));
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

		$(this).find('input[type="hidden"]').each( function() {
            if ($(this).closest('.question').hasClass('hidden-review-question') && !$('#clinic_dentists').val()) {
                console.log('Skip 4th question'); //don't check because it's 4th question and I didn't pick a dentist
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

        if( $('#youtube_id').val().trim().length && !$('#video-agree').is(':checked') ) {
            allgood = false;
            $('#video-not-agree').show();
            $('html, body').animate({
                scrollTop: $('.review-tabs').offset().top - 20
            }, 500);
        }

        console.log($('.treatment').val());
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

        $.post( 
            $(this).attr('action'), 
            $(this).serialize() , 
            function( data ) {
                if(data.success) {
                    console.log('success');
                    $('#review-confirmed').show();
                    $('#review-submit-button').hide();

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
            }).fail( function() {
                $('#video-error').show();
                $('#start-video').show();
            } );
        });
    }

    $('.short-desc-arrow').click( function() {
        if ($(this).hasClass('active')) {
            $(this).removeClass('active');
            $(this).closest('.edit-profile-wrapper').find('.dentist-short-desc').removeClass('active');
        } else {
            $(this).addClass('active');
            $(this).closest('.edit-profile-wrapper').find('.dentist-short-desc').addClass('active');
        }
    });

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

        // el.closest('.address-suggester-wrapper').find('.social-dropdown .social-link[social-type="'+ $(this).attr('social-type') +'"]').each( function() {
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
            //console.log($(this).attr('cur-type'));
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

        var social_wrapper = $(this).closest('.address-suggester-wrapper').find('.social-wrap');
        var cloned = social_wrapper.first().clone(true).insertAfter( $(this).closest('.address-suggester-wrapper').find('.social-wrap').last() );

        cloned.find('.social-link-input').val('');
        cloned.find('.social-dropdown .social-link:not(.inactive)').first().trigger('click');

        if((social_wrapper.length +1) == social_wrapper.first().find('.social-dropdown a').length ) {
            $(this).hide();
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
        showFullReview(id);
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
            series.columns.template.width = am4core.percent(35);

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

    $('#invite-again').click( function(e) {
        e.preventDefault();

        var invite_url = $(this).attr('data-href');
        var invitation_id = $(this).attr('inv-id');

        console.log(invite_url);

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

});
