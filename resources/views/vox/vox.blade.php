@extends('vox')

@section('content')

		<div class="container page-questions">
			@if($vox->type!='user_details')
				<a href="{{ getLangUrl('/') }}" class="questions-back"><i class="fa fa-arrow-left"></i> {{ trans('vox.common.questionnaires') }}</a>
			@endif

			<div id="question-meta" style="{{ $vox->type=='user_details' ? 'margin-top: 40px;' : '' }}">

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
						@if($vox->type!='user_details')
							<div class="col-md-6 tar">
								<span class="bold">
										<span id="dcn-test-reward-before">
											{!! trans('vox.common.dcn_to_be_collected') !!}: {{ $vox->getRewardTotal() }}
										</span>
										<span id="dcn-test-reward-after" style="display: none;">
											{!! trans('vox.common.dcn_collected') !!}:
											<span id="current-question-reward">
												
											</span>
										</span>
								</span>
							</div>
						@endif
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
						@if($vox->type=='user_details' && $loop->iteration == 3)
							<div class="question-group location-question question-group-{{ $loop->iteration }}" data-id="{{ $question->id }}" style="display: none;">
								<div class="question">
									{!! nl2br($question->question) !!}
								</div>
								<div class="answers">
									{{ Form::select( 'country_id' , ['' => '-'] + \App\Models\Country::get()->pluck('name', 'id')->toArray() , $user->country_id , array('class' => 'form-control country-select') ) }}
                                    {{ Form::select( 'city_id' , $user->country_id ? \App\Models\City::where('country_id', $user->country_id)->get()->pluck('name', 'id')->toArray() : ['' => trans('vox.common.select-country')] , $user->city_id , array('class' => 'form-control city-select') ) }}
								</div>

								<a href="javascript:;" class="next-answer">{!! trans('vox.page.'.$current_page.'.next') !!}</a>
							</div>
						@elseif($question->type == 'multiple_choice')
							<div class="question-group question-group-{{ $question->id }} multiple-choice" {!! isset($answered[$question->id]) ? 'data-answer="'.( is_array( $answered[$question->id] ) ? implode(',', $answered[$question->id]) : $answered[$question->id] ).'"' : '' !!} data-id="{{ $question->id }}" {!! $question->id==$first_question ? '' : 'style="display: none;"' !!} {!! $question->question_trigger ? "data-trigger='$question->question_trigger'" : "" !!}>
								<div class="question">
									{!! nl2br($question->question) !!}
								</div>
								<div class="answers">
									@foreach( $question->vox_scale_id && !empty($scales[$question->vox_scale_id]) ? explode(',', $scales[$question->vox_scale_id]->answers) :  json_decode($question->answers, true) as $answer)
										<label class="answer-checkbox" for="answer-{{ $question->id }}-{{ $loop->index+1 }}">
											<input id="answer-{{ $question->id }}-{{ $loop->index+1 }}" type="checkbox" name="answer" class="answer{!! mb_substr($answer, 0, 1)=='!' ? ' disabler' : '' !!}" value="{{ $loop->index+1 }}">
											{{ mb_substr($answer, 0, 1)=='!' ? mb_substr($answer, 1) : $answer }}											
										</label>
									@endforeach
								</div>

								<a href="javascript:;" class="next-answer">{!! trans('vox.page.'.$current_page.'.next') !!}</a>
							</div>
						@elseif($question->type == 'scale')
							<div class="question-group question-group-{{ $question->id }} scale" data-id="{{ $question->id }}" {!! $question->id==$first_question ? '' : 'style="display: none;"' !!} {!! $question->question_trigger ? 'data-trigger="'.$question->question_trigger.'"' : "" !!}>
								<div class="question">
									{!! nl2br($question->question) !!}
								</div>
								<div class="answers">

									<div class="answers-inner">

										<div class="clearfix mobile-hide">
											<div class="answer-title" style="width: 20%;">
												&nbsp;
											</div>
											@foreach( explode(',', $scales[$question->vox_scale_id]->answers) as $ans)											
												<div class="answer-title" style="width: {{ (100 - 20) / count(explode(',', $scales[$question->vox_scale_id]->answers)) }}%;">
													<span>{{ $ans }}</span>
												</div>
											@endforeach
										</div>

										<div class="flickity">
											@foreach(json_decode($question->answers, true) as $k => $answer)
												<div class="answer-radios-group clearfix">
													<div class="answer-question">
														<h3>{{ $answer }}</h3>
													</div>
													@foreach( explode(',', $scales[$question->vox_scale_id]->answers) as $ans)
														<div class="tac answer-inner" style="width: {{ (100 - 20) / count(explode(',', $scales[$question->vox_scale_id]->answers)) }}%;">
															<label class="answer-radio" for="answer-{{ $question->id }}-{{ $loop->index+1 }}-{{ $k }}">
																<input id="answer-{{ $question->id }}-{{ $loop->index+1 }}-{{ $k }}" type="radio" name="answer-{{ $k }}" class="answer" value="{{ $loop->index+1 }}" style="display: none;">
																{{ $ans }}											
															</label>
														</div>
													@endforeach
												</div>
											@endforeach
										</div>
									</div>
								</div>

								<a href="javascript:;" class="next-answer">{!! trans('vox.page.'.$current_page.'.next') !!}</a>
							</div>
						@else
							<div class="question-group question-group-{{ $question->id }} single-choice {{ $question->is_control == -1 ? 'shuffle' : '' }}" {!! isset($answered[$question->id]) ? 'data-answer="'.$answered[$question->id].'"' : '' !!} data-id="{{ $question->id }}" {!! $question->id==$first_question ? '' : 'style="display: none;"' !!} {!! $question->question_trigger ? "data-trigger='$question->question_trigger'" : "" !!}>
								<div class="question">
									{!! nl2br($question->question) !!}
								</div>
								<div class="answers">
									@foreach($question->vox_scale_id && !empty($scales[$question->vox_scale_id]) ? explode(',', $scales[$question->vox_scale_id]->answers) :  json_decode($question->answers, true) as $answer)
										<a class="answer answer-checkbox" data-num="{{ $loop->index+1 }}" for="answer-{{ $question->id }}-{{ $loop->index+1 }}">
											<input id="answer-{{ $question->id }}-{{ $loop->index+1 }}" type="radio" name="answer" class="answer" value="{{ $loop->index+1 }}" style="display: none;">
											{{ $answer }}											
										</a>
									@endforeach
								</div>
							</div>
						@endif
					@endforeach
