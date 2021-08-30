@if(!empty($birthyear_q))
	<div class="question-group birthyear-question tac user-detail-question" demogr-id="age_groups">
		<div class="question">
			{!! trans('vox.page.questionnaire.question-birth') !!}
		</div>
		<div class="answers">
			<select class="answer" name="birthyear-answer" id="birthyear-answer">
        		<option value="">-</option>
				{!! App\Models\Vox::getBirthyearOptions() !!}
        	</select>
		</div>

		<a href="javascript:;" class="next-answer">{!! trans('vox.page.questionnaire.next') !!}</a>
	</div>
@elseif(!empty($gender_q))
	<div class="question-group gender-question single-choice user-detail-question" demogr-id="gender">
		<div class="question">
			{!! trans('vox.page.questionnaire.question-sex') !!}
		</div>
		<div class="answers">
			<label class="answer answer" for="answer-gender-m" data-num="m">
				<input id="answer-gender-m" type="radio" demogr-index="1" name="gender-answer" class="answer" value="m" style="display: none;">
				{!! trans('vox.page.questionnaire.question-sex-m') !!}
			</label>
			<label class="answer answer" for="answer-gender-f" data-num="f">
				<input id="answer-gender-f" type="radio" demogr-index="2" name="gender-answer" class="answer" value="f" style="display: none;">
				{!! trans('vox.page.questionnaire.question-sex-f') !!}
			</label>
		</div>
	</div>
@elseif(!empty($country_id_q))
	<div class="question-group location-question user-detail-question">
		<div class="question">
			{!! trans('vox.page.questionnaire.question-country') !!}
		</div>
		<div class="answers">
			<div class="alert alert-warning ip-country mobile" style="display: none;">
        		{!! trans('vox.common.different-ip') !!}
            </div>
			{{ Form::select( 'country_id' , ['' => '-'] + \App\Models\Country::with('translations')->get()->pluck('name', 'id')->toArray() , $user->country_id , array('class' => 'country-select form-control country-dropdown', 'real-country' => !empty($country_id) ? $country_id : '') ) }}
		</div>

		<a href="javascript:;" class="next-answer">{!! trans('vox.page.questionnaire.next') !!}</a>
	</div>
@elseif(!empty($details_question_id))
	<div class="question-group question-group-details question-group-{{ $details_question_id }} single-choice user-detail-question" data-id="{{ $details_question_id }}" demogr-id="{{ $details_question_id }}" custom-type="{{ $details_question_id }}">

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
			<a href="javascript:;" class="next-answer">{!! trans('vox.page.questionnaire.next') !!}</a>
		@endif
	</div>
