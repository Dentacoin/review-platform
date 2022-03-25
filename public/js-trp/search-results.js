$(document).ready(function(){
    
    var initResultsMap = function(popup=false) {    
        let map_container = popup ? $('#search-map-mobile') : $('#search-map');

        if(popup) {
            var width = 0;
            var slider = $('.mobile-map-inner .result-container');

            slider.each( function() {
                width+=$(this).outerWidth();
            });

            $('.mobile-map-inner').css('width', width + (
                slider.length * (
                    parseInt(slider.css('margin-left')) + parseInt(slider.css('margin-right'))
                ) 
            ));
        }
        let search_map = new google.maps.Map(popup ? document.getElementById('search-map-mobile') : document.getElementById('search-map'), {
            center: {
                lat: parseFloat(map_container.attr('lat')), 
                lng: parseFloat(map_container.attr('lon'))
            },
            zoom: parseInt(map_container.attr('zoom')),
            backgroundColor: 'none',
            mapTypeControl: popup ? false : true,
            fullscreenControl: false
        });

        mapMarkers = {};
        let bounds = new google.maps.LatLngBounds();
        
        bounds.extend({
            lat: parseFloat(map_container.attr('lat')), 
            lng: parseFloat(map_container.attr('lon'))
        });

        $('.result-container[lat]').each( function() {
            if( !$(this).attr('lat') || !$(this).attr('lon') ) {
                return false;
            }
            var dentist_id = $(this).attr('dentist-id');
            var LatLon = {
                lat: parseFloat($(this).attr('lat')), 
                lng: parseFloat($(this).attr('lon'))
            };
            mapMarkers[dentist_id] = new google.maps.Marker({
                position: LatLon,
                map: search_map,
                icon: images_path+'/map-pin-inactive.png',
                id: dentist_id,
            });
    
            bounds.extend(LatLon);
    
            google.maps.event.addListener(mapMarkers[dentist_id], 'mouseover', ( function() {
                $('.result-container[dentist-id="'+this.id+'"]').addClass('active');
                this.setIcon(images_path+'/map-pin-active.png');
            }).bind(mapMarkers[dentist_id]) );
    
            google.maps.event.addListener(mapMarkers[dentist_id], 'mouseout', ( function() {
                $('.result-container[dentist-id="'+this.id+'"]').removeClass('active');
                this.setIcon(images_path+'/map-pin-inactive.png');
            }).bind(mapMarkers[dentist_id]) );
    
            google.maps.event.addListener(mapMarkers[dentist_id], 'click', ( function() {
                if( $(window).width()<=998 ) {
                    var container = $('.mobile-map-inner .result-container[dentist-id="'+this.id+'"]');

                    for(var i in mapMarkers) {
                        mapMarkers[i].setIcon(images_path+'/map-pin-inactive.png');
                    }
    
                    this.setIcon(images_path+'/map-pin-active.png');

                    var st = 0;
                    var prev = container.prev();
                    while(prev.length) {
                        st += prev.outerWidth() + parseInt(prev.css('margin-left')) + parseInt(prev.css('margin-right'));
                        prev = prev.prev();
                    }

                    $('.mobile-map-results').animate({
                        scrollLeft: st
                    }, 500);

                } else {
                    var container = $('.result-container[dentist-id="'+this.id+'"]');
                    for(i=0;i<3;i++) {
                        container.fadeTo('slow', 0).fadeTo('slow', 1);
                    }
    
                    var st = 0;
                    var prev = container.prev();
                    while(prev.length) {
                        st += prev.outerHeight();
                        prev = prev.prev();
                    }
                    $('.dentist-results').animate({
                        scrollTop: st
                    }, 500);
                }
            }).bind(mapMarkers[dentist_id]) );
        });
    
        if( $('.result-container[lat]').length ) {
            search_map.fitBounds(bounds);
        } else {
            search_map.setZoom(12);
        }
    
        $('.result-container').off('mouseover').mouseover( function() {
            var dentist_id = $(this).attr('dentist-id');					
            if( mapMarkers[dentist_id] ) {
                mapMarkers[dentist_id].setIcon(images_path+'/map-pin-active.png');	    								
            }
        });
    
        $('.result-container').off('mouseout').mouseout( function() {
            var dentist_id = $(this).attr('dentist-id');					
            if( mapMarkers[dentist_id] ) {
                mapMarkers[dentist_id].setIcon(images_path+'/map-pin-inactive.png');	    		
            }
        });
    
        if(search_map.getZoom() > 16) {
            search_map.setZoom(16);
        }
    }

    if($(window).outerWidth() > 998) {
        initResultsMap();
    }
    
    $('.open-map').click( function() {
		$('body').addClass('popup-visible');
        
        if($('header').is(':visible')) {
            $('#map-results-popup').css('top', $('header').outerHeight());
            $('#map-results-popup .mobile-map-results').css('margin-top', '-300px');
        } else {
            $('#map-results-popup').css('top', 0);
            $('#map-results-popup .mobile-map-results').css('margin-top', '-240px');
        }

        $('#map-results-popup').show();
        if(!$('#search-map-mobile').children().length) {
            initResultsMap(true);
        }
    });
    
    $('.close-map').click( function() {
		$('body').removeClass('popup-visible');
        $('#map-results-popup').hide();
    });

	//
	//Filters
	//

	$('.sort-by a').click( function() {
		$(this).parent().find('a').removeClass('active');
		$(this).addClass('active');
		$(this).parent().find('input').val( $(this).attr('sort') );
	});

    $('.clear-filters').click( function() {
        $(this).closest('.filter-options').find('.checkbox-label').removeClass('active');
        $(this).closest('.filter-options').find('.checkbox-label input[type="checkbox"]').prop('checked', false);
        $(this).closest('.filter').removeClass('active');

        if($(this).hasClass('clear-specializations')) {
            let form_href = $('.filter-options').closest('form').attr('no-specializations-href');
            let params = $('.search-get-form').serialize();
            $('.search-get-form').attr('action', form_href+'/'+'?'+params );
        }
        // $('.sort-by a').removeClass('active');
        // $('.sort-by a[sort="rating"]').addClass('active');
        // $('input[name="sort"]').val('rating');

		// $('.search-get-form').attr('action', window.location.href.split('?')[0]);
    });

    $('.clear-all-filters').click( function() {
        $('.checkbox-label').removeClass('active');
        $('.checkbox-label input[type="checkbox"]').prop('checked', false);
        $('.filter').removeClass('active');
        let form_href = $('.filter-options').closest('form').attr('no-specializations-href');
        let params = $('.search-get-form').serialize();
        $('.search-get-form').attr('action', form_href+'/'+'?'+params ).submit();
    });

    $('.filter-options .specializations').change( function() {
    	let cats = [];
		let labels = $(this).closest('.filter-options').find('label.active');
        let params = $('.search-get-form').serialize();
        
        if(labels.length) {
            let form_href = $('.filter-options').closest('form').attr('specializations-href');
            labels.each( function() {
                cats.push($(this).find('input').val());
            });
            
            console.log(cats);
            $(this).closest('form').attr('action', form_href+'/'+cats.join('-')+'?'+params );
            
        } else {
            let form_href = $('.filter-options').closest('form').attr('no-specializations-href');
            $(this).closest('form').attr('action', form_href+'/'+'?'+params );
        }
	});

    $('.filter-order').change( function() {
        $(this).closest('.filter').find('.filter-order-active-text').html($(this).closest('label').attr('label-text'));
    });

    $('.special-checkbox').change( function() {
        if($(this).closest('.filter-options').find('label.active').length) {
            $(this).closest('.filter').addClass('active');
        } else {
            $(this).closest('.filter').removeClass('active');
        }
    });

	//
	//Search Results
	//
	

	$('.result-container [data-popup], .result-container [href]').click( function(e) {
		e.stopPropagation();
		e.preventDefault();

		if( $(this).attr('href') ) {
			if( $(this).attr('target')=='_blank' ) {
				window.open($(this).attr('href'));
			} else {
				window.location.href = $(this).attr('href');				
			}
		}
	});

    $('.open-filters').click( function() {
        $('.filters-wrapper').addClass('open');
		$('body').addClass('popup-visible');
    });

    $('.close-filter').click( function() {
        $('.filters-wrapper').removeClass('open');
		$('body').removeClass('popup-visible');
    });

    if( $(window).width()<=768 ) {
        $('body').click( function(e) {
            if($(e.target).closest('.filter').length || $(e.target).closest('.filter-options').length) {
            } else {
                $('.filter-options').hide();
            }
        });

        $('.filter').click( function() {
            $(this).find('.filter-options').css('top', $(this).offset().top + $(this).outerHeight() + 5);
            $('.filter-options').hide();
            $(this).find('.filter-options').show();
        });
    }
});