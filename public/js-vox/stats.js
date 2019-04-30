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

var main_chart_options;
var main_chart_data;
var map_country;
var map_country_data;

$(document).ready(function(){

    //All surveys

    $('.stats-cats a.cat').click( function() {
        $(this).toggleClass('active');
        $(this).next().toggleClass('active');
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

            list.sort(function(a, b) {
                if( parseInt($(a).attr('published')) > parseInt($(b).attr('published')) ) {
                    return -1;
                } else {
                    return 1;
                }
            });
            
            list.each( function() {
                if ($(this).attr('featured')=='1') {
                    $(this).show().attr("found", 1)
                }
            });

            list.each(function() {
                wrapper.append(this);
            });

        } else if (sort == 'all') {

            list.show().attr("found", 1);

            list.sort(function(a, b) {
                if( parseInt($(a).attr('sort-order')) < parseInt($(b).attr('sort-order')) ) {
                    return -1;
                } else {
                    return 1;
                }
            });

            list.each(function() {
                wrapper.append(this);
            });

        } else {

            list.show().attr("found", 1);

            if(sort=='newest') {
                list.sort(function(a, b) {
                    if( parseInt($(a).attr('sort-order')) < parseInt($(b).attr('sort-order')) ) {
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

            list.hide();
            list.slice( 0, 5 ).show();
        }
    }


    $('.sort-menu a').click( function(e) {
        if (!$(this).hasClass('active')) {
            $('.sort-menu a').removeClass('active');
            $(this).addClass('active');
        }

        window.location.hash = $(this).attr('sort');

        handleFilters();
        scroll_ribbon();
    } );

    if (window.location.hash.length && $('a[sort="'+window.location.hash.substring(1)+'"]').length) {
        $('a[sort="'+window.location.hash.substring(1)+'"]').trigger( "click" );
    }


    function scroll_ribbon() {
        if (window.location.hash == '#all') {
            if($(window).scrollTop() > $('.stats-holder .vox-stat:nth-of-type(3)').offset().top / 2) {
                $('body').addClass('scrolled-ribbon');
                $('body').css('padding-bottom', $('#stat-ribbon').outerHeight());
            } else {
                $('body').removeClass('scrolled-ribbon');
                $('body').css('padding-bottom', '0px');
            }
        }        
    }

    $(window).scroll(scroll_ribbon);


    $('#survey-search').on('change keyup', function() {
        handleSearch();
    });    


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
        packages: ['corechart', 'bar'],
    });
    google.charts.setOnLoadCallback(function() {
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

                main_chart_data = [];
                for(var i in data.main_chart) {
                    main_chart_data.push(data.main_chart[i]);
                    legend.push(data.main_chart[i][0]);
                }

                main_chart_options = {};
                if(type=='dependency') {
                    main_chart_options.slices = {};
                    main_chart_options.slices[data.relation_info.answer] = {
                        offset: 0.2
                    };

                    if( data.relation_info.question.length > 100 ) {
                        main_chart_options.with_long_hint = true;                        
                    }
                } else {
                    if(data.answer_id) {
                        main_chart_options.slices = {};
                        main_chart_options.slices[data.answer_id-1] = {
                            offset: 0.2
                        };
                    }
                }

                if( scale!='gender' ) {

                    main_chart_options.chartArea = {
                        left:'10%',
                        top:'20%',
                        width:'80%',
                        height:'65%'
                    };
                }

                //console.log('main chart data: ', main_chart_data);
                $(this).find('.main-chart').show();
                drawChart(main_chart_data, $(this).find('.main-chart')[0], main_chart_options, true);

                $(this).find('.total-m').hide();
                $(this).find('.total-f').hide();
                $(this).find('.hint').hide();
                $(this).find('.map-hint').hide();

                $(this).find('.total-all b').html(data.total);
                $(this).find('.total-all').show();



                if(type=='dependency') {
                    console.log('dependency');
                    var rows = [];

                    rows.push([ 'Answer', 'Respondents', { role: 'style' } ]);
                    var line = [];
                    for(var i in data.second_chart) {
                        line.push(data.second_chart[i][1]);
                    }
                    var sum = line.reduce(function(a, b) { return a + b; }, 0);
                        
                    var j=0;
                    for(var i in data.second_chart) {
                        var arr = data.second_chart[i][0].split(' ');
                        var newi = arr.join('\n\r');
                        rows.push([ newi, data.second_chart[i][1] ? data.second_chart[i][1]/sum : 0, chart_colors[j] ]);
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
                        rows.push(data.second_chart[i]);
                    }
                    drawChart(rows, $(this).find('.second-chart')[0], {
                        pieHole: 0.6,
                        width: 270,
                    });

                    var rows = [];
                    for(var i in data.third_chart) {
                        rows.push(data.third_chart[i]);
                    }
                    drawChart(rows, $(this).find('.third-chart')[0], {
                        pieHole: 0.6,
                        width: 270,
                    });

                    setupLegend($(this).find('.legend'), legend, data.answer_id);

                    $(this).find('.total-m').show().find('b').html(data.totalm);
                    $(this).find('.total-f').show().find('b').html(data.totalf);
                } else if(scale=='country_id') {
                    map_country = null;
                    map_country_data = null;
                    $(this).find('.main-chart').hide();
                    
                    var rows = [];
                    for(var i in data.second_chart) {
                        rows.push(data.second_chart[i]);
                    }
                    $(this).find('.second-chart').html('');
                    
                    $(this).find('.map-hint').html('Answers distribution by country').show();

                    setTimeout( (function() {
                        drawMap(this.rows, $(this.container).find('.second-chart')[0]);
                    }).bind({
                        rows: rows,
                        container: this
                    }), 100 );
                    
                    var total = main_chart_data.reduce(function(a, b) { return a + b[1]; }, 0);
                    console.log(total);
                    for(var i in main_chart_data) {
                        main_chart_data[i][1] = main_chart_data[i][1] / total;
                    }

                    main_chart_data.unshift(['', '']);
                    drawColumns( main_chart_data, $(this).find('.third-chart')[0], null, null, true);
                    
                    setupLegend($(this).find('.legend'), []);

                    $(this).find('.total-all').hide();
                } else {

                    var rows = [];
                    var headers = [];
                    headers.push( scale_name );
                    for(var i in data.second_chart[0]) {
                        if(i!=0 && (!data.answer_id || i==data.answer_id )) {
                            headers.push(data.second_chart[0][i][0]);
                        }
                    }
                    rows.push(headers);

                    for(var i in data.second_chart) {
                        var key = data.second_chart[i][0];
                        var line = [];
                        for(var j in data.second_chart[i]) {
                            if(j==0) {
                                continue;
                            }
                            line.push(data.second_chart[i][j][1]);
                        }
                        var newline = [];
                        var sum = line.reduce(function(a, b) { return a + b; }, 0);
                        for(var j in line) {
                            if(!data.answer_id || j==data.answer_id-1 ) {
                                newline.push( line[j] ? line[j]/sum : 0 );
                            }
                        }

                        var arr = key.toString().split(' ');
                        var newi = arr.join('\n\r');
                        newline.unshift(newi.trim());
                        rows.push( newline );
                    }

                    if( data.answer_id ) {
                        var total = 0;
                        for(var i in rows) {
                            if(i!=0) {
                                total += rows[i][1];
                            }
                        }

                        for(var i in rows) {
                            if(i!=0) {
                                rows[i][1] = rows[i][1] / total;
                            }
                        }
                    }

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
                top:more_options.slices ? (more_options.with_long_hint ? '20%' : '70%' ) : '10%',
                width:more_options.slices ? '70%' : '80%',
                height: more_options.slices ? (more_options.with_long_hint ? '60%' : '70%' ) : '80%'
            },
            colors: chart_colors,
            legend: {
                position: 'none'
            },
            width: 350,
            height: (more_options.with_long_hint ? 400 : 300)
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

            //Dependency + Answer ID
            var globalMax = null;
            if( rows[0].length==2 ) {
                var globalMax = 0 ;
                for(var i=1;i<rows.length;i++) {
                    if( rows[i][1] > globalMax) {
                        globalMax = rows[i][1];
                    }
                }
            }

            if( typeof(rows[0][ rows[0].length-1 ])=='object' ) {

                if( globalMax ) {
                    var max = globalMax;
                } else {
                    var max = 0;
                    for(var i=1; i<rows.length; i++) {
                        if(rows[i][1] > max) {
                            max = rows[i][1];
                        }

                    }
                }

                for(var i=1; i<rows.length; i++) {
                    $(container).append('<div class="group-heading">'+rows[i][0]+'</div>');
                    var pl = 80*rows[i][1]/max;
                    var color = fixedColor ? chart_colors[fixedColor-1] : rows[i][2];
                    $(container).append('<div class="custombar"> <span style="width: '+parseInt(pl)+'%; background-color: '+color+';"></span> '+(rows[i][1]*100).toFixed(1)+'%</div>');
                }
                console.log(rows);

            } else {

                for(var i=1; i<rows.length; i++) {
                    $(container).append('<div class="group-heading">'+rows[i][0]+'</div>');

                    if( globalMax ) {
                        var max = globalMax;
                    } else {
                        var max = 0;
                        for(var j=1; j<rows[i].length; j++) {
                            if(rows[i][j] > max) {
                                max = rows[i][j];
                            }
                        }
                    }

                    for(var j=1; j<rows[i].length; j++) {
                        var pl = 80*rows[i][j]/max;
                        var color = fixedColor ? chart_colors[fixedColor-1] : chart_colors[j-1];
                        if( typeof(rows[0][j])!='object' ) {
                            $(container).append('<div class="custombar"> <span style="width: '+parseInt(pl)+'%; background-color: '+color+';"></span> '+(rows[i][j]*100).toFixed(1)+'%</div>');
                        }
                    }
                }
                
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

            options.width = $(window).width()<768 ? $(container).closest('.graphs').innerWidth() : ( $(window).width()<1200 ? $(container).closest('.graphs').innerWidth() : options.width);
            options.height = $(window).width()<768 ? $(container).closest('.graphs').innerWidth() : ( $(window).width()<1200 ? $(container).closest('.graphs').innerWidth()/2 : options.height);
                

            //console.log( options );
            var chart = new google.charts.Bar( container );
            //chart.draw(data, options);
            chart.draw(data, google.charts.Bar.convertOptions(options));
        }
    }



    var drawMap = function(rows, container) {


        var map = am4core.create(container, am4maps.MapChart);
        map.geodata = am4geodata_worldLow;
        map.projection = new am4maps.projections.Projection(); //Equirectangular
        
        // Create map polygon series
        var polygonSeries = map.series.push(new am4maps.MapPolygonSeries());
        // Make map load polygon (like country names) data from GeoJSON
        polygonSeries.useGeodata = true;
        polygonSeries.tooltip.getFillFromObject = false;
        polygonSeries.tooltip.background.fill = am4core.color("#119c88");

        // Configure series
        var polygonTemplate = polygonSeries.mapPolygons.template;
        polygonTemplate.stroke = am4core.color("#333333");
        polygonTemplate.strokeWidth = 1;
        polygonTemplate.tooltipText = "{name}: {value}% ({count} resp.)";
        polygonTemplate.fill = am4core.color("#f3f3f3");

        polygonSeries.events.on("hit", (function(ev) {
            ev.target.mapPolygons.each(function(polygon) {
                polygon.setState("default");
            });
        }).bind(container));

        polygonSeries.mapPolygons.template.events.on("hit", (function(ev) {
            $(this).closest('.graphs').find('.map-hint').html('Answers distribution by country' ).show();
            if(!map_country || ev.target.tooltipDataItem.dataContext.name!=map_country) {
                ev.target.setState("highlight");
                map_country = ev.target.tooltipDataItem.dataContext.name;
                map_country_data = ev.target.dataItem.dataContext;           
                drawColumns( map_country_data.pieData, $(this).closest('.graphs').find('.third-chart')[0], null, 2, true);
                $(this).closest('.graphs').find('.map-hint').html('Answers distribution in <b>' + ev.target.tooltipDataItem.dataContext.name + '</b>' ).show();
            } else {
                ev.target.setState("default");
                map_country = null;
                map_country_data = null;
                drawColumns( main_chart_data, $(this).closest('.graphs').find('.third-chart')[0], null, null, true);
            }
        }).bind(container));
        
        polygonSeries.mapPolygons.template.events.on("over", (function(ev) {
            $(this).closest('.graphs').find('.map-hint').html('Answers distribution by country' ).show();
            if(map_country) {
                $(this).closest('.graphs').find('.map-hint').html('Answers distribution in <b>' + map_country + '</b>' ).show();
            }

            if( ev.target.dataItem.dataContext.pieData ) {
                if( ev.target.dataItem.dataContext.name!=map_country ) {
                    ev.target.setState("hovered");                    
                    $(this).closest('.graphs').find('.map-hint').html('Answers distribution in <b>' + ev.target.tooltipDataItem.dataContext.name + '</b>' ).show();
                }
                drawColumns( ev.target.dataItem.dataContext.pieData, $(this).closest('.graphs').find('.third-chart')[0], null, 2, true);
            }
        }).bind(container));

        polygonSeries.mapPolygons.template.events.on("out", (function(ev) {
            $(this).closest('.graphs').find('.map-hint').html('Answers distribution by country' ).show();
            if(map_country) {
                $(this).closest('.graphs').find('.map-hint').html('Answers distribution in <b>' + map_country + '</b>' ).show();
            }

            if( ev.target.dataItem.dataContext.name!=map_country ) {
                ev.target.setState("default");
            }

            
            drawColumns( map_country_data ? map_country_data.pieData : main_chart_data, $(this).closest('.graphs').find('.third-chart')[0], null, map_country ? 2 : null, true);
        }).bind(container));

        polygonSeries.mapPolygons.template.adapter.add("tooltipText", function(text, target, key) {
            if( target.tooltipDataItem.dataContext.value ) {
                return text;                
            } else {
                return 'No responses from {name}';                
            }
        });

        // Create hover state and set alternative fill color
        //var hoverState = polygonTemplate.states.create("hover");
        //hoverState.properties.fill = am4core.color("#111111");

        var hoverState = polygonTemplate.states.create("hovered");
        hoverState.propertyFields.fill = "hoverColor";

        var selectedState = polygonTemplate.states.create("highlight");
        selectedState.properties.fill = am4core.color("#119c88");

        // Remove Antarctica
        polygonSeries.exclude = ["AQ"];

        // Add some data
        var max = 0;
        var total = rows.reduce(function (a, b) {
            var rowTotal = b.reduce(function (c, d) {
                return parseInt(d[1]) ? c + d[1] : c;
            }, 0);
            if(rowTotal>max) {
                max = rowTotal;
            }
            return a + rowTotal;
        }, 0);
        var chartData = [];
        for(var i in rows) {
            var rowTotal = rows[i].reduce(function (c, d) {
                return parseInt(d[1]) ? c + d[1] : c;
            }, 0);


            //20 - 96
            //var lummax

            var rgb = hslToRgb(0, 0, 0.96 - (rowTotal/max)*0.76 ); // 20 == 100% // 96 == 0%

            var pieData = [
                ['', '']
            ];
            for(var j = 2; j < rows[i].length; j++) {
                pieData.push([ rows[i][j][0] , rows[i][j][1] / rowTotal ]);
            }
            //console.log( pieData );

            chartData.push({
                "id": rows[i][0], //rows[i][0],
                "name": rows[i][1][1],
                "value": (rowTotal/total*100).toFixed(1),
                "count": rowTotal,
                "fill": am4core.color(rgb),
                "hoverColor": am4core.color("#119c88"),
                "pieData": pieData,
            });
        }
        polygonSeries.data = chartData;

        // Bind "fill" property to "fill" key in data
        polygonTemplate.propertyFields.fill = "fill";

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


function hslToRgb(h, s, l) {
  var r, g, b;

  if (s == 0) {
    r = g = b = l; // achromatic
  } else {
    function hue2rgb(p, q, t) {
      if (t < 0) t += 1;
      if (t > 1) t -= 1;
      if (t < 1/6) return p + (q - p) * 6 * t;
      if (t < 1/2) return q;
      if (t < 2/3) return p + (q - p) * (2/3 - t) * 6;
      return p;
    }

    var q = l < 0.5 ? l * (1 + s) : l + s - l * s;
    var p = 2 * l - q;

    r = hue2rgb(p, q, h + 1/3);
    g = hue2rgb(p, q, h);
    b = hue2rgb(p, q, h - 1/3);
  }

  return "#" + componentToHex(r* 255) + componentToHex(g* 255) + componentToHex(b* 255);
}

function componentToHex(c) {
    c = Math.ceil(c);
    var hex = c.toString(16);
    return hex.length == 1 ? "0" + hex : hex;
}