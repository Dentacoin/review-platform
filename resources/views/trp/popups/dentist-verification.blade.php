<div class="popup verification-popup no-image active removable" id="verification-popup" scss-load="trp-popup-verification" js-load="dentist-verification">
	<div class="popup-inner">
		<a href="javascript:;" class="close-popup">
			<img src="{{ url('img/close-icon.png') }}"/>
		</a>
		
		<div class="verification-content">
			<h2 class="mont">
				{!! nl2br(trans('trp.popup.verification-popup.title')) !!}
			</h2>

			<div class="step" step="1">
				<p class="popup-desc">
					{!! nl2br(trans('trp.popup.verification-popup.step1')) !!}
				</p>

				<div class="open-hours-section edit-mode">
					{!! Form::open([
						'class' => 'edit-working-hours-form',
						'method' => 'post', 
						'url' => getLangUrl('verification-dentist-work-hours')
					]) !!}
						{!! csrf_field() !!}
						<input type="hidden" name="last_user_id" value=""/>
						<input type="hidden" name="last_user_hash" value=""/>
						@include('trp.parts.open-hours', [
							'withoutUser' => true,
							'loggedUserAllowEdit' => true,
							'dentistWorkHours' => false,
						])
						<input type="hidden" name="json" value="1" />
						<input type="hidden" name="field" value="work_hours"/>

						<div class="step-buttons tac">
							<a href="javascript:;" class="white-button skip" to-step="2">
								{{ trans('trp.common.skip') }}
							</a>
							<button type="submit" class="blue-button">
								{!! nl2br(trans('trp.popup.verification-popup.save-open-hours')) !!}
							</button>
						</div>
					{!! Form::close() !!}
				</div>
			</div>

			<div class="step" step="2" style="display: none;">
				<div id="clinic-add-team">
					<p class="popup-desc">
						{!! nl2br(trans('trp.popup.verification-popup.step2')) !!}
					</p>

					@include('trp.parts.add-team-member', [
						'withoutUser' => true,
					])

					<div class="step-buttons tac">
						<a href="javascript:;" class="white-button skip" to-step="3">
							{{ trans('trp.common.skip') }}
						</a>
					</div>
				</div>
			</div>

			<div class="step" step="3" style="display: none;">
				<p class="popup-desc">
					{!! nl2br(trans('trp.popup.verification-popup.step3')) !!}
				</p>

				{!! Form::open([
					'method' => 'post', 
					'class' => 'verification-form', 
					'url' => getLangUrl('verification-dentist') 
				]) !!}
					{!! csrf_field() !!}
					
					<input type="hidden" name="last_user_id" value=""/>
					<input type="hidden" name="last_user_hash" value=""/>

					<div class="modern-field">
						<textarea class="modern-input" id="dentist-description" name="description" maxlength="512"></textarea>
					</div>

					<div class="alert alert-warning descr-error" style="display: none; margin-top: 20px;">
						{{ trans('trp.popup.verification-popup.description-error') }}
					</div>
					<div class="alert alert-success descr-success" style="display: none; margin-top: 20px;"></div>
					<div class="tac step-buttons">
						<a href="javascript:;" class="white-button close-popup">
							{{ trans('trp.common.skip') }}
						</a>
						<button class="blue-button" type="submit">
							{!! nl2br(trans('trp.popup.verification-popup.save')) !!}
						</button>
					</div>

				{!! Form::close() !!}
			</div>
		</div>
	</div>
</div>