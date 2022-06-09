var slider = null;
var showPopup = null;
var closePopup = null;
var handlePopups = null;
var ajax_is_running = false;
var loadedJS = {};
var prepareMapFunction;
var mapsLoaded = false;
var mapsWaiting = [];
var initMap;
var chooseExistingDentistActions;
var refreshOnClosePopup = false;
var editWorkingHours;
var fixFlickty;
var map_loaded = false;
var upload_loaded = false;
var croppie_loaded = false;
var loadPopupFiles;

var handleTooltip;
var attachTooltips;
var modernFieldsUpdate;
var dentacoin_down = false;
var handleActivePopupFunctions;

jQuery(document).ready(function($) {

    $.ajax( {
		url: 'https://dentacoin.com',
		type: 'GET',
		success: function( data ) {
			dentacoin_down = false;
		},
		error: function(data) {
			console.log(data);
		    dentacoin_down = true;
		},
		timeout: 5000
	});

    $.ajax( {
		url: 'https://api.dentacoin.com/api/enums/',
		type: 'GET',
		success: function( data ) {
			if(data) {
				dentacoin_down = false;
			} else {
				dentacoin_down = true;
			}
		},
		error: function(data) {
			console.log(data);
		    dentacoin_down = true;
		},
		timeout: 5000
	});

	$('.mobile-menu').click( function() {
		$('.menu-primary-container').addClass('active');
		$('body').addClass('popup-visible');
	});

	$('.close-menu').click( function() {
		$('.menu-primary-container').removeClass('active');
		$('body').removeClass('popup-visible');
	});

	handlePopups = function(id) {
		var dataPopupClick = function(e) {
			showPopup( $(this).attr('data-popup'), null, e );
		}

		var dataPopupClickLogged = function(e) {
			if( user_id ) {
				if(!$(this).hasClass('disabled-button')) {
					showPopup( $(this).attr('data-popup-logged'), null, e );				
				}
			} else {
				if(dentacoin_down) {
		    		showPopup('failed-popup');
		    	} else {
					$.event.trigger({type: 'openPatientRegister'});

					$(document).on('dentacoinLoginGatewayLoaded', function (event) {
						var cta = $('.dentacoin-login-gateway-container .cta');
						cta.show();
						for(i=0;i<3;i++) {
							cta.fadeTo('slow', 0).fadeTo('slow', 1);
						}
			        });
			    }
			}
		}

		$('[data-popup]').off('click').click( dataPopupClick );
		$('[data-popup-logged]').off('click').click( dataPopupClickLogged );
	}
	handlePopups();

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
	    	}, 0)
	    }
    }
    modernFieldsUpdate();

	$('input').focus( function() {
		$(this).removeClass('has-error');
	});

    var loadMapScript = function() {
		var second_stop = false;
    	if (!map_loaded && typeof google === 'undefined' && !second_stop) {
			second_stop = true;
			$.getScript('https://maps.googleapis.com/maps/api/js?key=AIzaSyCaVeHq_LOhQndssbmw-aDnlMwUG73yCdk&libraries=places&callback=initMap&language=en', function() {
				map_loaded = true;
    		});
    	}
    }

	var loadCroppie = function() {
		if (!croppie_loaded) {
			$.getScript(window.location.origin+'/js/croppie.min.js', function() {
				croppie_loaded = true;
				$('head').append('<link rel="stylesheet" type="text/css" href="'+window.location.origin+'/css/croppie.css">');
			});
		}
	}

	var loadUploadAvatarScript = function() {
		if (!upload_loaded) {
			$.getScript(window.location.origin+'/js/upload.js', function() {
				upload_loaded = true;
			});
		}
	}

	loadPopupFiles = function(poppup_id) {
		if ($('#'+poppup_id+'.active').length) {

			var scss_to_load = $('#'+poppup_id+'.active').attr('scss-load');
			
			if (typeof scss_to_load !== 'undefined' && scss_to_load !== false && !$('#'+scss_to_load+'-scss').length) {
				$('head').append('<link rel="stylesheet" id="'+scss_to_load+'-scss" type="text/css" href="'+window.location.origin+'/css/'+scss_to_load+'.css">');
			}

			var js_to_load = $('#'+poppup_id+'.active').attr('js-load');
			
			if (typeof js_to_load !== 'undefined' && js_to_load !== false && !loadedJS[js_to_load]) {

				loadedJS[js_to_load] = true;
				$.getScript(window.location.origin+'/js-trp/popups/'+js_to_load+'.js');
			}
		}
	}

	showPopup = function(id, res=null, e) {
		var event_res = res;
		
		if(id=='popup-login') {
			if(dentacoin_down && !user_id) {
	    		showPopup('failed-popup');
	    	} else {
				setTimeout( function() {
					$.event.trigger({type: 'openPatientLogin'});
				}, 500);
			}

		} else if(id=='popup-login-dentist') {
			if(dentacoin_down && !user_id) {
	    		showPopup('failed-popup');
	    	} else {
				setTimeout( function() {
					$.event.trigger({type: 'openDentistLogin'});
				}, 500);
			}

		} else if(id=='popup-register-dentist') {
			if(dentacoin_down && !user_id) {
	    		showPopup('failed-popup');
	    	} else {
				setTimeout( function() {
					$.event.trigger({type: 'openDentistRegister'});
				}, 500);
			}

		} else if(id=='popup-register') {
			if(dentacoin_down && !user_id) {
	    		showPopup('failed-popup');
	    	} else {
				setTimeout( function() {
					$.event.trigger({type: 'openPatientRegister'});
				}, 500);
			}
		} else if(
			id == 'popup-share' 
			|| id == 'verification-popup'
			|| id == 'failed-popup' 
			|| id == 'popup-existing-dentist' 
		) {
			$.ajax({
	            type: "POST",
	            url: window.location.origin+'/get-popup/',
	            data: {
	                id: id,
	            },
	            success: function(data) {
	                if(data) {
	                	if($('.popup.active').length && $('.popup.active').hasClass('verification-popup')) {
	                		$('.verification-popup').removeClass('active');
	                	} else if($('.popup.active').length && !$('popup.active').hasClass('removable')) {
	                		$('.popup.active:not(.removable)').removeClass('active');
	                	} else {
	                		$('.popup.removable').remove();
	                	}
	                	$('body').append(data);
	                	$('body').addClass('popup-visible');
	                	$('.loader-mask').hide();
	                	handleActivePopupFunctions();
	                	handlePopups();

	                	if($('.popup.active').attr('id') == 'popup-share') {
	                		
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
									var url = 'https://twitter.com/share?url=' + escape(post_url) + '&text=' + escape(post_title);
								}
								window.open( url , 'ShareWindow', 'height=450, width=550, top=' + (jQuery(window).height() / 2 - 275) + ', left=' + (jQuery(window).width() / 2 - 225) + ', toolbar=0, location=0, menubar=0, directories=0, scrollbars=0');
							});

							$('.copy-link').click( function(e) {
								e.preventDefault();
								e.stopPropagation();
					
								var $temp = $("<input>");
								$("body").append($temp);
								$temp.val($(this).closest('.share-link').find('input').val()).select();
								document.execCommand("copy");
								$temp.remove();        
					
								$(this).attr('alternative', $(this).text().trim());
								$(this).html('<img src="'+all_images_path+'/check-icon.svg"/>');
					
								setTimeout( (function() {
									$(this).html( $(this).attr('alternative').length ? $(this).attr('alternative') : '<img src="'+all_images_path+'/copy-files.svg"/>' );
								}).bind(this), 3000 );
							});
							
	                	} else if($('.popup.active').attr('id') == 'verification-popup') {
							loadUploadAvatarScript();
							loadCroppie();

							if(event_res) {
								if (event_res.token_user) {
						            $('input[name="last_user_hash"]').val(event_res.token_user);
						        }
						        if (event_res.data.id) {
						            $('input[name="last_user_id"]').val(event_res.data.id);
						        }

						        if (!event_res.data.is_clinic) {
						        	$('.step[step="2"]').remove();
						        	$('.step[step="3"] .popup-desc').html('<span>Step 2:</span> Add a short description about your dental practice');
						        }

						        $('.image-label').css('background-image', 'none');
							}

	                	} else if($('.popup.active').attr('id') == 'popup-existing-dentist') {

	                		for(var i in event_res) {
								$('.existing-dentists').append('\
									<div class="dentist-exists"ex-dent-id='+event_res[i].id+'>\
										<div class="ex-dentist-avatar" style="background-image: url('+event_res[i].avatar+');"></div>\
										<div class="ex-d-info">\
											<p>'+event_res[i].name+'</p>\
											<p>in '+event_res[i].location+'</p>\
										</div>\
										<div class="ex-d-btns">\
											<a href="javascript:;" class="choose-ex-d button">Yes</a>\
											<a href="javascript:;" class="close-ex-d button button-inner-white">No</a>\
										</div>\
									</div>\
								');
							}

							$('.add-team-member-form').find('.check-for-same').val('1');
							chooseExistingDentistActions();
						}

						loadPopupFiles(id);
	                }
	            },
	            error: function(data) {
	                console.log('error');
	            }
	        });
		} else {

			if(id == 'popup-branch') {
				loadUploadAvatarScript();
				loadCroppie();
			}

			$('#'+id+'.popup').addClass('active');
			handlePopups();
			if ($('.popup.active').length) {
				$('body').addClass('popup-visible');
			}

			$('.close-popup').click( function() {
				closePopup();
			});
		}		

		loadPopupFiles(id);
	}

	closePopup = function() {
		var custom_widget_popup = false;
		var existing_dentist = false;
		var invite_popup = false;
		var partner_wallet_address_popup = false;

		if($('#select-reviews-popup').hasClass('active')) {
			custom_widget_popup = true;
		}

		if($('#invite-sample').hasClass('active')) {
			invite_popup = true;
		}
		
		if($('#popup-existing-dentist').hasClass('active')) {
			existing_dentist = true;
		}

		if($('#add-wallet-address').hasClass('active')) {
			partner_wallet_address_popup = true;
		}

		if(($('#add-team-popup').hasClass('active') || $('#popup-invite').hasClass('active')) && $('body').hasClass('guided-tour')) {
			$('.bubble-guided-tour .skip-step').trigger('click');
		}

		if(partner_wallet_address_popup) {
			$.ajax({
	            type: "POST",
	            url: lang + '/close-partner-wallet-popup/',
				data: {
					_token: $('meta[name="csrf-token"]').attr('content'),
				},
	        });
		}

		if( refreshOnClosePopup ) {
			window.location.reload();
		}
		if(existing_dentist) {
			$('#popup-existing-dentist').remove();
			$('#verification-popup').addClass('active');
			if($('.verification-info').length) {

                if($('.verification-form:visible').length) {
                    $('.wh-btn').hide();
                } else {
                    $('.verification-info').hide()
                }
            }

		} else if($('.popup.active').hasClass('removable')){
			
			$('.popup.removable').remove();
			$('body').removeClass('popup-visible');
		} else {
			$('.popup').removeClass('active');
			$('body').removeClass('popup-visible');
		}

		if(custom_widget_popup) {
			showPopup( 'popup-widget' );
		}

		if(invite_popup) {
			showPopup( 'popup-invite' );
		}
	}

	handleActivePopupFunctions =  function() {
		modernFieldsUpdate();

	    $('.close-popup').click( function() {
			closePopup();
		});

		$('.popup').click( function(e) {
			if(!$(this).hasClass('first-guided-tour-popup')) {
				if( !$(e.target).closest('.popup-inner').length && !$(e.target).hasClass('dont-close-popup') && !$(e.target).closest('.dont-close-popup').length ) {
					closePopup();
				}
			}
		});

	    $('input[name="mode"]').change( function() {
	        $(this).closest('.modern-radios').removeClass('has-error');
	    });

	    $('.type-radio').change( function(e) {
			$(this).closest('.mobile-radios').find('label').removeClass('active');
			$(this).closest('label').addClass('active');
		    $('.ajax-alert[error="'+$(this).attr('name')+'"]').remove();
		});

		$('.address-suggester-input').click( function() {
	    	loadMapScript();
	    });

	    $('.search-form input').on('focus keyup', function() {
	    	loadMapScript();
	    });

		$('.special-checkbox, .checkbox').change( function() {
			if($(this).attr('type') == 'checkbox') {
				$(this).closest('label').toggleClass('active');
				$(this).closest('label').removeClass('has-error').removeClass('jump-it');		
				$('.ajax-alert[error="'+$(this).attr('name')+'"]').remove();
			} else {
				$(this).closest('label').parent().find('label').removeClass('active');
				$(this).closest('label').parent().find('input').prop('checked', false);
				$(this).closest('label').addClass('active');
				$(this).prop('checked', true);
			}
		});

		$('.tab').click( function() {
			$('.tab').removeClass('active');
			$(this).addClass('active');
			$('.tab-container').removeClass('active');
			$('#'+ $(this).attr('data-tab')).addClass('active');
		});
	}
	handleActivePopupFunctions();

	if(getUrlParameter('popup-loged')) {
		if( user_id ) {
			showPopup( getUrlParameter('popup-loged') );
		} else {
			if(dentacoin_down) {
	    		showPopup('failed-popup');
	    	} else {
				$.event.trigger({type: 'openPatientRegister'});

				$(document).on('dentacoinLoginGatewayLoaded', function (event) {
					var cta = $('.dentacoin-login-gateway-container .cta');
					cta.show();
					for(i=0;i<3;i++) {
						cta.fadeTo('slow', 0).fadeTo('slow', 1);
					}
		        });
		   	}
		}
	}
	if(getUrlParameter('popup')) {
		if(getUrlParameter('popup') == 'popup-lead-magnet') {
			window.location.href = lead_magnet_url;
		} else {
			showPopup( getUrlParameter('popup') );
		}
	}

	if(getUrlParameter('dcn-gateway-type') && dentacoin_down) {
		showPopup('failed-popup');
	}

	if($(window).outerWidth() > 768) {
		function fix_header(e){
			if ($('header').outerHeight() - 40 < $(window).scrollTop()) {
				$('header').addClass('fixed-header');
			} else {
				$('header').removeClass('fixed-header');
			}
		}
	} else {
		var lastScrollTop = 0;
		function fix_header(e){
			if ($('header').height() < $(window).scrollTop()) {
				$('header').addClass('fixed-header');
			} else {
				$('header').removeClass('fixed-header');
			}
			var st = $(this).scrollTop();
			if (st > lastScrollTop){
				$('header').addClass('header-down');

				if($('.fixed-tabs').length) {
					$('.fixed-tabs').css('top', 0);
				}
			} else {
				$('header').removeClass('header-down');

				if($('.fixed-tabs').length) {
					$('.fixed-tabs').css('top', $('header').outerHeight());
				}
			}
			lastScrollTop = st;
		};
	}
	$(window).scroll(fix_header);
	fix_header();


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

	$('.slider-wrapper [href]').click( function(e) {
		e.stopPropagation();
		e.preventDefault();
		window.location.href = $(this).attr('href');
	});

	$('.button-sign-up-dentist').click( function() {
		fbq('track', 'DentistInitiateRegistration');
		gtag('event', 'ClickSignup', {
			'event_category': 'DentistRegistration',
			'event_label': 'InitiateDentistRegistration',
		});
	});

	//to  be deleted ?
	$('.button-sign-up-patient').click( function() {
		fbq('track', 'PatientInitiateRegistration');
		gtag('event', 'ClickSignup', {
			'event_category': 'PatientRegistration',
			'event_label': 'PatientInitiateRegistration',
		});
	});

	//to  be deleted ?
	$('.button-login-patient').click( function() {
		fbq('track', 'PatientLogin');
		gtag('event', 'ClickLogin', {
			'event_category': 'PatientLogin',
			'event_label': 'LoginPopup',
		});
	});

	handleTooltip = function(e) {
        $('.tooltip-window').html($(this).attr('text'));

		var that = $(this).closest('.tooltip-text').length ? $(this).closest('.tooltip-text') : $(this);
		var y = that.offset().top + that.outerHeight() + 10;
		var x = that.offset().left + that.outerWidth() / 2 - $('.tooltip-window').outerWidth() / 2 ;

		$('.tooltip-window').css('left', x );
		$('.tooltip-window').css('top', y );
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

	// $('.strength-button').click( function() {
	// 	if ($(this).hasClass('active')) {
	// 		$(this).removeClass('active');
	// 		$('body').removeClass('dark');
	// 		$('.strength-parent').removeClass('active');
	// 		// $('.strength-wrapper').css('top', '100%');
	// 		$('.stretching-box').css('height', 0);

	// 	} else {
	// 		$(this).addClass('active');
	// 		$('body').addClass('dark');
	// 		$('.strength-parent').addClass('active');
	// 		// $('.strength-wrapper').css('top', 'calc(100% - '+$('.strength-wrapper').outerHeight()+'px)');

	// 		$('.stretching-box').css('height', $('.strength-flickity').outerHeight());
	// 	}
	// });

    // if ($('.strength-flickity').length) {
    // 	var $carousel = $('.strength-flickity');

	// 	$carousel.flickity({
	//     	wrapAround: true,
	// 		//adaptiveHeight: true,
	// 		draggable: true,
	// 		pageDots: false,
	// 	});
	// }

	// var showStrength = function() {

	// 	if(($('body').hasClass('page-dentist') || $('body').hasClass('page-index')) && $('.strength-parent').length) {
			
	// 		$('.strength-parent').css('display', 'block');
	// 		$carousel.flickity('resize');
	// 	}
	// }
	// showStrength();

	// $('.black-overflow').click( function() {
	// 	if ($('.strength-button').length) {
	// 		$('.strength-button').removeClass('active');
	// 		$('.strength-parent').removeClass('active');
	// 		// $('.strength-wrapper').css('top', '100%');
	// 		$('.stretching-box').css('height', 0);
	// 	}		
	// });

	// $('[event_category]').click( function() {
	// 	gtag('event', $(this).attr('event_action'), {
    //         'event_category': $(this).attr('event_category'),
    //         'event_label': $(this).attr('event_label'),
    //     });
	// });

	// $('.str-invite').click( function() {
	// 	$('.strength-button.active').trigger('click');
	// 	showPopup('popup-invite');
	// });

	// $('.str-description').click( function() {
	// 	$('body, html').animate({
    //         scrollTop: $('.profile-tabs').offset().top
    //     }, 500);
	// 	$('.strength-button.active').trigger('click');
	// 	$('[data-tab="about"]').trigger('click');
	// 	$('.about-content[role="presenter"] a').trigger('click');
	// 	$('#dentist-description').css('box-shadow', '0px 0px 14px 2px #F44336');
	// });

	// $('.str-socials').click( function() {
	// 	$('body, html').animate({
    //         scrollTop: 0
    //     }, 500);
	// 	$('.strength-button.active').trigger('click');
	// 	$('.open-edit:visible').trigger('click');
	// 	$('.social-wrap:visible').css('box-shadow', '0px 0px 13px -1px #F44336');
	// });

	// $('.str-team').click( function() {
	// 	$('.strength-button.active').trigger('click');
	// 	showPopup('add-team-popup');
	// });

	// $('.str-photos').click( function() {
	// 	$('body, html').animate({
    //         scrollTop: $('.profile-tabs').offset().top
    //     }, 500);
	// 	$('.strength-button.active').trigger('click');
	// 	$('[data-tab="about"]').trigger('click');
	// });

	// $('.str-see-reviews').click( function() {
	// 	$('.strength-button.active').trigger('click');
	// 	$('[data-tab="reviews"]').trigger('click');
	// 	$('body, html').animate({
    //         scrollTop: $('.written-wrapper').first().offset().top - 200
    //     }, 500);

  //       if(ajax_is_running) {
		// 	return;
		// }
		// ajax_is_running = true;

  //   	$.ajax( {
		// 	url: window.location.origin+'/en/profile/check-reviews/',
		// 	type: 'GET',
		// 	dataType: 'json',
		// 	success: function( data ) {
		// 		console.log('success-reviews');
		// 	    ajax_is_running = false;
		// 	},
		// 	error: function(data) {
		// 		console.log(data);
		// 	    ajax_is_running = false;
		// 	}
		// });
	// });


    var hasNumber = function(myString) {
        return /\d/.test(myString);
    }
    var hasLowerCase = function(str) {
        return (/[a-z]/.test(str));
    }
    var hasUpperCase = function(str) {
        return (/[A-Z]/.test(str));
    }
    var validatePassword = function(password) {
        return password.trim().length >= 8 && password.trim().length <= 30 && hasLowerCase(password) && hasUpperCase(password) && hasNumber(password);
    }

    $('#claim-profile-form').submit( function(e) {
        e.preventDefault();

        if(ajax_is_running) {
            return;
        }
        ajax_is_running = true;

        $(this).find('.ajax-alert').remove();
        $(this).find('.alert').hide();
        $(this).find('.has-error').removeClass('has-error');

        if($('#claim-password').val() && !validatePassword($('#claim-password').val()) ) {

        	$('#password-validator').show();
        	ajax_is_running = false;
        	return;
        }

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
				if (data.reload) {

                    if (!Cookies.get('performance_cookies')) {
                        basic.cookies.set('performance_cookies', 1);
                    }
                    if (!Cookies.get('functionality_cookies')) {
                        basic.cookies.set('functionality_cookies', 1);
                    }
                    if (!Cookies.get('strictly_necessary_policy')) {
                        basic.cookies.set('strictly_necessary_policy', 1);
                    }

                    if ($('.dcn-privacy-policy-cookie').length) {
                        $('.dcn-privacy-policy-cookie').remove();
                    }

					window.location.reload();
				} else {
					$('#claim-popup').addClass('claimed');
				}
				
			} else {

				if(data.messages) {
					for(var i in data.messages) {
	                    $(this).find('[name="'+i+'"]').closest('.alert-after').after('<div class="alert alert-warning ajax-alert">'+data.messages[i]+'</div>');

	                    $(this).find('[name="'+i+'"]').addClass('has-error');

	                    if ($(this).find('[name="'+i+'"]').closest('.modern-file').length) {
	                        $(this).find('[name="'+i+'"]').closest('.modern-file').addClass('has-error');
	                    }

	                    if ($(this).find('[name="'+i+'"]').closest('.agree-label').length) {
	                        $(this).find('[name="'+i+'"]').closest('.agree-label').addClass('has-error');
	                        $(this).find('[name="'+i+'"]').closest('.agree-label').after('<div class="alert alert-warning ajax-alert">'+data.messages[i]+'</div>');
	                    }
	                }
	                $('.popup').animate({
		                scrollTop: $('.ajax-alert:visible').first().offset().top
		            }, 500);
				} else {
					$('#claim-err').html(data.message).show();
				}
			}
            ajax_is_running = false;

	    }).bind(this) ).fail(function (data) {
			$('#claim-err').show();
	    });

	    return;
    } );

    $('.claimed-ok').click( function() {
		closePopup();
	});

	$('.get-started-button').click( function() {
		if(dentacoin_down && !user_id) {
    		showPopup('failed-popup');
    	} else {
			$.event.trigger({type: 'openDentistRegister'});
		}
	});

    $('.str-check-assurance').click( function() {
    	if(ajax_is_running) {
			return;
		}
		ajax_is_running = true;

    	$.ajax( {
			url: window.location.origin+'/en/profile/check-assurance/',
			type: 'GET',
			dataType: 'json',
			success: function( data ) {
				console.log('success-assurance');
			    ajax_is_running = false;
			},
			error: function(data) {
				console.log(data);
			    ajax_is_running = false;
			}
		});
    });

    $('.str-check-dentacare').click( function() {
    	if(ajax_is_running) {
			return;
		}
		ajax_is_running = true;

    	$.ajax( {
			url: window.location.origin+'/en/profile/check-dentacare/',
			type: 'GET',
			dataType: 'json',
			success: function( data ) {
				console.log('success-dentacare');
			    ajax_is_running = false;
			},
			error: function(data) {
				console.log(data);
			    ajax_is_running = false;
			}
		});
    });

    $('.open-str-link').click( function() {
    	window.open($(this).attr('href'), '_blank');
    });

    if(!dentacoin_down) {
    	
	    $(document).on('dentistAuthSuccessResponse', async function ( event) {
	    	if(event.response_data.trp_ban) {
	    		window.location.href = $('#site-url').attr('url')+lang+'/banned/';
	    	} else {
	    		window.location.href = $('#site-url').attr('url');
	    	}
	    });
	    $(document).on('patientAuthSuccessResponse', async function ( event) {
	    	if(event.response_data.trp_ban) {
	    		window.location.href = $('#site-url').attr('url')+lang+'/banned/';
	    	} else {
	    		window.location.href = $('#site-url').attr('url');
	    	}
	    });

	    $(document).on('dentistRegisterSuccessResponseTrustedReviews', async function ( event) {
	    	showPopup('verification-popup', event.response_data);
	    });
    }

    $('.open-dentacoin-gateway').click(function(e) {

    	if(dentacoin_down && !user_id) {
    		e.stopImmediatePropagation();
    		showPopup('failed-popup');
    	}
    });


    //working hours
	
	editWorkingHours = function() {

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
	}
    //end working hours
	
	// setTimeout( function() {

	//     $('.support-icon').css('bottom', $('.christmas-banner:visible').outerHeight() + ($(window).outerWidth() <= 768 ? 10 : 20));
	//     if($('.christmas-banner').length) {
	//     	$('body').addClass('with-banner');
	//     	$('.christmas-banner:visible .banner-video')[0].play();
	//     	$('.christmas-banner:visible .banner-video')[0].removeAttribute("controls");
	//     }

	//     $('.close-banner').click( function(e) {
	//     	e.preventDefault();
	//     	$('.support-icon').css('bottom', ($(window).outerWidth() <= 768 ? 10 : 20));
	//     	$('.christmas-banner').hide();

	//     	$.ajax( {
	// 			url:  window.location.origin+'/en/remove-banner/',
	// 			type: 'GET',
	// 		});
	//     });
    // }, 500);

});

//
//Maps stuff
//

prepareMapFunction = function( callback ) {
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

var basic = {
    cookies: {
        set: function(name, value) {
            if(name == undefined){
                name = "cookieLaw";
            }
            if(value == undefined){
                value = 1;
            }
            var d = new Date();
            d.setTime(d.getTime() + (100*24*60*60*1000));
            var expires = "expires="+d.toUTCString();
            document.cookie = name + "=" + value + "; " + expires + ";domain=.dentacoin.com;path=/;secure";
            if(name == "cookieLaw"){
                $(".cookies_popup").slideUp();
            }
        },
        get: function(name) {

            if(name == undefined){
                var name = "cookieLaw";
            }
            name = name + "=";
            var ca = document.cookie.split(';');
            for(var i=0; i<ca.length; i++) {
                var c = ca[i];
                while (c.charAt(0)==' ') c = c.substring(1);
                if (c.indexOf(name) == 0) return c.substring(name.length, c.length);
            }

            return "";
        }
    }
};