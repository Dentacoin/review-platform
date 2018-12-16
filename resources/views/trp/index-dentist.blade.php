@extends('trp')

@section('content')

	<div class="container">
		<div class="signin-top">
	    	<h2>Harness the Power of Patient Feedback!</h2>
	    	<p>Join Dentacoin Trusted Reviews - the first Blockchain-based platform for trusted, detailed and rewarded feedback on dental services. Learn and earn from up-to-date, qualified feedback and see your dental practice succeeding! Your willingness to improve is rewarded with Dentacoin cryptocurrency, accepted by dental practices, laboratories and suppliers worldwide.</p>

			<a href="javascript:;" class="button" data-popup="popup-register">
				Sign in
			</a>

	    </div>
    </div>

    <div class="signin-form-wrapper">
    	<img src="{{ url('img-trp/signin-laptop.png') }}">
    	<div class="container clearfix">
    		<form class="signin-form">

				<div class="form-inner">
					<input type="text" name="name" placeholder="Full name" class="input">
					<input type="email" name="email" placeholder="Email address" class="input">
					<input type="password" name="password" placeholder="Password" class="input">
					<div class="tac">
						<input type="submit" value="Sign in" class="button">
					</div>
				</div>

				<p class="have-account">
					Already have an account? <a href="javascript:;" data-popup="popup-login">Log in</a>
				</p>

    		</form>
    	</div>
    </div>

    <div class="container section-how">
    	<h2 class="tac">How it Works? <span class="h1">6</span> Simple Steps to Get Started</h2>

    	<div class="clearfix">
    		<div class="left">
    			<div class="how-block flex flex-center">
	    			<span class="h1">01</span>
	    			<p>Create profile as a dentist / clinic from <a href="javascript:;" data-popup="popup-register">here</a>.</p>
	    		</div>
    			<div class="how-block flex flex-center">
	    			<span class="h1">02</span>
	    			<p>Wait for a verification call or email from our side in order to get verified.</p>
	    		</div>
    			<div class="how-block flex flex-center">
	    			<span class="h1">03</span>
	    			<p>Invite your patients to send you feedback.</p>
	    		</div>
    		</div>
    		<div class="right">		    			
    			<div class="how-block flex flex-center">
	    			<span class="h1">04</span>
	    			<p>
	    				<a href="https://wallet.dentacoin.com/" target="_blank">Set up a wallet</a> and get rewarded with Dentacoin.
	    			</p>
	    		</div>
    			<div class="how-block flex flex-center">
	    			<span class="h1">05</span>
	    			<p>Implement our Trusetd Reviews widget on your website.</p>
	    		</div>
    			<div class="how-block flex flex-center">
	    			<span class="h1">06</span>
	    			<p>Invite your patients to send you feedback.</p>
	    		</div>
    		</div>
    	</div>
    </div>

    <div class="section-learn">
    	<div class="container">
    		<h2>Learn and Earn From Your Patients' Feedback!</h2>
    		<a href="javascript:;" class="button button-white" data-popup="popup-register">Sign up</a>
    	</div>
    </div>
@endsection