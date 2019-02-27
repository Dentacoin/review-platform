@extends('vox')

@section('content')

	<div class="page-questions">

		@if($user->loggedFromBadIp())
			<div class="container">
				<div class="alert alert-warning" style="margin-top: 100px;">
					{{ trans('vox.page.questionnaire.vox-bad-ip') }}
				</div>
			</div>
		@else

			@if(!$welcomerules && $first_question_num == 1)
				<div class="mobile-welcome-images">
					<img class="mobile-bubble-effect" src="{{ url('new-vox-img/blue-circle.png') }}">
					<img class="mobile-person-effect" src="{{ url('new-vox-img/welcome-test-person.png') }}">
				</div>
			@endif

			<div class="container" id="question-meta">
				<div class="questions">

					<div class="col-md-8 col-md-offset-2 clearfix">
						<h1 class="questionnaire-title tac vox-survey-title">
							- {{ $vox->title }} -
							@if($admin)
								<a href="{{ $vox->getLink() }}?goback=1" class="go-back-admin">&laquo; Back</a>
							@endif
						</h1>
						<p class="questionnaire-description tac" {!! !empty($answered) && count($answered)>1 ? 'style="display: none;"' : '' !!} >
							{{ $vox->description }}
						</p>
						<p class="demographic-questionnaire-description tac" style="display: none;" >
							{{ trans('vox.common.demographics') }}
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
					<div class="col-md-12 clearfix">

						@if(!$not_bot)
							<div class="question-group" data-id="bot" id="bot-group">
								<div class="question">
									{!! trans('vox.page.questionnaire.not-robot') !!}
								</div>
								<div class="answers tac">
									<div class="g-recaptcha" id="g-recaptcha" data-callback="sendReCaptcha" style="display: inline-block;" data-sitekey="6LfmCmEUAAAAAH20CTYH0Dg6LGOH7Ko7Wv1DZlO0"></div>
									<div class="alert alert-warning" id="captcha-error" style="display: none;">
										{!! trans('vox.page.questionnaire.not-robot-invalid') !!}
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
									<select class="answer" name="birthyear-answer" id="birthyear-answer">
	                            		<option value="">-</option>
										@for($i=(date('Y')-18);$i>=(date('Y')-90);$i--)
	                            			<option value="{{ $i }}">{{ $i }}</option>
	                            		@endfor
	                            	</select>
								</div>

								<a href="javascript:;" class="next-answer">{!! trans('vox.page.questionnaire.next') !!}</a>
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
									{{ Form::select( 'country_id' , ['' => '-'] + \App\Models\Country::get()->pluck('name', 'id')->toArray() , $user->country_id , array('class' => 'country-select form-control') ) }}
								</div>

								<a href="javascript:;" class="next-answer">{!! trans('vox.page.questionnaire.next') !!}</a>
							</div>
						@endif

						@foreach( $details_fields as $key => $info )
							@if($user->$key==null)
								@include('vox.template-parts.vox-question', [
									'details_question' => $info,
									'details_question_id' => $key
								])
							@endif
						@endforeach
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

				<div class="container done-section">

					<div class="col-md-3">
						<img class="image-left" src="{{ url('new-vox-img/well-done.png') }}">
					</div>

					<div class="col-md-9 tac">
						@if($admin)
							<a href="{{ $vox->getLink() }}?goback=1" class="go-back-admin">&laquo; Back</a>
						@endif
						<h3 class="done-title">
							{!! trans('vox.page.questionnaire.well-done', [
								'who' => '<span class="blue-text">'.$user->getName().'</span>'
							]) !!}
						</h3>
						<h4>
							{!! trans('vox.page.questionnaire.well-done-content', [
								'amount' => '<span id="coins-test">'.$vox->getRewardTotal().'</span>',
								'link' => '<a href="'.getLangUrl('profile').'">',
								'endlink' => '</a>',
							]) !!}
						</h4>

						<p class="next-title">
							{!! trans('vox.page.questionnaire.what-next') !!}
							
						</p>

						<div class="wrapper-buttons">
							<a class="white-button" href="{{ getLangUrl('/') }}">
								{!! trans('vox.page.questionnaire.what-next-another') !!}
								
							</a>
							@if($vox->has_stats)
								<a class="white-button" href="{{ $vox->getStatsList() }}">
									{!! trans('vox.page.questionnaire.what-next-stats') !!}
									
								</a>
							@endif
							<a class="white-button" id="invite-button" href="javascript:;">
								@if($user->is_dentist)
									{!! trans('vox.page.questionnaire.what-next-invite-dentist') !!}
								@else
									{!! trans('vox.page.questionnaire.what-next-invite') !!}
								@endif
								
							</a>
							<div class="invite-link" style="display: none;">
								<p>
								@if($user->is_dentist)
									{!! trans('vox.page.questionnaire.what-next-invite-dentist-hint') !!}
								@else
									{!! trans('vox.page.questionnaire.what-next-invite-hint') !!}
								@endif
									
								</p>
								{{ Form::text( 'link', getLangUrl('invite/'.$user->id.'/'.$user->get_invite_token()), array('class' => 'form-control select-me', 'id' => 'invite-url' ) ) }}
								<div class="share-wrap tal">
									<a class="copy-link" href="javascript:;">
										<img src="{{ url('new-vox-img/copy-icon.png') }}">
										{!! trans('vox.page.questionnaire.what-next-copy') !!}
										
									</a>
									<a class="share fb" data-url="{{ getLangUrl('invite/'.$user->id.'/'.$user->get_invite_token()) }}" data-title="{{ trans('vox.social.share.title') }}">
										<img src="{{ url('new-vox-img/fb-icon.png') }}">
										{!! trans('vox.page.questionnaire.what-next-fb') !!}
										
									</a>
									<a class="share twt" data-url="{{ getLangUrl('invite/'.$user->id.'/'.$user->get_invite_token()) }}" data-title="{{ trans('vox.social.share.title') }}">
										<img src="{{ url('new-vox-img/twitter-icon.png') }}">
										{!! trans('vox.page.questionnaire.what-next-tw') !!}
										
									</a>
									<a class="share messenger" data-url="{{ getLangUrl('invite/'.$user->id.'/'.$user->get_invite_token()) }}" data-title="{{ trans('vox.social.share.title') }}">
										<img src="{{ url('new-vox-img/messenger-icon.png') }}">
										{!! trans('vox.page.questionnaire.what-next-me') !!}
										
									</a>										
									<a href="https://mail.google.com/mail/?view=cm&fs=1&su={!! urlencode( $email_data['title'] ) !!}&body={!! urlencode( $email_data['content'] ) !!}" target="_blank">
										<img src="{{ url('new-vox-img/gmail-icon.png') }}">
										{!! trans('vox.page.questionnaire.what-next-gm') !!}
										
									</a>
								</div>
							</div>
						</div>
						

					</div>
				</div>

				<div class="section-stats">
					<div class="container">
						<img src="{{ url('new-vox-img/stats-front.png') }}">
						<h3>
							{!! trans('vox.page.questionnaire.curious') !!}
							
						</h3>
						<a href="{{ getLangUrl('dental-survey-stats') }}" class="check-stats">
							{{ trans('vox.common.check-statictics') }}
						</a>
					</div>
				</div>
			</div>
		@endif

	</div>


	<script type="text/javascript">
		var vox = {
			count: {{ $total_questions }},
			count_real: {{ $real_questions }},
			reward: {{ intval($vox->getRewardTotal()) }},
			reward_single: {{ $vox->getRewardPerQuestion()->dcn }},
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
				<div class="tac">
					<a class="btn inactive closer simple-countdown" alt-text="Continue">
						Continue in: <span>10</span>
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
			<a class="btn back-btn btn-start-over closer">
				<i class="fas fa-redo"></i>
				Start over
			</a>
			<a class="btn back-btn btn-roll-back closer">
				<i class="far fa-arrow-alt-circle-left"></i> Roll Back
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
							<b>
								{!! trans('vox.page.bans.rules-permaban') !!}
								
							</b>
						</div>
					</div>
					<div class="tac">
						<a class="btn closer">
							{!! trans('vox.page.bans.rules-got-it') !!}
							
						</a>
					</div>
				</div>
			</div>
		</div>

	@endif
    	

@endsection