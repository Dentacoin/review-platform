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
					<label class="answer answer" data-num="{{ $answer_id }}" for="answer-{{ $details_question_id }}-{{ $answer_id }}">
						<input id="answer-{{ $details_question_id }}-{{ $answer_id }}" type="radio" name="answer" class="answer" value="{{ $answer_id }}"  demogr-index="{{ $loop->iteration }}" style="display: none;">
						{{ $answer }}											
					</label>
				@endforeach
			@endif
		</div>

		@if(count($details_question['values'])>4)
			<a href="javascript:;" class="next-answer">{!! trans('vox.page.'.$current_page.'.next') !!}</a>
		@endif
	</div>
@elseif($question->type == 'multiple_choice')
	<div class="
		question-group question-group-{{ $question->id }} multiple-choice 
		{!! empty($question->dont_randomize_answers) ? 'shuffle' : ''  !!}
	" 

	{!! isset($answered[$question->id]) ? 'data-answer="'.( is_array( $answered[$question->id] ) ? implode(',', $answered[$question->id]) : $answered[$question->id] ).'"' : '' !!} 
	data-id="{{ $question->id }}" 
	{!! $question->id==$first_question ? '' : 'style="display: none;"' !!} 
	{!! $question->question_trigger ? "data-trigger='$question->question_trigger'" : "" !!}  
	trigger-type="{{ $question->trigger_type }}" 
	welcome="{!! $question->vox_id == 11 ? '1' : '' !!}">

		<div class="loader-survey"><img src="{{ url('new-vox-img/survey-loader.gif') }}"></div>

		<div class="question {{ !empty($question->imageOnlyInQuestion()) || !empty($question->imageInTooltipAndQuestion()) ? 'question-with-image' : '' }}" {!! !empty($question->imageOnlyInTooltip()) || !empty($question->imageInTooltipAndQuestion()) ? 'tooltip-image="'.$question->getImageUrl(false).'"' : ''  !!}>
			@if(!empty($question->imageOnlyInQuestion()) || !empty($question->imageInTooltipAndQuestion()))
				<a class="question-image" data-lightbox="{{ $question->id }}" href="{{ $question->getImageUrl(false) }}">
					<img class="q-img" src="{{ $question->getImageUrl(true) }}" style="max-width: 100%;">
					<img class="zoom-img" src="{{ url('new-vox-img/zoom-in-icon2.svg') }}"/>
				</a>
				<div class="q-text"> 
			@endif

			{!! nl2br($question->questionWithTooltips()) !!}

			@if(!empty($question->imageOnlyInQuestion()) || !empty($question->imageInTooltipAndQuestion()))
				</div>
			@endif
		</div>
		<div class="answers {!! !$question->allAnswersHaveImages() && count($question->vox_scale_id && !empty($scales[$question->vox_scale_id]) ? explode(',', $scales[$question->vox_scale_id]->answers) :  json_decode($question->answers, true)) >= 8 ? 'in-columns' : '' !!} {{ $question->allAnswersHaveImages() ? 'question-pictures' : '' }}">
			@if(!$question->allAnswersHaveImages() && count($question->vox_scale_id && !empty($scales[$question->vox_scale_id]) ? explode(',', $scales[$question->vox_scale_id]->answers) :  json_decode($question->answers, true)) >= 8)
				<div class="answers-column"> 
			@endif
			@foreach( $question->vox_scale_id && !empty($scales[$question->vox_scale_id]) ? explode(',', $scales[$question->vox_scale_id]->answers) :  json_decode($question->answers, true) as $k => $answer)
				<div class="checkbox {!! mb_substr($answer, 0, 1)=='!' || mb_substr($answer, 0, 1)=='#' ? ' disabler-label' : '' !!}">
					<label class="answer-checkbox no-mobile-tooltips {{ !empty($question->hasAnswerTooltip($answer, $question)) ? 'tooltip-text' : '' }}" for="answer-{{ $question->id }}-{{ $loop->index+1 }}" {!! !empty($question->hasAnswerTooltip($answer, $question)) ? 'text="'.$question->hasAnswerTooltip($answer, $question).'"' : '' !!}
					{!! !$question->allAnswersHaveImages() && $question->hasAnswerTooltip($answer, $question) && !empty($question->getAnswerImageUrl(false, $k)) ? 'tooltip-image="'.$question->getAnswerImageUrl(false, $k).'"' : '' !!}>
						<i class="far fa-square"></i>
						<input id="answer-{{ $question->id }}-{{ $loop->index+1 }}" type="checkbox" name="answer" class="answer{!! mb_substr($answer, 0, 1)=='!' ? ' disabler' : '' !!} input-checkbox" value="{{ $loop->index+1 }}">

						@if($question->allAnswersHaveImages() && !empty($question->getAnswerImageUrl(false, $k)))
							<div class="answer-image" style="background-image: url({{ $question->getAnswerImageUrl(true, $k ) }})">
								<img class="img-unchecked" src="{{ url('new-vox-img/non-selected-img-answer-icon.svg') }}">
								<img class="img-checked" src="{{ url('new-vox-img/selected-img-answer-icon.svg') }}"/>
								<a class="zoom-answer" data-lightbox="an-{{ $question->id }}-{{ $k }}" href="{{ $question->getAnswerImageUrl(false, $k ) }}">
									<img src="{{ url('new-vox-img/zoom-in-icon2.svg') }}"/>
								</a>
							</div>

						@endif
						{!! nl2br(App\Models\VoxQuestion::handleAnswerTooltip( mb_substr($answer, 0, 1)=='!' || mb_substr($answer, 0, 1)=='#' ? mb_substr($answer, 1) : $answer))  !!}

						@if(!empty($question->hasAnswerTooltip($answer, $question)))
							<div class="answer-mobile-tooltip tooltip-text" text="{!! $question->hasAnswerTooltip($answer, $question) !!}"><i class="fas fa-question-circle"></i>
							</div>
						@endif
					</label>
					{!! !$question->allAnswersHaveImages() && $question->hasAnswerTooltip($answer, $question) && !empty($question->getAnswerImageUrl(false, $k)) ? '<img src="'.$question->getAnswerImageUrl(false, $k).'" style="display: none !important;" />' : '' !!}
				</div>
				@if(!$question->allAnswersHaveImages() && count($question->vox_scale_id && !empty($scales[$question->vox_scale_id]) ? explode(',', $scales[$question->vox_scale_id]->answers) :  json_decode($question->answers, true)) >= 8 && round(count($question->vox_scale_id && !empty($scales[$question->vox_scale_id]) ? explode(',', $scales[$question->vox_scale_id]->answers) :  json_decode($question->answers, true)) / 2) == $loop->iteration )
					</div> 
					<div class="answers-column"> 
				@endif
			@endforeach

			@if( !$question->allAnswersHaveImages() && count($question->vox_scale_id && !empty($scales[$question->vox_scale_id]) ? explode(',', $scales[$question->vox_scale_id]->answers) :  json_decode($question->answers, true)) >= 8)
				</div> 
			@endif
		</div>

		<a href="javascript:;" class="next-answer">{!! trans('vox.page.'.$current_page.'.next') !!}</a>
	</div>
