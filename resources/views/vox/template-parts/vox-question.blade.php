@if($question->type == 'multiple_choice')
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