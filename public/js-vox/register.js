$(document).ready(function(){

	$('#register-civic-button').click( function(e) {
	});

    if( $('#register-civic-button').length ) {

        // Step 2: Instantiate instance of civic.sip
        var civicSip = new civic.sip({ appId: 'rkvErCDdf' });


         // Step 3: Start scope request.
        $('#register-civic-button').click(function () {
			if (!$('#read-privacy').prop("checked")) {
				$('#read-privacy').closest('.form-group').addClass('has-error');
				return;
			}
			
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

    const fb_config = {
        //app_id: '299398824049604',
        app_id: '1906201509652855',
        platform: 'fb'
    };

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


    $('.fb-register-button-new').click( function(rerequest){
        rerequest.preventDefault();

        if ($('#read-privacy').prop("checked")) {
            var that = $(this);

            FB.login(function (response) {

                if(response.authResponse && response.status == "connected") {
                    var ac_token = response.authResponse.accessToken;

                    $.ajax({
                        type: "POST",
                        url: that.attr('url'),
                        data: {
                            access_token: ac_token,
                            _token: $('input[name="_token"]').val()
                        },
                        dataType: 'json',
                        success: function(ret) {
                            if(ret.success == true) {

                                window.location.href = ret.link;

                            } else {
                                if (ret.message) {
                                    $('.reg-false-alert').html(ret.message);
                                }
                                if (ret.link) {
                                    window.location.href = ret.link;
                                }
                            }
                        },
                        error: function(ret) {
                            console.log('error')
                        }
                    });
                }
            }, {scope: 'email'});
        } else {
            $('#read-privacy').closest('.form-group').addClass('has-error');
        }        
    });

});