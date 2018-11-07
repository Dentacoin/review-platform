var time_filter = null;
var timeframe = [];
var gc_loaded = false;
var chart_colors = [
    '#4894af', 
    '#119c88', 
    '#32ddfa', 
    '#9dccc5', 
    '#4475a2',
    '#06b7d5',
    '#5ac8b8',
    '#85bacd',
    '#001f58',
    '#48f0af',
];

$(document).ready(function(){

    //All surveys

    $('.stats-cats a.cat').click( function() {
        $(this).toggleClass('active');
        $(this).next().toggleClass('active');
    });


    $('.sort-menu a').click( function() {
        if (!$(this).hasClass('active')) {
            $('.sort-menu a').removeClass('active');
            $(this).addClass('active');
        }

        handleFilters();
    } );


    $('#survey-search').on('change keyup', function() {
        handleSearch();
    });

    var handleFilters = function() {
        if(!$('.sort-menu a.active').length) {
            return;
        }

        $('#survey-search').val('');

        var sort = $('.sort-menu a.active').attr('sort');
        var wrapper = $('.stats-holder');
        var list = wrapper.children('.vox-stat');

        if (sort == 'featured') {
            list.hide().attr("found", 0);

            list.each( function() {
                if ($(this).attr('featured')=='1') {
                    $(this).show().attr("found", 1)
                }
            });

        } else {


            list.show().attr("found", 1);

            if(sort=='newest') {
                list.sort(function(a, b) {
                    if( parseInt($(a).attr('published')) > parseInt($(b).attr('published')) ) {
                        return -1;
                    } else {
                        return 1;
                    }
                });
            } else if(sort=='popular') {
                list.sort(function(a, b) {
                    if( parseInt($(a).attr('popular')) > parseInt($(b).attr('popular')) ) {
                        return -1;
                    } else {
                        return 1;
                    }
                });
            }

            list.each(function() {
                wrapper.append(this);
            });
        }
    }


    var handleSearch = function() {
        $('.sort-menu a.active').removeClass('active');

        $('.vox-stat').show().attr("found", 1);;

        if ($('#survey-search').val().length > 3) {
            $('.vox-stat').each( function() {
                if( $(this).find('h3').text().toLowerCase().indexOf($('#survey-search').val().toLowerCase()) == -1) {
                    $(this).hide().attr("found", 0);
                }
            });
        }

        if ( !$('.vox-stat:visible').length ) {
            $('#survey-not-found').show();
        } else {
            $('#survey-not-found').hide();
        }
    }

    //Individual Survey

    google.charts.load('current', {
        packages: ['corechart', 'bar', 'geochart'],
        mapsApiKey: 'AIzaSyCaVeHq_LOhQndssbmw-aDnlMwUG73yCdk'
    });
    google.charts.setOnLoadCallback(function() {
        console.log('GC loaded');
        gc_loaded = true;
        $('.stat.active').each( function() {
            reloadGraph(this);
        } );
    });

    var handleFilterChange  = function() {
        time_filter = $('.filters a.active').attr('filter');

        if(time_filter=='all') {
            timeframe = null;
        } else if(time_filter=='custom') {

        } else {
            if(time_filter=='last7') {
                var ms = new Date().getTime() - 86400000*7;
            } else if(time_filter=='last30') {
                var ms = new Date().getTime() - 86400000*30;
            } else if(time_filter=='last365') {
                var ms = new Date().getTime() - 86400000*365;
            }
            timeframe = new Date(ms);
        }

        if( $('.stat.restore-me').length ) {
            $('.stat.restore-me').removeClass('restore-me').addClass('active');
        }

        $('.stat.active').each( function() {
            reloadGraph(this);
        } )
    }

    $('.button-holder .text').click( function() {
        $('#date-from').val('');
        $('#date-to').val('');
        $('#custom-datepicker').DatePickerClear();
        //handleFilterChange();
    } ) 

    $('.filters a').click( function(e) {
        e.preventDefault();

        $('.filters a').removeClass('active');
        $(this).addClass('active');
        if( $(this).attr('filter')=='custom' ) {
            $('.filters-custom').show();

            if( !$('#custom-datepicker').attr('inited') ) {

                $('#custom-datepicker').DatePicker({
                    mode:"range", 
                    inline: true, 
                    calendars: 3,
                    starts: 1,
                    locale: {
                        "format": "MM/DD/YYYY",
                        "separator": " - ",
                        "applyLabel": "Show Stats",
                        "cancelLabel": "Cancel",
                        "fromLabel": "From",
                        "toLabel": "To",
                        "customRangeLabel": "Custom",
                        "weekLabel": "W",
                        "daysMin": [
                            "S",
                            "M",
                            "T",
                            "W",
                            "T",
                            "F",
                            "S"
                        ],
                        "months": [
                            "January",
                            "February",
                            "March",
                            "April",
                            "May",
                            "June",
                            "July",
                            "August",
                            "September",
                            "October",
                            "November",
                            "December"
                        ],
                        "monthsShort": [
                            "January",
                            "February",
                            "March",
                            "April",
                            "May",
                            "June",
                            "July",
                            "August",
                            "September",
                            "October",
                            "November",
                            "December"
                        ],
                        "firstDay": 2
                    },
                    onChange: function(data, elm) {
                        timeframe = data;
                        console.log(data);

                        var options = { 
                        //    weekday: 'long', 
                            year: 'numeric', 
                            month: 'short', 
                            day: 'numeric' 
                        };

                        var from = data[0].toLocaleDateString('en', options);
                        var to = data[1].toLocaleDateString('en', options);

                        $('#date-from').val(from);
                        if(from!=to) {
                            $('#date-to').val(to);                            
                        } else {
                            $('#date-to').focus();
                        }
                    }
                });

                $('.datepicker, .datepickerContainer').css({
                    height: 'auto',
                    width: 'auto'
                });

                $('.datepickerContainer').after( $('#datepicker-extras') );

                $('#custom-datepicker').attr('inited', '1')
            }
        } else {
            $('.filters-custom').hide();
            handleFilterChange();
        }
    } );

    $('#custom-dates-save').click(handleFilterChange);
    handleFilterChange();

    $('.stats .stat a.title').click( function() {
        if( $( '.stat.restore-me' ).length ) {
            return;
        }
        var stat = $(this).closest('.stat');
        if( !stat.hasClass('active') ) {
            reloadGraph( stat );
        } else {
            stat.removeClass('active');
        }
    } );

    $('.stat .scales a').click( function() {
        $(this).closest('.scales').find('a').removeClass('active');
        $(this).addClass('active');
        reloadGraph( $(this).closest('.stat') );
    } );

    $('.stat .nav').click( function() {
        var active = $(this).closest('.stat').find('.scales a.active');
        active.removeClass('active');
        if($(this).hasClass('nav-left')) {
            if(active.prev().length) {
                active.prev().addClass('active');
            } else {
                $(this).closest('.stat').find('.scales a').last().addClass('active');
            }
        } else {
            if(active.next().length) {
                active.next().addClass('active');
            } else {
                $(this).closest('.stat').find('.scales a').first().addClass('active');
            }
        }
        reloadGraph( $(this).closest('.stat') );
    });

    var reloadGraph = function( elm ) {
        if( !$(elm).is(':visible') || !gc_loaded ) {
            return;
        }
        console.log('Reload: ' + $(elm).find('a.title').text());


        var options = { 
        //    weekday: 'long', 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric' 
        };

        var phptimeframe = null;
        if(timeframe && timeframe.length==2) {
            phptimeframe = [ timeframe[0].toLocaleDateString('en-GB', options), timeframe[1].toLocaleDateString('en-GB', options) ];
        } else if(timeframe) {
            phptimeframe = timeframe.toLocaleDateString('en-GB', options);
        }

        var params = {
            timeframe: phptimeframe,
            question_id: $(elm).attr('question-id'),
            answer_id: $(elm).attr('answer-id'),
            scale: $(elm).find('.scales a.active').attr('scale')
        }
    
        $.post( 
            window.location.href, 
            params, 
            (function( data ) {
                console.log(data);
                if(!data.total) {
                    $('.stats .stat.active').removeClass('active').addClass('restore-me');
                    $('#daterange-error').show();
                    return;
                }
                $('#daterange-error').hide();


                $(this).addClass('active');

                var type = $(this).attr('stat-type');
                var scale = $(this).find('.scales a.active').attr('scale');
                var scale_name = $(this).find('.scales a.active').text();
                var legend = [];

                var rows = [];
                for(var i in data.main_chart) {
                    rows.push([i, data.main_chart[i]]);
                    legend.push(i);
                }

                var options = {};
                if(type=='dependency') {
                    options.slices = {};
                    options.slices[data.relation_info.answer] = {
                        offset: 0.2
                    };
                } else {
                    if(data.answer_id) {
                        options.slices = {};
                        options.slices[data.answer_id-1] = {
                            offset: 0.2
                        };
                    }
                }

                if( scale!='gender' ) {

                    options.chartArea = {
                        left:'10%',
                        top:'20%',
                        width:'80%',
                        height:'65%'
                    };
                }

                drawChart(rows, $(this).find('.main-chart')[0], options, true);

                $(this).find('.total-m').hide();
                $(this).find('.total-f').hide();
                $(this).find('.hint').hide();
                $(this).find('.map-hint').hide();

                $(this).find('.total-all b').html(data.total);



                if(type=='dependency') {
                    console.log('dependency');
                    var rows = [];

                    rows.push([ 'Answer', 'Respondents', { role: 'style' } ]);
                    var line = Object.values(data.second_chart);
                    var sum = line.reduce(function(a, b) { return a + b; }, 0);
                        
                    var j=0;
                    for(var i in data.second_chart) {
                        var arr = i.split(' ');
                        var newi = arr.join('\n\r');
                        rows.push([ newi, data.second_chart[i] ? data.second_chart[i]/sum : 0, chart_colors[j] ]);
                        j++;
                    }
                    console.log(rows);
                    var options = {
                        width: 750
                    }

                    drawColumns(rows, $(this).find('.second-chart')[0], options, data.relation_info.answer+1, true);
                    $(this).find('.hint').html( data.relation_info.question ).show();
                    $(this).find('.third-chart').html('');


                } else if(scale=='gender') {
                    var rows = [];
                    for(var i in data.second_chart) {
                        rows.push([i, data.second_chart[i]]);
                    }
                    drawChart(rows, $(this).find('.second-chart')[0], {
                        pieHole: 0.6,
                        width: 270,
                    });

                    var rows = [];
                    for(var i in data.third_chart) {
                        rows.push([i, data.third_chart[i]]);
                    }
                    drawChart(rows, $(this).find('.third-chart')[0], {
                        pieHole: 0.6,
                        width: 270,
                    });

                    setupLegend($(this).find('.legend'), legend, data.answer_id);

                    $(this).find('.total-m').show().find('b').html(data.totalm);
                    $(this).find('.total-f').show().find('b').html(data.totalf);
                } else if(scale=='country_id') {
                    var rows = [];
                    rows.push(['Country', 'Respondents']);
                    for(var i in data.second_chart) {
                        rows.push([i, data.second_chart[i]]);
                    }
                    //rows = rows.slice(0,25);
                    console.log(rows);
                    drawMap(rows, $(this).find('.second-chart')[0]);
                    $(this).find('.third-chart').html('');
                    setupLegend($(this).find('.legend'), legend, data.answer_id);
                    $(this).find('.hint').html('Click on a pie slice to see the geo spread of the respective answer.').show();
                    $(this).find('.map-hint').show();
                } else {

                    var rows = [];
                    var headers = [];
                    headers.push( scale_name );
                    j=0;
                    for(var i in data.second_chart[Object.keys(data.second_chart)[0]]) {
                        if(!data.answer_id || j==data.answer_id-1 ) {
                            headers.push(i);
                        }
                        j++;
                    }
                    rows.push(headers);

                    for(var i in data.second_chart) {
                        var line = Object.values(data.second_chart[i]);
                        var newline = [];
                        var sum = line.reduce(function(a, b) { return a + b; }, 0);
                        for(var j in line) {
                            if(!data.answer_id || j==data.answer_id-1 ) {
                                newline.push( line[j] ? line[j]/sum : 0 );
                            }
                        }

                        var arr = i.split(' ');
                        var newi = arr.join('\n\r');
                        newline.unshift(newi.trim());
                        rows.push( newline );
                    }   

                    console.log(rows);
                    drawColumns(rows, $(this).find('.second-chart')[0], null, data.answer_id);

                    $(this).find('.third-chart').html('');
                    setupLegend($(this).find('.legend'), legend, data.answer_id);
                    $(this).find('.hint').html('Click on a pie slice to see data only for the respective answer.').show();
                }

                $(this).find('.second-chart').attr('class', 'second-chart '+(type=='dependency' ? 'dependency' : scale) );
                $(this).find('.third-chart').attr('class', 'third-chart '+(type=='dependency' ? 'dependency' : scale) );

            }).bind(elm)
        );

    }

    var setupLegend = function(container, legend, answer) {
        container.html('');
        console.log( legend.length );
        for(var i in legend) {
            container.append( $('<div answer-id="'+(parseInt(i)+1)+'" class="'+(legend.length>5 ? 'short' : 'standard')+(answer && i!=(answer-1) ? ' inactive' : '')+'"><span style="background-color: '+chart_colors[i]+';"></span>'+legend[i]+'</div>') );
        }

        container.find('div').click( function() {
            var container = $(this).closest('.stat');
            container.attr('answer-id', $(this).attr('answer-id') );
            reloadGraph( container );            
        } )
    }


    var drawChart = function(rows, container, more_options, is_main) {
        
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Genders');
        data.addColumn('number', 'Answers');
        data.addRows(rows);

        // Set chart options
        var options = {
            backgroundColor: 'transparent',
            chartArea: {
                left:more_options.slices ? '15%' : '10%',
                top:more_options.slices ? '15%' : '10%',
                width:more_options.slices ? '70%' : '80%',
                height: more_options.slices ? '70%' : '80%'
            },
            colors: chart_colors,
            legend: {
                position: 'none'
            },
            width: 350,
            height: 300
        };

        if(more_options) {
            for(var i in more_options) {
                options[i] = more_options[i];
            }
        }

        if( $(window).width()<768 ) {
            options.width = $(container).closest('.graphs').innerWidth();
            options.height = $(container).closest('.graphs').innerWidth();
        } else if( $(window).width()<1200 ) {
            options.width = $(container).closest('.graphs').innerWidth()/2;
            options.height = $(container).closest('.graphs').innerWidth()/2;
        }
        

        // Instantiate and draw our chart, passing in some options.
        var chart = new google.visualization.PieChart( container );

        chart.draw(data, options);   

        if( is_main ) {
            google.visualization.events.addListener(chart, 'select', (function() {
                var selection = this.getSelection();
                console.log(selection);
                if( typeof selection[0].row!='undefined' ) {
                    var container = $(this.container).closest('.stat');
                    if(  container.attr('answer-id')==(selection[0].row + 1) ) {
                        container.removeAttr('answer-id');
                    } else {
                        container.attr('answer-id', selection[0].row + 1);                        
                    }
                    reloadGraph( container );
                }
            }).bind(chart));

            // The selection handler.
            // Loop through all items in the selection and concatenate
            // a single message from all of them.            
        }

    }

    var drawColumns = function(rows, container, more_options, fixedColor, dependency) {

        if( $(window).width()<1200 || dependency ) {
            $(container).html('<div class="mobile-chart"></div>');
            container = $(container).find('.mobile-chart');

            var width = $(window).width()<1200 ? $(container).closest('.graphs').innerWidth() : (more_options && more_options.width ? more_options.width : 540);

            $(container).css('width', width );
            if( typeof(rows[0][ rows[0].length-1 ])=='object' ) {

                var max = 0;
                for(var i=1; i<rows.length; i++) {
                    if(rows[i][1] > max) {
                        max = rows[i][1];
                    }

                }

                for(var i=1; i<rows.length; i++) {
                    $(container).append('<div class="group-heading">'+rows[i][0]+'</div>');
                    var pl = 80*rows[i][1]/max;
                    var color = fixedColor ? chart_colors[fixedColor-1] : rows[i][2];
                    $(container).append('<div class="custombar"> <span style="width: '+parseInt(pl)+'%; background-color: '+color+';"></span> '+parseInt(rows[i][1]*100)+'%</div>');
                }
                console.log(rows);

            } else {

                for(var i=1; i<rows.length; i++) {
                    $(container).append('<div class="group-heading">'+rows[i][0]+'</div>');
                    var max = 0;


                    for(var j=1; j<rows[i].length; j++) {

                        if(rows[i][j] > max) {
                            max = rows[i][j];
                        }
                    }
                    for(var j=1; j<rows[i].length; j++) {
                        var pl = 80*rows[i][j]/max;
                        var color = fixedColor ? chart_colors[fixedColor-1] : chart_colors[j-1];
                        if( typeof(rows[0][j])!='object' ) {
                            $(container).append('<div class="custombar"> <span style="width: '+parseInt(pl)+'%; background-color: '+color+';"></span> '+parseInt(rows[i][j]*100)+'%</div>');
                        }
                    }
                }
                console.log(rows);
                
            }

        } else {


            var biggerLabels = false;
            if( rows.length<=5 && $(window).width()>768 ) {
                biggerLabels = true;
                for(var i in rows) {
                    rows[i][0] = rows[i][0].replace(new RegExp('\n', 'g'), ' ').replace(new RegExp('\r', 'g'), ' ')
                }
            }

            var data = google.visualization.arrayToDataTable(rows);

            console.log(data);

            var options = {
                backgroundColor: 'transparent',
                chartArea: {
                    left:'10%',
                    top:'10%',
                    width:'80%',
                    height:'80%'
                },
                colors: fixedColor ? [ chart_colors[fixedColor-1] ] : chart_colors,
                legend: {
                    position: 'none'
                },
                width: 540,
                height: 260,
                vAxis: {
                    format: 'percent',
                    title: null,
                    textPosition: 'none',
                    slantedText: true,
                    slantedTextAngle:45,
                },
                hAxis: {
                    //format: 'percent',
                    title: null,
                    textPosition: 'none',
                    showTextEvery: 1, 
                    slantedText: true, 
                    slantedTextAngle: 90,
                    textStyle: { 
                        //color: 'red',
                        fontSize: biggerLabels ? 15 : 10,
                        // bold: <boolean>,
                        // italic: <boolean> 
                    }
                },
                axes: {
                     x: {
                         0: { 
                            side: 'bottom', 
                            label: "",
                            slantedText: true,
                            slantedTextAngle:45,
                            showTextEvery: 1, 
                        }
                     }
                }
            };

            if(more_options) {
                for(var i in more_options) {
                    options[i] = more_options[i];
                }
            }

            options.width = $(window).width()<768 ? $(container).closest('.graphs').innerWidth() : ( $(window).width()<1200 ? $(container).closest('.graphs').innerWidth() : options.width),
            options.height = $(window).width()<768 ? $(container).closest('.graphs').innerWidth() : ( $(window).width()<1200 ? $(container).closest('.graphs').innerWidth()/2 : options.height),
                

            console.log( options );
            var chart = new google.charts.Bar( container );
            //chart.draw(data, options);
            chart.draw(data, google.charts.Bar.convertOptions(options));
        }
    }



    var drawMap = function(rows, container) {

        var data = google.visualization.arrayToDataTable(rows);

        var options = {
            backgroundColor: 'transparent',
            chartArea: {
                left:'10%',
                top:'10%',
                width:'80%',
                height:'80%'
            },
            width: $(window).width()<768 ? $(container).closest('.graphs').innerWidth() : ( $(window).width()<1200 ? $(container).closest('.graphs').innerWidth() : 490),
            height: $(window).width()<768 ? $(container).closest('.graphs').innerWidth() : ( $(window).width()<1200 ? $(container).closest('.graphs').innerWidth()/2 : 260),
            colorAxis: {
                colors: ['#f5f5f5', '#333']
            },
            magnifyingGlass: {
                enable: true, 
                zoomFactor: 5.0
            }
        };

        var chart = new google.visualization.GeoChart(container);

        chart.draw(data, options);
    }


    $('.stats .stat:first-child').addClass('active');

    $('.copy-invite-link').click( function() {
        // var $temp = $("<input>");
        // $("body").append($temp);
        $('.select-me').select();
        document.execCommand("copy");
        $('.select-me').blur();        
    } );

    $('.social-share').click( function() {
        $('.share-popup').addClass('active');
    });

    $('.share-buttons .share').click( function() {
        console.log(document.title);
        var href = $(this).closest('.share-buttons').attr('data-href');
        if ($(this).parent().hasClass('fb')) {
            var url = 'https://www.facebook.com/dialog/share?app_id=1906201509652855&display=popup&href=' + escape(href);
        } else if ($(this).parent().hasClass('twt')) {
            var url = 'https://twitter.com/share?url=' + escape(href);
        } else if ($(this).parent().hasClass('google')) {
            var url = 'https://plus.google.com/share?url=' + escape(href);
        }
        window.open( url , 'ShareWindow', 'height=450, width=550, top=' + (jQuery(window).height() / 2 - 275) + ', left=' + (jQuery(window).width() / 2 - 225) + ', toolbar=0, location=0, menubar=0, directories=0, scrollbars=0');
    });

    $(window).resize( function() {
        $('.stat.active').each( function() {
            reloadGraph(this);
        } );
    } )
});