@elseif($question->type == 'scale')
	<div class="question-group question-group-{{ $question->id }} scale"

	data-id="{{ $question->id }}" 
	{!! isset($answered[$question->id]) ? 'data-answer="'.( is_array( $answered[$question->id] ) ? implode(',', $answered[$question->id]) : $answered[$question->id] ).'"' : '' !!} 
	{!! $question->id==$first_question ? '' : 'style="display: none;"' !!} 
	{!! $question->question_trigger ? 'data-trigger="'.$question->question_trigger.'"' : "" !!} 
	trigger-type="{{ $question->trigger_type }}" 
	welcome="{!! $question->vox_id == 11 ? '1' : '' !!}">

		<div class="loader-survey"><img src="{{ url('new-vox-img/survey-loader.gif') }}"></div>
		<div class="question {{ !empty($question->imageOnlyInQuestion()) || !empty($question->imageInTooltipAndQuestion()) ? 'question-with-image' : '' }}" {!! !empty($question->imageOnlyInTooltip()) || !empty($question->imageInTooltipAndQuestion()) ? 'tooltip-image="'.$question->getImageUrl(false).'"' : ''  !!}>
			@if(!empty($question->imageOnlyInQuestion()) || !empty($question->imageInTooltipAndQuestion()))
				<a class="question-image" data-lightbox="{{ $question->id }}" href="{{ $question->getImageUrl(false) }}">
					<img class="q-img" src="{{ $question->getImageUrl(true) }}" style="max-width: 100%;">
					<img class="zoom-img" src="{{ url('new-vox-img/zoom-in-icon2.svg') }}"/>
				</a>
				<div class="q-text"> 
			@endif

			{!! nl2br($question->questionWithTooltips()) !!}

			@if(!empty($question->imageOnlyInQuestion()) || !empty($question->imageInTooltipAndQuestion()))
				</div>
			@endif
		</div>
		<div class="answers">
			<div class="answers-inner">
				<div class="flickity">
					
					@foreach(json_decode($question->answers, true) as $k => $answer)
						<div class="answer-radios-group clearfix">
							<div class="answer-question">
								<h3 {!! !$question->allAnswersHaveImages() && $question->hasAnswerTooltip($answer, $question) && !empty($question->getAnswerImageUrl(false, $k)) ? 'tooltip-image="'.$question->getAnswerImageUrl(false, $k ).'"' : '' !!}>{!!  nl2br( App\Models\VoxQuestion::handleAnswerTooltip(mb_substr($answer, 0, 1)=='#' ? mb_substr($answer, 1) : $answer)) !!}
								</h3>
								{!! !$question->allAnswersHaveImages() && $question->hasAnswerTooltip($answer, $question) && !empty($question->getAnswerImageUrl(false, $k)) ? '<img src="'.$question->getAnswerImageUrl(false, $k).'" style="display: none !important;" />' : '' !!}
							</div>
							<div class="buttons-list clearfix {!! count(explode(',', $scales[$question->vox_scale_id]->answers)) >= 8 ? 'in-columns' : '' !!}"> 
								@if(count(explode(',', $scales[$question->vox_scale_id]->answers)) >= 8)
									<div class="answers-column"> 
								@endif
								@foreach( explode(',', $scales[$question->vox_scale_id]->answers) as $ans)
									<div class="tac answer-inner" style="width: {{ 100 / count(explode(',', $scales[$question->vox_scale_id]->answers)) }}%;">
										<label class="answer-radio" for="answer-{{ $question->id }}-{{ $loop->index+1 }}-{{ $k }}">
											<input id="answer-{{ $question->id }}-{{ $loop->index+1 }}-{{ $k }}" type="radio" name="answer-{{ $k }}" class="answer" value="{{ $loop->index+1 }}" style="display: none;">
											{{ $ans }}											
										</label>
									</div>
									@if(count(explode(',', $scales[$question->vox_scale_id]->answers)) >= 8 && round(count(explode(',', $scales[$question->vox_scale_id]->answers)) / 2) == $loop->iteration )
										</div> 
										<div class="answers-column"> 
									@endif
								@endforeach
								@if(count(explode(',', $scales[$question->vox_scale_id]->answers)) >= 8)
									</div> 
								@endif
							</div> 
						</div>
					@endforeach

				</div>
			</div>
		</div>

		<a href="javascript:;" class="next-answer">{!! trans('vox.page.'.$current_page.'.next') !!}</a>
	</div>
