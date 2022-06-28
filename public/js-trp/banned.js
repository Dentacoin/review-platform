var ssoLoaded = 0;
var ssoTotal = 0;
var redirectToAccount;
var getUrlParameter;

$(document).ready(function(){

	redirectToAccount = function() {
		console.log('redirect!!!');
		window.location.href = 'https://account.dentacoin.com/trusted-reviews?platform=trusted-reviews';
	}

	//redirect after logout images are loaded
	ssoTotal = $('.sso img').length;
	
	$('.sso img').each( function() {
		if( $(this)[0].complete ) {
			ssoLoaded++;		
			if(ssoLoaded==ssoTotal) {
				redirectToAccount();
			}	
		}
	});

	$('.sso img').on('load error', function() {
		ssoLoaded++;		
		if(ssoLoaded==ssoTotal) {
			redirectToAccount();
		}
	});

	setTimeout( redirectToAccount, 15000 );
});