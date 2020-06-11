var VoxTest = {}
ajax_is_running = false;
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

	checkFilledDots = function( event, index) {
		var goods = new Array;
		var flickity = $('.flickity:visible');
		var missing = false;
		if( flickity.length ) {
			flickity.find('.answer-radios-group').each( function() {
	            if( $(this).find('.answer-radio.active-label').length ) {
	                goods.push(true);
	            } else {
	                goods.push(false);
	                missing = true;
	            }
	        } );
	        var i=0;
	        flickity.find('.flickity-page-dots .dot').each( function() {
	            if(goods[i]) {
	                $(this).addClass('filled');
	            } else {
	                $(this).removeClass('filled');
	            }
	            i++;
	        } );

	        if(!missing) {
				$('.question-group:visible .next-answer').show().trigger('click');
	        }
		}
	}

	VoxTest.handleNextQuestion = function() {
		$('#current-question-bar').css('width', ((vox.current / vox.count)*100)+'%');
		var mins = Math.ceil( (vox.count - vox.current)/6 );
		$('#current-question-num').html( mins<2 ? '<1' : '~'+mins );

		// if (welcome_vox) {
		// 	if( vox.current > (vox.count_real + welcome_vox_q_count) ) {
		// 		$('#dcn-test-reward-bonus').show();

		// 	}
		// } else {
		// 	if( vox.current > vox.count_real ) {
		// 		$('#dcn-test-reward-bonus').show();

		// 	}
		// }

		if(vox.current>1) {

			var answerd_q = 0;
			$('.question-group').each( function() {
				if( $(this).attr('data-answer') && $(this).attr('data-answer')!='0' && !$(this).attr('welcome') ) {
					answerd_q++;
				}
			});

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
				// console.log('doingAsl');
				// var old = parseInt( $('#bonus-question-reward').text().trim() );
				// $('#bonus-question-reward').html( old + vox.reward_single );

				// old = parseInt( $('.coins-test').first().text().trim() );
				// $('.coins-test').html( old + parseInt(vox.reward_single) );

			} else if( doingWelcome ) {
                            
                var w_answers = [];
                if($('#welcome_answ').length) {
                    $('.question-group[welcome=1]').each( function() {
                        var d_ans = $(this).attr('data-answer');

                        if (typeof d_ans !== typeof undefined && d_ans !== false) {
                            w_answers.push($(this).attr('data-id')+':'+$(this).attr('data-answer'));
                        }
                        
                    });
                    $('#welcome_answ').val(w_answers.join(';'));
                }
                
				console.log('doingWelcome');
				$('#current-question-reward').html( (vox.current / welcome_vox_q_count) * 100 );
				$('#dcn-test-reward-before').html('DCN: 100');
			} else { //Нормални
				
				console.log('normal');
				var reward = 0;
				if( $('body').hasClass('page-welcome-survey') ) {
					var reward_per_question = ( vox.reward / vox.count_real );
					reward = reward_per_question * answerd_q;
				} else {
					reward = vox.reward_single * answerd_q;					
				}
				reward += welcomeBonus;

				$('#current-question-reward').html( Math.round(reward) );
				$('#dcn-test-reward-before').hide();
				$('#dcn-test-reward-after').show();

				$('.coins-test').html( Math.round(reward) );
			}
		}

		//if (window.innerWidth < 768 && $('.question-group:visible').hasClass('scale')) {
		if ($('.question-group:visible').hasClass('scale')) {

			$('.question-group:visible .flickity').flickity({
				wrapAround: true,
				adaptiveHeight: true,
				draggable: false
			});

			$('.question-group:visible .flickity').on( 'select.flickity', checkFilledDots);
			$('.question-group:visible .next-answer').hide();
		}

		if($('.question-group:visible').hasClass('shuffle')) {
			var parent = $('.question-group:visible .answers');

			if(parent.hasClass('in-columns')) {
				parent.find('.answers-column').each( function() {
					var divs = $(this).children().not(".disabler-label");

				    while (divs.length) {
				        $(this).prepend(divs.splice(Math.floor(Math.random() * divs.length), 1)[0]);
				    }
				});
			} else {

			    var divs = parent.children().not(".disabler-label");

			    while (divs.length) {
			        parent.prepend(divs.splice(Math.floor(Math.random() * divs.length), 1)[0]);
			    }
			}
		}
	}
	
	if(typeof(vox)!='undefined') {
		VoxTest.handleNextQuestion();		
	}

	var urlParam = function(name){
	    var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
	    if(results) {
	    	return results[1];
	    } else {
	    	return 0;
	    }
	    
	}

	if(urlParam('testmode') !== 0 && urlParam('q-id') !== 0 ) {
		$('.question-group').hide();

		var question = $('.question-group-'+urlParam('q-id'));
		question.show();

		if(question.hasClass('shuffle')) {
			var parent = question.find('.answers');

			if(parent.hasClass('in-columns')) {
				question.find('.answers-column').each( function() {
					var divs = $(this).children().not(".disabler-label");

				    while (divs.length) {
				        $(this).prepend(divs.splice(Math.floor(Math.random() * divs.length), 1)[0]);
				    }
				});
			} else {
			    var divs = parent.children().not(".disabler-label");

			    while (divs.length) {
			        parent.prepend(divs.splice(Math.floor(Math.random() * divs.length), 1)[0]);
			    }
			}
		}
		if (question.hasClass('scale')) {

			question.find('.flickity').flickity({
				wrapAround: true,
				adaptiveHeight: true,
				draggable: false
			});

			question.find('.flickity').on( 'select.flickity', checkFilledDots);
			question.find('.next-answer').hide();
		}
	} else if ($('#bot-group').length) {
		$('.question-group').hide();
        $('#bot-group').show();
	} else if ($('.question-group[welcome=1]').length) {
        $('.question-group').hide();
        console.log('welcome-show');
        $('.question-group[welcome]').first().show();
    } else if($('.user-detail-question').length) {
        $('.question-group').hide();
        console.log('demographics-show');
        $('.user-detail-question').first().show();
    }


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

    $('.second-absolute').click( function(e) {
		$('html, body').animate({
        	scrollTop: $('.section-recent-surveys').offset().top
        }, 500);
	});

	function checkbox_active() {
		$('.input-checkbox').click( function(e) {
			if ($(this).closest('label').hasClass('active')) {
				$(this).closest('label').removeClass('active');
			} else {
				$(this).closest('label').addClass('active');
			}
		});
	}
	checkbox_active();

	$('input').focus( function() {
		$(this).removeClass('has-error');
	});

	$('.type-radio').change( function(e) {
		$(this).closest('.modern-radios').find('label').removeClass('active');
		$(this).closest('label').addClass('active');
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

	$('#language-selector').change( function() {
		var arr = window.location.pathname.split('/');
		arr[1] = $(this).val();
		window.location.href = window.location.origin + arr.join('/');
	});


	if( $('#header_questions').length ) {
		setInterval( function() {
			$.ajax( {
				url: '/question-count',
				type: 'GET',
				dataType: 'json',
				success: function( data ) {
					// console.log(data);
					var my_amount = parseInt($('#header-balance').html()) * data.dcn_price_full

					$('#header_questions').html(data.question_count);
					$('#header-rate').html(data.header_price);
					$('#header-change').html('('+data.dcn_change+'%)').css('color', parseFloat(data.dcn_change)>0 ? '#4caf50' : '#e91e63' );
					$('#header-usd').html( '$' + parseFloat(my_amount).toFixed(2) );
				}
			});

	    }, 10000 );
	}


    //Selects
    $(".select-me").on("click focus", function () {
        $(this).select();
    });

    $('.native-share').click( function() {
        navigator.share({
            title: $(this).attr('data-title'),
            url: $(this).attr('data-url')
        }).then( function() {
        	console.log('Successful share')
        })
        .catch(function(error) {
        	console.log('Error sharing:', error)
        });

    });

	$('.share').click( function() {
		var post_url = $(this).attr('data-url');
		var post_title = $(this).attr('data-title');
		if ($(this).hasClass('fb')) {
			var url = 'https://www.facebook.com/dialog/share?app_id=1906201509652855&display=popup&href=' + escape(post_url);
		} else if ($(this).hasClass('twt')) {
			var url = 'https://twitter.com/share?url=' + escape(post_url) + '&text=' + escape(post_title);
		} else if ($(this).hasClass('google')) {
			var url = 'https://plus.google.com/share?url=' + escape(post_url);
		}  else if ($(this).hasClass('messenger')) {
			var url = 'fb-messenger://share?link=' + encodeURIComponent(post_url);
		}
		window.open( url , 'ShareWindow', 'height=450, width=550, top=' + (jQuery(window).height() / 2 - 275) + ', left=' + (jQuery(window).width() / 2 - 225) + ', toolbar=0, location=0, menubar=0, directories=0, scrollbars=0');
	});

	$('.popup.close-on-shield').click( function(e) {
		if($(e.target).hasClass('popup')) {
			$(this).removeClass('active');
			$('body').removeClass('popup-visible');

			if ($(this).hasClass('daily-poll') && $('#calendar').length) {
				calendarEvents($('#calendar').attr('month'), $('#calendar').attr('year'));
				calendarListEvents($('#calendar').attr('month'), $('#calendar').attr('year'));
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
		}
	} );

	$('.popup .closer').click( function() {
		if($(this).hasClass('inactive')) {
			return;
		}
		if($(this).closest('.popup').hasClass('ban')) {
			window.location.reload();
		}
		$(this).closest('.popup').removeClass('active');
		$('body').removeClass('popup-visible');

		if ($(this).closest('.popup').hasClass('daily-poll') && $('#calendar').length) {
			calendarEvents($('#calendar').attr('month'), $('#calendar').attr('year'));
			calendarListEvents($('#calendar').attr('month'), $('#calendar').attr('year'));
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
	} );


	$('.popcircle .closer').click( function() {
		$(this).closest('.popcircle').removeClass('active');
	} );

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

	if( $(window).width()<768 ) {
		$('.header-a').click( function(e) {

			e.preventDefault();
			$(this).closest('.header-right').find('.expander-wrapper').addClass('active');
			return false;
		} );
	}

	showPopup = function(id, e) {
		if(id=='poll-popup') {
			$('.poll-bubble').hide();

			if ($('#poll-popup').length && $('#poll-popup .poll-answers').hasClass('shuffle-answers')) {
		        var divs = $('#poll-popup .poll-answers').children().not(".dont-shuffle");

		        while (divs.length) {
		            $('#poll-popup .poll-answers').prepend(divs.splice(Math.floor(Math.random() * divs.length), 1)[0]);
		        }
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
		$('.popup').removeClass('active');
		console.log('eee');
		if($('.popup').hasClass('download-stats-popup')) {
			console.log($('.popup').find('.demogr-inner').attr('inner'));
			console.log($('.stat[question-id="'+$('.popup').find('.demogr-inner').attr('inner')+'"]'));
			console.log($('.popup').find('.demogr-inner'));
			$('.stat[question-id="'+$('.popup').find('.demogr-inner').attr('inner')+'"]').after($('.popup').find('.demogr-inner'));
		}
	}

	$('.close-popup').click( function() {
		closePopup();
	});

	handlePopups = function(id) {
		var dataPopupClick = function(e) {
			showPopup( $(this).attr('data-popup'), e );
		}
		$('[data-popup]').off('click', dataPopupClick).click( dataPopupClick );

	}
	handlePopups();

    if(getUrlParameter('popup')) {
		showPopup( getUrlParameter('popup') );
	}

	if(getUrlParameter('dcn-gateway-type') && dentacoin_down) {
		showPopup('failed-popup');
	}

	tooltipsFunction = function() {

	    var handleTooltip = function(e) {

	    	if( $(this).closest('.no-mobile-tooltips').length ) {
	    		var that = $(this).closest('.no-mobile-tooltips');
	    	} else {
	    		var that = $(this).closest('.tooltip-text');
	    	}

	    	//console.log( $(this).closest('.tooltip-text').attr('text') );

	        $('.tooltip-window').text( $(this).closest('.tooltip-text').attr('text') );
	        $('.tooltip-window').css('display', 'block');

	    	var y = that.offset().top + that.outerHeight() + 10;
	    	var x = that.offset().left + that.outerWidth() / 2 - $('.tooltip-window').outerWidth() / 2 ;

	        $('.tooltip-window').css('left', x );
	        $('.tooltip-window').css('top', y );

	        // if ( window.innerWidth < 768 && window.innerWidth - $('.tooltip-window').outerWidth() - 20 < e.pageX ) {
	        //     $('.tooltip-window').css('left', window.innerWidth - $('.tooltip-window').outerWidth() - 20 );
	        // }

	        e.stopPropagation();

	        if ($(this).closest('.tooltip-text').hasClass('info-cookie')) {
	        	$('.tooltip-window').addClass('dark-tooltip');
	        } else {
	        	$('.tooltip-window').removeClass('dark-tooltip');
	        }

	    }

	    if (window.innerWidth < 768) {
	    	$('.no-mobile-tooltips').click( function() {
	    		$('.tooltip-window').hide();
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

	            $('.tooltip-window').hide();
	        });
	    }

	    $('.answer-mobile-tooltip').click( function(e) {
	    	e.stopPropagation();
	    	e.preventDefault();

	    	if (window.innerWidth < 768 && !$(this).hasClass('no-mobile-tooltips')) {
	            handleTooltip.bind(this)(e);
	        }
	    });
	}

	tooltipsFunction();

    var handleCoinTooltip = function(e) {
    	var that = $(this);

        $('.doublecoin-tooltip').css('display', 'block');

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
            $('.doublecoin-tooltip').hide();
        });
    }


    $('.close-explander').click( function() {
    	$(this).closest('.expander-wrapper').removeClass('active');
    });
    

    if ($('.strength-flickity').length) {
	    $('.strength-flickity').flickity({
	    	wrapAround: true,
			draggable: true,
			pageDots: false,
		});
	}

	$('.strength-button').click( function() {
		if ($(this).hasClass('active')) {
			$(this).removeClass('active');
			$('.strength-parent').removeClass('active');
		} else {
			$(this).addClass('active');
			$('.strength-parent').addClass('active');
		}
	});
	
	$('[event_category]').click( function() {
		gtag('event', $(this).attr('event_action'), {
            'event_category': $(this).attr('event_category'),
            'event_label': $(this).attr('event_label'),
        });
	});

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
                	
                	pollStats(ret.chart);
                	$('#poll-popup').find('.poll-stats-wrapper h3').html($('#poll-popup').find('.poll-stats-wrapper h3').attr('alternative-title'));
                    $('#poll-popup').find('.poll-question').html(ret.title);
                    $('#poll-popup').find('.poll-stats-wrapper p').remove();
                	$('#poll-popup').find('.content').hide();
                	$('#poll-popup').find('.poll-stats-wrapper').show();
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
                	$('#poll-popup').addClass('active');
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
                	$('#poll-popup').addClass('active');

                	tooltipsFunction();
                	pollStats();
                	pollsFunction();

                    var q = $('#poll-popup .poll-form-wrapper .poll-question').html();

                    gtag('event', 'Click', {
                        'event_category': 'DailyPollCallendar',
                        'event_label': q+'-DailyPollQuestion',
                    });
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

	$('.close-bubble').click( function() {
		if(window.innerWidth > 768) {
			$.ajax( {
				url: window.location.origin+'/en/hide-dailypoll',
				type: 'GET',
				dataType: 'json',
				success: function( data ) {
					console.log('success-hide');
				    ajax_is_running = false;
				},
				error: function(data) {
					console.log(data);
				    ajax_is_running = false;
				}
			});
		}
		//tuk
		$('.poll-bubble').addClass('hide-it');
	});

	if (window.innerWidth <= 768) {
		$('.poll-bubble').addClass('small-bubble');

		$('.small-bubble').click( function() {
			$(this).removeClass('small-bubble');
		});
	}

	if ($('.poll-bubble').length && window.innerWidth >= 768) {
		$(window).on("scroll", function() {
			if(!$('.poll-bubble').hasClass('hide-it')) {

				var scrollHeight = $(document).height();
				var scrollPosition = $(window).height() + $(window).scrollTop();
				if ((scrollHeight - scrollPosition) / scrollHeight === 0) {
				    $('.poll-bubble').fadeOut();
				} else {
					$('.poll-bubble').fadeIn();
				}
			}
		});
	}

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

	        // var countries = [];
	        // for (var i in $('[name="target-countries"]').select2('data')) {
	        // 	countries.push($('[name="target-countries"]').select2('data')[i]['text']);
	        // }

	        // $('[name="target-countries"]').val(countries.toString());
	        // console.log(countries.toString(), $('[name="target-countries"]').val());

	        //console.log($('[name="target-countries"]').val());
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
	    setTimeout( function() {
	    	FB.CustomerChat.showDialog();
	    }, 60000);	
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