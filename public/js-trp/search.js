var slider = null;
var sliderTO = null;
var searchTO = null;
var displaySuggestions;
var sendSuggestions;
var autocomplete;
var form_href;

jQuery(document).ready(function($){

	$('#search-input').focus( function(e) {
		$('body').addClass('dark');
	});

	$('.black-overflow').click( function(e) {
		$('body').removeClass('dark');
		$('.search-form .results').hide();
	});

	//
	//Filters
	//

	$('.sort-by a').click( function() {
		$(this).parent().find('a').removeClass('active');
		$(this).addClass('active');
		$(this).parent().find('input').val( $(this).attr('sort') );
	} );



    $('.sort-stars .stars').mousemove( function(e) {
        var rate = e.offsetX;
        rate = Math.ceil( rate*5 / $(this).width() );

        $(this).find('.bar').css('width', (rate*20)+'%' );
    } ).click( function(e) {
        var rate = e.offsetX;
        rate = Math.ceil( rate*5 / $(this).width() );

        $(this).find('input').val(rate);
        $(this).find('.bar').css('width', (rate*20)+'%' );
    } ).mouseout( function(e) {
        var rate = parseInt($(this).find('input').val());
        if(rate) {
            $(this).find('.bar').css('width', (rate*20)+'%' );
        } else {
            $(this).find('.bar').css('width', 0 );
        }
    } );


    $('.clear-filters').click( function() {
        $('.sort-stars .stars input').val('');
        $('.sort-stars .stars .bar').css('width', '0%' );

        $('.checkbox-label').removeClass('active');
        $('.checkbox-label input[type="checkbox"]').prop('checked', false);

        $('.sort-by a').removeClass('active');
        $('.sort-by a[sort="rating"]').addClass('active');
        $('input[name="sort"]').val('rating');

    } );


    form_href = $('.sort-category').closest('form').attr('base-href');
    $('.sort-category .special-checkbox').change( function() {
    	
    	var cats = [];
		var labels = $(this).closest('.sort-category').find('label.active');

		labels.each( function() {
			cats.push($(this).find('input').val());
		});

		$(this).closest('form').attr('action', form_href+cats.join('-') )

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
	} );

	//
	//Maps
	//

	prepareMapFucntion( function() {

		$('#search-input').closest('form').submit( function(e) {
			e.preventDefault();

			var event = jQuery.Event("keyup");
			event.which = 13; // # Some key code value
			$('#search-input').trigger(event);


			console.log(event);
			return false;
		} );

		$('#search-input').on( 'keyup', function(e) {
            var keyCode = e.keyCode || e.which;
            if (keyCode === 40 || keyCode === 38) { //Down / Up
            	var activeLink = $('.search-form .results a.active');
            	if(activeLink.length) {
            		var where = activeLink.closest('.results-type').hasClass('dentists-results') ? 'dentists' : 'locations';
            		activeLink.removeClass('active');
            		if( keyCode === 40 ) { // Down
            			console.log('down');
            			if( activeLink.next().length ) {
            				console.log(1);
            				activeLink.next().addClass('active');
            			} else {
            				if(where=='dentists') {
            					if( $('.search-form .results .locations-results a').length ) {
            						console.log(2);
			            			$('.search-form .results .locations-results a').first().addClass('active');
			            		} else {
            						console.log(3);
            						$('.search-form .results .dentists-results a').first().addClass('active');
			            		}
            				} else {
            					if( $('.search-form .results .dentists-results a').length ) {
            						console.log(4);
			            			$('.search-form .results .dentists-results a').first().addClass('active');
			            		} else {
            						console.log(5);
            						$('.search-form .results .locations-results a').first().addClass('active');
			            		}

            				}
            			}
            		} else { // UP
            			if( activeLink.prev().length ) {
            				activeLink.prev().addClass('active');
            			} else {
            				if(where=='dentists') {
            					if( $('.search-form .results .locations-results a').length ) {
			            			$('.search-form .results .locations-results a').last().addClass('active');
			            		} else {
            						$('.search-form .results .dentists-results a').last().addClass('active');
			            		}
            				} else {
            					if( $('.search-form .results .dentists-results a').length ) {
			            			$('.search-form .results .dentists-results a').last().addClass('active');
			            		} else {
            						$('.search-form .results .locations-results a').last().addClass('active');
			            		}
            					
            				}
            			}

            		}
            	} else {
            		if( $('.search-form .results .locations-results a').length ) {
            			if (keyCode === 40) { // Down
            				$('.search-form .results .locations-results a').first().addClass('active');
            			} else {
            				$('.search-form .results .locations-results a').last().addClass('active');            				
            			}
            		} else if( $('.search-form .results .dentists-results a').length ) {
            			if (keyCode === 40) { // Down
            				$('.search-form .results .dentists-results a').first().addClass('active');
            			} else {
            				$('.search-form .results .dentists-results a').last().addClass('active');            				
            			}
            		}
            	}
            } else if (keyCode === 13) {
            	if( $('.search-form .results a.active').length ) {
            		window.location.href = $('.search-form .results a.active').first().attr('href');
            	} else if( $('.search-form .results .locations-results a').length ) {
            		window.location.href = $('.search-form .results .locations-results a').first().attr('href');
            	} else if( $('.search-form .results .dentists-results a').length ) {
            		window.location.href = lang + '/dentists/' + encodeURIComponent( $(this).val() ) + '/all-results';
            	}
            } else {
				if( $(this).val().length > 2 ) {
					//Show Loding
					if(searchTO) {
						clearTimeout(searchTO);
					}
					searchTO = setTimeout(sendSuggestions, 300);
				} else {
					$('.search-form .results').hide();
				}
            }
		});

		autocomplete = new google.maps.places.AutocompleteService();

	} );

	sendSuggestions = function() {
		$('.search-form .results').show();
		$('.search-form .results .locations-results').hide();
		$('.search-form .results .dentists-results').hide();
		$('.search-form .results .locations-results .list').html('');
		$('.search-form .results .dentists-results .list').html('');

		var query = $('#search-input').val();

		autocomplete.getPlacePredictions({ 
			input: query,
			types: ['(cities)'],
		}, displaySuggestions);

		$.ajax( {
			url: '/user-name',
			type: 'POST',
			data: {
				username: query
			},
			dataType: 'json',
			success: function( data ) {
				console.log( data );
				if(data.length) {
					$('.search-form .results .dentists-results').show();
					$('.search-form .results .dentists-results .list').html('');
					for(var i in data) {
						$('.search-form .results .dentists-results .list').append('\
							<a class="clearfix" href="'+data[i].link+'">\
								'+data[i].name+' - '+data[i].location+'\
								<div class="ratings">\
									<div class="stars">\
										<div class="bar" style="width: '+(data[i].rating ? parseFloat(data[i].rating)/5*100 : 0)+'%;">\
										</div>\
									</div>\
									<span class="rating">\
										('+(data[i].reviews ? data[i].reviews : '0')+' reviews)\
									</span>\
								</div>\
							</a>\
						');
						
					}
				}
				//  (2000 km away)
			},
			error: function(data) {
				;
			}
		});
	}

	displaySuggestions = function(predictions, status) {
		if (status != google.maps.places.PlacesServiceStatus.OK) {
			console.log(status);
			return;
		}

		$('.search-form .results .locations-results .list').html('').show();
		predictions.forEach(function(prediction) {
			// console.log( prediction );
			var href = prediction.description;
			href = href.replace(/\s+/g, '-').toLowerCase();
			href = href.replace(/\,/g, '');
			console.log(href);

			$('.search-form .results .locations-results .list').append('<a class="address-link" href="/'+lang+'/dentists/'+encodeURIComponent(href)+'">'+prediction.description+'</a>');
		});

		$('.search-form .results .locations-results').show();
	};
	


});