@elseif(array_key_exists($question->id, $cross_checks) && $question->cross_check == 'birthyear')
	<div class="
		question-group question-group-{{ $question->id }} birthyear-question 
		{{ $question->is_control == -1 ? 'shuffle' : '' }} 
	" 

	data-answer="{!! $user->birthyear !!}" 
	data-id="{{ $question->id }}" 
	{!! $question->id==$first_question ? '' : 'style="display: none;"' !!} 
	{!! $question->question_trigger ? "data-trigger='$question->question_trigger'" : "" !!}  
	trigger-type="{{ $question->trigger_type }}" 
	{!! array_key_exists($question->id, $cross_checks) ? 'cross-check-correct="'.$cross_checks[$question->id].'" cross-check-id="'.$cross_checks_references[$question->id].'"' : '' !!} 
	welcome="{!! $question->vox_id == 11 ? '1' : '' !!}">

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
@elseif($question->type == 'number')
	<div class="question-group question-group-{{ $question->id }} number" 

	{!! isset($answered[$question->id]) ? 'data-answer="'.$answered[$question->id].'"' : '' !!} 
	data-id="{{ $question->id }}" 
	{!! $question->id==$first_question ? '' : 'style="display: none;"' !!} 
	{!! $question->question_trigger ? "data-trigger='$question->question_trigger'" : "" !!}  
	trigger-type="{{ $question->trigger_type }}" 
	{!! array_key_exists($question->id, $cross_checks) ? 'cross-check-correct="'.$cross_checks[$question->id].'" cross-check-id="'.$cross_checks_references[$question->id].'"' : '' !!} 
	welcome="{!! $question->vox_id == 11 ? '1' : '' !!}">

		<div class="loader-survey"><img src="{{ url('new-vox-img/survey-loader.gif') }}"></div>

		<div class="question {{ !empty($question->imageOnlyInQuestion()) || !empty($question->imageInTooltipAndQuestion()) ? 'question-with-image' : '' }}" {!! !empty($question->imageOnlyInTooltip()) || !empty($question->imageInTooltipAndQuestion()) ? 'tooltip-image="'.$question->getImageUrl(false).'"' : ''  !!}>
			@if(!empty($question->imageOnlyInQuestion()) || !empty($question->imageInTooltipAndQuestion()))
				<a class="question-image" data-lightbox="{{ $question->id }}" href="{{ $question->getImageUrl(false) }}">
					<img class="q-img" src="{{ $question->getImageUrl(true) }}" style="max-width: 100%;">
					<img class="zoom-img" src="{{ url('new-vox-img/zoom-in-icon2.svg') }}"/>
				</a>
				<div class="q-text"> 
			@endif

			{!! nl2br($question->questionWithTooltips()) !!}

			@if(!empty($question->imageOnlyInQuestion()) || !empty($question->imageInTooltipAndQuestion()))
				</div>
			@endif
		</div>
		<div class="answers">
			<input type="number" name="answer-number" class="answer-number" min="{{ explode(':',$question->number_limit)[0] }}" max="{{ explode(':',$question->number_limit)[1] }}">
		</div>

		<a href="javascript:;" class="next-answer">{!! trans('vox.page.'.$current_page.'.next') !!}</a>

		<div style="display: none; margin-top: 10px;text-align: center;" class="answer-number-error alert alert-warning">
			{!! trans('vox.page.questionnaire.answer-number-error', ['min' => explode(':',$question->number_limit)[0], 'max' => explode(':',$question->number_limit)[1] ]) !!}
		</div>
	</div>
