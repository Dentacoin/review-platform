var initAddressSuggesters;
var checkAddress;
var setupMap;

jQuery(document).ready(function($){

    setupMap = function(conatiner, coords) {

        conatiner.find('.suggester-map-div').show();

        if( !conatiner.find('.suggester-map-div').attr('inited') ) {
            //init map
            var profile_address_map = new google.maps.Map( conatiner.find('.suggester-map-div')[0], {
                center: coords,
                zoom: 14,
                backgroundColor: 'none'
            });

            //add map pin
            var marker = new google.maps.Marker({
                map: profile_address_map,
                icon: images_path+'/map-pin-inactive.png',
                draggable:true,
                position: coords,
            });

            marker.addListener('dragend', function(e) {
                //on changing address
                this.map.panTo( this.getPosition() );
                
                var geocoder = new google.maps.Geocoder();
                geocoder.geocode({'location': this.getPosition()}, (function(results, status) {
                    if (status == 'OK') {
                        var gstring = results[0].formatted_address;
                        var country_name = this.find('.country-select option:selected').text();
                        gstring = gstring.replace(', '+country_name, '');

                        this.find('.address-suggester-input').val(gstring).blur();
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

        prepareMapFunction( function() {

            $('.address-suggester-input').each( function() {
            	var conatiner = $(this).closest('.address-suggester-wrapper-input');
	            
                //get addresses only from chosen country
	            conatiner.find('.country-select').change( function() {
	                var country_code = $(this).find('option:selected').attr('code');
	                GMautocomplete.setComponentRestrictions({
	                    'country': country_code
	                });
	            });

	            if( conatiner.find('.suggester-map-div').attr('lat') ) {
	            	var coords = {
                        lat: parseFloat( conatiner.find('.suggester-map-div').attr('lat') ), 
                        lng: parseFloat( conatiner.find('.suggester-map-div').attr('lon') )
                    };
                    setupMap(conatiner, coords);
	            }

	            var input = $(this)[0];
	            var country_code = conatiner.find('.country-select option:selected').attr('code');
	            var options = {
	                componentRestrictions: {
	                    country: country_code
	                },
	                types: $(this).hasClass('city-dentist') ? ['(cities)'] : ['address']              
	            };

	            var GMautocomplete = new google.maps.places.Autocomplete(input, options);
	            GMautocomplete.conatiner = conatiner;
	            google.maps.event.addListener(GMautocomplete, 'place_changed', (function () {
	            	var place = this.getPlace();
                    this.conatiner.find('.address-suggester-input').val(place.formatted_address ? place.formatted_address : place.name).blur();
	            }).bind(GMautocomplete));

                $(this).blur( function(e) {
                    var conatiner = $(this).closest('.address-suggester-wrapper-input');
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
                });
            });
        });

        $('.address-suggester-input').on('keyup keypress', function(e) {
            var keyCode = e.keyCode || e.which;
            if (keyCode === 13) { 
                e.preventDefault();
                return false;
            }
        });
	}

	checkAddress = function(place, conatiner) {
        conatiner.find('.geoip-hint').hide();
        conatiner.find('.different-country-hint').hide();
        conatiner.find('.geoip-confirmation').hide();
        conatiner.find('.suggester-map-div').hide();
        
    	if( place && typeof place.geometry !== null) {

    		if(conatiner.find('.country-select').length) {
                var gstring = conatiner.find('.address-suggester-input').val();
                var country_name = conatiner.find('.country-select option:selected').text();
                var country_code_name = conatiner.find('.country-select option:selected').attr('code').toUpperCase();
                var address_country;

                //get address -> country
                for (var i in place.address_components) {
                    for( var t in place.address_components[i].types) {
                        if (place.address_components[i].types[t] == 'country') {
                            address_country = place.address_components[i].short_name;
                            break;
                        }
                    }
                }

                if ( 
                    //kosovo
                    (country_code_name == 'XK' && (address_country == 'XK' || typeof address_country === 'undefined')) 
                    || (address_country == country_code_name) 
                ) {
                    gstring = gstring.replace(', '+country_name, '');
                    conatiner.find('.address-suggester-input').val(gstring);

                    setupMap(conatiner, {
                        lat: place.geometry.location.lat(), 
                        lng: place.geometry.location.lng()
                    });

                    conatiner.find('.geoip-confirmation').show();
                } else {
                    conatiner.find('.different-country-hint').show();
                }
            } else {
                var gstring = conatiner.find('.address-suggester-input').val();

                setupMap(conatiner, {
                    lat: place.geometry.location.lat(), 
                    lng: place.geometry.location.lng()
                });

                conatiner.find('.geoip-confirmation').show();
            }       
        } else {
            conatiner.find('.geoip-hint').show();
        }
	}

    if( $('.address-suggester-input').length && using_google_maps) {
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