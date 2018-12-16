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


    var stickyMenu = function() {
        if( $(window).width()>992 ) {
            $(".profile-menu").stick_in_parent({ offset_top: 95 });
        } else {
            $(".profile-menu").trigger("sticky_kit:detach");
        }
    }
    stickyMenu();
    $(window).resize(stickyMenu);

    $('.profile-menu-mobile').click( function() {
        $('.profile-menu .menu-list').toggleClass('active');
        $('.profile-menu-mobile').toggleClass('active');
        $('.mobile-shadow').toggleClass('active');
    } );

    //Invites

    $('#invite-wrapper .step .btn').click( function() {
        if( $('#wallet-needed').length ) {
            $('.option-div').hide();
            $(window).scrollTop(0);
            $("#wallet-needed").animate({opacity:0},200,"linear",function(){
                $(this).animate({opacity:1},200,"linear",function(){
                    $(this).animate({opacity:0},200,"linear",function(){
                        $(this).animate({opacity:1},200);
                        });
                    });
                });
            return;            
        }

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

    //
    //Withdraw
    //


    $('#wallet-amount').on('blur onchange', function() {
        $(this).removeClass("has-error");
    });

    $('#wallet-amount').on('blur onchange', function() {
        var val = parseInt( $(this).val() );
        if(isNaN(val) || val<3000) {
            $(this).addClass('has-error')
            $(this).closest('form').find('button').addClass('btn-inactive').removeClass('btn-primary');
        } else {
            $(this).closest('form').find('button').removeClass('btn-inactive').addClass('btn-primary');
        }
    } );

    $('#withdraw-form').submit( function(e) {
        e.preventDefault();
        if(ajax_is_running) {
            return;
        }
        ajax_is_running = true;
        $('#withdraw-form .alert').hide();
        
        var btn = $(this).find('button[type="submit"]').first();
        btn.attr('data-old', btn.html());
        btn.html('<i class="fa fa-spinner fa-pulse fa-fw"></i>');

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

});






/*
 Sticky-kit v1.1.2 | WTFPL | Leaf Corcoran 2015 | http://leafo.net
*/
(function(){var b,f;b=this.jQuery||window.jQuery;f=b(window);b.fn.stick_in_parent=function(d){var A,w,J,n,B,K,p,q,k,E,t;null==d&&(d={});t=d.sticky_class;B=d.inner_scrolling;E=d.recalc_every;k=d.parent;q=d.offset_top;p=d.spacer;w=d.bottoming;null==q&&(q=0);null==k&&(k=void 0);null==B&&(B=!0);null==t&&(t="is_stuck");A=b(document);null==w&&(w=!0);J=function(a,d,n,C,F,u,r,G){var v,H,m,D,I,c,g,x,y,z,h,l;if(!a.data("sticky_kit")){a.data("sticky_kit",!0);I=A.height();g=a.parent();null!=k&&(g=g.closest(k));
if(!g.length)throw"failed to find stick parent";v=m=!1;(h=null!=p?p&&a.closest(p):b("<div />"))&&h.css("position",a.css("position"));x=function(){var c,f,e;if(!G&&(I=A.height(),c=parseInt(g.css("border-top-width"),10),f=parseInt(g.css("padding-top"),10),d=parseInt(g.css("padding-bottom"),10),n=g.offset().top+c+f,C=g.height(),m&&(v=m=!1,null==p&&(a.insertAfter(h),h.detach()),a.css({position:"",top:"",width:"",bottom:""}).removeClass(t),e=!0),F=a.offset().top-(parseInt(a.css("margin-top"),10)||0)-q,
u=a.outerHeight(!0),r=a.css("float"),h&&h.css({width:a.outerWidth(!0),height:u,display:a.css("display"),"vertical-align":a.css("vertical-align"),"float":r}),e))return l()};x();if(u!==C)return D=void 0,c=q,z=E,l=function(){var b,l,e,k;if(!G&&(e=!1,null!=z&&(--z,0>=z&&(z=E,x(),e=!0)),e||A.height()===I||x(),e=f.scrollTop(),null!=D&&(l=e-D),D=e,m?(w&&(k=e+u+c>C+n,v&&!k&&(v=!1,a.css({position:"fixed",bottom:"",top:c}).trigger("sticky_kit:unbottom"))),e<F&&(m=!1,c=q,null==p&&("left"!==r&&"right"!==r||a.insertAfter(h),
h.detach()),b={position:"",width:"",top:""},a.css(b).removeClass(t).trigger("sticky_kit:unstick")),B&&(b=f.height(),u+q>b&&!v&&(c-=l,c=Math.max(b-u,c),c=Math.min(q,c),m&&a.css({top:c+"px"})))):e>F&&(m=!0,b={position:"fixed",top:c},b.width="border-box"===a.css("box-sizing")?a.outerWidth()+"px":a.width()+"px",a.css(b).addClass(t),null==p&&(a.after(h),"left"!==r&&"right"!==r||h.append(a)),a.trigger("sticky_kit:stick")),m&&w&&(null==k&&(k=e+u+c>C+n),!v&&k)))return v=!0,"static"===g.css("position")&&g.css({position:"relative"}),
a.css({position:"absolute",bottom:d,top:"auto"}).trigger("sticky_kit:bottom")},y=function(){x();return l()},H=function(){G=!0;f.off("touchmove",l);f.off("scroll",l);f.off("resize",y);b(document.body).off("sticky_kit:recalc",y);a.off("sticky_kit:detach",H);a.removeData("sticky_kit");a.css({position:"",bottom:"",top:"",width:""});g.position("position","");if(m)return null==p&&("left"!==r&&"right"!==r||a.insertAfter(h),h.remove()),a.removeClass(t)},f.on("touchmove",l),f.on("scroll",l),f.on("resize",
y),b(document.body).on("sticky_kit:recalc",y),a.on("sticky_kit:detach",H),setTimeout(l,0)}};n=0;for(K=this.length;n<K;n++)d=this[n],J(b(d));return this}}).call(this);