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
							@if($testmode)
								<a href="{{ $vox->getLink() }}?goback=1" class="go-back-admin">&laquo; Back</a>
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
						@if(!empty($answered))
							<div class="answered-box">
								<p>
									{!! trans('vox.page.questionnaire.unfinished-survey.text') !!}
								</p>
								<a href="javascript:;" class="start-over" u-id="{{ $user->id }}" vox-id="{{ $vox->id }}" url="{{ getLangUrl('start-over') }}">
									<img src="{{ url('new-vox-img/start-over.svg') }}">
									{!! trans('vox.page.questionnaire.unfinished-survey.button') !!}
								</a>
							</div>
						@endif
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
					<div class="col-md-8 col-md-offset-2 clearfix">

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

						@if(!empty($welcome_vox))
							@foreach( $welcome_vox->questions as $question )
								@include('vox.template-parts.vox-question')
							@endforeach
						@endif

						@foreach( $vox->questions as $question )
							@include('vox.template-parts.vox-question')
						@endforeach

						@if(!$user->birthyear)
							<div class="question-group birthyear-question tac" style="display: none;">
								<div class="question">
									{!! trans('vox.page.questionnaire.question-birth') !!}
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
									{!! trans('vox.page.questionnaire.question-sex') !!}
								</div>
								<div class="answers">
									<a class="answer answer" for="answer-gende-m" data-num="m">
										<input id="answer-gender-m" type="radio" name="gender-answer" class="answer" value="m" style="display: none;">
										{!! trans('vox.page.questionnaire.question-sex-m') !!}
									</a>
									<a class="answer answer" for="answer-gender-f" data-num="f">
										<input id="answer-gender-f" type="radio" name="gender-answer" class="answer" value="f" style="display: none;">
										{!! trans('vox.page.questionnaire.question-sex-f') !!}
									</a>
								</div>
							</div>
						@endif

						@if(!$user->country_id )
							<div class="question-group location-question" style="display: none;">
								<div class="question">
									{!! trans('vox.page.questionnaire.question-country') !!}
								</div>
								<div class="answers">
									<div class="alert alert-warning ip-country mobile" style="display: none;">
		                        		Hmm... Your IP thinks differently. <br/>
										Sure you've entered the right country?
			                        </div>
									{{ Form::select( 'country_id' , ['' => '-'] + \App\Models\Country::with('translations')->get()->pluck('name', 'id')->toArray() , $user->country_id , array('class' => 'country-select form-control country-dropdown', 'real-country' => !empty($country_id) ? $country_id : '') ) }}
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
						@if($done_all)
							<img class="image-left done-all-image" src="{!! url('new-vox-img/all-surveys.png') !!}">
						@else
							<img class="image-left" src="{!! url('new-vox-img/well-done.png') !!}">
						@endif
					</div>

					<div class="col-md-9 tac">
						@if($testmode)
							<a href="{{ $vox->getLink() }}?goback=1" class="go-back-admin">&laquo; Back</a>
						@endif

						<h3 class="done-title">
							@if($done_all)
								{!! trans('vox.page.questionnaire.well-done.all-surveys', [
									'who' => '<span class="blue-text">'.$user->getName().'</span>'
								]) !!}
							@else
								{!! trans('vox.page.questionnaire.well-done', [
									'who' => '<span class="blue-text">'.$user->getName().'</span>'
								]) !!}
							@endif
						</h3>

						<h4 class="done-desc">
							@if($done_all)
								{!! trans('vox.page.questionnaire.well-done-content.all-surveys', [
									'amount' => '<span class="coins-test">'.$vox->getRewardTotal().'</span>',
								]) !!}
							@else
								{!! trans('vox.page.questionnaire.well-done-content', [
									'amount' => '<span class="coins-test">'.$vox->getRewardTotal().'</span>',
									'link' => '<a href="https://account.dentacoin.com/?platform=dentavox">',
									'endlink' => '</a>',
								]) !!}
							@endif
						</h4>

						<p class="next-title">
							@if($done_all)
								{!! trans('vox.page.questionnaire.what-next.all-surveys') !!}
							@endif
						</p>

						<div class="wrapper-buttons">
							@if($done_all)

								@if($vox->has_stats)
									<a class="white-button" href="{{ $vox->getStatsList() }}">
										{!! trans('vox.page.questionnaire.what-next-stats.all-surveys') !!}
									</a>
								@endif
								<a class="white-button" href="{{ getLangUrl('/') }}">
									{!! trans('vox.page.questionnaire.go-surveys.all-surveys') !!}
								</a>

							@else
								<a class="white-button" id="scroll-to-surveys" href="javascript:;">
									{!! trans('vox.page.questionnaire.what-next-another') !!}
								</a>
								@if($vox->has_stats)
									<a class="white-button" href="{{ $vox->getStatsList() }}">
										{!! trans('vox.page.questionnaire.what-next-stats') !!}
									</a>
								@endif
							@endif
							<a class="white-button" href="https://account.dentacoin.com/?platform=dentavox">
								{!! trans('vox.page.questionnaire.open-wallet.all-surveys') !!}
							</a>
						</div>

						@if($done_all)
							<p class="what-do-next">
								{!! trans('vox.page.questionnaire.check-apointment.all-surveys') !!}
							</p>
						@endif
					</div>
				</div>

				@include('vox.template-parts.vox-done-parts')
			</div>

			<div class="question-done page-questions-done" id="question-related-done" style="display: none;">

				<div class="container done-section">

					<div class="col-md-3">
						<img class="image-left" src="{{ url('new-vox-img/well-done.png') }}">
					</div>

					<div class="col-md-9 related-container tac">
						@if($testmode)
							<a href="{{ $vox->getLink() }}?goback=1" class="go-back-admin">&laquo; Back</a>
						@endif

						<h3 class="done-title">
							{!! trans('vox.page.questionnaire.well-done', [
								'who' => '<span class="blue-text">'.$user->getName().'</span>'
							]) !!}
						</h3>
						<h4>
							{!! trans('vox.page.questionnaire.well-done-content', [
								'amount' => '<span class="coins-test">'.$vox->getRewardTotal().'</span>',
								'link' => '<a href="https://account.dentacoin.com/?platform=dentavox">',
								'endlink' => '</a>',
							]) !!}
						</h4>

						@if(!empty($related_vox))
							<div class="section-recent-surveys relateds">
								<h4>{{ trans('vox.page.questionnaire.related-survey') }}</h4>
								<div class="questions-inner">

							      	<div class="swiper-slide">
							      		<div class="slider-inner">
								    		<div class="slide-padding">
								    			<a href="{{ $related_vox->getLink() }}" class="cover" style="background-image: url('{{ $related_vox->getImageUrl() }}');" alt='{{ trans("vox.page.stats.title-single", ["name" => $related_vox->title ]) }}'>
									  				@if($related_vox->featured)
									  					<img class="featured-img doublecoin" src="{{ url('new-vox-img/flipping-coin.gif') }}">
									  				@endif
									  			</a>	
												<div class="vox-header clearfix">
													<div class="flex first-flex">
														<div class="col left">
															<h4 class="survey-title bold">{{ $related_vox->title }}</h4>
														</div>
													</div>
													<div class="flex first-flex">
														<div class="col right">
															<span class="bold">{{ !empty($related_vox->complex) ? 'max ' : '' }} {{ $related_vox->getRewardTotal() }} DCN</span>
															<p>{{ $related_vox->formatDuration() }}</p>
														</div>					
													</div>
													<div class="flex second-flex">
														<div class="col right">
															<div class="btns">
																<a class="opinion blue-button" href="{{ $related_vox->getLink() }}">
																	{{ trans('vox.common.take-the-test') }}
																</a>
															</div>
														</div>
													</div>
												</div>
									      	</div>
								      	</div>
								    </div>
								</div>
							</div>
						@endif
					</div>
				</div>

				@include('vox.template-parts.vox-done-parts')
			</div>

			<div class="section-recent-surveys other-surveys" id="other-surveys" style="display: none;">
				<h2>{{ trans('vox.page.questionnaire.next-survey') }}</h2>

				<div class="swiper-container">
				    <div class="swiper-wrapper">
				    	@foreach($suggested_voxes as $survey)
					      	@include('vox.template-parts.vox-swiper-slides')
				      	@endforeach
				    </div>

				    <div class="swiper-pagination"></div>
				</div>

				<div class="tac">
					<a href="{{ getLangUrl('/') }}" class="blue-button more-surveys">{{ trans('vox.page.questionnaire.next-survey-button') }}</a>
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
			url: '{{ $vox->getLink() }}',
		};

		var welcome_vox = {{ !empty($welcome_vox) ? 'true' : 'false' }};
		var welcome_vox_q_count = {{ !empty($welcome_vox) ? $welcome_vox->questions->count() : 'false' }};
		var related = {{ !empty($related_vox) ? 'true' : 'false' }};
		var testmode = {{ $testmode ? $testmode : 'false' }};
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
    	

	{!! csrf_field() !!}
	
@endsection