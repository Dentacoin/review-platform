var videoStart, videoLength;
var slider = null;
var sliderTO = null;
var handleReviewEvents;
var showFullReview;
var handleDCNreward;
var galleryFlickty;
var teamFlickity;
var suggestDentist;
var suggestClinic;
var suggestedDentistClick;
var suggestClinicClick;
var aggregated_reviews;
var editor;
var fb_page_error;
var load_lightbox;
var load_flickity;
var dont_initialize_flickity;
var load_maps = false;
var click_on_map;

$(document).ready(function(){

    showFullReview = function(id, d_id) {
        showPopup('view-review-popup');
        $.ajax( {
            url: '/'+lang+'/review/' + id,
            type: 'GET',
            data: {
                d_id: d_id,
            },
            success: (function( data ) {
                $('#the-detailed-review').html(data);
                showPopup('view-review-popup');
                handleReviewEvents();
                attachTooltips();
                $('#view-review-popup .share-popup').attr('share-href', window.location.href.split('?')[0] + '?review_id=' + this);
            }).bind(id)
        });
    }

    $('.header-info .fa-search').click( function() {
        $('.home-search-form').show();
        $(this).hide();
        $('#search-input').focus();
        $('.blue-background').addClass('extended');
    } );

    var flickityFunctions = function() {
        if(!dont_initialize_flickity) {

            if (window.innerWidth > 768) {
                var draggable_gallery = false;
            } else {
                var draggable_gallery = true;
            }

            if (window.innerWidth > 992) {
                var draggable_team = false;
            } else {
                var draggable_team = true;
            }

            galleryFlickty = $('.gallery-flickity').flickity({
                autoPlay: false,
                wrapAround: true,
                cellAlign: 'left',
                pageDots: true,
                freeScroll: true,
                groupCells: 1,
                draggable: draggable_gallery,
            });

            galleryFlickty.resize();

            teamFlickity = $('.flickity').flickity({
                autoPlay: false,
                wrapAround: true,
                cellAlign: 'left',
                pageDots: false,
                freeScroll: true,
                groupCells: 1,
                draggable: draggable_team,
            });

            teamFlickity.resize();
        }
    }

    var loadMap = function() {
        if( $('#profile-map').length && !$('#profile-map').attr('inited') ) {
            $('#profile-map').attr('inited', 'done');
            $('.info-address').hide();

            prepareMapFucntion( function() {
                    
                var profile_map = new google.maps.Map(document.getElementById('profile-map'), {
                    center: {
                        lat: parseFloat($('#profile-map').attr('lat')), 
                        lng: parseFloat($('#profile-map').attr('lon'))
                    },
                    zoom: 15,
                    backgroundColor: 'none'
                });

                var mapMarker = new google.maps.Marker({
                    position: {
                        lat: parseFloat($('#profile-map').attr('lat')), 
                        lng: parseFloat($('#profile-map').attr('lon'))
                    },
                    map: profile_map,
                    icon: images_path+'/map-pin-active.png',
                });

                var data = $('.scroll-to-map:visible').attr('map-tooltip');
                var infowindow = new google.maps.InfoWindow({
                  content: data
                });
                infowindow.open(profile_map,mapMarker);

            } );
        }
    }

    $('.fake-map').click( function() {
        if (!load_maps && typeof google === 'undefined' ) {
            $.getScript('https://maps.googleapis.com/maps/api/js?key=AIzaSyCaVeHq_LOhQndssbmw-aDnlMwUG73yCdk&libraries=places&callback=initMap&language=en', function() {
                load_maps = true;

                loadMap();
                
            } );
        } else {
            loadMap();
        }
    });

    $('.tab').click( function() {
        if( $(this).attr('data-tab')=='about' ) {

            if(!click_on_map) {
                if(typeof google === 'undefined' ) {
                    $.getScript('https://maps.googleapis.com/maps/api/js?key=AIzaSyCaVeHq_LOhQndssbmw-aDnlMwUG73yCdk&libraries=places&callback=initMap&language=en', function() {
                        click_on_map = true;

                        loadMap();
                        
                    } );
                } else {
                    loadMap();
                }
            }

            if (!load_lightbox ) {
                $.getScript(window.location.origin+'/js/lightbox.js', function() {
                    $('head').append('<link rel="stylesheet" type="text/css" href="'+window.location.origin+'/css/lightbox.css">');
                    load_lightbox = true;
                } );
            }

            if (!load_flickity ) {
                $.getScript(window.location.origin+'/js-trp/flickity.min.js', function() {
                    $('head').append('<link rel="stylesheet" type="text/css" href="'+window.location.origin+'/css/flickity.min.css">');
                    flickityFunctions();
                    load_flickity = true;
                } );
            } else {
                flickityFunctions();
            }            
        }
    });


    if( getUrlParameter('tab') ) {
        $('.profile-tabs a[data-tab="'+getUrlParameter('tab')+'"]').trigger('click');
    } else if($('.profile-tabs .force-active').length) {
        $('.profile-tabs .force-active').click();
    } else {
        $('.profile-tabs a').first().click();
    }

    if(getUrlParameter('review_id')) {
        showFullReview( getUrlParameter('review_id'), $('#cur_dent_id').val() );
    }

    handleReviewEvents = function() {

        $('.thumbs-up, .thumbs-down').off('click').click( function() {
            
            var type = $(this).hasClass('thumbs-up') ? 'useful' : 'unuseful';
            var that = this;
            $.ajax( {
                url: '/'+lang+'/'+type+'/' + $(this).closest('.review-wrapper').attr('review-id'),
                type: 'GET',
                dataType: 'json',
                success: (function( data ) {
                    if(data.success) {

                        $(that).closest('.review-footer').find('.thumbs-up span').html( data.upvotes );
                        $(that).closest('.review-footer').find('.thumbs-down span').html( data.downvotes );

                        var icon_up = $(that).closest('.review-footer').find('.thumbs-up');
                        var icon_down = $(that).closest('.review-footer').find('.thumbs-down');

                        if (data.upvote_status) {
                            icon_up.addClass('voted');
                        } else {
                            icon_up.removeClass('voted');
                        }

                        if (data.downvote_status) {
                            icon_down.addClass('voted');
                        } else {
                            icon_down.removeClass('voted');
                        }

                        icon_up.find('img').attr('src', data.upvote_image);
                        icon_down.find('img').attr('src', data.downvote_image);
                    }
                }).bind(that)
            }); 
        } );

        $('.reply-button.show-hide').off('click').click( function() {
            var tmp = $(this).attr('alternative');
            $('.reply-button.show-hide').attr('alternative', $(this).text());
            $('.reply-button.show-hide').html(tmp);
            $('.review-replied-wrapper:not(.reply-form)').toggle();
        });

        $('.reply-review').off('click').click( function() {
            $(this).closest('.review-wrapper').find('.reply-form').toggle();
        });

        $('.reply-form-element').off('submit').submit( function(e) {
            e.preventDefault();

            $(this).find('.alert').hide();

            var input = $(this).find('textarea').first();

            if( !input.val().trim().length ) {
                $(this).find('.alert').show();
                $('html, body').animate({
                    scrollTop: $(this).offset().top - 60
                }, 500);
                return;
            }

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
                        that.closest('.review-content').html(data.reply);
                    } else {
                        that.find('.alert').show();

                        $('html, body').animate({
                            scrollTop: that.offset().top - 60
                        }, 500);
                    }
                    ajax_is_running = false;
                }).bind(that), "json"
            );          

            return false;

        } );
        
    }
    handleReviewEvents();

    $('.more').click( function(e) {

        if (!$(e.target).closest('.review-replied-wrapper').length && !$(e.target).closest('.review-footer').length) {
            var id = $(this).closest('.review-wrapper').attr('review-id');
            showFullReview(id, $('#cur_dent_id').val());
        }
    } );

    $('.show-review').click( function(e) {
        var id = $(this).attr('review-id');
        showFullReview(id, $('#cur_dent_id').val());
    } );

    $('.scroll-to-map').click( function() {
        $('.profile-tabs .tab[data-tab="about"]').trigger('click');
        $('html, body').animate({
            scrollTop: $('.map-container').offset().top - $('header').height() - 40
        }, 500);        
    });


    if ($('#reviews-chart').length) {

        setTimeout( function() {
            $('.chart').each( function() {
                $(this).css('height', $(this).attr('to-height') * ( $(window).outerWidth() <= 768 ? 40 : 50) );
            });
        }, 200);
        
        if ($(window).outerWidth() < 980) {

            var prepended = false;
            $(window).on('scroll', function() {
                if(!prepended) {                    
                    $('.review-chart').prepend('<img class="slide-animation" src="'+window.location.origin+'/img-trp/slide.gif">');
                    $('.slide-animation').addClass('active');
                    $('.review-chart').on('click taphold swipe touchmove', function() {
                        $('.slide-animation').removeClass('active');
                    });
                    prepended = true;
                }
            });

        }
    }

    if (window.location.hash.length && $('.tab[data-tab="'+window.location.hash.substring(1)+'"]').length) {
        $('.tab[data-tab="'+window.location.hash.substring(1)+'"]').trigger( "click" );
    }

});