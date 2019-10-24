<div class="popup fixed-popup" id="submit-review-popup">
	<div class="popup-inner inner-white">
		<div class="popup-pc-buttons">
			<a href="javascript:;" class="close-popup"><i class="fas fa-times"></i></a>
		</div>

		<div class="popup-mobile-buttons">
			<a href="javascript:;" class="close-popup">< {!! nl2br(trans('trp.common.back')) !!}</a>
		</div>

		@if($item->id == $user->id)
			<div class="alert alert-info">
				{{ trans('trp.popup.submit-review-popup.self') }}
			</div>
		@elseif($user->loggedFromBadIp())
			<div class="alert alert-info">
				{!! nl2br(trans('trp.popup.submit-review-popup.bad-ip')) !!}
			</div>
		@elseif(!empty($my_review))
			<div class="alert alert-info ask-dentist-alert">
				@if($user->approvedPatientcanAskDentist($item->id))
					{!! nl2br(trans('trp.popup.submit-review-popup.already-left-review')) !!}
					<br><br>
					<a href="{{ getLangUrl('dentist/'.$item->slug).'ask' }}" class="button ask-dentist">
						{!! nl2br(trans('trp.popup.submit-review-popup.limit-send')) !!}
					</a>
				@else
					You already reached your review limit to this dentist for the period. Ask for an invitation next month.
				@endif
			</div>
		@elseif($user->is_dentist)
			<div class="alert alert-info">
				{!! nl2br(trans('trp.popup.submit-review-popup.is-dentist')) !!}
			</div>
		@elseif(!empty($user))
			<div class="dcn-review-reward" {!! $is_trusted ? '' : 'style="display: none;"' !!}>
				<img src="{{ url('img-trp/mini-logo-blue.png') }}">
				<span class="reward-info">
					DCN 
					<span id="review-reward-so-far">0</span> / 
					<span id="review-reward-total" standard="{{ $review_reward }}" video="{{ $review_reward_video }}">{{ $review_reward }}</span>
				</span>
			</div>
			
			<h2>
				{!! nl2br(trans('trp.popup.submit-review-popup.title')) !!}
			</h2>

			{!! Form::open(array('url' => getLangUrl('dentist/'.$item->slug), 'id' => 'write-review-form', 'method' => 'post')) !!}
				<div class="questions-wrapper">

					@if($item->is_dentist && !$item->is_clinic && $item->my_workplace_approved->isNotEmpty())	
						<div class="question skippable">
							<h4 class="popup-title">
								{!! nl2br(trans('trp.popup.submit-review-popup.dentist-visit', ['name' => $item->getName() ])) !!}
							</h4>
							<div class="review-answers">
								<div class="clearfix subquestion">
								   <select name="dentist_clinics" class="input" id="dentist_clinics">
										<option value="" disabled selected>Please select</option>
										<option value="">{{ trans('trp.popup.submit-review-popup.dentist-cabinet') }}</option>
										@foreach($item->my_workplace_approved as $workplace)
											<option value="{{ $workplace->clinic->id }}">{{ $workplace->clinic->getName() }}</option>
										@endforeach
									</select>
						        </div>
						    </div>
						</div>
					@endif

					@foreach($questions as $qid => $question)
						@if($item->is_clinic && $item->teamApproved->isNotEmpty() && $item->teamApproved->count() && $loop->iteration == 4 )
							<div class="question skippable">
								<h4 class="popup-title">
									{{ trans('trp.popup.submit-review-popup.dentist-treat') }}
								</h4>
								<div class="review-answers">
									<div class="clearfix subquestion">
							            <select name="clinic_dentists" class="input" id="clinic_dentists">
											<option value="">
												{!! nl2br(trans('trp.popup.submit-review-popup.dentist-dont-remember')) !!}
											</option>
											@foreach($item->teamApproved as $team)
												<option value="{{ $team->clinicTeam->id }}">{{ $team->clinicTeam->getName() }}</option>
											@endforeach
										</select>
							        </div>
							    </div>
							</div>
						@endif


						<div class="question {{ $item->is_clinic && $item->teamApproved->isNotEmpty() && $item->teamApproved->count() > 1 && $loop->iteration == 4 ? 'hidden-review-question' : '' }}" {{ $item->is_clinic && $item->teamApproved->isNotEmpty() && $item->teamApproved->count() > 1 && $loop->iteration == 4 ? 'style=display:none;' : '' }}>
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
								{!! nl2br(trans('trp.popup.submit-review-popup.answer-all')) !!}
							</div>
						</div>
					@endforeach

					<div class="question question-treatments">
						<h4 class="popup-title">
							{{ trans('trp.treatments.question') }}
						</h4>
						<div class="review-answers">
							<div class="treatment-wrapper">
								@foreach(config('trp.treatments') as $t => $treatment)
									@if($t == 'most_popular')
										<p class="treatment-category">
											• {{ trans('trp.treatments.category.'.$t) }}
										</p>

										@foreach($treatment as $mp)
											<label for="{{ $t.'-'.$mp }}">
												<input type="checkbox" value="{{ $mp }}" id="{{ $t.'-'.$mp }}" class="treatment" treatment="{{ $mp }}" category="{{ $t }}">
												{!! App\Models\Review::handleTreatmentTooltips(trans('trp.treatments.'.$mp)) !!}
												<span class="close-treatment">х</span>
											</label>
										@endforeach
									@endif
								@endforeach

								<p class="treatment-category more-treatments-title">• If you can’t find your treatment, you can search from the list with all types of dental treatment: </p>
								<a href="javascript:;" class="more-treatments"><i class="fas fa-plus"></i> All treatments</a>

								<div class="treatments-hidden">
									@foreach(config('trp.treatments') as $t => $treatment)
										@if($t != 'most_popular')
											<p class="treatment-category">
												• {{ trans('trp.treatments.category.'.$t) }}
											</p>

											@foreach($treatment as $mp)
												<label for="{{ $t.'-'.$mp }}">
													<input type="checkbox" name="treatments[]" value="{{ $mp }}" id="{{ $t.'-'.$mp }}" class="treatment" treatment="{{ $mp }}" category="{{ $t }}">
													{!! App\Models\Review::handleTreatmentTooltips(trans('trp.treatments.'.$mp)) !!}
													<span class="close-treatment">х</span>
												</label>
											@endforeach
										@endif
									@endforeach
								</div>
							</div>
							<div class="alert alert-warning" style="display: none;" id="treatment-error">
								{{ trans('trp.treatments.error') }}
							</div>
					    </div>
					</div>

					<div class="question">

						<h4 class="popup-title">
							<span class="blue">
								{!! nl2br(trans('trp.popup.submit-review-popup.last-question')) !!}
							</span>
							{!! nl2br(trans('trp.popup.submit-review-popup.last-question-text', ['name' => $item->getName()])) !!}
						</h4>
						

						<div class="reviews-wrapper">

							<div class="review-tabs flex-tablet">
								<a class="active" href="javascript:;" data-type="text">
									{!! nl2br(trans('trp.popup.submit-review-popup.text-review')) !!}
									
								</a>
								<span>or</span>
								<a class="video-button" href="javascript:;" data-type="video">
									{!! nl2br(trans('trp.popup.submit-review-popup.video-review')) !!}
									<div class="video-dcn"  {!! $is_trusted ? '' : 'style="display: none;"' !!}>
										+{{ $review_reward_video - $review_reward }} DCN
									</div>
								</a>
							</div>

							<div class="review-box">

								<input type="text" class="input" id="review-title" name="title" value="{{ $my_review ? $my_review->title : '' }}" placeholder="{!! nl2br(trans('trp.popup.submit-review-popup.title-placeholder')) !!}">

								<div id="review-option-text" class="review-type-content" style="">
									{{ Form::textarea( 'answer', $my_review ? $my_review->answer : '', array( 'id' => 'review-answer', 'class' => 'input tooltip-text fixed-tooltip', 'placeholder' => trans('trp.popup.submit-review-popup.last-question-placeholder'), 'text' => nl2br(trans('trp.popup.submit-review-popup.last-question-tooltip')) )) }}
								</div>

								<div id="review-option-video" class="review-type-content" style="display: none;">
									@if($my_review && $my_review->youtube_id)
										<div class="alert alert-info">
											{!! nl2br(trans('trp.popup.submit-review-popup.video-already-shot')) !!}
										</div>
										<div class="videoWrapper">
											<iframe width="560" height="315" src="https://www.youtube.com/embed/{{ $my_review->youtube_id }}" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
										</div>
									@else
										<p>
											{!! nl2br(trans('trp.popup.submit-review-popup.video-widget-title')) !!}
											
										</p>
										<span class="option-span">
											<b>01</b>
											{!! nl2br(trans('trp.popup.submit-review-popup.video-widget-1')) !!}
											
										</span>
										<span class="option-span">
											<b>02</b>
											{!! nl2br(trans('trp.popup.submit-review-popup.video-widget-2')) !!}
											
										</span>
										<span class="option-span">
											<b>03</b>
											{!! nl2br(trans('trp.popup.submit-review-popup.video-widget-3')) !!}
											
										</span>
										<span class="option-span">
											<b>04</b>
											{!! nl2br(trans('trp.popup.submit-review-popup.video-widget-4')) !!}
											
										</span>
										<span class="option-span">
											<b>05</b>
											{!! nl2br(trans('trp.popup.submit-review-popup.video-widget-5')) !!}
											
										</span>

										<label class="checkbox-label" for="video-agree">
											<input type="checkbox" class="special-checkbox" id="video-agree" name="video-agree" value="video-agree">
											<i class="far fa-square"></i>
											{!! nl2br(trans('trp.popup.submit-review-popup.video-widget-terms', [
												'link' => '<a class="read-privacy" target="_blank" href="https://dentacoin.com/privacy-policy">',
												'endlink' => '</a>',												
											])) !!}
											
										</label>

										<div class="alert alert-warning" style="display: none;" id="video-not-agree">
											{!! nl2br(trans('trp.popup.submit-review-popup.video-agree')) !!}
										</div>

										<video id="myVideo" class="video-js vjs-default-skin"></video>

										<div class="tac custom-controls" style="margin-top: 20px;">
											<div class="alert alert-warning" style="display: none;" id="video-error">
												{{ trans('trp.popup.submit-review-popup.video-error') }}
											</div>
											<div class="alert alert-warning" style="display: none;" id="video-denied">
												{{ trans('trp.popup.submit-review-popup.video-denied') }}
											</div>
											<div class="alert alert-warning" style="display: none;" id="video-short">
												{{ trans('trp.popup.submit-review-popup.video-short') }}
											</div>


											<a href="javascript:;" id="init-video" class="button">
												<i class="fas fa-video" style="color: white; margin-right: 5px;"></i>
												{{ trans('trp.popup.submit-review-popup.video-allow') }}
											</a>
											
											<a href="javascript:;" id="start-video" class="button" style="display: none;">
												<i class="fas fa-film"></i>
												{{ trans('trp.popup.submit-review-popup.video-start') }}
											</a>

											<a href="javascript:;" id="stop-video" class="button" style="display: none;">
												<i class="fas fa-stop-circle"></i>
												{{ trans('trp.popup.submit-review-popup.video-stop') }}
											</a>
											
											<div id="video-progress" style="display: none;">
												{!! trans('trp.popup.submit-review-popup.video-processing',[
													'percent' => '<span id="video-progress-percent"></span>'
												]) !!}
											</div>
											
											<div id="video-youtube" style="display: none;">
												{{ trans('trp.popup.submit-review-popup.video-youtube') }}
											</div>
											
											<div class="alert alert-success" style="display: none;" id="video-uploaded">
												{{ trans('trp.popup.submit-review-popup.video-uploaded') }}
											</div>
										</div>
									@endif
									<input type="hidden" id="youtube_id" name="youtube_id" value="{{ $my_review ? $my_review->youtube_id : '' }}" />

								</div>
							</div>

						</div>
						
						<div class="tac">
							<button type="submit" class="button"  id="review-submit-button" data-loading="{{ trans('trp.popup.submit-review-popup.loading') }}" >
								{{ trans('trp.popup.submit-review-popup.submit') }}
							</button>
						</div>


						<div class="alert alert-warning" id="review-answer-error" style="display: none;">
							{{ trans( 'trp.popup.submit-review-popup.last-question-invalid' ) }}
						</div>

						<div class="alert alert-warning" id="review-error" style="display: none;">
							{!! nl2br(trans('trp.popup.submit-review-popup.answer-all')) !!}
						</div>
						<div class="alert alert-warning" id="review-short-text" style="display: none;">
							{{ trans('trp.popup.submit-review-popup.text-short') }}
						</div>

		                <div class="alert alert-warning" id="review-crypto-error" style="display: none;">
		                	{{ trans('trp.popup.submit-review-popup.crypto-error') }}
			            	<span class="error-info" style="display: block; margin: 10px 0px;">
			            	</span>
		                </div>
			            <div class="alert alert-info" id="review-confirmed" style="display: none;">
			            	@if($is_trusted)
				            	{!! trans('trp.popup.submit-review-popup.done',[
				            		'link' => '<a href="'.getLangUrl('profile').'">',
				            		'endlink' => '</a>',
				            	]) !!}
				            	<br/>
				            	<br/>
				            	<a class="button" href="{{ getLangUrl('dentist/'.$item->slug) }}">
				            		{{ trans('trp.popup.submit-review-popup.my-review') }}
				            	</a>
				            @else
				            	{{ trans('trp.popup.submit-review-popup.done-non-trusted') }}
				            	<br/>
				            	<br/>
				            	<a class="button ask-review-button" data-popup-logged="popup-ask-dentist">
				            		{{ trans('trp.popup.submit-review-popup.done-non-trusted-invite') }}
				            	</a>
				            @endif
			            </div>

					</div>


		        </div>
			{!! Form::close() !!}
		@endif

	</div>
</div>