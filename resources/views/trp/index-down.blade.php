@if(empty($user))

	<div class="info-section">
		<div class="container">
			<h2 class="gbb">{!! nl2br(trans('trp.page.index.hint')) !!}</h2>

			<div class="info-box flex flex-mobile">
				<div class="info-icon">
					<img src="{{ url('img-trp/index-icon-1.png') }}">
				</div>
				<div class="info-text">
					<h3>{!! nl2br(trans('trp.page.index.intro-title-1')) !!}</h3>
					<p>{!! nl2br(trans('trp.page.index.intro-description-1')) !!}</p>
				</div>
			</div>
			<div class="info-box flex flex-mobile">
				<div class="info-icon">
					<img src="{{ url('img-trp/index-icon-2.png') }}">
				</div>
				<div class="info-text">
					<h3>{!! nl2br(trans('trp.page.index.intro-title-2')) !!}</h3>
					<p>{!! nl2br(trans('trp.page.index.intro-description-2')) !!}</p>
				</div>
			</div>
			<div class="info-box flex flex-mobile">
				<div class="info-icon">
					<img src="{{ url('img-trp/index-icon-3.png') }}">
				</div>
				<div class="info-text">
					<h3>{!! nl2br(trans('trp.page.index.intro-title-3')) !!}</h3>
					<p>{!! nl2br(trans('trp.page.index.intro-description-3')) !!}</p>
				</div>
			</div>
			<div class="tac">
				<a href="javascript:;" data-popup="popup-register" class="button button-sign-up-patient">{!! nl2br(trans('trp.page.index.intro-button')) !!}</a>
			</div>
		</div>
	</div>

	<div class="add-practice">
		<div class="container">
			<div class="flex flex-mobile">
				<div class="col">
					<div class="practice-image">
						<img class="pc-practice-img" src="{{ url('img-trp/index-rated-dentist.png') }}">
					</div>
				</div>
				<div class="col">
					<h2>{!! nl2br(trans('trp.page.index.first-rated-dentist.title')) !!}</h2>
					<div class="mobile-practice-img">
						<img src="{{ url('img-trp/index-rated-dentist.png') }}">
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
						<div class="tac">
							<a href="javascript:;" class="button button-white button-sign-up-patient" data-popup="popup-register">
								{!! nl2br(trans('trp.page.index.usp.join-now')) !!}
							</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
@endif
