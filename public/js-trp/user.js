var videoStart, videoLength;
var handleReviewEvents;
var showFullReview;
var handleDCNreward;
var handleActivePopupFunctions;
var suggestedDentistClick;
var editor;
var fb_page_error;
var load_lightbox;
var loadFlickityJS = false;
var load_flickity = false;
var loadMapsJS = false;
var load_maps = false;
var handleEdit;

$(document).ready(function() {

    //dentist verifies review from patient
    var verifyReview = function() {
        $('.verify-review').click( function() {

            if(ajax_is_running) {
                return;
            }
            ajax_is_running = true;

            var that = $(this);
            var review_id_param = $(this).closest('.written-review').length ? $(this).closest('.written-review').attr('review-id') : $('.details-wrapper').attr('review-id');

            $.ajax({
                type: "POST",
                url: window.location.origin+'/en/verify-review/',
                data: {
                    review_id: review_id_param,
                    _token: $('input[name="_token"]').val(),
                },
                dataType: 'json',
                success: function(ret) {
                    if(ret.success) {
                        if(that.closest('.written-review').length) {
                            that.closest('.written-review').find('.trusted-sticker').show();
                        } else {
                            that.closest('.details-wrapper').find('.review-rating-new').addClass('verified-review');
                            that.closest('.details-wrapper').find('.review-rating-new .trusted').show();
                            $('.written-review[review-id="'+review_id_param+'"]').find('.trusted-sticker').css('display', 'inline-flex');
                        }

                        that.hide();
                    }
                    ajax_is_running = false;
                },
                error: function(ret) {
                    console.log('error');
                    ajax_is_running = false;
                }
            });
        });
    }
    verifyReview();
    
    showFullReview = function(id, d_id) {
        showPopup('view-review-popup');

        $('#the-detailed-review').html('<div class="loader-mask">\
            <div class="loader">\
                "Loading..."\
            </div>\
        </div>');

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
                verifyReview();
            }).bind(id)
        });
    }

    //load page down sections on scroll (google page speed)
	if($('#to-append').length) {

		$(window).scroll( function() {
			if (!$('#to-append').hasClass('appended')) {
				$('#to-append').addClass('appended');
				$.ajax({
					type: "POST",
					url: lang + '/dentist-down/',
                    data: {
                        slug: window.location.pathname.split('/')[3],
                    },
					success: function(ret) {
						$('#to-append').append(ret);
                        
                        handleActivePopupFunctions();
						handleClickToOpenPopups();
                        handleReviewEvents();
                        attachTooltips();
                        verifyReview();
                        handleEdit();
                        fixedTabs();
                        initJS();
					},
					error: function(ret) {
						console.log('error');
					}
				});
			}
		});
	}

    var flickityFunctions = function() {

        if($('.gallery-flickity').length) {
            $('.gallery-flickity').flickity({
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

        if($('.highlights-flickity').length) {
            $('.highlights-flickity').flickity({
                autoPlay: false,
                wrapAround: true,
                cellAlign: 'center',
                freeScroll: true,
                groupCells: 1,
                draggable: false,
            });

            $('.highlights-flickity').each( function() {
                var mh = 0;
                $(this).find('.hightlight').css('height', 'auto');
                $(this).find('.hightlight').each( function() {
                    if( $(this).outerHeight() > mh ) {
                        mh = $(this).outerHeight();
                    }
                });
                $(this).find('.hightlight').css('height', mh+'px');
            });
        } else {
            if($('.highlights-mobile-flickity').length && $(window).outerWidth() <= 768) {
                $('.highlights-mobile-flickity').flickity({
                    autoPlay: false,
                    wrapAround: true,
                    cellAlign: 'center',
                    freeScroll: true,
                    groupCells: 1,
                    draggable: false,
                });
            }
        }
        
        if($('.address-flickity').length) {
            $('.address-flickity').flickity({
                autoPlay: false,
                wrapAround: true,
                cellAlign: 'left',
                freeScroll: true,
                groupCells: 1,
                draggable: false,
            });

            $('.address-flickity').on( 'select.flickity', function( event, index ) {
                $('.carousel-status').html(index+1+' of '+$('.address-slider').length);
            });
        }
    }

    var loadMap = function() {
        if( $('.profile-map').length) {

            $('.profile-map').each( function() {
                var that = $(this);

                if(!that.attr('inited') ) {

                    that.attr('inited', 'done');
                    $('.info-address').hide();
        
                    prepareMapFunction( function() {
                        var profile_map = new google.maps.Map(document.getElementById(that.attr('id')), {
                            center: {
                                lat: parseFloat(that.attr('lat')), 
                                lng: parseFloat(that.attr('lon'))
                            },
                            zoom: 15,
                            backgroundColor: 'none'
                        });
        
                        var mapMarker = new google.maps.Marker({
                            position: {
                                lat: parseFloat(that.attr('lat')), 
                                lng: parseFloat(that.attr('lon'))
                            },
                            map: profile_map,
                            icon: images_path+'/map-pin-active.png',
                        });
                    });
                }
            });
        }
    }

    var initJS = function() {

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

        if (!load_lightbox ) {
            load_lightbox = true;
            $.getScript(window.location.origin+'/js/lightbox.js', function() {
                $('head').append('<link rel="stylesheet" type="text/css" href="'+window.location.origin+'/css/lightbox.css">');
            });
        }
    }

    //user sections
    var fixedTabs = function() {
        
        if(!$('.tab-title.patients-tab.active').length) {
            if($(window).scrollTop() > $('.tab-sections').offset().top - 100) {
                if(!$('.tab-titles').hasClass('fixed-tabs')) {
                    $('.tab-titles').addClass('fixed-tabs');
                }
            } else {
                if($('.tab-titles').hasClass('fixed-tabs')) {
                    $('.tab-titles').removeClass('fixed-tabs');
                }
            }
    
            var active_section = null;
            $('.tab-title:not(.grayed):not(.patients-tab)').each( function() {
                if($(this).offset().top > $('#'+$(this).attr('data-tab')).offset().top - 150) {
                    active_section = $(this).attr('data-tab');
                }
            });
    
            if(active_section) {
                $('.tab-title').removeClass('active');
                $('.tab-title[data-tab="'+active_section+'"]').addClass('active');
            }

            if($(window).outerWidth() <= 768) {
                let left = 0;
                let prev = $('.tab-title[data-tab="'+active_section+'"]').prev();
                
                while(prev.length) {
                    left += prev.outerWidth();
                    prev = prev.prev();
                }

                $('.tab-titles .container').animate({
                    scrollLeft: left
                }, 10);
            }
        }
    }

    if($(window).scrollTop() > 10) {
        fixedTabs();
        initJS();
    }

    $(window).on('scroll', function() {
        fixedTabs();
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
                        that.closest('.reply-form').after('<div class="review-replied-wrapper">\
                            <img class="review-avatar" src="'+data.dentist_avatar+'"/>\
                            <div>\
                                <p class="replied-info">\
                                    <img src="'+images_path+'/reply-icon.svg" />Replied by '+data.dentist_name+'\
                                </p>\
                                <p class="review-content">'+data.reply+'</p>\
                            </div>\
                        </div>');

                        that.closest('.written-review').find('.reply-review').remove();
                        that.closest('.reply-form').remove();
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
                                handleClickToOpenPopups();
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

        if($(this).hasClass('patients-tab')) {
            if(!$(this).hasClass('grayed')) {
                $('.tab-sections').hide();
                $('.asks-section').show();
                $('.tab-titles').removeClass('fixed-tabs');
                $('.tab-title').removeClass('active');
                $(this).addClass('active');
            }
        } else {
            if($('.tab-title.patients-tab.active').length && !$(this).hasClass('patients-tab')) {
                $('.tab-title').removeClass('active');
                $(this).addClass('active');
            }

            $('.tab-sections').show();
            $('.asks-section').hide();
            if($('#'+$(this).attr('data-tab')).length) {
                $('body, html').animate({
                    scrollTop: $('#'+$(this).attr('data-tab')).offset().top - $('header').outerHeight() - $('.tab-titles').outerHeight()
                }, 500);
            }
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