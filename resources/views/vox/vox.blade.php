@extends('vox')

@section('content')

		<div class="main-title">
			<h1 class="bold title">
				{!! nl2br(trans('vox.page.'.$current_page.'.title')) !!}
			</h1>
		</div>
		<div class="container page-questions">
			<a href="{{ getLangUrl('/') }}" class="questions-back"><i class="fa fa-arrow-left"></i> {{ trans('vox.common.questionnaires') }}</a>

			<div id="question-meta">

				<h1 class="questionnaire-title">{{ $vox->title }}</h1>
				<p class="questionnaire-description">
					{{ $vox->description }}
				</p>
				<div class="questions">

					<div class="questions-dots">
						<div class="dot" id="current-question-bar" style="width: 0%;"></div>
					</div>
					<div class="row questions-header clearfix">
						<div class="col-md-6">
							<span class="bold">
								{{ trans('vox.common.step') }} <span id="current-question-num"></span>
								 / 
								{{ $vox->questions->count() }}
							</span>
						</div>
						<div class="col-md-6 tar">
							<span class="bold">
								<span id="current-question-reward">
									
								</span>
								 / 
								{{ $vox->reward }} DCN
							</span>
						</div>
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
								<div class="g-recaptcha" id="g-recaptcha" data-callback="sendReCaptcha" style="display: inline-block;" data-sitekey="6LdmpjQUAAAAAMlVjnFzaKp5nyKsGcalxhS_hcDd"></div>
								<div class="alert alert-warning" id="captcha-error" style="display: none;">
									{!! trans('vox.page.'.$current_page.'.not-robot-invalid') !!}
								</div>					
							</div>
						</div>
					@endif

					@foreach( $vox->questions as $question )
						<div class="question-group" data-id="{{ $question->id }}" {!! $question->id==$first_question ? '' : 'style="display: none;"' !!} >
							<div class="question">
								{!! nl2br($question->question) !!}
							</div>
							<div class="answers">
								@foreach(json_decode($question->answers, true) as $answer)
									<a href="javascript:;" class="answer" data-num="{{ $loop->index+1 }}">{{ $answer }}</a>
								@endforeach
							</div>
						</div>
					@endforeach

					<div class="question-hints">
						<p class="hint">
							{{ trans('vox.page.'.$current_page.'.finish-all', ['reward' => $vox->reward]) }}
						</p>
					</div>
				</div>

			</div>

			<div class="question-done page-questions-done" id="question-done" style="display: none;">
				<div class="modal-body">
					<p class="popup-title">
						{{ trans('vox.page.'.$current_page.'.good-job') }}
					</p>
					<p class="popup-second-title bold">
						{{ trans('vox.page.'.$current_page.'.just-won') }}
					</p>
					<div class="price">
						<img src="img-vox/dc-logo.png"/>
						<span class="coins">{{ $vox->reward }} DCN</span>
					</div>
				</div>
				<p class="buttons-description">
					{{ trans('vox.page.'.$current_page.'.try-another') }}
				</p>
				<a href="{{ getLangUrl('/') }}" class="button-questionnaries">{{ trans('vox.common.questionnaires') }}</a>
				<p class="buttons-description">
					{{ trans('vox.page.'.$current_page.'.withdraw') }}
				</p>
				<a href="{{ getLangUrl('profile/wallet') }}" class="button-wallet">{{ trans('vox.page.'.$current_page.'.wallet') }}</a>
			</div>

		</div>


		<script type="text/javascript">
			var vox = {
				count: {{ $vox->questions->count() }},
				reward: {{ $vox->reward }},
				current: {{ $first_question_num }},
				url: '{{ getLangUrl('questionnaire/'.$vox->id) }}'
			}
		</script>

@endsection