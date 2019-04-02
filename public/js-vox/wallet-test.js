$(document).ready(function(){
    // Step 2: Instantiate instance of civic.sip
    var civicSip = new civic.sip({ appId: 'rkvErCDdf' });


     // Step 3: Start scope request.
    var button = document.querySelector('#signupButton');
    button.addEventListener('click', function () {
        $('#has-wallet .alert').hide();
        $('#signupButton').hide();
        civicSip.signup({ style: 'popup', scopeRequest: civicSip.ScopeRequests.PROOF_OF_IDENTITY });
    });

    var civicError = function() {
        $('#signupButton').show();
        $('#civic-wait').hide();
        $('html, body').animate({
            scrollTop: $("#signupButton").offset().top
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
                    alert('All good');
                    $('#civic-wait').show();
                    $('#signupButton').hide();

                    console.log(jwtToken);
                    setTimeout(function() {
                        $.post( 
                            $('#jwtAddress').val(), 
                            {
                                jwtToken: jwtToken
                            }, 
                            function( data ) {
                                if(data.weak) {
                                    $('#civic-weak').show();
                                    civicError();
                                } else if(data.duplicate) {
                                    $('#civic-duplicate').show();
                                    civicError();
                                } else if(data.success) {
                                    window.location.reload();
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


});