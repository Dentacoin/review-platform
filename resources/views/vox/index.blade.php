@extends('vox')

@section('content')

	<div class="full">
		<p class="first-absolute">
			<span>
				EARN DENTACOIN <br/> by taking surveys
			</span>
			<br/>
			<a class="black-button" href="{{ getLangUrl('welcome-survey') }}">Start now</a>
		</p>
		<a href="javascript:;" class="second-absolute">
			More
		</a>
	</div>
	<div class="container section-work">

		<h2>How it works</h2>		

		<div class="row">
			<div class="col-md-3 tac" style="{{ $user ? 'margin-left: 12%' : '' }}">
				<div class="image-wrapper warm-image">
					<img src="{{ url('new-vox-img/warm-up.png') }}">
				</div>
				<div>
					<h4>1. WARM UP</h4>
					<p>Answer a few “welcome” questions about your dental care habits to get your first 100 DCN!</p>
				</div>
			</div>
			@if(!$user)
				<div class="col-md-3 tac">
					<div class="image-wrapper sign-image">
						<img src="{{ url('new-vox-img/sign-up.png') }}">
					</div>
					<div>
						<h4>2. SIGN UP</h4>
						<p>Register quickly and easily with your Facebook profile. This is to make sure you are a real person.</p>
					</div>
				</div>
			@endif
			<div class="col-md-3 tac">
				<div class="image-wrapper grab-image">
					<img src="{{ url('new-vox-img/grab-reward.png') }}">
				</div>
				<div>
					<h4>{{ $user ? '2' : '3' }}. GRAB REWARD</h4>
					@if($user)
						<p>Go to your Dentacoin Wallet to review add withdraw your reward.</p>
					@else
						<p>Upon registration, 100 DCN will be assigned to your profle. You can withdraw them anytime!</p>
					@endif
				</div>
			</div>
			<div class="col-md-3 tac">
				<div class="image-wrapper no-image">
					<img src="{{ url('new-vox-img/take-surveys.png') }}">
				</div>
				<div>
					<h4>{{ $user ? '3' : '4' }}. TAKE SURVEYS</h4>
					<p>Each survey will give you coins. The more questions you answer, the bigger your reward. Ready?</p>
				</div>
			</div>
		</div>

		<div class="row tac">
			<div class="col-md-12">
				<a class="black-button" href="{{ getLangUrl('welcome-survey') }}">Start now</a>
			</div>
		</div>
	</div>

	<div class="section-stats">
		<div class="container">
			<img src="{{ url('new-vox-img/stats-front.png') }}">
			<h3>Curious to see our dental survey stats?</h3>
			<a href="{{ getLangUrl('dental-survey-stats') }}" class="check-stats">Check stats</a>
		</div>
	</div>

	<div class="container section-about">
		<h2 class="tac">About Dentavox</h2>
		<h4>PROVIDING VALUABLE PATIENT INSIGHTS TO help improve global dental health</h4>
		<p>
			DentaVox is a market research platform developed by the <a href="https://dentacoin.com/" target="_blank">Dentacoin Foundation.</a> The web app collects customer wisdom through surveys on various dental care topics. After each questionnaire you take, you are rewarded with a different amount of Dentacoin (DCN), the first cryptocurrency created for the dental industry.
		</p>
		<p>
			You can store the DCN collected in your wallet, exchange it to other altcoins and/or currencies or use them to pay for dental services in multiple <a href="https://dentacoin.com/partner-network" target="_blank">partner dental practices</a> across the world.
		</p>
	</div>
    	
@endsection