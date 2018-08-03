@extends('vox')

@section('content')

		<div class="container page-questions">

			@if($user->loggedFromBadIp())

				<div class="alert alert-warning" style="margin-top: 100px;">
					{{ trans('vox.page.'.$current_page.'.vox-bad-ip') }}

					<a id="bad-ip-appeal" href="{{ getLangUrl('appeal') }}"> {{ trans('vox.page.'.$current_page.'.vox-bad-ip-button') }} </a>
				</div>

			@else
				<a href="{{ getLangUrl('/') }}" class="questions-back"><i class="fa fa-arrow-left"></i> {{ trans('vox.common.questionnaires') }}</a>

				<div id="question-meta">

					<h1 class="questionnaire-title">
						{{ $vox->title }}
						@if(!empty($admin))
							<a href="{{ $vox->getLink() }}?goback=1" class="go-back-admin">&laquo; Back</a>
						@endif
					</h1>
					<p class="questionnaire-description" {!! !empty($answered) && count($answered)>1 ? 'style="display: none;"' : '' !!} >
						{{ $vox->description }}
					</p>
					<div class="questions">

						<div class="questions-dots">
							<div class="dot" id="current-question-bar" style="width: 0%;"></div>
						</div>
						<div class="row questions-header clearfix">
							<div class="col-md-6">
								<span class="bold">
									{!! trans('vox.common.estimated_time', [
										'time' => '<span id="current-question-num"></span>'
									]) !!}
								</span>
							</div>
							<div class="col-md-6 tar">
								<span class="bold">
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
							<div class="question-group birthyear-question" style="display: none;">
								<div class="question">
									What's your year of birth?
								</div>
								<div class="answers">
									<input type="number" name="birthyear-answer" id="birthyear-answer" min="{{ date('Y')-100 }}" max="{{ date('Y')-18 }}">
								</div>

								<a href="javascript:;" class="next-answer">{!! trans('vox.page.'.$current_page.'.next') !!}</a>
							</div>
						@endif

						@if(!$user->gender)
							<div class="question-group gender-question single-choice" style="display: none;">
								<div class="question">
									What's your gender?
								</div>
								<div class="answers">
									<a class="answer answer-checkbox" for="answer-gende-m" data-num="m">
										<input id="answer-gender-m" type="radio" name="gender-answer" class="answer" value="m" style="display: none;">
										Male										
									</a>
									<a class="answer answer-checkbox" for="answer-gender-f" data-num="f">
										<input id="answer-gender-f" type="radio" name="gender-answer" class="answer" value="f" style="display: none;">
										Female										
									</a>
								</div>
							</div>
						@endif

						@if(!$user->city_id && !$user->country_id )
							<div class="question-group location-question" style="display: none;">
								<div class="question">
									Where do you live?
								</div>
								<div class="answers">
									{{ Form::select( 'country_id' , ['' => '-'] + \App\Models\Country::get()->pluck('name', 'id')->toArray() , $user->country_id , array('class' => 'form-control country-select') ) }}
	                                {{ Form::select( 'city_id' , $user->country_id ? \App\Models\City::where('country_id', $user->country_id)->get()->pluck('name', 'id')->toArray() : ['' => trans('vox.common.select-country')] , $user->city_id , array('class' => 'form-control city-select') ) }}
								</div>

								<a href="javascript:;" class="next-answer">{!! trans('vox.page.'.$current_page.'.next') !!}</a>
							</div>
						@endif

						@if($details_test)
							@foreach( $details_test->questions as $question )
								@include('vox.template-parts.vox-question')
							@endforeach
						@endif
	<!-- 
						<div class="question-hints">
							<p class="hint">
								{{ trans('vox.page.'.$current_page.'.finish-all', ['reward' => $vox->getRewardTotal()]) }}
							</p>
						</div>
	 -->
					</div>
					<div style="display: none; margin-top: 10px;text-align: center;" class="answer-error alert alert-danger">
						{!! trans('vox.page.'.$current_page.'.answer-error') !!}
					</div>

				</div>

				<div class="question-done page-questions-done" id="question-done" style="display: none;">
					<div class="modal-body">
						<p class="popup-title">
							{{ trans('vox.page.'.$current_page.'.good-job') }}
						</p>
						<p class="popup-second-title bold">
							{{ trans('vox.page.'.$current_page.'.just-won') }}
						</p>
						<div class="price">
							<img src="img-vox/dc-logo.png"/>
							<span class="coins"><span class="coins" id="coins-test" style="margin-top: 0px;">{{ $vox->getRewardTotal() }}</span> DCN</span>
						</div>
					</div>
					
					<p class="buttons-description">
						{{ trans('vox.page.'.$current_page.'.try-another') }}
					</p>
					<a href="{{ getLangUrl('/') }}" class="button-questionnaries">{{ trans('vox.common.questionnaires') }}</a>

					<p class="buttons-description">
						{{ trans('vox.page.'.$current_page.'.withdraw') }}
					</p>
					<a href="{{ getLangUrl('profile/wallet') }}" class="button-wallet">{{ trans('vox.page.'.$current_page.'.wallet') }}</a>

					<p class="buttons-description">
						{{ trans('vox.page.'.$current_page.'.invite') }}
					</p>

	    			@if(!$user->my_address())
						<a href="{{ getLangUrl('profile/invite') }}" class="button-wallet">{{ trans('vox.page.'.$current_page.'.invite-no-address') }}</a>
	    			@else

		    			<div id="invite-wrapper">
							{{ Form::text( 'link', getLangUrl('invite/'.$user->id.'/'.$user->get_invite_token()), array('class' => 'form-control select-me' ) ) }}
							<p class="buttons-description">
								{{ trans('vox.page.'.$current_page.'.invite-share') }}
							</p>
							<br/>
							<div class="no-mobile-share tac" style="display: none;">
								<a class="btn btn-primary share fb" data-url="{{ getLangUrl('invite/'.$user->id.'/'.$user->get_invite_token()) }}" data-title="{{ trans('vox.social.share.title') }}">
									<i class="fa fa-facebook">
									</i>
									Facebook
								</a>
								<a class="btn btn-primary share twt" data-url="{{ getLangUrl('invite/'.$user->id.'/'.$user->get_invite_token()) }}" data-title="{{ trans('vox.social.share.title') }}">
									<i class="fa fa-twitter">
									</i>
									Twitter
								</a>
								<a class="btn btn-primary share google" data-url="{{ getLangUrl('invite/'.$user->id.'/'.$user->get_invite_token()) }}" data-title="{{ trans('vox.social.share.title') }}">
									<i class="fa fa-google-plus">
									</i>
									Google+
								</a>
								<a class="btn btn-primary" href="{{ getLangUrl('profile/invite') }}">
									<i class="fa fa-envelope">
									</i>
									Email
								</a>
							</div>
							<div class="has-mobile-share tac" style="display: none;">
								<a class="btn btn-primary native-share" data-url="{{ getLangUrl('invite/'.$user->id.'/'.$user->get_invite_token()) }}" data-title="{{ trans('vox.social.share.title') }}">
									<i class="fa fa-share">
									</i>
									Share
								</a>
								<a class="btn btn-primary" href="{{ getLangUrl('profile/invite') }}">
									<i class="fa fa-envelope">
									</i>
									Email
								</a>				
							</div>
						</div>

					@endif

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


		<div class="new-popup popup-permanent-ban">
			<div class="new-popup-wrapper">
				<div class="step">
					<img src="{{ url('img/popups/permanent-ban.jpg') }}" />
					<h2>
						{!! nl2br(trans('vox.page.'.$current_page.'.popup.permanent-ban.title')) !!}
					</h2>
					<p>
						{!! nl2br(trans('vox.page.'.$current_page.'.popup.permanent-ban.content')) !!}
					</p>
					<a class="active-btn step-btn">
						{!! nl2br(trans('vox.page.'.$current_page.'.popup.close')) !!}
					</a>
				</div>
			</div>
		</div>

		<div class="new-popup popup-temporary-ban">
			<div class="new-popup-wrapper">
				<div class="step">
					<img src="{{ url('img/popups/temporary-ban.jpg') }}" />
					<h2>
						{!! nl2br(trans('vox.page.'.$current_page.'.popup.temporary-ban.title')) !!}
					</h2>
					<p>
						{!! nl2br(trans('vox.page.'.$current_page.'.popup.temporary-ban.content')) !!}
					</p>
					<a class="active-btn step-btn">
						{!! nl2br(trans('vox.page.'.$current_page.'.popup.close')) !!}
					</a>
				</div>
			</div>
		</div>

		<div class="new-popup popup-mistakes">
			<div class="new-popup-wrapper">
				<div class="step">
					<img src="{{ url('img/popups/mistakes.jpg') }}" />
					<h2>
						{!! trans('vox.page.'.$current_page.'.popup.mistakes.title', ['count' => '<span id="mistakes-left"></span>']) !!}
					</h2>
					<p>
						{!! nl2br(trans('vox.page.'.$current_page.'.popup.mistakes.content')) !!}
					</p>
					<a class="active-btn step-btn">
						{!! nl2br(trans('vox.page.'.$current_page.'.popup.close')) !!}
					</a>
				</div>
			</div>
		</div>
    	

@endsection