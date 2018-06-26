var videoStart, videoLength;
var slider = null;
var sliderTO = null;

$(document).ready(function(){

    $('.show-map').click( function() {
        $('.insert-map').append( $('.map') );
        $('.map').show();
        $('.hide-map').css('display', 'inline-block');
        initMap();
        $(this).hide();
    } );

    $('.hide-map').click( function() {
        $('.map').hide();
        $('.show-map').css('display', 'inline-block');
        $(this).hide();
    } );

	$('#write-review-btn').click( function(e) {
		e.preventDefault();

		if($(this).hasClass('verify-phone')) {
			$('#phone-verify-modal').modal();
		} else {
			$('#review-form').show();
			$(this).closest('.panel-body').remove();
		}

	} );


    if( $('.gallery-slider').length ) {
        if (window.innerWidth > 1200) {

            slider = $('.gallery-slider').bxSlider({
                useCSS: true,
                responsive: true,
                minSlides: 1,
                maxSlides: 3,
                moveSlides: 1,
                slideWidth: 380,
                adaptiveHeight: true
            });
            sliderTO = setInterval( function() {
                slider.redrawSlider();
                if($('.gallery-slider img').height()>0) {
                    clearInterval(sliderTO);
                }
            }, 500);
        } else {
            slider = $('.gallery-slider').bxSlider({
                useCSS: true,
                responsive: true,
                pager: true,
            });
            sliderTO = setInterval( function() {
                slider.redrawSlider();
                if($('.gallery-slider img').height()>0) {
                    clearInterval(sliderTO);
                }
            }, 500);
        }
    }


    $('.btn-show-review').click(function() {
        $(this).prev().toggle();
        var newT = $(this).attr('data-alt-text').trim();
        $(this).attr( 'data-alt-text', $(this).html().trim() )
        $(this).html(newT);
        
    });

    if( $('#civic-widget').length ) {

        // Step 2: Instantiate instance of civic.sip
        var civicSip = new civic.sip({ appId: 'rkvErCDdf' });


         // Step 3: Start scope request.
        var button = document.querySelector('#signupButton');
        button.addEventListener('click', function () {
            $('#withdraw-widget .alert').hide();
            $('#signupButton').hide();
            civicSip.signup({ style: 'popup', scopeRequest: civicSip.ScopeRequests.BASIC_SIGNUP });
        });

        var civicError = function() {
            $('#signupButton').show();
            $('#civic-wait').hide();
            $('html, body').animate({
                scrollTop: $("#signupButton").offset().top
            }, 500);
        }

        // Listen for data
        civicSip.on('auth-code-received', function (event) {
            console.log(event);
            var jwtToken = event.response;
            //sendAuthCode(jwtToken);

            $.ajax({
                type: "POST",
                url: 'https://dentacoin.net/civic',
                data: {
                    jwtToken: jwtToken
                },
                dataType: 'json',
                success: function(ret) {
                    if(!ret.userId) {
                        $('#civic-error').show();
                        civicError();
                    } else {
                        $('#civic-wait').show();
                        $('#signupButton').hide();

                        console.log(jwtToken);
                        setTimeout(function() {
                            $.post( 
                                $('#jwtAddress').val(), 
                                {
                                    jwtToken: jwtToken
                                }, 
                                function( data ) {
                                    if(data.weak) {
                                        $('#civic-weak').show();
                                        civicError();
                                    } else if(data.duplicate) {
                                        $('#civic-duplicate').show();
                                        civicError();
                                    } else if(data.success) {
                                        window.location.reload();
                                    } else {
                                        $('#civic-error').show();
                                        civicError();
                                    }
                                }, "json"
                            )
                            .fail(function(xhr, status, error) {
                                $('#civic-error').show();
                                civicError();
                            });
                        }, 3000);
                    }
                },
                error: function(ret) {
                    $('#civic-error').show();
                    civicError();
                }
            });

        });

        civicSip.on('user-cancelled', function (event) {
            $('#civic-cancelled').show();
            civicError();
        });

        civicSip.on('read', function (event) {
            $('#civic-wait').show();
            console.log('read');
        });

        civicSip.on('civic-sip-error', function (error) {
            $('#civic-error').show();
            civicError();
            console.log('   Error type = ' + error.type);
            console.log('   Error message = ' + error.message);
        });
    }

    $('#show-whole-review').click( function() {
        var newT = $(this).attr('data-alt-text').trim();
        $(this).attr( 'data-alt-text', $(this).html().trim() )
        $(this).html(newT);
        $(this).parent().prev().toggle();
    } );

    
    $('.show-entire-aggregation').click(function() {
        $(this).closest('.rating-panel').find('.aggregation').show();
        $(this).closest('.rating').hide();
    });
    
    $('.hide-entire-aggregation').click(function() {
        $(this).closest('.rating-panel').find('.aggregation').hide();
        $(this).closest('.rating-panel').find('.show-entire-aggregation').closest('.rating').show();
    });

	$('.label-trusted').click( function(e) {
		e.preventDefault();
		$('#trusted-modal').modal();
	});

	$('.btn-claim').click( function(e) {
		e.preventDefault();
		$('#claim-modal').modal();
	});

	$('.claim-type').change( function(){
		$(this).closest('.modal-body').find('.type-div').hide();
	    $(this).closest('.modal-body').find('.type-' + $(this).attr('data-type') ).show();
	    $(this).closest('.btn-group').find('.btn').removeClass('btn-primary').addClass('btn-default');
	    $(this).closest('.btn').addClass('btn-primary').removeClass('btn-default');
	    $(this).closest('.btn').blur();
	});


	$('.useful').click( function() {
		if($(this).hasClass('upvote-done')) {
			;
		} else if($(this).hasClass('needs-login')) {
			$(this).closest('.panel').find('.user-login-upvote').show();
        } else if($(this).hasClass('verify-phone')) {
            $('#phone-verify-modal').modal();
		//} else if(typeof(account)=='undefined' || !account) {
        //    $('#no-wallet-modal').modal();
		} else {
			var that = this;
			$.ajax( {
				url: '/'+lang+'/useful/' + $(this).attr('data-review-id'),
				type: 'GET',
				dataType: 'json',
				success: (function( data ) {
                    if(data.limit) {
                        ;
                    } else {
                        $(that).addClass('upvote-done').removeClass('btn-primary').addClass('btn-success').html( $(that).attr('data-done-text') );
    					var oc = parseInt($(that).closest('.media').find('.upvote-count').html());
    					if(oc) {
    						$(that).closest('.media').find('.upvote-count').html( ++oc );
    						$(that).closest('.media').find('.upvote-wrpapper').show();
    					}
                    }
				}).bind(that)
			});			
		}
	} );

	$('.reply-form').submit( function(e) {
		e.preventDefault();

		$(this).find('.alert').hide();

		var input = $(this).find('.review-reply').first();

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
                	that.closest('.rating').find('.the-reply').show();
                	that.closest('.rating').find('.reply-content').html(data.reply);
                	that.remove();
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

	$('#write-review-form').submit( function(e) {
		e.preventDefault();

        $('#review-crypto-error').hide();
        $('#review-answer-error').hide();
		$('#review-short-text').hide();
		$('#review-error').hide();
        $('#video-not-agree').hide();

		var allgood = true;

		$(this).find('input[type="hidden"]').each( function() {
			if( !parseInt($(this).val()) && $(this).attr('name')!='_token' && $(this).attr('name')!='youtube_id' ) {
				allgood = false;
				console.log( $(this) );
				$(this).closest('.stars').find('.rating').show();
				$('html, body').animate({
                    scrollTop: $(this).closest('.panel-body').offset().top - 60
                }, 500);
				return false;
			}
		} );

        if( $('#youtube_id').val().trim().length && !$('#video-agree').is(':checked') ) {
            allgood = false;
            $('#video-not-agree').show();
            $('html, body').animate({
                scrollTop: $('#video-agree').offset().top - 80
            }, 500);

        }

		if(allgood) {
			if( !$('#review-answer').val().trim().length && !$('#youtube_id').val().trim().length ) {
				allgood = false;
				$('#review-answer-error').show();
				$('html, body').animate({
	                scrollTop: $('#review-answer').closest('.panel-body').offset().top - 60
	            }, 500);

			}
		}



		if(ajax_is_running || !allgood) {
			return;
		}
		ajax_is_running = true;


        var btn = $(this).find('[type="submit"]').first();
        console.log(btn);
        btn.attr('data-old', btn.html());
        btn.html('<i class="fa fa-spinner fa-pulse fa-fw"></i> '+btn.attr('data-loading'));

        $.post( 
            $(this).attr('action'), 
            $(this).serialize() , 
            function( data ) {
                console.log(data);
                if(data.success) {

                    if(data.link) {
                        $('#review-confirmed').show().find('a.etherscan-link').attr('href', data.link);
                    } else {
                        $('#review-pending').show();
                    }
                    
                    $('#review-submit-button').hide();
                } else {
                    if(data.valid_input) {
                        $('#review-crypto-error').show();
                        $('#review-crypto-error span').html(data.message);

                        $('html, body').animate({
                            scrollTop: $('#review-crypto-error').closest('.panel-body').offset().top - 60
                        }, 500);    
                    } else if(data.short_text) {
                        $('#review-short-text').show();

                        $('html, body').animate({
                            scrollTop: $('#review-short-text').closest('.panel-body').offset().top - 60
                        }, 500);                        
                    } else {
                    	$('#review-error').show();

    	                $('html, body').animate({
    	                	scrollTop: $('#review-answer').closest('.panel-body').offset().top - 60
    	                }, 500);
                    }
                }
                
                btn.html( btn.attr('data-old') );
                ajax_is_running = false;
            }, "json"
        );			


        return false;

    } );

	$('#phone-verify-send-form').submit( function(e) {
		e.preventDefault();

		if(ajax_is_running) {
			return;
		}
		ajax_is_running = true;
        
        $('#phone-verify-send-form').find('.alert').hide();

        $.post( 
            $(this).attr('action'), 
            $(this).serialize() , 
            function( data ) {
                if(data.success) {
                    $('#phone-verify-send-form').hide();
                    $('#phone-verify-code-form').show();
                } else {
                    if(data.reason && data.reason=='phone_taken') {
                        $('#phone-taken').show();
                    } else {
                        $('#phone-invalid').show();
                    }
                }
                ajax_is_running = false;
            }, "json"
        );

        return false;

    } );

	$('#phone-verify-code-form').submit( function(e) {
		e.preventDefault();

		if(ajax_is_running) {
			return;
		}
		ajax_is_running = true;

        $.post( 
            $(this).attr('action'), 
            $(this).serialize() , 
            function( data ) {
                if(data.success) {
                	$('.verify-phone').removeClass('verify-phone');
                    $('#phone-verify-code-form').hide();
                    $('#phone-verify-success').show();
                } else {
                    $('#phone-verify-code-form').find('.alert').show();
                }
                ajax_is_running = false;
            }, "json"
        );

        return false;

    } );

	$('#claim-phone-send-form').submit( function(e) {
		e.preventDefault();

		if(ajax_is_running) {
			return;
		}
		ajax_is_running = true;

        $.post( 
            $(this).attr('action'), 
            $(this).serialize() , 
            function( data ) {
                if(data.success) {
                    $('#claim-phone-send-form').hide();
                    $('#claim-phone-code-form').show();
                } else {
                    $('#claim-phone-send-form').find('.alert').show();
                }
                ajax_is_running = false;
            }, "json"
        );

        return false;

    } );

	$('#claim-phone-code-form').submit( function(e) {
		e.preventDefault();

		if(ajax_is_running) {
			return;
		}
		ajax_is_running = true;

        $.post( 
            $(this).attr('action'), 
            $(this).serialize() , 
            function( data ) {
                if(data.success) {
                    $('#claim-phone-code-form').hide();
                    $('#claim-phone-password-form').show();
                    $('#go-to-claim').attr('href', data.link);
                } else {
                    $('#claim-phone-code-form').find('.alert').show();
                }
                ajax_is_running = false;
            }, "json"
        );

        return false;

    } );

	$('#claim-phone-password-form').submit( function(e) {
		e.preventDefault();

		if(ajax_is_running) {
			return;
		}
		ajax_is_running = true;

        $.post( 
            $(this).attr('action'), 
            $(this).serialize() , 
            function( data ) {
                if(data.success) {
                	window.location.href = data.url;
                } else {
                    $('#claim-phone-password-form').find('.alert').show();
                }
                ajax_is_running = false;
            }, "json"
        );

        return false;

    } );

	$('#claim-email-send-form').submit( function(e) {
		e.preventDefault();

		if(ajax_is_running) {
			return;
		}
		ajax_is_running = true;

		$('#claim-email-send-form').find('.alert').hide();
        $.post( 
            $(this).attr('action'), 
            $(this).serialize() , 
            function( data ) {
                if(data.success) {
                    $('#claim-email-send-form').find('.tbh-div').hide();
                    $('#claim-email-send-form').find('.alert.alert-success').show();
                } else {
                    $('#claim-email-send-form').find('.alert.alert-warning').show();
                }
                ajax_is_running = false;
            }, "json"
        );

        return false;

    } );



    $('#review-form .ratings').mousemove( function(e) {
    	var rate = offsetToRate(e.offsetX);
    	$(this).find('.stars .bar').css('width', (rate * 30) + ((rate-1) * 18) );
    } );
    $('#review-form .ratings').click( function(e) {
    	var rate = offsetToRate(e.offsetX);
    	$(this).find('input').val(rate);
    	$(this).closest('.ratings').find('.rating').hide();
    } );
    $('#review-form .ratings').mouseout( function(e) {
    	var rate = parseInt($(this).find('input').val());
    	if(rate) {
    		$(this).find('.stars .bar').css('width', (rate * 30) + ((rate-1) * 18) );
    	} else {
    		$(this).find('.stars .bar').css('width', 0 );
    	}
    } );



    //Video reviews

    $('#review-type-nav a').click( function() {
        $('#review-type-nav li').removeClass('active');
        $(this).parent().addClass('active');
        $('.review-type-content').hide();
        $('#review-option-' + $(this).attr('data-type')).show();
    } );

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
                    maxLength: 600,
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


});

function offsetToRate(offset) {
	return Math.ceil( offset / 48 );
}