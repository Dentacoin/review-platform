ajax_is_running = false;
var pollsFunction;
var pollStats;
var pollStatsAnimate;
var tooltipsFunction;
var calendarEvents;
var calendarListEvents;
var showStats;
var go_to_date;
var poll_stats;
var showPoll;

$(document).ready(function(){

    var calendarEl = document.getElementById('calendar');

    var clickOnDate = function(info) {
        if (poll_stats !== undefined && poll_stats) {
            showStats($(info.dayEl).attr('poll-id'));
        } else {
            if ( $(info.dayEl).attr('class').indexOf("to-take")!==-1 || $(info.dayEl).attr('class').indexOf("admin")!==-1  ) {
                showPoll($(info.dayEl).attr('poll-id'));
            } else {
                console.log($(info.dayEl));
                showStats($(info.dayEl).attr('poll-id'));
            }
        }
    }

    var calendar = new FullCalendar.Calendar(calendarEl, {
		plugins: [ 'interaction', 'dayGrid', 'list' ],
		header: {
			left: 'prev,next today',
			center: 'title',
			right: 'dayGridMonth,listMonth'
		},
		editable: false,
		dateClick: clickOnDate
    });


    calendar.render();

    var pad = function(str, max) {
	   str = str.toString();
	   return str.length < max ? pad("0" + str, max) : str;
	}


    calendarEvents = function(month, year) {

    	if(ajax_is_running) {
			return;
		}

		ajax_is_running = true;

        var c_date = calendar.getDate();
        if (!month) {
        	var month = c_date.toISOString().split('T')[0].split('-')[1];
        }
        if (!year) {
        	var year = c_date.toISOString().split('T')[0].split('-')[0];
        }
        
        var link = $('#calendar').attr('data-link');

    	$.ajax({
            type: "POST",
            url: link,
            data: {
                month: pad(month, 2),
                year: year,
                _token: $('input[name="_token"]').val(),
            },
            dataType: 'json',
            success: function(ret) {
                if(ret.success) {
                	if (ret.daily_polls) {
                		for (var i in ret.daily_polls) {
                			$('#calendar').find('.fc-bg td[data-date="'+ret.daily_polls[i].date+'"]').html('');
                			$('#calendar').find('.fc-content-skeleton td[data-date="'+ret.daily_polls[i].date+'"]').html('');
                			$('#calendar').find('.fc-bg td[data-date="'+ret.daily_polls[i].date+'"]').append('<a class="poll-day" href="javascript:;"></a>');
                			$('#calendar').find('.fc-bg td[data-date="'+ret.daily_polls[i].date+'"]').attr('poll-id', ret.daily_polls[i].id);
                			$('#calendar').find('.fc-bg td[data-date="'+ret.daily_polls[i].date+'"] .poll-day').append('<span class="fc-day-number">'+ret.daily_polls[i].day+'</span>');
                			$('#calendar').find('.fc-bg td[data-date="'+ret.daily_polls[i].date+'"] .poll-day').append('<img class="poll-image" src="'+ret.daily_polls[i].category_image+'"/>');
                			$('#calendar').find('.fc-bg td[data-date="'+ret.daily_polls[i].date+'"] .poll-day').append('<p class="poll-q">'+ret.daily_polls[i].title+'</p>');
                			$('#calendar').find('.fc-bg td[data-date="'+ret.daily_polls[i].date+'"]').css('background-color', ret.daily_polls[i].color);
                			if (ret.daily_polls[i].closed) {
                				$('#calendar').find('.fc-bg td[data-date="'+ret.daily_polls[i].date+'"] .poll-day').append('<img class="poll-stat-image" src="'+ret.daily_polls[i].closed_image+'"/>');
                				$('#calendar').find('.fc-bg td[data-date="'+ret.daily_polls[i].date+'"] .poll-day').append('<p class="butn check-stat">Results</p>');
                				$('#calendar').find('.fc-bg td[data-date="'+ret.daily_polls[i].date+'"]').addClass('stats');
                			} else {
	                			if (ret.daily_polls[i].to_take) {
	                				$('#calendar').find('.fc-bg td[data-date="'+ret.daily_polls[i].date+'"] .poll-day').append('<img class="poll-take-image" src="'+ret.daily_polls[i].to_take_image+'"/>');
	                				$('#calendar').find('.fc-bg td[data-date="'+ret.daily_polls[i].date+'"]').addClass('to-take');
	                				$('#calendar').find('.fc-bg td[data-date="'+ret.daily_polls[i].date+'"] .poll-day').append('<p class="butn answer">Answer</p>');
	                			} else if (ret.daily_polls[i].taken) {
                                    $('#calendar').find('.fc-bg td[data-date="'+ret.daily_polls[i].date+'"]').removeClass('to-take');
	                				$('#calendar').find('.fc-bg td[data-date="'+ret.daily_polls[i].date+'"] .poll-day').append('<img class="poll-stat-image" src="'+ret.daily_polls[i].closed_image+'"/>');
	                				$('#calendar').find('.fc-bg td[data-date="'+ret.daily_polls[i].date+'"] .poll-day').append('<img class="poll-taken-image" src="'+ret.daily_polls[i].taken_image+'"/>');
	                				$('#calendar').find('.fc-bg td[data-date="'+ret.daily_polls[i].date+'"] .poll-day').append('<p class="butn check-stat">Results</p>');
	                				$('#calendar').find('.fc-bg td[data-date="'+ret.daily_polls[i].date+'"]').addClass('stats');
	                			}
                			}

                            if (ret.daily_polls[i].scheduled) {
                                $('#calendar').find('.fc-bg td[data-date="'+ret.daily_polls[i].date+'"] .poll-day').append('<i class="fas fa-clock"></i>');
                                $('#calendar').find('.fc-bg td[data-date="'+ret.daily_polls[i].date+'"]').addClass('admin');
                            }

                		}
                	}

                    if(ret.monthly_descr) {
                        $('.monthly-description p').html(ret.monthly_descr);
                        $('.monthly-description').show();
                    } else {
                        $('.monthly-description').hide();
                    }

                    if (go_to_date !== undefined && go_to_date) {
                        clickOnDate({
                            dayEl: $('td[data-date="'+go_to_date+'"]')[0]
                        });
                        go_to_date = null;
                    }

                } else {
    				console.log('error');
                }
            },
            error: function(ret) {
                console.log('error');
            }
        });
        ajax_is_running = false;
    }

    if (go_to_date !== undefined && go_to_date) {
        calendar.gotoDate(go_to_date);
        calendarEvents(go_to_month, go_to_year);
    } else {

        if ($(window).innerWidth() >= 768) {
            calendarEvents();
        }
    }


    $('.fc-prev-button, .fc-next-button').click(function(){
    	var c_date = calendar.getDate();
    	var month = parseInt(c_date.toISOString().split('T')[0].split('-')[1]) + 1;
    	var year = c_date.toISOString().split('T')[0].split('-')[0];

    	if (month == 13) {
    		month = 1;
    		year = parseInt(year) + 1;
    	}

    	calendarEvents(month,year);
    	calendarListEvents(month,year);

        $('#calendar').attr('month', month);
        $('#calendar').attr('year', year);

	});

    $('.fc-dayGridMonth-button').click(function(){
        calendarEvents($('#calendar').attr('month'), $('#calendar').attr('year'));
        calendarListEvents($('#calendar').attr('month'), $('#calendar').attr('year'));
    });

    $('.fc-today-button').click(function(){
        calendarEvents();
        calendarListEvents();
    });

    calendarListEvents = function(month, year) {
    	if(ajax_is_running) {
			return;
		}

		ajax_is_running = true;

        var c_date = calendar.getDate();
        if (!month) {
        	var month = c_date.toISOString().split('T')[0].split('-')[1];
        }
        if (!year) {
        	var year = c_date.toISOString().split('T')[0].split('-')[0];
        }
        
        var link = $('#calendar').attr('data-link');

    	$.ajax({
            type: "POST",
            url: link,
            data: {
                month: pad(month, 2),
                year: year,
                _token: $('input[name="_token"]').val(),
            },
            dataType: 'json',
            success: function(ret) {
                if(ret.success) {
                	if (ret.daily_polls) {
                		$('.fc-list-empty-wrap1').html('');
                		for (var i in ret.daily_polls) {

                			if ($(window).innerWidth() >= 1080) {

	                			$('.fc-list-empty-wrap1').append('<div class="info-list"><span class="day-word">'+ret.daily_polls[i].day_word+'</span><span class="poll-full-date">'+ret.daily_polls[i].custom_date+'</span></div>');
	                			$('.fc-list-empty-wrap1').append('<a href="javascript:;" class="list-event" poll-id="'+ret.daily_polls[i].id+'" data-date="'+ret.daily_polls[i].date+'" style="background-color: '+ret.daily_polls[i].color+';"><img class="poll-image" src="'+ret.daily_polls[i].category_image+'"/>'+ret.daily_polls[i].title+'</a>');

                                var elem = $('.fc-list-empty-wrap1 .list-event[data-date="'+ret.daily_polls[i].date+'"]');
	                			
	                		} else {

	                			$('.fc-list-empty-wrap1').append('<a href="javascript:;" class="list-event" poll-id="'+ret.daily_polls[i].id+'" data-date="'+ret.daily_polls[i].date+'"><div class="mobile-poll-date"><span class="poll-day-word">'+ret.daily_polls[i].day_word_mobile+'</span><span class="poll-day-mobile">'+ret.daily_polls[i].day_mobile+'</span></div><div class="mobile-poll-content"  style="background-color: '+ret.daily_polls[i].color+';"><img class="poll-image" src="'+ret.daily_polls[i].category_image+'"/>'+ret.daily_polls[i].title+'</div></a>');

                                var elem = $('.fc-list-empty-wrap1 .list-event[data-date="'+ret.daily_polls[i].date+'"] .mobile-poll-content');
	                		}

                            if (ret.daily_polls[i].closed) {
                                elem.append('<img class="poll-stat-image" src="'+ret.daily_polls[i].closed_image+'"/>');
                                $('.fc-list-empty-wrap1 .list-event[data-date="'+ret.daily_polls[i].date+'"]').addClass('stats');
                            } else {
                                if (ret.daily_polls[i].to_take) {
                                    elem.append('<img class="poll-take-image" src="'+ret.daily_polls[i].to_take_image+'"/>');
                                    $('.fc-list-empty-wrap1 .list-event[data-date="'+ret.daily_polls[i].date+'"]').addClass('to-take');
                                } else if (ret.daily_polls[i].taken) {
                                    $('.fc-list-empty-wrap1 .list-event[data-date="'+ret.daily_polls[i].date+'"]').removeClass('to-take');
                                    elem.append('<img class="poll-stat-image" src="'+ret.daily_polls[i].closed_image+'"/>');
                                    elem.append('<img class="poll-taken-image" src="'+ret.daily_polls[i].taken_image+'"/>');
                                    $('.fc-list-empty-wrap1 .list-event[data-date="'+ret.daily_polls[i].date+'"]').addClass('stats');
                                }
                            }

                            if (ret.daily_polls[i].scheduled) {
                                $('.fc-list-empty-wrap1 .list-event[data-date="'+ret.daily_polls[i].date+'"]').append('<i class="fas fa-clock"></i>');
                                $('.fc-list-empty-wrap1 .list-event[data-date="'+ret.daily_polls[i].date+'"]').addClass('admin');
                            }
                		}
						//heights();

						setTimeout( function() {

							$('.fc-scroller').css('height', $('.fc-list-empty-wrap1').outerHeight());
                            console.log($('.fc-scroller').height());
						}, 100);


                        if (go_to_date !== undefined && go_to_date) {

                            if (poll_stats !== undefined && poll_stats) {
                                showStats($('.list-event[data-date="'+go_to_date+'"]').attr('poll-id'));
                            } else {
                                console.log(go_to_date);
                                $('.list-event[data-date="'+go_to_date+'"]').trigger('click');
                            }
                            
                            go_to_date=null;
                        }
                	}
                } else {
    				console.log('error');
                }
            },
            error: function(ret) {
                console.log('error');
            }
        });
        ajax_is_running = false;
    }


	$('.fc-listMonth-button').click( function() {

        if (go_to_date !== undefined && go_to_date) {
            calendarListEvents(go_to_month, go_to_year);
        } else {
            calendarListEvents($('#calendar').attr('month'), $('#calendar').attr('year'));
        }
	});

	$(window).off('click').click( function(e) {
		if( $(e.target).closest('.list-event').length ) {
			if ($(e.target).closest('.list-event').hasClass('to-take') || $(e.target).closest('.list-event').hasClass('admin')) {
				showPoll($(e.target).closest('.list-event').attr('poll-id'));
			} else {
				showStats($(e.target).closest('.list-event').attr('poll-id'));
			}
	    }
	} );

	if ($(window).innerWidth() <= 768) {
		$('.fc-listMonth-button').trigger('click');

        $(window).on('scroll', function() {
            $('.fc-scroller').css('height', $('.fc-list-empty-wrap1').outerHeight());
        });
	}
    

});