@elseif($question->type == 'multiple_choice')
	<div class="question-group question-group-{{ $question->id }} multiple-choice {!! empty($question->dont_randomize_answers) ? 'shuffle' : ''  !!}"  
	data-id="{{ $question->id }}"
	welcome="{!! $question->vox_id == 11 ? '1' : '' !!}">

		@php($imageQuestion = !empty($question->imageOnlyInQuestion()) || !empty($question->imageInTooltipAndQuestion()))

		<div class="question {{ $imageQuestion ? 'question-with-image' : '' }}" {!! !empty($question->imageOnlyInTooltip()) || !empty($question->imageInTooltipAndQuestion()) ? 'tooltip-image="'.$question->getImageUrl(false).'"' : ''  !!}>
			@if($imageQuestion)
				<a class="question-image" data-lightbox="{{ $question->id }}" href="{{ $question->getImageUrl(false) }}">
					<img class="q-img" src="{{ $question->getImageUrl(true) }}" style="max-width: 100%;">
					<img class="zoom-img" src="{{ url('new-vox-img/zoom-in-icon2.svg') }}"/>
				</a>
				<div class="q-text"> 
			@endif

			{!! nl2br($question->questionWithTooltips()) !!}

			@if($imageQuestion)
				</div>
			@endif
		</div>

		@php($questionAnswers = isset($question_answers_reordered) ? $question_answers_reordered : ($question->vox_scale_id && !empty($scales[$question->vox_scale_id]) ? explode(',', $scales[$question->vox_scale_id]->answers) : json_decode($question->answers, true)))

		@php($isInColumns = (!$question->allAnswersHaveImages() && count($questionAnswers) >= 8))

		<div class="answers {!! $isInColumns ? 'in-columns' : '' !!} {{ $question->allAnswersHaveImages() ? 'question-pictures' : '' }}">

			@if($isInColumns)
				<div class="answers-column"> 
			@endif

			@foreach( $questionAnswers as $k => $answer)
				@if(empty($answers_shown) || (!empty($answers_shown) && in_array($loop->iteration, $answers_shown)))
					<div class="checkbox {!! mb_substr($answer, 0, 1)=='!' || mb_substr($answer, 0, 1)=='#' ? 'disabler-label' : '' !!}">

						@php($answerTooltip = $question->hasAnswerTooltip($answer, $question))
						@php($answerImage = $question->getAnswerImageUrl(false, $k))

						<label 
							class="answer-checkbox no-mobile-tooltips {{ !empty($answerTooltip) ? 'tooltip-text' : '' }} {!! $excluded_answers && isset($excluded_answers[$k+1]) ? 'excluded-answer' : '' !!}" 
							for="answer-{{ $question->id }}-{{ $loop->index+1 }}" {!! !empty($answerTooltip) ? 'text="'.$answerTooltip.'"' : '' !!}
							{!! !$question->allAnswersHaveImages() && $answerTooltip && !empty($answerImage) ? 'tooltip-image="'.$answerImage.'"' : '' !!}
							{!! $excluded_answers && isset($excluded_answers[$k+1]) ? 'excluded-group="'.$excluded_answers[$k+1].'"' : '' !!}
						>

						<div class="checkbox-square">✓</div>
							<input id="answer-{{ $question->id }}-{{ $loop->index+1 }}" type="checkbox" name="answer" class="answer{!! mb_substr($answer, 0, 1)=='!' ? ' disabler' : '' !!} input-checkbox" value="{{ $loop->index+1 }}">

							@if($question->allAnswersHaveImages() && !empty($answerImage))
								<div class="answer-image" style="background-image: url({{ $question->getAnswerImageUrl(true, $k ) }})">
									<img class="img-unchecked" src="{{ url('new-vox-img/non-selected-img-answer-icon.svg') }}">
									<img class="img-checked" src="{{ url('new-vox-img/selected-img-answer-icon.svg') }}"/>
									<a class="zoom-answer" data-lightbox="an-{{ $question->id }}-{{ $k }}" href="{{ $answerImage }}">
										<img src="{{ url('new-vox-img/zoom-in-icon2.svg') }}"/>
									</a>
								</div>
							@endif

							{!! nl2br(App\Models\VoxQuestion::handleAnswerTooltip( mb_substr($answer, 0, 1)=='!' || mb_substr($answer, 0, 1)=='#' ? mb_substr($answer, 1) : $answer))  !!}

							@if(!empty($answerTooltip))
								<div class="answer-mobile-tooltip tooltip-text" text="{!! $answerTooltip !!}">
									<img class="question-mark" src="{{ url('img/question-mark.png') }}" />
								</div>
							@endif
						</label>
						{!! !$question->allAnswersHaveImages() && $answerTooltip && !empty($answerImage) ? '<img src="'.$answerImage.'" style="display: none !important;" />' : '' !!}
					</div>
				@endif
				@if($isInColumns && round(count($questionAnswers) / 2) == $loop->iteration )
					</div> 
					<div class="answers-column"> 
				@endif
			@endforeach

			@if( $isInColumns)
				</div> 
			@endif
		</div>

		<a href="javascript:;" class="next-answer">{!! trans('vox.page.questionnaire.next') !!}</a>
	</div>
