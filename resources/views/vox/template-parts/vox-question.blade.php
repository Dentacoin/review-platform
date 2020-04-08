@if(!empty($details_question_id))
	<div class="question-group question-group-details question-group-{{ $details_question_id }} single-choice user-detail-question" data-id="{{ $details_question_id }}" demogr-id="{{ $details_question_id }}" custom-type="{{ $details_question_id }}" style="display: none;">
		<div class="loader-survey"><img src="{{ url('new-vox-img/survey-loader.gif') }}"></div>
		<div class="question">
			{!! nl2br($details_question['label']) !!}
		</div>
		<div class="answers">
			@if(count($details_question['values'])>5)
				<select name="{{ $details_question_id }}" class="form-control">
					<option value="">-</option>
					@foreach($details_question['values'] as $answer_id => $answer)
						<option value="{{ $answer_id }}" demogr-index="{{ $loop->iteration }}">{{ $answer }}</option>
					@endforeach
				</select>
			@else
				@foreach($details_question['values'] as $answer_id => $answer)
					<a class="answer answer" data-num="{{ $answer_id }}" for="answer-{{ $details_question_id }}-{{ $answer_id }}">
						<input id="answer-{{ $details_question_id }}-{{ $answer_id }}" type="radio" name="answer" class="answer" value="{{ $answer_id }}"  demogr-index="{{ $loop->iteration }}" style="display: none;">
						{{ $answer }}											
					</a>
				@endforeach
			@endif
		</div>

		@if(count($details_question['values'])>4)
			<a href="javascript:;" class="next-answer">{!! trans('vox.page.'.$current_page.'.next') !!}</a>
		@endif
	</div>
@elseif($question->type == 'multiple_choice')
	<div class="question-group question-group-{{ $question->id }} multiple-choice {!! empty($question->dont_randomize_answers) ? 'shuffle' : ''  !!}" {!! isset($answered[$question->id]) ? 'data-answer="'.( is_array( $answered[$question->id] ) ? implode(',', $answered[$question->id]) : $answered[$question->id] ).'"' : '' !!} data-id="{{ $question->id }}" {!! $question->id==$first_question ? '' : 'style="display: none;"' !!} {!! $question->question_trigger ? "data-trigger='$question->question_trigger'" : "" !!}  trigger-type="{{ $question->trigger_type }}" welcome="{!! $question->vox_id == 11 ? '1' : '' !!}">
		<div class="loader-survey"><img src="{{ url('new-vox-img/survey-loader.gif') }}"></div>
		<div class="question">
			{!! nl2br($question->questionWithTooltips()) !!}
		</div>
		<div class="answers">
			@foreach( $question->vox_scale_id && !empty($scales[$question->vox_scale_id]) ? explode(',', $scales[$question->vox_scale_id]->answers) :  json_decode($question->answers, true) as $k => $answer)
				<div class="checkbox {!! mb_substr($answer, 0, 1)=='!' || mb_substr($answer, 0, 1)=='#' ? ' disabler-label' : '' !!}">
					<label class="answer-checkbox no-mobile-tooltips {{ !empty($question->hasAnswerTooltip($answer, $question)) ? 'tooltip-text' : '' }}" for="answer-{{ $question->id }}-{{ $loop->index+1 }}" {!! !empty($question->hasAnswerTooltip($answer, $question)) ? 'text="'.$question->hasAnswerTooltip($answer, $question).'"' : '' !!}>
						<i class="far fa-square"></i>
						<input id="answer-{{ $question->id }}-{{ $loop->index+1 }}" type="checkbox" name="answer" class="answer{!! mb_substr($answer, 0, 1)=='!' ? ' disabler' : '' !!} input-checkbox" value="{{ $loop->index+1 }}">

						{!! nl2br(App\Models\VoxQuestion::handleAnswerTooltip( mb_substr($answer, 0, 1)=='!' || mb_substr($answer, 0, 1)=='#' ? mb_substr($answer, 1) : $answer))  !!}

						@if(!empty($question->hasAnswerTooltip($answer, $question)))
							<div class="answer-mobile-tooltip tooltip-text" text="{!! $question->hasAnswerTooltip($answer, $question) !!}"><i class="fas fa-question-circle"></i>
							</div>
						@endif
					</label>
				</div>
			@endforeach
		</div>

		<a href="javascript:;" class="next-answer">{!! trans('vox.page.'.$current_page.'.next') !!}</a>
	</div>
