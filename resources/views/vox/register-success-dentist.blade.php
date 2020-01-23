@extends('vox')

@section('content')

	<div class="section-register-success success-dentist">

		<div class="container">
			<div class="col-md-12 tac">
				<img src="{{ url('new-vox-img/welcome-dentist.png') }}" alt="Dentavox man welcome dentist">
				<div class="right-content {!! $user->short_description ? 'active' : '' !!}">
					<div class="verification-content">
						<h1>
							@if($user->is_clinic)
								{!! nl2br( trans('vox.page.welcome-to-dentavox.approval-request-title.clinic') ) !!}
							@else
								{!! nl2br( trans('vox.page.welcome-to-dentavox.approval-request-title.dentist') ) !!}
							@endif
						</h1>

						<h4>
							{!! nl2br( trans('vox.page.welcome-to-dentavox.approval-request-subtitle') ) !!}
						</h4>
					</div>

					<div class="verification-info">

						<div class="without-short-desc">
							<h2>
								{!! nl2br( trans('vox.page.welcome-to-dentavox.approval-request.user-info-title') ) !!}
							</h2>

							{!! Form::open(array('method' => 'post', 'class' => 'verification-form', 'url' => getLangUrl('welcome-to-dentavox') )) !!}
								{!! csrf_field() !!}
								<h4>
									{!! nl2br( trans('vox.page.welcome-to-dentavox.approval-request.user-info-hint') ) !!}
								</h4>
								<div class="modern-field">
									<textarea class="modern-input" id="dentist-short-description" name="short_description" maxlength="150"></textarea>
									<label for="dentist-short-description">
										<span>{!! nl2br( trans('vox.page.welcome-to-dentavox.approval-request.user-info.short_description') ) !!}</span>
									</label>
									<p>{!! nl2br( trans('vox.page.welcome-to-dentavox.approval-request.user-info.short_description.hint') ) !!}</p>
								</div>

								<div class="tac">
									<input class="blue-button big-blue-button" type="submit" value="{!! nl2br( trans('vox.page.welcome-to-dentavox.approval-request.user-info.save') ) !!}">
								</div>

							{!! Form::close() !!}

							<div class="alert alert-warning" style="display: none;"></div>
						</div>
						<div class="with-short-desc">

							<div class="enhancing-info">
								<h2>Thank you for enhancing your profile information!</h2>

								<p>
									You will be able to review your accounts on DentaVox and Trusted Reviews as soon as your profile has been verified.<br><br>
									Curious to see our dental survey stats? Dive into a pool of topics and keep track of the latest patient experience trends!
								</p>

								<a href="{{ getLangUrl('dental-survey-stats') }}" class="blue-button">CHECK DENTAL SURVEY STATS</a>
							</div>
						</div>

					</div>
				</div>
			</div>
		</div>
	</div>


    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>

@endsection