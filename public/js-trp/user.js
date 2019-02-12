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

$(document).ready(function(){

    showFullReview = function(id) {
        showPopup('view-review-popup');
        $.ajax( {
            url: '/'+lang+'/review/' + id,
            type: 'GET',
            success: (function( data ) {
                $('#the-detailed-review').html(data);
                showPopup('view-review-popup');
                handleReviewEvents();
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


                } );
            }


            galleryFlickty = $('.gallery-flickity').flickity({
                autoPlay: false,
                wrapAround: true,
                cellAlign: 'left',
                pageDots: true,
                freeScroll: true,
                groupCells: 1,
            });

            galleryFlickty.resize();

            teamFlickity = $('.flickity').flickity({
                autoPlay: false,
                wrapAround: true,
                cellAlign: 'left',
                pageDots: false,
                freeScroll: true,
                groupCells: 1,
            });

            teamFlickity.resize();
        }
    });


    if( getUrlParameter('tab') ) {
        $('.profile-tabs a[data-tab="'+getUrlParameter('tab')+'"]').trigger('click');
    } else {
        $('.profile-tabs a').first().click();
    }

    if(getUrlParameter('review_id')) {
        showFullReview( getUrlParameter('review_id') );
    }

    $('#clinic_dentists').change( function() {
        if ($(this).val()) {
            $('.hidden-review-question').show();
        } else {
            $('.hidden-review-question').hide();
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
    		if($(this).hasClass('voted')) {
    			;
            } else {
                var type = $(this).hasClass('thumbs-up') ? 'useful' : 'unuseful';
    			var that = this;
    			$.ajax( {
    				url: '/'+lang+'/'+type+'/' + $(this).closest('.review-wrapper').attr('review-id'),
    				type: 'GET',
    				dataType: 'json',
    				success: (function( data ) {
                        if(data.limit) {
                            ;
                        } else {
                            $(that).addClass('voted');
                            var oc = parseInt($(that).find('span').html());
                            $(that).find('span').html( ++oc );

                            var icon = $(that).find('img').first();
                            console.log( icon.attr('src').replace('.png', '-color.png') );
                            icon.attr('src', icon.attr('src').replace('.png', '-color.png'));
                        }
    				}).bind(that)
    			});			
    		}
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
        console.log(1123);
        $('.invite-patient-form').submit( function(e) {
            console.log(456);
            e.preventDefault();

            if(ajax_is_running) {
                return;
            }

            ajax_is_running = true;

            $(this).find('.invite-alert').hide().removeClass('alert-warning').removeClass('alert-success');

            $.post( 
                $(this).attr('action'), 
                $(this).serialize() , 
                (function( data ) {
                    if(data.success) {
                        $(this).find('.invite-email').val('');
                        $(this).find('.invite-name').val('').focus();
                        $(this).find('.invite-alert').show().addClass('alert-success').html(data.message);
                    } else {
                        $(this).find('.invite-alert').show().addClass('alert-warning').html(data.message);                    
                    }
                    ajax_is_running = false;
                }).bind(this), "json"
            );

            return false;
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

    //Profile edit
    $('.edit-profile').submit( function(e) {
        e.preventDefault();

        if(ajax_is_running) {
            return;
        }
        ajax_is_running = true;
        $('.edit-error').hide();

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

        var file = $(this)[0].files[0];
        var upload = new Upload(file, $(this).attr('upload-url'), function(data) {
            $('.add-gallery-image .image-label').removeClass('loading');

            var html = '<div class="slider-wrapper">\
                <div class="slider-image cover" style="background-image: url(\''+data.url+'\')"></div>\
            </div>\
            ';

            galleryFlickty.flickity( 'insert', $(html), 1 );

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
        $.get( 
            $(this).attr('href'), 
            function( data ) {
                if(data.success) {
                    $('.ask-dentist').closest('.alert').hide();
                    $('.ask-success').show();
                    $('.button-ask').remove();
                } else {

                }
            }
        );
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

        $('#review-crypto-error').hide();
        $('#review-answer-error').hide();
		$('#review-short-text').hide();
		$('#review-error').hide();
        $('#video-not-agree').hide();
        $('#write-review-form .rating-error').hide();

		var allgood = true;

		$(this).find('input[type="hidden"]').each( function() {
            if ($(this).closest('.review').hasClass('hidden-review-question') && !$('#clinic_dentists').val()) {
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
        el.find('.current-social i').removeAttr('class');
        el.find('.current-social i').attr('class', $(this).attr('social-class'));
        el.find('.social-link-input').attr('name', 'socials['+$(this).attr('social-type')+']');
        el.find('.social-dropdown').removeClass('active');
        console.log(el.closest('.address-suggester-wrapper').find('.social-dropdown .social-link'));
        el.closest('.address-suggester-wrapper').find('.social-dropdown .social-link[social-type="'+ $(this).attr('social-type') +'"]').each( function() {
            $(this).addClass('inactive');
        });

    });

    $('.add-social-profile').click( function() {

        var social_wrapper = $(this).closest('.address-suggester-wrapper').find('.social-wrap');

        var cloned = social_wrapper.first().clone(true).appendTo( $(this).closest('.address-suggester-wrapper') );

        cloned.find('.social-link-input').val('');
        cloned.find('.social-dropdown .social-link:not(.inactive)').first().trigger('click');

        if((social_wrapper.length +1) == social_wrapper.first().find('.social-dropdown a').length ) {
            $(this).hide();
        }
        
    });



});
