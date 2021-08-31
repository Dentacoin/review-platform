<a href="javascript:;" class="strength-button">
	<img class="down" src="{{ url('img/caret-black-down.png') }}"/>
	<img class="up" src="{{ url('img/caret-black-up.png') }}"/>
</a>
<div class="strength-wrapper">
	<p class="extra-title">{!! $user->is_dentist ? nl2br(trans('trp.strength.title-dentist')) : nl2br(trans('trp.strength.title')) !!}</p>
	<div class="stretching-box">
		<div class="strength-flickity">
			@foreach($strength_arr as $strength)
				@if(!empty($strength['text']))
					<div class="strength-flickity-slide">
						<h2><a href="{{ !empty($strength['buttonHref']) ? $strength['buttonHref'] : 'javascript:;' }}" {{ !empty($strength['target']) ? 'target="_blank"' : '' }} >{{ $strength['title'] }}</a></h2>
						<a href="{{ !empty($strength['buttonHref']) ? $strength['buttonHref'] : 'javascript:;' }}" {{ !empty($strength['target']) ? 'target="_blank"' : '' }} class="strength-box">
							<div class="strength-image">
								<img src="{{ url('img-strength/'.$strength['image'].'.svg') }}">
							</div>
							<span class="strength-desc">{!! $strength['text'] !!}</span>
						</a>
						<div class="strength-urls">
							@if(!empty($strength['iosLink']))
								<a target="_blank" href="{{ $strength['androidLink'] }}" class="button app-store android" {{ !empty($strength['event_category']) ? 'event_category='.$strength['event_category'] : '' }} {{ !empty($strength['event_action']) ? 'event_action='.$strength['event_action'] : '' }} {{ !empty($strength['event_label']) ? 'event_label='.$strength['event_label'] : '' }}></a>
								<a target="_blank" href="{{ $strength['iosLink'] }}" class="button app-store ios" {{ !empty($strength['event_category']) ? 'event_category='.$strength['event_category'] : '' }} {{ !empty($strength['event_action']) ? 'event_action='.$strength['event_action'] : '' }} {{ !empty($strength['event_label']) ? 'event_label='.$strength['event_label'] : '' }}></a>
							@else
								<a href="{{ !empty($strength['buttonHref']) ? $strength['buttonHref'] : 'javascript:;' }}" class="button {{ $strength['completed'] ? 'completed' : '' }} {{ !empty($strength['buttonjs']) ? $strength['buttonjs'] : '' }}" {{ !empty($strength['target']) ? 'target="_blank"' : '' }} {{ !empty($strength['event_category']) ? 'event_category='.$strength['event_category'] : '' }} {{ !empty($strength['event_action']) ? 'event_action='.$strength['event_action'] : '' }} {{ !empty($strength['event_label']) ? 'event_label='.$strength['event_label'] : '' }} >{{ $strength['buttonText'] }}</a>
							@endif								
						</div>
						
						<div class="strenght-progress">
							<p>{!! nl2br(trans('trp.strength.progress')) !!}: <span><span class="strength-current">{{ $user->is_dentist ? $strength_arr['completed_steps'] : $completed_strength }}</span>/<span class="strength-total">{{ $user->is_dentist ? 10 : count($strength_arr) }}</span></span></p>
						</div>
					</div>
				@endif
			@endforeach
		</div>
	</div>
	@if($user->is_dentist)
		<div class="strength-scale-wrapper">
			@for($i=0; $i<=10; $i++)
				<div class="strength-divider" style="left: {{ 10 * $i }}%"></div>
			@endfor
			<div class="strength-tick {{ $strength_arr['completed_steps'] == 0 ? 'zero' : '' }}" style="left: {{ 10 * $strength_arr['completed_steps'] }}%; {{ $strength_arr['completed_steps'] == 10 ? 'display: none;' : '' }}"></div>
			<div class="strength-star {{ $strength_arr['completed_steps'] == 10 ? 'full' : '' }}"></div>
			<div class="strength-scale">
				<div class="strength-scale-inner" style="width: {{ 10 * $strength_arr['completed_steps'] }}% "></div>
			</div>
		</div>

	@else
		<div class="strength-scale-wrapper">
			@foreach($strength_arr as $strength)
				<div class="strength-divider" style="left: {{ (100 / count($strength_arr)) * $loop->iteration }}%"></div>
			@endforeach
			<div class="strength-tick {{ $completed_strength == 0 ? 'zero' : '' }}" style="left: {{ (100 / count($strength_arr)) * $completed_strength }}%; {{ $completed_strength == count($strength_arr) ? 'display: none;' : '' }}"></div>
			<div class="strength-star {{ $completed_strength == count($strength_arr) ? 'full' : '' }}"></div>
			<div class="strength-scale">
				<div class="strength-scale-inner" style="width: {{ (100 / count($strength_arr)) * $completed_strength }}% "></div>
			</div>
		</div>
	@endif
</div>