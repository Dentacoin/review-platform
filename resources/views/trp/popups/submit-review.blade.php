<div class="popup fixed-popup" id="submit-review-popup">
	<div class="popup-inner inner-white">
		<div class="popup-pc-buttons">
			<a href="javascript:;" class="close-popup"><i class="fas fa-times"></i></a>
		</div>

		<div class="popup-mobile-buttons">
			<a href="javascript:;" class="close-popup">< back</a>
		</div>



		@if($item->id == $user->id)
			<div class="alert alert-info">
				{{ trans('front.page.dentist.review-form-hint-self') }}
			</div>
		@elseif(!$user->civic_id)
			<h2>Submit Your Review</h2>
			<div class="question">
				<h4 class="popup-title">
					Please confirm your identity
				</h4>
				<div class="review-answers">
					<p>
						In order to ensure all reviews on our website are real you'll need to confirm your identity via Civic. We use Civic - a Blockchain-based identity platform that guarantees us that a person can have only one account on our platform. Please start by downloading the Civic app on your smartphone using the links below and add an email address or phone number to your Civic account. Then click the "Login with Civic" button below and use the app to scan the QR code. 
						<br/>
						<br/>
					</p>
					<p>
						1. Download and install Civic 
						<br/>
						<br/>
					</p>
                	<p>
                		<a href="https://play.google.com/store/apps/details?id=com.civic.sip" target="_blank" class="civic-download civic-android"></a>
                		<a href="https://itunes.apple.com/us/app/civic-secure-identity/id1141956958?mt=8" target="_blank" class="civic-download civic-ios"></a>
						<br/>
						<br/>
                	</p>
					<p>
						2. Click the button below and scan the QR code. Please be patient, the validation procedure may take up to 3 minutes. 
						<br/>
						<br/>
					</p>

					<button id="signupButton" class="civic-button-a medium" type="button" scope="BASIC_SIGNUP">
						<span style="color: white;">{!! nl2br(trans('vox.page.profile.home.civic-button')) !!}</span>
					</button>

					<div id="civic-cancelled" class="alert alert-info" style="display: none;">
						{!! nl2br(trans('vox.page.profile.home.civic-cancelled')) !!}
					</div>
					<div id="civic-error" class="alert alert-warning" style="display: none;">
						{!! nl2br(trans('vox.page.profile.home.civic-error')) !!}
					</div>
					<div id="civic-weak" class="alert alert-warning" style="display: none;">
						{!! nl2br(trans('vox.page.profile.home.civic-weak')) !!}
					</div>
					<div id="civic-wait" class="alert alert-info" style="display: none;">
						{!! nl2br(trans('vox.page.profile.home.civic-wait')) !!}
					</div>
					<div id="civic-duplicate" class="alert alert-warning" style="display: none;">
						{!! nl2br(trans('vox.page.profile.home.civic-duplicate')) !!}
					</div>
					<input type="hidden" id="jwtAddress" value="{!! getLangUrl('profile/jwt') !!}" />

				</div>
			</div>

		@elseif($dentist_limit_reached)
			<div class="alert alert-info">
				@if($has_asked_dentist)
					@if($has_asked_dentist->status=='no')
						{{ trans('front.page.'.$current_page.'.write-review-limit-dentists-denied', ['name' => $item->getName()]) }}
					@else
						{{ trans('front.page.'.$current_page.'.write-review-limit-dentists-waiting', ['name' => $item->getName()]) }}
					@endif
				@else
					{{ trans('front.page.'.$current_page.'.write-review-limit-dentists', ['name' => $item->getName()]) }}
					<br/>
					<br/>
					<a href="{{ $item->getLink().'/ask' }}" class="button ask-dentist">
						SEND REQUEST
					</a>
				@endif
			</div>
			<div class="alert alert-success ask-success" style="display: none;">
				Your request was sent. We'll let you know as soon as {{ $item->getName() }} approves it
			</div>
		@elseif($user->loggedFromBadIp())
			<div class="alert alert-info">
				{!! trans('front.page.'.$current_page.'.write-review-bad-ip') !!}
			</div>
		@elseif($review_limit_reached)
			<div class="alert alert-info">
				{{ trans('front.page.'.$current_page.'.write-review-limit-'.$review_limit_reached) }}
			</div>
		@elseif(!empty($my_review))
			<div class="alert alert-info">
				You've already left a review
			</div>
		@elseif($user->is_dentist)
			<div class="alert alert-info">
				Dentists can't write reviews
			</div>
		@elseif(!empty($user))
			<div class="dcn-review-reward">
				<img src="{{ url('img-trp/mini-logo-blue.png') }}">
				<span class="reward-info">
					DCN 
					<span id="review-reward-so-far">0</span> / 
					<span id="review-reward-total" standard="{{ $review_reward }}" video="{{ $review_reward_video }}">{{ $review_reward }}</span>
				</span>
			</div>
			
			<h2>Submit Your Review</h2>

			{!! Form::open(array('url' => $item->getLink(), 'id' => 'write-review-form', 'method' => 'post')) !!}
				<div class="questions-wrapper">

					@if($item->is_dentist && !$item->is_clinic && $item->my_workplace_approved->isNotEmpty())	
						<div class="question skippable">
							<h4 class="popup-title">
								{{ trans('front.page.dentist.dentist-visit', ['name' => $item->getName() ]) }}
							</h4>
							<div class="review-answers">
								<div class="clearfix subquestion">
								   <select name="dentist_clinics" class="input">
										<option value="">{{ trans('front.page.dentist.dentist-cabinet') }}</option>
										@foreach($item->my_workplace_approved as $workplace)
											<option value="{{ $workplace->clinic->id }}">{{ $workplace->clinic->getName() }}</option>
										@endforeach
									</select>
						        </div>
						    </div>
						</div>
					@endif

					@foreach($questions as $qid => $question)
						@if($item->is_clinic && $item->teamApproved->isNotEmpty() && $loop->iteration == 4 )
							<div class="question skippable">
								<h4 class="popup-title">
									{{ trans('front.page.dentist.dentist-treat') }}
								</h4>
								<div class="review-answers">
									<div class="clearfix subquestion">
							            <select name="clinic_dentists" class="input" id="clinic_dentists">
											<option value="">{{ trans('front.page.dentist.dentist-not-remembered') }}</option>
											@foreach($item->teamApproved as $team)
												<option value="{{ $team->clinicTeam->id }}">{{ $team->clinicTeam->getName() }}</option>
											@endforeach
										</select>
							        </div>
							    </div>
							</div>
						@endif


						<div class="question {{ $item->is_clinic && $item->team->isNotEmpty() && $item->team->count() > 1 && $loop->iteration == 4 ? 'hidden-review-question' : '' }}" {{ $item->is_clinic && $item->team->isNotEmpty() && $item->team->count() > 1 && $loop->iteration == 4 ? 'style=display:none;' : '' }}>
							<h4 class="popup-title">
								{{ str_replace('{name}', $item->name, $question->question) }}
							</h4>
					
						    <div class="review-answers">
					    	@foreach(json_decode($question['options'], true) as $i => $option)
								<div class="clearfix subquestion">
									<div class="answer">
										{{ $option[0] }}
									</div>
									<div class="answer">
										<div class="ratings average tac">
											<div class="stars">
												<div class="bar" style="width: {{ $my_review ? json_decode($my_review->answers[$qid]->options, true)[$i]*5/100 : 0 }};%">
												</div>
												<input type="hidden" name="option[{{ $question['id'] }}][]" value="{{ $my_review ? json_decode($my_review->answers[$qid]->options, true)[$i] : '' }}" />
											</div>
										</div>
									</div>
									<div class="answer tar">
										{{ $option[1] }}
									</div>
								</div>
					    	@endforeach
							</div>
							<div class="rating-error" style="display: none;">
								{{ trans('front.page.dentist.review-form-answer-all') }}
							</div>
						</div>
					@endforeach

					<div class="question">

						<h4 class="popup-title">
							<span class="blue">{{ trans('front.page.dentist.review-form-last') }}:</span>
							{{ trans('front.page.dentist.review-form-last-question', ['name' => $item->getName()]) }}
						</h4>
						

						<div class="reviews-wrapper">

							<div class="review-tabs flex-tablet">
								<a class="active" href="javascript:;" data-type="text">
									Text review
								</a>
								<span>or</span>
								<a class="video-button" href="javascript:;" data-type="video">
									Video review

									<div class="video-dcn">
										+{{ $review_reward_video - $review_reward }} DCN
									</div>
								</a>
							</div>

							<div class="review-box">

								<input type="text" class="input" id="review-title" name="title" value="{{ $my_review ? $my_review->title : '' }}" placeholder="Short title (limit: 100 characters) *">

								<div id="review-option-text" class="review-type-content" style="">
									{{ Form::textarea( 'answer', $my_review ? $my_review->answer : '', array( 'id' => 'review-answer', 'class' => 'input', 'placeholder' => trans( 'front.page.dentist.review-form-last-question-placeholder' ) )) }}
								</div>

								<div id="review-option-video" class="review-type-content" style="display: none;">
									@if($my_review && $my_review->youtube_id)
										<div class="alert alert-info">
											{{ trans('front.page.dentist.review-form-video-already-shot') }}
										</div>
										<div class="videoWrapper">
											<iframe width="560" height="315" src="https://www.youtube.com/embed/{{ $my_review->youtube_id }}" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
										</div>
									@else
										<p>
											Use the widget below to record your video review. Here are few things to have in mind:
										</p>
										<span class="option-span"><b>01</b>Your video should be at least 15 seconds long</span>
										<span class="option-span"><b>02</b>The video will get uploaded to our YouTube channel</span>
										<span class="option-span"><b>03</b>We'll approve the video before making it visible to everyone</span>
										<span class="option-span"><b>04</b>You'll receive your reward only if your video gets approved</span>
										<span class="option-span"><b>05</b>If you are experiencing any technical issues (especially on smartphones), please try recording your video review on your laptop or personal computer.</span>

										<label class="checkbox-label" for="video-agree">
											<input type="checkbox" class="special-checkbox" id="video-agree" name="video-agree" value="video-agree">
											<i class="far fa-square"></i>
											By submitting your video review you agree that after approval it can be uploaded on reviews.dentacoin.com and youtube.com. You also agree to our <a class="read-privacy" target="_blank" href="https://dentacoin.com/privacy-policy">Privacy Policy</a>
										</label>

										<div class="alert alert-warning" style="display: none;" id="video-not-agree">
											{{ trans('front.page.dentist.review-form-video-not-agree') }}
										</div>

										<video id="myVideo" class="video-js vjs-default-skin"></video>

										<div class="tac custom-controls" style="margin-top: 20px;">
											<div class="alert alert-warning" style="display: none;" id="video-error">
												{{ trans('front.page.dentist.review-form-video-error') }}
											</div>
											<div class="alert alert-warning" style="display: none;" id="video-denied">
												{{ trans('front.page.dentist.review-form-video-denied') }}
											</div>
											<div class="alert alert-warning" style="display: none;" id="video-short">
												{{ trans('front.page.dentist.review-form-video-short') }}
											</div>


											<a href="javascript:;" id="init-video" class="button">
												<i class="fas fa-video" style="color: white; margin-right: 5px;"></i>
												{{ trans('front.page.dentist.review-form-video-allow') }}
											</a>
											
											<a href="javascript:;" id="start-video" class="button" style="display: none;">
												<i class="fas fa-film"></i>
												{{ trans('front.page.dentist.review-form-video-start') }}
											</a>

											<a href="javascript:;" id="stop-video" class="button" style="display: none;">
												<i class="fas fa-stop-circle"></i>
												{{ trans('front.page.dentist.review-form-video-stop') }}
											</a>
											
											<div id="video-progress" style="display: none;">
												{!! trans('front.page.dentist.review-form-video-processing',[
													'percent' => '<span id="video-progress-percent"></span>'
												]) !!}
											</div>
											
											<div id="video-youtube" style="display: none;">
												{{ trans('front.page.dentist.review-form-video-youtube') }}
											</div>
											
											<div class="alert alert-success" style="display: none;" id="video-uploaded">
												{{ trans('front.page.dentist.review-form-video-uploaded') }}
											</div>
										</div>
									@endif
									<input type="hidden" id="youtube_id" name="youtube_id" value="{{ $my_review ? $my_review->youtube_id : '' }}" />

								</div>
							</div>

						</div>

					</div>

					<div class="tac" style="display: none;">
						<button type="submit" class="button"  id="review-submit-button" data-loading="{{ trans('front.common.loading') }}" >
							{{ trans('front.page.dentist.review-form-submit') }}
						</button>
					</div>


					<div class="alert alert-warning" id="review-answer-error" style="display: none;">
						{{ trans( 'front.page.dentist.review-form-last-question-invalid' ) }}
					</div>

					<div class="alert alert-warning" id="review-error" style="display: none;">
						{{ trans('front.page.dentist.review-form-answer-all') }}
					</div>
					<div class="alert alert-warning" id="review-short-text" style="display: none;">
						{{ trans('front.page.dentist.review-form-text-short') }}
					</div>

	                <div class="alert alert-warning" id="review-crypto-error" style="display: none;">
	                	{{ trans('front.page.dentist.review-form-crypto-error') }}
		            	<span class="error-info" style="display: block; margin: 10px 0px;">
		            	</span>
	                </div>
		            <div class="alert alert-info" id="review-pending" style="display: none;">
		            	{{ trans('front.page.dentist.review-form-pending') }}
		            	<a href="{{ $item->getLink() }}" style="display: block; margin: 10px 0px;">
		            		{{ trans('front.page.dentist.review-form-my-review') }}
		            	</a>
		            </div>
		            <div class="alert alert-info" id="review-confirmed" style="display: none;">
		            	{{ trans('front.page.dentist.review-form-done') }}
		            	<a href="{{ $item->getLink() }}" style="display: block; margin: 10px 0px;">
		            		{{ trans('front.page.dentist.review-form-my-review') }}
		            	</a>
		            	<a class="etherscan-link" target="_blank" href="" style="display: block; margin: 10px 0px;">
		            		{{ trans('front.page.dentist.review-form-etherscan') }}
		            	</a>
		            </div>

		        </div>
			{!! Form::close() !!}
		@endif

	</div>
</div>