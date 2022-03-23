$(document).ready(function(){
    
    var search_map = new google.maps.Map(document.getElementById('search-map'), {
        center: {
            lat: parseFloat($('#search-map').attr('lat')), 
            lng: parseFloat($('#search-map').attr('lon'))
        },
        zoom: 13,
        backgroundColor: 'none'
    });

    mapMarkers = {};
    var bounds = new google.maps.LatLngBounds();

    bounds.extend({
        lat: parseFloat($('#search-map').attr('lat')), 
        lng: parseFloat($('#search-map').attr('lon'))
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

            if( $(window).width()<768 ) {
                var container = $('.search-results-wrapper .result-container[full-dentist-id="'+this.id+'"]');
                $('#map-mobile-tooltip').html( container.html() ).attr('href', container.attr('href') ).css('display', 'flex');

                for(var i in mapMarkers) {
                    mapMarkers[i].setIcon(images_path+'/map-pin-inactive.png');
                }

                this.setIcon(images_path+'/map-pin-active.png');
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

        // $('.sort-by a').removeClass('active');
        // $('.sort-by a[sort="rating"]').addClass('active');
        // $('input[name="sort"]').val('rating');

		// $('.search-get-form').attr('action', window.location.href.split('?')[0]);
    });

    $('.filter-options .specializations').change( function() {
    	let cats = [];
		let labels = $(this).closest('.filter-options').find('label.active');
        let form_href = $('.filter-options').closest('form').attr('base-href');
        let params = $('.search-get-form').serialize();

        if(labels.length) {
            labels.each( function() {
                cats.push($(this).find('input').val());
            });

            console.log(cats);
            $(this).closest('form').attr('action', form_href+'/'+cats.join('-')+'?'+params );
            
        } else {
            form_href = $('.filter-options').closest('form').attr('no-specializations-href');
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
        $('.filters-wrapper').show();
    });
});