<a href="javascript:;" class="strength-button"></a>
<div class="strength-wrapper">
	<p class="extra-title">{!! $user->is_dentist ? nl2br(trans('trp.strength.title-dentist')) : nl2br(trans('trp.strength.title')) !!}</p>
	<div class="stretching-box">
		<div class="strength-flickity">
			@foreach($strength_arr as $strength)
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
						<p>{!! nl2br(trans('trp.strength.progress')) !!}: <span><span class="strength-current">{{ $completed_strength }}</span>/<span class="strength-total">{{ count($strength_arr) }}</span></span></p>
					</div>
				</div>
			@endforeach
		</div>
	</div>
	<div class="strength-scale-wrapper">
		@foreach($strength_arr as $strength)
			<div class="strength-divider" style="left: {{ (100 / count($strength_arr)) * $loop->iteration }}%"></div>
		@endforeach
		<div class="strength-tick {{ $completed_strength == 0 ? 'zero' : '' }}" style="left: {{ (100 / count($strength_arr)) * $completed_strength }}%"></div>
		<div class="strength-star {{ $completed_strength == count($strength_arr) ? 'full' : '' }}"></div>
		<div class="strength-scale">
			<div class="strength-scale-inner" style="width: {{ (100 / count($strength_arr)) * $completed_strength }}% "></div>
		</div>
	</div>
</div>