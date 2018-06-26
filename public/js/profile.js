$(document).ready(function(){

    $('.bxslider').bxSlider({
        useCSS: true,
        responsive: true,
    });

    //Selects
    $(".select-me").on("click focus", function () {
        $(this).select();
    });

    //Avatars
	$('#add-avatar').change( function(){

        if(ajax_is_running) {
            return;
        }
        ajax_is_running = true;

	    var file = $(this)[0].files[0];
	    var upload = new Upload(file, $(this).closest('form').attr('action'), function (data) {
            console.log(data);
            $('#avatar-add').removeClass('loading').addClass('has-image');
            $('#avatar-add').find('img').attr('src', data.url + '?rand='+Math.random());
            ajax_is_running = false;
            // your callback here
        });

	    $(this).closest('form').removeClass('has-image').addClass('loading');

	    upload.doUpload();
	    
	});

    //Dashboard Boxes 

    if($('.panel-profile-dashboard').length) {
        function fixHeights() {
       
            $('.panel-profile-dashboard .panel').css('height', 'auto');
     
            if (window.innerWidth > 990) {
     
                $('.panel-profile-dashboard .form-group').each( function() {
                    var maxh = 0;
                    $(this).find('.panel').each( function() {
                        if( $(this).outerHeight() > maxh) {
                            maxh = $(this).outerHeight();
                        }
                    });
                    $(this).find('.panel').css('height', maxh);
                });
            }
        }
        $(window).resize(fixHeights);
        fixHeights();
    }

    $('.changer').click( function() {
        $('#add-avatar').trigger('click'); 
    } )

    //Gallery
    $('.gallery-pic input').change( function() {
        if(ajax_is_running) {
            return;
        }
        ajax_is_running = true;

        if($(this).closest('.gallery-pic').hasClass('empty')) {
            var position = 8 - $('.gallery-pic.empty').length;
        } else {
            var position = $(this).closest('.gallery-pic').attr('data-position');            
        }


        $('#gallery-photo-'+position).removeClass('empty').addClass('loading');

        var file = $(this)[0].files[0];
        var upload = new Upload(file, $(this).closest('form').attr('action') + '/' + position, function(data) {
            console.log(data);
            $('#gallery-photo-'+data.position).removeClass('loading');
            $('#gallery-photo-'+data.position).find('img').attr('src', data.url + '?rand='+Math.random());
            ajax_is_running = false;
        });

        upload.doUpload();

    } );

    $('.gallery-pic .deleter').click( function(e) {
        e.preventDefault();
        if(ajax_is_running) {
            return;
        }
        ajax_is_running = true;

        r = confirm(areYouSure);
        if(r) {
            $.ajax( {
                url: $(this).attr('href'),
                type: 'GET',
                dataType: 'json',
                success: function( data ) {
                    ajax_is_running = false;
                    window.location.reload();
                }
            });

        }
    } );
    $('.gallery-pic .editor').click( function(e) {
        $(this).closest('.gallery-pic').find('input').click();
    });

    //Invites
    if( $('#invite-patient-form').length ) {

        $('#invite-patient-form').submit( function(e) {
            e.preventDefault();

            $('#invite-alert').hide();

            if(ajax_is_running) {
                return;
            }

            ajax_is_running = true;

            $('#invite-alert').hide().removeClass('alert-warning').removeClass('alert-success');

            $.post( 
                $(this).attr('action'), 
                $(this).serialize() , 
                function( data ) {
                    if(data.success) {
                        $('#invite-email').val('');
                        $('#invite-name').val('').focus();
                        $('#invite-alert').show().addClass('alert-success').html(data.message);
                    } else {
                        $('#invite-alert').show().addClass('alert-warning').html(data.message);                    
                    }
                    ajax_is_running = false;
                }, "json"
            );

            return false;
        } );

        $('#share-contacts-form').submit( function(e) {
            e.preventDefault();
            if(ajax_is_running) {
                return;
            }
            ajax_is_running = true;

            $.post( 
                $(this).attr('action'), 
                $(this).serialize() , 
                function( data ) {
                    if(data.success) {
                        $('#contacts-results').hide();
                        $('#contacts-alert').show().addClass('alert-success').html(data.message);
                    } else {
                        $('#contacts-alert').show().addClass('alert-warning').html(data.message);                    
                    }
                    ajax_is_running = false;
                }, "json"
            );

            return false;
        } );

        hello.on('auth.login', function(auth) {

            // Call user information, for the given network
            hello(auth.network).api('me').then(function(r) {
                console.log( auth.network );
                console.log( r );
            });
        });

        $('.btn-share-contacts').click( function() {
            $('#contacts-alert').hide();
            $('#contacts-results').hide();
            $('#contacts-error').hide();
            $('#contacts-results-empty').hide();
            var network = $(this).attr('data-netowrk');
            // login
            hello(network).login({scope:'friends'}).then(function(auth) {
                // Get the friends
                // using path, me/friends or me/contacts
                hello(network).api('me/'+(network=='yahoo' ? 'friends' : 'contacts'), {limit:1000}).then(function responseHandler(r) {
                    console.log(r);
                    var found = false;
                    $('#contacts-results-list').html('');
                    for(var i in r.data) {
                        if(r.data[i].email && r.data[i].email.indexOf('@')!=-1) {
                            $('#contacts-results-list').append('<label for="contact-'+i+'" class="form-control"><input id="contact-'+i+'" type="checkbox" name="contacts[]" value="'+(r.data[i].name ? r.data[i].name+'|' : '')+r.data[i].email+'" /> '+(r.data[i].name ? r.data[i].name+' ('+r.data[i].email+')' : r.data[i].email)+'</label>');
                            found = true;
                        }
                    }
                    if(!found) {
                        $('#contacts-results-empty').show();
                    } else {
                        $('#contacts-results').show();                        
                    }
                });
            }, function() {
                if(!auth||auth.error){
                    $('#contacts-error').show();
                    console.log("Signin aborted");
                    return;
                }
            });
            
        } )


        hello.init({
            windows: 'f5c6f6f7-aed0-4477-8ad2-d6b264b0a491',
            google: '313352423951-bl64tutb9f7fdl2bjljgref1lriujinp.apps.googleusercontent.com',
            yahoo: 'dj0yJmk9TUkySmtOUndhRWZFJmQ9WVdrOU9XUnlSemR3Tm5FbWNHbzlNQS0tJnM9Y29uc3VtZXJzZWNyZXQmeD03NQ--'
        }, {
            redirect_uri: socials_redirect_url,
            scope: "basic,friends",
            oauth_proxy: 'https://auth-server.herokuapp.com/proxy'
        });

        $('#search-contacts').on( 'change keyup', function() {
            var s = $(this).val().toLowerCase();
            if(s.length>3) {
                $('#contacts-results-list label').hide();
                $('#contacts-results-list label').each( function() {
                    if( $(this).find('input').first().val().toLowerCase().indexOf( s ) !=-1 ) {
                        $(this).show();
                    }
                } );

            } else {
                $('#contacts-results-list label').show();
                
            }
        } );

    }

    //Wallet
    $('#transfer-form').submit( function(e) {
        e.preventDefault();

        sendDCN( $('#transfer-wallet-address').val(), $('#transfer-wallet-amount').val() );
        
    });

    //Rewards
    
    $('#reward-form').submit( function(e) {
        e.preventDefault();
        if(ajax_is_running) {
            return;
        }
        ajax_is_running = true;

        $('.panel-body-reward .alert').hide();

        var dcn_address = $('#transfer-reward-address').val();
        if ( typeof web3 !== 'undefined' && !web3.isAddress(dcn_address) ) {
            $('#reward-invalid').show();
            ajax_is_running = false;
            return;
        }

        var btn = $(this).find('button[type="submit"]').first();
        btn.attr('data-old', btn.html());
        btn.html('<i class="fa fa-spinner fa-pulse fa-fw"></i> '+btn.attr('data-loading'));

        $.ajax({
            type: "POST",
            url: $(this).attr('action'),
            data: $(this).serialize(),
            dataType: 'json',
            success: function (data) {

                if(data.success) {
                    $('#reward-form').remove();
                    window.location.reload();
                } else {
                    $('#reward-error').show().html( data.message );
                }

                btn.html( btn.attr('data-old') );
                ajax_is_running = false;
            },
            error: function (error) {
                $('#reward-error').show();
                ajax_is_running = false;
            }
        });
        
    });

    $('#balance-form').submit( function(e) {
        e.preventDefault();

        $('#has-no-wallet .alert').hide();

        var dcn_address = $('#transfer-balance-address').val();

        $.ajax({
            type: "POST",
            url: $(this).attr('action'),
            data: $(this).serialize(),
            dataType: 'json',
            success: function (data) {
                if(data.success) {
                    $('#balance-succcess').show();
                    $('#balance-amount').html(data.result + ' DCN');
                    $('#balance-succcess a').attr('href', 'https://etherscan.io/token/0x08d32b0da63e2C3bcF8019c9c5d849d7a9d791e6?a=' + dcn_address);
                } else {
                    $('#balance-error').show();
                }
            },
            error: function (error) {
                $('#balance-error').show();
            }
        });
        
    });


    if( $('#withdraw-widget').length ) {

        if( $('#civic-widget').length ) {

            // Step 2: Instantiate instance of civic.sip
            var civicSip = new civic.sip({ appId: 'rkvErCDdf' });


             // Step 3: Start scope request.
            var button = document.querySelector('#signupButton');
            button.addEventListener('click', function () {
                $('#withdraw-widget .alert').hide();
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
                            $('.profile-balance').html(data.balance);
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

    }


    //Profile info
    $('.work-hour-cb').change( function() {
        var active = $(this).is(':checked');
        console.log(active);
        var texts = $(this).closest('.form-group').find('input[type="text"]');
        if(active) {
            texts.prop("disabled", false);
        } else {
            texts.attr('disabled', 'disabled');
        }
    } )

    //Widget

    $('.btn-group-justified label').click( function() {
        var id = $(this).attr('for');
        $('.option-div').hide();
        $('#option-mode').show();
        $('#widget-preview').show();
        $('#'+id).show();
        $('.btn-group-justified .btn').removeClass('btn-primary');
        $(this).addClass('btn-primary');
    } );

    var refreshWidgetCode = function() {
        if(typeof widet_url=='undefined') {
            return
        }
        var wmode = parseInt($('.widget-modes input:checked').val());
        wmode = isNaN(wmode) ? 0 : wmode;
        var parsedUrl = widet_url.replace('{mode}', wmode);
        $('#option-iframe textarea').val('<iframe style="width: 100%; height: 50vh; border: none; outline: none;" src="'+parsedUrl+'"></iframe>');
        $('#option-js textarea').val('<div id="trp-widget"></div><script type="text/javascript" src="https://reviews.dentacoin.com/js/widget.js"></script> <script type="text/javascript"> TRPWidget.init("'+parsedUrl+'"); </script>');
    }

    $('.widget-modes input').change(refreshWidgetCode);
    refreshWidgetCode();


});

