<div class="popup no-image" id="recommend-dentist" scss-load="trp-popup-recommend-dentist" js-load="recommend-dentist">
	<div class="popup-inner inner-white">
		<a href="javascript:;" class="close-popup">
			<img src="{{ url('img/close-icon.png') }}"/>
		</a>

		<h2 class="mont">
			{!! nl2br(trans('trp.popup.popup-recommend-dentist.title')) !!}
		</h2>

		<h5>
			{!! nl2br(trans('trp.popup.popup-recommend-dentist.info')) !!}
		</h5>

		{!! Form::open([
			'method' => 'post', 
			'class' => 'recommend-dentist-form', 
			'url' => getLangUrl('recommend-dentist') 
		]) !!}
			{!! csrf_field() !!}

			<div class="flex">
				<div class="col rec-first">
					<div class="modern-field">
						<input type="text" name="name" id="recommend-name" class="modern-input recommend-name" autocomplete="off">
						<label for="recommend-name">
							<span>{!! trans('trp.popup.popup-recommend-dentist.name') !!}:</span>
						</label>
					</div>
				</div>
				<div class="col">
					<div class="modern-field">
						<input type="email" name="email" id="recommend-email" class="modern-input recommend-email" autocomplete="off">
						<label for="recommend-email">
							<span>{!! trans('trp.popup.popup-recommend-dentist.email') !!}:</span>
						</label>
					</div>
				</div>
			</div>
			<input type="hidden" name="dentist-id" value="{{ $item->id }}" class="recommend-dentist-id">

			<div class="alert recommend-alert" style="display: none; margin-bottom: 20px;">
			</div>

			<div class="tac">
				<input type="submit" class="blue-button" value="{!! trans('trp.popup.popup-recommend-dentist.recommend') !!}">
			</div>
		{!! Form::close() !!}	
	</div>
</div>