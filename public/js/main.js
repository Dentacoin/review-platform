var ajax_is_running = false;
var locationTO = null;
var clinicTO = null;
var addressTO = null;
var addressRunning = null;
var address_selector = 'select[name="country_id"], select[name="city_id"], input[name="address"], input[name="zip"]';
var map;

$(document).ready(function(){

	// $('[data-toggle="tooltip"]').tooltip();

	$('#all-locations').change( function() {
		var active = $(this).is(':checked');
		if(active) {
			$(this).closest('form').find('.location-input').attr('disabled', 'disabled');
		} else {
			$(this).closest('form').find('.location-input').prop("disabled", false);
		}
	} )

	$('.country-select').change( function() {
		if(ajax_is_running) {
			return;
		}
		ajax_is_running = true;

    	var city_select = $(this).closest('form').find('.city-select').first();
    	city_select.attr('disabled', 'disabled');
    	var that = this;
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
    				city_select.append('<option value="'+i+'">'+data.cities[i]+'</option>');
			    }
			    $('.phone-code-holder').html(data.code);
			    ajax_is_running = false;
				//city_select
				//$('#modal-message .modal-body').html(data);
				$(that).trigger('changed');
			},
			error: function(data) {
				console.log(data);
			    ajax_is_running = false;
			}
		});

    });

	$('.location-dropdown a').click( function() {
		var parent = $(this).closest('.site-content');
		parent.find('.search-wrapper input[name="country"]').val($(this).attr('data-country-id'));
		parent.find('.search-wrapper input[name="city"]').val(' ');
		parent.find('.search-wrapper input[type="submit"]').click();
	});

	$('select[name="type"]').on('change', function (e) {
	    var optionSelected = $("option:selected", this);
	    var valueSelected = this.value;
	    console.log(valueSelected);

	    if('dentist' == valueSelected) {
	    	$('.type-toggle-dentist').attr('checked', 'checked');
	    	$('.type-toggle-clinic').removeAttr('checked');
	    	console.log('dentist')
	    } else if('clinic' == valueSelected) {
	    	$('.type-toggle-dentist').removeAttr('checked');
	    	$('.type-toggle-clinic').attr('checked', 'checked');
	    	console.log('clinic')
	    } else {
	    	$('.type-toggle-dentist').attr('checked', 'checked');
	    	$('.type-toggle-clinic').attr('checked', 'checked');
	    	console.log('all')
	    }
	});


	$('.type-toggle').on('change', function (e) {
		var valueSelected = '';

		if( $('.type-toggle-dentist').is(':checked') && !$('.type-toggle-clinic').is(':checked') ) {
	    	$('#search_type').val('dentist');			
		} else if( !$('.type-toggle-dentist').is(':checked') && $('.type-toggle-clinic').is(':checked') ) {
	    	$('#search_type').val('clinic');
		} else if( !$('.type-toggle-dentist').is(':checked') && !$('.type-toggle-clinic').is(':checked') ) {
			if( $(this).hasClass('type-toggle-dentist') ) {
				$('.type-toggle-clinic').attr('checked', 'checked');
	    		$('#search_type').val('clinic');
			} else {
				$('.type-toggle-dentist').attr('checked', 'checked');
	    		$('#search_type').val('dentist');
			}
		} else {
	    	$('#search_type').val('');
		}

	    $('#search-form').submit();
	});

    $('.write-review').click( function() {
        $('#review-form').addClass('active');
    });

    $('.closer').click( function() {
        $(this).closest('.new-popup').removeClass('active');
    });

    $('#read-privacy').change( function(e) {
    	if ($(this).is(':checked')) {
    		$(this).parent().parent().find('#register-civic-button').css('display', 'inline-block');
    		$(this).parent().parent().find('.fb-button-inside').css('display', 'block');
    	} else {
    		$(this).parent().parent().find('#register-civic-button').hide();
    		$(this).parent().parent().find('.fb-button-inside').hide();
    	}
    });

    $('.new-btn-show-review').click(function() {
    	var review_id = $(this).attr('data-user-id');
		$.ajax( {
			url: lang+'/full-review/'+review_id,
			type: 'GET',
			success: function( data ) {
				$('#show-review .inner').html( data );
				$('#show-review').addClass('active');
			}
		});
        
    });

    $('#btn-show-whole-review').click(function() {
        $('#show-whole-review-form').addClass('active');
        
    });

    $('.new-popup').click( function(e) {
        if (!$(e.target).closest('.new-popup-wrapper').length) {
            $('.new-popup').removeClass('active');
        }
    });

    $('.agree-gdpr').click( function() {
    	$.ajax( {
			url: '/'+lang +'/accept-gdpr',
			type: 'GET',
			success: function( data ) {
				$('#gdprPopup').removeClass('active');
			}
		});
    });

    setInterval( function() {
		$.ajax( {
			url: '/question-count',
			type: 'GET',
			dataType: 'json',
			success: function( data ) {
				//console.log(data);
				var my_amount = parseInt($('#header-balance').html()) * data.dcn_price_full

				$('#header-rate').html(data.dcn_price);
				$('#header-change').html('('+data.dcn_change+'%)');
				$('#header-usd').html( '$' + parseFloat(my_amount).toFixed(2) );
			}
		});

    }, 10000 );

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
			return;
		}

    	if(ajax_is_running) {
			return;
		}
		ajax_is_running = true;

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

				var len = 0;
				for(var i in data) {
					len++;
				}

				if (len) {
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
				} else {
					container.html('No places found by that name');
				}
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


 	var userSuggester = function() {
		if( $(this).val().trim().length < 4 ) {
			return;
		}

    	if(ajax_is_running) {
			return;
		}
		ajax_is_running = true;

		$(this).closest('.location').addClass('loading');

		var that = $(this);

    	$.ajax( {
			url: 'user-name',
			type: 'POST',
			dataType: 'json',
			data: {
				username: $(this).val()
			},
			success: (function( data ) {
				that.closest('.location').removeClass('loading').addClass('visible');
				var container = that.closest('.location').find('.results').first();
				if (data.length) {
					container.html('');
					for(var i in data) {
						container.append('<a href="'+data[i].link+'" >'+data[i].name+'<span '+((data[i].is_clinic == 1) ? "class='clinic-color'" : "class='dentist-color'") +'">'+data[i].type+'</span></a>');
					}
				} else {
					container.html('No clinic/dentist found by that name');
				}
			    ajax_is_running = false;
			}).bind(that)
		});
    };

    $('.user-input').keyup( function() {
    	if(locationTO) {
    		clearTimeout(locationTO);
    	}

    	locationTO = setTimeout(userSuggester.bind(this), 300);
    } );

    var clinicSuggester = function() {
        if( $(this).val().trim().length < 4 ) {
            return;
        }

        if(ajax_is_running) {
            return;
        }
        ajax_is_running = true;

        $(this).closest('.clinic-suggester').addClass('loading');

        var that = $(this);

        $.ajax( {
            url: 'suggest-clinic/'+user_id,
            type: 'POST',
            dataType: 'json',
            data: {
                joinclinic: $(this).val()
            },
            success: (function( data ) {
            	console.log(data);
	            that.closest('.clinic-suggester').removeClass('loading').addClass('visible');
	            var container = that.closest('.clinic-suggester').find('.results').first();

            	if (data.length) {
	                container.html('');
	                for(var i in data) {
	                    container.append('<a href="javascript:;" data-id="'+data[i].id+'">'+data[i].name+'</a>');
	                }

	                container.find('a').click( function() {
	                    $('#joinclinicid').val( $(this).attr('data-id') );
	                    $(this).closest('.clinic-suggester').removeClass('visible');
	                    $(this).closest('form').find('#joinclinic').val( $(this).html() );
	                } );
	            } else {
	            	container.html('No clinics found by that name');
	            }
	            ajax_is_running = false;

            }).bind(that)
        });
    };

    $('#joinclinic').keydown( function(event) {

        if( event.keyCode == 13) {
            event.preventDefault();
            event.stopPropagation();
            return false;
        }

        if(clinicTO) {
            clearTimeout(clinicTO);
        }

        clinicTO = setTimeout(clinicSuggester.bind(this), 300);
    } );


	$('body').click( function(e) {
		if (!$(e.target).closest('.dentist-suggester').length) {
			$('.dentist-suggester').removeClass('visible');
		}

		if (!$(e.target).closest('.clinic-suggester').length) {
			$('.clinic-suggester').removeClass('visible');
		}

		if (!$(e.target).closest('.location').length) {
			$('.location').removeClass('visible');
		}
	});


    $('.open-search-box').click( function() {
    	$(this).closest('.col-md-8').find('.search-box-after').show();
    	$(this).closest('.col-md-8').find('.search-box-before').hide();
    });

    $('.close-search-box').click( function() {
    	$(this).closest('.col-md-8').find('.search-box-before').show();
    	$(this).closest('.col-md-8').find('.search-box-after').hide();
    });


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

	if( $('#address-validator').length ) {

		$('input[name="address"]').closest('.form-group').after( $('#address-validator') );

		$('#locations-confirm-btn').click( function() {
			$(address_selector).closest('.form-group').hide();
			$('#locations-title').hide();			
			$('#locations-confirm').hide();
			$('#locations-go-here a').each( function() {
				if( $(this).hasClass('active') ) {
					$('#google-place-id').val( $(this).attr('data-place-id') );
				} else {
					$(this).hide();
				}
			} );
			$('#locations-go-here a').off('click');
		} );

		var handleAddress = function() {
			if(addressRunning) {
				return;
			}

			addressRunning = true;

			var country = $('select[name="country_id"] option:selected').html();
			var city = $('select[name="city_id"] option:selected').html();
			var address = $('input[name="address"]').val();
			var zip = $('input[name="zip"]').val();


			console.log(country, city, address, zip);

			if(address.length) {
				var long_address = country + ', ' + city + (zip.length ? ',' + zip : '') + ', ' + address;
				$(address_selector).attr('disabled', 'disabled')

				var geocoder = new google.maps.Geocoder();
				geocoder.geocode({'address': long_address}, function(results, status) {
					$(address_selector).removeAttr('disabled');
					console.log(status, results);
					
					$('#address-validator').show();
					$('#locations-go-here').html('');
					$('#locations-error').hide();
					$('#locations-ok').hide();
					$('#locations-confirm').hide();

					if (status === 'OK') {

						var request = {
							location: results[0].geometry.location,
							radius: '200',
							type: ['dentist']
						};

						service = new google.maps.places.PlacesService( document.createElement('div') );
						service.nearbySearch(request, function(results, status) {
							console.log(status, results);
							if (status === 'OK') {
								if(results.length) {
									for(var i in results) {
										var place_html = $('<a class="col-md-12 location-place" data-place-id="'+results[i].id+'" href="javascript:;"></a>');
										place_html.append( $('<h4>'+results[i].name+'</h4>') );
										place_html.append( $('<span>'+results[i].vicinity+'</span>') );
										$('#locations-go-here').append(place_html);
									}
									$('#locations-ok').show();
									$('#locations-go-here a.location-place').click( function() {
										$('#locations-go-here a.location-place').removeClass('active');
										$(this).addClass('active');
										$('#locations-confirm').show();
									});
									addressRunning = false;
								} else {
									$('#locations-error').show();	
									addressRunning = false;								
								}
							} else {
								$('#locations-error').show();		
								addressRunning = false;						
							}
						});
					} else {
						$('#locations-error').show();
						addressRunning = false;
					}
					//resultsMap.setCenter(results[0].geometry.location);
				});				
			} else {
				$('#address-validator').hide();
				addressRunning = false;
			}

		}

		$('#google-place-id').val( '' );
		$(address_selector).on('change', handleAddress)
		$(address_selector).on('keyup', function() {
			if(addressTO) {
	    		clearTimeout(addressTO);
	    	}

	    	addressTO = setTimeout(handleAddress, 2000);
		});
	}

	
	$('#set-email-form').submit( function(e) {
		e.preventDefault();
		if(ajax_is_running) {
			return;
		}
		ajax_is_running = true;
		$('#set-email-error').hide();

        $.post( 
            $(this).attr('action'), 
            $(this).serialize() , 
            function( data ) {
                if(data.success) {
                	$('#set-email-form').hide();
                	$('#email-refresh').show();
                	$('#verify-email-span').html( $('#verify-email').val() );
                } else {
                	$('#set-email-error').show().html(data.message);
                }
                ajax_is_running = false;
            }, "json"
        );

	} );


	$('#stop-submit').click( function(e) {
		e.preventDefault();
		if(ajax_is_running) {
			return;
		}
		ajax_is_running = true;
        $('#stop-error').hide();
        
        $.post( 
            '/wait', 
            {
            	email: $('#stop-email').val(),
            	name: $('#stop-name').val(),
            }, 
            function( data ) {
            	console.log(data);
                if(data.success) {
                	$('#stop-submit').after( $('<div style="margin-top: 20px; margin-bottom: 0px;" class="alert alert-success">Thank you. We\'ll let you know as soon as registrations are open again.</div>') );
                	$('#stop-submit').remove();
                } else {
                	if( !$('#stop-error').length ) {
                		$('#stop-submit').after( $('<div style="margin-top: 20px; margin-bottom: 0px;" class="alert alert-warning" id="stop-error"></div>') );
                	}
                	$('#stop-error').html('').show();
                	for(var i in data.messages) {
                		$('#stop-error').append(data.messages[i] + '<br/>');
                	}
                }
                ajax_is_running = false;
            }, "json"
        );

	} );

}


var Upload = function (file, url, success) {
    this.file = file;
    this.url = url;
    this.success = success
};

Upload.prototype.getType = function() {
    return this.file.type;
};
Upload.prototype.getSize = function() {
    return this.file.size;
};
Upload.prototype.getName = function() {
    return this.file.name;
};
Upload.prototype.doUpload = function () {
    var that = this;
    var formData = new FormData();

    // add assoc key values, this will be posts values
    formData.append("image", this.file, this.getName());
    formData.append("upload_file", true);

    $.ajax({
        type: "POST",
        url: this.url,
        xhr: function () {
            var myXhr = $.ajaxSettings.xhr();
            if (myXhr.upload) {
                myXhr.upload.addEventListener('progress', that.progressHandling, false);
            }
            return myXhr;
        },
        success: this.success,
        error: function (error) {
            ajax_is_running = false;
        },
        async: true,
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        timeout: 60000
    });
};

Upload.prototype.progressHandling = function (event) {
    var percent = 0;
    var position = event.loaded || event.position;
    var total = event.total;
    var progress_bar_id = "#progress-wrp";
    if (event.lengthComputable) {
        percent = Math.ceil(position / total * 100);
    }
    console.log(percent);
};