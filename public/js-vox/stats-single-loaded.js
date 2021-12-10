var time_filter = null;
var timeframe = [];
var gc_loaded = false;
var top_answers_colors = [];
var chart_colors = [
    '#fe6e69', 
    '#42b9f3', 
    '#a87ce5', 
    '#e47ce5', 
    '#7c86e5',
    '#00c9b7',
    '#bbe9a9',
    '#7ce4e5',
    '#e5e47c',
    '#e5a87c',

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
    
    '#444444',
    '#97a3a3',
    '#0ecae9', 
    '#81e1f3', 
    '#e3c2c0', 
    '#8e7b6d', 
    '#22556e', 
    '#4799b7', 
    '#6db3bf', 
    '#94cfc9',
    '#88b6d7',
    '#3dbaba',
    '#28888c',
    '#444444',
    '#97a3a3',
    '#131a40',
    '#2142a6',
    '#3359a6',
    '#738cbf',
    '#c2d7f2',
    '#153641', 
];

var main_chart_options;
var main_chart_data;
var converted_rows;
var map_country;
var map_country_data;
var showPopup;
ajax_is_running = false;
var chartsToLoaded = 0;
var getUrlParameter;
var first_stat_loaded = false;
var is_safari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent);
var is_firefox = navigator.userAgent.toLowerCase().indexOf('firefox') > -1;
var open_download_popup = false;
var colors = [];
var load_js = false;
var user_id;
var reloadGraph;

// stats ordering answers logic:
// - no scale, no # -> answers in stats are ordered by number of respondents who said so
// - chosen scale - answers are ordered as in scale
// - # in some of the answers - they remain at the end always, as in the questions; the rest are ordered by number of respondents
// - # in all answers - keep the order as is, treat it as a scale

