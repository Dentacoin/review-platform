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

    if ($('#christmasBanner').length) {
    	$('#christmasBanner')[0].removeAttribute("controls");

    	if ($(window).outerWidth() <= 768) {
    		$('#christmasBanner').attr('src', $('#christmasBanner').attr('mobile-src'));
    	}
    }

    $(".close-banner").click( function(e) {
		e.preventDefault();

		$('.christmas-banner').hide();
	});


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

				console.log( parseInt($('#search-map').attr('zoom')) );

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


    $('.widget-radio').change( function(e) {
		$(this).closest('.widget-options').find('label').removeClass('active');
		$(this).closest('label').addClass('active');
	});

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
			// $('html, body').animate({
   //              scrollTop: 100
   //          }, 500);
			$(this).closest('.header-info').find('.expander-wrapper').addClass('active');
		} );
	}


	$('.slider-wrapper [href]').click( function(e) {
		e.stopPropagation();
		e.preventDefault();
		window.location.href = $(this).attr('href');
	} );


	if(!Cookies.get('no-ids')) {
		$('#ids').css('display', 'block');

		$('#ids i').click( function(e) {
			e.preventDefault();
			e.stopPropagation();
			Cookies.set('no-ids', true, { expires: 365 });
			$('#ids').hide();
		});
	}

	if(!Cookies.get('cookiebar') && !$('body').hasClass('sp-trp-iframe') ) {
		$('#cookiebar').css('display', 'flex');
		$('#cookiebar a.accept').click( function() {
			Cookies.set('cookiebar', true, { expires: 365 });
			$('#cookiebar').hide();
			showStrength();
		} );
	}

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

			Cookies.set('hide-strength', true, { expires: 1 });
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

			if(!Cookies.get('cookiebar')) {
				$('.strength-parent').hide();
			} else {
				$('.strength-parent').css('display', 'block');
				$carousel.flickity('resize');

				if(!Cookies.get('hide-strength')) {
					setTimeout( function() {
						$('.strength-button').trigger('click');
					}, 1000);
				}
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