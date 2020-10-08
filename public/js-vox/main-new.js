ajax_is_running = false;
var VoxTest = {};
var handleScrollbars;
var scrollToActive;
var FB_status;
var fbLogin;
var fb_city_id;
var checkFilledDots;
var simpleCountDown, hoursCountdown;
var simpleCountDownTO, hoursCountdownTO;
var flickityScales;

var mapsLoaded = false;
var mapsWaiting = [];
var modernFieldsUpdate;
var welcome_vox;
var welcome_vox_q_count;
var pollsFunction;
var pollStats;
var pollStatsAnimate;
var tooltipsFunction;
var calendarEvents;
var calendarListEvents;
var showStats;
var showPoll;
var dentacoin_down = false;
var welcome_vox_now;
var cur_href = null;

var preloadImages = function(urls, allImagesLoadedCallback){
    var loadedCounter = 0;
    var toBeLoadedNumber = urls.length;
    var preloadImage = function(url, anImageLoadedCallback){
        var img = new Image();
        img.onload = anImageLoadedCallback;
        img.src = url;
    }
    urls.forEach(function(url){
        preloadImage(url, function(){
            loadedCounter++;
                console.log('Number of loaded images: ' + loadedCounter);
          if(loadedCounter == toBeLoadedNumber){
            allImagesLoadedCallback();
          }
        });
    });
}

