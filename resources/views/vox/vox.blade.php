@extends('vox')

@section('content')

	<div class="page-questions">

		@if($user->loggedFromBadIp())
			<div class="container">
				<div class="alert alert-warning" style="margin-top: 100px;">
					{{ trans('vox.page.'.$current_page.'.vox-bad-ip') }}

					<a id="bad-ip-appeal" href="{{ getLangUrl('appeal') }}"> {{ trans('vox.page.'.$current_page.'.vox-bad-ip-button') }} </a>
				</div>
			</div>
		@else

			<div class="container" id="question-meta">
				<div class="questions">

					<div class="col-md-8 col-md-offset-2 clearfix">
						<h1 class="questionnaire-title tac">
							- {{ $vox->title }} -
							@if($admin)
								<a href="{{ $vox->getLink() }}?goback=1" class="go-back-admin">&laquo; Back</a>
							@endif
						</h1>
						<p class="questionnaire-description tac" {!! !empty($answered) && count($answered)>1 ? 'style="display: none;"' : '' !!} >
							{{ $vox->description }}
						</p>
						<p class="demographic-questionnaire-description tac" style="display: none;" >
							You're almost done! Help us complete your demographic profile to ensure quality dental survey results!
						</p>

						<div class="questions-dots">
							<div class="dot" id="current-question-bar" style="width: 0%;"></div>
						</div>
						<div class="row questions-header clearfix">
							<div class="col-md-6">
								<span>
									{!! trans('vox.common.estimated_time', [
										'time' => '<span id="current-question-num"></span>'
									]) !!}
								</span>
							</div>
							<div class="col-md-6 tar">
								<span>
									<span id="dcn-test-reward-before">
										{{ $vox->complex ? 'Max ' : '' }} {!! trans('vox.common.dcn_to_be_collected') !!}: {{ $vox->getRewardTotal() }}
									</span>
									<span id="dcn-test-reward-after" style="display: none;">
										{!! trans('vox.common.dcn_collected') !!}:
										<span id="current-question-reward">
											
										</span>
									</span>
								</span>
							</div>
						</div>

						<div id="wrong-control" class="alert alert-warning" style="display: none;">
							{!! trans('vox.page.'.$current_page.'.wrong-answer') !!}
						</div>
					</div>
					<div class="col-md-12 clearfix">

						@if(!$not_bot)
							<div class="question-group" data-id="bot" id="bot-group">
								<div class="question">
									{!! trans('vox.page.'.$current_page.'.not-robot') !!}
								</div>
								<!--
									<div class="answers tac">
										<label for="iagree">
											<input type="checkbox" id="iagree" />
											{!! trans('vox.page.'.$current_page.'.iagree') !!}	
										</label>
									</div>
								-->
								<div class="answers tac">
									<div class="g-recaptcha" id="g-recaptcha" data-callback="sendReCaptcha" style="display: inline-block;" data-sitekey="6LfmCmEUAAAAAH20CTYH0Dg6LGOH7Ko7Wv1DZlO0"></div>
									<div class="alert alert-warning" id="captcha-error" style="display: none;">
										{!! trans('vox.page.'.$current_page.'.not-robot-invalid') !!}
									</div>					
								</div>
							</div>
						@endif

						@foreach( $vox->questions as $question )
							@include('vox.template-parts.vox-question')
						@endforeach

						@if(!$user->birthyear)
							<div class="question-group birthyear-question tac" style="display: none;">
								<div class="question">
									What's your year of birth?
								</div>
								<div class="answers">
									<input type="number" name="birthyear-answer" class="answer" id="birthyear-answer" min="{{ date('Y')-100 }}" max="{{ date('Y')-18 }}">
								</div>

								<a href="javascript:;" class="next-answer">{!! trans('vox.page.'.$current_page.'.next') !!}</a>
							</div>
						@endif

						@if(!$user->gender)
							<div class="question-group gender-question single-choice" style="display: none;">
								<div class="question">
									What's your biological sex?
								</div>
								<div class="answers">
									<a class="answer answer" for="answer-gende-m" data-num="m">
										<input id="answer-gender-m" type="radio" name="gender-answer" class="answer" value="m" style="display: none;">
										Male										
									</a>
									<a class="answer answer" for="answer-gender-f" data-num="f">
										<input id="answer-gender-f" type="radio" name="gender-answer" class="answer" value="f" style="display: none;">
										Female										
									</a>
								</div>
							</div>
						@endif

						@if(!$user->country_id )
							<div class="question-group location-question" style="display: none;">
								<div class="question">
									Where do you live?
								</div>
								<div class="answers">
									{{ Form::select( 'country_id' , ['' => '-'] + \App\Models\Country::get()->pluck('name', 'id')->toArray() , $user->country_id , array('class' => 'form-control') ) }}
								</div>

								<a href="javascript:;" class="next-answer">{!! trans('vox.page.'.$current_page.'.next') !!}</a>
							</div>
						@endif

						@foreach( $details_fields as $key => $info )
							@if(empty($user->$key))
								@include('vox.template-parts.vox-question', [
									'details_question' => $info,
									'details_question_id' => $key
								])
							@endif
						@endforeach
	 				</div>
				</div>
				<div style="display: none; margin-top: 10px;text-align: center;" class="answer-error alert alert-danger">
					{!! trans('vox.page.'.$current_page.'.answer-error') !!}
				</div>
			</div>


			<div class="question-done page-questions-done" id="question-done" style="display: none;">

				<div class="container done-section">

					<div class="col-md-3">
						<img class="image-left" src="{{ url('new-vox-img/well-done.png') }}">
					</div>

					<div class="col-md-9 tac">
						<h3 class="done-title">Well done, <span class="blue-text"> {{ $user->getName() }}!</span></h3>
						<h4>
							You’ve just earned <span id="coins-test">{{ $vox->getRewardTotal() }}</span> DCN! Thank you for sharing your valuable insights! To review / withdraw your reward, go to your <a href="{{ getLangUrl('profile/wallet') }}">Dentacoin Wallet.</a>
						</h4>

						<p class="next-title">What do you feel like doing next?</p>

						<div class="wrapper-buttons">
							<a class="white-button" href="{{ getLangUrl('/') }}">Take another survey</a>
							@if($vox->stats_questions->isNotEmpty())
								<a class="white-button" href="{{ $vox->getStatsList() }}">View stats</a>
							@endif
							<a class="white-button" id="invite-button" href="javascript:;">Invite friends</a>
							<div class="invite-link" style="display: none;">
								<p>
									Use this link to refer friends and earn DCN for each new registration!
								</p>
								{{ Form::text( 'link', getLangUrl('invite/'.$user->id.'/'.$user->get_invite_token()), array('class' => 'form-control select-me', 'id' => 'invite-url' ) ) }}
								<div class="share-wrap tal">
									<a class="copy-link" href="javascript:;">
										<img src="{{ url('new-vox-img/copy-icon.png') }}">
										Copy link
									</a>
									<a class="share fb" data-url="{{ getLangUrl('invite/'.$user->id.'/'.$user->get_invite_token()) }}" data-title="{{ trans('vox.social.share.title') }}">
										<img src="{{ url('new-vox-img/fb-icon.png') }}">
										Share on Facebook
									</a>
									<a class="share twt" data-url="{{ getLangUrl('invite/'.$user->id.'/'.$user->get_invite_token()) }}" data-title="{{ trans('vox.social.share.title') }}">
										<img src="{{ url('new-vox-img/twitter-icon.png') }}">
										Share on Twitter
									</a>
									<a class="share messenger" data-url="{{ getLangUrl('invite/'.$user->id.'/'.$user->get_invite_token()) }}" data-title="{{ trans('vox.social.share.title') }}">
										<img src="{{ url('new-vox-img/messenger-icon.png') }}">
										Send via Messenger
									</a>										
									<a href="https://mail.google.com/mail/?view=cm&fs=1&su={!! urlencode(trans('vox.page.'.$current_page.'.invite-gmail-subject')) !!}&body={!! urlencode( trans('vox.page.'.$current_page.'.invite-gmail-body' , ['link' => getLangUrl('invite/'.$user->id.'/'.$user->get_invite_token()) ]) ) !!}" target="_blank">
										<img src="{{ url('new-vox-img/gmail-icon.png') }}">
										Send via Gmail
									</a>
								</div>
							</div>
						</div>
						

					</div>
				</div>

				<div class="section-stats">
					<div class="container">
						<img src="{{ url('new-vox-img/stats-front.png') }}">
						<h3>Curious to see other survey stats?</h3>
						<a href="{{ getLangUrl('dental-survey-stats') }}" class="check-stats">Check stats</a>
					</div>
				</div>
			</div>
		@endif

	</div>


	<script type="text/javascript">
		var vox = {
			count: {{ $real_questions }},
			reward: {{ intval($vox->getRewardTotal()) }},
			current: {{ $first_question_num }},
			url: '{{ $vox->getLink() }}'
		}
	</script>


	<div class="popup ban">
		<div class="wrapper">
			<img src="" class="zman" />
			<div class="inner">
				<h2></h2>
				<p>
				</p>
				<small>
					Note: Bans are irreversible. Please, do not send appeals to our Support.
				</small>
				<h3 class="hours-countdown">
					Return to DentaVox in: <span></span>
				</h3>
			</div>
			<a class="closer x">
				<i class="fas fa-times"></i>
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
				<a class="btn inactive closer simple-countdown" alt-text="Continue">
					Continue in: <span>10</span>
				</a>
			</div>
		</div>
	</div>
	<div class="popcircle warning">
		<img src="" class="zman" />
		<div class="wrapper">
			<h2></h2>
			<p>
				
			</p>
			<a class="btn closer">
				<i class="far fa-arrow-alt-circle-left"></i> Roll Back
			</a>
		</div>
	</div>

	@if(!empty($user->filledVoxes()) && false)

		<div class="popup active first-test">
			<div class="wrapper">
				<div class="inner">
					<h2 class="tac">DentaVox rules</h2>
					<div class="flex rules">
						<div class="col flex flex-center">
							Take your time
							<img src="{{ url('new-vox-img/popup-time.png') }}" />
						</div>
						<div class="col flex flex-center">
							<img src="{{ url('new-vox-img/popup-focused.png') }}" />
							Stay Focused
						</div>
					</div>
					<h3 class="tac">or you'll get banned</h3>
					<div class="flex icons">
						<div class="col">
							<img src="{{ url('new-vox-img/popup-sign-1.png') }}" />
							24h
						</div>
						<div class="col">
							<img src="{{ url('new-vox-img/popup-sign-2.png') }}" />
							72h
						</div>
						<div class="col">
							<img src="{{ url('new-vox-img/popup-sign-3.png') }}" />
							168h
						</div>
						<div class="col">
							<img src="{{ url('new-vox-img/popup-sign-4.png') }}" />
							<b>permaban</b>
						</div>
					</div>
					<div class="tac">
						<a class="btn closer">
							Got it
						</a>
					</div>
				</div>
			</div>
		</div>

	@endif
    	

@endsection