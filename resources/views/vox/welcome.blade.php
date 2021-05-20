@extends('vox')

@section('content')

	<div class="section-welcome">

		<div class="container flex">

			<div class="mobile-welcome-images">
				<img class="mobile-bubble-effect" src="{{ url('new-vox-img/blue-circle.png') }}">
				<img class="mobile-person-effect" src="{{ url('new-vox-img/welcome-test-person.png') }}">
			</div>

			<div class="finish-test">
				<div class="mobile-welcome-images">
					<img class="mobile-bubble-effect" src="{{ url('new-vox-img/blue-circle.png') }}">
					<img class="mobile-person-effect" src="{{ url('new-vox-img/welcome-test-finish.png') }}">
				</div>
			</div>

			<div class="flex-3">
				<img class="image-left" src="{{ url('new-vox-img/dentavox-surveys-welcome-blinking-man.png') }}" alt="{{ trans('vox.welcome-survey.image.alt') }}">
			</div>

			<div class="flex-9">
				<div class="questions">
					<h3 class="questionnaire-title tac">
						{{ $vox->title }}
					</h3>
					<p class="questionnaire-description" {!! !empty($answered) && count($answered)>1 ? 'style="display: none;"' : '' !!} >
						{{ $vox->description }}
					</p>
					<p class="demographic-questionnaire-description" style="display: none;" >
						{!! nl2br(trans('vox.welcome-survey.description')) !!}
					</p>
					<div class="questions-dots">
						<div class="dot" id="current-question-bar" style="width: 0%;"></div>
					</div>
					<div class="triangle"></div>
					<div class="questions-header clearfix">
						<div class="flex-1">
							<span>
								{!! trans('vox.common.estimated_time', [
									'time' => '<span id="current-question-num"></span>'
								]) !!}
							</span>
						</div>
						<div class="flex-1 tar">
							<span>
								<span id="dcn-test-reward-before">
									{!! trans('vox.common.dcn_to_be_collected') !!}: {{ $vox->getRewardTotal() }}
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

					@foreach( $vox->questions as $question )
						<div class="question-group" data-id="{{ $question->id }}" {!! $loop->first ? '' : 'style="display: none;"' !!} >
							<div class="question">
								{!! nl2br($question->question) !!}
							</div>
							<div class="answers tac">
								@foreach(json_decode($question->answers, true) as $answer)
									<label href="javascript:;" class="answer" data-num="{{ $loop->index+1 }}">{{ $answer }}</label>
								@endforeach
							</div>
						</div>
					@endforeach

					@if(!$user || ($user && empty($user->birthyear)))
						<div class="question-group birthyear-question" data-id="birthyear" style="display: none;">
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

					@if(!$user || ($user && !$user->gender))
						<div class="question-group gender-question" data-id="gender" style="display: none;">
							<div class="question">
								{!! trans('vox.page.questionnaire.question-sex') !!}
							</div>
							<div class="answers">
								<label class="answer answer" for="answer-gende-m" data-num="m">
									<input id="answer-gender-m" type="radio" name="gender-answer" class="answer" value="m" style="display: none;">
									{!! trans('vox.page.questionnaire.question-sex-m') !!}
								</label>
								<label class="answer answer" for="answer-gender-f" data-num="f">
									<input id="answer-gender-f" type="radio" name="gender-answer" class="answer" value="f" style="display: none;">
									{!! trans('vox.page.questionnaire.question-sex-f') !!}
								</label>
							</div>
						</div>
					@endif

					@if(!$user || ($user && !$user->country_id))
						<div class="question-group location-question" data-id="location" style="display: none;">
							<div class="question">
								{!! trans('vox.page.questionnaire.question-country') !!}
							</div>
							<div class="answers">
								<div class="alert alert-warning ip-country mobile" style="display: none;">
	                        		{!! trans('vox.common.different-ip') !!}
		                        </div>
								{{ Form::select( 'country_id' , ['' => '-'] + \App\Models\Country::with('translations')->get()->pluck('name', 'id')->toArray() , null , array('class' => 'country-select form-control country-dropdown', 'real-country' => !empty($country_id) ? $country_id : '') ) }}
							</div>

							<a href="javascript:;" class="next-answer">{!! trans('vox.page.questionnaire.next') !!}</a>
						</div>
					@endif
					
					<div class="question-done" style="display: none;">
						<div class="question tac">
							{!! nl2br(trans('vox.page.index.thank-you')) !!}
						</div>
					</div>

					<div style="display: none; margin-top: 10px;text-align: center;" class="answer-error alert alert-warning">
						{!! trans('vox.page.questionnaire.answer-error') !!}
					</div>
					<div style="display: none; margin-top: 10px;text-align: center;" class="birthday-answer-error alert alert-warning">
						{!! nl2br(trans('vox.page.questionnaire.birthday-error')) !!}
					</div>

				</div>
			</div>
		</div>
	</div>

	<script type="text/javascript">
		var vox = {
			count: {{ $total_questions }},
			count_real: {{ $total_questions }},
			reward: {{ $vox->getRewardTotal() }},
			reward_single: {{ $vox->getRewardPerQuestion()->dcn }},
			current: 1
		};
		var welcome_vox_now = true;
	</script>

	@if($user)
		<div class="section-welcome-done" style="display: none;">
			<div class="container flex">

				<div class="flex-3">
					<img class="image-left" src="{{ url('new-vox-img/dentavox-man-surveys-registration.png') }}" alt="Dentavox man surveys registration ">
				</div>

				<div class="flex-9 tac">
					<h3 class="done-title">{!! trans('vox.welcome-survey.done.title', ['username' => '<span class="blue-text">'.$user->getNames().'!</span>']) !!}</h3>
					<h4>
						{!! nl2br(trans('vox.welcome-survey.done.description', [
							'reward' => '<span class="coins-test">'.$vox->getRewardTotal().'</span>',
							'walletlink' => '<a href="https://account.dentacoin.com/?platform=dentavox">',
							'endlink' => '</a>',
						])) !!}
					</h4>

					<a class="blue-button gradient-line" href="{{ getLangUrl('/') }}">{{ trans('vox.welcome-survey.done.button') }}</a>
				</div>
			</div>

			<div class="section-stats">
				<div class="container clearfix">
					<img src="{{ url('new-vox-img/dentavox-dental-statistics-icon.png') }}" alt="Dentavox dental statistics icon">
					<h3>{{ trans('vox.page.welcome-to-dentavox.curious') }}</h3>
					<a href="{{ getLangUrl('dental-survey-stats') }}" class="check-stats">{{ trans('vox.common.check-statictics') }}</a>
				</div>
			</div>
		</div>
	@endif
	
@endsection