@elseif($question->type == 'rank')
	<div class="question-group question-group-{{ $question->id }} rank" 

	{!! isset($answered[$question->id]) ? 'data-answer="'.( is_array( $answered[$question->id] ) ? implode(',', $answered[$question->id]) : $answered[$question->id] ).'"' : '' !!} 
	data-id="{{ $question->id }}" 
	{!! $question->id==$first_question ? '' : 'style="display: none;"' !!} 
	{!! $question->question_trigger ? "data-trigger='$question->question_trigger'" : "" !!}  
	trigger-type="{{ $question->trigger_type }}" 
	{!! array_key_exists($question->id, $cross_checks) ? 'cross-check-correct="'.$cross_checks[$question->id].'" cross-check-id="'.$cross_checks_references[$question->id].'"' : '' !!} 
	welcome="{!! $question->vox_id == 11 ? '1' : '' !!}">
	
		<div class="loader-survey"><img src="{{ url('new-vox-img/survey-loader.gif') }}"></div>
		<div class="question {{ !empty($question->imageOnlyInQuestion()) || !empty($question->imageInTooltipAndQuestion()) ? 'question-with-image' : '' }}" {!! !empty($question->imageOnlyInTooltip()) || !empty($question->imageInTooltipAndQuestion()) ? 'tooltip-image="'.$question->getImageUrl(false).'"' : ''  !!}>
			@if(!empty($question->imageOnlyInQuestion()) || !empty($question->imageInTooltipAndQuestion()))
				<a class="question-image" data-lightbox="{{ $question->id }}" href="{{ $question->getImageUrl(false) }}">
					<img class="q-img" src="{{ $question->getImageUrl(true) }}" style="max-width: 100%;">
					<img class="zoom-img" src="{{ url('new-vox-img/zoom-in-icon2.svg') }}"/>
				</a>
				<div class="q-text"> 
			@endif

			{!! nl2br($question->questionWithTooltips()) !!}
			<p>{!! !empty($question->rank_explanation) ? $question->rank_explanation : trans('vox.page.questionnaire.rank-info') !!}</p>
			@if(!empty($question->imageOnlyInQuestion()) || !empty($question->imageInTooltipAndQuestion()))
				</div>
			@endif
			
		</div>
		<div class="answers answers-draggable">
			@foreach($question->vox_scale_id && !empty($scales[$question->vox_scale_id]) ? explode(',', $scales[$question->vox_scale_id]->answers) :  json_decode($question->answers, true) as $key => $answer)
				<label class="answer-rank no-mobile-tooltips" data-num="{{ $loop->iteration }}" rank-order="{{ $loop->iteration }}" for="answer-{{ $question->id }}-{{ $loop->index+1 }}"  {!! !empty($question->hasAnswerTooltip($answer, $question)) ? 'text="'.$question->hasAnswerTooltip($answer, $question).'"' : '' !!} 
				{!! !$question->allAnswersHaveImages() && $question->hasAnswerTooltip($answer, $question) && !empty($question->getAnswerImageUrl(false, $key)) ? 'tooltip-image="'.$question->getAnswerImageUrl(false, $key ).'"' : '' !!}>
					<input id="answer-{{ $question->id }}-{{ $loop->index+1 }}" type="radio" name="answer" class="answer" value="{{ $loop->index+1 }}" style="display: none;">
					<img src="{{ url('new-vox-img/sortable-squares.png') }}">
					<select name="rank-order" class="rank-order">
						<option value=""></option>
						@for($i=1;$i<=count(json_decode($question->answers, true));$i++)
							<option value="{{ $i }}">{{ $i }}</option>
						@endfor
					</select>
					<div class="rank-answer"> 

						{!! App\Models\VoxQuestion::handleAnswerTooltip(mb_substr($answer, 0, 1)=='#' ? mb_substr($answer, 1) : $answer) !!}
					</div>

					@if(!empty($question->hasAnswerTooltip($answer, $question)))
						<div class="answer-mobile-tooltip tooltip-text" text="{!! $question->hasAnswerTooltip($answer, $question) !!}"><i class="fas fa-question-circle"></i>
						</div>
					@endif
					{!! !$question->allAnswersHaveImages() && $question->hasAnswerTooltip($answer, $question) && !empty($question->getAnswerImageUrl(false, $key)) ? '<img src="'.$question->getAnswerImageUrl(false, $key).'" style="display: none !important;" />' : '' !!}
				</label>
			@endforeach
		</div>

		<a href="javascript:;" class="next-answer">{!! trans('vox.page.'.$current_page.'.next') !!}</a>

		<div style="display: none; margin-top: 10px;text-align: center;" class="answer-rank-error alert alert-warning">
			Please rank all answers.
		</div>
	</div>
