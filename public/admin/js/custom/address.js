var initAddressSuggesters;
var checkAddress;
var setupMap;
var mapsWaiting;

jQuery(document).ready(function($){

    setupMap = function(conatiner, coords) {
        conatiner.find('.suggester-map-div').show();
        if( !conatiner.find('.suggester-map-div').attr('inited') ) {
            var profile_address_map = new google.maps.Map( conatiner.find('.suggester-map-div')[0], {
                center: coords,
                zoom: 14,
                backgroundColor: 'none'
            });
            var marker = new google.maps.Marker({
                map: profile_address_map,
                draggable:true,
                position: coords,
            });

            marker.addListener('dragend', function(e) {
                this.map.panTo( this.getPosition() );
                var container = $(this.map.getDiv()).closest('.address-suggester-wrapper');
                
                var geocoder = new google.maps.Geocoder();
                geocoder.geocode({'location': this.getPosition()}, (function(results, status) {
                    if (status == 'OK') {

                        var gstring = results[0].formatted_address;
                        var country_name = this.find('.country-select option:selected').text();
                        gstring = gstring.replace(', '+country_name, '');
                        console.log( gstring );

                        this.find('.address-suggester').val(gstring).blur();
                    } else {
                        checkAddress(null, this);
                    }
                }).bind(conatiner) );
            });
            conatiner.find('.suggester-map-div').attr('inited', 1);
            conatiner.find('.suggester-map-div').data('map', profile_address_map);
            conatiner.find('.suggester-map-div').data('marker', marker);
        } else {
            conatiner.find('.suggester-map-div').data('map').panTo(coords);
            conatiner.find('.suggester-map-div').data('marker').setPosition(coords);
        }
        
    }

	initAddressSuggesters = function() {

        prepareMapFucntion( function() {

            $('.address-suggester').each( function() {
            	var conatiner = $(this).closest('.address-suggester-wrapper');
	            
	            conatiner.find('.country-select').change( function() {
	                var cc = $(this).find('option:selected').attr('code');
	                GMautocomplete.setComponentRestrictions({
	                    'country': cc
	                });
	            } );


	            if( conatiner.find('.suggester-map-div').attr('lat') ) {
	            	var coords = {
                        lat: parseFloat( conatiner.find('.suggester-map-div').attr('lat') ), 
                        lng: parseFloat( conatiner.find('.suggester-map-div').attr('lon') )
                    };
                    setupMap(conatiner, coords);
	            }


	            var input = $(this)[0];
	            var cc = conatiner.find('.country-select option:selected').attr('code');
	            var options = {
	                componentRestrictions: {
	                    country: cc
	                },
	                types: ['address']                
	            };

	            var GMautocomplete = new google.maps.places.Autocomplete(input, options);
	            GMautocomplete.conatiner = conatiner;
	            google.maps.event.addListener(GMautocomplete, 'place_changed', (function () {
	            	var place = this.getPlace();
                    this.conatiner.find('.address-suggester').val(place.formatted_address ? place.formatted_address : place.name).blur();
	            }).bind(GMautocomplete));


                $(this).blur( function(e) {
                    var conatiner = $(this).closest('.address-suggester-wrapper');
                    var country_name = conatiner.find('.country-select option:selected').text();
                    var country_code = conatiner.find('.country-select option:selected').attr('code');


                    var geocoder = new google.maps.Geocoder();
                    var address = $(this).val();
                    geocoder.geocode( {
                        'address': address,
                        'region': country_code
                    }, (function(results, status) {
                        if (status == 'OK') {
                            checkAddress(results[0], this);
                        } else {
                            checkAddress(null, this);
                        }
                    }).bind(conatiner) );
                } );

            } )

            
        });

        $('.address-suggester').on('keyup keypress', function(e) {
            var keyCode = e.keyCode || e.which;
            if (keyCode === 13) { 
                e.preventDefault();
                return false;
            }
        });
	}

	checkAddress = function(place, conatiner) {
        //conatiner.find('.address-suggester').blur();
        conatiner.find('.geoip-hint').hide();
        conatiner.find('.geoip-confirmation').hide();
        conatiner.find('.suggester-map-div').hide();
            console.log('Geocoding result: ', place);
        
    	if( place && place.geometry && place.types && (place.types.indexOf('street_address') != -1)) {
    		//address_components
    		
            var gstring = conatiner.find('.address-suggester').val();
            var country_name = conatiner.find('.country-select option:selected').text();
            gstring = gstring.replace(', '+country_name, '');
            conatiner.find('.address-suggester').val(gstring);

            var coords = {
                lat: place.geometry.location.lat(), 
                lng: place.geometry.location.lng()
            };
            setupMap(conatiner, coords);

            conatiner.find('.geoip-confirmation').show();

            if ($('.scrape-submit').length) {
                $('.scrape-submit').removeAttr("disabled");
            }

            return;
       
        } else {
            conatiner.find('.geoip-hint').show();
            if ($('.scrape-submit').length) {
                $('.scrape-submit').prop('disabled', 'disabled');
            }
        }
        

	}

    if( $('.address-suggester').length ) {
    	initAddressSuggesters();
    }
});


function trimChar (str, c) {
  if (c === "]") c = "\\]";
  if (c === "\\") c = "\\\\";
  return str.replace(new RegExp(
    "^[" + c + "]+|[" + c + "]+$", "g"
  ), "");
}


prepareMapFucntion = function( callback ) {
    if(mapsLoaded) {
        callback();
    } else {
        mapsWaiting.push(callback);
    }
}

initMap = function () {
    mapsLoaded = true;
    for(var i in mapsWaiting) {
        mapsWaiting[i]();
    }

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

                    map = new google.maps.Map($(this)[0], {
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