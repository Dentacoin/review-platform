@extends('vox')

@section('content')

	<div class="section-register-success">

		<div class="container">
			<div class="col-md-12 tac">
				<img src="{{ url('new-vox-img/register-success.png') }}">

				<h2>
					{!! nl2br( trans('vox.page.welcome-to-dentavox.title') ) !!}
				</h2>

				<h4>
					@if($user->is_dentist)
						{!! nl2br( trans('vox.page.welcome-to-dentavox.subtitle-dentist') ) !!}
					@else
						{!! nl2br( trans('vox.page.welcome-to-dentavox.subtitle-patient') ) !!}
					@endif
				</h4>

				<div class="tac clearfix">
					@if($user->is_dentist)
						<a class="blue-button" href="{{ getLangUrl('profile/invite') }}">
							{!! nl2br( trans('vox.page.welcome-to-dentavox.invite') ) !!}
							
						</a>
					@endif
					<a class="blue-button" href="{{ getLangUrl('/') }}">
						{!! nl2br( trans('vox.page.welcome-to-dentavox.take-surveys') ) !!}
						
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
							<h4>								
								{!! nl2br( trans('vox.page.welcome-to-dentavox.hints-dentist-title-1') ) !!}
							</h4>
							<p>								
								{!! nl2br( trans('vox.page.welcome-to-dentavox.hints-dentist-content-1') ) !!}
							</p>
						@else
							<h4>								
								{!! nl2br( trans('vox.page.welcome-to-dentavox.hints-patient-title-1') ) !!}
							</h4>
							<p>
								{!! nl2br( trans('vox.page.welcome-to-dentavox.hints-patient-content-1') ) !!}								
							</p>
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
							<h4>								
								{!! nl2br( trans('vox.page.welcome-to-dentavox.hints-dentist-title-2') ) !!}
							</h4>
							<p>
								{!! nl2br( trans('vox.page.welcome-to-dentavox.hints-dentist-content-2') ) !!}								
							</p>
						@else
							<h4>
								{!! nl2br( trans('vox.page.welcome-to-dentavox.hints-patient-title-2') ) !!}
							</h4>
							<p>
								{!! nl2br( trans('vox.page.welcome-to-dentavox.hints-patient-content-2') ) !!}
							</p>
						@endif
					</div>
				</div>
			</div>
		</div>

	</div>

	<div class="section-stats">
		<div class="container">
			<img src="{{ url('new-vox-img/stats-front.png') }}">
			<h3>
				{!! nl2br(trans('vox.page.welcome-to-dentavox.curious')) !!}
			</h3>
			<a href="{{ getLangUrl('dental-survey-stats') }}" class="check-stats">{{ trans('vox.common.check-statictics') }}</a>
		</div>
	</div>

@endsection