var ajax_is_running = false;
var locationTO = null;

$(document).ready(function(){

	$('[data-toggle="tooltip"]').tooltip();

	$('.country-select').change( function() {
		if(ajax_is_running) {
			return;
		}
		ajax_is_running = true;

    	var city_select = $(this).closest('form').find('.city-select').first();
    	city_select.attr('disabled', 'disabled');
		$.ajax( {
			url: '/cities/' + $(this).val(),
			type: 'GET',
			dataType: 'json',
			success: function( data ) {
				city_select.attr('disabled', false)
			    .find('option')
			    .remove();
    			city_select.append('<option value="">-</option>');
			    for(var i in data.cities) {
    				city_select.append('<option value="'+i+'">'+data[i]+'</option>');
			    }
			    $('.phone-code-holder').html(data.code);
			    ajax_is_running = false;
				//city_select
				//$('#modal-message .modal-body').html(data);
			}
		});

    } );

    function fixRatings() {
    		console.log('ale')
    	if($(window).width()<992) {
    		$('.ratings').each( function() {
    			$(this).insertAfter( $(this).closest('.rating-line').find('.rating-right') );
    		} );
    	} else {
    		$('.ratings').each( function() {
    			$(this).insertBefore( $(this).closest('.rating-line').find('.rating-right') );
    		} );
    	}
    }
    fixRatings();
    $(window).resize(fixRatings)

    var locationEvent = function() {
    	console.log( $(this) );
		if( $(this).val().trim().length < 4 ) {
			console.log('wtf');
			return;
		}

    	if(ajax_is_running) {
			return;
		}
		ajax_is_running = true;

		console.log('PRE-AJAX');

		$(this).closest('.location').addClass('loading');

		var that = $(this);

    	$.ajax( {
			url: 'location',
			type: 'POST',
			dataType: 'json',
			data: {
				location: $(this).val()
			},
			success: (function( data ) {
				console.log('AJAX!');
				that.closest('.location').removeClass('loading').addClass('visible');
				var container = that.closest('.location').find('.results').first();
				console.log(container);
				container.html('');
				for(var i in data) {
					container.append('<a href="javascript:;" data-country="'+data[i].country+'" data-city="'+data[i].city+'" >'+data[i].name+'</a>');
					if(i>10) {
						break;
					}
				}
				container.find('a').click( function() {
					$(this).closest('form').find('.country_id').val( $(this).attr('data-country') );
					$(this).closest('form').find('.city_id').val( $(this).attr('data-city') );
					$(this).closest('.location').removeClass('visible');
					$(this).closest('form').find('input.location-input').val( $(this).html().replace('<b>', '').replace('</b>', '') );
				} );
			    ajax_is_running = false;
			}).bind(that)
		});
    };

    $('.location-input').keyup( function() {
    	if(locationTO) {
    		clearTimeout(locationTO);
    	}

    	locationTO = setTimeout(locationEvent.bind(this), 300);
    } );

	$('.sharer a').click( function() {
		var href = $(this).closest('.sharer').attr('data-href');
		console.log(href);
		if ($(this).hasClass('fb')) {
			var url = 'https://www.facebook.com/dialog/share?app_id=1906201509652855&display=popup&href=' + escape(href);
		} else if ($(this).hasClass('tw')) {
			var url = 'https://twitter.com/share?url=' + escape(href) + '&text=' + document.title;
		} else if ($(this).hasClass('gp')) {
			var url = 'https://plus.google.com/share?url=' + escape(href);
		}
		console.log(url);
		window.open( url , 'ShareWindow', 'height=450, width=550, top=' + (jQuery(window).height() / 2 - 275) + ', left=' + (jQuery(window).width() / 2 - 225) + ', toolbar=0, location=0, menubar=0, directories=0, scrollbars=0');
	});

});



function initMap(){
	$('.map').each( function(){
		var address = $(this).attr('data-address') ;

		var geocoder = new google.maps.Geocoder();
		geocoder.geocode( { 'address': address}, (function(results, status) {
			console.log(address);
			console.log(status);
	        if (status == google.maps.GeocoderStatus.OK) {
				if (status != google.maps.GeocoderStatus.ZERO_RESULTS) {
					var position = {
						lat: results[0].geometry.location.lat(), 
						lng: results[0].geometry.location.lng()
					};

					var map = new google.maps.Map($(this)[0], {
						center: position,
    					scrollwheel: false,
						zoom: 15
					});

					new google.maps.Marker({
						position: position,
						map: map,
						title: results[0].formatted_address
					});

				} else {
					console.log('456');
					$(this).remove();
				}
			} else {
				console.log('123');
				$(this).remove();
			}
		}).bind( $(this) )  );

	});
}