@extends('vox')

@section('content')

	<div class="page-questions">
		<div class="container" id="question-meta">
			<div class="questions">

				<div class="col-md-8 col-md-offset-2 clearfix">
					<h1 class="questionnaire-title tac">
						- {{ $vox->title }} -
					</h1>
					<p class="questionnaire-description tac" >
						{{ $vox->description }}
					</p>

				</div>
			</div>

			<div class="vox-not-logged">
				<h2>
					Take Surveys & Get Rewards 
				</h2>

				<div class="flex break-mobile">
					<div class="col">
						<img src="{{ url('new-vox-img/vox-not-logged-register.png') }}" />
						<div>
							<h3>
								Don't have an account yet?
							</h3>
							<p>
								To start taking surveys on DentaVox, you need to answer a quick Welcome Questionnaire and log in using your Facebook or Civic account. 
							</p>
							<a class="btn" href="{{ getLangUrl('welcome-survey') }}">
								GET STARTED
							</a>
						</div>
					</div>
					<div class="col">
						<img src="{{ url('new-vox-img/vox-not-logged-login.png') }}" />
						<div>
							<h3>
								Already have an account?
							</h3>
							<p>
								Glad to see you on DentaVox again! Looks like you have logged out from your Profile. Click on the button below to log in and start your next round!
							</p>
							<a class="btn" href="{{ getLangUrl('login') }}">
								GO TO LOGIN
							</a>
						</div>
					</div>
				</div>

			</div>
		</div>
	</div>

@endsection