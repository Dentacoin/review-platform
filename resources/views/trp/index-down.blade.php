@if(empty($user))

	<div class="info-section">
		<div class="container">
			<h2 class="gbb">{!! nl2br(trans('trp.page.index.hint')) !!}</h2>

			<div class="info-box flex flex-mobile">
				<div class="info-icon">
					<img src="{{ url('img-trp/dentacoin-find-the-best-dentist-icon.png') }}" alt="{{ trans('trp.alt-tags.best-dentist') }}">
				</div>
				<div class="info-text">
					<h3>{!! nl2br(trans('trp.page.index.intro-title-1')) !!}</h3>
					<p>{!! nl2br(trans('trp.page.index.intro-description-1')) !!}</p>
				</div>
			</div>
			<div class="info-box flex flex-mobile">
				<div class="info-icon">
					<img src="{{ url('img-trp/dentacoin-make-your-voice-heard-icon.png') }}" alt="{{ trans('trp.alt-tags.make-voice-heard') }}">
				</div>
				<div class="info-text">
					<h3>{!! nl2br(trans('trp.page.index.intro-title-2')) !!}</h3>
					<p>{!! nl2br(trans('trp.page.index.intro-description-2')) !!}</p>
				</div>
			</div>
			<div class="info-box flex flex-mobile">
				<div class="info-icon">
					<img src="{{ url('img-trp/dentacoin-get-rewarded-icon.png') }}" alt="{{ trans('trp.alt-tags.get-rewarded') }}">
				</div>
				<div class="info-text">
					<h3>{!! nl2br(trans('trp.page.index.intro-title-3')) !!}</h3>
					<p>{!! nl2br(trans('trp.page.index.intro-description-3')) !!}</p>
				</div>
			</div>
			<div class="tac">
				<a href="javascript:;" class="button button-sign-up-patient open-dentacoin-gateway patient-register">{!! nl2br(trans('trp.page.index.intro-button')) !!}</a>
			</div>
		</div>
	</div>

	<div class="add-practice">
		<div class="container">
			<div class="flex flex-mobile">
				<div class="col">
					<div class="practice-image">
						<img class="pc-practice-img" src="{{ url('img-trp/dentacoin-trusted-reviews-add-practice-icon.png') }}" alt="{{ trans('trp.alt-tags.add-practice') }}">
					</div>
				</div>
				<div class="col">
					<h2>{!! nl2br(trans('trp.page.index.first-rated-dentist.title')) !!}</h2>
					<div class="mobile-practice-img">
						<img src="{{ url('img-trp/dentacoin-trusted-reviews-add-practice-icon.png') }}" alt="{{ trans('trp.alt-tags.add-practice') }}">
					</div>
					<p class="practice-subtitle">{!! nl2br(trans('trp.page.index.first-rated-dentist.subtitle')) !!}</p>
					<p>
						{!! nl2br(trans('trp.page.index.first-rated-dentist.description')) !!}
					</p>
					<div class="tac-tablet">
						<a href="{{ getLangUrl('welcome-dentist') }}" class="button button-yellow">
							{!! nl2br(trans('trp.page.index.first-rated-dentist.button')) !!}
						</a>
					</div>
				</div>
			</div>
		</div>

	</div>

	<div class="front-info">
		<div class="third">
			<div class="container">
				<div class="how-works">
					<div class="fixed-width">
						<h2>
							{!! nl2br(trans('trp.page.index.usp-3-title')) !!}
						</h2>
						<p>
							{!! nl2br(trans('trp.page.index.usp-3-content')) !!}
						</p>
					</div>
				</div>
			</div>
		</div>
	</div>
@endif
