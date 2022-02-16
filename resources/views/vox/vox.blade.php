@extends('vox')

@section('content')

	<div class="page-questions">
		<div class="loader-mask">
		    <div class="loader">
		      	{{ trans('vox.common.loading') }}
		    </div>
		</div>

		@if(!$welcomerules && $first_question_num == 1)
			<div class="mobile-welcome-images">
				<img class="mobile-bubble-effect" src="{{ url('new-vox-img/blue-circle.png') }}">
				<img class="mobile-person-effect" src="{{ url('new-vox-img/welcome-test-person.png') }}">
			</div>
		@endif

		<div class="container" id="question-meta">
			<div class="questions">

				<div class="quest-wrap">
					<h1 class="questionnaire-title tac vox-survey-title">
						- {{ $vox->title }} -
						@if($testmode)
							<a href="{{ $vox->getLink() }}?testmode=1&goback=1&q-id={{ request('q-id') ?? '0' }}" class="go-back-admin">&laquo; Back</a>
						@endif
						@if($isAdmin)
							<div class="vox-mode-wrapper">
								<a href="{{ $vox->getLink() }}?testmode=0" class="vox-mode {{ $testmode ? '' : 'active' }}">
									Live
								</a>
								<a href="{{ $vox->getLink() }}?testmode=1" class="vox-mode {{ $testmode ? 'active' : '' }}">
									Test
								</a>
							</div>
						@endif
					</h1>
					@if($answered && !$welcome_vox)
						<div class="answered-box">
							<p>
								{!! trans('vox.page.questionnaire.unfinished-survey.text') !!}
							</p>
							<a href="javascript:;" class="start-over" u-id="{{ $user->id }}" vox-id="{{ $vox->id }}" url="{{ getLangUrl('start-over') }}" cur-url="{{ $vox->getLink() }}?testmode=1">
								<img src="{{ url('new-vox-img/start-over.svg') }}">
								{!! trans('vox.page.questionnaire.unfinished-survey.button') !!}
							</a>
						</div>
					@endif
					<p class="questionnaire-description tac" {!! $answered>1 ? 'style="display: none;"' : '' !!} >
						{{ $vox->description }}
					</p>

					<div class="questions-dots">
						<div class="dot" id="current-question-bar" style="width: 0%;"></div>
					</div>
					<div class="flex questions-header clearfix">
						<div class="flex-1">
							<span>
								{!! trans('vox.common.estimated_time', [
									'time' => '<span id="current-question-num"></span>'
								]) !!}
							</span>
						</div>
						<div class="flex-1 tar">
							<span>
								<span id="dcn-test-reward-before">
									{{ !empty($vox->complex) ? 'Max ' : '' }} {!! trans('vox.common.dcn_to_be_collected') !!}: {{ $vox->getRewardTotal() }}
								</span>
								<span id="dcn-test-reward-after" style="display: none;">
									{!! trans('vox.common.dcn_collected') !!}:
									<span id="current-question-reward">
										
									</span>
								</span>
								<span id="dcn-test-reward-bonus" style="display: none;">
									/ 
									{!! trans('vox.common.dcn_bonus') !!}:
									<span id="bonus-question-reward">
										0
									</span>
								</span>
							</span>
						</div>
					</div>

					<div id="wrong-control" class="alert alert-warning" style="display: none;">
						{!! trans('vox.page.questionnaire.wrong-answer') !!}
					</div>
				</div>
				<div class="quest-wrap" id="q-wrap">
					<div class="loader-survey" id="loader-survey" style="display: none;"><img src="{{ url('new-vox-img/survey-loader.gif') }}"></div>
					<div id="questions-box">
						@if(!$not_bot)
							<div class="question-group" data-id="bot" id="bot-group">
								<div class="question">
									{!! trans('vox.page.questionnaire.not-robot') !!}
								</div>
								<div class="answers tac">
									<div class="g-recaptcha" id="g-recaptcha" data-callback="sendReCaptcha" style="display: inline-block;" data-sitekey="6LddiYEeAAAAAJR7ynIBy4aDGm6tFzWs3rCw3MTK"></div>
									<div class="alert alert-warning" id="captcha-error" style="display: none;">
										{!! trans('vox.page.questionnaire.not-robot-invalid') !!}
									</div>					
								</div>
							</div>
						@endif
					</div>
 				</div>
			</div>
			<div style="display: none; margin-top: 10px;text-align: center;" class="answer-error alert alert-warning">
				{!! trans('vox.page.questionnaire.answer-error') !!}
			</div>
			<div style="display: none; margin-top: 10px;text-align: center;" class="answer-scale-error alert alert-warning">
				{!! trans('vox.page.questionnaire.answer-scale-error') !!}
			</div>
		</div>

		<div class="question-done page-questions-done" id="question-done" style="display: none;">
			<div class="taken-survey-wrapper">
				<div class="container">
					<div class="flex">
						<div class="col">
							<img class="taken-survey-image" src="{{ url('new-vox-img/dentavox-man-survey-taken.jpg') }}" alt="Dentavox man survey taken">
						</div>
						<div class="col taken-survey-description">
							@if($testmode)
								<a href="{{ $vox->getLink() }}?testmode=1&goback=1" class="go-back-admin">&laquo; Back</a>
							@endif
							<h3 class="done-title">
								{!! trans('vox.page.questionnaire.well-done', [
									'who' => '<span class="blue-text">'.$user->getNames().'</span>'
								]) !!}
							</h3>
							@if($user->platform == 'external')
								<p>
									You’ve just earned <span class="coins-test"></span> DCN!
								</p>
							@else
								<p>
									{!! nl2br(trans('vox.page.questionnaire.well-done-content', [
										'amount' => '<span class="coins-test"></span>',
										'link' => '<a href="https://account.dentacoin.com/?platform=dentavox">',
										'endlink' => '</a>',
									])) !!}
								</p>
							@endif
						</div>
					</div>
				</div>

				@include('vox.template-parts.related-voxes', [
					'related_voxes' => $related_voxes,
					'suggested_voxes' => $suggested_voxes,
				])

				@include('vox.template-parts.stats-video', [
					'vox' => $vox,
					'related_voxes' => $related_voxes,
					'suggested_voxes' => $suggested_voxes
				])

				@include('vox.template-parts.suggested-voxes', [
					'related_voxes' => $related_voxes,
					'suggested_voxes' => $suggested_voxes,
				])
			</div>
		</div>
	</div>

	<script type="text/javascript">
		var vox = {
			count: {{ $total_questions }},
			count_real: {{ $real_questions }},
			reward: {{ intval($vox->getRewardTotal()) }},
			reward_single: {{ $vox->getRewardPerQuestion()->dcn }},
			current: {{ $first_question_num }},
			answered_without_skip_count: {{ $answered_without_skip_count }},
			url: '{{ $vox->getLink() }}',
		};

		var welcome_vox = {{ !empty($welcome_vox) ? 'true' : 'false' }};
		var welcome_vox_q_count = {{ !empty($welcome_vox) ? $welcome_vox->questions->count() : 'false' }};
		var testmode = {{ $testmode ? $testmode : 'false' }};
		var next_q_url = '{{ getLangUrl('get-next-question') }}';
		var vox_id = {{ $vox->id }};
		@if($testmode)
			var question_id = {!! !empty(request('q-id')) ? request('q-id') : 'false' !!};
		@endif
	</script>

	@if(!empty($cross_checks))
		<div class="popup cross-checks">
			<div class="wrapper">
				<div class="inner tac">
					<h2>{!! nl2br(trans('vox.popup.cross-checks-popup.title')) !!}</h2>
					<h4>{!! nl2br(trans('vox.popup.cross-checks-popup.subtitle-1')) !!}</h4>
					<h4>{!! nl2br(trans('vox.popup.cross-checks-popup.subtitle-2')) !!}</h4>
					<div class="cross-checks-answers">
					</div>
					<br/>
					<a href="javascript:;" class="white-button update-answer">{!! nl2br(trans('vox.popup.cross-checks-popup.update')) !!}</a>
					<div style="margin-top: 20px; text-align: center; display: none;" class="pick-answer alert alert-warning">
						{!! nl2br(trans('vox.page.questionnaire.answer-error')) !!}
					</div>
				</div>
			</div>
		</div>
	@endif

	<div class="popup ban">
		<div class="wrapper">
			<img src="" class="zman" />
			<div class="inner">
				<h2></h2>
				<p>
				</p>
				<small>
					{!! nl2br(trans('vox.popup.ban-popup.title')) !!}
				</small>
				<h3 class="hours-countdown">
					{!! nl2br(trans('vox.popup.ban-popup.return-to-vox')) !!} <span></span>
				</h3>
			</div>
			<a class="closer-pop x">
				<img class="close-x-img" src="{{ url('new-vox-img/close-popup.png') }}"/>
			</a>
		</div>
	</div>

	<div class="popup warning">
		<div class="wrapper">
			<img src="" class="zman" />
			<div class="inner">
				<h2></h2>
				<p>
					
				</p>
				<div class="tac">
					<a class="btn inactive closer-pop simple-countdown" alt-text="{{ trans('vox.popup.warning-popup.continue-button') }}">
						{!! nl2br(trans('vox.popup.warning-popup.continue')) !!} <span>10</span>
					</a>
				</div>
			</div>
		</div>
	</div>

	<div class="popcircle warning">
		<img src="" class="zman" />
		<div class="wrapper">
			<h2></h2>
			<p>
				
			</p>
			<a class="btn back-btn btn-start-over">
				<img src="{{ url('img/redo-arrow.png') }}"/> 
				{!! nl2br(trans('vox.popup.warning-popup.start-over')) !!}
			</a>
			<a class="btn back-btn btn-roll-back">
				<img src="{{ url('img/arrow-circle-left.png') }}"/> 
				{!! nl2br(trans('vox.popup.warning-popup.roll-back')) !!}
			</a>
		</div>
	</div>

	<div class="popup vpn close-on-shield">
		<div class="wrapper">
			<img src="{{ url('new-vox-img/vpn.png') }}" class="zman" />
			<div class="inner">
				<h2>Oops, VPN or proxy detected</h2>
				<p>
					It seems like you are using VPN or proxy. Please turn it off to access your DentaVox platform. 
				</p>
				<small>
					If you have disabled the VPN, proxy or routing software and you still see this message, contact your internet service provider for further assistance.
				</small>
			</div>
			<a class="closer-pop x">
				<img class="close-x-img" src="{{ url('new-vox-img/close-popup.png') }}"/>
			</a>
		</div>
	</div>

	@if($welcomerules)

		<div class="popup active first-test">
			<div class="wrapper">
				<div class="inner">
					<h2 class="tac">
						{!! trans('vox.page.bans.rules-title') !!}
					</h2>
					<div class="flex rules">
						<div class="col flex flex-center">
							{!! trans('vox.page.bans.rules-time') !!}
							<img src="{{ url('new-vox-img/popup-time.png') }}" />
						</div>
						<div class="col flex flex-center">
							<img src="{{ url('new-vox-img/popup-focused.png') }}" />
							{!! trans('vox.page.bans.rules-focus') !!}
						</div>
					</div>
					<h3 class="tac">
						{!! trans('vox.page.bans.rules-banned') !!}
					</h3>
					<div class="flex icons">
						<div class="col">
							<img src="{{ url('new-vox-img/popup-sign-1.png') }}" />
							{{ trans('vox.page.bans.24h') }}
						</div>
						<div class="col">
							<img src="{{ url('new-vox-img/popup-sign-2.png') }}" />
							{{ trans('vox.page.bans.72h') }}
						</div>
						<div class="col">
							<img src="{{ url('new-vox-img/popup-sign-3.png') }}" />
							{{ trans('vox.page.bans.168h') }}
						</div>
						<div class="col">
							<img src="{{ url('new-vox-img/popup-sign-4.png') }}" />
							<b>
								{!! trans('vox.page.bans.rules-permaban') !!}
							</b>
						</div>
					</div>
					<div class="tac">
						<div class="checkbox">
							<label class="checkbox-label agree-label" for="agree-faq" style="text-align: left; margin-bottom: 30px; margin-top: -20px;">
								<input type="checkbox" class="special-checkbox" id="agree-faq" name="agree-faq" value="1">
								<div class="checkbox-square">✓</div>
								{!! trans('vox.page.bans.agree-faq', [
									'link' => '<a href="'.getLangUrl('faq').'" target="_blank">',
									'endlink' => '</a>',
								]) !!}
							</label>
						</div>
						<div class="alert alert-warning rules-error" style="display: none;margin-bottom: 20px;">{!! trans('vox.page.bans.rules.error') !!}</div> 
						<a class="btn rules-ok">
							{!! trans('vox.page.bans.rules-got-it') !!}
						</a>
					</div>
				</div>
			</div>
		</div>
	@endif
    	
	{!! csrf_field() !!}
	
@endsection