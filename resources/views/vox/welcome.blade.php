@extends('vox')

@section('content')

	<div class="section-welcome">

		<div class="container">

			<div class="col-md-3">
				<img class="image-left" src="{{ url('new-vox-img/welcome.png') }}">
			</div>

			<div class="col-md-9">
				<div class="questions">
					<h3 class="questionnaire-title tac">
						{{ $vox->title }}
					</h3>
					<p class="questionnaire-description" {!! !empty($answered) && count($answered)>1 ? 'style="display: none;"' : '' !!} >
						{{ $vox->description }}
					</p>
					<p class="demographic-questionnaire-description" style="display: none;" >
						You're almost done! Help us complete your demographic profile to ensure quality dental survey results!
					</p>
					<div class="questions-dots">
						<div class="dot" id="current-question-bar" style="width: 0%;"></div>
					</div>
					<div class="triangle"></div>
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
									{!! trans('vox.common.dcn_to_be_collected') !!}: {{ $vox->getRewardTotal() }}
								</span>
								<span id="dcn-test-reward-after" style="display: none;">
									{!! trans('vox.common.dcn_collected') !!}:
									<span id="current-question-reward">
										
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
									<a href="javascript:;" class="answer" data-num="{{ $loop->index+1 }}">{{ $answer }}</a>
								@endforeach
							</div>
						</div>
					@endforeach

					@if(!$user || ($user && !$user->birthyear))
						<div class="question-group birthyear-question" data-id="birthyear" style="display: none;">
							<div class="question">
								What's your year of birth?
							</div>
							<div class="answers">
								<input type="number" class="answer" name="birthyear-answer" id="birthyear-answer" min="{{ date('Y')-100 }}" max="{{ date('Y')-18 }}">
							</div>

							<a href="javascript:;" class="next-answer">{!! trans('vox.page.questionnaire.next') !!}</a>
						</div>
					@endif

					@if(!$user || ($user && !$user->gender))
						<div class="question-group gender-question" data-id="gender" style="display: none;">
							<div class="question">
								What's your biological sex?
							</div>
							<div class="answers">
								<a class="answer answer" for="answer-gende-m" data-num="m">
									<input id="answer-gender-m" type="radio" name="gender-answer" class="answer" value="m" style="display: none;">
									Male										
								</a>
								<a class="answer answer" for="answer-gender-f" data-num="f">
									<input id="answer-gender-f" type="radio" name="gender-answer" class="answer" value="f" style="display: none;">
									Female										
								</a>
							</div>
						</div>
					@endif

					@if(!$user || ($user && !$user->country_id))
						<div class="question-group location-question" data-id="location" style="display: none;">
							<div class="question">
								Where do you live?
							</div>
							<div class="answers">
								{{ Form::select( 'country_id' , ['' => '-'] + \App\Models\Country::get()->pluck('name', 'id')->toArray() , null , array('class' => 'country-select form-control') ) }}
							</div>

							<a href="javascript:;" class="next-answer">{!! trans('vox.page.questionnaire.next') !!}</a>
						</div>
					@endif
					
					<div class="question-done" style="display: none;">
						<div class="question tac">
							{!! nl2br(trans('vox.page.index.thank-you')) !!}
						</div>
					</div>

					<div style="display: none; margin-top: 10px;text-align: center;" class="answer-error alert alert-danger">
						{!! trans('vox.page.questionnaire.answer-error') !!}
					</div>

				</div>
			</div>
		</div>
	</div>

	<script type="text/javascript">
		var vox = {
			count: {{ $real_questions }},
			reward: {{ $vox->getRewardTotal() }},
			current: 1
		}
		var register_url = '{{ getLangUrl('registration') }}';
	</script>


	@if($user)
		<div class="section-welcome-done" style="display: none;">
			<div class="container">

				<div class="col-md-3">
					<img class="image-left" src="{{ url('new-vox-img/register.png') }}">
				</div>

				<div class="col-md-9 tac">
					<h3 class="done-title">Good job, <span class="blue-text"> {{ $user->getName() }}!</span></h3>
					<h4>
						You’ve just earned <span id="coins-test">{{ $vox->getRewardTotal() }}</span> DCN! To withdraw your <br/> reward, just go to your <a href="{{ getLangUrl('profile/wallet') }}">Wallet.</a> Ready to get more <br/> Dentacoin tokens?
					</h4>

					<a class="blue-button gradient-line" href="{{ getLangUrl('/') }}">Take me to the surveys</a>
				</div>
			</div>

			<div class="section-stats">
				<div class="container">
					<img src="{{ url('new-vox-img/stats-front.png') }}">
					<h3>Curious to see our survey stats?</h3>
					<a href="{{ getLangUrl('dental-survey-stats') }}" class="check-stats">Check stats</a>
				</div>
			</div>
		</div>
	@endif
    	
@endsection