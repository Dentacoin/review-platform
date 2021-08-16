ajax_is_running = false;
var showStats;
var showPoll;

$(document).ready(function(){

    var initCalendar = function() {

        $('.style-calendar .chosen-list').click( function() {
            $('#poll-calendar').addClass('list-calendar');
        });

        $('.style-calendar .chosen-month').click( function() {
            $('#poll-calendar').removeClass('list-calendar');
        });

        $('.list-event, .poll-day').click( function() {
            if ($(this).hasClass('to-take') || $(this).hasClass('admin')) {
                showPoll($(this).attr('poll-id'));
            } else {
                showStats($(this).attr('poll-id'));
            }
        });
        
        $('.ajax-url').click( function(e) {
            e.preventDefault();

            if(ajax_is_running) {
	            return;
	        }

	        ajax_is_running = true;
    
            $.ajax({
                type: "GET",
                url: $(this).attr('href')+'&'+($('#poll-calendar').hasClass('list-calendar') ? 'list=1' : 'list=0' ),
                dataType: 'json',
                success: function(ret) {
                    console.log(ret);
                    if(ret.success) {
                        $('#append-calendar').html(ret.html);
                        initCalendar();

                        if(ret.monthly_descr) {
                            $('.monthly-description p').html(ret.monthly_descr);
                            $('.monthly-description').show();
                        } else {
                            $('.monthly-description').hide();
                        }
                    }
                }
            });

            ajax_is_running = false;
        })
    }

    initCalendar();

    
    

});