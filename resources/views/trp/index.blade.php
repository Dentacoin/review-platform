@extends('trp')

@section('content')

	<div class="black-overflow" style="display: none;"></div>

	<div class="home-section tac">
		<div class="container">
			<h1 class="mont">
				{!! nl2br(trans('trp.page.index.title')) !!}
			</h1>
			
			@include('trp.parts.search-form')
			
			@include('trp.parts.flickity-dentists', [
				'title' => trans('trp.page.index.flickity.title'),
				'subtitle' => trans('trp.page.index.flickity.subtitle')
			])
		</div>
	</div>
	
	@if(!empty($user))
		@if(config('trp.without_admins_check') && date('d.m.Y') > '28.06.2022')
		@else
			<div class="invite-new-dentist-background">
				<div class="invite-new-dentist-wrapper container">
					<img src="{{ url('img-trp/invite-new-dentist.png')}}"/>
					<form class="invite-new-dentist-form address-suggester-wrapper-input" action="{{ getLangUrl('invite-new-dentist') }}" method="post">
						{!! csrf_field() !!}

						<h2 class="mont">
							{!! trans('trp.page.invite.popup.title') !!}
						</h2>
						<h5>
							{!! trans('trp.page.invite.popup.subtitle', [
								'reward' => '<b>'.App\Models\Reward::getReward('patient_add_dentist').' DCN</b>'
							]) !!}
						</h5>

						<div class="mode-dentist-clinic alert-after flex flex-mobile">
							<label class="green-checkbox" for="mode-dentist1">
								<div>
									<img class="active-image" src="{{ url('img-trp/dentist-icon-active.svg') }}"/>
									<img class="inactive-image" src="{{ url('img-trp/dentist-icon.svg') }}"/>
								</div>
								{!! nl2br(trans('trp.page.invite.mode.dentist')) !!}
								<span>✓</span>
								<input class="checkbox" type="radio" name="mode" id="mode-dentist1" value="dentist"/>
							</label>

							<label class="green-checkbox" for="mode-clinic2">
								<div>
									<img class="active-image" src="{{ url('img-trp/clinic-icon-active.svg') }}"/>
									<img class="inactive-image" src="{{ url('img-trp/clinic-icon.svg') }}"/>
								</div>
								{!! nl2br(trans('trp.page.invite.mode.clinic')) !!}
								<span>✓</span>
								<input class="checkbox" type="radio" name="mode" id="mode-clinic2" value="clinic"/>
							</label>
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
							<input type="text" name="website" id="dentist-website1" class="modern-input" autocomplete="off">
							<label for="dentist-website1">
								<span>{!! trans('trp.page.invite.popup.website') !!}</span>
							</label>
						</div>

						<div class="modern-field alert-after">
							<input type="text" name="phone" id="dentist-tel1" class="modern-input" autocomplete="off">
							<label for="dentist-tel1">
								<span>{!! nl2br(trans('trp.page.invite.phone')) !!}</span>
							</label>
						</div>

						<div class="tac">
							<button type="submit" class="blue-button">
								<div class="loader"><i></i></div>
								{{ trans('trp.page.invite.submit') }}
							</button>
						</div>

						<div class="alert alert-success" style="display: none;"></div>
					</form>
					<div class="success-invited-dentist">
						<div class="tac">
							<img src="{{ url('img-trp/check.png') }}" class="check-image"/>
						</div>
						<h2 class="mont">
							{!! trans('trp.page.invite.popup.success.title') !!}
						</h2>
						<p class="step-info">
							{!! trans('trp.page.invite.popup.success.text', [
								'name' => '<span class="d-name"></span>',
								'reward' => '<span>'.App\Models\Reward::getReward('patient_add_dentist').' DCN</span>'
							]) !!}
						</p>
				
						<div class="tac">
							<a href="javascript:;" class="invite-new-dentist-again blue-button">{!! trans('trp.page.invite.popup.success.button') !!}</a>
						</div>
					</div>
				</div>
			</div>
		@endif

		{{-- <div class="strength-parent fixed">
			@include('trp.parts.strength-scale')
		</div> --}}
	@else
		<div class="info-section tac">
			<div class="container">
				<h2 class="mont">{!! nl2br(trans('trp.page.index.intro.title')) !!}</h2>
				<h3>{!! nl2br(trans('trp.page.index.intro.subtitle')) !!}</h3>

				<div class="flex flex-text-center">
					<div class="info-box">
						<div class="info-icon to-append-image" data-src="{{ url('img-trp/dentacoin-find-the-best-dentist-icon.png') }}" data-alt="{{ trans('trp.alt-tags.best-dentist') }}">
						</div>
						<div class="info-text">
							<h3>{!! nl2br(trans('trp.page.index.intro-title-1')) !!}</h3>
							<p>{!! nl2br(trans('trp.page.index.intro-description-1')) !!}</p>
							<a href="javascript:;" class="white-button scroll-to-search">{!! nl2br(trans('trp.page.index.intro-button-1')) !!}</a>
						</div>
					</div>
					<div class="info-box">
						<div class="info-icon to-append-image" data-src="{{ url('img-trp/dentacoin-make-your-voice-heard-icon.png') }}" data-alt="{{ trans('trp.alt-tags.make-voice-heard') }}">
						</div>
						<div class="info-text">
							<h3>{!! nl2br(trans('trp.page.index.intro-title-2')) !!}</h3>
							<p>{!! nl2br(trans('trp.page.index.intro-description-2')) !!}</p>
							<a href="javascript:;" class="white-button {{ !empty($user) ? '' : 'open-dentacoin-gateway patient-login' }}">{!! nl2br(trans('trp.page.index.intro-button-2')) !!}</a>
						</div>
					</div>
					<div class="info-box">
						<div class="info-icon to-append-image" data-src="{{ url('img-trp/dentacoin-get-rewarded-icon.png') }}" data-alt="{{ trans('trp.alt-tags.get-rewarded') }}">
						</div>
						<div class="info-text">
							<h3>{!! nl2br(trans('trp.page.index.intro-title-3')) !!}</h3>
							<p>{!! nl2br(trans('trp.page.index.intro-description-3')) !!}</p>
							<a href="javascript:;" class="white-button {{ !empty($user) ? '' : 'open-dentacoin-gateway patient-login' }}">{!! nl2br(trans('trp.page.index.intro-button-3')) !!}</a>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div id="to-append"></div>
	@endif

@endsection