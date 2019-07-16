$(document).ready(function(){

    //Mobile menu
    /*
    var mobileMenuClick = function( e ) {
        console.log( $(window).width()<992, $(this).hasClass('active') );
        if( $(window).width()<992 && $(this).hasClass('active')  ) {
            e.preventDefault();
            $('.menu-list').toggleClass('open');        
        }
    }
    $('.menu-list a').click(mobileMenuClick);
    */
    if ($('.swiper-container').length) {

        if (window.innerWidth > 768) {

            var swiper = new Swiper('.swiper-container', {
                slidesPerView: 3,
                slidesPerGroup: 3,
                spaceBetween: 0,
            });
        } else {
            var swiper = new Swiper('.swiper-container', {
                slidesPerView: 1,
                spaceBetween: 0,
                effect: 'coverflow',
                grabCursor: true,
                centeredSlides: true,
                coverflowEffect: {
                    rotate: 50,
                    stretch: 0,
                    depth: 100,
                    modifier: 1,
                    slideShadows : false,
                },
            });
        }
    }

    if( $('.list-item.active').length && $(window).width()<992 ) {
        history.scrollRestoration = "manual";
        $('html, body').animate({
            scrollTop: $('.page-title').offset().top - 20
        }, 500);
    }

    //Invites

    $('#invite-wrapper .step .btn').click( function() {
        // if( $('#wallet-needed').length ) {
        //     $('.option-div').hide();
        //     $(window).scrollTop(0);
        //     $("#wallet-needed").animate({opacity:0},200,"linear",function(){
        //         $(this).animate({opacity:1},200,"linear",function(){
        //             $(this).animate({opacity:0},200,"linear",function(){
        //                 $(this).animate({opacity:1},200);
        //                 });
        //             });
        //         });
        //     return;            
        // }

        var id = $(this).attr('for');
        if( $(window).width()<992 ) {
            $(this).closest('.step').append( $('#'+id) )
            $('#'+id).show();
            $(this).remove();
        } else {
            $('.option-div').hide();
            $('#'+id).show();
            $('#invite-wrapper .step .btn').removeClass('btn-primary').addClass('btn-inactive');
            $(this).addClass('btn-primary').removeClass('btn-inactive');
        }
    } );


    $('.copy-invite-link').click( function() {
        // var $temp = $("<input>");
        // $("body").append($temp);
        $('.select-me').select();
        document.execCommand("copy");
        $('.select-me').blur();        
    } );



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
            yahoo: 'dj0yJmk9YzZhMlhjcm1WWWR0JmQ9WVdrOWVVdGhSM05OTkdzbWNHbzlNQS0tJnM9Y29uc3VtZXJzZWNyZXQmeD01ZA--'
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

    //Bans
    if( $('.popup.banned').length ) {
        hoursCountdown();
    }

    //Currency
    if( $('.balance').length ) {
        var convertDcn = function() {
            var currency = $('.balance .active-currency').text().trim();
            $('.balance .convertor-value').html( (parseInt( $('.balance .dcn-amount').text().replace(' ', '') ) * currency_rates[currency]).toFixed(2) );
        }

        $('.balance .expander a').click( function() {
            $('.balance .expander a').removeClass('active');
            $(this).addClass('active');
            $('.balance .active-currency').html( $(this).attr('currency') );
            convertDcn();
        } )

        convertDcn();
    }

    $('.balance-button').click( function() {
        $('.balance-button').removeClass('active');
        $(this).addClass('active');
        $(this).closest('.balance-wrap').find('.dcn-amount').html($(this).attr('amount'));
        convertDcn();
    });



    if ($('body').hasClass('sp-vox-iframe')) {
        var content_heigth = $('.popup').length ? ($('.popup').heigth() + $('.site-content').height()) : $('.site-content').height();
        console.log($('.popup').heigth(), $('.site-content').height(),  content_heigth);

        function triggerIframeSizeEventForParent() {
            window.parent.postMessage(
                {
                    event_id: 'iframe_size_event',
                    data: {
                        width: $('.site-content').width(),
                        height: content_heigth
                    }
                },
                "*"
            );
        }
        triggerIframeSizeEventForParent();
        $(window).resize(triggerIframeSizeEventForParent);

        $('a').attr('target', '_top');
    }

});