$(document).ready(function(){

    scrollToActive();

    $('.previous-question').click( function() {
        var cur = $('.statistics-block:visible').first();
        var cur_title = $('.all-titles .statistics-question:visible').first();
        var other = cur.prev().length ? cur.prev() : cur.parent().find('.statistics-block').last();
        var other_title = cur.prev().length ? cur_title.prev() : cur_title.parent().find('.statistics-question').last();
        cur.hide();
        cur_title.hide();
        other.show();
        other_title.show();
        moveQuestionBar();
    } );
    $('.next-question').click( function() {
        var cur = $('.statistics-block:visible').first();
        var cur_title = $('.all-titles .statistics-question:visible').first();
        var other = cur.next().length ? cur.next() : cur.parent().find('.statistics-block').first();
        var other_title = cur.next().length ? cur_title.next() : cur_title.parent().find('.statistics-question').first();
        cur.hide();
        cur_title.hide();
        other.show();
        other_title.show();
        moveQuestionBar();
    } );


    var nowTemp = new Date();
    var now = new Date(nowTemp.getFullYear(), nowTemp.getMonth(), nowTemp.getDate(), 0, 0, 0, 0);

    var checkin = $('#date-from').datepicker().on('changeDate', function(ev) {
        if (ev.date.valueOf() > checkout.date.valueOf()) {
            var newDate = new Date(ev.date)
            newDate.setDate(newDate.getDate() + 1);
            checkout.setValue(newDate);
        }
        checkin.hide();
        $('#date-to')[0].focus();
    }).data('datepicker');

    var checkout = $('#date-to').datepicker({
        onRender: function(date) {
            return date.valueOf() <= checkin.date.valueOf() ? 'disabled' : '';
        }
    }).on('changeDate', function(ev) {
        checkout.hide();
    }).data('datepicker');

    $('#stats-form').submit( function(e) {
        e.preventDefault();

        window.location.href = window.location.origin + window.location.pathname + '?start=' + $('#date-from').val() + '&end=' + $('#date-to').val() + '&country=' + $('#country').val() 
    } );



    $(window).resize( function() {
        Plotly.Plots.resize(document.getElementById('statistic'));
    });


    function moveQuestionBar() {
        var cur = $('.statistics-block:visible').first();
        var cnt = 1;
        var p = cur.prev();
        while( p.length ) {
            cnt++;
            p = p.prev();
        }

        var left = ( 100 / parseInt($('#current-question-bar').attr('data-count')) ) * (cnt-1);
        $('#current-question-bar').css('margin-left', left+'%');

        var data = chart_data[cur.attr('data-id')];
        console.log(data);

        var layout = {
            showlegend: false,
            xaxis: {
                range: ['{{ $start }}', '{{ $end }}'],
                type: 'date'
            },
            yaxis: {
                side: 'right'
            },
            margin: {
                l: 0,
                r: 40,
                b: 40,
                t: 0,
                pad: 0
            }
        };

        Plotly.newPlot('statistic', data, layout, {displayModeBar: false});
    }
    moveQuestionBar();
});