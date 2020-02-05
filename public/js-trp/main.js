var slider = null;
var sliderTO = null;
var showPopup = null;
var closePopup = null;
var handlePopups = null;
var ajax_is_running = false;
var switchLogins;
var prepareMapFucntion;
var mapsLoaded = false;
var mapsWaiting = [];
var initMap;
var mapMarkers = {};
var fixFlickty;
var suggestTO;
var refreshOnClosePopup = false;
var onloadCallback;
var map_loaded = false;

var initLoginScripts;
var prepareLoginFucntion;
var loginLoaded = false;
var loginsWaiting = [];
var handleTooltip;
var attachTooltips;
var modernFieldsUpdate;


jQuery(document).ready(function($){

	//To be deleted
	$('.country-select').change( function() {

    	$(this).closest('form').find('input[name="address"]').val('');
    	$(this).closest('form').find('.suggester-map-div').hide();
    	$(this).closest('form').find('.geoip-confirmation').hide();
        $(this).closest('form').find('.geoip-hint').hide();
        $(this).closest('form').find('.different-country-hint').hide();

		if(ajax_is_running) {
			return;
		}
		ajax_is_running = true;

    	var city_select = $(this).closest('form').find('.city-select').first();
    	city_select.attr('disabled', 'disabled');
    	var that = this;
		$.ajax( {
			url: '/cities/' + $(this).val(),
			type: 'GET',
			dataType: 'json',
			success: function( data ) {
				city_select.attr('disabled', false)
			    .find('option')
			    .remove();
    			city_select.append('<option value="">-</option>');
			    for(var i in data.cities) {
    				city_select.append('<option value="'+i+'">'+data.cities[i]+'</option>');
			    }
			    $('.phone-code-holder').html(data.code);
			    ajax_is_running = false;
				//city_select
				//$('#modal-message .modal-body').html(data);
				$(that).trigger('changed');
			},
			error: function(data) {
				console.log(data);
			    ajax_is_running = false;
			}
		});

    });

    modernFieldsUpdate = function() {
	    $('.modern-input').focus( function() {
	    	$(this).closest('.modern-field').addClass('active');
	    	$(this).removeClass('has-error');
	    	$('.ajax-alert[error="'+$(this).attr('name')+'"]').remove();
	    });

	    $('.modern-input').focusout( function() {
	    	if (!$(this).val()) {
	    		$(this).closest('.modern-field').removeClass('active');
	    	}
	    });

	    if ($('.modern-input').length) {
	    	setTimeout( function() {

		    	$('.modern-input').each( function() {
		    		if ($(this).val() || $(this).is(":-webkit-autofill")) {
		    			$(this).closest('.modern-field').addClass('active');
		    		}
		    	});
	    	} , 0)
	    }
    }
    modernFieldsUpdate();

	$('input').focus( function() {
		$(this).removeClass('has-error');
	});

    switchLogins = function(what) {
        if(what=='login') {
            $('#signin-form-popup').hide();
            $('#signin-form-popup-left').hide();
            $('#login-form-popup').show();
            $('#login-form-popup-left').show();
        } else {
            $('#signin-form-popup').show();
            $('#signin-form-popup-left').show();
            $('#login-form-popup').hide();
            $('#login-form-popup-left').hide();
        }
    }

    onloadCallback = function() {
        grecaptcha.render('captcha-div', {
          'sitekey' : '6LfmCmEUAAAAAH20CTYH0Dg6LGOH7Ko7Wv1DZlO0',
          'size' : 'compact'
        });
      };

    var loadCaptchaScript = function() {
    	if (!$('#captcha-script').length) {

		    $('body').append( $('<link rel="stylesheet" type="text/css" href="https://hosted-sip.civic.com/css/civic-modal.min.css" />') );
    		$.getScript('https://hosted-sip.civic.com/js/civic.sip.min.js', function() {
    			$.getScript(window.location.origin+'/js-trp/login.js', function() {
    				initLoginScripts();
    			});
		    	$('body').append( $('<script src="'+window.location.origin+'/js-trp/upload.js"></script>') );
		    	$('body').append( $('<script src="'+window.location.origin+'/js-trp/address.js"></script>') );

	    		$('body').append( $('<script id="captcha-script" src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit" async defer"></script>') );
    		} );
    	}
    }

    var loadMapScript = function() {
    	if (!map_loaded) {

    		$.getScript('https://maps.googleapis.com/maps/api/js?key=AIzaSyCaVeHq_LOhQndssbmw-aDnlMwUG73yCdk&libraries=places&callback=initMap&language=en', function() {

	    		map_loaded = true;
    		} );
    	}
    }

    $('#search-input, .address-suggester').click( function() {
    	loadMapScript();
    });

	showPopup = function(id, e) {
		if(id=='popup-login') {
			loadCaptchaScript();
			id = 'popup-register';
			switchLogins('login');
		} else if(id=='popup-login-dentist') {
			loadCaptchaScript();
			id = 'popup-register';
			switchLogins('login');
			$('.form-wrapper').removeClass('chosen');
			$('.form-button.white-form-button').closest('.form-wrapper').addClass('chosen');
		} else if(id=='popup-register-dentist') {
			loadCaptchaScript();
			id = 'popup-register';
			switchLogins('register');
			$('.form-wrapper').removeClass('chosen');
			$('.form-button.white-form-button').closest('.form-wrapper').addClass('chosen');
		} else if(id=='popup-register') {
			loadCaptchaScript();
			switchLogins('register');
		} else if(id=='map-results-popup') {
			prepareMapFucntion( function() {    
				var search_map = new google.maps.Map(document.getElementById('search-map'), {
					center: {
						lat: parseFloat($('#search-map').attr('lat')), 
						lng: parseFloat($('#search-map').attr('lon'))
					},
					zoom: parseInt($('#search-map').attr('zoom')),
					backgroundColor: 'none'
				});

				mapMarkers = {};
				var bounds = new google.maps.LatLngBounds();
				
				bounds.extend({
					lat: parseFloat($('#search-map').attr('lat')), 
					lng: parseFloat($('#search-map').attr('lon'))
				});

				$('#map-results-popup .result-container[lat]').each( function() {
					if( !$(this).attr('lat') || !$(this).attr('lon') ) {
						return false;
					}
					var did = $(this).attr('dentist-id');
					var LatLon = {
						lat: parseFloat($(this).attr('lat')), 
						lng: parseFloat($(this).attr('lon'))
					};
					mapMarkers[did] = new google.maps.Marker({
						position: LatLon,
						map: search_map,
						icon: images_path+'/map-pin-inactive.png',
						id: did,
					});

					bounds.extend(LatLon);

					google.maps.event.addListener(mapMarkers[did], 'mouseover', (function() {
						$('#map-results-popup .result-container[dentist-id="'+this.id+'"]').addClass('active');
						this.setIcon(images_path+'/map-pin-active.png');
					}).bind(mapMarkers[did]) );
					google.maps.event.addListener(mapMarkers[did], 'mouseout', (function() {
						$('#map-results-popup .result-container[dentist-id="'+this.id+'"]').removeClass('active');
						this.setIcon(images_path+'/map-pin-inactive.png');
					}).bind(mapMarkers[did]) );
					google.maps.event.addListener(mapMarkers[did], 'click', (function() {
						if( $(window).width()<768 ) {
							var container = $('.search-results-wrapper .result-container[full-dentist-id="'+this.id+'"]');
							$('#map-mobile-tooltip').html( container.html() ).attr('href', container.attr('href') ).css('display', 'flex');

							for(var i in mapMarkers) {
								mapMarkers[i].setIcon(images_path+'/map-pin-inactive.png');
							}

							this.setIcon(images_path+'/map-pin-active.png');
						} else {
							var container = $('#map-results-popup .result-container[dentist-id="'+this.id+'"]');
							for(i=0;i<3;i++) {
								container.fadeTo('slow', 0).fadeTo('slow', 1);
							}

							var st = 0;
							var prev = container.prev();
							while(prev.length) {
								st += prev.height() + 10;
								prev = prev.prev();
							}
				            $('#map-results-popup .flex-3').animate({
				                scrollTop: st
				            }, 500);
						}
					}).bind(mapMarkers[did]) );

				} );


				if(!$('#search-map').attr('worldwide')) {
					if( $('#map-results-popup .result-container[lat]').length ) {
						search_map.fitBounds(bounds);
					} else {
						search_map.setZoom(12);
					}
				}

				$('#map-results-popup .result-container').off('mouseover').mouseover( function() {
					var did = $(this).attr('dentist-id');					
					if( mapMarkers[did] ) {
						mapMarkers[did].setIcon(images_path+'/map-pin-active.png');	    								
					}
				} )
				$('#map-results-popup .result-container').off('mouseout').mouseout( function() {
					var did = $(this).attr('dentist-id');					
					if( mapMarkers[did] ) {
						mapMarkers[did].setIcon(images_path+'/map-pin-inactive.png');	    		
					}
				} )

				if(search_map.getZoom() > 16) {
					search_map.setZoom(16);
				}

			} );
		} else if(id=='submit-review-popup') {
			$('.questions-wrapper .question').addClass('hidden');
			if( $(window).width()<768 ) {
				$('.questions-wrapper .question .review-answers .subquestion').addClass('hidden');
			}

			$('.questions-wrapper .question input[type="hidden"]').off('change').change( function() {
				if( $(window).width()<768 ) {
					if( $(this).closest('.subquestion').next().length ) {
						$(this).closest('.subquestion').next().removeClass('hidden');
					}
				}

				var ok = true;
				$(this).closest('.question').find('input[type="hidden"]').each( function() {
					var v = parseInt($(this).val());
					if( !v ) {
						ok = false;
						return false;
					}
				} );

				if(ok) {
					$(this).closest('.question').next().removeClass('hidden');

					if( !$(this).closest('.question').next().next().hasClass('question') || $(this).closest('.question').next().hasClass('skippable') ) {
						$(this).closest('.question').next().next().removeClass('hidden');
					}
				}

	            $('.popup, .popup-inner').animate({
	                scrollTop: $('.questions-wrapper').innerHeight()
	            }, 500);

			} );

			$('.questions-wrapper .question').first().removeClass('hidden');
			$('.questions-wrapper .question').each( function() {
				$(this).find('.review-answers .subquestion').first().removeClass('hidden');
			} )
			
		} else if(id=='popup-share') {
			var url = $(e.target).closest('[share-href]').length ? $(e.target).closest('[share-href]').attr('share-href') : window.location.href;
			$('#share-url').val(url);
			$('#share-address').val(url);
			$('#popup-share .share-buttons').attr('data-href', url);

			$('#popup-share .share-buttons .share').off('click').click( function() {
				var post_url = $(this).closest('.share-buttons').attr('data-href');
				var post_title = $(this).closest('.share-buttons').attr('data-title');
				if ($(this).attr('network')=='fb') {
					var url = 'https://www.facebook.com/dialog/share?app_id=1906201509652855&display=popup&href=' + escape(post_url);
				} else if ($(this).attr('network')=='twt') {
					console.log(post_title);
					var url = 'https://twitter.com/share?url=' + escape(post_url) + '&text=' + escape(post_title);
				}
				window.open( url , 'ShareWindow', 'height=450, width=550, top=' + (jQuery(window).height() / 2 - 275) + ', left=' + (jQuery(window).width() / 2 - 225) + ', toolbar=0, location=0, menubar=0, directories=0, scrollbars=0');
			});

		} else if(id=='popup-wokring-time') {

			if($('#'+id).is('[empty-hours]')) {
				setTimeout( function() {
		        	$('.work-hour-cb').trigger('click');
		        }, 200 );
				
				$('.popup-desc').each( function() {
					$(this).find('select').first().find('option[value="09"]').attr('selected','selected');
					$(this).children('select').eq(1).find('option[value="00"]').attr('selected','selected');
					$(this).children('select').eq(2).find('option[value="18"]').attr('selected','selected');
					$(this).children('select').eq(3).find('option[value="00"]').attr('selected','selected');
				});
			}
			
            if ($('#day-1').is(':checked')) {
                $('.all-days-equal').show();
            } else {
                $('.all-days-equal').hide();
	        }
		} else if(id == 'popup-lead-magnet') {
		    $('#magnet-website').on('keyup keydown', function() {
		        $(this).val($(this).val().toLowerCase());
		    });
		}

		$('.popup').removeClass('active');
		$('#'+id+'.popup').addClass('active');
		handlePopups();
		if ($('.popup.active').length) {
			$('body').addClass('popup-visible');
		}
	}

	closePopup = function() {
		var custom_widget_popup = false;
		if($('#select-reviews-popup').hasClass('active')) {
			custom_widget_popup = true;
		}
		$('.popup').removeClass('active');
		$('body').removeClass('popup-visible');
		if ($('.privacy-policy-cookie').length) {
			$('.privacy-policy-cookie').removeClass('blink');
		}			

		if( refreshOnClosePopup ) {
			window.location.reload();
		}

		if(custom_widget_popup) {
			showPopup( 'popup-widget' );
		}
	}

	handlePopups = function(id) {
		var dataPopupClick = function(e) {
			showPopup( $(this).attr('data-popup'), e );
		}

		var dataPopupClickLogged = function(e) {
			if( user_id ) {
				showPopup( $(this).attr('data-popup-logged'), e );				
			} else {
				showPopup( 'popup-register', e );
				var cta = $('#popup-register .cta');
				cta.show();
				for(i=0;i<3;i++) {
					cta.fadeTo('slow', 0).fadeTo('slow', 1);
				}

			}
		}

		$('[data-popup]').off('click', dataPopupClick).click( dataPopupClick );
		$('[data-popup-logged]').off('click', dataPopupClickLogged).click( dataPopupClickLogged );

		// $('.fixed-popup').css( 'height', $('.fixed-popup .popup-inner').outerHeight() + 100 );
		// $('.fixed-popup').css( 'min-height', $(document).height() );

	}
	handlePopups();

	if(getUrlParameter('popup-loged')) {
		if( user_id ) {
			showPopup( getUrlParameter('popup-loged') );
		} else {
			showPopup( 'popup-register' );
			var cta = $('#popup-register .cta');
			cta.show();
			for(i=0;i<3;i++) {
				cta.fadeTo('slow', 0).fadeTo('slow', 1);
			}
		}
	}
	if(getUrlParameter('popup')) {
		showPopup( getUrlParameter('popup') );
	}

	function fix_header(e){
		if ( ($('header').outerHeight() - 40 < $(window).scrollTop()) ) {
			$('header').addClass('fixed-header');
		} else {
			$('header').removeClass('fixed-header');
		}
	}
	$(window).scroll(fix_header);
	fix_header();

	$('.special-checkbox').change( function() {
		$(this).closest('label').toggleClass('active');
		$(this).closest('label').removeClass('has-error');		
        $('.ajax-alert[error="'+$(this).attr('name')+'"]').remove();
	});

	$('.tab').click( function() {
		$('.tab').removeClass('active');
		$(this).addClass('active');
		$('.tab-container').removeClass('active');
		$('#'+ $(this).attr('data-tab')).addClass('active');
	});

	$('.close-popup').click( function() {
		closePopup();
	});

	$('.popup').click( function(e) {
		if( !$(e.target).closest('.popup-inner').length ) {
			closePopup();
		}
	} );

	$('#share-link-form').submit( function(e) {
        e.preventDefault();
        $(this).find('.alert').hide();

        if(ajax_is_running) {
            return;
        }
        ajax_is_running = true;

        $.post( 
            $(this).attr('action'), 
            $(this).serialize() , 
            (function( data ) {
                if(data.success) {
                	$(this).find('.alert-success').show();
                	$(this).find('[name="email"]').val('').focus();
                } else {
                	$(this).find('.alert-warning').show();
                }
                ajax_is_running = false;
            }).bind(this), "json"
        );          

        return false;

	} );

	$('input[name="mode"]').change( function() {
        $(this).closest('.modern-radios').removeClass('has-error');
    } );

    $('.invite-new-dentist-form .address-suggester').focus(function(e) {
        $('.invite-new-dentist-form .button').addClass('disabled');
    });

	$('.invite-new-dentist-form').submit( function(e) {
        e.preventDefault();

        if (!$(this).find('.button').hasClass('disabled')) {

	        $(this).find('.ajax-alert').remove();
	        $(this).find('.alert').hide();
	        $(this).find('.has-error').removeClass('has-error');

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
	                	closePopup();
	                	$('#inv_dent_name').html(data.dentist_name);
	                	showPopup( 'invite-new-dentist-success-popup' );
	                	that[0].reset();
	                   //$(this).find('.alert-success').html(data.message).show();
	                    gtag('event', 'Invite', {
							'event_category': 'InviteDentist',
							'event_label': 'InvitedDentists',
						});
	                } else {
	                    for(var i in data.messages) {
	                        $('[name="'+i+'"]').closest('.alert-after').after('<div class="alert alert-warning ajax-alert" error="'+i+'">'+data.messages[i]+'</div>');
	                        $('[name="'+i+'"]').addClass('has-error');
	                        if ($('[name="'+i+'"]').closest('.modern-radios').length) {
	                            $('[name="'+i+'"]').closest('.modern-radios').addClass('has-error');
	                        }
	                    }

	                    $('html, body').animate({
			                scrollTop: $('.ajax-alert:visible').first().offset().top - $('header').height() - 150
			            }, 500);
	                }
	                ajax_is_running = false;
	            }).bind(that), "json"
	        );
	        return false;
	    }
	} );

    $('.copy-link').click( function(e) {
    	e.preventDefault();
    	e.stopPropagation();

        var $temp = $("<input>");
        $("body").append($temp);
        $temp.val($(this).closest('.flex').find('input').val()).select();
        document.execCommand("copy");
        $temp.remove();        

        $(this).attr('alternative', $(this).text().trim());
        $(this).html('<i class="fas fa-check-circle"></i>');

        setTimeout( (function() {
        	$(this).html( $(this).attr('alternative').length ? $(this).attr('alternative') : '<i class="far fa-copy"></i>' );
        }).bind(this), 3000 );
    } );

    $('.type-radio').change( function(e) {
		$(this).closest('.mobile-radios').find('label').removeClass('active');
		$(this).closest('label').addClass('active');
	    $('.ajax-alert[error="'+$(this).attr('name')+'"]').remove();
	});

	//
	//Flickty fixes
	//

	fixFlickty = function() {
		$('.flickity-slider').each( function() {
			var mh = 0;
			$(this).find('.slider-wrapper').css('height', 'auto');
			$(this).find('.slider-wrapper').each( function() {
				if( $(this).outerHeight() > mh ) {
					mh = $(this).outerHeight();
				}
			} );
			$(this).find('.slider-wrapper').css('height', mh+'px');
		} );
	}
	$(window).resize( fixFlickty );
	fixFlickty();

	if($(window).width() < 768) {
		$('header .profile-btn').off('click').click( function(e) {
			e.preventDefault();
			$(this).closest('.header-info').find('.expander-wrapper').addClass('active');
		} );
	}

	$('.slider-wrapper [href]').click( function(e) {
		e.stopPropagation();
		e.preventDefault();
		window.location.href = $(this).attr('href');
	} );

	$('.button-sign-up-dentist').click( function() {
		fbq('track', 'DentistInitiateRegistration');
		gtag('event', 'ClickSignup', {
			'event_category': 'DentistRegistration',
			'event_label': 'InitiateDentistRegistration',
		});
	});

	$('.button-sign-up-patient').click( function() {
		fbq('track', 'PatientInitiateRegistration');
		gtag('event', 'ClickSignup', {
			'event_category': 'PatientRegistration',
			'event_label': 'PatientInitiateRegistration',
		});
	});

	$('.button-login-patient').click( function() {
		fbq('track', 'PatientLogin');
		gtag('event', 'ClickLogin', {
			'event_category': 'PatientLogin',
			'event_label': 'LoginPopup',
		});
	});

	handleTooltip = function(e) {

        $('.tooltip-window').html($(this).attr('text'));

        if (window.innerWidth < 768) {
      //   if ($(this).hasClass('fixed-tooltip')) {

	        var that = $(this).closest('.tooltip-text');
	        var y = that.offset().top + that.outerHeight() + 10;
	    	var x = that.offset().left + that.outerWidth() / 2 - $('.tooltip-window').outerWidth() / 2 ;

	        $('.tooltip-window').css('left', x );
	        $('.tooltip-window').css('top', y );
        } else {

        	 $('.tooltip-window').css('left', e.pageX - ($('.tooltip-window').outerWidth() / 2) );

	        if (window.innerWidth > 768) {
		        if (window.innerWidth - $('.tooltip-window').outerWidth() - 20 < e.pageX ) {
		            $('.tooltip-window').css('left', window.innerWidth - $('.tooltip-window').outerWidth() - 20 );
		        }
		    }

	        if (window.innerWidth < 768) {
	        	$('.tooltip-window').css('top', e.pageY + 15 );
	        } else {
	        	$('.tooltip-window').css('top', e.pageY + 30 );
	        }
        }

        $('.tooltip-window').css('display', 'block');

        if ($(this).closest('.tooltip-text').hasClass('info-cookie')) {
        	$('.tooltip-window').addClass('dark-tooltip');
        } else {
        	$('.tooltip-window').removeClass('dark-tooltip');
        }
    }

    attachTooltips = function() {
	    if($('.tooltip-text:not(.tooltip-initted)').length) {

	        $('.tooltip-text:not(.tooltip-initted)').on('mouseover mousemove', function(e) {
	            if (window.innerWidth > 768) {
	                handleTooltip.bind(this)(e);
	            }
	        });

	        $('.tooltip-text:not(.tooltip-initted)').on('click', function(e) {
	            if (window.innerWidth < 768 && !$(this).hasClass('no-mobile-tooltips')) {
	                handleTooltip.bind(this)(e);
	            }
	        });

	        $('.tooltip-text:not(.tooltip-initted)').on('mouseout', function(e) {

	            $('.tooltip-window').hide();
	        });
	        //$('.tooltip-text:not(.tooltip-initted)').addClass('tooltip-initted');
	    }
    }
    attachTooltips();

    var symbolsCount = function() {
        var length = $(this).val().length;

        if (length > parseInt($(this).attr('maxsymb'))) {
            $('.short-descr-error').show();
            $(this).addClass('has-error');
        } else {
            $('.short-descr-error').hide();
            $(this).removeClass('has-error');
        }
    }

    $('textarea[name="short_description"]').keyup(symbolsCount);
    if( $('textarea[name="short_description"]').length ) {
        symbolsCount.bind($('textarea[name="short_description"]'))();
    }

    $('.close-explander').click( function() {
    	$(this).closest('.expander-wrapper').removeClass('active');
    });

	$('.strength-button').click( function() {
		if ($(this).hasClass('active')) {
			$(this).removeClass('active');
			$('body').removeClass('dark');
			$('.strength-parent').removeClass('active');
			// $('.strength-wrapper').css('top', '100%');
			$('.stretching-box').css('height', 0);

			if (Cookies.get('functionality_cookies')) {
				Cookies.set('hide-strength', true, { expires: 1, secure: true });
			}
		} else {
			$(this).addClass('active');
			$('body').addClass('dark');
			$('.strength-parent').addClass('active');
			// $('.strength-wrapper').css('top', 'calc(100% - '+$('.strength-wrapper').outerHeight()+'px)');

			$('.stretching-box').css('height', $('.strength-flickity').outerHeight());
		}
	});

    if ($('.strength-flickity').length) {
    	var $carousel = $('.strength-flickity');

		$carousel.flickity({
	    	wrapAround: true,
			//adaptiveHeight: true,
			draggable: true,
			pageDots: false,
		});
	}

	var showStrength = function() {

		if(($('body').hasClass('page-dentist') || $('body').hasClass('page-index')) && $('.strength-parent').length) {
			
			$('.strength-parent').css('display', 'block');
			$carousel.flickity('resize');

			if(!Cookies.get('hide-strength')) {
				setTimeout( function() {
					$('.strength-button').trigger('click');
				}, 1000);
			}
		}
	}
	showStrength();

	$('.black-overflow').click( function() {
		if ($('.strength-button').length) {
			$('.strength-button').removeClass('active');
			$('.strength-parent').removeClass('active');
			// $('.strength-wrapper').css('top', '100%');
			$('.stretching-box').css('height', 0);
		}		
	});

	$('[event_category]').click( function() {
		gtag('event', $(this).attr('event_action'), {
            'event_category': $(this).attr('event_category'),
            'event_label': $(this).attr('event_label'),
        });
	});

	$('.str-invite').click( function() {
		$('.strength-button.active').trigger('click');
		showPopup('popup-invite');
	});

	$('.str-edit').click( function() {
		$('body, html').animate({
            scrollTop: 0
        }, 500);
		$('.strength-button.active').trigger('click');
		$('.open-edit:visible').trigger('click');
	});

	$('.str-description').click( function() {
		$('body, html').animate({
            scrollTop: $('.profile-tabs').offset().top
        }, 500);
		$('.strength-button.active').trigger('click');
		$('[data-tab="about"]').trigger('click');
		$('.about-content[role="presenter"] a').trigger('click');
		$('#dentist-description').css('box-shadow', '0px 0px 14px 2px #F44336');
	});

	$('.str-socials').click( function() {
		$('body, html').animate({
            scrollTop: 0
        }, 500);
		$('.strength-button.active').trigger('click');
		$('.open-edit:visible').trigger('click');
		$('.social-wrap:visible').css('box-shadow', '0px 0px 13px -1px #F44336');
	});

	$('.str-team').click( function() {
		$('.strength-button.active').trigger('click');
		showPopup('add-team-popup');
	});

	$('.str-photos').click( function() {
		$('body, html').animate({
            scrollTop: $('.profile-tabs').offset().top
        }, 500);
		$('.strength-button.active').trigger('click');
		$('[data-tab="about"]').trigger('click');
	});

	$('.str-working-hours').click( function() {
		$('.strength-button.active').trigger('click');
		showPopup('popup-wokring-time');
	});

	$('.str-widget').click( function() {
		$('.strength-button.active').trigger('click');
		showPopup('popup-widget');
	});

	$('.str-see-reviews').click( function() {
		$('.strength-button.active').trigger('click');
		$('[data-tab="reviews"]').trigger('click');
		$('body, html').animate({
            scrollTop: $('.review-wrapper').first().offset().top - 200
        }, 500);
	});

    $('#claim-profile-form').submit( function(e) {
        e.preventDefault();

        if(ajax_is_running) {
            return;
        }
        ajax_is_running = true;

        $(this).find('.ajax-alert').remove();
        $(this).find('.alert').hide();
        $(this).find('.has-error').removeClass('has-error');

        var formData = new FormData(this);

        $.ajax({
	        url: $(this).attr('action'),
	        type: 'POST',
	        data: formData,
	        cache: false,
	        contentType: false,
	        processData: false
	    }).done( (function (data) {
			if(data.success) {
				$('#claim-popup').addClass('claimed');
			} else {
				console.log(data);
				for(var i in data.messages) {
                    $(this).find('[name="'+i+'"]').closest('.alert-after').after('<div class="alert alert-warning ajax-alert">'+data.messages[i]+'</div>');

                    $(this).find('[name="'+i+'"]').addClass('has-error');

                    if ($(this).find('[name="'+i+'"]').closest('.modern-file').length) {
                        $(this).find('[name="'+i+'"]').closest('.modern-file').addClass('has-error');
                    }
                }
                $('.popup').animate({
	                scrollTop: $('.ajax-alert:visible').first().offset().top
	            }, 500);
			}
            ajax_is_running = false;

	    }).bind(this) ).fail(function (data) {
			$(this).find('.alert-warning').show();
	    });

	    return;
    } );

    $('.claimed-ok').click( function() {
		closePopup();
	});

	$('.country-dropdown').change( function() {

		if ($(this).attr('real-country') != '') {
			if ($(this).val() != $(this).attr('real-country')) {
				$(this).parent().parent().find('.ip-country').show();
			} else {
				$(this).parent().parent().find('.ip-country').hide();
			}
		}
	});

	$('.dentist-name-register').on('keyup keypress', function() {

		if($(this).val() && !$(this).val().match(/^[\w\d\s\+\'\&.,-]*$/)) {
			$('#alert-name-dentist').show();
			$('.tooltip-window').hide();
		} else {
			$('#alert-name-dentist').hide();
		}
	});

	$('.get-started-button').click( function() {
		showPopup( 'popup-register-dentist' );
	});

	

	$('.lead-magnet-form-step2').submit( function(e) {
        e.preventDefault();

        if(ajax_is_running) {
            return;
        }

        ajax_is_running = true;

        var that = $(this);
        $('.loader').fadeIn();
        $('.loader-mask').fadeIn();
        $('.loader-text').fadeIn();
        //$('#magnet-submit').append('<div class="loader"><i class="fas fa-circle-notch fa-spin fa-3x fa-fw"></i></div>');

        $.post( 
            $(this).attr('action'), 
            $(this).serialize() , 
            function( data ) {
                if(data.success) {

                	// var ans_3 = '';

                 //    $('.lead-magnet-checkbox[name="answer-3[]"]:checked').each( function() {
                 //        ans_3 += $(this).attr('ans-text')+'|';
                 //    });

                 //    ans_3.slice(0,-1);

                 //    _aaq.push(['setContactFields', {
                 //        firstname:$("#magnet-name").val(),
                 //        website:$("#magnet-website").val(),
                 //        email:$("#magnet-email").val(),
                 //        country:$('#magnet-country option:selected').text(),
                 //        priority:$('.lead-magnet-radio[name="answer-1"]:checked').attr('ans-text'),
                 //        reviews_tool:$('.lead-magnet-radio[name="answer-2"]:checked').attr('ans-text'),
                 //        ask_reviews:ans_3,
                 //        frequently_reviews: $('.lead-magnet-radio[name="answer-4"]:checked').length ? $('.lead-magnet-radio[name="answer-4"]:checked').attr('ans-text') : '',
                 //        reviews_reply:$('.lead-magnet-radio[name="answer-5"]:checked').attr('ans-text'),
                 //    }]);
                 //    _aaq.push(['rememberConsentGiven', false, 2]);
                 //    _aaq.push(['trackPageView']);

                    console.log('push', _aaq);

                    fbq('track', 'TRPMagnetComplete');

                    gtag('event', 'SeeScore', {
                        'event_category': 'LeadMagnet',
                        'event_label': 'ReplyToReviews',
                    });

                    setTimeout( function() {
                        window.location.href = data.url;
                    }, 8000);
                } else {
                	console.log('error');
                }
                ajax_is_running = false;
            }, "json"
        );

        return false;
    } );

    $('.lead-magnet-radio').change( function() {
    	$(this).closest('.answer-radios-magnet').find('label').removeClass('active');
    	$(this).closest('label').addClass('active');
    });

    $('.lead-magnet-checkbox').change( function() {
    	$(this).closest('label').toggleClass('active');

        if ($(this).hasClass('disabler')) {
            if ($(this).prop('checked')) {

                $(this).closest('.buttons-list').find('.lead-magnet-checkbox').not(this).prop('disabled', true);
                $(this).closest('.buttons-list').find('.lead-magnet-checkbox').not(this).prop('checked', false);
                $(this).closest('.buttons-list').find('.magnet-label:not(.disabler-label)').addClass('disabled-label');
                $(this).closest('.buttons-list').find('.magnet-label:not(.disabler-label)').removeClass('active');
            } else {
                $(this).closest('.buttons-list').find('.lead-magnet-checkbox').not(this).prop('disabled', false);
                $(this).closest('.buttons-list').find('.magnet-label:not(.disabler-label)').removeClass('disabled-label');
            }
        }
    });

    $('.lead-magnet-radio').click( function() {
        if ($(this).attr('name') == 'answer-1') {
            gtag('event', 'Next', {
                'event_category': 'LeadMagnet',
                'event_label': 'Priority',
            });
        } else if ($(this).attr('name') == 'answer-2') {
            gtag('event', 'Next', {
                'event_category': 'LeadMagnet',
                'event_label': 'Tool',
            });
        } else if ($(this).attr('name') == 'answer-4') {
            gtag('event', 'Next', {
                'event_category': 'LeadMagnet',
                'event_label': 'Frequency',
            });
        }

        if ($(this).attr('name') == 'answer-5') {
            $(this).closest('form').find('button').trigger('click');
        } else {

            $('.flickity-magnet').flickity('next');
        }

    });

    $('.magnet-validator').click( function() {
    		
	    if($(this).closest('.answer-radios-magnet').find('input:checked').length) {
            gtag('event', 'Next', {
                'event_category': 'LeadMagnet',
                'event_label': 'AskForReviews',
            });

	    	if ($(this).hasClass('validator-skip')) {
	    		if ($(this).closest('.answer-radios-magnet').find('input:checked').val() == '4') {
	    			$('.flickity-magnet').flickity( 'select', 4 );
	    		} else {
	    			$('.flickity-magnet').flickity('next');
	    		}
	    	} else {
	    		$('.flickity-magnet').flickity('next');
	    	}    		
    	} else {
    		$(this).closest('.flickity-viewport').css('height', $(this).closest('.flickity-viewport').height() + 76);
    		$(this).closest('.answer-radios-magnet').find('.alert-warning').show();
    	}

    });

    $('#open-magnet').click( function() {
        gtag('event', 'Open', {
            'event_category': 'LeadMagnet',
            'event_label': 'Popup',
        });
    });

    $('.first-form-button').click( function(e) {
        e.preventDefault();

        if(ajax_is_running) {
            return;
        }
        ajax_is_running = true;
        var that = $(this);
        $.post( 
            $(this).attr('data-validator'), 
            $('#lead-magnet-form-step2').serialize(), 
            function( data ) {
                if(data.success) {

                    if (!Cookies.get('marketing_cookies') && !$('#ariticform_wrapper_leadmagnetform').length) {
                        basic.cookies.set('marketing_cookies', 1);

                        $('body').append("<script>\
                            (function(w,d,t,u,n,a,m){\
                                if(typeof w['AriticTrackingObject'] !== 'undefined') return;w['AriticTrackingObject']=n;\
                                w[n]=w[n]||function(){(w[n].q=w[n].q||[]).push(arguments)},a=d.createElement(t),\
                                m=d.getElementsByTagName(t)[0];a.async=1;a.src=u;m.parentNode.insertBefore(a,m)\
                            })(window,document,'script','https://dentacoin.ariticapp.com/ma/atc.js','at');\
                        </script>");

                        $.getScript('https://dentacoin.ariticapp.com/ma/patc.js', function() {
                            console.log('patc')
                        } );

                        $('body').append('<script type="text/javascript">\
                            function LeadMagenet() {\
                                setTimeout( function() {\
                                    _aaq.push(["setContactFields", {\
                                        firstname:document.getElementById("magnet-name").value,\
                                        website:document.getElementById("magnet-website").value,\
                                        email:document.getElementById("magnet-email").value,\
                                    }]);\
                                    _aaq.push("rememberConsentGiven", false, 3);\
                                    _aaq.push(["trackPageView"]);\
                                }, 5000);\
                            }\
                        </script>');

                        $('head').append("<script type='text/javascript'>\
                            if (typeof AriticSDKLoaded == 'undefined') {\
                                var AriticSDKLoaded = true;\
                                var head            = document.getElementsByTagName('head')[0];\
                                var script          = document.createElement('script');\
                                script.type         = 'text/javascript';\
                                script.src          = 'https://ariticpinpoint.dentacoin.com/ma/media/js/aritic-form.min.js';\
                                script.onload       = function() {\
                                    AriticSDK.onLoad();\
                                };\
                                head.appendChild(script);\
                                var AriticDomain = 'https://ariticpinpoint.dentacoin.com/ma';\
                                var AriticLang   = {\
                                    'submittingMessage': 'Please wait...'\
                                }\
                            }\
                        </script>\
                        ");

                        $('body').append('<style type="text/css"> body{font-size: 13px; line-height: 1.3856}audio, canvas, img, svg, video{max-width: 100%; height: auto; box-sizing: border-box}.ariticform_wrapper{max-width: 100%}.ariticform-innerform{width: 100%}.ariticform-name{font-weight: 700; font-size: 1.5em; margin-bottom: 3px}.ariticform-description{margin-top: 2px; margin-bottom: 10px}.ariticform-error{margin-bottom: 10px; color: red}.ariticform-message{margin-bottom: 10px; color: green}.ariticform-row{display: block; padding: 10px}.ariticform-label{font-size: 1.1em; display: block; margin-bottom: 5px}.ariticform-row.ariticform-required .ariticform-label:after{color: #e32; content: " *"; display: inline}.ariticform-helpmessage{display: block; font-size: .9em; margin-bottom: 3px}.ariticform-errormsg{display: block; color: red; margin-top: 2px}.ariticform-input, .ariticform-selectbox, .ariticform-textarea{color: #000; width: 100%; padding: .5em .5em; border: 1px solid #ccc; background: #fff; box-shadow: 0 0 0 #fff inset; border-radius: 4px; box-sizing: border-box}.ariticform-checkboxgrp-label{font-weight: 400}.ariticform-radiogrp-label{font-weight: 400}.ariticform-pagebreak.btn-default{color: #5d6c7c; background-color: #fff}.ariticform-button, .ariticform-pagebreak{display: inline-block; margin-bottom: 0; font-weight: 600; text-align: center; vertical-align: middle; cursor: pointer; background-image: none; border: 1px solid transparent; white-space: nowrap; padding: 6px 12px; font-size: 13px; line-height: 1.3856; border-radius: 3px; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none; user-select: none}.ariticform-pagebreak-wrapper .ariticform-button-wrapper{display: inline}.ariticform_wrapper{margin: 0 auto; display: -ms-flexbox; display: -webkit-flex; display: flex; -ms-flex-wrap: wrap; -webkit-flex-wrap: wrap}.ariticform-page-wrapper{width: 100%; display: -ms-flexbox; display: -webkit-flex; display: flex; -ms-flex-wrap: wrap; -webkit-flex-wrap: wrap}.ariticform-row{float: left; box-sizing: border-box; width: 100%}.ariticform-col-1-2{width: 50%}.ariticform-col-1-3{width: 33.3%}.ariticform_wrapper form{width: 100%}.ariticform-aligncenter{text-align: center}.ariticform-alignleft{text-align: left}.ariticform-alignright{text-align: right}.ariticform_wrapper .ariticform-single-col .ariticform-label{width: 30%; float: left}.ariticform_wrapper .ariticform-single-col .ariticform-checkboxgrp-input, .ariticform_wrapper .ariticform-single-col .ariticform-input, .ariticform_wrapper .ariticform-single-col .ariticform-radiogrp-input, .ariticform_wrapper .ariticform-single-col .ariticform-textarea{width: 70%; float: left}.ariticform_wrapper .ariticform-single-col .ariticform-checkboxgrp-input.ariticform-withoutlabel, .ariticform_wrapper .ariticform-single-col .ariticform-input.ariticform-withoutlabel, .ariticform_wrapper .ariticform-single-col .ariticform-radiogrp-input.ariticform-withoutlabel, .ariticform_wrapper .ariticform-single-col .ariticform-textarea.ariticform-withoutlabel{width: 100%}.ariticform-innerform{display: -ms-flexbox; display: -webkit-flex; display: flex; -ms-flex-wrap: wrap; -webkit-flex-wrap: wrap; flex-wrap: wrap}.ariticform_wrapper{width: 720px}.ariticform-label-left, .ariticform-label-right{width: 150px}.ariticform-label{white-space: normal}.ariticform-label-left{display: inline-block; white-space: normal; float: left; text-align: left}.ariticform-label-right{display: inline-block; white-space: normal; float: left; text-align: right}.ariticform-label-top{white-space: normal; display: block; float: none; text-align: left}.form-radio-item label:before{top: 0}.form-all{font-size: 16px}.ariticform-label{font-weight: 700}.form-checkbox-item label, .form-radio-item label{font-weight: 400}.ariticform_wrapper{background-color: #fff}.ariticform_wrapper{color: #555}.ariticform-label, label{font-size: 12}.ariticform-label, label{color: #6f6f6f}.ariticform-label{color: #555}.ariticform-label, label{color: #6f6f6f}.form-all{width: 720px}.ariticform_wrapper{padding: 0}.ariticform_wrapper{border-radius: unset}.ariticform-freehtml, .ariticform-label, .ariticform-row, .ariticform_wrapper{font-family: Arial, Helvetica, sans-serif}.ariticform-input, .ariticform-textarea{background-color: #fff}.ariticform-button{background-color: #fff; color: #000; border-color: #fff}.ariticform-button-wrapper{text-align: left}.ariticform-button{text-transform: uppercase; border-radius: unset}.ariticform-button{border-color: #000}.ariticform-input, .ariticform-textarea{border-radius: unset}.ariticform_wrapper{box-sizing: border-box;}</style><div id="ariticform_wrapper_leadmagnetform" class="ariticform_wrapper"> <form autocomplete="false" role="form" method="post" action="https://ariticpinpoint.dentacoin.com/ma/form/submit?formId=13" id="ariticform_leadmagnetform" data-aritic-form="leadmagnetform" data-aritic-id="13" enctype="multipart/form-data"> <div class="ariticform-error" id="ariticform_leadmagnetform_error"></div><div class="ariticform-message" id="ariticform_leadmagnetform_message"></div><div class="ariticform-innerform"> <div class="ariticform-page-wrapper ariticform-page-1" data-aritic-form-page="1"> <div id="ariticform_leadmagnetform_practice_name" data-validate="practice_name" data-validation-type="firstname" class="ariticform-row ariticform-text ariticform-field-1 ariticform-col-1-1 ariticform-double-col ariticform-required"> <label id="ariticform_label_leadmagnetform_practice_name" for="ariticform_input_leadmagnetform_practice_name" class="ariticform-label">Practice name</label> <input id="ariticform_input_leadmagnetform_practice_name" name="ariticform[practice_name]" value="" class="ariticform-input" type="text"> <span class="ariticform-errormsg" style="display: none;">This is required.</span> </div><div id="ariticform_leadmagnetform_website" data-validate="website" data-validation-type="text" class="ariticform-row ariticform-text ariticform-field-2 ariticform-col-1-1 ariticform-double-col ariticform-required"> <label id="ariticform_label_leadmagnetform_website" for="ariticform_input_leadmagnetform_website" class="ariticform-label">Website</label> <input id="ariticform_input_leadmagnetform_website" name="ariticform[website]" value="" class="ariticform-input" type="text"> <span class="ariticform-errormsg" style="display: none;">This is required.</span> </div><div id="ariticform_leadmagnetform_country" class="ariticform-row ariticform-select ariticform-field-3 ariticform-col-1-1 ariticform-double-col"> <label id="ariticform_label_leadmagnetform_country" for="ariticform_input_leadmagnetform_country" class="ariticform-label">Country</label> <select id="ariticform_input_leadmagnetform_country" name="ariticform[country]" value="" class="ariticform-selectbox"> <option value=""></option> <option value="Afghanistan">Afghanistan</option> <option value="Åland Islands">Åland Islands</option> <option value="Albania">Albania</option> <option value="Algeria">Algeria</option> <option value="Andorra">Andorra</option> <option value="Angola">Angola</option> <option value="Anguilla">Anguilla</option> <option value="Antarctica">Antarctica</option> <option value="Antigua and Barbuda">Antigua and Barbuda</option> <option value="Argentina">Argentina</option> <option value="Armenia">Armenia</option> <option value="Aruba">Aruba</option> <option value="Australia">Australia</option> <option value="Austria">Austria</option> <option value="Azerbaijan">Azerbaijan</option> <option value="Bahamas">Bahamas</option> <option value="Bahrain">Bahrain</option> <option value="Bangladesh">Bangladesh</option> <option value="Barbados">Barbados</option> <option value="Belarus">Belarus</option> <option value="Belgium">Belgium</option> <option value="Belize">Belize</option> <option value="Benin">Benin</option> <option value="Bermuda">Bermuda</option> <option value="Bhutan">Bhutan</option> <option value="Bolivia">Bolivia</option> <option value="Bonaire, Saint Eustatius and Saba">Bonaire, Saint Eustatius and Saba</option> <option value="Bosnia and Herzegovina">Bosnia and Herzegovina</option> <option value="Botswana">Botswana</option> <option value="Bouvet Island">Bouvet Island</option> <option value="Brazil">Brazil</option> <option value="Brunei">Brunei</option> <option value="Bulgaria">Bulgaria</option> <option value="Burkina Faso">Burkina Faso</option> <option value="Burundi">Burundi</option> <option value="Cape Verde">Cape Verde</option> <option value="Cambodia">Cambodia</option> <option value="Cameroon">Cameroon</option> <option value="Canada">Canada</option> <option value="Cayman Islands">Cayman Islands</option> <option value="Central African Republic">Central African Republic</option> <option value="Chad">Chad</option> <option value="Chile">Chile</option> <option value="China">China</option> <option value="Colombia">Colombia</option> <option value="Comoros">Comoros</option> <option value="Cook Islands">Cook Islands</option> <option value="Costa Rica">Costa Rica</option> <option value="Croatia">Croatia</option> <option value="Cuba">Cuba</option> <option value="Cyprus">Cyprus</option> <option value="Czech Republic">Czech Republic</option> <option value="Denmark">Denmark</option> <option value="Djibouti">Djibouti</option> <option value="Dominica">Dominica</option> <option value="Dominican Republic">Dominican Republic</option> <option value="Democratic Republic of the Congo">Democratic Republic of the Congo</option> <option value="East Timor">East Timor</option> <option value="Ecuador">Ecuador</option> <option value="Egypt">Egypt</option> <option value="El Salvador">El Salvador</option> <option value="Equatorial Guinea">Equatorial Guinea</option> <option value="Eritrea">Eritrea</option> <option value="Estonia">Estonia</option> <option value="Ethiopia">Ethiopia</option> <option value="Falkland Islands">Falkland Islands</option> <option value="Fiji">Fiji</option> <option value="Finland">Finland</option> <option value="France">France</option> <option value="French Guiana">French Guiana</option> <option value="French Polynesia">French Polynesia</option> <option value="Gabon">Gabon</option> <option value="Gambia">Gambia</option> <option value="Georgia">Georgia</option> <option value="Germany">Germany</option> <option value="Ghana">Ghana</option> <option value="Gibraltar">Gibraltar</option> <option value="Greece">Greece</option> <option value="Greenland">Greenland</option> <option value="Grenada">Grenada</option> <option value="Guadeloupe">Guadeloupe</option> <option value="Guam">Guam</option> <option value="Guatemala">Guatemala</option> <option value="Guernsey">Guernsey</option> <option value="Guinea">Guinea</option> <option value="Guinea Bissau">Guinea Bissau</option> <option value="Guyana">Guyana</option> <option value="Haiti">Haiti</option> <option value="Heard Island and McDonald Islands">Heard Island and McDonald Islands</option> <option value="Holy See">Holy See</option> <option value="Honduras">Honduras</option> <option value="Hong Kong">Hong Kong</option> <option value="Hungary">Hungary</option> <option value="Iceland">Iceland</option> <option value="India">India</option> <option value="Indonesia">Indonesia</option> <option value="Iran">Iran</option> <option value="Iraq">Iraq</option> <option value="Ireland">Ireland</option> <option value="Israel">Israel</option> <option value="Italy">Italy</option> <option value="Ivory Coast">Ivory Coast</option> <option value="Jamaica">Jamaica</option> <option value="Japan">Japan</option> <option value="Jersey">Jersey</option> <option value="Jordan">Jordan</option> <option value="Kazakhstan">Kazakhstan</option> <option value="Kenya">Kenya</option> <option value="Kiribati">Kiribati</option> <option value="Kuwait">Kuwait</option> <option value="Kyrgyzstan">Kyrgyzstan</option> <option value="Laos">Laos</option> <option value="Latvia">Latvia</option> <option value="Lebanon">Lebanon</option> <option value="Lesotho">Lesotho</option> <option value="Liberia">Liberia</option> <option value="Libya">Libya</option> <option value="Liechtenstein">Liechtenstein</option> <option value="Lithuania">Lithuania</option> <option value="Luxembourg">Luxembourg</option> <option value="Macao">Macao</option> <option value="Macedonia">Macedonia</option> <option value="Madagascar">Madagascar</option> <option value="Malawi">Malawi</option> <option value="Malaysia">Malaysia</option> <option value="Maldives">Maldives</option> <option value="Mali">Mali</option> <option value="Malta">Malta</option> <option value="Marshall Islands">Marshall Islands</option> <option value="Martinique">Martinique</option> <option value="Mauritania">Mauritania</option> <option value="Mauritius">Mauritius</option> <option value="Mayotte">Mayotte</option> <option value="Mexico">Mexico</option> <option value="Micronesia">Micronesia</option> <option value="Moldova">Moldova</option> <option value="Monaco">Monaco</option> <option value="Mongolia">Mongolia</option> <option value="Montenegro">Montenegro</option> <option value="Montserrat">Montserrat</option> <option value="Morocco">Morocco</option> <option value="Mozambique">Mozambique</option> <option value="Myanmar">Myanmar</option> <option value="Namibia">Namibia</option> <option value="Nauru">Nauru</option> <option value="Nepal">Nepal</option> <option value="Netherlands">Netherlands</option> <option value="New Caledonia">New Caledonia</option> <option value="New Zealand">New Zealand</option> <option value="Nicaragua">Nicaragua</option> <option value="Niger">Niger</option> <option value="Nigeria">Nigeria</option> <option value="Niue">Niue</option> <option value="North Korea">North Korea</option> <option value="Northern Mariana Islands">Northern Mariana Islands</option> <option value="Norway">Norway</option> <option value="Oman">Oman</option> <option value="Pakistan">Pakistan</option> <option value="Palau">Palau</option> <option value="Palestine">Palestine</option> <option value="Panama">Panama</option> <option value="Papua New Guinea">Papua New Guinea</option> <option value="Paraguay">Paraguay</option> <option value="Peru">Peru</option> <option value="Philippines">Philippines</option> <option value="Pitcairn">Pitcairn</option> <option value="Poland">Poland</option> <option value="Portugal">Portugal</option> <option value="Puerto Rico">Puerto Rico</option> <option value="Qatar">Qatar</option> <option value="Republic of the Congo">Republic of the Congo</option> <option value="Réunion">Réunion</option> <option value="Romania">Romania</option> <option value="Russia">Russia</option> <option value="Rwanda">Rwanda</option> <option value="Saint Barthelemy">Saint Barthelemy</option> <option value="Saint Helena, Ascension and Tristan da Cunha">Saint Helena, Ascension and Tristan da Cunha</option> <option value="Saint Kitts and Nevis">Saint Kitts and Nevis</option> <option value="Saint Lucia">Saint Lucia</option> <option value="Saint Martin">Saint Martin</option> <option value="Saint Pierre and Miquelon">Saint Pierre and Miquelon</option> <option value="Saint Vincent and the Grenadines">Saint Vincent and the Grenadines</option> <option value="Samoa">Samoa</option> <option value="San Marino">San Marino</option> <option value="Sao Tome and Principe">Sao Tome and Principe</option> <option value="Saudi Arabia">Saudi Arabia</option> <option value="Senegal">Senegal</option> <option value="Serbia">Serbia</option> <option value="Seychelles">Seychelles</option> <option value="Sierra Leone">Sierra Leone</option> <option value="Singapore">Singapore</option> <option value="Slovakia">Slovakia</option> <option value="Slovenia">Slovenia</option> <option value="Solomon Islands">Solomon Islands</option> <option value="Somalia">Somalia</option> <option value="South Africa">South Africa</option> <option value="South Georgia and the South Sandwich Islands">South Georgia and the South Sandwich Islands</option> <option value="South Korea">South Korea</option> <option value="South Sudan">South Sudan</option> <option value="Spain">Spain</option> <option value="Sri Lanka">Sri Lanka</option> <option value="Svalbard and Jan Mayen">Svalbard and Jan Mayen</option> <option value="Sudan">Sudan</option> <option value="Suriname">Suriname</option> <option value="Swaziland">Swaziland</option> <option value="Sweden">Sweden</option> <option value="Switzerland">Switzerland</option> <option value="Syria">Syria</option> <option value="Tahiti">Tahiti</option> <option value="Taiwan">Taiwan</option> <option value="Tajikistan">Tajikistan</option> <option value="Tanzania">Tanzania</option> <option value="Thailand">Thailand</option> <option value="Togo">Togo</option> <option value="Tokelau">Tokelau</option> <option value="Tonga">Tonga</option> <option value="Trinidad and Tobago">Trinidad and Tobago</option> <option value="Tunisia">Tunisia</option> <option value="Turkey">Turkey</option> <option value="Turkmenistan">Turkmenistan</option> <option value="Turks and Caicos Islands">Turks and Caicos Islands</option> <option value="Tuvalu">Tuvalu</option> <option value="United Kingdom">United Kingdom</option> <option value="United States">United States</option> <option value="Unknown">Unknown</option> <option value="Uganda">Uganda</option> <option value="Ukraine">Ukraine</option> <option value="United Arab Emirates">United Arab Emirates</option> <option value="Uruguay">Uruguay</option> <option value="Uzbekistan">Uzbekistan</option> <option value="Vanuatu">Vanuatu</option> <option value="Venezuela">Venezuela</option> <option value="Vietnam">Vietnam</option> <option value="Virgin Islands (British)">Virgin Islands (British)</option> <option value="Virgin Islands (U.S.)">Virgin Islands (U.S.)</option> <option value="Wallis and Futuna">Wallis and Futuna</option> <option value="Western Sahara">Western Sahara</option> <option value="Yemen">Yemen</option> <option value="Yugoslavia">Yugoslavia</option> <option value="Zambia">Zambia</option> <option value="Zimbabwe">Zimbabwe</option> </select> <span class="ariticform-errormsg" style="display: none;"></span> </div><div id="ariticform_leadmagnetform_email" data-validate="email" data-validation-type="email" class="ariticform-row ariticform-email ariticform-field-4 ariticform-col-1-1 ariticform-double-col ariticform-required"> <label id="ariticform_label_leadmagnetform_email" for="ariticform_input_leadmagnetform_email" class="ariticform-label">Email</label> <input id="ariticform_input_leadmagnetform_email" name="ariticform[email]" value="" class="ariticform-input" type="email"> <span class="ariticform-errormsg" style="display: none;">This is required.</span> </div><div id="ariticform_leadmagnetform_gdpr_checkbox" data-validate="gdpr_checkbox" data-validation-type="checkboxgrp" class="ariticform-row ariticform-checkboxgrp ariticform-field-5 ariticform-col-1-1 ariticform-double-col ariticform-required"> <div class="ariticform-checkboxgrp-input"> <label id="ariticform_checkboxgrp_label_gdpr_checkbox" for="ariticform_checkboxgrp_checkbox_gdpr_checkbox" style="width:100%;"> <input name="ariticform[gdpr_checkbox][]" id="ariticform_checkboxgrp_checkbox_gdpr_checkbox" type="checkbox" value="2" style="float: left;margin-right: 10px;"> <p>By submitting the form, you agree to our <a href="https://dentacoin.com/privacy-policy">PrivacyPolicy</a>.</p></label> </div><span class="ariticform-errormsg" style="display: none;">This is required.</span> </div><div id="ariticform_leadmagnetform_submit" class="ariticform-row ariticform-button-wrapper ariticform-field-6 ariticform-col-1-1 ariticform-single-col"> <button type="submit" name="ariticform[submit]" id="ariticform_input_leadmagnetform_submit" value="" class="ariticform-button btn btn-default">Submit</button> </div></div></div><input type="hidden" name="ariticform[formId]" id="ariticform_leadmagnetform_id" value="13"> <input type="hidden" name="ariticform[return]" id="ariticform_leadmagnetform_return" value=""> <input type="hidden" name="ariticform[formName]" id="ariticform_leadmagnetform_name" value="leadmagnetform"> </form></div>');

                        // $.getScript('//dentacoin.ariticapp.com/ma/form/generate.js?id=13', function() {
                        //     console.log('gdpr')
                        // } );
                    }


                    if (!Cookies.get('performance_cookies')) {
                        basic.cookies.set('performance_cookies', 1);
                    }
                    if (!Cookies.get('functionality_cookies')) {
                        basic.cookies.set('functionality_cookies', 1);
                    }
                    if (!Cookies.get('strictly_necessary_policy')) {
                        basic.cookies.set('strictly_necessary_policy', 1);
                    }

                    if ($('.privacy-policy-cookie').length) {
                        $('.privacy-policy-cookie').hide();
                    }

                    that.closest('.magnet-content').next().show();
                    that.closest('.magnet-content').hide();

                    that.closest('.popup-inner').find('.colorful-tabs').find('.col').removeClass('active');
                    that.closest('.popup-inner').find('.colorful-tabs').find('.second-step').addClass('active');

                    var $carousel = $('.flickity-magnet');

                    $carousel.flickity({
                        //wrapAround: true,
                        adaptiveHeight: true,
                        draggable: false,
                        pageDots: true,
                    });

                    fbq('track', 'TRPMagnetStart');

                    gtag('event', 'RunTest', {
                        'event_category': 'LeadMagnet',
                        'event_label': 'ContactDetails',
                    });

                    setTimeout( function() {
                        
                        $('#ariticform_input_leadmagnetform_practice_name').val( $('#magnet-name').val() );
                        $('#ariticform_input_leadmagnetform_website').val( $('#magnet-website').val() );
                        $('#ariticform_input_leadmagnetform_country').val( $('#magnet-country option:selected').text() );
                        $('#ariticform_input_leadmagnetform_email').val( $('#magnet-email').val() );
                        $('#ariticform_checkboxgrp_checkbox_gdpr_checkbox').prop('checked', true);

                        $('#ariticform_input_leadmagnetform_submit').trigger('click');
                    }, 2000);


                } else {
                    that.closest('form').find('.ajax-alert').remove();
                    for(var i in data.messages) {
                        that.closest('form').find('[name="'+i+'"]').addClass('has-error');
                        that.closest('form').find('[name="'+i+'"]').closest('.alert-after').after('<div class="alert alert-warning ajax-alert" error="'+i+'">'+data.messages[i]+'</div>'); 

                        if (that.closest('form').find('[name="'+i+'"]').closest('.agree-label').length) {
                            that.closest('form').find('[name="'+i+'"]').closest('.agree-label').addClass('has-error');
                        }  
                    }
                }
                ajax_is_running = false;
            }, 
            "json"
        );

    } );

});

