var initAddressSuggesters;

jQuery(document).ready(function($){

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

                    conatiner.find('.suggester-map-div').show();
                    var profile_address_map = new google.maps.Map( conatiner.find('.suggester-map-div')[0], {
                        center: coords,
                        zoom: 14,
                        backgroundColor: 'none'
                    });

                    var marker = new google.maps.Marker({
                        map: profile_address_map,
                        center: coords,
                        position: coords,
                    });
	            }


	            var input = $(this)[0];
	            var cc = conatiner.find('.country-select option:selected').attr('code');
	            var options = {
	                componentRestrictions: {
	                    country: cc
	                },
	                types: ['address']                
	            };

	            console.log('hmm');

	            var GMautocomplete = new google.maps.places.Autocomplete(input, options);
	            GMautocomplete.conatiner = conatiner;
	            google.maps.event.addListener(GMautocomplete, 'place_changed', (function () {
	            	var place = this.getPlace();

	                this.conatiner.find('.address-suggester').blur();
	                this.conatiner.find('.geoip-hint').hide();
		            this.conatiner.find('.suggester-map-div').hide();

	            	if( place && place.geometry ) {
	            		//address_components
	            		console.log(place);
	            		console.log( place.formatted_address )
	            		console.log( place.types ); //street_address
	            		console.log( place.geometry.location.lat() )
	            		console.log( place.geometry.location.lng() )


	            		if( place.types.indexOf('street_address')!=-1 || place.types.indexOf('street_number')!=-1 ) {
	            			var cname = '';
	            			var newaddress = place.name + ', ' + place.vicinity;
	            			this.conatiner.find('.address-suggester').val(newaddress);

	            			prepareMapFucntion( (function() {
		                        var coords = {
		                            lat: place.geometry.location.lat(), 
		                            lng: place.geometry.location.lng()
		                        };

		                        this.conatiner.find('.suggester-map-div').show();
		                        var profile_address_map = new google.maps.Map( this.conatiner.find('.suggester-map-div')[0], {
		                            center: coords,
		                            zoom: 14,
		                            backgroundColor: 'none'
		                        });

		                        var marker = new google.maps.Marker({
		                            map: profile_address_map,
		                            center: coords,
		                            position: coords,
		                        });

		                    }).bind(this) );

		                    return;
	            		}
	               
	                }
	                
	                this.conatiner.find('.geoip-hint').show();
	            }).bind(GMautocomplete));

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