$(document).ready(function(){

	var shower = function(e){
	    $(this).closest('.btn-group').find('.btn').removeClass('btn-primary').addClass('btn-default');
	    $(this).closest('.btn').addClass('btn-primary').removeClass('btn-default');
	    $(this).closest('.btn').blur();

	    if( $('#radio-patient:checked').length ) {
	    	$('#register-div-patient').show();
	    	$('#register-div').hide();
	    } else {
	    	$('#register-div-patient').hide();
	    	$('#register-div').show();

	    	if( $('#radio-clinic:checked').length ) {
		    	$('#clinic-name-label').show();
		    	$('#dentist-name-label').hide();
		    	$('.clinic-show').show();
		    	$('.dentist-show').hide();
	    	} else {
		    	$('#clinic-name-label').hide();
		    	$('#dentist-name-label').show();
		    	$('.clinic-show').hide();
		    	$('.dentist-show').show();	    		
	    	}
	    }
	    e.stopPropagation();
	};

	$('.register-type').change( shower );
	$('label.btn').click( shower );

	$('.fb-register').click( function(e) {
		e.preventDefault();
		window.location.href = $(this).attr('href') + '/' + $('.register-type:checked').val();
	});


	$('#go-to-2').click( function(e) {

		e.preventDefault();

        $.post( 
            $(this).attr('data-validator'), 
            $('#register-form').serialize() , 
            function( data ) {
                if(data.success) {
					$('#register-error').hide();
                	$('#step-1').hide();
                	$('#step-2').show();

					var request = {
						type: ['dentist'],
						query: $('#dentist-name').val()
					};

					service = new google.maps.places.PlacesService( document.createElement('div') );
					service.textSearch(request, function(results, status) {
						if (status === 'OK' && results[0]) {
							$('#has-address').show();

							var dmap = new google.maps.Map($('#dentist-map')[0], {
								center: results[0].geometry.location,
		    					scrollwheel: false,
								zoom: 15
							});

							new google.maps.Marker({
								position: results[0].geometry.location,
								map: dmap,
								title: results[0].formatted_address
							});

							service.getDetails({
								placeId: results[0].place_id
							}, function(place, status) {
								console.log(status);
								console.log(place);
								if (status === 'OK' && place) {

									if( place.formatted_phone_number ) {
										$('#dentist-phone').val( place.formatted_phone_number );
									}
									if( place.website ) {
										$('#dentist-website').val( place.website );
									}

									var full_address = place.formatted_address;
									var ac = place.address_components;
									for(var i in ac) {
										if(ac[i].types.indexOf('postal_code')!=-1) {
											$('#dentist-zip').val( ac[i].long_name );
											full_address = full_address.replace( ' ,'+ac[i].long_name, '');
										}
										if(ac[i].types.indexOf('country')!=-1) {
											var code = ac[i].short_name.toLowerCase();
											console.log(code);
											$('#dentist-country').on('changed', (function() {
												for(var i in this) {
													if( this[i].types.indexOf('locality')!=-1 || this[i].types.indexOf('postal_town')!=-1  ) {
														console.log(this[i]);
														var city = this[i].long_name;
														var cid = $('#dentist-city option').filter(function () { return $(this).html() == city; }).val();
														if( cid ) {
															$('#dentist-city').val( cid );
														}
													}
												}
											}).bind(place.address_components) );
											$('#dentist-country').val( $('#dentist-country option[data-code="'+code+'"]').val() ).change();

											full_address = full_address.replace(', '+ac[i].long_name, '');
										}
										if( ac[i].types.indexOf('locality')!=-1 || ac[i].types.indexOf('postal_town')!=-1  ) {
											full_address = full_address.replace(', '+ac[i].long_name, '');
										}
									}

									if(full_address.length) {
										$('#dentist-address').val(full_address);										
									}
										
								}

								
							});
						} else {
							$('#no-address').show();
						}
					})


                } else {
					$('#register-error').show();
					$('#register-error span').html('');
					for(var i in data.messages) {
						$('#register-error span').append(data.messages[i] + '<br/>');
						$('input[name="'+i+'"]').closest('.form-group').addClass('has-error');
					}

	                $('html, body').animate({
	                	scrollTop: $('#register-error').offset().top - 60
	                }, 500);
	                grecaptcha.reset();
                }
                ajax_is_running = false;
            }, 
            "json"
        );

	} );

	$('#register-form').keydown(function(event){
		if(event.keyCode == 13) {
			if( $('#step-1').is(':visible') ) {
				event.preventDefault();
				$('#go-to-2').click();
				return false;				
			}
		}
	});





	$('#register-form').submit( function(e) {
		e.preventDefault();

		$(this).find('.alert').hide();
		$(this).find('.has-error').removeClass('has-error');

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
               		window.location.href = data.url;
                } else {
					$('#register-error').show();
					$('#register-error span').html('');
					for(var i in data.messages) {
						$('#register-error span').append(data.messages[i] + '<br/>');
						$('input[name="'+i+'"]').closest('.form-group').addClass('has-error');
					}

	                $('html, body').animate({
	                	scrollTop: $('#register-error').offset().top - 60
	                }, 500);
	                grecaptcha.reset();
                }
                ajax_is_running = false;
            }).bind(that), "json"
        );			


        return false;

    } );

    $('#register-form input').on('focus', function(e){
    	console.log($(this).closest('.form-group'));
	    $(this).closest('.form-group').removeClass('has-error');
	});


    //Gallery
    $('#add-avatar').change( function() {
        if(ajax_is_running) {
            return;
        }
        ajax_is_running = true;

        $('.add-photo').addClass('loading');

        var file = $(this)[0].files[0];
        var upload = new Upload(file, upload_url, function(data) {
            console.log(data);
            $('.add-photo').removeClass('loading');
            $('.add-photo').css('background-image', "url('"+data.thumb+"')");
            if($('.add-photo').find('.photo-cta').length) {
            	$('.add-photo').find('.photo-cta').remove();
            }
            $('#photo-name').val( data.name );
            ajax_is_running = false;
        });

        upload.doUpload();

    } );


    if( $('#register-civic-button').length ) {

        // Step 2: Instantiate instance of civic.sip
        var civicSip = new civic.sip({ appId: 'rkvErCDdf' });


         // Step 3: Start scope request.
        $('#register-civic-button').click(function () {
        	if( $(this).hasClass('loading') ) {
        		return;
        	}
        	$(this).addClass('loading');
            $('#civic-error').hide();
            $('#withdraw-widget .alert').hide();
            civicSip.signup({ style: 'popup', scopeRequest: civicSip.ScopeRequests.BASIC_SIGNUP });
        });

        var civicError = function() {
        	$('#register-civic-button').removeClass('loading')
            $('#register-civic-button').show();
        	$('#civic-wait').hide();
            $('html, body').animate({
                scrollTop: $("#register-civic-button").offset().top
            }, 500);
        }

        // Listen for data
        civicSip.on('auth-code-received', function (event) {
            console.log(event);
            var jwtToken = event.response;
            //sendAuthCode(jwtToken);

            $.ajax({
                type: "POST",
                url: 'https://dentacoin.net/civic',
                data: {
                    jwtToken: jwtToken
                },
                dataType: 'json',
                success: function(ret) {
                    if(!ret.userId) {
                        $('#civic-error').show();
                        civicError();
                    } else {

        				$('#civic-wait').show();
                        console.log(jwtToken);
                        setTimeout(function() {
                            $.post( 
                                $('#jwtAddress').val(), 
                                {
                                    jwtToken: jwtToken,
                                    '_token': $('#register-civic-button').closest('form').find('input[name="_token"]').val()
                                }, 
                                function( data ) {
                                    if(data.weak) {
                                        $('#civic-weak').show();
                                        civicError();
                                    } else if(data.success) {
                                    	if( data.redirect ) {
                                    		window.location.href = data.redirect;	
                                    	} else {
                                    		window.location.reload();
                                    	}
                                    } else {
                                        $('#civic-error').show();
                                        civicError();
                                    }
                                }, "json"
                            )
                            .fail(function(xhr, status, error) {
                                $('#civic-error').show();
                                civicError();
                            });
                        }, 3000);
                    }
                },
                error: function(ret) {
                    $('#civic-error').show();
                    civicError();
                }
            });

        });

        civicSip.on('user-cancelled', function (event) {
            $('#civic-cancelled').show();
            civicError();
        });

        civicSip.on('read', function (event) {
        	$('#civic-wait').show();
            console.log('read');
        });

        civicSip.on('civic-sip-error', function (error) {
            $('#civic-error').show();
            civicError();
            console.log('   Error type = ' + error.type);
            console.log('   Error message = ' + error.message);
        });
    }



});