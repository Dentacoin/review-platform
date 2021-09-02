@if(!empty($daily_poll))
	<div class="poll-bubble {!! !empty(session('hide_poll')) ? 'small-bubble' : '' !!}">
		<img class="small-bubble-image" src="{{ url('new-vox-img/poll-of-the-day-icon.png') }}" alt="Dentavox daily polls popup mobile">
		<div class="bubble-inner">
			<img class="main-bubble-image" src="{{ url('new-vox-img/dentavox-man-daily-polls-rewards.png') }}" alt="Dentavox man daily polls rewards">
			<div class="white-bubble">
				<a href="javascript:;" class="close-bubble"><img src="{{ url('new-vox-img/close-popup.png') }}"></a>
				<h4>{!! nl2br(trans('vox.daily-polls.popup.title')) !!}</h4>
				<p>
					{!! nl2br(trans('vox.daily-polls.popup.respondents-short', ['current_respondents' => '<span class="daily-respondents">'.$daily_poll->respondentsCount().'</span>' ])) !!}
				</p>
				<div class="poll-reward twerk-it"><img src="{{ url('new-vox-img/coin-icon.png') }}">{{ $daily_poll_reward }} DCN</div>
				<a href="javascript:;" class="answer-poll {{ $current_page == 'daily-polls' ? 'regenerate-poll-popup' : '' }}" cur-poll-id="{{ $daily_poll->id }}" q="{{ $daily_poll->question }}" {!! $current_page != 'daily-polls' ? 'data-popup="poll-popup"' : '' !!} data-href="{{ date('d-m-Y',$daily_poll->launched_at->timestamp) }}">{!! nl2br(trans('vox.daily-polls.popup.answer')) !!}</a>
			</div>
		</div>
	</div>
@elseif(!empty($closed_daily_poll))
	<div class="poll-bubble {!! !empty(session('hide_poll')) ? 'small-bubble' : '' !!}">
		<img class="small-bubble-image" src="{{ url('new-vox-img/poll-of-the-day-icon.png') }}" alt="Dentavox daily polls popup mobile">
		<div class="bubble-inner">
			<img class="main-bubble-image" src="{{ url('new-vox-img/dentavox-man-daily-polls-rewards.png') }}" alt="Dentavox man daily polls rewards">
			<div class="white-bubble closed-bubble">
				<a href="javascript:;" class="close-bubble"><img src="{{ url('new-vox-img/close-popup.png') }}"></a>
				<h4>{!! nl2br(trans('vox.daily-polls.popup.closed-poll.title.1')) !!}</h4>
				<h4 class="closed">{!! nl2br(trans('vox.daily-polls.popup.closed-poll.title.2')) !!}</h4>
				<p class="closed-p">{!! nl2br(trans('vox.daily-polls.popup.closed-poll.title.3')) !!}</p>
				<a href="javascript:;" class="closed-poll-button see-stats" poll-id="{{ $closed_daily_poll->id }}">{!! nl2br(trans('vox.daily-polls.popup.closed-poll.button')) !!}</a>
			</div>
		</div>
	</div>
@endif