@extends('vox')

@section('content')

	<div class="container">
		<link rel="stylesheet" href="https://hosted-sip.civic.com/css/civic-modal.min.css">

		<script src="https://hosted-sip.civic.com/js/civic.sip.min.js"></script>
		
		<button id="signupButton" class="civic-button-a medium" type="button">
			<span>Log in with Civic</span>
		</button>

		<script type="text/javascript">
			window.onload = function() {
				// Step 2: Instantiate instance of civic.sip
	  			var civicSip = new civic.sip({ appId: 'HkMGSKLyG' });

	  			 // Step 3: Start scope request.
				var button = document.querySelector('#signupButton');
				button.addEventListener('click', function () {
					civicSip.signup({ style: 'popup', scopeRequest: civicSip.ScopeRequests.BASIC_SIGNUP });
				});

				// Listen for data
				civicSip.on('auth-code-received', function (event) {
					console.log(event);
					var jwtToken = event.response;
					//sendAuthCode(jwtToken);
				});

				civicSip.on('user-cancelled', function (event) {
					console.log('user-cancelled');
				});

				civicSip.on('read', function (event) {
					console.log('read');
				});

				civicSip.on('civic-sip-error', function (error) {
					// handle error display if necessary.
					console.log('   Error type = ' + error.type);
					console.log('   Error message = ' + error.message);
				});
			}
		</script>
	</div>

@endsection