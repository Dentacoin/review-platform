<div class="popup fixed-popup" id="recommend-dentist">
	<div class="popup-inner inner-white">
		<div class="popup-pc-buttons">
			<a href="javascript:;" class="close-popup"><i class="fas fa-times"></i></a>
		</div>

		<div class="popup-mobile-buttons">
			<a href="javascript:;" class="close-popup">< {!! nl2br(trans('trp.common.back')) !!}</a>
		</div>
		<h2>
			Recommend to a Friend
		</h2>

		<h4 class="popup-title">
			VIA EMAIL
		</h4>

		<p class="popup-desc">
			• Just enter a name and email address and we will send an email with link to the profile of your recommended dentist.
		</p>
		<br/>
		<br/>

		{!! Form::open(array('method' => 'post', 'class' => 'recommend-dentist-form', 'url' => getLangUrl('recommend-dentist') )) !!}
			{!! csrf_field() !!}

			<div class="flex">
				<div class="col rec-first">
					<div class="modern-field">
						<input type="text" name="name" id="recommend-name" class="modern-input recommend-name" autocomplete="off">
						<label for="recommend-name">
							<span>{!! nl2br(trans('trp.popup.popup-invite.name')) !!}:</span>
						</label>
					</div>
				</div>
				<div class="col">
					<div class="modern-field">
						<input type="email" name="email" id="recommend-email" class="modern-input recommend-email" autocomplete="off">
						<label for="recommend-email">
							<span>{!! nl2br(trans('trp.popup.popup-invite.email')) !!}:</span>
						</label>
					</div>
				</div>
			</div>
			<input type="hidden" name="dentist-id" value="{{ $item->id }}" class="recommend-dentist-id">

			<div class="alert recommend-alert" style="display: none; margin-top: 20px;">
			</div>

			<div class="tac">
				<input type="submit" class="button" value="Recommend">
			</div>
		{!! Form::close() !!}	
	</div>
</div>