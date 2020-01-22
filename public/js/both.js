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
            document.cookie = name + "=" + value + "; " + expires + ";path=/";
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

			if($('body').hasClass('page-welcome-dentist')) {
				$('body').append("<script>\
                    (function(w,d,t,u,n,a,m){\
                        if(typeof w['AriticTrackingObject'] !== 'undefined') return;w['AriticTrackingObject']=n;\
                        w[n]=w[n]||function(){(w[n].q=w[n].q||[]).push(arguments)},a=d.createElement(t),\
                        m=d.getElementsByTagName(t)[0];a.async=1;a.src=u;m.parentNode.insertBefore(a,m)\
                    })(window,document,'script','https://dentacoin.ariticapp.com/ma/atc.js','at');\
                </script>");

                $.getScript('https://dentacoin.ariticapp.com/ma/patc.js', function() {
                    console.log('patc')
                } );

                $('body').append('<script type="text/javascript">\
                    function LeadMagenet() {\
                        setTimeout( function() {\
                            _aaq.push(["setContactFields", {\
                                firstname:document.getElementById("magnet-name").value,\
                                website:document.getElementById("magnet-website").value,\
                                email:document.getElementById("magnet-email").value,\
                            }]);\
                            _aaq.push(["trackPageView"]);\
                        }, 5000);\
                    }\
                </script>');

                $.getScript('//dentacoin.ariticapp.com/ma/form/generate.js?id=13', function() {
                    console.log('gdpr')
                } );
			}
		});

		$('.adjust-cookies').click(function() {
			$('.privacy-policy-cookie').removeClass('blink');
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

					if($('body').hasClass('page-welcome-dentist')) {
						$('body').append("<script>\
                            (function(w,d,t,u,n,a,m){\
                                if(typeof w['AriticTrackingObject'] !== 'undefined') return;w['AriticTrackingObject']=n;\
                                w[n]=w[n]||function(){(w[n].q=w[n].q||[]).push(arguments)},a=d.createElement(t),\
                                m=d.getElementsByTagName(t)[0];a.async=1;a.src=u;m.parentNode.insertBefore(a,m)\
                            })(window,document,'script','https://dentacoin.ariticapp.com/ma/atc.js','at');\
                        </script>");

                        $.getScript('https://dentacoin.ariticapp.com/ma/patc.js', function() {
                            console.log('patc')
                        } );

                        $('body').append('<script type="text/javascript">\
                            function LeadMagenet() {\
                                setTimeout( function() {\
                                    _aaq.push(["setContactFields", {\
                                        firstname:document.getElementById("magnet-name").value,\
                                        website:document.getElementById("magnet-website").value,\
                                        email:document.getElementById("magnet-email").value,\
                                    }]);\
                                    _aaq.push(["trackPageView"]);\
                                }, 5000);\
                            }\
                        </script>');

                        $.getScript('//dentacoin.ariticapp.com/ma/form/generate.js?id=13', function() {
                            console.log('gdpr')
                        } );
					}
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
	        $('.privacy-policy-cookie').addClass('blink');
	        $('.bottom-drawer').css('z-index', '1010');
	        e.preventDefault();
	        e.stopPropagation();
	    }
	});

});