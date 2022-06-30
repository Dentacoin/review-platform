<div class="popup" id="submit-review-popup" scss-load="trp-popup-submit-review" js-load="submit-review">
	{!! Form::open(array('url' => getLangUrl('write-review'), 'id' => 'write-review-form', 'method' => 'post')) !!}
		{!! csrf_field() !!}

		@php
			$video_reviews_stopped = App\Models\StopVideoReview::find(1)->stopped ? true : false;
			$alreadySubmitedReview = $user->cantSubmitReviewToSameDentist($item->id);
			$questions = App\Models\Question::with('translations')->where('type', '!=', 'deprecated')->orderBy('order', 'asc')->get();
            $ratingForDentistQuestions = App\Models\Review::$ratingForDentistQuestions;
			$approvedPatientcanAskDentistForReview = $user->approvedPatientcanAskDentistForReview($item->id);
			$reviewReward = App\Models\Reward::getReward('review_trusted');
			$reviewVideoReward = App\Models\Reward::getReward('review_video_trusted');
		@endphp

		<input type="hidden" name="dentist_id" value="{{ $item->id }}"/>
		<div class="popup-wrapper">
			<a href="javascript:;" class="close-review-popup">
				<img src="{{ url('img/arrow-left-blue.png') }}"/>
			</a>
			<div class="write-review-step write-review-step-1 flex flex-center dont-close-popup">
				@if(!$alreadySubmitedReview)
					<div class="review-info-description hidden-info">
						<img src="{{ url('img-trp/write-review-info.png') }}"/>

						<h3 class="mont">Submit a review and get juicy DCN rewards!</h3>

						<div class="steps-info">
							<p><span>1</span>Write a few words or record a video</p>
							<p><span>2</span>Rate your dental experience</p>
							<p><span>3</span>Select the dental services received</p>
							<p><span>4</span>Check your overall rating and...</p>
						</div>

						<p class="earn-text">
							EARN THE STANDARD OR 2X REWARD!
						</p>

						<a href="javascript:;" class="toggle-review-info"></a>
					</div>
				@endif
				<div class="popup-inner">
					<a href="javascript:;" class="close-popup">
						<img src="{{ url('img/close-icon.png') }}"/>
					</a>

					<h2 class="mont">
						{!! nl2br(trans('trp.popup.submit-review-popup.title', [
							'name' => $item->getNames()
						])) !!}
					</h2>

					@if($alreadySubmitedReview)
						@if($approvedPatientcanAskDentistForReview === true)
							<div class="alert alert-info">
								{!! nl2br(trans('trp.popup.submit-review-popup.already-left-review')) !!}
							</div>
							<br><br>
							<div class="tac">
								<a href="{{ getLangUrl('dentist/'.$item->slug.'/ask') }}" class="blue-button ask-dentist ask-dentist-after-submit-review">
									{!! nl2br(trans('trp.popup.submit-review-popup.limit-send')) !!}
								</a>
							</div>
						@else
							<div class="alert alert-info">
								{!! nl2br(trans('trp.popup.submit-review-popup.next-month', [
									'days_count' => $approvedPatientcanAskDentistForReview
								])) !!}
							</div>
						@endif
					@else

						<div class="subtitle-box">
							<p>Tell us more about your dental visit and earn</p>
							<h2><span class="subtitle">{{ $reviewReward }} DCN</span></h2>
							<p>as a reward for your helpful feedback!</p>
						</div>

						<div class="reviews-wrapper">
							<div class="review-tabs flex-tablet">
								<label class="green-checkbox active" for="text-review">
									<div>
										<img class="active-image" src="{{ url('img-trp/review-text-active.png') }}"/>
										<img class="inactive-image" src="{{ url('img-trp/review-text.png') }}"/>
									</div>
									{!! nl2br(trans('trp.popup.submit-review-popup.text-review')) !!}
									<span>✓</span>
									<input class="checkbox review-type" id="text-review" type="radio" value="text" checked="checked">
								</label>

								<label class="green-checkbox" for="video-review">
									<div>
										<img class="active-image" src="{{ url('img-trp/review-video-active.png') }}"/>
										<img class="inactive-image" src="{{ url('img-trp/review-video.png') }}"/>
									</div>
									{!! nl2br(trans('trp.popup.submit-review-popup.video-review')) !!}
									<span>✓</span>
									<input class="checkbox review-type" id="video-review" type="radio" value="video">
									<div class="video-reward">
										2x <p>reward</p>
									</div>
								</label>
							</div>

							<div class="modern-field active review-title-wrap">
								<input 
									type="text" 
									name="title" 
									id="review-title" 
									class="modern-input" 
									autocomplete="off" 
									maxlength="50"
									readonly onfocus="this.removeAttribute('readonly');"
									placeholder="Dr Jones helped me overcome my dental fear!"
								/>
								<label for="review-title">
									<span>{!! nl2br(trans('trp.popup.submit-review-popup.title-placeholder')) !!}:</span>
								</label>
							</div>

							<div class="review-option-text review-type-content" style="">
								<div class="modern-field active alert-after">
									<textarea 
										class="modern-input" 
										id="review-answer" 
										name="answer" 
										maxlength="1500"
										placeholder="{{ trans('trp.popup.submit-review-popup.last-question-placeholder') }}"
									></textarea>
									<label for="review-answer">
										<span>Tell us more (max 1500 characters):</span>
									</label>
								</div>
							</div>

							<div class="review-video-info review-option-video review-type-content {{ $video_reviews_stopped ? 'hide-video-reviews' : '' }}" style="display: none;">
								<div>

									@if($video_reviews_stopped)
										<p class="alert alert-warning">
											Due to maintenance, video reviews are temporarily unavailable. Please, try again tomorrow or submit a text review.
										</p>
									@else
										<span class="option-span">
											{!! nl2br(trans('trp.popup.submit-review-popup.video-widget-1')) !!}
										</span>
										<span class="option-span">
											{!! nl2br(trans('trp.popup.submit-review-popup.video-widget-2')) !!}
										</span>
										<span class="option-span">
											{!! nl2br(trans('trp.popup.submit-review-popup.video-widget-3')) !!}
										</span>
										<span class="option-span">
											{!! nl2br(trans('trp.popup.submit-review-popup.video-widget-4')) !!}
										</span>
										<span class="option-span">
											{!! nl2br(trans('trp.popup.submit-review-popup.video-widget-5')) !!}
										</span>
										<span class="option-span">
											{!! nl2br(trans('trp.popup.submit-review-popup.video-widget-6')) !!}
										</span>
										<span class="option-span">
											{!! nl2br(trans('trp.popup.submit-review-popup.video-widget-7')) !!}
										</span>

										<video id="myVideo" class="video-js vjs-default-skin"></video>

										<div class="tac custom-controls" style="margin-top: 20px;">
											<div class="alert alert-warning video-alerts" style="display: none; margin-bottom:20px;" id="alert-video-error">
												{{ trans('trp.popup.submit-review-popup.video-error') }}
												{{-- An error occurred. Please try again --}}
											</div>
											<div class="alert alert-warning video-alerts" style="display: none; margin-bottom:20px;" id="alert-video-denied">
												{{ trans('trp.popup.submit-review-popup.video-denied') }}
											</div>
											<div class="alert alert-warning video-alerts" style="display: none; margin-bottom:20px;" id="alert-video-connect-camera">
												Please connect your camera.
											</div>
											<div class="alert alert-warning video-alerts" style="display: none; margin-bottom:20px;" id="alert-video-short">
												{{ trans('trp.popup.submit-review-popup.video-short') }}
											</div>

											<a href="javascript:;" id="init-video-button" class="green-button video-buttons">
												<img src="{{ url('img-trp/camera-white.svg') }}" width="30"/>
												{{ trans('trp.popup.submit-review-popup.video-allow') }}
											</a>
											
											<a href="javascript:;" id="start-video-button" class="green-button video-buttons" style="display: none;">
												<img src="{{ url('img/film-roll.svg') }}" style="margin-right: 5px;" width="15"/>
												{{ trans('trp.popup.submit-review-popup.video-start') }}
											</a>

											<a href="javascript:;" id="stop-video-button" class="green-button video-buttons" style="display: none;">
												<img src="{{ url('img/stop-video.svg') }}" style="margin-right: 5px;" width="15"/>
												{{ trans('trp.popup.submit-review-popup.video-stop') }}
											</a>
											
											<div class="video-alerts" id="video-progress-loader" style="display: none;margin-top: 20%;">
												{!! trans('trp.popup.submit-review-popup.video-processing',[
													'percent' => '<span id="video-progress-percent"></span>'
												]) !!}
											</div>
											
											<div class="alert alert-info video-alerts" id="alert-video-youtube-uploading" style="display: none;">
												{{ trans('trp.popup.submit-review-popup.video-youtube') }}
											</div>

											<div class="alert alert-success video-alerts" style="display: none;" id="alert-video-uploaded">
												{{ trans('trp.popup.submit-review-popup.video-uploaded') }}
											</div>

											<p class="agree-text">
												{!! nl2br(trans('trp.popup.submit-review-popup.video-widget-terms', [
													'link_privacy' => '<a target="_blank" href="https://dentacoin.com/privacy-policy">',
													'link_youtube' => '<a target="_blank" href="https://www.youtube.com">',
													'link_youtube_terms' => '<a target="_blank" href="https://www.youtube.com/t/terms">',
													'endlink' => '</a>',												
												])) !!}
											</p>
										</div>								
									@endif
									<input type="hidden" id="youtube_id" name="youtube_id" value="" />
								</div>
							</div>
						</div>
						
						<div class="tac review-type-content review-option-text">
							<a href="javascript:;" class="blue-button review-next-step" step="2" url="{{ getLangUrl('write-review/2') }}">
								Next: Rate your dental experience >
							</a>
						</div>
						
						@if(!$video_reviews_stopped)
							<div class="tac review-type-content review-option-video" style="display: none;">
								<a href="javascript:;" class="blue-button review-next-step submit-video-review" step="2" url="{{ getLangUrl('write-review/2') }}">
									Next: Rate your dental experience >
								</a>
							</div>
						@endif

						<div class="alert alert-warning" id="review-answer-error" style="display: none;">
							{{ trans( 'trp.popup.submit-review-popup.last-question-invalid' ) }}
						</div>

						<div class="alert alert-warning" id="review-short-text" style="display: none;">
							{{ trans('trp.popup.submit-review-popup.text-short') }}
						</div>
					@endif
				</div>
			</div>
			@if(!$alreadySubmitedReview)
				<div class="write-review-step write-review-step-2 flex flex-center dont-close-popup" style="display: none;">
					<img class="popup-image" src="{{ url('img-trp/write-review-2.png') }}"/>
					<div class="popup-inner">
						<a href="javascript:;" class="close-popup">
							<img src="{{ url('img/close-icon.png') }}"/>
						</a>

						<h2 class="mont">
							Rate your dental experience
						</h2>
						
						<div class="subtitle-box">
							<p>Now let’s go through all steps of your patient journey.</p>
							<p>How would you evaluate the following factors:</p>
						</div>

						<div class="questions-rating-wrapper {{ $item->is_dentist && !$item->is_clinic && $item->my_workplace_approved->isNotEmpty() ? 'dentist-in-clinic' : '' }}">
							@if($item->is_dentist && !$item->is_clinic && $item->my_workplace_approved->isNotEmpty())

								<div class="teams">
									<input type="hidden" name="dentist_clinics" id="dentist_clinics" value="own"/>
									
									<div class="select-team-wrapper chosen">
										<span class="select-team-chosen-label">{{ trans('trp.popup.submit-review-popup.select') }}</span>
										<p>
											{{ trans('trp.popup.submit-review-popup.dentist-cabinet', [
												'name' => $item->getNames()
											]) }}
										</p>
										<div class="caret-down"></div>

										<div class="select-team-options">
											<a class="select-team" href="javascript:;" team-id="own">
												<img src="{{ $item->getImageUrl('thumb') }}"/>
												{{ trans('trp.popup.submit-review-popup.dentist-cabinet', [
													'name' => $item->getNames()
												]) }}
											</a>
											@foreach($item->my_workplace_approved as $workplace)
												<a class="select-team" href="javascript:;" team-id="{{ $workplace->clinic->id }}">
													<img src="{{ $workplace->clinic->getImageUrl('thumb') }}"/>
													my experience with this dentist at <b>{{ $workplace->clinic->getNames() }}</b>
												</a>
											@endforeach
										</div>
									</div>
								</div>
							@endif

							@if($item->is_clinic && $item->teamApproved->isNotEmpty())
								<div class="teams">
									<input type="hidden" name="clinic_dentists" id="clinic_dentists"/>

									<div class="select-team-wrapper">
										<span class="select-team-chosen-label">Select treating dentist:</span>
										<p>Select treating dentist:</p>
										<div class="caret-down"></div>
										<span class="remove-selected-team">x</span>

										<div class="select-team-options">
											@foreach($item->teamApproved as $team)
												<a class="select-team" href="javascript:;" team-id="{{ $team->clinicTeam->id }}">
													<img src="{{ $team->clinicTeam->getImageUrl('thumb') }}"/>
													{{ $team->clinicTeam->getNames() }}
												</a>
											@endforeach
											<a class="select-team" href="javascript:;" team-id="">
												{!! nl2br(trans('trp.popup.submit-review-popup.dentist-dont-remember')) !!}
											</a>
										</div>
									</div>
								</div>
							@endif

							<div class="questions">
								@foreach($questions as $qid => $question)
									<div class="question {{ in_array($question->id, $ratingForDentistQuestions) ? 'dentist-question' : '' }}" q-id="{{ $question->id }}">
										<p>
											{{ $question->label }}
										</p>

										<div class="ratings big">
											<div class="stars">
												<div class="bar" style="width: 0%">
												</div>
												<input type="hidden" name="option[{{ $question['id'] }}]" value="" />
											</div>
										</div>
										
									</div>
								@endforeach
								<div class="alert alert-warning rating-error" style="display: none;">
									{!! nl2br(trans('trp.popup.submit-review-popup.answer-all')) !!}
								</div>
							</div>
						</div>

						<div class="submit-review-buttons">
							<a class="white-button review-prev-step" step="1"><</a>
							<a href="javascript:;" class="blue-button review-next-step" step="3" url="{{ getLangUrl('write-review/3') }}">
								Select the dental services received >
							</a>
						</div>
					</div>
				</div>
				<div class="write-review-step write-review-step-3 flex flex-center dont-close-popup" style="display: none;"> 
					<img class="popup-image" src="{{ url('img-trp/write-review-2.png') }}"/>
					<div class="popup-inner">

						<div class="write-review-loader" style="display: none;">
							<h2 class="mont">Calculating your rating for {{ $item->getNames() }}</h2>
							<video
								type="video/mp4" 
								src="{{ url('img-trp/trp-score-loading-animation.mp4') }}" 
								playsinline 
								autoplay 
								muted 
								loop
								controls=""
							>
							</video>							
						</div>

						<a href="javascript:;" class="close-popup">
							<img src="{{ url('img/close-icon.png') }}"/>
						</a>

						<h2 class="mont smaller-h2">
							@if($item->is_clinic)
								Select all dental services received at {{ $item->getNames() }}
							@else
								Select all dental services received at {{ $item->getNames() }}{{ strtolower(substr($item->getNames(), -1)) == 's' ? "’" : '’s' }} Office
							@endif
						</h2>

						<div class="treatments-wrapper">
							@foreach(config('trp.treatments') as $t => $treatment)
								@if($t == 'most_popular')
									<p class="treatment-category">
										{{ trans('trp.treatments.category.'.$t) }}
									</p>

									@foreach($treatment as $mp)
										<label class="treatment-label" for="{{ $t.'-'.$mp }}">
											<input type="checkbox" value="{{ $mp }}" id="{{ $t.'-'.$mp }}" class="treatment" treatment="{{ $mp }}" category="{{ $t }}">
											{!! App\Models\Review::handleTreatmentTooltips(trans('trp.treatments.'.$mp)) !!}
										</label>
									@endforeach
								@endif
							@endforeach

							<p class="treatment-category more-treatments-title">
								By specialty:
								{{-- {{ trans('trp.treatments.info') }}: --}}
							</p>

							@foreach(config('trp.treatments') as $t => $treatment)
								@if($t != 'most_popular')
									<span href="javascript:;" class="treatment-label more-treatments">
										{{ explode(':', trans('trp.treatments.category.'.$t))[0] }}
										<div class="caret-down"></div>
									
										<div class="treatment-options">
											@foreach($treatment as $mp)
												<label class="checkbox-label" for="{{ $t.'-'.$mp }}-{{ $loop->index }}">
													<div class="flex flex-mobile flex-center">
														<input 
															type="checkbox" 
															name="treatments[]" 
															value="{{ $mp }}" 
															id="{{ $t.'-'.$mp }}-{{ $loop->index }}" 
															class="treatment" 
															treatment="{{ $mp }}" 
															category="{{ $t }}"
															treatment-label="{!! App\Models\Review::withoutTreatmentTooltips(trans('trp.treatments.'.$mp)) !!}"
														>
														<div class="checkbox-square">✓</div>
														{!! App\Models\Review::handleTreatmentTooltips(trans('trp.treatments.'.$mp)) !!}
													</div>
												</label>
											@endforeach
										</div>
									</span>
								@endif
							@endforeach
							<a href="javascript:;" class="review-selected-treatments">Review all selected</a>

							<div class="selected-treatments-wrapper">
								<p class="treatment-category">
									Selected:
								</p>
								<div class="selected-treatments">
									<a>Check up <span>X</span></a>
									<a>Тooth cleaning <span>X</span></a>
								</div>
							</div>

							<div class="alert alert-warning" style="display: none;" id="treatment-error">
								{{-- {{ trans('trp.treatments.error') }} --}}
								Please select all dental services you have received.
							</div>
						</div>
						
						<div class="submit-review-buttons">
							<a class="white-button review-prev-step" step="2"><</a>
							<button type="submit" class="blue-button" id="review-submit-button">
								{{ trans('trp.popup.submit-review-popup.submit') }}
							</button>
						</div>
					</div>
				</div>
				<div class="write-review-step write-review-success flex flex-center dont-close-popup" style="display: none;"> 
					<img class="popup-image" src="{{ url('img-trp/popup-images/review-success.png') }}"/>
					<div class="popup-inner">
						<a href="javascript:;" class="close-popup">
							<img src="{{ url('img/close-icon.png') }}"/>
						</a>

						<h2 class="mont">
							Thank you for your honest feedback!
						</h2>
						<p class="step-info">The overall rating you have just given for <b>{{ $item->getNames() }}</b> is:</p>

						<div class="tac">
							<div class="review-rating-new">
								<span class="rating mont">
									5
								</span>
								<div class="ratings big">
									<div class="stars fixed-stars">
										<div class="bar" style="width: 100%;">
										</div>
									</div>
								</div>
					
								<div class="trusted tooltip-text">
									<img src="{{ url('img-trp/mobile-logo-white.png') }}"/>
								</div>
							</div>
						</div>

						<div class="success-bottom-section text-success" style="display: none;">
							<h2 class="mont">Congrats!</h2>
							<p class="step-info">You have just received as a reward for your contribution:</p>

							<div class="review-reward-wrapper">
								<img src="{{ url('img/dcn-logo-optimism.svg') }}"/>
								<span class="review-reward">{{ $reviewReward }} DCN</span>
							</div>

							<a target="_blank" href="https://account.dentacoin.com?platform=trusted-reviews" class="learn-link">
								Learn how to withdraw your rewards
							</a>

							<div class="tac">
								<a href="javascript:;" class="blue-button close-popup">Close</a>
							</div>
						</div>

						<div class="success-bottom-section video-success" style="display: none;">
							<h2 class="mont">Your video is being reviewed…</h2>
							<p class="step-info">Upon our approval, you will receive a double reward for your diligent contribution:</p>

							<div class="review-reward-wrapper">
								<img src="{{ url('img/dcn-logo-optimism.svg') }}"/>
								<span class="review-reward">{{ $reviewReward }} DCN</span>
							</div>

							<a target="_blank" href="https://account.dentacoin.com?platform=trusted-reviews" class="learn-link">Learn how to keep track of your rewards</a>
							
							<div class="tac">
								<a href="javascript:;" class="blue-button close-popup">Close</a>
							</div>
						</div>

						<div class="success-bottom-section verify-review" style="display: none;">
							<h2 class="mont">Get verified, get your reward!</h2>
							<p class="step-info">
								Only Trusted Reviews are rewarded with DCN. Ask your dentist to confirm that you are their patient and claim your DCN reward!
							</p>

							<div class="review-reward-wrapper">
								<img src="{{ url('img/dcn-logo-optimism.svg') }}"/>
								<span class="review-reward">{{ $reviewReward }} DCN</span>
							</div>
							
							<a href="{{ getLangUrl('dentist/'.$item->slug.'/ask/') }}" 
							original-href="{{ getLangUrl('dentist/'.$item->slug.'/ask/') }}" 
							class="blue-button ask-dentist-submit-review">
								Request verification
							</a>
						</div>
					</div>
				</div>
				<div class="write-review-step verification-review-success flex flex-center dont-close-popup" style="display: none;"> 
					<img class="popup-image" src="{{ url('img-trp/popup-images/review-success.png') }}"/>
					<div class="popup-inner">
						<a href="javascript:;" class="close-popup">
							<img src="{{ url('img/close-icon.png') }}"/>
						</a>

						<div class="tac">
							<img src="{{ url('img-trp/check.png') }}" class="check-image"/>
						</div>
						<h2 class="mont">
							Verification request sent
						</h2>
						<p class="step-info">
							You have successfully submitted your request for verification. We will let you know as soon as your dentist confirms that you are their patient.
						</p>

						<div class="tac">
							<a target="_blank" href="https://account.dentacoin.com?platform=trusted-reviews" class="learn-link">Learn how to keep track of your rewards ></a>
						</div>

						<div class="tac">
							<a href="javascript:;" class="close-popup blue-button">Close</a>
						</div>
					</div>
				</div>
			@endif
		</div>
	{!! Form::close() !!}
</div>