@elseif($question->type == 'scale')
	<div class="question-group question-group-{{ $question->id }} scale" data-id="{{ $question->id }}" {!! isset($answered[$question->id]) ? 'data-answer="'.( is_array( $answered[$question->id] ) ? implode(',', $answered[$question->id]) : $answered[$question->id] ).'"' : '' !!} {!! $question->id==$first_question ? '' : 'style="display: none;"' !!} {!! $question->question_trigger ? 'data-trigger="'.$question->question_trigger.'"' : "" !!} trigger-type="{{ $question->trigger_type }}" welcome="{!! $question->vox_id == 11 ? '1' : '' !!}">
		<div class="loader-survey"><img src="{{ url('new-vox-img/survey-loader.gif') }}"></div>
		<div class="question">
			{!! nl2br($question->questionWithTooltips()) !!}
		</div>
		<div class="answers">

			<div class="answers-inner">

				<div class="flickity">
					@foreach(json_decode($question->answers, true) as $k => $answer)
						<div class="answer-radios-group clearfix">
							<div class="answer-question">
								<h3 class="{{ !empty(json_decode($question->answers_tooltips, true)[$k]) ? 'tooltip-text' : '' }}" {!! !empty(json_decode($question->answers_tooltips, true)[$k]) ? 'text="'.json_decode($question->answers_tooltips, true)[$k].'"' : '' !!}>{!!  nl2br( App\Models\VoxQuestion::handleAnswerTooltip($answer)) !!}
								</h3>
							</div>
							<div class="buttons-list clearfix"> 
								@foreach( explode(',', $scales[$question->vox_scale_id]->answers) as $ans)
									<div class="tac answer-inner" style="width: {{ 100 / count(explode(',', $scales[$question->vox_scale_id]->answers)) }}%;">
										<label class="answer-radio" for="answer-{{ $question->id }}-{{ $loop->index+1 }}-{{ $k }}">
											<input id="answer-{{ $question->id }}-{{ $loop->index+1 }}-{{ $k }}" type="radio" name="answer-{{ $k }}" class="answer" value="{{ $loop->index+1 }}" style="display: none;">
											{{ $ans }}											
										</label>
									</div>
								@endforeach
							</div> 
						</div>
					@endforeach
				</div>
			</div>
		</div>

		<a href="javascript:;" class="next-answer">{!! trans('vox.page.'.$current_page.'.next') !!}</a>
	</div>
@elseif(array_key_exists($question->id, $cross_checks) && $question->cross_check == 'birthyear')
	<div class="question-group question-group-{{ $question->id }} birthyear-question {{ $question->is_control == -1 ? 'shuffle' : '' }}" data-answer="{!! $user->birthyear !!}" data-id="{{ $question->id }}" {!! $question->id==$first_question ? '' : 'style="display: none;"' !!} {!! $question->question_trigger ? "data-trigger='$question->question_trigger'" : "" !!}  trigger-type="{{ $question->trigger_type }}" {!! array_key_exists($question->id, $cross_checks) ? 'cross-check-correct="'.$cross_checks[$question->id].'" cross-check-id="'.$cross_checks_references[$question->id].'"' : '' !!} welcome="{!! $question->vox_id == 11 ? '1' : '' !!}">
		<div class="loader-survey"><img src="{{ url('new-vox-img/survey-loader.gif') }}"></div>

		<div class="question">
			{!! nl2br($question->questionWithTooltips()) !!}
		</div>
		<div class="answers">
			<select class="answer" name="birthyear-answer" id="birthyear-answer">
        		<option value="">-</option>
				@for($i=(date('Y')-18);$i>=(date('Y')-90);$i--)
        			<option value="{{ $i }}">{{ $i }}</option>
        		@endfor
        	</select>
		</div>

		<a href="javascript:;" class="next-answer">{!! trans('vox.page.'.$current_page.'.next') !!}</a>
	</div>
@else
	<div class="question-group question-group-{{ $question->id }} single-choice {{ $question->is_control == -1 || (empty($question->dont_randomize_answers) && empty($question->vox_scale_id) && empty($scales[$question->vox_scale_id])) ? 'shuffle' : '' }}" {!! isset($answered[$question->id]) ? 'data-answer="'.$answered[$question->id].'"' : '' !!} data-id="{{ $question->id }}" {!! $question->id==$first_question ? '' : 'style="display: none;"' !!} {!! $question->question_trigger ? "data-trigger='$question->question_trigger'" : "" !!}  trigger-type="{{ $question->trigger_type }}" {!! array_key_exists($question->id, $cross_checks) ? 'cross-check-correct="'.$cross_checks[$question->id].'" cross-check-id="'.$cross_checks_references[$question->id].'"' : '' !!} welcome="{!! $question->vox_id == 11 ? '1' : '' !!}">
		<div class="loader-survey"><img src="{{ url('new-vox-img/survey-loader.gif') }}"></div>
		<div class="question">
			{!! nl2br($question->questionWithTooltips()) !!}
		</div>
		<div class="answers">
			@foreach($question->vox_scale_id && !empty($scales[$question->vox_scale_id]) ? explode(',', $scales[$question->vox_scale_id]->answers) :  json_decode($question->answers, true) as $key => $answer)
				<a class="answer answer no-mobile-tooltips {!! mb_substr($answer, 0, 1)=='#' ? ' disabler-label' : '' !!}" data-num="{{ $loop->index+1 }}" for="answer-{{ $question->id }}-{{ $loop->index+1 }}"  {!! !empty($question->hasAnswerTooltip($answer, $question)) ? 'text="'.$question->hasAnswerTooltip($answer, $question).'"' : '' !!}>
					<input id="answer-{{ $question->id }}-{{ $loop->index+1 }}" type="radio" name="answer" class="answer" value="{{ $loop->index+1 }}" style="display: none;">
					{!! nl2br( App\Models\VoxQuestion::handleAnswerTooltip(mb_substr($answer, 0, 1)=='#' ? mb_substr($answer, 1) : $answer)) !!}

					@if(!empty($question->hasAnswerTooltip($answer, $question)))
						<div class="answer-mobile-tooltip tooltip-text" text="{!! $question->hasAnswerTooltip($answer, $question) !!}"><i class="fas fa-question-circle"></i>
						</div>
					@endif
				</a>
			@endforeach
		</div>
	</div>
@endif