<!-- 
					<div class="question-hints" style="{{ $vox->type=='user_details' ? 'display: none;' : '' }}">
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
					@if($vox->type!='user_details')
						<p class="popup-second-title bold">
							{{ trans('vox.page.'.$current_page.'.just-won') }}
						</p>
						<div class="price">
							<img src="img-vox/dc-logo.png"/>
							<span class="coins">{{ $vox->getRewardTotal() }} DCN</span>
						</div>
					@endif
				</div>

				@if($vox->type=='user_details')
					<div class="dentacoin-info alert alert-info" style="display: none;">
						<p class="buttons-description">
							{{ trans('vox.page.'.$current_page.'.dentacoin-info') }}							
						</p>
						<a href="https://dentacoin.com/" style="margin-bottom: 10px;" target="_blank" class="button-questionnaries">{{ trans('vox.page.'.$current_page.'.learn-more') }}</a>
					</div>

					<p class="buttons-description">
						{{ trans('vox.page.'.$current_page.'.try-another-user-details') }}
					</p>
					<a href="{{ getLangUrl('/') }}" class="button-questionnaries">{{ trans('vox.common.questionnaires') }}</a>
				@else
					<p class="buttons-description">
						{{ trans('vox.page.'.$current_page.'.try-another') }}
					</p>
					<a href="{{ getLangUrl('/') }}" class="button-questionnaries">{{ trans('vox.common.questionnaires') }}</a>
				
				@endif

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

		</div>


		<script type="text/javascript">
			var vox = {
				count: {{ $vox->questions->count() }},
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