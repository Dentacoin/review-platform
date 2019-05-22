<div class="popup fixed-popup verification-popup" id="verification-popup">
	<div class="popup-inner inner-white">
		<a href="javascript:;" class="close-popup">
			<i class="fas fa-times"></i>
		</a>
		<input type="hidden" name="last_user_id" value="">
		<input type="hidden" name="last_user_hash" value="">
		<div class="verification-content">
			<img src="{{ url('img-trp/verification-icon.png') }}">
			<h2>
				{!! nl2br(trans('trp.popup.verification-popup.title')) !!}
			</h2>
			<h4>
				{!! nl2br(trans('trp.popup.verification-popup.hint')) !!}
			</h4>
		</div>
		<div class="verification-info">

			<h2>{!! nl2br(trans('trp.popup.verification-popup.subtitle')) !!}</h2>

			{!! Form::open(array('method' => 'post', 'class' => 'invite-dentist-form', 'url' => getLangRoute('invite-dentist') )) !!}
				{!! csrf_field() !!}

				<div class="dentist-suggester-wrapper suggester-wrapper">
					<div class="modern-field">
						<input type="text" name="invitedentist" class="modern-input dentist-suggester suggester-input" value="" autocomplete="off">
						<label for="invitedentist">
							<span>{!! nl2br(trans('trp.popup.verification-popup.add-dentist')) !!}</span>
						</label>
						<p>{!! nl2br(trans('trp.popup.verification-popup.add-dentist.hint')) !!}</p>

						<div class="suggest-results">
						</div>
						<input type="hidden" class="suggester-hidden" name="dentist_id" value="" url="{{ getLangRoute('invite-dentist') }}">
						<i class="search-icon fas fa-search"></i>
					</div>
				</div>

			{!! Form::close() !!}

			@if(!session('join_clinic') && !session('invited_by'))
				{!! Form::open(array('method' => 'post', 'class' => 'invite-clinic-form', 'url' => getLangRoute('invite-clinic') )) !!}
					{!! csrf_field() !!}

					<div class="search-input" id="clinic-widget">
						<div class="input-wrapper cilnic-suggester-wrapper suggester-wrapper">
							<div class="modern-field">
								<input type="text" name="clinic_name" id="dentist-workplace" class="modern-input cilnic-suggester suggester-input" value="" autocomplete="off">
								<label for="clinic_name">
									<span>{!! nl2br(trans('trp.popup.verification-popup.workplace')) !!}</span>
								</label>
								<p>{!! nl2br(trans('trp.popup.verification-popup.workplace.hint')) !!}</p>

								<div class="suggest-results">
								</div>
								<input type="hidden" class="suggester-hidden" name="clinic_id" value="{{ session('join_clinic') && session('invited_by') ? session('invited_by') : '' }}"  value="" url="{{ getLangRoute('register-invite') }}">
								<i class="search-icon fas fa-search"></i>
							</div>

						</div>
					</div>

					<div class="invite-clinic-wrap" style="display: none;">
						<div class="invite-title tac">
							<span>{!! nl2br(trans('trp.popup.verification-popup.workplace.title')) !!}</span>
						</div>

						<div class="modern-field">
							<input type="text" name="clinic-name" id="clinic-name" class="modern-input" autocomplete="off">
							<label for="clinic-name">
								<span>{!! nl2br(trans('trp.popup.verification-popup.workplace.name')) !!}</span>
							</label>
						</div>

						<div class="modern-field">
							<input type="email" name="clinic-email" id="clinic-email" class="modern-input" autocomplete="off">
							<label for="clinic-email">
								<span>{!! nl2br(trans('trp.popup.verification-popup.workplace.email')) !!}</span>
							</label>
						</div>

						<div class="modern-field">
							<input type="text" name="clinic-address" id="clinic-address" class="modern-input" autocomplete="off">
							<label for="clinic-address">
								<span>{!! nl2br(trans('trp.popup.verification-popup.workplace.address')) !!}</span>
							</label>
						</div>

						<div class="modern-field">
							<input type="text" name="clinic-website" id="clinic-website" class="modern-input" autocomplete="off">
							<label for="clinic-website">
								<span>{!! nl2br(trans('trp.popup.verification-popup.workplace.website')) !!}</span>
							</label>
						</div>

						<div class="modern-field">
							<input type="text" name="clinic-phone" id="clinic-phone" class="modern-input" autocomplete="off">
							<label for="clinic-phone">
								<span>{!! nl2br(trans('trp.popup.verification-popup.workplace.phone')) !!}</span>
							</label>
						</div>

						<div class="invite-clinic-buttons">
							<div class="col">
								<a href="javascript:;" class="button button-white big-button cancel-invitation">{!! nl2br(trans('trp.popup.verification-popup.workplace.cancel')) !!}</a>
							</div>
							<div class="col">
								<input class="button big-button" type="submit" value="{!! nl2br(trans('trp.popup.verification-popup.workplace.invite')) !!}">
							</div>
						</div>
					</div>
				{!! Form::close() !!}
			@endif

			<div class="alert alert-success" style="display: none;"></div>

			<div class="alert alert-warning" style="display: none;"></div>

			{!! Form::open(array('method' => 'post', 'class' => 'verification-form', 'url' => getLangUrl('verification-dentist') )) !!}
				{!! csrf_field() !!}
				<div class="modern-field">
					<textarea class="modern-input" id="dentist-short-description" name="short_description" maxlength="150"></textarea>
					<label for="dentist-short-description">
						<span>{!! nl2br(trans('trp.popup.verification-popup.short_description')) !!}</span>
					</label>
					<p>{!! nl2br(trans('trp.popup.verification-popup.short_description.hint')) !!}</p>
				</div>

				<div class="tac">
					<input class="button big-button" type="submit" value="{!! nl2br(trans('trp.popup.verification-popup.save')) !!}">
				</div>

			{!! Form::close() !!}
		</div>
	</div>
</div>