@else
	<div class="
		question-group question-group-{{ $question->id }} single-choice 
		{{ $question->is_control == -1 || (empty($question->dont_randomize_answers) && empty($question->vox_scale_id) && empty($scales[$question->vox_scale_id])) ? 'shuffle' : '' }} 
	" 

	{!! isset($answered[$question->id]) ? 'data-answer="'.$answered[$question->id].'"' : '' !!} 
	data-id="{{ $question->id }}" 
	{!! $question->id==$first_question ? '' : 'style="display: none;"' !!} 
	{!! $question->question_trigger ? "data-trigger='$question->question_trigger'" : "" !!}  
	trigger-type="{{ $question->trigger_type }}" 
	{!! array_key_exists($question->id, $cross_checks) ? 'cross-check-correct="'.$cross_checks[$question->id].'" cross-check-id="'.$cross_checks_references[$question->id].'"' : '' !!} 
	welcome="{!! $question->vox_id == 11 ? '1' : '' !!}">
	
		<div class="loader-survey"><img src="{{ url('new-vox-img/survey-loader.gif') }}"></div>
		<div class="question {{ !empty($question->imageOnlyInQuestion()) || !empty($question->imageInTooltipAndQuestion()) ? 'question-with-image' : '' }}" {!! !empty($question->imageOnlyInTooltip()) || !empty($question->imageInTooltipAndQuestion()) ? 'tooltip-image="'.$question->getImageUrl(false).'"' : ''  !!}>
			@if(!empty($question->imageOnlyInQuestion()) || !empty($question->imageInTooltipAndQuestion()))
				<a class="question-image" data-lightbox="{{ $question->id }}" href="{{ $question->getImageUrl(false) }}">
					<img class="q-img" src="{{ $question->getImageUrl(true) }}" style="max-width: 100%;">
					<img class="zoom-img" src="{{ url('new-vox-img/zoom-in-icon2.svg') }}"/>
				</a>
				<div class="q-text"> 
			@endif

			{!! nl2br($question->questionWithTooltips()) !!}

			@if(!empty($question->imageOnlyInQuestion()) || !empty($question->imageInTooltipAndQuestion()))
				</div>
			@endif
		</div>
		<div class="answers {{ $question->allAnswersHaveImages() ? 'question-pictures' : '' }}">
			@foreach($question->vox_scale_id && !empty($scales[$question->vox_scale_id]) ? explode(',', $scales[$question->vox_scale_id]->answers) :  json_decode($question->answers, true) as $key => $answer)
				<label class="answer answer no-mobile-tooltips {!! mb_substr($answer, 0, 1)=='#' ? ' disabler-label' : '' !!}" data-num="{{ $loop->index+1 }}" for="answer-{{ $question->id }}-{{ $loop->index+1 }}"  {!! !empty($question->hasAnswerTooltip($answer, $question)) ? 'text="'.$question->hasAnswerTooltip($answer, $question).'"' : '' !!} 
				{!! !$question->allAnswersHaveImages() && $question->hasAnswerTooltip($answer, $question) && !empty($question->getAnswerImageUrl(false, $key)) ? 'tooltip-image="'.$question->getAnswerImageUrl(false, $key ).'"' : '' !!}>
					<input id="answer-{{ $question->id }}-{{ $loop->index+1 }}" type="radio" name="answer" class="answer" value="{{ $loop->index+1 }}" style="display: none;">

					@if($question->allAnswersHaveImages() && !empty($question->getAnswerImageUrl(false, $key)))
						<div class="answer-image" style="background-image: url({{ $question->getAnswerImageUrl(true, $key) }})">

							<a class="zoom-answer" data-lightbox="an-{{ $question->id }}-{{ $key }}" href="{{ $question->getAnswerImageUrl(false, $key) }}">
								<img src="{{ url('new-vox-img/zoom-in-icon2.svg') }}"/>
							</a>
						</div>

					@endif

					{!! App\Models\VoxQuestion::handleAnswerTooltip(mb_substr($answer, 0, 1)=='#' ? mb_substr($answer, 1) : $answer) !!}

					@if(!empty($question->hasAnswerTooltip($answer, $question)))
						<div class="answer-mobile-tooltip tooltip-text" text="{!! $question->hasAnswerTooltip($answer, $question) !!}"><i class="fas fa-question-circle"></i>
						</div>
					@endif
					{!! !$question->allAnswersHaveImages() && $question->hasAnswerTooltip($answer, $question) && !empty($question->getAnswerImageUrl(false, $key)) ? '<img src="'.$question->getAnswerImageUrl(false, $key).'" style="display: none !important;" />' : '' !!}
				</label>
			@endforeach
		</div>
	</div>
@endif