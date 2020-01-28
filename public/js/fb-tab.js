$(document).ready(function(){

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

    // set a variable with the signed_request sent to your app URL by Facebook via POST

    {{ dd(request('signed_request')) }}
    var signedRequest = "{{ request('signed_request') }}";

    FB.getLoginStatus(function (response) {
        // do not use the response.authResponse.signedRequest but the one above instead
        // and let the javascript SDK parse the good signed_request for you
        var page = this.parseSignedRequest(signedRequest).page;
        // is the current user an admin of this page? true/false
        var isAdmin = page.admin;
        // do you like this page? true/false
        var isLiked = page.liked;
        // and here is the Facebook page ID
        var pageID = page.id;
        if (response.status === 'connected') {


		    $.ajax( {
		        url: window.location.origin+'/en/facebook-tab/',
		        type: 'POST',
		        dataType: 'json',
		        data: {
		        	_token: $('input[name="_token"]').val(),
		            pageid: pageID
		        },
		        success: function(data) {
		        	if (data.success) {
			            for (var i in data.reviews) {
			            	console.log(data);
			            	$('.list-reviews').append('<div class="list-review">\
								<div class="list-review-left">\
									<a href="'+data.dentist_link+'?review_id='+data.reviews[i]['id']+'" target="_blank" class="review-avatar" style="background-image: url('+data.reviews[i]['patient_avatar']+');"></a>\
									<span class="review-date">'+data.reviews[i]['date_converted']+'</span>\
								</div>\
								<div class="list-review-right">'+data.reviews[i]['converted_title']+'\
					    			<div class="ratings">\
										<div class="stars">\
											<div class="bar" style="width: '+data.reviews[i]['rating_converted']+'%;">\
											</div>\
										</div>\
									</div>\
									<div class="review-content">'+data.reviews[i]['converted_answer']+'</div>\
									<span class="review-name">'+data.reviews[i]['patient_name']+'</span>\
									<span class="mobile-review-date">'+data.reviews[i]['date_converted']+'</span>\
								</div>\
							</div>');
			            }

		        	} else {
		        		console.log('eeee');
		        	}

		        },
		        error: function(data) {
		            console.log('error');
		        }
		    });
		} else if (response.status === 'not_authorized') {
            // the user is logged in to Facebook,
            // but has not authenticated your app

        } else {
            // the user isn't logged in to Facebook.

        }

    }, true);
});