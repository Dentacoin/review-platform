<div class="popup fixed-popup popup-with-background daily-poll close-on-shield active" id="poll-popup">
	<div class="popup-inner">
		<a href="javascript:;" class="closer">
			<img src="{{ url('new-vox-img/close-popup.png') }}">
			<div class="back-home">
				{!! nl2br(trans('vox.daily-polls.popup.back')) !!}
			</div>
		</a>
		<div class="daily-image">
			<img class="main-bubble-image" src="{{ url('new-vox-img/dentavox-man-daily-polls-rewards.png') }}" alt="Dentavox man daily polls rewards">
		</div>
		<div class="content poll-form-wrapper">
			<form action="{{ !empty($daily_poll) ? getLangUrl('poll/'.$daily_poll->id) : '' }}" class="poll-form">
				<div class="loader-mask" style="position: absolute;">
				    <div class="loader">
				      	{{ trans('vox.common.loading') }}
				    </div>
				</div>
				<h3>
					{!! nl2br(trans('vox.daily-polls.popup.title')) !!}
				</h3>
				{!! csrf_field() !!}
				<div class="poll-question">
					{{ !empty($daily_poll) ? $daily_poll->question  : '' }}
				</div>
				<div class="poll-answers {!! !empty($daily_poll) && $daily_poll->type == 'scale' ? 'poll-scale-answers' : '' !!} {!! !empty($daily_poll) && $daily_poll->type != 'scale' && empty(
					$daily_poll->dont_randomize_answers) ? 'shuffle-answers' : '' !!}">
					@if(!empty($daily_poll))
						@foreach($daily_poll->scale_id && !empty($poll_scales[$daily_poll->scale_id]) ? explode(',', $poll_scales[$daily_poll->scale_id]->answers) : json_decode($daily_poll->answers, true) as $key => $answer)
							<label class="poll-answer {{ mb_substr($answer, 0, 1)=='#' ? 'dont-shuffle' : '' }}" for="ans-{{ $key }}">
								<input type="radio" name="answer" class="answer" value="{{ $loop->index+1 }}" id="ans-{{ $key }}">
								{!! nl2br( App\Models\Poll::handleAnswerTooltip(mb_substr($answer, 0, 1)=='#' ? mb_substr($answer, 1) : $answer)) !!}
							</label>
						@endforeach
					@endif
				</div>
				<input type="submit" name="submit" style="display: none;">
			</form>
			<div class="poll-reward"><img src="{{ url('new-vox-img/coin-icon.png') }}">{{ $daily_poll_reward }} DCN</div>
		</div>
		<div class="content poll-stats-wrapper" style="display: none;">
			<div class="poll-group">
				<div class="loader-mask" style="position: absolute;">
				    <div class="loader">
				      	{{ trans('vox.common.loading') }}
				    </div>
				</div>
				<h3 alternative-title="{!! nl2br(trans('vox.daily-polls.popup.we-asked')) !!}" title="{!! nl2br(trans('vox.daily-polls.popup.get-exited', ['reward' => '<b>'. $daily_poll_reward.' DCN</b>'])) !!}">
					{!! nl2br(trans('vox.daily-polls.popup.get-exited', ['reward' => '<b>'. $daily_poll_reward.' DCN</b>'])) !!}
				</h3>
				@if(!empty($daily_poll))
					<p>
						{!! nl2br(trans('vox.daily-polls.popup.respondents', ['current_respondents' => $daily_poll->respondentsCount() ])) !!}
					</p>
				@endif
				{!! csrf_field() !!}

				<div class="poll-question">
					{{ !empty($daily_poll) ? $daily_poll->question : '' }}
				</div>
				<div class="poll-stats">
					<div id="chart-poll"></div>
				</div>
			</div>

			@if(!empty($user))
				<div class="get-reward-buttons">
					<a href="{{ getLangUrl('daily-polls') }}" class="white-button browse-polls browse-all-polls">
						<img src="{{ url('new-vox-img/polls-calendar.svg') }}" alt="Dentavox daily polls calendar">
						{!! nl2br(trans('vox.daily-polls.popup.browse-polls')) !!}
					</a>			
					<a href="javascript:;" class="white-button next-stat browse-next-stats">
						{!! nl2br(trans('vox.daily-polls.popup.next-results')) !!}
						<img src="{{ url('new-vox-img/next-arrow-blue.svg') }}">
					</a>
					<a href="javascript:;" class="blue-button next-poll">
						{!! nl2br(trans('vox.daily-polls.popup.next-poll')) !!}
						<img src="{{ url('new-vox-img/next-arrow.svg') }}">
					</a>
				</div>
			@else
				<a href="javascript:;" class="blue-button sign open-dentacoin-gateway patient-register">
					<img src="{{ url('new-vox-img/coins.svg') }}">
					{!! nl2br(trans('vox.daily-polls.popup.signin')) !!}
				</a>

				<div class="get-reward-buttons" style="display: none;">
					<a href="javascript:;" class="white-button next-stat">
						{!! nl2br(trans('vox.daily-polls.popup.next-results')) !!}
						<img src="{{ url('new-vox-img/next-arrow-blue.svg') }}">
					</a>
					<a href="javascript:;" class="blue-button next-poll">
						{!! nl2br(trans('vox.daily-polls.popup.next-poll')) !!}
						<img src="{{ url('new-vox-img/next-arrow.svg') }}">
					</a>
				</div>
			@endif
		</div>
		<div class="content poll-closed-wrapper" style="display: none;">
			<h3>
				{!! nl2br(trans('vox.daily-polls.popup.title')) !!}
			</h3>
			<h2>{!! nl2br(trans('vox.daily-polls.popup.reached-respondents')) !!}</h2>
			<div class="get-reward-buttons">
				<a href="javascript:;" class="white-button see-stats">
					{!! nl2br(trans('vox.daily-polls.popup.see-results')) !!}
				</a>
				<a href="javascript:;" class="blue-button next-poll">
					{!! nl2br(trans('vox.daily-polls.popup.next-poll')) !!}
					<img src="{{ url('new-vox-img/next-arrow.svg') }}">
				</a>
			</div>
		</div>
		<div class="content poll-taken-all-wrapper" style="display: none;">
			<h3>
				{!! nl2br(trans('vox.daily-polls.popup.title')) !!}
			</h3>
			<h2>{!! nl2br(trans('vox.daily-polls.popup.no-open-polls')) !!}</h2>
			<div class="get-reward-buttons">
				<a href="{{ getLangUrl('paid-dental-surveys') }}" class="blue-button">
					{!! nl2br(trans('vox.daily-polls.popup.take-paid-surveys')) !!}
				</a>
			</div>
		</div>
	</div>
</div>