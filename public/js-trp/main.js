var slider = null;
var sliderTO = null;
var showPopup = null;
var closePopup = null;
var handlePopups = null;
var ajax_is_running = false;
var prepareMapFucntion;
var mapsLoaded = false;
var mapsWaiting = [];
var initMap;
var mapMarkers = {};
var fixFlickty;
var suggestTO;
var refreshOnClosePopup = false;
var map_loaded = false;

var handleTooltip;
var attachTooltips;
var modernFieldsUpdate;
var uploadTeamImage;
var id_counter=0;
var dentacoin_down = false;
var handleActivePopupFunctions;

jQuery(document).ready(function($){

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

    var loadMapScript = function() {
    	if (!map_loaded && typeof google === 'undefined' ) {
    		$.getScript('https://maps.googleapis.com/maps/api/js?key=AIzaSyCaVeHq_LOhQndssbmw-aDnlMwUG73yCdk&libraries=places&callback=initMap&language=en', function() {
	    		map_loaded = true;
    		} );
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
		} else if(id == 'popup-share' || id == 'verification-popup' || id == 'popup-wokring-time-waiting'
		|| id == 'failed-popup' || id == 'popup-existing-dentist' || id == 'invite-new-dentist-popup'
		|| id == 'invite-new-dentist-success-popup' || id == 'popup-lead-magnet') {

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
							
	                	} else if($('.popup.active').attr('id') == 'verification-popup') {
	                		$.getScript(window.location.origin+'/js-trp/login.js', function() {
							});
					        $.getScript(window.location.origin+'/js/upload.js', function() {
							});

							if(event_res) {
								if (event_res.token_user) {
						            $('input[name="last_user_hash"]').val(event_res.token_user);
						        }
						        if (event_res.data.id) {
						            $('input[name="last_user_id"]').val(event_res.data.id);
						        }
						        if (event_res.data.is_clinic) {

						            $('.wh-btn').hide();
						            $('#title-clinic').show();
						            $('#title-dentist').hide();
						        } else {
						        	$('#title-clinic').hide();
						            $('#title-dentist').show();
						            $('#clinic-add-team').remove();
						        }

						        $('.image-label').css('background-image', 'none');
							}

	                	} else if($('.popup.active').attr('id')=='popup-wokring-time-waiting') {

							if($('#popup-wokring-time-waiting').length) {
								$('#popup-wokring-time-waiting').find('[name="last_user_id"]').val($('#verification-popup input[name="last_user_id"]').val());
								$('#popup-wokring-time-waiting').find('[name="last_user_hash"]').val($('#verification-popup input[name="last_user_hash"]').val());
							}
							
				            if ($('#day-1').is(':checked')) {
				                $('.all-days-equal').show();
				            } else {
				                $('.all-days-equal').hide();
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

						} else if($('.popup.active').attr('id') == 'invite-new-dentist-success-popup') {

		                	$('#inv_dent_name').html(event_res.dentist_name);

						} else if($('.popup.active').attr('id') == 'popup-lead-magnet') {

							$('#magnet-website').on('keyup keydown', function() {
						        $(this).val($(this).val().toLowerCase());
						    });
						}
	                }
	            },
	            error: function(data) {
	                console.log('error');
	            }
	        });
		} else {

			if(id=='submit-review-popup') {

				if($(window).outerWidth() && typeof FB !== 'undefined') {
				    FB.CustomerChat.hide();
			    }

			    if($(window).outerWidth() < 768) {
			    	$(document.body).on('touchmove', function() {
			    		$('.question').find('.popup-title').removeClass('sticky-q');
			    	});
			    }

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
						$(this).closest('.question').nextAll(".question").not('.do-not-show').first().removeClass('hidden');

						if($(window).outerWidth() < 768) {
							$(this).closest('.question').find('.popup-title').removeClass('sticky-q');
						}

						if( !$(this).closest('.question').next().next().hasClass('question') || $(this).closest('.question').next().hasClass('skippable') ) {
							$(this).closest('.question').next().next().removeClass('hidden');
							$(this).closest('.question').next().next().show();
						}
					}

		            $('.popup, .popup-inner').animate({
		                scrollTop: $('.questions-wrapper').innerHeight()
		            }, 500);

		            if($(window).outerWidth() < 768) {

			            $('.question').find('.popup-title').removeClass('sticky-q');

			            if($(this).closest('.question').offset().top - 100 < 0 && $(this).closest('.question').next().length && $(this).closest('.question').next().attr('q-id') !== false) {
			            	if(!$(this).closest('.question').find('.popup-title').hasClass('sticky-q') && !ok) {
			            		$(this).closest('.question').find('.popup-title').addClass('sticky-q');	
			            	}
			            }
			        }
				} );

				$('.questions-wrapper .question').first().removeClass('hidden');
				$('.questions-wrapper .question').each( function() {
					$(this).find('.review-answers .subquestion').first().removeClass('hidden');
				} );
				
			} else if(id == 'map-results-popup') {
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
			} else if($('.popup.active').attr('id')=='popup-wokring-time') {

				if($('.popup-wokring-time').length && $('#popup-wokring-time').is('[empty-hours]')) {
					
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
		    } else if(id =='social-profile-popup') {
		    	$.getScript(window.location.origin+'/js/upload.js', function() {

        			$('.popup .closer-pop').click( function() {

				        if($(this).hasClass('inactive')) {
				            return;
				        }
				        if($(this).closest('.popup').hasClass('ban')) {
				            window.location.reload();
				        }

				        $(this).closest('.popup').removeClass('active');
				        $('body').removeClass('popup-visible');
				    } );
        		});
		    }

			$('#'+id+'.popup').addClass('active');
			handlePopups();
			if ($('.popup.active').length) {
				$('body').addClass('popup-visible');
			}
		}
	}

	closePopup = function() {
		var waiting_for_approval = false;
		var custom_widget_popup = false;
		var existing_dentist = false;
		var invite_popup = false;
		var verification_working_time = false;

		if($('#select-reviews-popup').hasClass('active')) {
			custom_widget_popup = true;
		}
		if($('#invite-sample').hasClass('active')) {
			invite_popup = true;
		}
		if($('#popup-wokring-time-waiting').hasClass('active')) {
			waiting_for_approval = true;
			$('#popup-wokring-time-waiting form').submit();
		}
		if($('#popup-existing-dentist').hasClass('active')) {
			existing_dentist = true;
		}

		if(($('#add-team-popup').hasClass('active') || $('#popup-invite').hasClass('active')) && $('body').hasClass('guided-tour')) {
			$('.bubble-guided-tour .skip-step').trigger('click');
		}

		if($('#popup-wokring-time').hasClass('active')) {
			$('#popup-wokring-time form').submit();
		}

		if( refreshOnClosePopup ) {
			window.location.reload();
		}

		if(invite_popup) {
			showPopup( 'popup-invite' );
		}

		if(custom_widget_popup) {
			showPopup( 'popup-widget' );
		}

		if(window.innerWidth < 768 && typeof FB !== 'undefined') {
		    FB.CustomerChat.show();
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

		} else if(waiting_for_approval) {
			$('#popup-wokring-time-waiting').remove();
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

	}

	handlePopups = function(id) {
		var dataPopupClick = function(e) {
			showPopup( $(this).attr('data-popup'), null, e );
		}

		var dataPopupClickLogged = function(e) {
			if( user_id ) {
				showPopup( $(this).attr('data-popup-logged'), null, e );				
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

	handleActivePopupFunctions =  function() {
		modernFieldsUpdate();
		
		$('.copy-link').click( function(e) {
	    	e.preventDefault();
	    	e.stopPropagation();

	        var $temp = $("<input>");
	        $("body").append($temp);
	        $temp.val($(this).closest('.flex').find('input').val()).select();
	        document.execCommand("copy");
	        $temp.remove();        

	        $(this).attr('alternative', $(this).text().trim());
	        $(this).html('<img src="'+all_images_path+'/check-icon.svg"/>');

	        setTimeout( (function() {
	        	$(this).html( $(this).attr('alternative').length ? $(this).attr('alternative') : '<img src="'+all_images_path+'/copy-files.svg"/>' );
	        }).bind(this), 3000 );
	    } );

	    $('.add-team-member-form').submit( function(e) {
	        e.preventDefault();

	        if(ajax_is_running) {
	            return;
	        }

	        ajax_is_running = true;

	        $(this).find('.member-alert').hide().removeClass('alert-warning').removeClass('alert-success');

	        that = $(this);

	        $.post( 
	            $(this).attr('action'), 
	            $(this).serialize() , 
	            function (data) {
	                if(data.success) {

	                	if(data.dentists) {
	                		showPopup('popup-existing-dentist', data.dentists);
	                	} else {                		
		                    that.find('.check-for-same').val('');
		                    that.find('.photo-name-team').val('');
		                    that.find('.image-label').css('background-image', 'none');
		                    that.find('.image-label').find('.centered-hack').show();
		                    that.find('.team-member-email').val('');
		                    that.find('.team-member-name').val('').focus();
		                    that.find('.member-alert').show().addClass('alert-success').html(data.message);
		                    $('.existing-dentists').children().remove();

		                    if (data.with_email) {
		                    	gtag('event', 'Invite', {
		                            'event_category': 'DentistRegistration',
		                            'event_label': 'ClinicTeam',
		                        });
		                    } else {
		                    	gtag('event', 'Add', {
		                            'event_category': 'DentistRegistration',
		                            'event_label': 'ClinicTeam',
		                        });
		                    }
	                	}
	                    
	                } else {
	                    that.find('.member-alert').show().addClass('alert-warning').html(data.message);                    
	                }
	                ajax_is_running = false;

		        }, "json"
			);
		});

		$('#popup-wokring-time-waiting form').submit( function(e) {
			e.preventDefault();
			
	        if(ajax_is_running) {
	            return;
	        }
	        ajax_is_running = true;

	        that = $(this);
	        $.post( 
	            $(this).attr('action'), 
	            $(this).serialize() , 
	            (function( data ) {
	                if (data.success) {

	                	gtag('event', 'Add', {
	                        'event_category': 'DentistRegistration',
	                        'event_label': 'OpenHours',
	                    });

	                } else {
	                    console.log('error');
	                }
	                ajax_is_running = false;
	            }).bind(this), "json"
	        );          

	        return false;
	    });

	    $('.work-hour-cb').change( function() {
	        var active = $(this).is(':checked');
	        var texts = $(this).closest('.popup-desc').find('select');
	        if(active) {
	        	texts.removeClass('grayed');
	            //texts.prop("disabled", false);
	        } else {
	        	texts.addClass('grayed');
	            //texts.attr('disabled', 'disabled');
	        }

	        if ($(this).attr('name') == 'day-1') {
	            if ($(this).is(':checked')) {
	                $('.all-days-equal').show();
	            } else {
	                $('.all-days-equal').hide();
	            }
	        }
	    } );

	    $('.popup-wokring-time select').on('change click',  function() {
	    	$(this).closest('.popup-desc').find('input').prop('checked', true);
	    	$(this).closest('.popup-desc').find('select').removeClass('grayed');

	    	if ($('#day-1').is(':checked')) {
	            $('.all-days-equal').show();
	        } else {
	            $('.all-days-equal').hide();
	        }
	    });

	    $('.all-days-equal').click( function() {
	        for (var i = 2; i<6; i++) {
	            if (!$('#day-'+i).is(':checked')) {
	                $('#day-'+i).click();
	            }
	            $('[name="work_hours['+i+'][0][0]"]').val($('[name="work_hours[1][0][0]"]').val());
	            $('[name="work_hours['+i+'][0][1]"]').val($('[name="work_hours[1][0][1]"]').val());
	            $('[name="work_hours['+i+'][1][0]"]').val($('[name="work_hours[1][1][0]"]').val());
	            $('[name="work_hours['+i+'][1][1]"]').val($('[name="work_hours[1][1][1]"]').val());
	        }
	    });

	    $('.close-popup').click( function() {
			closePopup();
		});

		$('.close-cur-popup').click( function() {
			$(this).closest('.popup').removeClass('active');
		})

		$('.popup').click( function(e) {
			if(!$(this).hasClass('first-guided-tour-popup')) {
				
				if( !$(e.target).closest('.popup-inner').length ) {
					closePopup();
				}
			}
		} );

		$('.team-member-job').change( function() {
	        if ($(this).val() == 'dentist') {
	            $(this).closest('.flex').find('.mail-col').show();
	        } else {
	            $(this).closest('.flex').find('.mail-col').hide();
	        }
	    });

	    $('input[name="mode"]').change( function() {
	        $(this).closest('.modern-radios').removeClass('has-error');
	    } );

	    $('.invite-new-dentist-form .address-suggester-input').focus(function(e) {
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
		                	showPopup( 'invite-new-dentist-success-popup', data);

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
		                    console.log($('.has-error').first());
		                    $('.popup').animate({
				                scrollTop: $('.has-error').first().offset().top
				            }, 500);
		                }
		                ajax_is_running = false;
		            }).bind(that), "json"
		        );
		        return false;
		    }
		} );

	    $('.type-radio').change( function(e) {
			$(this).closest('.mobile-radios').find('label').removeClass('active');
			$(this).closest('label').addClass('active');
		    $('.ajax-alert[error="'+$(this).attr('name')+'"]').remove();
		});

		$('#search-input, .address-suggester-input').click( function() {
	    	loadMapScript();
	    });

	    $('#search-input').on('focus keyup', function() {
	    	loadMapScript();
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
	        //$('#magnet-submit').append('<div class="loader"><i></i></div>');

	        $.post( 
	            $(this).attr('action'), 
	            $(this).serialize() , 
	            function( data ) {
	                if(data.success) {
	                    fbq('track', 'TRPMagnetComplete');

	                    gtag('event', 'SeeScore', {
	                        'event_category': 'LeadMagnet',
	                        'event_label': 'ReplyToReviews',
	                    });

	                    window.location.href = data.url;
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

		$('.magnet-popup').click( function() {
	        var that = $(this);

	        $.ajax({
	            type: "GET",
	            url: that.attr('data-url'),
	            dataType: 'json',
	            success: function(ret) {
	                if(ret.session) {
	                    window.location.href = ret.url;
	                } else {
	                    showPopup('popup-lead-magnet');
	                }
	            },
	            error: function(ret) {
	                console.log('error');
	            }
	        });
	        
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
		showPopup( getUrlParameter('popup') );
	}

	if(getUrlParameter('dcn-gateway-type') && dentacoin_down) {
		showPopup('failed-popup');
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

        if($(this).closest('.tooltip-text').hasClass('input-tooltip')) {
        	var that = $(this).closest('.tooltip-text');
	        var y = that.offset().top - $('.tooltip-window').outerHeight() - 5;
	    	var x = that.offset().left + that.outerWidth() / 2 - $('.tooltip-window').outerWidth() / 2 ;

	        $('.tooltip-window').css('left', x );
	        $('.tooltip-window').css('top', y );

	        $('.tooltip-window').addClass('top-tooltip');
        } else {
        	$('.tooltip-window').removeClass('top-tooltip');
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

	$('.str-see-reviews').click( function() {
		$('.strength-button.active').trigger('click');
		$('[data-tab="reviews"]').trigger('click');
		$('body, html').animate({
            scrollTop: $('.review-wrapper').first().offset().top - 200
        }, 500);

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
	});


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

	$('.country-dropdown').change( function() {
		if ($(this).attr('real-country') != '') {
			if ($(this).val() != $(this).attr('real-country')) {
				$(this).parent().parent().find('.ip-country').show();
			} else {
				$(this).parent().parent().find('.ip-country').hide();
			}
		}
	});

	$('.get-started-button').click( function() {
		if(dentacoin_down && !user_id) {
    		showPopup('failed-popup');
    	} else {
			$.event.trigger({type: 'openDentistRegister'});
		}
	});

    var chooseExistingDentistActions = function() {
    	$('.close-ex-d').click( function(e) {
			e.preventDefault();
			e.stopPropagation();

	    	if ($(this).closest('.existing-dentists').children().length > 1) {
	    		$(this).closest('.dentist-exists').remove();
	    	} else {
	    		$(this).closest('.popup').remove();
	    		if($('#verification-popup').length) {
	    			$('#verification-popup').addClass('active');
	    		}
	    	}
	    });

	    $('.choose-ex-d').click( function(e) {
			e.preventDefault();
			e.stopPropagation();

	        var ex_d_id = $(this).closest('.dentist-exists').attr('ex-dent-id');
	        var clinic_id = $('input[name="last_user_id"]').length ? $('input[name="last_user_id"]').val() : null;
	        var that = $(this);
	        var form = $('.add-team-member-form');

	        $.ajax({
	            type: "POST",
	            url: window.location.origin+'/en/profile/add-existing-dentist-team/',
	            data: {
	            	clinic_id: clinic_id,
	            	ex_d_id: ex_d_id,
	                _token: $('input[name="_token"]').val(),
	            },
	            dataType: 'json',
	            success: function(ret) {
	                if(ret.success) {

	                	that.closest('.popup').remove();
	                	if($('#verification-popup').length) {
			    			$('#verification-popup').addClass('active');
			    		}

	                	form.find('.check-for-same').val('');
	                	// that.closest('.dentist-exists').find('.ex-d-btns').append('<div class="alert alert-success" style="display:inline-block;">Added</div>');
	                	// that.closest('.dentist-exists').find('.ex-d-btns a').remove();

	                    form.find('.team-member-email').val('');
	                    form.find('.team-member-job').val('dentist');
	                    form.find('.team-member-name').val('').focus();
	                    form.find('.team-member-photo').closest('.image-label').css('background-image', 'none');
	                    form.find('.photo-name-team').val('');
	                    form.find('.member-alert').show().addClass('alert-success').html(ret.message);
	                	
	                } else {
	    				console.log('error');
	                }
	            },
	            error: function(ret) {
	                console.log('error');
	            }
	        });
	    });
    }

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

	    		var attr = $('#site-url').attr('open-popup');

				if (typeof attr !== typeof undefined && attr !== false && attr == 'invite-dentist') {
				    window.location.href = $('#site-url').attr('url')+'?popup=invite-new-dentist-popup';
				} else {
					window.location.href = $('#site-url').attr('url');
				}
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

    $('#social-profile-form').submit( function(e) {
        e.preventDefault();

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
                    $('body').removeClass('popup-visible');
                    $('#social-profile-popup').remove();
                } else {

                    if(data.without_image) {
                        that.find('.without-image').show();
                    } else {

                        for(var i in data.messages) {
                            $('[name="'+i+'"]').closest('.alert-after').after('<div class="alert alert-warning ajax-alert" error="'+i+'">'+data.messages[i]+'</div>');

                            $('[name="'+i+'"]').addClass('has-error');
                        }
                    }
                }
                ajax_is_running = false;
            }).bind(that), "json"
        );  
    } );

});

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