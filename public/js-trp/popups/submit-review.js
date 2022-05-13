var videoStart, videoLength;
var showFullReview;
var ajax_is_running = false;

$(document).ready(function() {
    
    $('.review-type').change( function() {
        $('#review-answer-error').hide();
        $('.review-type-content').hide();
        $('.review-option-'+$(this).val()).show();

        if( $(this).val()=='video' ) {
            if($('.review-video-info').hasClass('hide-video-reviews')) {
                $('.review-title-wrap').hide();
            } else {
                $('.review-title-wrap').show();
            }
        } else {
            $('.review-title-wrap').show();
        }
    });

    var showNextStep = function(step) {
        $('.write-review-step').hide();
        $('.write-review-step-'+step).show();

        if($(window).outerWidth() <= 768) {
            $('.popup').animate({
                scrollTop: 0
            }, 500);
        }
    }

    $('.show-second-step').click( function() {
        showNextStep(2);
    });

    $('.review-next-step').click( function() {

        if($(this).attr('step') == 2) {

            $('#review-short-text').hide();
            $('#review-answer-error').hide();

            if(ajax_is_running) {
                return;
            }
            ajax_is_running = true;

            var that = $(this);

            $.ajax( {
                url: $(this).attr('url'),
                type: 'POST',
                data: {
                    title: $('#review-title').val(),
                    answer: $('#review-answer').val(),
                    youtube_id: $('#youtube_id').val(),
                    _token: $('input[name="_token"]').val()
                },
                dataType: 'json',
                success: function( data ) {
                    if(data.success) {

                        if(that.hasClass('submit-video-review')) {
                            $('.write-review-step').hide();
                            $('.write-video-review-success').show();
                            that.removeClass('submit-video-review');
                        } else {
                            showNextStep(2);
                        }
                    } else {
                        if(data.short_text) {
                            $('#review-short-text').show();
                            $('.popup').animate({
                                scrollTop: $('#review-short-text').offset().top - 20
                            }, 500);
                        } else {
                            $('#review-answer-error').show();
                            $('.popup').animate({
                                scrollTop: $('#review-answer-error').offset().top - 20
                            }, 500);
                        }
                    }
                    ajax_is_running = false;
                },
                error: function(data) {
                    console.log('ERROR SF')
                }
            });
        } else if($(this).attr('step') == 3) {
            
            $('.rating-error').hide();
            let formData = new FormData($('#write-review-form')[0]);
            
            if(ajax_is_running) {
                return;
            }
            ajax_is_running = true;

            $.ajax( {
                url: $(this).attr('url'),
                type: 'POST',
                data: formData,
                dataType: 'json',
                cache: false,
                contentType: false,
                processData: false,
                success: function( data ) {
                    if(data.success) {
                        showNextStep(3);
                    } else {
                        $('.rating-error').show();
                        $('.popup').animate({
                            scrollTop: $('.rating-error').offset().top - 20
                        }, 500);
                    }
                    ajax_is_running = false;
                },
                error: function(data) {
                    console.log('ERROR SF')
                }
            });
        }
    });

    $('.review-prev-step').click( function() {
        $('.write-review-step').hide();
        $('.write-review-step-'+$(this).attr('step')).show();
    });


    // SELECT DENTIST/CLINIC FOR REVIEW
    
    $('.popup').click( function(e) {
        let selectTeam = $(e.target).closest('.select-team-wrapper');
        if (selectTeam.length) {
            if(selectTeam.find('.select-team-options').is(':visible')) {
                selectTeam.removeClass('active');
            } else {
                selectTeam.addClass('active');
            }
        } else {
            if($('.select-team-wrapper').length) {
                $('.select-team-wrapper').removeClass('active');
            }
        }
    });
    
    $('.remove-selected-team').click( function(e) {
        e.preventDefault();
        e.stopPropagation();

        $(this).closest('.teams').find('input').val('');
        $(this).closest('.teams').find('p').html($('.select-team-chosen-label').text());
        $('.select-team-wrapper').removeClass('active').removeClass('chosen');
    });
    
    $('.select-team').click( function(e) {
        e.preventDefault();
        e.stopPropagation();

        if($(this).closest('.dentist-in-clinic').length) {
            if ($(this).attr('team-id') == 'own') {
                $('#submit-review-popup .questions .question').hide();
                $('#submit-review-popup .questions .question.dentist-question').show();
            } else {
                $('#submit-review-popup .questions .question').show();
            }
        }

        $(this).closest('.teams').find('input').val($(this).attr('team-id'));
        $(this).closest('.teams').find('p').html($(this).text());
        $('.select-team-wrapper').removeClass('active').addClass('chosen');
    });


    //ANSWER QUESTIONS

    $('#write-review-form .stars:not(.fixed-stars)').mousemove( function(e) {
        var rate = e.offsetX;
        rate = Math.ceil( rate*5 / $(this).width() );
        $(this).find('.bar').css('width', (rate*20)+'%' );

    }).click( function(e) {
        var rate = e.offsetX;
        rate = Math.ceil( rate*5 / $(this).width() );

        $(this).find('input').val(rate).trigger('change');
        $(this).closest('.review-answers').find('.rating-error').hide();
        $(this).find('.bar').css('width', (rate*20)+'%' );

    }).mouseout( function(e) {
        var rate = parseInt($(this).find('input').val());
        if(rate) {
            $(this).find('.bar').css('width', (rate*20)+'%' );
        } else {
            $(this).find('.bar').css('width', 0 );
        }
    });

    //Write review 

    $('#write-review-form').submit( function(e) {
        e.preventDefault();

        //check from treatments
        if( !$('.treatment').is(':checked') ) {
            $('#treatment-error').show();
            $('.popup').animate({
                scrollTop: $('.question-treatments').offset().top - 20
            }, 500);
            return;
        }

        //hide error messages
        $('#treatment-error').hide();
        $('#review-answer-error').hide();
        $('#review-short-text').hide();
        $('#review-error').hide();
        $('#write-review-form .rating-error').hide();

        //disable form for submitting again
        if(ajax_is_running) {
            return;
        }
        ajax_is_running = true;
        
        //show loader
        $('.write-review-loader').fadeIn();
        $('.write-review-loader video')[0].play();
        $('.write-review-loader video')[0].removeAttribute("controls");

        $('.popup').animate({
            scrollTop: 0
        }, 500);

        var that = $(this);

        setTimeout( function() {
            $.post( 
                that.attr('action'), 
                that.serialize() , 
                function( data ) {
                    if(data.success) {

                        $('.review-reward').html(data.review_reward+' DCN');
                        if(data.review_trusted) {
                            $('.review-rating-new').addClass('verified-review');
                        }
                        $('.review-rating-new .rating').html(data.review_rating.toFixed(1));
                        $('.review-rating-new .bar').css('width', data.review_rating/5*100+'%');

                        $('.write-review-step').hide();

                        if(data.review_video) {
                            $('.success-bottom-section.video-success').show();
                        } else {
                            if(data.review_trusted) {
                                $('.success-bottom-section.text-success').show();
                            } else {
                                $('.success-bottom-section .ask-dentist-submit-review').attr('href', $('.success-bottom-section .ask-dentist-submit-review').attr('original-href') + data.review_id);
                                $('.success-bottom-section.verify-review').show();
                            }
                        }

                        $('.write-review-success').show();

                        gtag('event', 'Submit', {
                            'event_category': 'Reviews',
                            'event_label': 'Reviews',
                        });
                    } else {
                        if(data.short_text) {
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
                    
                    ajax_is_running = false;
                }, "json"
            );            
        }, 5000);
    });


    //VIDEO REVIEW

    if($('#myVideo').length) { //if video reviews are not stopped

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
            videojs.log('Using video.js', videojs.VERSION, 'with videojs-record', videojs.getPluginVersion('record'), 'and recordrtc', RecordRTC.version);
        });
        
        $('#init-video-button').click( function() {
            $('.myVideo-dimensions').show();
            player.record().getDevice();
        });

        $('#start-video-button').click( function() {
            $('.video-alerts').hide();
            $('.video-buttons').hide();
            $('.myVideo-dimensions').show();
            player.record().start();
            $('#review-answer-error').hide();
            $('#stop-video-button').show();
        } );

        $('#stop-video-button').click( function() {
            player.record().stop();
            $('#wrapper').hide();
        });

        // error handling
        player.on('deviceError', function() {
            $('.video-alerts').hide();
            console.log('device error:', player.deviceErrorCode);

            if( player.deviceErrorCode.toString().indexOf('Requested device not found') != -1) {
                $('#alert-video-connect-camera').show();
            } else {
                if(player.deviceErrorCode.name && player.deviceErrorCode.name=="NotAllowedError") {
                    $('#alert-video-denied').show();
                } else {
                    $('#alert-video-error').show();
                }
            }
            $('.myVideo-dimensions').hide();            
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
            $('.video-alerts').hide();
            $('.video-buttons').hide();
            $('#start-video-button').show();
            console.log('deviceReady!');
        });

        // user completed recording and stream is available
        player.on('finishRecord', function() {
            $('.video-alerts').hide();
            $('.video-buttons').hide();

            videoLength = Date.now() - videoStart;
            if(videoLength<15000) { //can't submit review under 15 sec
                videoLength = null;
                videoStart = null;
                $('#alert-video-short').show();
                $('#start-video-button').show();
                $('.myVideo-dimensions').hide();   
                return;
            }
            console.log('finished recording: ', player.recordedData, player.recordedData.video);

            //add percentage loader
            $('#video-progress-loader').show();

            var fd = new FormData();
            var vd = player.recordedData.video ? player.recordedData.video : player.recordedData;

            fd.append('qqfile', vd);

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
                                $('#video-progress-loader').hide();
                                $('#alert-video-youtube-uploading').show();
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
                                $('#video-progress-loader').hide();
                                $('#alert-video-youtube-uploading').show();
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
                    $('.video-alerts').hide();
                    $('.video-buttons').hide();
                    $('#youtube_id').val(responseJSON.url);
                    $('.myVideo-dimensions').hide();
                    $('#alert-video-uploaded').show();
                } else {
                    // console.log(responseJSON);
                    $('.video-alerts').hide();
                    $('.video-buttons').hide();
                    $('#alert-video-error').show();
                    $('#start-video-button').show();
                    $('.myVideo-dimensions').hide();

                    // $('.video-buttons').hide();
                    // $('.video-alerts').hide();
                    // $('#youtube_id').val('test');
                    // $('.myVideo-dimensions').hide();
                    // $('#alert-video-uploaded').show();
                }
            }).fail( function(error) {
                // console.log(error);
                $('.video-alerts').hide();
                $('.video-buttons').hide();
                $('#alert-video-error').show();
                $('#start-video-button').show();
                $('.myVideo-dimensions').hide();

                // $('.video-buttons').hide();
                // $('.video-alerts').hide();
                // $('#youtube_id').val('test');
                // $('.myVideo-dimensions').hide();
                // $('#alert-video-uploaded').show();
            });
        });
    }

    //Ask for trusted

    $('.ask-dentist-submit-review').click( function(e) {
        e.preventDefault();

        if(ajax_is_running) {
            return;
        }
        ajax_is_running = true;

        $.get( 
            $(this).attr('href'), 
            function( data ) {
                if(data.success) {
                    $('.write-review-step').hide();
                    $('.verification-review-success').show();

                    gtag('event', 'Request', {
                        'event_category': 'Reviews',
                        'event_label': 'InvitesAsk',
                    });
                }
            }
        );
    });


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
                        closePopup();
                    } else {
                        $('.ask-dentist').closest('.alert').hide();
                        $('.ask-success').show();
                        $('.button-ask').remove();
                    }

                    gtag('event', 'Request', {
                        'event_category': 'Reviews',
                        'event_label': 'InvitesAsk',
                    });
                }
            }
        );
    });

    // $('.ask-review-button').click( function(e) {
    //     $('#popup-ask-dentist').find('.ask-dentist').attr('href', $('#popup-ask-dentist').find('.ask-dentist').attr('original-href')+'/1' );

    //     gtag('event', 'Request', {
    //         'event_category': 'Reviews',
    //         'event_label': 'InvitesAskUnver',
    //     });
    // });

    // $('.ask-review').click( function() {
    //     var id = $(this).attr('review-id');
    //     showFullReview(id, $('#cur_dent_id').val());
    // } );



    //////TREATMENTS

    var removeTreatment = function() {
        $('.remove-treatment').click( function(е) {
            е.preventDefault();
            $('.treatment[treatment="'+$(this).attr('treatment-type')+'"]').prop('checked', false);
            $('.treatment[treatment="'+$(this).attr('treatment-type')+'"]').first().trigger('change');
            $(this).remove();
        });
    }

    $('.treatment').change( function(e) {
        e.preventDefault();

        $(this).closest('label').toggleClass('active');
        let duplicate_treatment = $(this).closest('.treatments-wrapper').find('.treatment[treatment="'+$(this).attr('treatment')+'"][category!="'+$(this).attr('category')+'"]');
        let more_treatments_label = $('input[value="'+$(this).attr('treatment')+'"]').closest('.more-treatments');

        if (duplicate_treatment.length) {
            if (duplicate_treatment.closest('label').hasClass('active')) {
                duplicate_treatment.closest('label').removeClass('active');
                duplicate_treatment.prop('checked', false);
                if(!more_treatments_label.find('input:checked').length) {
                    more_treatments_label.removeClass('active');
                }
            } else {
                duplicate_treatment.closest('label').addClass('active');
                duplicate_treatment.prop('checked', true);
                more_treatments_label.addClass('active');
            }
        } else {
            if($(this).is(':checked')) {
                more_treatments_label.addClass('active');
            } else {
                if(!more_treatments_label.find('input:checked').length) {
                    more_treatments_label.removeClass('active');
                }
            }
        }

        if($('.treatment[name]:checked').length) {
            $('.review-selected-treatments').show();

            let selectedTreatments = '';
            $('.treatment[name]:checked').each( function() {
                selectedTreatments+='<a class="remove-treatment dont-close-popup treatment-type="'+$(this).attr('treatment')+'">'+$(this).attr('treatment-label')+' <span>X</span></a>';
            });

            $('.selected-treatments-wrapper .selected-treatments').html(selectedTreatments);
            removeTreatment();
        } else {
            $('.review-selected-treatments').hide();
            $('.selected-treatments-wrapper .selected-treatments').html('');
            $('.selected-treatments-wrapper').removeClass('active');
        }

        $('#treatment-error').hide();
    });

    $('.review-selected-treatments').click( function() {
        $('.selected-treatments-wrapper').toggleClass('active');
    });



    //other

    $('.toggle-review-info').click( function() {
        $('.review-info-description').toggleClass('hidden-info');
    });

});