<div class="popup fixed-popup claim-popup" id="claim-popup">
	<div class="popup-inner inner-white">
		<a href="javascript:;" class="close-popup">
			<i class="fas fa-times"></i>
		</a>
		<div class="claim-details">
			<div class="header-claim tac">
				<img src="{{ url('img-trp/verification-check.png') }}">
				<h2>{!! nl2br(trans('trp.popup.popup-claim-profile.title')) !!}</h2>
				<h3>{!! nl2br(trans('trp.popup.popup-claim-profile.subtitle')) !!}</h3>
				<h4>{!! nl2br(trans('trp.popup.popup-claim-profile.trustest-dentists')) !!}</h4>
			</div>

			<form class="claim-profile-form" id="claim-profile-form" enctype="multipart/form-data" method="post" {!! $current_page == 'dentist' ? 'action="'.getLangUrl('welcome-dentist/claim/'.$item->id).'"' : '' !!}>
				{!! csrf_field() !!}

				<div class="modern-field alert-after">
					<input type="text" name="name" id="claim-name" class="modern-input" autocomplete="off">
					<label for="claim-name">
						<span>{!! nl2br(trans('trp.popup.popup-claim-profile.name')) !!}</span>
					</label>
				</div>

				@if(empty(request()->input('utm_content')))
					<div class="modern-field alert-after">
						<input type="email" name="email" id="claim-email" class="modern-input" autocomplete="off">
						<label for="claim-email">
							<span>{!! nl2br(trans('trp.popup.popup-claim-profile.email')) !!}</span>
						</label>
					</div>
				@endif

				<div class="modern-field alert-after">
					<input type="text" name="phone" id="claim-tel" class="modern-input" autocomplete="off">
					<label for="claim-tel">
						<span>{!! nl2br(trans('trp.popup.popup-claim-profile.phone')) !!}</span>
					</label>
				</div>

				<div class="modern-field alert-after">
					<input type="text" name="job" id="claim-job" class="modern-input" autocomplete="off" readonly onfocus="this.removeAttribute('readonly');">
					<label for="claim-job">
						<span>{!! nl2br(trans('trp.popup.popup-claim-profile.job')) !!}</span>
					</label>
				</div>

				<div class="modern-field alert-after">
					<textarea class="modern-input" id="claim-explain-related" name="explain-related"></textarea>
					<label for="claim-explain-related">
						<span>{!! nl2br(trans('trp.popup.popup-claim-profile.explain-related')) !!}</span>
					</label>
				</div>

				<div class="modern-field alert-after">
					<input type="password" name="password" id="claim-password" class="modern-input" autocomplete="off">
					<label for="claim-password">
						<span>{!! nl2br(trans('trp.popup.popup-claim-profile.password')) !!}</span>
					</label>
					<p>{!! nl2br(trans('trp.popup.popup-claim-profile.password.hint')) !!}</p>
				</div>
				
				<div class="modern-field alert-after">
					<input type="password" name="password-repeat" id="claim-password-repeat" class="modern-input" autocomplete="off">
					<label for="claim-password-repeat">
						<span>{!! nl2br(trans('trp.popup.popup-claim-profile.repeat-password')) !!}</span>
					</label>
				</div>

				<div class="tac">
					<input type="submit" value="{!! nl2br(trans('trp.popup.popup-claim-profile.submit')) !!}" class="button"/>
				</div>

				<div class="alert alert-success" style="display: none;"></div>
				<div class="alert alert-warning" style="display: none;">{!! nl2br(trans('trp.popup.popup-claim-profile.error')) !!}</div>
			</form>
		</div>
		<div class="claim-success">
			<div class="header-claim tac">
				<img src="{{ url('img-trp/verification-check.png') }}">
				<h2>{!! nl2br(trans('trp.popup.popup-claim-profile.thank-you.title')) !!}</h2>
				<h4>{!! nl2br(trans('trp.popup.popup-claim-profile.thank-you.subtitle')) !!}</h4>
				<a href="javascript:;" class="button claimed-ok">{!! nl2br(trans('trp.popup.popup-claim-profile.thank-you.ok')) !!}</a>
			</div>
		</div>
	</div>
</div>