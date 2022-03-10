<div class="popup fixed-popup invite-new-dentist-popup active removable" id="invite-new-dentist-popup" scss-load="trp-popup-invite-new-dentist">
	<img class="popup-image" src="{{ url('img-trp/invite-popup-image.png') }}"/>
	<div class="popup-inner inner-white">
		<a href="javascript:;" class="close-popup">
			<img src="{{ url('img/close-icon.png') }}"/>
		</a>
		<h2 class="mont">
			Invite Your Dentist to Trusted Reviews
			{{-- {!! trans('trp.page.invite.popup.title') !!} --}}
		</h2>
		<h5>
			All real entries will be rewarded with <b>{{ App\Models\Reward::getReward('patient_add_dentist') }} DCN</b>.
		</h5>
		<form class="invite-new-dentist-form address-suggester-wrapper-input" action="{{ getLangUrl('invite-new-dentist') }}" method="post">
			{!! csrf_field() !!}

			<div class="mobile-radios modern-radios alert-after flex">
				<div class="radio-label col">
				  	<label for="mode-dentist1">
						<span class="modern-radio">
							<span></span>
						</span>
				    	<input class="type-radio" type="radio" name="mode" id="mode-dentist1" value="dentist">
				    	{!! nl2br(trans('trp.page.invite.mode.dentist')) !!}
				  	</label>
				</div>
				<div class="radio-label col">
				  	<label for="mode-clinic2">
						<span class="modern-radio">
							<span></span>
						</span>
				    	<input class="type-radio" type="radio" name="mode" id="mode-clinic2" value="clinic">
				    	{!! nl2br(trans('trp.page.invite.mode.clinic')) !!}
				  	</label>
				</div>
			</div>

			<div class="modern-field alert-after">
				<input type="text" name="name" id="dentist-name1" class="modern-input" autocomplete="off">
				<label for="dentist-name1">
					<span>{!! nl2br(trans('trp.page.invite.name')) !!}</span>
				</label>
			</div>

			<div class="modern-field alert-after">
				<input type="email" name="email" id="dentist-email1" class="modern-input" autocomplete="off">
				<label for="dentist-email1">
					<span>{!! nl2br(trans('trp.page.invite.email')) !!}</span>
				</label>
			</div>

			<div class="modern-field" style="display: none;">
				<select name="country_id" id="dentist-country1" class="modern-input country-select">
					@if(!$country_id)
						<option>-</option>
					@endif
					@if(!empty($countries))
						@foreach( $countries as $country )
							<option value="{{ $country->id }}" code="{{ $country->code }}" {!! $country_id==$country->id ? 'selected="selected"' : '' !!} >{{ $country->name }}</option>
						@endforeach
					@endif
				</select>
			</div>

			<div class="modern-field alert-after">
				<input type="text" name="address" id="dentist-address1" class="modern-input address-suggester-input" autocomplete="off" placeholder=" ">
				<label for="dentist-address1">
					<span>{!! nl2br(trans('trp.page.invite.address')) !!}</span>
				</label>
			</div>

			<div>
		    	<div class="suggester-map-div" style="height: 200px; display: none; margin: 10px 0px; background: transparent;">
		        </div>
		        <div class="alert alert-info geoip-confirmation mobile" style="display: none; margin: 10px 0px 20px;">
		        	{!! nl2br(trans('trp.common.check-address')) !!}
		        </div>
		        <div class="alert alert-warning geoip-hint mobile" style="display: none; margin: -10px 0px 10px;">
		        	{!! nl2br(trans('trp.common.invalid-address')) !!}
		        </div>
		        <div class="alert alert-warning different-country-hint mobile" style="display: none; margin: -10px 0px 10px;">
		        	{!! nl2br(trans('trp.common.invalid-country')) !!}
		        </div>
		    </div>

			<div class="modern-field alert-after">
				<input type="text" name="fb" id="dentist-fb" class="modern-input" autocomplete="off">
				<label for="dentist-fb">
					<span>Facebook page:</span>
				</label>
			</div>

			<div class="modern-field alert-after">
				<input type="text" name="website" id="dentist-website1" class="modern-input" autocomplete="off">
				<label for="dentist-website1">
					<span>{!! nl2br(trans('trp.page.invite.website')) !!}</span>
				</label>
			</div>

			<div class="modern-field alert-after">
				<input type="text" name="phone" id="dentist-tel1" class="modern-input" autocomplete="off">
				<label for="dentist-tel1">
					<span>{!! nl2br(trans('trp.page.invite.phone')) !!}</span>
				</label>
			</div>

			<div class="tac">
				<input type="submit" value="Send Invite" class="blue-button next"/>
				{{-- <input type="submit" value="{!! nl2br(trans('trp.page.invite.submit')) !!}" class="button next"/> --}}
			</div>

			<div class="alert alert-success" style="display: none;"></div>
		</form>
	</div>
</div>