$(document).ready(function(){

	$.ajax( {
		url: 'https://dentacoin.com',
		type: 'GET',
		success: function( data ) {
			dentacoin_down = false;
		},
		error: function(data) {
		    dentacoin_down = true;
		},
		timeout: 5000
	});

    $('.country-select').change( function() {
    	$(this).closest('form').find('input[name="address"]').val('');
    	$(this).closest('form').find('.suggester-map-div').hide();
    	$(this).closest('form').find('.geoip-confirmation').hide();
        $(this).closest('form').find('.geoip-hint').hide();
        $(this).closest('form').find('.different-country-hint').hide();

    	var city_select = $(this).closest('form').find('.city-select').first();
    	city_select.attr('disabled', 'disabled');
		$.ajax( {
			url: '/cities/' + $(this).val(),
			type: 'GET',
			dataType: 'json',
			success: function( data ) {
				// console.log(data);
    			city_select.attr('disabled', false)
			    .find('option')
			    .remove();
			    for(var i in data.cities) {
			    	// console.log( fb_city_id, data.cities[i] );
    				city_select.append('<option value="'+i+'" '+(fb_city_id && fb_city_id==data.cities[i] ? 'selected="selected"' : '' )+'>'+data.cities[i]+'</option>');
			    }
				//city_select
                $('.phone-code-holder').html(data.code);
				//$('#modal-message .modal-body').html(data);
			}
		});
    } );

    VoxTest.handleNextQuestion = function(skip=null) {

		$('#current-question-bar').css('width', ((vox.current / vox.count)*100)+'%');
		var mins = Math.ceil( (vox.count - vox.current)/6 );
		$('#current-question-num').html( mins<2 ? '<1' : '~'+mins );

		if(vox.current>1) {

			var answerd_q = vox.answered_without_skip_count;

			var welcomeBonus = 0;
			if( welcome_vox ) {
				var doingAsl = vox.current > ( (vox.count_real+1) + welcome_vox_q_count );
				var doingWelcome = vox.current <= welcome_vox_q_count;
				if(!doingWelcome && !doingAsl) {
					welcomeBonus = 100;
				}

			} else {
				var doingAsl = vox.current > (vox.count_real+1);
				var doingWelcome = false;
			}

			if( doingAsl ) {
				//console.log('doingAsl');

			} else if( welcome_vox_now || welcome_vox) {

				//console.log('doingWelcome');
				$('#current-question-reward').html( (vox.current / welcome_vox_q_count) * 100 );
				$('#dcn-test-reward-before').html('DCN: 100');
			} else { //Нормални
				
				//console.log('normal');
				if(!skip) {
					var reward = 0;
				}
				if( $('body').hasClass('page-welcome-survey') ) {
					var reward_per_question = ( vox.reward / vox.count_real );
					reward = reward_per_question * answerd_q;
				} else {
					if(!skip) {
						reward = vox.reward_single * answerd_q;
					}
				}
				if(!skip) {
					reward += welcomeBonus;

					$('#current-question-reward').html( Math.round(reward) );
					$('#dcn-test-reward-before').hide();
					$('#dcn-test-reward-after').show();

					$('.coins-test').html( Math.round(reward) );
				}
			}
		}		
	}

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

	    $('input').focus( function() {
			$(this).removeClass('has-error');
		});

		$('.type-radio').change( function(e) {
			$(this).closest('.modern-radios').find('label').removeClass('active');
			$(this).closest('label').addClass('active');
		});

		$('input[name="target"]').change( function() {
	        $(this).closest('.modern-radios').removeClass('has-error');
	        $('.ajax-alert[error="'+$(this).attr('name')+'"]').remove();
	        var val = $('#target-specific:checked').length;
	        if(val) {
	            $('.target-row').show();
	        } else {
	            $('.target-row').hide();
	        }
	    } );

	    if ($('.select2').length && typeof select2 !== 'undefined') {
	        $(".select2").select2({
	            multiple: true,
	            placeholder: 'Select Country/ies',
	        });
	    }
    }
    modernFieldsUpdate();

	$('#language-selector').change( function() {
		var arr = window.location.pathname.split('/');
		arr[1] = $(this).val();
		window.location.href = window.location.origin + arr.join('/');
	});
	
	simpleCountDown = function() {
		clearInterval(simpleCountDownTO);

		simpleCountDownTO = setInterval( function() {
			var secs = parseInt($('.simple-countdown span').text());
			--secs;
			if(secs) {
				$('.simple-countdown span').html(secs);
			} else {
				$('.simple-countdown').html( $('.simple-countdown').attr('alt-text') ).removeClass('inactive');
				clearInterval(simpleCountDownTO);
			}
		}, 1000 );
	}

	hoursCountdown = function() {
		clearInterval(hoursCountdownTO);
		
		hoursCountdownTO = setInterval( function() {
			var arr = $('.hours-countdown span').text().split(':');
			var hours = parseInt(arr[0]);
			var mins = parseInt(arr[1]);
			var secs = parseInt(arr[2]);
			if(secs==0) {
				secs=59;
				if(mins==0) {
					mins = 23;
					if(hours==0) {
						clearInterval(hoursCountdownTO);
						window.location.reload();
						return;
					} else {
						--hours;				
					}
				} else {
					--mins;				
				}
			} else {
				--secs;			
			}

			secs = (secs+'').padStart(2, '0');
			mins = (mins+'').padStart(2, '0');
			$('.hours-countdown span').html(hours+':'+mins+':'+secs)
		}, 1000 );
	}

	showPopup = function(id, ret, e) {

		if(id=='download-stats-popup') {
			$('.popup:not(.download-stats-popup)').remove();
			$('#download-stats-popup').addClass('active');
        	$('body').addClass('popup-visible');
        	$('.loader-mask').hide();
		} else {
			
			$.ajax({
	            type: "POST",
	            url: window.location.origin+'/get-popup/',
	            data: {
	                id: id,
	                vox_id: $('#current-stats-vox').length ? $('#current-stats-vox').val() : '',
	            },
	            success: function(data) {
	                if(data) {
						$('.popup:not(.download-stats-popup)').remove();
	                	$('body').append(data);
	                	$('body').addClass('popup-visible');
	                	$('.loader-mask').hide();
	                	handleActivePopupFunctions();

	                	if($('.popup.active').attr('id') == 'poll-popup') {

	                		$('.poll-bubble').hide();

							if ($('#poll-popup').length && $('#poll-popup .poll-answers').hasClass('shuffle-answers')) {
						        var divs = $('#poll-popup .poll-answers').children().not(".dont-shuffle");

						        while (divs.length) {
						            $('#poll-popup .poll-answers').prepend(divs.splice(Math.floor(Math.random() * divs.length), 1)[0]);
						        }
						    }
	                		if(ret) {
	                			if(ret.show_poll) {
		                			$('.loader-mask').remove();
				                	$('#poll-popup .poll-answers').html('');
				                	$('#poll-popup').find('.poll-question').html(ret.title);
				                	$('#poll-popup').find('form').attr('action', ret.url);

				                	for (var i in ret.answers ) {
				                		$('#poll-popup .poll-answers').append('<label class="poll-answer '+(ret.answers[i].indexOf('#') > -1 ? 'dont-shuffle' : '')+'" for="ans-'+(parseInt(i) + 1)+'"><input type="radio" name="answer" class="answer" value="'+(parseInt(i) + 1)+'" id="ans-'+(parseInt(i) + 1)+'">'+(ret.answers[i].indexOf('#') > -1 ? ret.answers[i].substr(1) : ret.answers[i]) +'</label>');
				                	}

				                    if (ret.randomize_answers) {
				                        $('#poll-popup .poll-answers').addClass('shuffle-answers');
				                    } else {
				                        $('#poll-popup .poll-answers').removeClass('shuffle-answers');
				                    }

				                    if ($('#poll-popup .poll-answers').hasClass('shuffle-answers')) {
				                        var divs = $('#poll-popup .poll-answers').children().not(".dont-shuffle");

				                        while (divs.length) {
				                            $('#poll-popup .poll-answers').prepend(divs.splice(Math.floor(Math.random() * divs.length), 1)[0]);
				                        }
				                    }

				                	$('#poll-popup').find('.poll-stats-wrapper h3').html($('#poll-popup').find('.poll-stats-wrapper h3').attr('title'));
				                	$('#poll-popup').find('.content').hide();
				                	$('#poll-popup').find('.poll-form-wrapper').show();

				                	tooltipsFunction();
				                	pollStats();
				                	pollsFunction();

				                    var q = $('#poll-popup .poll-form-wrapper .poll-question').html();

				                    gtag('event', 'Click', {
				                        'event_category': 'DailyPollCallendar',
				                        'event_label': q+'-DailyPollQuestion',
				                    });

				                    if(ret.date_href) {
				                		cur_href = window.location.pathname;
				                   		history.pushState({}, null, '/en/daily-polls/'+ret.date_href);
				                	}
	                			} else if(ret.show_stats) {
	                				pollStats(ret.chart);
				                	$('#poll-popup').find('.poll-stats-wrapper h3').html($('#poll-popup').find('.poll-stats-wrapper h3').attr('alternative-title'));
				                    $('#poll-popup').find('.poll-question').html(ret.title);
				                    $('#poll-popup').find('.poll-stats-wrapper p').remove();
				                	$('#poll-popup').find('.content').hide();
				                	$('#poll-popup').find('.poll-stats-wrapper').show();
				                	pollsFunction();
				                    pollStatsAnimate();
				                    $('#poll-popup').find('.poll-stats-wrapper p').remove();
				                    if (ret.closed) {
				                        $('<p>'+ret.date+'</p>').insertAfter('.poll-stats-wrapper h3');

				                        if (!ret.has_user) {
				                            $('.get-reward-buttons').show();
				                            $('.sign').hide();
				                        }
				                    }
				                    $('.next-poll').removeClass('taken-all');
				                	if (!ret.next_poll) {
				                		$('.next-poll').addClass('taken-all');
				                	} else {
				                		$('.next-poll').attr('poll-id', ret.next_poll);
				                	}
				                	if ($('.next-stat').length) {
				                		$('.next-stat').attr('stat-id', ret.next_stat);
				                	}
				                	
				                	if(ret.date_href) {
				                		cur_href = window.location.pathname;
				                   		history.pushState({}, null, '/en/daily-polls/'+ret.date_href);
				                	}
	                			}
	                		}
	                	} else if($('.popup.active').attr('id') == 'request-survey-popup') {
	                		$('head').append('<link rel="stylesheet" type="text/css" href="'+window.location.origin+'/css/select2.min.css">');
	                		$.getScript(window.location.origin+'/js-vox/select2.min.js', function() {
	                			if ($('.select2').length) {
							        $(".select2").select2({
							            multiple: true,
							            placeholder: 'Select Country/ies',
							        });
							    }
	                		});
	                	}
	                }
	            },
	            error: function(data) {
	                console.log('error');
	            }
	        });
		}
	}

	var handleActivePopupFunctions =  function() {

		handlePopups();
	    modernFieldsUpdate();

		$('#request-survey-form').submit( function(e) {
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
		                	that[0].reset();
		                	if ($('.select2').length) {
		                		$('.select2').val(null).trigger('change').select2();
		                	}
		                	
		                   	$(this).find('.alert-success').show();
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

		$('.recommend-radio').change( function(e) {
			$('.recommend-radio').not(this).attr('disabled', 'disabled');
			//$(this).closest('.recommend-icons').find('label').removeClass('active');
			$(this).closest('label').addClass('active');
			$('#recommend-form').submit();
		});

		$('#recommend-button').click( function(e) {
			e.preventDefault();

			if ($('#recommend-description').val()) {
				$('#recommend-form').submit();
			} else {
				$('#recommend-description').addClass('has-error');
				$('#recommend-form').find('.alert-warning').show();
			}
		});

	    $('#recommend-form').submit( function(e) {
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

	                	if (data.recommend) {
	                		$(this).next().show();
	                		$(this).hide();
	                		$('#myVideoRecommend')[0].play();
	                	} else {
	                		if (data.description) {
	                			$(this).find('.hide-on-success').hide();	                	
		                   		$(this).find('.alert-success').show();
	                		} else {
	                			$(this).find('.hide-happy').show();
	                		}	                	
	                	}
	                }
	                ajax_is_running = false;
	            }).bind(that), "json"
	        );  
		} );

		$('.close-popup').click( function() {
			if($('.popup').hasClass('download-stats-popup')) {
				$('.stat[question-id="'+$('.popup').find('.demogr-inner').attr('inner')+'"]').after($('.popup').find('.demogr-inner'));
			}

			$('.popup:not(.download-stats-popup)').remove();
			if($('.download-stats-popup').length) {
				$('.download-stats-popup').removeClass('active');
			}
			$('body').removeClass('popup-visible');
		});
	}

	handlePopups = function(id) {
		var dataPopupClick = function(e) {
			showPopup( $(this).attr('data-popup'), null, e );
		}
		$('[data-popup]').off('click', dataPopupClick).click( dataPopupClick );

		$('.popup.close-on-shield').click( function(e) {
			if($(e.target).hasClass('popup')) {

				if ($(this).hasClass('daily-poll')) {
					if (cur_href) {
						history.pushState({}, null, $('#site-url').attr('url'));
						cur_href = null;
					}

					if($('#calendar').length) {
						calendarEvents($('#calendar').attr('month'), $('#calendar').attr('year'));
						calendarListEvents($('#calendar').attr('month'), $('#calendar').attr('year'));
					}
				}

				if($(this).hasClass('download-stats-popup')) {

					var attr = $(this).find('.demogr-inner').attr('scale');

					if (typeof attr !== typeof undefined && attr !== false) {
						$('.scale-stat-q[question-id="'+$(this).find('.demogr-inner').attr('inner')+'"][scale-answer-id="'+$(this).find('.demogr-inner').attr('scale')+'"]').after($(this).find('.demogr-inner'));
					} else {
						$('.stat[question-id="'+$(this).find('.demogr-inner').attr('inner')+'"]').after($(this).find('.demogr-inner'));
					}

					$('.demogr-inner').hide();

					$(this).find('.ajax-alert').remove();
				}

				if($(this).hasClass('warning') || $(this).hasClass('download-stats-popup') || $(this).hasClass('ban') ) {
					$(this).removeClass('active');
				} else {
					$('.popup:not(.ban):not(.warning):not(.first-test):not(.download-stats-popup)').remove();
				}
				$('body').removeClass('popup-visible');
			}
		} );

		$('.popup .closer').click( function() {
			if($(this).hasClass('inactive')) {
				return;
			}
			if($(this).closest('.popup').hasClass('ban')) {
				window.location.reload();
			}

			if ($(this).closest('.popup').hasClass('daily-poll')) {

				if (cur_href) {
					history.pushState({}, null, $('#site-url').attr('url'));
					cur_href = null;
				}

				if($('#calendar').length) {
					calendarEvents($('#calendar').attr('month'), $('#calendar').attr('year'));
					calendarListEvents($('#calendar').attr('month'), $('#calendar').attr('year'));
				}
			}

			if($(this).closest('.popup').hasClass('download-stats-popup')) {
				var id = $(this).closest('.popup').find('.demogr-inner').attr('inner');

				var attr = $(this).closest('.popup').find('.demogr-inner').attr('scale');
				if (typeof attr !== typeof undefined && attr !== false) {
					$('.scale-stat-q[question-id="'+$(this).closest('.popup').find('.demogr-inner').attr('inner')+'"][scale-answer-id="'+$(this).closest('.popup').find('.demogr-inner').attr('scale')+'"]').after($(this).closest('.popup').find('.demogr-inner'));
				} else {
					$('.stat[question-id="'+$(this).closest('.popup').find('.demogr-inner').attr('inner')+'"]').after($(this).closest('.popup').find('.demogr-inner'));
				}

				$('.demogr-inner').hide();
				$(this).closest('.popup').find('.ajax-alert').remove();
			}

			if($(this).closest('.popup').hasClass('warning') || $(this).closest('.popup').hasClass('download-stats-popup') || $(this).closest('.popup').hasClass('ban') ) {
				$(this).closest('.popup').removeClass('active');
			} else {
				$('.popup:not(.ban):not(.warning):not(.first-test):not(.download-stats-popup)').remove();
			}

			$('body').removeClass('popup-visible');
		} );

	}
	handlePopups();

    if(getUrlParameter('popup')) {
		showPopup( getUrlParameter('popup') );
	}

	if(getUrlParameter('dcn-gateway-type') && dentacoin_down) {
		showPopup('failed-popup');
	}

	tooltipsFunction = function() {

		// var tooltipSize = function(that) {
			//pri load tooltip with images na desktop bug
		// 	if(window.innerWidth > 768 && $('.tooltip-window').hasClass('tooltip-with-image')) {
	 //        	var y = that.offset().top + that.outerHeight() / 2 - $('.tooltip-window').outerHeight() / 2;
		//     	var x = that.offset().left + that.outerWidth() + 20;
	 //        } else {
		//     	var y = that.offset().top + that.outerHeight() + 10;
		//     	var x = that.offset().left + that.outerWidth() / 2 - $('.tooltip-window').outerWidth() / 2 ;
	 //        }

	 //        $('.tooltip-window').css('left', x );
	 //        $('.tooltip-window').css('top', y );
		// }

		var loadTooltipImage = function(elm) {
			var that = elm;

			$(".tooltip-window img").on('load', function(){
				if(window.innerWidth > 768) {

		        	var y = that.offset().top + that.outerHeight() / 2 - $('.tooltip-window').outerHeight() / 2;
			    	var x = that.offset().left + that.outerWidth() + 20;
				} else {
					//console.log(that.offset().left, that.outerWidth() / 2 , $('.tooltip-window').outerWidth() / 2);
					var y = that.offset().top + that.outerHeight() + 10;
		    		var x = that.offset().left + that.outerWidth() / 2 - $('.tooltip-window').outerWidth() / 2 ;
				}

		    	$('.tooltip-window').css('left', x );
	        	$('.tooltip-window').css('top', y );
			});
		}

		var closeTooltip = function() {
			$('.close-tooltip').click( function() {
				$('.tooltip-window').remove();
			});
		}

	    var handleTooltip = function(e) {
	    	$('body').append('<div class="tooltip-window" style="display:none;"></div>');

	    	if( $(this).closest('.no-mobile-tooltips').length ) {
	    		var that = $(this).closest('.no-mobile-tooltips');
	    	} else {
	    		var that = $(this).closest('.tooltip-text');
	    	}

	        $('.tooltip-window').html( $(this).closest('.tooltip-text').attr('text') );

	        var elem = $(this).closest('.tooltip-text');

	        if(elem.closest('.answer-radios-group').length) {
	        	var parent = elem.closest('h3');
	        } else {
	        	var parent = elem.closest('.question').length ? elem.closest('.question') : elem.closest('label');
	        }

	        if (parent.length && typeof parent.attr('tooltip-image') !== typeof undefined && parent.attr('tooltip-image') !== false) {
	        	$('.tooltip-window').addClass('tooltip-with-image');
	        	$('.tooltip-window').prepend('<img src="'+parent.attr('tooltip-image')+'"/>');

	        	if(window.innerWidth > 768) {
	        		$('.tooltip-window').prepend('<p>'+elem.text()+'</p>');
	        	} else {
	        		$('.tooltip-window').prepend('<p>'+elem.parent().find('span.tooltip-text').text()+'</p>');
	        		$('.tooltip-window').prepend('<span class="close-tooltip"></span>');
	        		closeTooltip();
	        	}
	        } else {
	        	$('.tooltip-window').removeClass('tooltip-with-image');
	        }

	        if(window.innerWidth > 768 && $('.tooltip-window').hasClass('tooltip-with-image')) {
	        	var y = that.offset().top + that.outerHeight() / 2 - $('.tooltip-window').outerHeight() / 2;
		    	var x = that.offset().left + that.outerWidth() + 20;
	        } else {
		    	var y = that.offset().top + that.outerHeight() + 10;
		    	var x = that.offset().left + that.outerWidth() / 2 - $('.tooltip-window').outerWidth() / 2 ;
	        }

	    	if($('.tooltip-window').hasClass('tooltip-with-image')) {
	    		loadTooltipImage(that);
	    	}

	        $('.tooltip-window').css('left', x );
	        $('.tooltip-window').css('top', y );
	        $('.tooltip-window').css('display', 'block');

	        $('.tooltip-window').removeClass('small-window');

	        if (that.closest('.question-pictures').length && window.innerWidth < 768 && window.innerWidth - $('.tooltip-window').outerWidth() - 20 < e.pageX ) {
	            $('.tooltip-window').css('left', window.innerWidth - $('.tooltip-window').outerWidth() - 20 );
	            $('.tooltip-window').addClass('small-window');
	        }

	        e.stopPropagation();

	        if ($(this).closest('.tooltip-text').hasClass('info-cookie')) {
	        	$('.tooltip-window').addClass('dark-tooltip');
	        } else {
	        	$('.tooltip-window').removeClass('dark-tooltip');
	        }

	    }

	    if (window.innerWidth < 768) {
	    	$('.no-mobile-tooltips').click( function() {
	    		$('.tooltip-window').remove();
	    	});
	    }
	    

	    if($('.tooltip-text').length) {

	        $('.tooltip-text').on('mouseover mousemove', function(e) {
	            if (window.innerWidth > 768) {
	                handleTooltip.bind(this)(e);
	            }
	        });

	        $('.tooltip-text').on('click', function(e) {
	            if (window.innerWidth < 768 && !$(this).hasClass('no-mobile-tooltips')) {
	                handleTooltip.bind(this)(e);
	            }
	        });

	        $('.tooltip-text').on('mouseout', function(e) {
	            $('.tooltip-window').remove();
	        });
	    }

	    $('.answer-mobile-tooltip').click( function(e) {
	    	e.stopPropagation();
	    	e.preventDefault();

	    	if($(this).hasClass('shown')) {
	    		$('.tooltip-window').remove();
	    	} else {
	    		
		    	if (window.innerWidth < 768 && !$(this).hasClass('no-mobile-tooltips')) {
		            handleTooltip.bind(this)(e);
		        }
	    	}

	    	$(this).toggleClass('shown');
	    });
	}

	tooltipsFunction();

    var handleCoinTooltip = function(e) {
    	var that = $(this);

        $('body').append('<div class="doublecoin-tooltip">\
			'+featured_coin_text+'\
			<span></span>\
		</div>');

    	var y = that.offset().top + that.outerHeight() - 100;
    	var x = that.offset().left;

        $('.doublecoin-tooltip').css('left', x );
        $('.doublecoin-tooltip').css('top', y );

        if ( window.innerWidth - $('.doublecoin-tooltip').innerWidth() - 20 < e.pageX ) {

    		$('.doublecoin-tooltip span').css('left', $('.doublecoin-tooltip').offset().left - $(window).innerWidth() + $('.doublecoin-tooltip').innerWidth() + 30 );
        	$('.doublecoin-tooltip').css('left', $(window).innerWidth() - $('.doublecoin-tooltip').innerWidth() - 20 );
    	
        } else {        	
        	$('.doublecoin-tooltip span').css('left', '20px' );
        }

        e.stopPropagation();

    }

    if($('.doublecoin').length) {
        $('.doublecoin').on('mouseover mousemove', function(e) {
            if (window.innerWidth > 768) {
                handleCoinTooltip.bind(this)(e);
            }
        });
        $('.doublecoin').on('click', function(e) {
        	e.preventDefault();
            if (window.innerWidth < 768) {
                handleCoinTooltip.bind(this)(e);
            }
        });
        $('.doublecoin').on('mouseout', function(e) {
            $('.doublecoin-tooltip').remove();
        });
    }

	if ($('body').hasClass('page-profile-redirect')) {

		var cnt = $('img.hide').length;
		var loaded = 0;
		$('img.hide').on('load error', function() {
	        loaded++;
			if(loaded==cnt) {
				$('.loader').fadeOut();
	        	$('.loader-mask').delay(350).fadeOut('slow');

				window.location.href = 'https://account.dentacoin.com/dentavox?platform=dentavox';
			}
	    });
	}

	//Daily Polls 

	pollStats = function(chart) {
		
        var max = 0;
        var total = 0;
        for(var i in chart) {
        	total+=parseInt(chart[i]);
            if(chart[i] > max) {
                max = parseInt(chart[i]);
            }
        }

        var container = $('#chart-poll');
        container.html('');

        var u = 0;
        for(var i in chart) {
        	u++;
            $(container).append('<div class="custombar-parent" sta="'+u+'" votes="'+(parseInt(chart[i])/total*100).toFixed(1)+'"><div class="group-heading">'+i+'</div></div>');
            var pl = 90*parseInt(chart[i])/max + 1;
            $(container).find('.custombar-parent[sta="'+u+'"]').append('<div class="custombar"><span stat-width="'+parseInt(pl)+'%"></span> '+(parseInt(chart[i])/total*100).toFixed(1)+'%</div>');
        }

        var list = container.children();

        list.sort(function(a, b) {
            if( parseInt($(a).attr('votes')) > parseInt($(b).attr('votes')) ) {
                return -1;
            } else if( parseInt($(a).attr('votes')) < parseInt($(b).attr('votes')) ) {
                return 1;
            }
        });

        list.each(function() {
            container.append(this);
        });
    }

    pollStatsAnimate = function() {
    	$('.custombar').each( function() {
    		$(this).find('span').css('width', $(this).find('span').attr('stat-width') );
    	});
    }

	showStats = function(poll_id) {
        
		var p_id = poll_id;

		$.ajax({
            type: "POST",
            url: window.location.origin+'/en/get-poll-stats/'+p_id,
            data: {
                _token: $('input[name="_token"]').val(),
            },
            dataType: 'json',
            success: function(ret) {
                if(ret.success) {
                	showPopup('poll-popup', ret);
                } else {
    				console.log('error');
                }
            },
            error: function(ret) {
                console.log('error');
            }
        });
	}

	showPoll = function(poll_id) {
		var p_id = poll_id;

		$.ajax({
            type: "POST",
            url: window.location.origin+'/en/get-poll-content/'+p_id,
            data: {
                _token: $('input[name="_token"]').val(),
            },
            dataType: 'json',
            success: function(ret) {
                if(ret.success) {
                	showPopup('poll-popup', ret);
                } else {
    				console.log('error');
                }
            },
            error: function(ret) {
                console.log('error');
            }
        });
	}

	pollsFunction = function() {

		$('.poll-answer .answer').change( function(e) {
			$(this).closest('form').submit();
		});

		$('.poll-form').submit( function(e) {
			e.preventDefault();

			if(ajax_is_running) {
	            return;
	        }

	        ajax_is_running = true;

	        var that = $(this);

	        $.post( 
	            $(this).attr('action'), 
	            $(this).serialize() , 
	            function( data ) {
	                if(data.success) {

	                	pollStats(data.chart);
	                	$('#poll-popup').find('.content').hide();
	                	that.closest('#poll-popup').find('.poll-stats-wrapper').show();
	                	pollStatsAnimate();
	                	$('.next-poll').removeClass('taken-all');
	                	if (!data.next_poll) {
	                		$('.next-poll').addClass('taken-all');
	                	} else {
	                		$('.next-poll').attr('poll-id', data.next_poll);
	                	}

						$('#poll-popup').find('.poll-stats-wrapper p').remove();
	                	$('<p>'+data.respondents+'</p>').insertAfter('.poll-stats-wrapper h3');
	                	if (!data.has_user) {
                            $('.get-reward-buttons').hide();
                            $('.sign').show();
                        }

                        $('.poll-bubble').addClass('hide-it');
	                } else {
	                	if (data.closed_poll) {
	                		$('#poll-popup').find('.content').hide();
	                		that.closest('.popup-inner').find('.poll-closed-wrapper').show();
	                		that.closest('.popup-inner').find('.poll-closed-wrapper .see-stats').attr('poll-id', data.closed_poll);
	                	} else if (data.chart) {
	                		pollStats(data.chart);
		                	$('#poll-popup').find('.content').hide();
		                	that.closest('#poll-popup').find('.poll-stats-wrapper').show();
		                	pollStatsAnimate();
		                	$('#poll-popup').find('.poll-stats-wrapper p').remove();
		                	$('<p>'+data.respondents+'</p>').insertAfter('.poll-stats-wrapper h3');
		                	if (!data.has_user) {
	                            $('.get-reward-buttons').hide();
	                            $('.sign').show();
	                        }
	                	} else {
	                		console.log('false'); 
	                	}
	                                    
	                }
	                ajax_is_running = false;

	                var q = $('#poll-popup .poll-form-wrapper .poll-question').html();

	                fbq('track', 'DV_DailyPoll');
	                gtag('event', 'Answer', {
		                'event_category': 'DailyPollAnswer',
		                'event_label': q+'-DailyPollQuestion',
		            });

	            }, "json"
	        );
		});

		$('.next-poll').click( function() {
			if ($(this).hasClass('taken-all')) {
        		$('#poll-popup .content').hide();
				$('#poll-popup .poll-taken-all-wrapper').show();
				
			} else {

				var poll_id = $(this).attr('poll-id');

				$.ajax({
		            type: "POST",
		            url: window.location.origin+'/en/get-poll-content/'+poll_id,
		            data: {
		                _token: $('input[name="_token"]').val(),
		            },
		            dataType: 'json',
		            success: function(ret) {
		                if(ret.success) {

		                	$('#poll-popup .poll-answers').html('');
		                	$('#poll-popup').find('.poll-question').html(ret.title);
		                	$('#poll-popup').find('form').attr('action', ret.url);
		                	for (var i in ret.answers ) {
		                		$('#poll-popup .poll-answers').append('<label class="poll-answer" for="ans-'+(parseInt(i) + 1)+'"><input type="radio" name="answer" class="answer" value="'+(parseInt(i) + 1)+'" id="ans-'+(parseInt(i) + 1)+'">'+ret.answers[i]+'</label>');
		                	}

		                	$('#poll-popup').find('.content').hide();
		                	$('#poll-popup').find('.poll-form-wrapper').show();
		                	tooltipsFunction();
		                	pollStats();
		                	pollsFunction();
		                } else {
		    				if (ret.all_polls_taken) {
		    					console.log('taken');
		    				}
		                }
		            },
		            error: function(ret) {
		                console.log('error');
		            }
		        });
			}
		});

		$('.answer-poll').click( function(e) {
			if($(this).hasClass('regenerate-poll-popup')) {
				showPoll($(this).attr('cur-poll-id'));
			} else {
        		cur_href = window.location.pathname;
           		history.pushState({}, null, '/en/daily-polls/'+$(this).attr('data-href'));
			}

			var q = $(this).attr('q');

        	gtag('event', 'Click', {
                'event_category': 'DailyPollPopup',
                'event_label': q+'-DailyPollQuestion',
            });
		});

		$('.next-stat').click( function() {
			showStats($(this).attr('stat-id'));
		});

		$('.see-stats').click( function() {
			showStats($(this).attr('poll-id'));
		});
	}
	pollsFunction();

	$('.close-bubble').click( function(e) {
		e.stopPropagation();

		if(window.innerWidth > 768) {
			$.ajax( {
				url: window.location.origin+'/en/hide-dailypoll',
				type: 'GET',
				dataType: 'json',
				success: function( data ) {
					console.log('success-hide');
					$(this).closest('.poll-bubble').addClass('small-bubble');
				},
				error: function(data) {
					console.log(data);
				}
			});
		}
		//tuk
		$(this).closest('.poll-bubble').addClass('small-bubble');
	});

	if (window.innerWidth <= 768) {
		$('.poll-bubble').addClass('small-bubble');
	}

	$('.small-bubble').click( function() {
		$(this).removeClass('small-bubble');
	});

	// if ($('.poll-bubble').length && window.innerWidth >= 768) {
	// 	$(window).on("scroll", function() {
	// 		if(!$('.poll-bubble').hasClass('hide-it')) {

	// 			var scrollHeight = $(document).height();
	// 			var scrollPosition = $(window).height() + $(window).scrollTop();
	// 			if ((scrollHeight - scrollPosition) / scrollHeight === 0) {
	// 			    $('.poll-bubble').fadeOut();
	// 			} else {
	// 				$('.poll-bubble').fadeIn();
	// 			}
	// 		}
	// 	});
	// }

	$('.country-dropdown').change( function() {

		if ($(this).attr('real-country') != '') {
			if ($(this).val() != $(this).attr('real-country')) {
				$(this).parent().parent().find('.ip-country').show();
			} else {
				$(this).parent().parent().find('.ip-country').hide();
			}
		}
	});

	if ($('.video-stats').length) {
		if (!$('.video-stats').hasClass('inited-video')) {
			$('.video-stats')[0].play();
		}
	}

	if( Cookies.get('first-login-recommendation') ) {
		showPopup('recommend-popup');
	}
    
    if ($('#myVideo').length) {
    	$('#myVideo')[0].removeAttribute("controls");
    }

	if ($('.daily-limit-reached').length) {
		hoursCountdown();
	}

    $('.open-str-link').click( function() {
    	window.open($(this).attr('href'), '_blank');
    });

    if(!dentacoin_down) {
	    $(document).on('dentistAuthSuccessResponse', async function ( event) {
	    	if(event.response_data.vox_ban) {
	    		window.location.href = $('#site-url').attr('url')+lang+'/banned/';
	    	} else {
	    		window.location.href = $('#site-url').attr('url');
	    	}
	    });

	    $(document).on('patientAuthSuccessResponse', async function ( event) {
	    	if(event.response_data.vox_ban) {
	    		window.location.href = $('#site-url').attr('url')+lang+'/banned/';
	    	} else {
	    		window.location.href = $('#site-url').attr('url');
	    	}
	    });
	}

    $('.check-welcome').click( function(e) {
    	e.preventDefault();

    	if (Cookies.get('first_test')) {
    		if(dentacoin_down) {
	    		e.stopImmediatePropagation();
	    		showPopup('failed-popup');
	    	} else {
    			$.event.trigger({type: 'openPatientRegister'});
    		}
    	} else {
    		window.location.href = $(this).attr('href');
    	}
    });

    $('.open-dentacoin-gateway').click(function(e) {
    	if(dentacoin_down && !user_id) {
    		e.stopImmediatePropagation();
    		showPopup('failed-popup');
    	}
    });

    if(typeof FB !== 'undefined') {
	    FB.CustomerChat.hideDialog();
    }

});

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