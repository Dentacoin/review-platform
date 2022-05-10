$(document).ready(function(){
    
    var initResultsMap = function(popup=false) {    
        let map_container = popup ? $('#search-map-mobile') : $('#search-map');

        if(popup) {
            //for mobile map popup
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

        //init map
        let search_map = new google.maps.Map(popup ? document.getElementById('search-map-mobile') : document.getElementById('search-map'), {
            center: {
                lat: parseFloat(map_container.attr('lat')), 
                lng: parseFloat(map_container.attr('lon'))
            },
            zoom: 13,
            backgroundColor: 'none',
            mapTypeControl: false, //view Satellite/Map
            fullscreenControl: false
        });

        //input search in map
        var searchBox = new google.maps.places.SearchBox(document.getElementById('search-in-map'));
        search_map.controls[google.maps.ControlPosition.TOP_CENTER].push(document.getElementById('search-in-map'));
        google.maps.event.addListener(searchBox, 'places_changed', function() {
            searchBox.set('map', null);
            var places = searchBox.getPlaces();

            var bounds = new google.maps.LatLngBounds();
            var i, place;
            for (i = 0; place = places[i]; i++) {
                (function(place) {
                    var marker = new google.maps.Marker({
                        position: place.geometry.location
                    });
                    marker.bindTo('map', searchBox, 'map');
                    google.maps.event.addListener(marker, 'map_changed', function() {
                        if (!this.getMap()) {
                            this.unbindAll();
                        }
                    });
                    if (place.geometry.viewport) {
                        // Only geocodes have viewport.
                        bounds.union(place.geometry.viewport);
                    } else {
                        bounds.extend(place.geometry.location);
                    }
                }(place));
            }
            search_map.fitBounds(bounds);
            searchBox.set('map', search_map);
            search_map.setZoom(Math.min(search_map.getZoom(),12));
        });

        mapMarkers = {};
        let bounds = new google.maps.LatLngBounds();
        
        bounds.extend({
            lat: parseFloat(map_container.attr('lat')), 
            lng: parseFloat(map_container.attr('lon'))
        });

        //dentists profiles
        $('.result-container[lat]').each( function() {
            if( !$(this).attr('lat') || !$(this).attr('lon') ) {
                //if no address - return
                return false;
            }
            var dentist_id = $(this).attr('dentist-id');
            var LatLon = {
                lat: parseFloat($(this).attr('lat')), 
                lng: parseFloat($(this).attr('lon'))
            };
            //add pins
            mapMarkers[dentist_id] = new google.maps.Marker({
                position: LatLon,
                map: search_map,
                icon: images_path+'/map-pin-inactive.png',
                id: dentist_id,
            });
    
            bounds.extend(LatLon);
            
            //pins on mouseover
            google.maps.event.addListener(mapMarkers[dentist_id], 'mouseover', ( function() {
                $('.result-container[dentist-id="'+this.id+'"]').addClass('active');
                this.setIcon(images_path+'/map-pin-active.png');
            }).bind(mapMarkers[dentist_id]) );
    
            //pins on mouseout
            google.maps.event.addListener(mapMarkers[dentist_id], 'mouseout', ( function() {
                $('.result-container[dentist-id="'+this.id+'"]').removeClass('active');
                this.setIcon(images_path+'/map-pin-inactive.png');
            }).bind(mapMarkers[dentist_id]) );
    
            //pins on click
            google.maps.event.addListener(mapMarkers[dentist_id], 'click', ( function() {

                if( $(window).width()<=998 ) {
                    //for mobile and tablet
                    var container = $('.mobile-map-inner .result-container[dentist-id="'+this.id+'"]');

                    for(var i in mapMarkers) {
                        mapMarkers[i].setIcon(images_path+'/map-pin-inactive.png');
                    }
    
                    this.setIcon(images_path+'/map-pin-active.png');

                    //scroll to dentist profile
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
                    //for pc
                    var container = $('.result-container[dentist-id="'+this.id+'"]');
                    for(i=0;i<3;i++) {
                        //add blinking effect to dentist profile
                        container.fadeTo('slow', 0).fadeTo('slow', 1);
                    }
    
                    //scroll to dentist profile
                    var st = 0;
                    var prev = container.prev();
                    while(prev.length) {
                        st += prev.outerHeight();
                        prev = prev.prev();
                    }
                    $('.dentist-results').animate({
                        scrollTop: st
                    }, 500);

                    //zoom to map pin and show info popup
                    var myLatLng = new google.maps.LatLng( container.attr('lat'), container.attr('lon') );
                    search_map.panTo(myLatLng);
                    search_map.setZoom(14);
                    
                    var infowindow = new google.maps.InfoWindow({
                        content: container.find('.hidden-info-window').html()
                    });
                    infowindow.open({
                        anchor: mapMarkers[dentist_id],
                        search_map,
                        shouldFocus: true,
                    });
                }
            }).bind(mapMarkers[dentist_id]) );
        });

        $('.result-container.flex').click( function() { //click on dentist result
            window.location.href = $(this).attr('dentist-link');
        });

        $('.d-address').click( function(e) { //click on dentist address
            e.preventDefault();
            e.stopPropagation();
            
            var dentistResult = $(this).closest('.result-container');
            
            //zoom to map pin and show info popup
            var myLatLng = new google.maps.LatLng( dentistResult.attr('lat'), dentistResult.attr('lon') );
            search_map.panTo(myLatLng);
            search_map.setZoom(14);
            
            var infowindow = new google.maps.InfoWindow({
                content: dentistResult.find('.hidden-info-window').html()
            });
            infowindow.open({
                anchor: mapMarkers[dentistResult.attr('dentist-id')],
                search_map,
                shouldFocus: true,
            });
        });
    
        if( $('.result-container[lat]').length ) {
            search_map.fitBounds(bounds);
        } else {
            search_map.setZoom(12);
        }
    
        //activate pin
        $('.result-container').off('mouseover').mouseover( function() {
            var dentist_id = $(this).attr('dentist-id');					
            if( mapMarkers[dentist_id] ) {
                mapMarkers[dentist_id].setIcon(images_path+'/map-pin-active.png');	    								
            }
        });
    
        //deactivate pin
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
        
        //fit map popup to screen
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
            $('.search-get-form').attr('action', form_href );
        }
    });

    $('.clear-all-filters').click( function() {
        $('.checkbox-label').removeClass('active');
        $('.checkbox-label input[type="checkbox"]').prop('checked', false);
        $('.filter').removeClass('active');
        let form_href = $('.filter-options').closest('form').attr('no-specializations-href');
        $('.search-get-form').attr('action', form_href ).submit();
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
            
            // console.log(cats);
            $(this).closest('form').attr('action', form_href+cats.join('-') );
            // $(this).closest('form').attr('action', form_href+'/'+cats.join('-')+'?'+params );
            
        } else {
            let form_href = $('.filter-options').closest('form').attr('no-specializations-href');
            $(this).closest('form').attr('action', form_href );
            // $(this).closest('form').attr('action', form_href+'/'+'?'+params );
        }
	});

    $('.filter-options .filter-type').change( function() {

        if($('.filter-options .filter-type:checked').length) {

            if($(this).is(':checked')) {
                if($(this).val() == 'all') {
                    $(this).closest('.filter').find('.checkbox-label').removeClass('active');
                    $(this).closest('.checkbox-label').addClass('active');
                    $(this).closest('.filter').find('input').not(this).prop('checked', false);
                } else {
                    $(this).closest('.filter').find('input[value="all"]').prop('checked', false);
                    $(this).closest('.filter').find('input[value="all"]').closest('.checkbox-label').removeClass('active');
                }
            }
        } else {
            $(this).closest('.filter').find('input[value="all"]').prop('checked', true);
            $(this).closest('.filter').find('input[value="all"]').closest('.checkbox-label').addClass('active');
        }
	});

    $('.special-checkbox').change( function() {
        if($(this).closest('.filter-options').find('label.active').length) {
            $(this).closest('.filter').addClass('active');
        } else {
            $(this).closest('.filter').removeClass('active');
        }
    });

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

	$('.search-get-form').submit( function(e) {
		e.preventDefault();

		if (!$(this).attr('action')) {
			$(this).attr('action', window.location.href.split('?')[0]);
		}

		if ($(this).find('input[name="sort"]').val() == 'rating') {
			$(this).find('input[name="sort"]').val('');
		}

		var form_inputs = $(this).find(":input[value!='']").serialize();
        // if ($(this).attr('action').indexOf("?") >= 0) {

        // } else {
            window.location.href = $(this).attr('action')+ (form_inputs ? '?' : '')+form_inputs;
        // }
	});
});