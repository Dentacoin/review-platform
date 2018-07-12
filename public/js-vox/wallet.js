$(document).ready(function(){

    var balanceUpdater = function() {
        $.post( 
            $('#balance-address').val(), 
            $('#balance-form').serialize() , 
            function( data ) {
                if(data.success) {
                    $('#my-balance').val(data.result);
                } else {
                }
            }, "json"
        );
    }

    if($('#balance-address').length) {
        balanceUpdater();
    }


    $('#withdraw-form').submit( function(e) {
        e.preventDefault();
        if(ajax_is_running) {
            return;
        }
        ajax_is_running = true;
        $('#withdraw-form .alert').hide();
        
        var btn = $(this).find('button[type="submit"]').first();
        btn.attr('data-old', btn.html());
        btn.html('<i class="fa fa-spinner fa-pulse fa-fw"></i> '+btn.attr('data-loading'));

        $.post( 
            $(this).attr('action'), 
            $(this).serialize() , 
            function( data ) {
                if(data.success) {
                    if(data.link) {
                        $('#withdraw-success').show();
                        $('#withdraw-success a').attr('href', data.link);
                        $('#withdraw-success a').html(data.link);
                    } else {
                        $('#withdraw-pending').show();
                    }
                    //balanceUpdater();
                    if(data.balance) {
                        $('#menu-balance').html(data.balance + ' DCN');
                        $('#header-balance').html(data.balance);
                    }
                } else {
                    $('#withdraw-error').show();
                    if(data.message) {
                        $('#withdraw-reason').show().html( data.message );
                    }
                }
                ajax_is_running = false;
                btn.html( btn.attr('data-old') );

            }, "json"
        );

    } );


    // Step 2: Instantiate instance of civic.sip
    var civicSip = new civic.sip({ appId: 'rkvErCDdf' });


     // Step 3: Start scope request.
    var button = document.querySelector('#signupButton');
    button.addEventListener('click', function () {
        $('#has-wallet .alert').hide();
        $('#signupButton').hide();
        civicSip.signup({ style: 'popup', scopeRequest: civicSip.ScopeRequests.BASIC_SIGNUP });
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