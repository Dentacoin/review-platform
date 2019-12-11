@if(!empty($daily_poll))
	<div class="poll-bubble">
		<img class="small-bubble-image" src="{{ url('new-vox-img/small-poll.png') }}">
		<div class="bubble-inner">
			<img class="main-bubble-image" src="{{ url('new-vox-img/daily-poll-first.png') }}">
			<div class="white-bubble">
				<a href="javascript:;" class="close-bubble"><img src="{{ url('new-vox-img/close-popup.png') }}"></a>
				<h4>{!! nl2br(trans('vox.daily-polls.popup.title')) !!}</h4>
				<p><span class="daily-respondents">{{ !empty($daily_poll) ? $daily_poll->respondentsCount() : '' }}</span>/100 people</p>
				<div class="poll-reward twerk-it"><img src="{{ url('new-vox-img/coin-icon.png') }}">{{ $daily_poll_reward }} DCN</div>
				<a href="javascript:;" class="answer-poll" q="{{ !empty($daily_poll) ? $daily_poll->question : '' }}" data-popup="poll-popup">{!! nl2br(trans('vox.daily-polls.popup.answer')) !!}</a>
			</div>
		</div>
	</div>
@elseif(!empty($closed_daily_poll))
	<div class="poll-bubble">
		<img class="small-bubble-image" src="{{ url('new-vox-img/small-poll.png') }}">
		<div class="bubble-inner">
			<img class="main-bubble-image" src="{{ url('new-vox-img/daily-poll-first.png') }}">
			<div class="white-bubble closed-bubble">
				<a href="javascript:;" class="close-bubble"><img src="{{ url('new-vox-img/close-popup.png') }}"></a>
				<h4>Daily Poll</h4>
				<h4 class="closed">Closed</h4>
				<p class="closed-p">You missed your chance. Try tomorrow. </p>
				<a href="javascript:;" class="closed-poll-button see-stats" poll-id="{{ $closed_daily_poll->id }}">Results</a>
			</div>
		</div>
	</div>
@endif

<div class="popup fixed-popup popup-with-background daily-poll close-on-shield" id="poll-popup">
	<div class="popup-inner">
		<a href="javascript:;" class="closer">
			<img src="{{ url('new-vox-img/close-popup.png') }}">
			<div class="back-home">
				{!! nl2br(trans('vox.daily-polls.popup.back')) !!}
			</div>
		</a>
		<div class="daily-image">
			<img class="main-bubble-image" src="{{ url('new-vox-img/daily-poll-first.png') }}">
		</div>
		<div class="content poll-form-wrapper">
			<form action="{{ !empty($daily_poll) ? getLangUrl('poll/'.$daily_poll->id) : '' }}" class="poll-form">
				<h3>
					{!! nl2br(trans('vox.daily-polls.popup.title')) !!}
				</h3>
				{!! csrf_field() !!}
				<div class="poll-question">
					{{ !empty($daily_poll) ? $daily_poll->question  : '' }}
				</div>
				<div class="poll-answers {!! !empty($daily_poll) && $daily_poll->type == 'scale' ? 'poll-scale-answers' : '' !!}">
					@if(!empty($daily_poll))
						@foreach($daily_poll->scale_id && !empty($poll_scales[$daily_poll->scale_id]) ? explode(',', $poll_scales[$daily_poll->scale_id]->answers) : json_decode($daily_poll->answers, true) as $key => $answer)
							<label class="poll-answer" for="ans-{{ $key }}">
								<input type="radio" name="answer" class="answer" value="{{ $loop->index+1 }}" id="ans-{{ $key }}">
								{!! nl2br( App\Models\Poll::handleAnswerTooltip($answer)) !!}
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
				<h3 alternative-title="{!! nl2br(trans('vox.daily-polls.popup.we-asked')) !!}" title="{!! nl2br(trans('vox.daily-polls.popup.get-exited', ['reward' => '<b>'. $daily_poll_reward.' DCN</b>'])) !!}">
					{!! nl2br(trans('vox.daily-polls.popup.get-exited', ['reward' => '<b>'. $daily_poll_reward.' DCN</b>'])) !!}
				</h3>
				@if(!empty($daily_poll))
					<p>
						Respondents: {{ $daily_poll->respondentsCount() }}/100 people
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
					@if($current_page != 'daily-polls')
						<a href="{{ getLangUrl('daily-polls') }}" class="white-button browse-polls">
							<img src="{{ url('new-vox-img/polls-calendar.svg') }}">
							{!! nl2br(trans('vox.daily-polls.popup.browse-polls')) !!}
						</a>
					@else					
						<a href="javascript:;" class="white-button next-stat">
							NEXT RESULTS
							<img src="{{ url('new-vox-img/next-arrow-blue.svg') }}">
						</a>
					@endif
					<a href="javascript:;" class="blue-button next-poll">
						{!! nl2br(trans('vox.daily-polls.popup.next-poll')) !!}
						<img src="{{ url('new-vox-img/next-arrow.svg') }}">
					</a>
				</div>
			@else
				<a href="{{ getLangUrl('registration') }}" class="blue-button sign">
					<img src="{{ url('new-vox-img/coins.svg') }}">
					{!! nl2br(trans('vox.daily-polls.popup.signin')) !!}
				</a>
				<div class="get-reward-buttons" style="display: none;">
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
			<h2>Oops! No more open polls for you. <br><br> Check again tomorrow for the new daily poll! Meanwhile, why don't you dive into our pool of paid surveys?</h2>
			<div class="get-reward-buttons">
				<a href="{{ getLangUrl('paid-dental-surveys') }}" class="blue-button">
					TAKE PAID SURVEYS
				</a>
			</div>
		</div>
	</div>
</div>