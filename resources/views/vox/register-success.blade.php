@extends('vox')

@section('content')

	<div class="section-register-success">

		<div class="container">
			<div class="col-md-12 tac">
				<img src="{{ url('new-vox-img/register-success.png') }}">

				<h2>Welcome To DentaVox!</h2>

				<h4>
					@if($user->is_dentist)
						Now you can earn DCN by referring your patients to our market research <br/> platform and / or taking our surveys yourself! Stay up to date with the latest <br/> patients’ insights on various dental topics!
					@else
						Now you can start taking our paid surveys and get rewarded with DCN every <br/> time! Share your insights on various dental topics and help improve the global <br/> dental industry!
					@endif
				</h4>

				<div class="tac clearfix">
					@if($user->is_dentist)
						<a class="blue-button" href="{{ getLangUrl('profile/invite') }}">
							Invite patients
						</a>
					@endif
					<a class="blue-button" href="{{ getLangUrl('/') }}">
						Take surveys
					</a>
				</div>
			</div>
		</div>
	</div>

	<div class="section-manage container">

		<div class="row">
			<div class="col-md-6">
				<div class="row">
					<div class="col-md-3 col-md-offset-1 tac">
						@if($user->is_dentist)
							<img src="{{ url('new-vox-img/user-patient.png') }}">
						@else
							<img src="{{ url('new-vox-img/use-tool.png') }}">
						@endif
					</div>
					<div class="col-md-8">
						@if($user->is_dentist)
							<h4>Manage your Dentacoin profile</h4>
							<p>Strengthen your profile, keep track of your transactions, and review your activity on all apps from a single place.</p>
						@else
							<h4>Use other Dentacoin tools</h4>
							<p>Explore the Dentacoin platform and earn more DCN using other apps! Just go to your profile picture in the website header and take a virtual tour.</p>
						@endif
					</div>
				</div>

			</div>

			<div class="col-md-6">
				<div class="row">
					<div class="col-md-3 col-md-offset-1 tac">
						@if($user->is_dentist)
							<img src="{{ url('new-vox-img/order-custom-survey.png') }}">
						@else
							<img src="{{ url('new-vox-img/user-patient.png') }}">
						@endif
					</div>
					<div class="col-md-8">
						@if($user->is_dentist)
							<h4>Order Custom Surveys</h4>
							<p>You need to explore patients’ attitude or experience on a certain dental health topic? <a href="javascript:;">Request</a> a custom survey!</p>
						@else
							<h4>Manage your Dentacoin profile</h4>
							<p>You can easily control your personal data, withdraw the DCN you have collected and change the settings of all apps from a single place.</p>
						@endif
					</div>
				</div>
			</div>
		</div>

	</div>

	<div class="section-stats">
		<div class="container">
			<img src="{{ url('new-vox-img/stats-front.png') }}">
			<h3>Curious to see our survey stats?</h3>
			<a href="{{ getLangUrl('dental-survey-stats') }}" class="check-stats">Check stats</a>
		</div>
	</div>

@endsection