$(document).ready(function(){

    reloadGraph = function( elm ) {

        if( !$(elm).is(':visible') || !gc_loaded ) {
            return;
        }

        var options = { 
        //    weekday: 'long', 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric' 
        };

        if($(elm).parent().hasClass('st-download')) {
            timeframe = null;
        }

        var phptimeframe = null;
        if(timeframe && timeframe.length==2) {
            phptimeframe = [ timeframe[0].toLocaleDateString('en-GB', options), timeframe[1].toLocaleDateString('en-GB', options) ];
        } else if(timeframe) {
            phptimeframe = timeframe.toLocaleDateString('en-GB', options);
        }

        var count_scale_options = null;
        var scale_options_arr = [];
        if ($(window).outerWidth() <= 768) {
            $(elm).find('.mobile-scales input[name="scale-'+($(elm).find('.mobile-scales a.active').attr('scale'))+'[]"]:checked').each(function(){
                scale_options_arr.push($(this).val());
            });
            count_scale_options = $(elm).find('.mobile-scales input[name="scale-'+($(elm).find('.mobile-scales a.active').attr('scale'))+'[]"]').length;
        } else {

            $(elm).find('.box .scales input[name="scale-'+($(elm).find('.box .scales a.active').attr('scale'))+'[]"]:checked').each(function(){
                scale_options_arr.push($(this).val());
            });
            count_scale_options = $(elm).find('.box .scales input[name="scale-'+($(elm).find('.box .scales a.active').attr('scale'))+'[]"]').length;
        }

        var can_click_on_legend = count_scale_options == scale_options_arr.length;

        var chosen_answer = '';
        if ( !$(elm).closest('.st-download').length && $(elm).find('.scales:visible a.active').attr('scale') != 'country_id') {
            if ($(elm).attr('answer-id')) {
                if (can_click_on_legend) {
                    chosen_answer = $(elm).attr('answer-id');
                } else {
                    chosen_answer = '';
                }
                
            } else {
                if($(window).outerWidth() <= 992) {
                    if(!$(elm).hasClass('can-show-all')) {
                        if($(elm).hasClass('multipletop_ans')) {
                            chosen_answer = $(elm).find('.main-chart .custom-legend').first().attr('answer-id');
                        } else {
                            chosen_answer = 1;
                        }
                    }
                } else {
                    chosen_answer = $(elm).attr('answer-id');
                }
            }
        }
        var params = {
            timeframe: phptimeframe,
            question_id: $(elm).attr('question-id'),
            scale_answer_id: $(elm).attr('scale-answer-id') ? $(elm).attr('scale-answer-id') : '',
            answer_id: chosen_answer,
            scale: $(elm).find('.scales:visible a.active').attr('scale'),
            scale_name: $(elm).find('.scales:visible a.active').attr('scale-name'),
            scale_options: $(elm).is("[stat-dem-option]") ? $(elm).attr('stat-dem-option').split(',') : scale_options_arr,
        }

        $(elm).attr('cur-scale', $(elm).find('.scales:visible a.active').attr('scale'));
    
        $.post( 
            window.location.href, 
            params, 
            (function( data ) {
                if(!data.total) {
                    $(this).find('.graphs').hide();
                    $('#daterange-error').show();
                    $('input').prop('disabled', false);

                    if($(this).closest('.st-download').length) {
                        $(this).find('.st-daterange-error').show();
                    }
                } else {
                    var chartsToLoad = $(elm).closest('.st-download').length ? parseInt($(elm).closest('.st-download').attr('count-dems')) : '';

                    $(this).addClass('active');
                    $(this).find('.graphs').show();
                    $('#daterange-error').hide();

                    var type = $(this).attr('stat-type');
                    var scale = $(this).find('.scales:visible a.active').attr('scale');
                    var scale_name = $(this).find('.scales:visible a.active').text();
                    var legend = [];

                    main_chart_data_clone = $.extend(true, [], data.main_chart);

                    main_chart_data = [];
                    for(var i in data.main_chart) {
                        main_chart_data.push(data.main_chart[i]);
                        legend.push(data.main_chart[i][0]);
                    }

                    converted_rows = [];
                    for(var i in data.converted_rows) {
                        converted_rows.push(data.converted_rows[i]);
                    }


                    main_chart_options = {};
                    if(scale=='dependency') {
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

                    $(this).find('.main-chart').parent().show();
                    $(this).find('.main-chart').removeClass('main-multiple-gender');
                    $(this).find('.second-chart').parent().removeClass('country-legend');

                    if (($(this).find('.multiple-stat').length || data.related_question_type == 'multiple') && data.related_question_type != 'single' ) {
                        drawMultipleColumns(main_chart_data, $(this).find('.main-chart')[0], main_chart_options, data.total, data.multiple_top_answers, data.question_type);
                    } else {
                        drawChart(main_chart_data, $(this).find('.main-chart')[0], main_chart_options, true, can_click_on_legend, scale == 'dependency' ? true : data.vox_scale_id, scale == 'dependency' ? 'single_choice' : data.question_type, scale, data.available_dep_answer);
                    }

                    if($(window).outerWidth() <= 768 && $('.mobile-button-legend').length && scale!='country_id') {
                        $('.mobile-button-legend').removeClass('active');
                        $('.mobile-button-legend').show();
                        $('.legend.open').removeClass('open');
                    }

                    $(this).find('.chart-2').attr('chart-scale', scale);
                    $(this).find('.multiple-gender-nav').hide();
                    $(this).find('.multiple-stat').addClass('mobile-bottom-margin');
                    $(this).find('.total-gender').hide();
                    $(this).find('.total-m').hide();
                    $(this).find('.total-f').hide();
                    $(this).find('.hint').hide();
                    $(this).find('.map-hint').hide();
                    $(this).find('.chart-2').removeClass('chart-flex');
                    $(this).find('.chart-3').show();
                    $(this).find('.dependency-question').html('').hide();

                    $(this).find('.total-all b').html(data.total);
                    $(this).find('.total-all').show();
                    $(this).find('.chart-1').removeClass('with-relation-hint');
                    $(this).find('.second-chart').removeClass('verticle-align-chart')
                    $(this).find('.relation-hint').hide();

                    $(elm).find('.legend').show();

                    var multiple_columns = data.question_type == 'multiple_choice' || data.question_type == 'rank'  ? true : false;

                    if(scale=='dependency') {

                        drawDependencyColumns(converted_rows, $(this).find('.second-chart')[0], data.relation_info.answer+1, data.answer_id);
                        
                        if (($(this).find('.multiple-stat').length || data.related_question_type == 'multiple') && data.related_question_type != 'single' ) {
                            $(this).find('.chart-2').addClass('verticle-align-chart');
                            $(this).find('.relation-hint').html($(this).find('.relation-hint').attr('for-multiple'));

                            setupMultipleLegend($(this).find('.main-chart'), legend, data.answer_id, can_click_on_legend);
                        } else {
                            $(this).find('.relation-hint').html($(this).find('.relation-hint').attr('for-single'));
                        }

                        if(data.available_dep_answer) {
                            $(this).find('.relation-hint').hide();
                        } else {
                            $(this).find('.relation-hint').show();
                        }

                        $(this).find('.chart-1').addClass('with-relation-hint');
                        $(this).find('.hint').html( data.relation_info.question ).show();
                        $(this).find('.dependency-question').html( data.relation_info.current_question ).show();
                        $(this).find('.third-chart').html('');
                        $(elm).find('.legend').hide();

                    } else if(scale=='gender') {

                        if(multiple_columns || data.answer_id) {
                            $(this).find('.total-gender').show();
                        }
                        $(this).find('.total-m').show().find('b').html(data.totalm);
                        $(this).find('.total-f').show().find('b').html(data.totalf);

                        if(multiple_columns || data.answer_id) {
                            drawGenderColumns(main_chart_data, data.second_chart, data.third_chart, $(this).find('.second-chart')[0], options, data.totalf, data.totalm, scale_options_arr, data.answer_id, main_chart_data, data.multiple_top_answers, data.vox_scale_id, data.question_type, );
                        } else {
                            drawChart(data.second_chart, $(this).find('.second-chart')[0], {
                                pieHole: 0.6,
                                width: 270,
                            }, can_click_on_legend, null, data.vox_scale_id, data.question_type);
                        }

                        if ($(window).outerWidth() <= 768) {
                            $(this).find('.total.total-f').attr('for', $(this).find('.total.total-f').attr('custom-for') + '-1');
                            $(this).find('.total.total-m').attr('for', $(this).find('.total.total-m').attr('custom-for') + '-1');
                        }

                        if(multiple_columns) {
                            $(this).find('.legend').hide();
                        } else {
                            setupLegend(main_chart_data_clone, data.total ,$(this).find('.legend'), legend, data.answer_id, can_click_on_legend, data.vox_scale_id);
                        }

                        if((multiple_columns && $('.main-multiple-gender').length && data.answer_id) || (data.question_type != 'multiple_choice' && data.question_type != 'rank' && data.answer_id )) {

                            $(this).find('.main-multiple-gender').find('.custom-legend').addClass('inactive');
                            $(this).find('.main-multiple-gender').find('.custom-legend[answer-id="'+data.answer_id+'"]').removeClass('inactive');

                            drawAnswerGenderColumns($(this).find('.second-chart')[0], data.answer_id, data.question_type);

                            if ($(window).outerWidth() <= 768) {
                                if (multiple_columns) {
                                    $(this).find('.gender-text').text($(this).find('.custom-legend:not(.inactive)').find('.group-heading').text());
                                    $(this).find('.nav-color').css('background-color', $(this).find('.custom-legend:not(.inactive)').find('.custombar span').css('background-color'));
                                } else {
                                    $(this).find('.gender-text').text($(this).find('.legend-div:not(.inactive)').find('.legend-text').text());
                                    $(this).find('.nav-color').css('background-color', $(this).find('.legend-div:not(.inactive)').find('.legend-color').css('background-color'));
                                    $(this).find('.custom-legend').first().find('span').css('background-color', $(this).find('.legend-div:not(.inactive)').find('.legend-color').css('background-color'));
                                }

                                $(this).find('.multiple-gender-nav').css('display', 'flex');
                                $(this).find('.multiple-stat').removeClass('mobile-bottom-margin');
                            }

                            setupMultipleLegend($(this).find('.main-chart'), legend, data.answer_id, can_click_on_legend);
                        } 

                        if(multiple_columns || data.answer_id) {
                        } else {
                            drawChart(data.third_chart, $(this).find('.third-chart')[0], {
                                pieHole: 0.6,
                                width: 270,
                            }, can_click_on_legend, null, data.vox_scale_id, data.question_type);
                        }

                        if (data.question_type != 'multiple_choice' && data.question_type != 'rank' && data.answer_id) {
                            $(this).find('.chart-3').hide();
                        }

                        if(multiple_columns || data.answer_id) {
                            if (data.answer_id) {
                                $(this).find('.hint').html('').html('Click on an answer to see data for it or <a href="javascript:;" class="to-all">see all</a>').show();
                            } else {
                                $(this).find('.hint').html('').html('Click on an answer to see data for it.').show();
                            }
                        } else {
                            if (data.answer_id) {
                                $(this).find('.hint').html('').html('Click on a pie slice to see data <br/> only for the respective answer or <a href="javascript:;" class="to-all">see all</a>').show();
                            } else {
                                $(this).find('.hint').html('').html('Click on a pie slice to see data <br/> only for the respective answer.').show();
                            }
                        }

                        if (data.question_type!='multiple_choice' && data.question_type != 'rank' && data.answer_id) {
                            $('.chart-2').addClass('chart-flex');
                            $('.total-f').hide();
                            $(this).find('.total-gender .total-f').show();
                        }
                    } else if(scale=='country_id') {
                        map_country = null;
                        map_country_data = null;
                        $(this).find('.main-chart').parent().hide();
                        $(this).find('.second-chart').parent().addClass('country-legend');
                        $(this).find('.second-chart').html('');
                        $(this).find('.map-hint').html('Answers distribution by country').show();

                        setTimeout( (function() {
                            drawMap(this.rows, $(this.container).find('.third-chart')[0], data.question_type == 'multiple_choice' ? data.multiple_top_answers : null, data.question_type);
                        }).bind({
                            rows: data.second_chart,
                            container: this
                        }), 100 );

                        // if(data.question_type == 'rank') {
                        //     drawMultipleColumns(main_chart_data, $(this).find('.second-chart')[0], main_chart_options, data.total, data.multiple_top_answers, data.question_type);
                        // } else {
                            drawMapColumns( converted_rows, $(this).find('.second-chart')[0], data.question_type == 'multiple_choice' ? data.multiple_top_answers : null, data.question_type);
                        // }
                        
                        $('.mobile-button-legend').hide();
                        $(elm).find('.legend').hide();
                        $(this).find('.total-all').hide();
                    } else {

                        $(this).find('.third-chart').html('');
                        if(data.question_type != 'multiple_choice' && data.question_type != 'rank') {
                            setupLegend(main_chart_data_clone, data.total, $(this).find('.legend'), legend, data.answer_id, can_click_on_legend, data.vox_scale_id);
                            if (data.answer_id) {
                                $(this).find('.hint').html('').html('Click on a pie slice to see data <br/> only for the respective answer or <a href="javascript:;" class="to-all">see all</a>').show();
                            } else {
                                $(this).find('.hint').html('').html('Click on a pie slice to see data <br/> only for the respective answer.').show();
                            }
                        } else {
                            setupMultipleLegend($(this).find('.main-chart'), legend, data.answer_id, can_click_on_legend);
                            if (data.answer_id) {
                                $(this).find('.hint').html('').html('Click on an answer to see data for it or <a href="javascript:;" class="to-all">see all</a>').show();
                            } else {
                                $(this).find('.hint').html('').html('Click on an answer to see data for it.').show();
                            }
                            
                            $(elm).find('.legend').hide();
                        }

                        drawColumns(data.second_chart, $(this).find('.second-chart')[0], data.answer_id);
                    }

                    $(this).find('.second-chart').attr('class', 'second-chart '+(scale) );
                    $(this).find('.third-chart').attr('class', 'third-chart '+(scale) );

                    $('input').prop('disabled', false);

                    if ((multiple_columns && scale != 'country_id' && $(window).outerWidth() > 1200) || (data.question_type!='multiple_choice' && data.question_type != 'rank' && scale == 'gender' && data.answer_id) ) {
                        $(elm).find('.third-chart').parent().hide();
                        if (multiple_columns && scale != 'country_id' && $(window).outerWidth() > 1200) {
                            $(elm).find('.main-chart').parent().css('max-width', '33.3%');
                        }
                        
                    } else {
                        $(elm).find('.third-chart').parent().show();
                        $(elm).find('.main-chart').parent().css('max-width', 'auto');
                    }

                    $('.custom-legend').css('cursor', 'pointer');
                    removeCurrentAnswer();

                    if ($(window).outerWidth() <= 768 && data.multiple_top_answers && $(this).find('.main-chart .custom-legend').length && !$(this).hasClass('already-clicked')) {
                        $(this).addClass('already-clicked');
                        $(this).find('.main-chart .custom-legend').first().trigger('click');
                    }

                    $(this).find('.loader').fadeOut();
                    $(this).find('.loader-mask').delay(350).fadeOut('slow');

                    if ((is_safari || is_firefox) && !first_stat_loaded && getUrlParameter('download') && $('#download-link').hasClass('for-download')) {

                        window.location.href = $('#download-link').attr('href');
                        first_stat_loaded = true;
                    }
                    if ((is_safari || is_firefox) && !first_stat_loaded && getUrlParameter('download-png') && $('#download-link-png').hasClass('for-download')) {

                        window.location.href = $('#download-link-png').attr('href');
                        first_stat_loaded = true;
                    }

                    if(chartsToLoad && $('#make-stat-image-btn').length) {
                        chartsToLoaded++;

                        if(chartsToLoaded==chartsToLoad) {
                            setTimeout( function() {

                                console.log('click it');
                                $('#make-stat-image-btn').trigger('click');
                            }, 1000);                            
                        }
                    }
                }

            }).bind(elm)
        );
    }

    if(!is_safari && !is_firefox && getUrlParameter('download') && $('#download-link').hasClass('for-download')) {
        window.location.href = $('#download-link').attr('href');
    }

    if(!is_safari && !is_firefox && getUrlParameter('download-png') && $('#download-link-png').hasClass('for-download')) {
        window.location.href = $('#download-link-png').attr('href');
    }

    var handleFilterChange  = function() {
        time_filter = $('.filters a.active').attr('filter');

        if(time_filter=='all') {
            timeframe = null;
        } else if(time_filter=='custom') {
            if($('#date-from').val() && $('#date-to').val()) {

                var from = $('#date-from').val();
                var to = $('#date-to').val();

                var d_from = from.split('/')[0];
                var m_from = from.split('/')[1];
                var y_from = from.split('/')[2];

                var converted_date_from = m_from+'/'+d_from+'/'+y_from;

                var d_to = to.split('/')[0];
                var m_to = to.split('/')[1];
                var y_to = to.split('/')[2];

                var converted_date_to = m_to+'/'+d_to+'/'+y_to;

                var day_from = new Date(converted_date_from);
                var day_to = new Date(converted_date_to);

                timeframe = [];
                timeframe.push(day_from);
                timeframe.push(day_to);
            }
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
            $(this).find('.loader').fadeIn();
            $(this).find('.loader-mask').fadeIn();
            reloadGraph(this);
        } )
    }

    $('.button-holder .text').click( function() {
        $('#date-from').val('');
        $('#date-to').val('');
        // $('#custom-datepicker').DatePickerClear();
        $('#custom-datepicker').data('dateRangePicker').clear();
        //handleFilterChange();
    } );

    $('select[name="single-stat-filters"]').change( function() {
        $('.filters a[filter="'+$(this).val()+'"]').trigger('click');
    });

    $('.filters a').click( function(e) {
        e.preventDefault();

        $('.filters a').removeClass('active');
        $(this).addClass('active');
        if( $(this).attr('filter')=='custom' ) {

            var that = $(this);

            $.getScript(window.location.origin+'/js/moment.js', function() {
                $.getScript(window.location.origin+'/js/daterangepicker.min.js', function() {
                    $('head').append('<link rel="stylesheet" type="text/css" href="'+window.location.origin+'/css/daterangepicker.min.css">');

                    that.closest('.filters-wrapper').find('.filters-custom').show();

                    if( !$('#custom-datepicker').attr('inited') ) {
                        var start_date = $('#custom-datepicker').attr('launched-date');
                        var dateToday = new Date();

                        $('#custom-datepicker').dateRangePicker({
                            inline:true,
                            container: '#custom-datepicker', 
                            alwaysOpen: true ,
                            endDate: dateToday,
                            startDate: start_date,
                            minDays: 1,
                            singleMonth: $(window).outerWidth() > 768 ? false : true,
                            format: 'MMM DD, YYYY',
                            setValue: function(s,s1,s2) {
                                var day_from = new Date(s1);
                                var day_to = new Date(s2);

                                timeframe = [];
                                timeframe.push(day_from);
                                timeframe.push(day_to);

                                var options = { 
                                //    weekday: 'long', 
                                    year: 'numeric', 
                                    month: 'numeric', 
                                    day: 'numeric' 
                                };

                                var from = timeframe[0].toLocaleDateString('en', options);
                                var to = timeframe[1].toLocaleDateString('en', options);

                                var d_from = from.split('/')[1];
                                var m_from = from.split('/')[0];
                                var y_from = from.split('/')[2];

                                var converted_date_from = d_from+'/'+m_from+'/'+y_from;

                                var d_to = to.split('/')[1];
                                var m_to = to.split('/')[0];
                                var y_to = to.split('/')[2];

                                var converted_date_to = d_to+'/'+m_to+'/'+y_to;

                                $('#date-from').val(converted_date_from);
                                $('#date-to').val(converted_date_to);
                            }
                        });

                        $('.datepicker, .datepickerContainer').css({
                            height: 'auto',
                            width: 'auto'
                        });

                        $('.datepickerContainer').after( $('#datepicker-extras') );

                        $('#custom-datepicker').attr('inited', '1');
                        $('#custom-datepicker').parent().prepend('<a href="javascript:;" class="close-custom-datepicker"><img src="'+images_path+'/close-popup.png"/></a>');

                        $('.close-custom-datepicker').click( function(e) {
                            e.preventDefault();
                            that.closest('.filters-custom').hide();
                        });
                    }
                } );
            } );
        } else {
            $('.filters-custom').hide();
            handleFilterChange();
        }
    } );

    $('#custom-dates-save').click(handleFilterChange);
    handleFilterChange();

    $('.scale-checkbox').change( function(r) {
        r.preventDefault();
        r.stopPropagation();
        
        if ($(this).hasClass('select-all-scales')) {
            $(this).closest('.scales-filter').find('label').addClass('active');
            $(this).closest('.scales-filter').find('input').prop('checked', true);
        } else {
            // $(this).closest('.scales-filter').find('label').removeClass('active');
            // $(this).closest('.scales-filter').find('.scale-checkbox').prop('checked', false);
            // $(this).closest('label').addClass('active');
            // $(this).prop('checked', true);
            $(this).closest('label').toggleClass('active');
            //$(this).prop('checked', !$(this).prop("checked"));

            $(this).closest('.scales-filter').find('.select-all-scales').prop('checked', false);
            $(this).closest('.scales-filter').find('.select-all-scales-label').removeClass('active');

            if (!$(this).closest('.scales-filter').find('input:checked').length) {
                $(this).closest('.scales-filter').find('label').addClass('active');
                $(this).closest('.scales-filter').find('input').prop('checked', true);
            }
        }

        $(this).closest('.with-children').trigger('click');
        
    });

    if ($(window).outerWidth() > 992) {
        $('.with-children').on('mouseover mousemove', function(e) {
            $(this).addClass('open');
        });

        $('.with-children').on('mouseout', function(e) {
            $(this).removeClass('open');
        });
    }

    $(document).on('click touchstart', function(ev){
        if (!$(ev.target).closest('.scales').length) {
            $('.with-children').removeClass('open');
        }
    });

    var removeCurrentAnswer = function() {

        $('.hint .to-all').click( function() {
            if ($(window).outerWidth() <= 992) {

                $(this).closest('.stat').find('.loader').fadeIn();
                $(this).closest('.stat').find('.loader-mask').fadeIn();
                $(this).closest('.stat').addClass('can-show-all');
                // $('html, body').animate({
                //     scrollTop: $(this).closest('.stat').find('.mobile-scales').offset().top
                // }, 500);

                if($(this).closest('.stat').find('.legend:visible').length) {
                    if ($(this).closest('.stat').hasClass('two-clicks-triggerd')) {
                        $(this).closest('.stat').find('.legend').find('.legend-div:not(.inactive)').trigger('click');
                    } else {
                        $(this).closest('.stat').find('.legend').find('.legend-div:not(.inactive)').trigger('click').trigger('click');
                        $(this).closest('.stat').addClass('two-clicks-triggerd');
                    }
                } else {
                    if ($(this).closest('.stat').hasClass('two-clicks-triggerd')) {
                        $(this).closest('.stat').find('.main-chart').find('.custom-legend:not(.inactive)').trigger('click');
                    } else {
                        $(this).closest('.stat').find('.main-chart').find('.custom-legend:not(.inactive)').trigger('click').trigger('click');
                        $(this).closest('.stat').addClass('two-clicks-triggerd');
                    }                    
                }
            } else {
                if($(this).closest('.stat').find('.legend:visible').length) {
                    $(this).closest('.stat').find('.legend').find('.legend-div:not(.inactive)').trigger('click');
                } else {
                    $(this).closest('.stat').find('.main-chart').find('.custom-legend:not(.inactive)').trigger('click');
                }
            }
        });
    }
    removeCurrentAnswer();

    $('.stat .scales a').click( function(e) {

        if (!$(e.target).closest('.scales-filter').length) {
            if ($(window).outerWidth() <= 992) {
                
                if ($(this).hasClass('with-children')) {
                    $(this).closest('.scales').find('.with-children').not(this).removeClass('open');
                    $(this).addClass('open');
                    //$(this).toggleClass('open');
                } else {
                    $(this).closest('.scales').find('a').removeClass('open');
                }
            } 

            // if ($(this).hasClass('with-children')) {
            //     $(this).toggleClass('open');
            // }

            //not do graph
            // if( $('.loader-mask:visible').length) {
            //     return;
            // }
            
            $(this).closest('.scales').find('a').removeClass('active');
            $(this).addClass('active');
            $(this).closest('.stat').find('.loader').fadeIn();
            $(this).closest('.stat').find('.loader-mask').fadeIn();
            $('input').prop('disabled', true);
            reloadGraph( $(this).closest('.stat') );
        }
    } );

    $('.mobile-button-legend').click( function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).closest('.graphs').find('.legend').toggleClass('open');
        $(this).toggleClass('active');
    });

    $('.gender-nav-right').click( function() {
        if ($(this).closest('.graphs').hasClass('multiple-stat')) {

            if ($(this).closest('.graphs').find('.main-multiple-gender .custom-legend:not(.inactive)').nextAll('.custom-legend:visible').first().length) {
                $(this).closest('.graphs').find('.main-multiple-gender .custom-legend:not(.inactive)').nextAll('.custom-legend:visible').first().trigger('click');
            } else {
                $(this).closest('.graphs').find('.main-multiple-gender .custom-legend:visible').first().trigger('click');
            }
        } else {
            if ($(this).closest('.graphs').find('.legend-div:not(.inactive)').next().length) {
                $(this).closest('.graphs').find('.legend-div:not(.inactive)').next().trigger('click');
            } else {
                $(this).closest('.graphs').find('.legend-div:visible').first().trigger('click');
            }
        }
    });

    $('.gender-nav-left').click( function() {
        if ($(this).closest('.graphs').hasClass('multiple-stat')) {
            if ($(this).closest('.graphs').find('.main-multiple-gender .custom-legend:not(.inactive)').prevAll('.custom-legend:visible').length) {
                $(this).closest('.graphs').find('.main-multiple-gender .custom-legend:not(.inactive)').prevAll('.custom-legend:visible').trigger('click');
            } else {
                $(this).closest('.graphs').find('.main-multiple-gender .custom-legend:visible').last().trigger('click');
            }
        } else {
            if ($(this).closest('.graphs').find('.legend-div:not(.inactive)').prev().length) {
                $(this).closest('.graphs').find('.legend-div:not(.inactive)').prev().trigger('click');
            } else {
                $(this).closest('.graphs').find('.legend-div:visible').last().trigger('click');
            }
        }
    });

    if ($('.share-buttons').length && $(window).outerWidth() <= 768) {
        $('.share-buttons').each( function() {
            $(this).closest('.stat').find('.graphs').prepend($(this));
        });
    }

    var setupLegend = function(rows, totalCount, container, legend, answer, can_click_on_legend, vox_scale_id) {

        container.html('');
        if(container.hasClass('more-q-legend')) {
            container.append('<div class="col f-c"></div><div class="col s-c"></div><div class="col t-c"></div>');
        }

        var count = rows.length;
        var middle = Math.ceil(count/3);
        var last = Math.ceil((count/3)*2);

        for(var i=0; i<rows.length; i++) {
            var max = 0;
            for(var j=1; j<rows[i].length; j++) {
                if(rows[i][j] > max) {
                    max = rows[i][j];
                }
            }
            container.append( $('<div l-id="'+i+'" answer-id="'+(parseInt(i)+1)+'" class="legend-div '+(rows.length>5 ? 'short' : 'standard')+(answer && i!=(answer-1) ? ' inactive' : '')+'" legend-text=""><span class="legend-text" style="display:none;">'+rows[i][0]+'</span><span class="mobile-percentage"></span><span class="legend-color" style="background-color: '+chart_colors[i]+';"></span>'+rows[i][0]+'</div>') );
            
            if ($(window).outerWidth() <= 768) {

                for(var j=1; j<rows[i].length; j++) {
                    $(container).find('.legend-div[l-id="'+i+'"] .mobile-percentage').append(((rows[i][j]/ totalCount )*100).toFixed(1)+'%');
                }
            }
        }

        container.find('.legend-div').click( function() {
            var container = $(this).closest('.stat');
            if (container.attr('answer-id') == $(this).attr('answer-id')) {
                container.attr('answer-id', '' );
            } else {
                container.attr('answer-id', $(this).attr('answer-id') );
            }

            if (!can_click_on_legend) {
                container.closest('.stat').find('.scales-filter label').addClass('active');
                container.closest('.stat').find('.scales-filter input').prop('checked', true);
            }
            reloadGraph( container );            
        } );
    }

    var setupMultipleLegend = function(container, legend, answer, can_click_on_legend) {

        for(var i in legend) {
            var cl = container.find('.custom-legend[answer-id="'+(parseInt(i)+1)+'"]');
            if (answer && i!=(parseInt(answer)-1)) {
                container.find('.custom-legend').addClass('inactive')
                container.find('.custom-legend[answer-id="'+(parseInt(answer))+'"]').removeClass('inactive')
            }
        }

        container.find('.custom-legend').click( function() {
            var container = $(this).closest('.stat');
            if (container.attr('answer-id') == $(this).attr('answer-id')) {
                container.attr('answer-id', '' );
            } else {
                container.attr('answer-id', $(this).attr('answer-id') );
            }
            if (!can_click_on_legend) {
                container.closest('.stat').find('.scales-filter label').addClass('active');
                container.closest('.stat').find('.scales-filter input').prop('checked', true);
            }
            reloadGraph( container );            
        } );

    }

    var drawChart = function(rows, container, more_options, is_main, can_click_on_legend, vox_scale_id, question_type, scale, available_dep_answer=null) {
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Genders');
        data.addColumn('number', 'Answers');
        data.addRows(rows);
        
        // Set chart options
        var options = {
            backgroundColor: 'transparent',
            chartArea: {
                left:more_options.slices ? '15%' : '10%',
                top:more_options.slices ? (more_options.with_long_hint ? '20%' : '10%' ) : '10%',
                width:more_options.slices ? '70%' : '80%',
                height: more_options.slices ? (more_options.with_long_hint ? '60%' : '70%' ) : '80%'
            },
            colors: chart_colors,
            legend: {
                position: 'none'
            },
            width: 350,
            height: (more_options.with_long_hint ? 400 : 300),
        };

        if(more_options) {
            for(var i in more_options) {
                options[i] = more_options[i];
            }
        }

        if(!$(container).closest('.st-download').length) {
            if( $(window).width()<768 ) {
                options.width = $(container).closest('.graphs').innerWidth();
                options.height = $(container).closest('.graphs').innerWidth();
            } else if( $(window).width()<1200 ) {
                options.width = $(container).closest('.graphs').innerWidth()/2;
                options.height = $(container).closest('.graphs').innerWidth()/2;
            }
        }
        // Instantiate and draw our chart, passing in some options.
        var chart = new google.visualization.PieChart( container );
        chart.draw(data, options);

        if( is_main ) {
            google.visualization.events.addListener(chart, 'select', (function() {

                var selection = this.getSelection();

                if( typeof selection[0] !='undefined' && typeof selection[0].row!='undefined' ) {

                    if(scale == 'dependency' && available_dep_answer) {
                        
                    } else {
                        var container = $(this.container).closest('.stat');

                        if(  container.attr('answer-id')==(selection[0].row + 1) ) {
                            container.removeAttr('answer-id');
                        } else {
                            container.attr('answer-id', selection[0].row + 1);                        
                        }

                        if (!can_click_on_legend) {
                            container.find('.scales-filter label').addClass('active');
                            container.find('.scales-filter input').prop('checked', true);
                        }

                        if ($(window).outerWidth() <= 768 && container.find('.mobile-button-legend').length) {
                            // $('html, body').animate({
                            //     scrollTop: container.find('.mobile-button-legend').offset().top
                            // }, 500);
                        }
                        if(typeof scale !== 'undefined' && scale == 'dependency') {
                            container.find('.loader').fadeIn();
                            container.find('.loader-mask').fadeIn();
                        }
                        reloadGraph( container );
                    }
                }
            }).bind(chart));

            // The selection handler.
            // Loop through all items in the selection and concatenate
            // a single message from all of them.            
        }
    }

    var drawDependencyColumns = function(rows, container, fixedColor, dep_answer) {
        $(container).html('<div class="mobile-chart"></div>');
        container = $(container).find('.mobile-chart');

        var width = $(window).width()<1200 && !$(container).closest('.st-download').length ? $(container).closest('.graphs').innerWidth() : 750;
        $(container).css('width', width );

        var globalMax = null;
        if( rows[0].length==2 ) {
            var globalMax = 0 ;
            for(var i=1;i<rows.length;i++) {
                if( rows[i][1] > globalMax) {
                    globalMax = rows[i][1];
                }
            }
        }

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
            if (rows[i][1]*100 == 0 && $(window).outerWidth() < 768) {
            } else {
                $(container).append('<div class="dependency" d="'+i+'"><div class="group-heading">'+rows[i][0]+'</div></div>');
                var pl = ($(window).outerWidth() > 768 ? 84 : 72)*rows[i][1]/max + 1;

                $(container).find('.dependency[d="'+i+'"]').append('<div class="custombar"> <span class="legend-color" style="width: '+parseInt(pl)+'%; background-color: '+chart_colors[fixedColor-1]+';"></span> '+(rows[i][1]*100).toFixed(1)+'%</div>');
            }
        }
    }

    var drawMapColumns = function(rows, container, multiple_top_answers, question_type) {

        $(container).html('<div class="mobile-chart"></div>');
        container = $(container).find('.mobile-chart');

        var width = $(window).width()<1200 && !$(container).closest('.st-download').length ? $(container).closest('.graphs').innerWidth() : 540;

        if(!$(container).hasClass('country_id')){
            $(container).css('width', width );
        }

        var globalMax = null;
        if( rows[0].length==2 ) {
            var globalMax = 0 ;
            for(var i=1;i<rows.length;i++) {
                if( rows[i][1] > globalMax) {
                    globalMax = rows[i][1];
                }
            }
        }

        for(var i=1; i<rows.length; i++) {
            $(container).append('<div class="sort-stat" answer-id="'+i+'"><div class="group-heading">'+rows[i][0]+'</div></div>');

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
                var pl = 80*rows[i][j]/max + 1;
                var color = chart_colors[i-1];
                if( typeof(rows[0][j])!='object' ) {
                    $(container).find('.sort-stat[answer-id="'+i+'"]').append('<div class="custombar"> <span class="legend-color" style="width: '+(pl ? parseInt(pl) : 1)+'%; background-color: '+color+';"></span> '+(question_type == 'rank' ? rows[i][j] : (!isNaN(rows[i][j]) ? (rows[i][j]*100).toFixed(1) : 0 ) +'%')+'</div>');
                }
            }
        }

        if(multiple_top_answers) {
            container.prepend('<p class="top-answers">Top '+multiple_top_answers+' answers</p>');
        }
    }

    var drawColumns = function(rows, container, fixedColor) {

        if( ($(window).width()<1200 && !$(container).closest('.st-download').length) ) {
            $(container).html('<div class="mobile-chart"></div>');
            container = $(container).find('.mobile-chart');

            var width = $(window).width()<1200 && !$(container).closest('.st-download').length ? $(container).closest('.graphs').innerWidth() : 540;

            if(!$(container).hasClass('country_id')){
                $(container).css('width', width );
            }

            var globalMax = null;
            if( rows[0].length==2 ) {
                var globalMax = 0 ;
                for(var i=1;i<rows.length;i++) {
                    if( rows[i][1] > globalMax) {
                        globalMax = rows[i][1];
                    }
                }
            }

            for(var i=1; i<rows.length; i++) {
                $(container).append('<div class="mobile-wrap" m-id="'+i+'"><div class="group-heading">• '+rows[i][0]+'</div></div>');

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
                    var pl = 80*rows[i][j]/max + 1;
                    var color = fixedColor ? chart_colors[fixedColor-1] : chart_colors[j-1];
                    if( typeof(rows[0][j])!='object' ) {
                        $(container).find('.mobile-wrap[m-id="'+i+'"]').append('<div class="custombar" votes="'+(rows[i][j]*100).toFixed(1)+'"> <span class="legend-color" style="width: '+parseInt(pl)+'%; background-color: '+color+';"></span> '+(rows[i][j]*100).toFixed(1)+'%</div>');
                    }
                }
            }

        } else {

            var fontSize = 10;
            if( rows.length<=5 && $(window).width()>768 ) {
                fontSize = 15;
            } else if(rows.length>=9) {
                fontSize = 8;                
            }

            console.log(rows);

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
                        fontSize: fontSize,
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

            options.width = $(container).closest('.st-download').length ? options.width : $(window).width()<768 ? $(container).closest('.graphs').innerWidth() : ( $(window).width()<1200 ? $(container).closest('.graphs').innerWidth() : options.width);
            options.height = $(container).closest('.st-download').length ? options.height : $(window).width()<768 ? $(container).closest('.graphs').innerWidth() : ( $(window).width()<1200 ? $(container).closest('.graphs').innerWidth()/2 : options.height);
                
            var chart = new google.charts.Bar( container );
            //chart.draw(data, options);

            chart.draw(data, google.charts.Bar.convertOptions(options));
        }
    }

    var drawMultipleColumns = function(rows, container, more_options, totalCount, multiple_top_answers, question_type) {

        $(container).html('<div class="mobile-chart"></div>');
        container = $(container).find('.mobile-chart');

        var width = $(window).width()<1200 && !$(container).closest('.st-download').length ? $(container).closest('.graphs').innerWidth() : 100+'%';
        $(container).css('width', width );

        var globalMax = null;
        if( rows[0].length==2 ) {
            var globalMax = 0 ;
            for(var i=0;i<rows.length;i++) {
                if( rows[i][1] > globalMax) {
                    globalMax = rows[i][1];
                }
            }
        }

        var array = [];

        for(var i=0; i<rows.length; i++) {
            array.push(rows[i][0]);
            $(container).append('<div answer-id="'+(i + 1)+'" class="custom-legend"><div class="group-heading">'+rows[i][0]+'</div></div>');

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
                var pl = 80*rows[i][j]/max + 1;

                if( typeof(rows[0][j])!='object' ) {
                    $(container).find('.custom-legend[answer-id="'+(i + 1)+'"]').append('<div class="custombar"> <span class="legend-color" style="width: '+parseInt(pl)+'%; background-color: '+chart_colors[i]+'"></span> '+(question_type == 'rank' ? rows[i][j] : ((rows[i][j]/ totalCount)*100).toFixed(1)+'%')+'</div>');
                }
            }
        }

        if(!container.hasClass('gender') && !container.hasClass('country_id') && multiple_top_answers) {
            container.prepend('<p class="top-answers">Top '+multiple_top_answers+' answers</p>');
        }
    }

    var drawGenderColumns = function(main_chart_rows, rowsf, rowsm, container, more_options, totalfCount, totalmCount, scale_options_arr, a_id, mainChartRows, multiple_top_answers, vox_scale_id, question_type) {
        $(container).html('<div class="mobile-chart"></div>');
        container = $(container).find('.mobile-chart');
        container.addClass('multiple-gender-chart');
        container.closest('.stat').find('.main-chart').addClass('main-multiple-gender');
        container.closest('.stat').find('.total-m').show();
        container.closest('.stat').find('.total-f').show();

        if (scale_options_arr.length && scale_options_arr.length == 1) {
            if (scale_options_arr[0] == 'f') {
                container.closest('.stat').find('.total-m').hide();
            } else if(scale_options_arr[0] == 'm') {
                container.closest('.stat').find('.total-f').hide();
            }

            container.closest('.stat').find('.main-multiple-gender').addClass('lower-margin');
        } else {
            container.closest('.stat').find('.main-multiple-gender').removeClass('lower-margin');
        }

        var width = $(window).width()<1200 && !$(container).closest('.st-download').length ? $(container).closest('.graphs').innerWidth() : 100+'%';
        $(container).css('width', width );

        var maxf = null;
        if( rowsf.length && rowsf[0].length==2 ) {
            var maxf = 0 ;
            for(var i=0;i<rowsf.length;i++) {
                if( rowsf[i][1] > maxf) {
                    maxf = rowsf[i][1];
                }
            }
        }

        var maxm = null;
        if( rowsm.length && rowsm[0].length==2 ) {
            var maxm = 0 ;
            for(var i=0;i<rowsm.length;i++) {
                if( rowsm[i][1] > maxm) {
                    maxm = rowsm[i][1];
                }
            }
        }

        for(var i=0; i<rowsf.length; i++) {
            $(container).append('<div answer-id="'+(i + 1)+'" class="custom-legend"></div>');

            for(var j=1; j<rowsf[i].length; j++) {

                var plm = 0;
                if($(window).outerWidth() > 768) {
                    var plf = question_type == 'rank' ? 80*rowsf[i][j]/maxf + 1 : ((rowsf[i][j]/ totalfCount)*100).toFixed(1);
                    // var plf = 80*rowsf[i][j]/maxf + 1;
                    if(rowsm[i]) {
                        var plm = question_type == 'rank' ? 80*rowsm[i][j]/maxm + 1 : ((rowsm[i][j]/ totalmCount)*100).toFixed(1);
                        // var plm = 80*rowsm[i][j]/maxm + 1;
                    }
                } else {
                    var plf = 72*rowsf[i][j]/maxf + 1;
                    if(rowsm[i]) {
                        var plm = 72*rowsm[i][j]/maxm + 1;
                    }
                }

                if( typeof(rowsf[0][j])!='object' ) {
                    // if(rowsf[i][j]){
                        $(container).find('.custom-legend[answer-id="'+(i + 1)+'"]').append('<div class="custombar clearfix legend-f"> <img src="'+window.location.origin+'/new-vox-img/women-icon.svg"><span class="legend-color" style="width: '+parseInt(plf)+'%; opacity: 0.4; background-color: '+chart_colors[i]+';"></span> '+(question_type == 'rank' ? rowsf[i][j] : ((rowsf[i][j]/ totalfCount)*100).toFixed(1)+'%')+'</div>');
                    // }
                    // if(rowsm[i][j]) {
                        $(container).find('.custom-legend[answer-id="'+(i + 1)+'"]').append('<div class="custombar clearfix legend-m"> <img src="'+window.location.origin+'/new-vox-img/man-icon.svg"><span class="legend-color" style="width: '+parseInt(plm)+'%; opacity: 0.7; background-color: '+chart_colors[i]+';"></span> '+(question_type == 'rank' ? rowsm[i][j] : ((rowsm[i][j]/ totalmCount)*100).toFixed(1)+'%')+'</div>');
                    // }
                }
            }
        }

        if (scale_options_arr.length && scale_options_arr.length == 1) {
            if (scale_options_arr[0] == 'f') {
                container.find('.legend-m').hide();
                container.find('.legend-f').show();
            } else if(scale_options_arr[0] == 'm') {
                container.find('.legend-f').hide();
                container.find('.legend-m').show();
            }
            if ($(window).width() > 768) {
                container.closest('.stat').find('.multiple-gender-chart').css('margin-top', '100px');
            }            
        } else {
            container.find('.legend-f').show();
            container.find('.legend-m').show();
            if ($(window).width() > 768) {
                container.closest('.stat').find('.multiple-gender-chart').css('margin-top', '0px');
            }
        }

        container.closest('.stat').find('.main-multiple-gender .custom-legend').click( function() {
            var cont = $(this).closest('.stat');
            if (cont.attr('answer-id') == $(this).attr('answer-id')) {
                cont.attr('answer-id', '' );
            } else {
                cont.attr('answer-id', $(this).attr('answer-id') );
            }
            
            reloadGraph( cont );
        } );
    }

    var drawAnswerGenderColumns = function(container, a_id, question_type) {
        
        $(container).find('.custom-legend').removeClass('verticle-gender').hide();
        $(container).find('.custom-legend[answer-id="'+a_id+'"]').addClass('verticle-gender').show();
        
        $(container).find('.custom-legend[answer-id="'+a_id+'"]').children().each( function() {
            if (question_type == 'multiple_choice' || question_type == 'rank' || ($(container).closest('.more-q-content').length) || $(window).width() <= 768) {
                $(this).find('span').height($(this).find('span').outerWidth());
            } else {
                $(this).find('span').height($(this).find('span').outerWidth() * 3 );
            }
            if ($(window).outerWidth()>768) {
                $(this).find('span').width(140);
            } else {
                $(this).find('span').width(90);
            }
            $(this).find('span').css('transform', 'none');
        });
        $(container).closest('.stat').find('.main-multiple-gender').addClass('lower-margin');
    }

    var drawMap = function(rows, container, multiple_top_answers, question_type) {

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
            var go_hit = false;

            for (var i in rows) {
                if(rows[i][0] == ev.target.dataItem.dataContext.id) {
                    go_hit = true;
                    break;
                }
            }

            if(go_hit) {                

                if(!map_country || ev.target.tooltipDataItem.dataContext.name!=map_country) {
                    map_country = ev.target.tooltipDataItem.dataContext.name;
                    map_country_data = ev.target.dataItem.dataContext;

                    ev.target.setState("highlight");
                    if(map_country_data.pieData!== undefined) {
                        drawMapColumns( map_country_data.pieData, $(this).closest('.graphs').find('.second-chart')[0], multiple_top_answers, question_type);
                        $(this).closest('.graphs').find('.map-hint').html('Answers distribution in <b>' + map_country + '</b>' ).show();
                    }

                } else {
                    map_country = null;
                    map_country_data = null;

                    drawMapColumns( converted_rows, $(this).closest('.graphs').find('.second-chart')[0], multiple_top_answers, question_type);
                }
            }

        }).bind(container));

        polygonSeries.mapPolygons.template.events.on("over", (function(ev) {

            $(this).closest('.graphs').find('.map-hint').html('Answers distribution by country' ).show();
            if(map_country) {
                $(this).closest('.graphs').find('.map-hint').html('Answers distribution in <b>' + map_country + '</b>' ).show();
            }
            if( ev.target.dataItem.dataContext.pieData !== undefined ) {
                if( ev.target.dataItem.dataContext.name!=map_country ) {
                    ev.target.setState("hovered");                    
                    $(this).closest('.graphs').find('.map-hint').html('Answers distribution in <b>' + ev.target.tooltipDataItem.dataContext.name + '</b>' ).show();
                }
                drawMapColumns( ev.target.dataItem.dataContext.pieData, $(this).closest('.graphs').find('.second-chart')[0], multiple_top_answers, question_type);
                
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

            
            drawMapColumns( map_country_data ? map_country_data.pieData : converted_rows, $(this).closest('.graphs').find('.second-chart')[0], multiple_top_answers, question_type);
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

        //get total respondents
        var max = rows[rows.length-1][1];
        rows = rows.slice(0,-1);

        var total = 0;
        for (var i in rows) {
            total+= rows[i][rows[i].length-1][1];
        }

        var chartData = [];
        for(var i in rows) {
            var resp_total = 0;
            resp_total+= rows[i][rows[i].length-1][1];
            rows[i] = rows[i].slice(0,-1);

            rowTotal = rows[i][rows[i].length-1][1];
            rows[i] = rows[i].slice(0,-1);

            //20 - 96
            //var lummax

            var rgb = hslToRgb(0, 0, 0.96 - ((question_type == 'rank' ? resp_total : rowTotal)/max)*0.76 ); // 20 == 100% // 96 == 0%

            var pieData = [
                ['', '']
            ];
            for(var j = 2; j < rows[i].length; j++) {
                pieData.push([ rows[i][j][0] , rows[i][j][1] / (question_type == 'rank' ? resp_total : rowTotal) ]);
            }

            chartData.push({
                "id": rows[i][0], //rows[i][0],
                "name": rows[i][1][1],
                "value": ((question_type == 'rank' ? resp_total : rowTotal)/total*100).toFixed(1),
                "count": question_type == 'rank' ? resp_total : rowTotal,
                "fill": am4core.color(rgb),
                "hoverColor": am4core.color("#119c88"),
                "pieData": pieData,
            });
        }
        
        polygonSeries.data = chartData;

        // Bind "fill" property to "fill" key in data
        polygonTemplate.propertyFields.fill = "fill";
    }

    if(user_id) {
        $('.stats .stat:first-child').addClass('active');
        google.charts.load('current', {
            packages: ['corechart', 'bar'],
        });
        google.charts.setOnLoadCallback(function() {
            gc_loaded = true;

            $('.stats .stat:first-child').addClass('active');

            $('.stat.active').each( function() {
                if($(this).find('.scale-stat-q').length) {
                    $(this).find('.scale-stat-q').first().addClass('active');
                    reloadGraph($(this).find('.scale-stat-q').first());
                } else {
                    reloadGraph(this);
                }
                
            } );
        });
    }

    $('.social-share').click( function() {
        $('.share-popup').addClass('active');
    });

    $('.share-buttons .share').click( function() {
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

    $('.download-format-radio').change( function(e) {
        $(this).closest('.download-formats').find('label').removeClass('active');
        $(this).closest('label').addClass('active');

        console.log($(this).val());
        if($(this).val() != 'xlsx') {
            $('[name="download-demographic[]"][value="country_id"]').closest('label').hide();
        } else {
            $('[name="download-demographic[]"][value="country_id"]').closest('label').show();
        }
    });

    $('.download-demographic-checkbox').change( function(e) {
        $(this).closest('label').addClass('active');
        $(this).prop('checked', true);
        $('.demogr-options').hide();
    });

    $('.active-removal').click( function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).closest('label').removeClass('active');
        $(this).closest('label').find('.download-demographic-checkbox').prop('checked', false);
        if($(this).closest('label').find('.demogr-options').length) {
            $(this).closest('label').find('.demogr-options').hide();
        }
    });

    $('.dem-arrow').click( function(e) {
        e.preventDefault();
        e.stopPropagation();
        if (!$(this).closest('label').hasClass('active')) {
            $(this).closest('label').addClass('active');
            $(this).closest('label').find('.download-demographic-checkbox').prop('checked', true);
        }
        $('.demogr-options').hide();
        $(this).closest('label').find('.demogr-options').show();
    });

    $('.dem-checkbox').change( function(e) {
        console.log('smenq');
        e.preventDefault();
        e.stopPropagation();
        
        if ($(this).hasClass('select-all-dem')) {
            $(this).closest('.demogr-options').find('label').addClass('active');
            $(this).closest('.demogr-options').find('input').prop('checked', true);
        } else {
            console.log($(this).closest('label'));
            $(this).closest('label').toggleClass('active');
            $(this).closest('.demogr-options').find('.select-all-dem').prop('checked', false);
            $(this).closest('.demogr-options').find('.select-all-dem-label').removeClass('active');

            if (!$(this).closest('.demogr-options').find('input:checked').length) {
                $(this).closest('.demogr-options').find('label').addClass('active');
                $(this).closest('.demogr-options').find('input').prop('checked', true);
            }
        }
    });

    $('.close-dem-options').click( function(e) {
        e.preventDefault();
        e.stopPropagation();

        $(this).closest('.demogr-options').hide();
    });

    $('.download-date-radio').change( function(e) {
        e.preventDefault();

        $(this).closest('.filters-custom-wrap').find('label').removeClass('active');
        $(this).closest('label').addClass('active');

        if($(this).closest('label').hasClass('download-custom-date')) {

            $(this).closest('.filters-wrapper').find('.filters-custom').show();

            if( !$('#custom-datepicker-download').attr('inited') ) {
                var start_date = $('#custom-datepicker-download').attr('launched-date');
                var dateToday = new Date();

                $('#custom-datepicker-download').dateRangePicker({
                    inline:true,
                    container: '#custom-datepicker-download', 
                    alwaysOpen: true ,
                    endDate: dateToday,
                    startDate: start_date,
                    minDays: 1,
                    singleMonth: $(window).outerWidth() > 768 ? false : true,
                    setValue: function(s,s1,s2) {
                        var day_from = new Date(s1);
                        var day_to = new Date(s2);

                        timeframe = [];
                        timeframe.push(day_from);
                        timeframe.push(day_to);

                        var options = { 
                        //    weekday: 'long', 
                            year: 'numeric', 
                            month: 'short', 
                            day: 'numeric' 
                        };

                        var from = timeframe[0].toLocaleDateString('en', options);
                        var to = timeframe[1].toLocaleDateString('en', options);
                        $('#date-from-download').val(from);
                        $('#date-to-download').val(to);
                    }
                });

                $('.datepicker, .datepickerContainer').css({
                    height: 'auto',
                    width: 'auto'
                });

                $('.datepickerContainer').after( $('#datepicker-extras-download') );

                $('#custom-datepicker-download').attr('inited', '1');
                $('#custom-datepicker-download').parent().prepend('<a href="javascript:;" class="close-custom-datepicker"><img src="'+images_path+'/close-popup.png"/></a>');

                $('.close-custom-datepicker').click( function(e) {
                    e.preventDefault();
                    $(this).closest('.filters-custom').hide();
                });
            }
        } else {
            $(this).closest('.filters-wrapper').find('.filters-custom').hide();
        }
    });

    $('#download-form').submit( function(e) {
        e.preventDefault();

        if(ajax_is_running) {
            return;
        }

        ajax_is_running = true;

        $(this).find('.ajax-alert').remove();
        $(this).find('.has-error').removeClass('has-error');
        var that = $(this);

        $.post( 
            $(this).attr('action'), 
            $(this).serialize(), 
            function( data ) {
                if(data.success) {
                    window.location.href = window.location.origin+window.location.pathname+data.tail;
                } else {

                    console.log(that);
                    for(var i in data.messages) {
                        if(i == 'download-date' || i == 'date-from-download' || i == 'date-to-download' ) {
                            if(!that.find('[error="download-date"]').length) {
                                that.find('[name="download-date"]').closest('.alert-after').after('<div class="alert alert-warning ajax-alert" error="download-date">'+data.messages[i]+'</div>');
                            }                            
                        } else if(i == 'download-demographic') {
                            that.find('[name="'+i+'[]"]').addClass('has-error');
                            that.find('[name="'+i+'[]"]').closest('.alert-after').after('<div class="alert alert-warning ajax-alert" error="'+i+'">'+data.messages[i]+'</div>');
                        } else {
                            that.find('[name="'+i+'"]').addClass('has-error');
                            that.find('[name="'+i+'"]').closest('.alert-after').after('<div class="alert alert-warning ajax-alert" error="'+i+'">'+data.messages[i]+'</div>');
                        }
                    }
                }
                ajax_is_running = false;
            }, "json"
        );
    });
    
    $('.download-stats-popup-btn').click( function() {
        showPopup('download-stats-popup');

        if($('#download-stats-popup').find('.demogr-inner').length) {
            $('#download-stats-popup').find('.demogr-inner').remove();
        }
        var inner = $(this).closest('.stat').next();
        inner.show();

        $('#download-stats-popup').find('.download-demographics').append(inner);

        $('#stats-for').val($(this).attr('for-stat'));
        if($(this).is('[for-scale]')) {
            $('#scale-for').val($(this).attr('for-scale'));
        }
    });

    $('#make-stat-image-btn').click( function() {
            
        var generateImage = function() {
            var st_title = $('.st-title').first();

            if(st_title.length) {
                domtoimage.toPng(st_title[0])
                .then( (function (dataUrl) {
                    var img = new Image();
                    img.src = dataUrl;
                    $('#stats-imgs').append(img);
                    st_title.remove();
                    generateImage();
                }).bind(st_title) )
                .catch(function (error) {
                    console.error('oops, something went wrong!', error);
                });
            } else {

                var elm = $('.echo').first();
                if(elm.length) {
                    domtoimage.toPng(elm[0])
                    .then( (function (dataUrl) {
                        var img = new Image();
                        img.src = dataUrl;
                        $('#stats-imgs').append(img);
                        elm.remove();
                        generateImage();
                    }).bind(elm) )
                    .catch(function (error) {
                        console.error('oops, something went wrong!', error);
                    });
                } else {

                    setTimeout( function() {
                        //console.log($('#stats-imgs').outerHeight());
                        $('#hidden_heigth').val($('#stats-imgs').outerHeight());

                        $('#hidden_html').val($('#stats-imgs').html());
                        $('#download-form-pdf').submit();
                        //alert('ready!');
                    }, 500);
                }
            }
        }

        var generatePngImage = function() {

            var elm = $('.echo-png').first();

            if(elm.length) {
                domtoimage.toPng(elm[0])
                .then( (function (dataUrl) {
                    var img = new Image();
                    img.src = dataUrl;
                    //$('#stats-png-imgs').html('');
                    $('#stats-png-imgs').append(img);
                    elm.remove();

                    generatePngImage();

                    // domtoimage.toBlob($('#stats-png-imgs')[0])
                    // .then(function (blob) {
                    //     window.saveAs(blob, 'stats.png');
                    //     generatePngImage();
                    // });

                }).bind(elm) )
                .catch(function (error) {
                    console.error('oops, something went wrong!', error);
                });
            } else {
                console.log('gotovo');

                setTimeout( function() {
                    $('#download-form-png').submit();
                }, 300);
            }
        }

        if($('.echo-png').length) {
            setTimeout( function() {
                generatePngImage();
            }, 100);
        } else {
            setTimeout( function() {
                generateImage();
            }, 100);
        }        
    });

    $('#download-form-pdf').submit( function(e) {
        e.preventDefault();

        if(ajax_is_running) {
            return;
        }

        ajax_is_running = true;
        var that = $(this);

        $.post( 
            $(this).attr('action'), 
            $(this).serialize(), 
            function( data ) {
                if(data.success) {
                    window.location.href = data.url;
                    window.shouldCloseAndRedirect = data.url_file;
                } else {
                    console.log('download error');
                }
                ajax_is_running = false;
            }, "json"
        );
    });

    $('#download-form-png').submit( function(e) {
        e.preventDefault();

        if(ajax_is_running) {
            return;
        }

        ajax_is_running = true;

        var formData = new FormData();
        formData.append("_token", $(this).find('input[name="_token"]').val());
        formData.append("stat_url", $(this).find('input[name="stat_url_png"]').val());
        formData.append("stat_title", $(this).find('input[name="stat_title_png"]').val());

        var i=0;
        $('#stats-png-imgs img').each( function() {
            i++;

            var base64 = getBase64Image($(this)[0]);
            var contentType = 'image/png';// In this case "image/gif"
            var blob = b64toBlob(base64, contentType);
            formData.append('picture'+i, blob);
        });

        var that = $(this);

        $.ajax({
            type: "POST",
            url: that.attr('action'),
            success: function (data) {
                if(data.success) {
                    window.location.href = data.url;
                    window.shouldCloseAndRedirect = data.url_file;
                } else {
                    console.log('not ok');
                }
                ajax_is_running = false;
            },
            error: function (error) {
                console.log('error');
            },
            async: true,
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            timeout: 60000
        });
    });

    var getBase64Image = function(img) {
        var canvas = document.createElement("canvas");
        canvas.width = img.width;
        canvas.height = img.height;
        var ctx = canvas.getContext("2d");
        ctx.drawImage(img, 0, 0);
        var dataURL = canvas.toDataURL("image/png");
        return dataURL.replace(/^data:image\/png;base64,/, "");
    }

    /**
    * Convert a base64 string in a Blob according to the data and contentType.
    * 
    * @param b64Data {String} Pure base64 string without contentType
    * @param contentType {String} the content type of the file i.e (image/jpeg - image/png - text/plain)
    * @param sliceSize {Int} SliceSize to process the byteCharacters
    * @see http://stackoverflow.com/questions/16245767/creating-a-blob-from-a-base64-string-in-javascript
    * @return Blob
    */
    function b64toBlob(b64Data, contentType, sliceSize) {
        contentType = contentType || '';
        sliceSize = sliceSize || 512;
        var byteCharacters = atob(b64Data);
        var byteArrays = [];

        for (var offset = 0; offset < byteCharacters.length; offset += sliceSize) {
            var slice = byteCharacters.slice(offset, offset + sliceSize);

            var byteNumbers = new Array(slice.length);
            for (var i = 0; i < slice.length; i++) {
                byteNumbers[i] = slice.charCodeAt(i);
            }

            var byteArray = new Uint8Array(byteNumbers);

            byteArrays.push(byteArray);
        }

        var blob = new Blob(byteArrays, {type: contentType});
        return blob;
    }
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

String.prototype.hashCode = function() {
    var hash = 0, i, chr;
    if (this.length === 0) return hash;
    for (i = 0; i < this.length; i++) {
        chr   = this.charCodeAt(i);
        hash  = ((hash << 5) - hash) + chr;
        hash |= 0; // Convert to 32bit integer
    }
    return Math.abs(hash);
};