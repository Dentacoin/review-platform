var ssoLoaded = 0;
var ssoTotal = 0;
var redirectToAccount;

$(document).ready(function(){
	redirectToAccount = function() {
		console.log('redirect!!!');
		window.location.href = 'https://account.dentacoin.com/dentavox?platform=dentavox';
	}

	ssoTotal = $('.sso img').length;
	$('.sso img').each( function() {
		if( $(this)[0].complete ) {
			ssoLoaded++;		
			if(ssoLoaded==ssoTotal) {
				redirectToAccount();
			}	
		}
	} );
	$('.sso img').on('load error', function() {
			console.log( $(this).attr('src') );
		ssoLoaded++;		
		if(ssoLoaded==ssoTotal) {
			redirectToAccount();
		}
	});

	setTimeout( redirectToAccount, 15000 );

});