@elseif($question->type == 'scale')
	<div class="question-group question-group-{{ $question->id }} scale" data-id="{{ $question->id }}" welcome="{!! $question->vox_id == 11 ? '1' : '' !!}">

		@php($imageQuestion = !empty($question->imageOnlyInQuestion()) || !empty($question->imageInTooltipAndQuestion()))

		<div class="question {{ $imageQuestion ? 'question-with-image' : '' }}" {!! !empty($question->imageOnlyInTooltip()) || !empty($question->imageInTooltipAndQuestion()) ? 'tooltip-image="'.$question->getImageUrl(false).'"' : ''  !!}>
			@if($imageQuestion)
				<a class="question-image" data-lightbox="{{ $question->id }}" href="{{ $question->getImageUrl(false) }}">
					<img class="q-img" src="{{ $question->getImageUrl(true) }}" style="max-width: 100%;">
					<img class="zoom-img" src="{{ url('new-vox-img/zoom-in-icon2.svg') }}"/>
				</a>
				<div class="q-text"> 
			@endif

			{!! nl2br($question->questionWithTooltips()) !!}

			@if($imageQuestion)
				</div>
			@endif
		</div>
		<div class="answers">
			<div class="answers-inner">
				<div class="mobile-overflow"></div>
				<div class="flickity">
					
					@foreach(json_decode($question->answers, true) as $k => $answer)
						@php($answerTooltip = $question->hasAnswerTooltip($answer, $question))
						@php($answerImage = $question->getAnswerImageUrl(false, $k))
						@php($questionAnswers = explode(',', $scales[$question->vox_scale_id]->answers))
						@php($isInColumns = count($questionAnswers) >= 8)

						<div class="answer-radios-group clearfix">
							<div class="answer-question">
								<h3 {!! !$question->allAnswersHaveImages() && $answerTooltip && !empty($answerImage) ? 'tooltip-image="'.$answerImage.'"' : '' !!}>{!!  nl2br( App\Models\VoxQuestion::handleAnswerTooltip(mb_substr($answer, 0, 1)=='#' ? mb_substr($answer, 1) : $answer)) !!}
								</h3>
								{!! !$question->allAnswersHaveImages() && $answerTooltip && !empty($answerImage) ? '<img src="'.$answerImage.'" style="display: none !important;" />' : '' !!}
							</div>
							<div class="buttons-list clearfix {!! $isInColumns ? 'in-columns' : '' !!}"> 
								@if($isInColumns)
									<div class="answers-column"> 
								@endif
								@foreach( $questionAnswers as $ans)
									<div class="tac answer-inner" style="width: {{ 100 / count($questionAnswers) }}%;">
										<label class="answer-radio" for="answer-{{ $question->id }}-{{ $loop->index+1 }}-{{ $k }}">
											<input id="answer-{{ $question->id }}-{{ $loop->index+1 }}-{{ $k }}" type="radio" name="answer-{{ $k }}" class="answer" value="{{ $loop->index+1 }}" style="display: none;">
											{{ $ans }}											
										</label>
									</div>
									@if($isInColumns && round(count($questionAnswers) / 2) == $loop->iteration )
										</div> 
										<div class="answers-column"> 
									@endif
								@endforeach
								@if($isInColumns)
									</div> 
								@endif
							</div> 
						</div>
					@endforeach

				</div>
			</div>
		</div>

		<a href="javascript:;" class="next-answer">{!! trans('vox.page.questionnaire.next') !!}</a>
	</div>
@elseif(array_key_exists($question->id, $cross_checks) && $question->cross_check == 'birthyear')
	<div class="
		question-group question-group-{{ $question->id }} birthyear-question 
		{{ $question->is_control == -1 ? 'shuffle' : '' }} 
	" 
	data-id="{{ $question->id }}" 
	{!! array_key_exists($question->id, $cross_checks) ? 'cross-check-correct="'.$cross_checks[$question->id].'" cross-check-id="'.$cross_checks_references[$question->id].'"' : '' !!} 
	welcome="{!! $question->vox_id == 11 ? '1' : '' !!}">

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

		<a href="javascript:;" class="next-answer">{!! trans('vox.page.questionnaire.next') !!}</a>
	</div>
@elseif($question->type == 'number')
	<div class="question-group question-group-{{ $question->id }} number" 

	data-id="{{ $question->id }}" 
	{!! array_key_exists($question->id, $cross_checks) ? 'cross-check-correct="'.$cross_checks[$question->id].'" cross-check-id="'.$cross_checks_references[$question->id].'"' : '' !!} 
	welcome="{!! $question->vox_id == 11 ? '1' : '' !!}">

		@php($imageQuestion = !empty($question->imageOnlyInQuestion()) || !empty($question->imageInTooltipAndQuestion()))

		<div class="question {{ $imageQuestion ? 'question-with-image' : '' }}" {!! !empty($question->imageOnlyInTooltip()) || !empty($question->imageInTooltipAndQuestion()) ? 'tooltip-image="'.$question->getImageUrl(false).'"' : ''  !!}>
			@if($imageQuestion)
				<a class="question-image" data-lightbox="{{ $question->id }}" href="{{ $question->getImageUrl(false) }}">
					<img class="q-img" src="{{ $question->getImageUrl(true) }}" style="max-width: 100%;">
					<img class="zoom-img" src="{{ url('new-vox-img/zoom-in-icon2.svg') }}"/>
				</a>
				<div class="q-text"> 
			@endif

			{!! nl2br($question->questionWithTooltips()) !!}

			@if($imageQuestion)
				</div>
			@endif
		</div>
		<div class="answers">
			<input type="number" name="answer-number" class="answer-number" min="{{ explode(':',$question->number_limit)[0] }}" max="{{ explode(':',$question->number_limit)[1] }}">
		</div>

		<a href="javascript:;" class="next-answer">{!! trans('vox.page.questionnaire.next') !!}</a>

		<div style="display: none; margin-top: 10px;text-align: center;" class="answer-number-error alert alert-warning">
			{!! trans('vox.page.questionnaire.answer-number-error', ['min' => explode(':',$question->number_limit)[0], 'max' => explode(':',$question->number_limit)[1] ]) !!}
		</div>
	</div>
