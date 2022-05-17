var videoStart, videoLength;
var slider = null;
var handleReviewEvents;
var showFullReview;
var handleDCNreward;
var galleryFlickty;
var suggestDentist;
var suggestClinic;
var suggestedDentistClick;
var suggestClinicClick;
var editor;
var fb_page_error;
var load_lightbox;
var loadFlickityJS = false;
var load_flickity = false;
var loadMapsJS = false;
var load_maps = false;

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

    var flickityFunctions = function() {

        if($('.gallery-flickity').length) {
            var galleryFlickty = $('.gallery-flickity').flickity({
                autoPlay: false,
                wrapAround: true,
                cellAlign: 'left',
                freeScroll: true,
                groupCells: 1,
                draggable: false,
            });
        }

        if($('.video-reviews-flickity').length) {
            $('.video-reviews-flickity').flickity({
                autoPlay: false,
                wrapAround: true,
                cellAlign: 'left',
                freeScroll: true,
                groupCells: 1,
                draggable: false,
            });
        }
    }

    var loadMap = function() {
        if( $('#profile-map').length && !$('#profile-map').attr('inited') ) {
            $('#profile-map').attr('inited', 'done');
            $('.info-address').hide();

            prepareMapFunction( function() {
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
            });
        }
    }

    var initJS = function() {
        if (!load_flickity ) {
            load_flickity = true;
            if (!loadFlickityJS ) {
                loadFlickityJS = true;
                $.getScript(window.location.origin+'/js/flickity.min.js', function() {
                    $('head').append('<link rel="stylesheet" type="text/css" href="'+window.location.origin+'/css/flickity.min.css">');
                    flickityFunctions();
                });
            } else {
                flickityFunctions();
            }
        }

        if(!load_maps) {
            load_maps = true;
            if (!loadMapsJS && typeof google === 'undefined' ) {
                loadMapsJS = true;
                $.getScript('https://maps.googleapis.com/maps/api/js?key=AIzaSyCaVeHq_LOhQndssbmw-aDnlMwUG73yCdk&libraries=places&callback=initMap&language=en', function() {
                    loadMap();
                });
            } else {
                loadMap();
            }
        }

        if (!load_lightbox ) {
            load_lightbox = true;
            $.getScript(window.location.origin+'/js/lightbox.js', function() {
                $('head').append('<link rel="stylesheet" type="text/css" href="'+window.location.origin+'/css/lightbox.css">');
            });
        }
    }

    if($(window).scrollTop() > 10) {
        initJS();
    }

    $(window).on('scroll', function() {
        initJS();
    });

    if(getUrlParameter('review_id')) {
        showFullReview( getUrlParameter('review_id'), $('#cur_dent_id').val() );
    }

    handleReviewEvents = function() {
        $('.reply-review').off('click').click( function() {
            $(this).closest('.regular-review').find('.reply-form').toggle();
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
        });
        
        $('.more').click( function(e) {
            if (!$(e.target).closest('.review-replied-wrapper').length && !$(e.target).closest('.review-footer').length) {
                var id = $(this).closest('.regular-review').attr('review-id');
                showFullReview(id, $('#cur_dent_id').val());
            }
        });

        $('.show-more-reviews').click( function() {
            let hidden_reviews = $('.hidden-review');
            // hidden_reviews.slice(0,2).removeClass('hidden-review');
            hidden_reviews.slice(0,10).removeClass('hidden-review');

            $(this).insertAfter($('.regular-review:not(.hidden-review)').last());

            if(!$('.hidden-review').length) {
                $(this).hide();
            }
        });

        $('[name="filter"]').change( function() {
            $(this).closest('.filter').find('.label').html($(this).attr('label'));

            let wrapper = $('.written-reviews');
            let reviews = $('.regular-review');

            if($(this).val() == 'newest') {
                reviews.sort(function(a, b) {
					if( parseInt($(a).attr('time')) > parseInt($(b).attr('time')) ) {
						return -1;
					} else {
						return 1;
					}
				});
            } else if($(this).val() == 'oldest') {
                reviews.sort(function(a, b) {
					if( parseInt($(a).attr('time')) < parseInt($(b).attr('time')) ) {
						return -1;
					} else {
						return 1;
					}
				});
            } else if($(this).val() == 'highest') {
                reviews.sort(function(a, b) {
					if( parseInt($(a).attr('rating')) > parseInt($(b).attr('rating')) ) {
						return -1;
					} else {
						return 1;
					}
				});
            } else if($(this).val() == 'lowest') {
                reviews.sort(function(a, b) {
					if( parseInt($(a).attr('rating')) < parseInt($(b).attr('rating')) ) {
						return -1;
					} else {
						return 1;
					}
				});
            }

            reviews.each(function() {
                wrapper.append(this);
            });

            if($('[name="type"]:checked').val() == 'trusted') { //only trusted reviews
                $('.regular-review[trusted="0"]').addClass('hidden-review');

                $('.show-more-reviews').insertAfter($('.regular-review:not(.hidden-review)').last());
            } else {
                $('.regular-review').addClass('hidden-review');
                $('.regular-review').slice(0,10).removeClass('hidden-review');
    
                $('.show-more-reviews').insertAfter($('.regular-review:not(.hidden-review)').last());
                if($('.hidden-review').length) {
                    $('.show-more-reviews').show();
                }
            }
        });

        $('[name="type"]').change( function() {
            $(this).closest('.filter').find('.label').html($(this).attr('label'));

            if($(this).val() == 'trusted') {
                $('.regular-review').addClass('hidden-review');
                $('.regular-review[trusted="1"]').removeClass('hidden-review');
                $('.show-more-reviews').hide();
            } else {
                $('.regular-review').addClass('hidden-review');
                $('.regular-review').slice(0,10).removeClass('hidden-review');
                $('.show-more-reviews').show();
            }
        });

        $('#search-review').on('keyup', function(e) {      
            $('#no-reviews').hide();

            if($(this).val()) {
                $('.show-more-reviews').hide();

                let searched_review = $(this);
                $('.regular-review').each( function() {
                    if ($(this).attr('find-in').indexOf(searched_review.val().toLowerCase()) >= 0 ) {
                        $(this).removeClass('hidden-review');
                    } else {
                        $(this).addClass('hidden-review');
                    }
                });

                if(!$('.regular-review:visible').length) {
                    $('#no-reviews').show();
                }
            } else {
                $('.regular-review').addClass('hidden-review');
                $('.regular-review').slice(0,10).removeClass('hidden-review');
                $('.show-more-reviews').show();
            }
        });
    }
    handleReviewEvents();

    $('.show-review').click( function(e) {
        var id = $(this).attr('review-id');
        showFullReview(id, $('#cur_dent_id').val());
    });

    $('.scroll-to-map').click( function() {
        $('.profile-tabs .tab[data-tab="about"]').trigger('click');
        $('html, body').animate({
            scrollTop: $('.map-container').offset().top - $('header').height() - 40
        }, 500);        
    });

    if ($('#append-section-reviews').length) {
        $(window).scroll( function() {
            if (!$('#append-section-reviews').hasClass('appended')) {
                $.ajax({
                    type: "POST",
                    url: lang + '/dentist-down/',
                    data: {
                        slug: window.location.pathname.split('/')[3],
                    },
                    success: function(ret) {
                        if(ret) {
                            if (!$('#append-section-reviews').hasClass('appended')) {
                                $('#append-section-reviews').append(ret);
                                $('#append-section-reviews').addClass('appended');

                                handleReviewEvents();
                                handlePopups();
                                attachTooltips();
                            }
                        }
                    },
                    error: function(ret) {
                        console.log('error');
                    }
                });
            }
        });
    }

    $('.show-full-announcement').click( function() {
        let announcement = $('.announcement-description');

        if(announcement.hasClass('active')) {
            announcement.html(announcement.attr('short-text'));
            $(this).html($(this).attr('short-text'));

            announcement.removeClass('active');
        } else {
            announcement.html(announcement.attr('long-text'));
            $(this).html($(this).attr('long-text'));
            
            announcement.addClass('active');
        }
    });

    $('.dentist-address').click( function() {
        $('body, html').animate({
            scrollTop: $('#locations').offset().top - $('header').outerHeight()
        }, 500);
    });

    $('.tab-title').click( function() {

        if(!$(this).hasClass('grayed')) {
            $('.tab-title').removeClass('active');
            $(this).addClass('active');
        }

        if($(this).hasClass('patients-tab')) {
            $('.tab-sections').hide();
            $('.asks-section').show();
        } else {
            $('.tab-sections').show();
            $('.asks-section').hide();
            $('body, html').animate({
                scrollTop: $('#'+$(this).attr('data-tab')).offset().top - $('header').outerHeight()
            }, 500);
        }
    });

    $('.show-video-reviews').click( function() {
        $('.reviews-type-buttons a').removeClass('active');
        $(this).addClass('active');
        $('.video-review-tab').show();
        $('.regular-review-tab').hide();

        if (!load_flickity ) {
            load_flickity = true;
            $.getScript(window.location.origin+'/js/flickity.min.js', function() {
                $('head').append('<link rel="stylesheet" type="text/css" href="'+window.location.origin+'/css/flickity.min.css">');
                flickityFunctions();
            });
        } else {
            flickityFunctions();
        }
    });

    $('.show-written-reviews').click( function() {
        $('.reviews-type-buttons a').removeClass('active');
        $(this).addClass('active');
        $('.video-review-tab').hide();
        $('.regular-review-tab').show();
    });

});