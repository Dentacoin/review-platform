<form class="invite-new-dentist-form address-suggester-wrapper" action="{{ getLangUrl('invite-new-dentist') }}" method="post">
	{!! csrf_field() !!}

	<div class="mobile-radios modern-radios alert-after flex">
		<div class="radio-label col">
		  	<label for="mode-dentist">
				<span class="modern-radio">
					<span></span>
				</span>
		    	<input class="type-radio" type="radio" name="mode" id="mode-dentist" value="dentist">
		    	{!! nl2br(trans('trp.page.invite.mode.dentist')) !!}
		  	</label>
		  	<span>{!! nl2br(trans('trp.page.invite.mode.dentist.hint')) !!}</span>
		</div>
		<div class="radio-label col">
		  	<label for="mode-clinic">
				<span class="modern-radio">
					<span></span>
				</span>
		    	<input class="type-radio" type="radio" name="mode" id="mode-clinic" value="clinic">
		    	{!! nl2br(trans('trp.page.invite.mode.clinic')) !!}								    	
		  	</label>
		  	<span>{!! nl2br(trans('trp.page.invite.mode.clinic.description')) !!}</span>
		</div>
	</div>

	<div class="modern-field alert-after">
		<input type="text" name="name" id="dentist-name" class="modern-input" autocomplete="off">
		<label for="dentist-name">
			<span>{!! nl2br(trans('trp.page.invite.name')) !!}</span>
		</label>
	</div>

	<div class="modern-field alert-after">
		<input type="email" name="email" id="dentist-email" class="modern-input" autocomplete="off">
		<label for="dentist-email">
			<span>{!! nl2br(trans('trp.page.invite.email')) !!}</span>
		</label>
	</div>

	<div class="modern-field" style="display: none;">
		<select name="country_id" id="dentist-country" class="modern-input country-select">
			@if(!$country_id)
				<option>-</option>
			@endif
			@foreach( $countries as $country )
				<option value="{{ $country->id }}" code="{{ $country->code }}" {!! $country_id==$country->id ? 'selected="selected"' : '' !!} >{{ $country->name }}</option>
			@endforeach
		</select>
	</div>

	<div class="modern-field alert-after">
		<input type="text" name="address" id="dentist-address" class="modern-input address-suggester" autocomplete="off" placeholder=" ">
		<label for="dentist-address">
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
    </div>

	<div class="modern-field alert-after">
		<input type="text" name="website" id="dentist-website" class="modern-input" autocomplete="off">
		<label for="dentist-website">
			<span>{!! nl2br(trans('trp.page.invite.website')) !!}</span>
		</label>
	</div>

	<div class="modern-field alert-after">
		<input type="text" name="phone" id="dentist-tel" class="modern-input" autocomplete="off">
		<label for="dentist-tel">
			<span>{!! nl2br(trans('trp.page.invite.phone')) !!}</span>
		</label>
	</div>

	<p class="invite-reward">{!! nl2br(trans('trp.page.invite.reward', ['amount' => App\Models\Reward::getReward('patient_add_dentist')])) !!}</p>

	<div class="tac">
		<input type="submit" value="{!! nl2br(trans('trp.page.invite.submit')) !!}" class="button next"/>
	</div>

	<div class="alert alert-success" style="display: none;"></div>
</form>