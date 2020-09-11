var basic = {
    cookies: {
        set: function(name, value) {
            if(name == undefined){
                name = "cookieLaw";
            }
            if(value == undefined){
                value = 1;
            }
            var d = new Date();
            d.setTime(d.getTime() + (100*24*60*60*1000));
            var expires = "expires="+d.toUTCString();
            document.cookie = name + "=" + value + "; " + expires + ";domain=.dentacoin.com;path=/;secure";
            if(name == "cookieLaw"){
                $(".cookies_popup").slideUp();
            }
        },
        get: function(name) {

            if(name == undefined){
                var name = "cookieLaw";
            }
            name = name + "=";
            var ca = document.cookie.split(';');
            for(var i=0; i<ca.length; i++) {
                var c = ca[i];
                while (c.charAt(0)==' ') c = c.substring(1);
                if (c.indexOf(name) == 0) return c.substring(name.length, c.length);
            }

            return "";
        }
    }
};


jQuery(document).ready(function($){

	if ($('.privacy-policy-cookie').length > 0)  {

		$('.privacy-policy-cookie .accept-all').click(function()    {
			basic.cookies.set('performance_cookies', 1);
			basic.cookies.set('functionality_cookies', 1);
			basic.cookies.set('marketing_cookies', 1);
			basic.cookies.set('strictly_necessary_policy', 1);

			$('.privacy-policy-cookie').hide();
			$('.agree-cookies').hide();

            if($('#pixel-code').length) {
                $('#pixel-code').remove();
                $('head').append("<script id='pixel-code'>\
                    !function(f,b,e,v,n,t,s)\
                    {if(f.fbq)return;n=f.fbq=function(){n.callMethod?\
                    n.callMethod.apply(n,arguments):n.queue.push(arguments)};\
                    if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';\
                    n.queue=[];t=b.createElement(e);t.async=!0;\
                    t.src=v;s=b.getElementsByTagName(e)[0];\
                    s.parentNode.insertBefore(t,s)}(window,document,'script',\
                    'https://connect.facebook.net/en_US/fbevents.js');\
                    fbq('consent', 'grant');\
                    fbq('init', '2010503399201502'); \
                    fbq('init', '2366034370318681');\
                    fbq('track', 'PageView');\
                </script>");
            }

            $('body').append("<div id='fb-root'></div>\
            <script>\
                window.fbAsyncInit = function() {\
                    FB.init({\
                        appId: '1906201509652855',\
                        xfbml: true,\
                        version: 'v7.0',\
                    });\
                };\
                (function(d, s, id) {\
                    var js, fjs = d.getElementsByTagName(s)[0];\
                    if (d.getElementById(id)) return;\
                    js = d.createElement(s); js.id = id;\
                    js.src = 'https://connect.facebook.net/en_US/sdk/xfbml.customerchat.js';\
                    fjs.parentNode.insertBefore(js, fjs);\
                }(document, 'script', 'facebook-jssdk'));\
            </script>");

            setTimeout( function() {
                FB.CustomerChat.showDialog();
            }, 60000);  
		});

		$('.adjust-cookies').click(function() {
			$('#customize-cookies').show();

			$('.close-customize-cookies-popup').click(function() {
				$('.customize-cookies').hide();
			});

			$('.custom-cookie-save').click(function() {
				basic.cookies.set('strictly_necessary_policy', 1);

				if($('#functionality-cookies').is(':checked')) {
					basic.cookies.set('functionality_cookies', 1);
				}

				if($('#marketing-cookies').is(':checked')) {
					basic.cookies.set('marketing_cookies', 1);

                    if($('#pixel-code').length) {
                        $('#pixel-code').remove();
                        $('head').append("<script id='pixel-code'>\
                            !function(f,b,e,v,n,t,s)\
                            {if(f.fbq)return;n=f.fbq=function(){n.callMethod?\
                            n.callMethod.apply(n,arguments):n.queue.push(arguments)};\
                            if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';\
                            n.queue=[];t=b.createElement(e);t.async=!0;\
                            t.src=v;s=b.getElementsByTagName(e)[0];\
                            s.parentNode.insertBefore(t,s)}(window,document,'script',\
                            'https://connect.facebook.net/en_US/fbevents.js');\
                            fbq('consent', 'grant');\
                            fbq('init', '2010503399201502'); \
                            fbq('init', '2366034370318681');\
                            fbq('track', 'PageView');\
                        </script>");
                    }

                    $('body').append("<div id='fb-root'></div>\
                    <script>\
                        window.fbAsyncInit = function() {\
                            FB.init({\
                                appId: '1906201509652855',\
                                xfbml: true,\
                                version: 'v7.0',\
                            });\
                        };\
                        (function(d, s, id) {\
                            var js, fjs = d.getElementsByTagName(s)[0];\
                            if (d.getElementById(id)) return;\
                            js = d.createElement(s); js.id = id;\
                            js.src = 'https://connect.facebook.net/en_US/sdk/xfbml.customerchat.js';\
                            fjs.parentNode.insertBefore(js, fjs);\
                        }(document, 'script', 'facebook-jssdk'));\
                    </script>");

                    setTimeout( function() {
                        FB.CustomerChat.showDialog();
                    }, 60000);
				}

				if($('#performance-cookies').is(':checked')) {
					basic.cookies.set('performance_cookies', 1);
				}

				$('.privacy-policy-cookie').hide();
				$('.agree-cookies').hide();
			});
		});
	}

	$('.cookie-checkbox').change( function() {
		$(this).closest('label').toggleClass('active');
	});


	$('.has-cookies-button').click( function(e) {
	    if (!Cookies.get('strictly_necessary_policy')) {
	        $('.agree-cookies').show();
	        $('.bottom-drawer').css('z-index', '1010');
	        e.preventDefault();
	        e.stopPropagation();
	    }
	});

});