@elseif($question->type == 'rank')
	<div class="question-group question-group-{{ $question->id }} rank" 

	data-id="{{ $question->id }}" 
	{!! array_key_exists($question->id, $cross_checks) ? 'cross-check-correct="'.$cross_checks[$question->id].'" cross-check-id="'.$cross_checks_references[$question->id].'"' : '' !!} 
	welcome="{!! $question->vox_id == 11 ? '1' : '' !!}">

		@php($imageQuestion = !empty($question->imageOnlyInQuestion()) || !empty($question->imageInTooltipAndQuestion()))
	
		<div class="question {{ $imageQuestion ? 'question-with-image' : '' }}" {!! !empty($question->imageOnlyInTooltip()) || !empty($question->imageInTooltipAndQuestion()) ? 'tooltip-image="'.$question->getImageUrl(false).'"' : ''  !!}>
			@if($imageQuestion)
				<a class="question-image" data-lightbox="{{ $question->id }}" href="{{ $question->getImageUrl(false) }}">
					<img class="q-img" src="{{ $question->getImageUrl(true) }}" style="max-width: 100%;">
					<img class="zoom-img" src="{{ url('new-vox-img/zoom-in-icon2.svg') }}"/>
				</a>
				<div class="q-text"> 
			@endif

			{!! nl2br($question->questionWithTooltips()) !!}
			<p>{!! !empty($question->rank_explanation) ? $question->rank_explanation : trans('vox.page.questionnaire.rank-info') !!}</p>
			@if($imageQuestion)
				</div>
			@endif
		</div>

		@php($questionAnswers = isset($question_answers_reordered) ? $question_answers_reordered : ($question->vox_scale_id && !empty($scales[$question->vox_scale_id]) ? explode(',', $scales[$question->vox_scale_id]->answers) : json_decode($question->answers, true)))

		<div class="answers answers-draggable">
			@foreach($questionAnswers as $key => $answer)
				@php($answerTooltip = $question->hasAnswerTooltip($answer, $question))
				@php($answerImage = $question->getAnswerImageUrl(false, $key))

				<label class="answer-rank no-mobile-tooltips" data-num="{{ $loop->iteration }}" rank-order="{{ $loop->iteration }}" for="answer-{{ $question->id }}-{{ $loop->index+1 }}"  {!! !empty($answerTooltip) ? 'text="'.$answerTooltip.'"' : '' !!} 
				{!! !$question->allAnswersHaveImages() && $answerTooltip && !empty($answerImage) ? 'tooltip-image="'.$answerImage.'"' : '' !!}>
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

					@if(!empty($answerTooltip))
						<div class="answer-mobile-tooltip tooltip-text" text="{!! $answerTooltip !!}">
							<img class="question-mark" src="{{ url('img/question-mark.png') }}" />
						</div>
					@endif
					{!! !$question->allAnswersHaveImages() && $answerTooltip && !empty($answerImage) ? '<img src="'.$answerImage.'" style="display: none !important;" />' : '' !!}
				</label>
			@endforeach
		</div>

		<a href="javascript:;" class="next-answer">{!! trans('vox.page.questionnaire.next') !!}</a>

		<div style="display: none; margin-top: 10px;text-align: center;" class="answer-rank-error alert alert-warning">
			{!! trans('vox.page.questionnaire.rank-error') !!}
		</div>
	</div>
