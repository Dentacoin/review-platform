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
                                    } else if(data.popup) {
                                        $('#'+data.popup).addClass('active');
                                    } else if(data.success) {
                                    	if( data.redirect ) {
                                    		window.location.href = data.redirect;	
                                    	} else {
                                    		window.location.reload();
                                    	}
                                    } else {
                                        $('#civic-error').show();
                                        $('#civic-error span').html(data.message);
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

    const fb_config = {
        //app_id: '299398824049604',
        app_id: '1906201509652855',
        platform: 'fb'
    };

    var fb_custom_btn = $('.fb-login-button-new');

    //application init
    window.fbAsyncInit = function () {
        FB.init({
            appId: fb_config.app_id,
            cookie: true,
            xfbml: true,
            version: 'v2.8'
        });
        FB.AppEvents.logPageView();
    };

    (function (d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) {
            return;
        }
        js = d.createElement(s);
        js.id = id;
        js.src = '//connect.facebook.net/bg_BG/sdk.js';
        fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));

    $('.fb-login-button-new').click( function(rerequest){

        var that = $(this);

        FB.login(function (response) {

            if(response.authResponse && response.status == "connected") {
                $('#new-login-form input[name="access-token"]').val(response.authResponse.accessToken);
                $('#new-login-form').submit();
            }
        });
    });

});