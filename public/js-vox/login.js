$(document).ready(function(){

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
                url: 'https://civic.dentacoin.net/civic',
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

                            $('#new-civic-login-form input[name="jwtToken"]').val(jwtToken);
                            $('#new-civic-login-form').submit();

                            // $.post( 
                            //     $('#jwtAddress').val(), 
                            //     {
                            //         jwtToken: jwtToken,
                            //         '_token': $('#register-civic-button').closest('form').find('input[name="_token"]').val()
                            //     }, 
                            //     function( data ) {
                            //         if(data.weak) {
                            //             $('#civic-weak').show();
                            //             civicError();
                            //         } else if(data.popup) {
                            //             $('#'+data.popup).addClass('active');
                            //         } else if(data.success) {
                            //         	if( data.redirect ) {
                            //         		window.location.href = data.redirect;	
                            //         	} else {
                            //         		window.location.reload();
                            //         	}
                            //         } else {
                            //             $('#civic-error').show();
                            //             $('#civic-error span').html(data.message);
                            //             civicError();
                            //         }
                            //     }, "json"
                            // )
                            // .fail(function(xhr, status, error) {
                            //     $('#civic-error').show();
                            //     civicError();
                            // });



                            
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

    $('#register-grace-button').click( function() {
        $('.new-auth').addClass('active');
    } );


    $('#grace-button').click( function() {
        $.ajax({
            type: "GET",
            url: lang + '/profile/setGrace',
        });
        $('.new-auth').remove();
    } );

    $('.new-auth .x').click( function() {
        $('.new-auth').removeClass('active');
    } );

});