@else
	<div class="
		question-group question-group-{{ $question->id }}
		single-choice 
		{{ $question->is_control == -1 || (empty($question->dont_randomize_answers) && empty($question->vox_scale_id) && empty($scales[$question->vox_scale_id])) ? 'shuffle' : '' }} 
	" 
	data-id="{{ $question->id }}"
	{!! array_key_exists($question->id, $cross_checks) ? 'cross-check-correct="'.$cross_checks[$question->id].'" cross-check-id="'.$cross_checks_references[$question->id].'"' : '' !!} 
	welcome="{!! $question->vox_id == 11 ? '1' : '' !!}">

		@php($imageQuestion = !empty($question->imageOnlyInQuestion()) || !empty($question->imageInTooltipAndQuestion()))

		<div class="question {{ $imageQuestion ? 'question-with-image' : '' }}" {!! !empty($question->imageOnlyInTooltip()) || !empty($question->imageInTooltipAndQuestion()) ? 'tooltip-image="'.$question->getImageUrl(false).'"' : ''  !!}>
			@if($imageQuestion)
				<a class="question-image" data-lightbox="{{ $question->id }}" href="{{ $question->getImageUrl(false) }}">
					<img class="q-img" src="{{ $question->getImageUrl(true) }}" style="max-width: 100%;">
					<img class="zoom-img" src="{{ url('new-vox-img/zoom-in-icon2.svg') }}"/>
				</a>
				<div class="q-text"> 
			@endif

			{!! nl2br($question->questionWithTooltips()) !!}

			@if($imageQuestion)
				</div>
			@endif
		</div>

		@php($questionAnswers = isset($question_answers_reordered) ? $question_answers_reordered : ($question->vox_scale_id && !empty($scales[$question->vox_scale_id]) ? explode(',', $scales[$question->vox_scale_id]->answers) : json_decode($question->answers, true)))

		@php($isInColumns = (empty($answers_shown) && !$question->allAnswersHaveImages() && count($questionAnswers) >= 8))

		<div class="answers
		{!! $isInColumns ? 'in-columns' : '' !!}
		{{ $question->allAnswersHaveImages() ? 'question-pictures' : '' }}
		">
			@if($isInColumns)
				<div class="answers-column"> 
			@endif
			@foreach($questionAnswers as $key => $answer)
				@if(empty($answers_shown) || (!empty($answers_shown) && in_array($loop->iteration, $answers_shown)))

					@php($answerTooltip = $question->hasAnswerTooltip($answer, $question))
					@php($answerImage = $question->getAnswerImageUrl(false, $key))

					<label class="answer answer no-mobile-tooltips {!! mb_substr($answer, 0, 1)=='#' ? ' disabler-label' : '' !!}" data-num="{{ $loop->index+1 }}" for="answer-{{ $question->id }}-{{ $loop->index+1 }}"  {!! !empty($answerTooltip) ? 'text="'.$answerTooltip.'"' : '' !!} 
					{!! !$question->allAnswersHaveImages() && $answerTooltip && !empty($answerImage) ? 'tooltip-image="'.$answerImage.'"' : '' !!}>
						<input id="answer-{{ $question->id }}-{{ $loop->index+1 }}" type="radio" name="answer" class="answer" value="{{ $loop->index+1 }}" style="display: none;">

						@if($question->allAnswersHaveImages() && !empty($answerImage))
							<div class="answer-image" style="background-image: url({{ $question->getAnswerImageUrl(true, $key) }})">

								<a class="zoom-answer" data-lightbox="an-{{ $question->id }}-{{ $key }}" href="{{ $answerImage }}">
									<img src="{{ url('new-vox-img/zoom-in-icon2.svg') }}"/>
								</a>
							</div>
						@endif

						{!! App\Models\VoxQuestion::handleAnswerTooltip(mb_substr($answer, 0, 1)=='#' ? mb_substr($answer, 1) : $answer) !!}

						@if(!empty($answerTooltip))
							<div class="answer-mobile-tooltip tooltip-text" text="{!! $answerTooltip !!}">
								<img class="question-mark" src="{{ url('img/question-mark.png') }}" />
							</div>
						@endif
						{!! !$question->allAnswersHaveImages() && $answerTooltip && !empty($answerImage) ? '<img src="'.$answerImage.'" style="display: none !important;" />' : '' !!}
					</label>
				@endif
				@if($isInColumns && round(count($questionAnswers) / 2) == $loop->iteration )
					</div>
					<div class="answers-column"> 
				@endif
			@endforeach

			@if( $isInColumns)
				</div> 
			@endif
		</div>
	</div>
@endif