//
//Logins function
//

prepareLoginFucntion = function( callback ) {

    if(loginLoaded) {
        callback();
    } else {
        loginsWaiting.push(callback);
    }
}

initLoginScripts = function () {
    loginLoaded = true;
    for(var i in loginsWaiting) {
        setTimeout(loginsWaiting[i]);
    }
}

//
//Maps stuff
//

prepareMapFucntion = function( callback ) {
    if(mapsLoaded) {
        callback();
    } else {
        mapsWaiting.push(callback);
    }
}

initMap = function () {
    mapsLoaded = true;
    for(var i in mapsWaiting) {
        mapsWaiting[i]();
    }

	$('.map').each( function(){
		var address = $(this).attr('data-address') ;

		var geocoder = new google.maps.Geocoder();
		geocoder.geocode( { 'address': address}, (function(results, status) {
			console.log(address);
			console.log(status);
	        if (status == google.maps.GeocoderStatus.OK) {
				if (status != google.maps.GeocoderStatus.ZERO_RESULTS) {
					var position = {
						lat: results[0].geometry.location.lat(), 
						lng: results[0].geometry.location.lng()
					};

					map = new google.maps.Map($(this)[0], {
						center: position,
    					scrollwheel: false,
						zoom: 15
					});

					new google.maps.Marker({
						position: position,
						map: map,
						title: results[0].formatted_address
					});

				} else {
					console.log('456');
					$(this).remove();
				}
			} else {
				console.log('123');
				$(this).remove();
			}
		}).bind( $(this) )  );

	});
}

var getUrlParameter = function(sParam) {
    var sPageURL = window.location.search.substring(1),
        sURLVariables = sPageURL.split('&'),
        sParameterName,
        i;

    for (i = 0; i < sURLVariables.length; i++) {
        sParameterName = sURLVariables[i].split('=');

        if (sParameterName[0] === sParam) {
            return sParameterName[1] === undefined ? true : decodeURIComponent(sParameterName[1]);
        }
    }
};