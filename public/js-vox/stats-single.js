var gc_loaded = false;
var load_js = false;
var user_id;

var reloadGraph;
var showPopup;

$(document).ready(function(){

    $('.stats .stat .title').click( function(e) {
        e.preventDefault();
        
        var that = $(this);

        if(!load_js && !user_id) {
            $('#main-loader').fadeIn();
            $('#main-loader').find('.loader-mask').fadeIn();

            $.getScript(window.location.origin+'/js-vox/stats-single-loaded.js', function() {
                $.getScript(window.location.origin+'/js/amcharts-core.js', function() {
                    $.getScript(window.location.origin+'/js/amcharts-maps.js', function() {
                        $.getScript(window.location.origin+'/js/amcharts-worldLow.js', function() {
                            $.getScript(window.location.origin+'/js/gstatic-charts-loader.js', function() {
                                google.charts.load('current', {
                                    packages: ['corechart', 'bar'],
                                });
                                google.charts.setOnLoadCallback(function() {
                                    gc_loaded = true;

                                    $('#main-loader').fadeOut();
                                    $('#main-loader').find('.loader-mask').delay(350).fadeOut('slow');

                                    $('.stats-image-wrapper').hide();

                                    $('.stat').each( function() {
                                        $(this).find('.graphs .stats-mask').append('<img class="stats-loader" src="'+window.location.origin+'/new-vox-img/dentavox-statistics-loader.gif" alt="Dentavox statistics loader">');
                                    });

                                    var stat = that.closest('.stat');
                                    
                                    if( !stat.hasClass('active') ) {
                                        if (stat.find('.scale-stat-q').length) {
                                            stat.addClass('active');
                                            stat.find('.scale-stat-q').first().addClass('active');
                                            reloadGraph(stat.find('.scale-stat-q').first());
                                        } else {
                                            stat.addClass('active');
                                            stat.find('.loader').fadeIn();
                                            stat.find('.loader-mask').fadeIn();
                                            $('input').prop('disabled', true);
                                            reloadGraph( stat );
                                        }
                                    } else {
                                        stat.removeClass('active');
                                    }
                                });
                                load_js = true;
                            });
                        });
                    });
                });
            });
        } else {

            if( $( '.stat.restore-me' ).length || $('.loader-mask:visible').length) {
                return;
            }
            var stat = $(this).closest('.stat');

            if( !stat.hasClass('active') ) {
                if (stat.find('.scale-stat-q').length) {
                    stat.addClass('active');
                    stat.find('.scale-stat-q').first().addClass('active');
                    reloadGraph(stat.find('.scale-stat-q').first());
                } else {
                    stat.addClass('active');
                    stat.find('.loader').fadeIn();
                    stat.find('.loader-mask').fadeIn();
                    $('input').prop('disabled', true);
                    reloadGraph( stat );
                }
            } else {
                stat.removeClass('active');
            }
        }
    });

    $('#load-stats').click( function(е) {
        е.preventDefault();
        if(!load_js && !user_id) {

            $('#main-loader').fadeIn();
            $('#main-loader').find('.loader-mask').fadeIn();

            $.getScript(window.location.origin+'/js-vox/stats-single-loaded.js', function() {
                $.getScript(window.location.origin+'/js/amcharts-core.js', function() {
                    $.getScript(window.location.origin+'/js/amcharts-maps.js', function() {
                        $.getScript(window.location.origin+'/js/amcharts-worldLow.js', function() {
                            $.getScript(window.location.origin+'/js/gstatic-charts-loader.js', function() {

                                google.charts.load('current', {
                                    packages: ['corechart', 'bar'],
                                });
                                google.charts.setOnLoadCallback(function() {
                                    gc_loaded = true;

                                    $('.stats-image-wrapper').hide();
                                    $('.filters-wrapper').show();

                                    $('.stat').each( function() {
                                        $(this).find('.graphs .stats-mask').append('<img class="stats-loader" src="'+window.location.origin+'/new-vox-img/dentavox-statistics-loader.gif" alt="Dentavox statistics loader">');
                                    });

                                    $('#main-loader').fadeOut();
                                    $('#main-loader').find('.loader-mask').delay(350).fadeOut('slow');

                                    $('.stats .stat:first-child').addClass('active');

                                    $('.stat.active').each( function() {
                                        if($(this).find('.scale-stat-q').length) {
                                            $(this).find('.scale-stat-q').first().addClass('active');
                                            reloadGraph($(this).find('.scale-stat-q').first());
                                        } else {
                                            reloadGraph(this);
                                        }
                                        
                                    });
                                });
                                load_js = true;
                            });
                        });
                    });
                });
            });
        }
    });

    $('.blurred-button').click( function(e) {
        if(dentacoin_down) {
            e.stopImmediatePropagation();
            showPopup('failed-popup');
        } else {
            if($(this).hasClass('log-btn')) {
                $.event.trigger({type: 'openPatientLogin'});
            } else {
                $.event.trigger({type: 'openPatientRegister'});
            }
        }
    });

    $('.scroll-to-blurred').click( function() {
        $('html, body').animate({
            scrollTop: $(".stats-blurred").offset().top
        }, 500);
    });
});