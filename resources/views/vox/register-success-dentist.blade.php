@extends('vox')

@section('content')

	<div class="section-register-success success-dentist">

		<div class="container">
			<div class="col-md-12 tac">
				<img src="{{ url('new-vox-img/welcome-dentist.png') }}">
				<div class="right-content">
					<div class="verification-content {!! !$user || ($user && $user->short_description) ? 'verticle-align' : '' !!}">
						<h1>
							{!! nl2br( trans('vox.page.welcome-to-dentavox.approval-request-title') ) !!}
						</h1>

						<h4>
							{!! nl2br( trans('vox.page.welcome-to-dentavox.approval-request-subtitle') ) !!}
						</h4>
					</div>

					@if($user && !$user->short_description)

						<div class="verification-info">
							<h2>{!! nl2br( trans('vox.page.welcome-to-dentavox.approval-request.user-info-title') ) !!}</h2>

							{!! Form::open(array('method' => 'post', 'class' => 'verification-form', 'url' => getLangRoute('verification-dentist') )) !!}
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
									<input class="blue-button" type="submit" value="{!! nl2br( trans('vox.page.welcome-to-dentavox.approval-request.user-info.save') ) !!}">
								</div>

							{!! Form::close() !!}

							<div class="alert alert-success" style="display: none;"></div>
							<div class="alert alert-warning" style="display: none;"></div>
						</div>
					@endif
				</div>
			</div>
		</div>
	</div>

@endsection