<div class="popup claim-popup no-image" id="claim-popup" scss-load="trp-popup-claim-dentist" js-load="claim-dentist">
	<div class="popup-inner">
		<a href="javascript:;" class="close-popup">
			<img src="{{ url('img/close-icon.png') }}"/>
		</a>
		<div class="claim-details">
			<div class="header-claim tac">
				<h2 class="mont">
					{{ trans('trp.popup.popup-claim-profile.title') }}
				</h2>
				<h5>{{ trans('trp.popup.popup-claim-profile.subtitle') }}</h5>
			</div>

			<form class="claim-profile-form" id="claim-profile-form" enctype="multipart/form-data" method="post" action="{{ getLangUrl('dentist/'.$item->slug.'/claim/'.$item->id) }}">
				{!! csrf_field() !!}

				@if( !empty($claim_user) && !empty(request()->input('utm_content')))
					<div class="modern-field alert-after">
						<input type="email" name="email" id="claim-email" value="{{ $claim_user->email }}" disabled="disabled" class="modern-input disabled" autocomplete="off">
						<label for="claim-email">
							<span>{!! nl2br(trans('trp.popup.popup-claim-profile.email')) !!}:</span>
						</label>
					</div>
				@endif

				<div class="modern-field alert-after">
					<input type="text" name="name" id="claim-name" class="modern-input" autocomplete="off">
					<label for="claim-name">
						<span>{!! nl2br(trans('trp.popup.popup-claim-profile.name')) !!}:</span>
					</label>
				</div>

				@if(empty(request()->input('utm_content')) && empty($claim_user))
					<div class="modern-field alert-after">
						<input type="email" name="email" id="claim-email" class="modern-input" autocomplete="off">
						<label for="claim-email">
							<span>{!! nl2br(trans('trp.popup.popup-claim-profile.email')) !!}:</span>
						</label>
					</div>
				@endif

				@if(empty(request()->input('old-dentist')) && empty($claim_user))
					<div class="modern-field alert-after">
						<input type="text" name="phone" id="claim-tel" class="modern-input" autocomplete="off">
						<label for="claim-tel">
							<span>{!! nl2br(trans('trp.popup.popup-claim-profile.phone')) !!}:</span>
						</label>
					</div>
				@endif

				@if(empty(request()->input('without-info')) && empty($claim_user))
					
					<div class="modern-field alert-after">
						<input type="text" name="job" id="claim-job" class="modern-input" autocomplete="off" readonly onfocus="this.removeAttribute('readonly');">
						<label for="claim-job">
							<span>{!! nl2br(trans('trp.popup.popup-claim-profile.job')) !!}:</span>
						</label>
					</div>

					@if(empty(request()->input('old-dentist')))
						<div class="modern-field alert-after">
							<textarea class="modern-input" id="claim-explain-related" name="explain-related"></textarea>
							<label for="claim-explain-related">
								<span>{!! nl2br(trans('trp.popup.popup-claim-profile.explain-related')) !!}</span>
							</label>
						</div>
					@endif
				@endif

				<div class="modern-field alert-after">
					<input type="password" name="password" id="claim-password" class="modern-input" autocomplete="off">
					<label for="claim-password">
						<span>{!! nl2br(trans('trp.popup.popup-claim-profile.password')) !!}</span>
					</label>
				</div>
				<div class="alert alert-warning" id="password-validator" style="display: none;">{{ trans('trp.popup.popup-claim-profile.password.error') }}</div>
				
				<div class="modern-field alert-after">
					<input type="password" name="password-repeat" id="claim-password-repeat" class="modern-input" autocomplete="off">
					<label for="claim-password-repeat">
						<span>{!! nl2br(trans('trp.popup.popup-claim-profile.repeat-password')) !!}</span>
					</label>
				</div>

				<label class="checkbox-label agree-label" for="claim-agree" style="text-align: left; margin-bottom: 30px;">
					<input type="checkbox" class="special-checkbox" id="claim-agree" name="agree" value="1">
					<div class="checkbox-square">✓</div>
					{!! nl2br(trans('trp.popup.popup-claim-profile.agree', [
						'link' => '<a class="read-privacy" href="https://dentacoin.com/privacy-policy/" target="_blank">',
						'endlink' => '</a>'
					])) !!}
				</label>

				<div class="tac">
					<input type="submit" value="{!! trans('trp.popup.popup-claim-profile.submit') !!}" class="blue-button"/>
				</div>

				<div class="alert alert-success" style="display: none;"></div>
				<div class="alert alert-warning" id="claim-err" style="display: none;">{!! nl2br(trans('trp.popup.popup-claim-profile.error')) !!}</div>
			</form>
		</div>
		<div class="claim-success">
			<div class="header-claim tac">
				<div class="tac">
					<img src="{{ url('img-trp/check.png') }}" class="check-image"/>
				</div>
				<h2 class="mont">
					{{ trans('trp.popup.popup-claim-profile.thank-you.title') }}
				</h2>
				<p class="step-info">
					{{ trans('trp.popup.popup-claim-profile.thank-you.subtitle') }}
				</p>
		
				<div class="tac">
					<a href="javascript:;" class="close-popup blue-button">{{ trans('trp.common.close') }}</a>
				</div>
			</div>
		</div>
	</div>
</div>