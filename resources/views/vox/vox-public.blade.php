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
					{{ trans('vox.page.questionnaire.not-logged-title') }}
					
				</h2>

				<div class="flex break-mobile">
					<div class="col">
						<img src="{{ url('new-vox-img/vox-not-logged-register.png') }}" />
						<div>
							<h3>
								{{ trans('vox.page.questionnaire.not-logged-register-title') }}
							</h3>
							<p>
								{{ trans('vox.page.questionnaire.not-logged-register-content') }}
							</p>
							<a class="btn" href="{{ getLangUrl('welcome-survey') }}">
								{{ trans('vox.page.questionnaire.not-logged-register-button') }}
							</a>
						</div>
					</div>
					<div class="col">
						<img src="{{ url('new-vox-img/vox-not-logged-login.png') }}" />
						<div>
							<h3>
								{{ trans('vox.page.questionnaire.not-logged-login-title') }}
							</h3>
							<p>
								{{ trans('vox.page.questionnaire.not-logged-login-content') }}
							</p>
							<a class="btn" href="{{ getLangUrl('login') }}">
								{{ trans('vox.page.questionnaire.not-logged-login-button') }}
							</a>
						</div>
					</div>
				</div>

			</div>
		</div>
	</div>

@endsection