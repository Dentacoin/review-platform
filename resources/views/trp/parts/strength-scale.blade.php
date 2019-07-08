<a href="javascript:;" class="strength-button"></a>
<div class="strength-wrapper">
	<p class="extra-title">{!! $user->is_dentist ? nl2br(trans('trp.strength.title-dentist')) : nl2br(trans('trp.strength.title')) !!}</p>
	<div class="stretching-box">
		<div class="strength-flickity">
			@foreach($user->getStrengthPlatform('trp') as $strength)
				<div class="strength-flickity-slide">
					<h2><a href="{{ $strength['buttonHref'] ? $strength['buttonHref'] : 'javascript:;' }}" {{ $strength['target'] == true ? 'target="_blank"' : '' }} >{{ $strength['title'] }}</a></h2>
					<a href="{{ $strength['buttonHref'] ? $strength['buttonHref'] : 'javascript:;' }}" {{ $strength['target'] == true ? 'target="_blank"' : '' }} class="strength-box">
						<div class="strength-image">
							<img src="{{ url('img-strength/'.$strength['image'].'.svg') }}">
						</div>
						<span class="strength-desc">{!! $strength['text'] !!}</span>
					</a>
					<div class="strength-urls">
						@if($strength['iosLink'])
							<a target="_blank" href="{{ $strength['androidLink'] }}" class="button app-store android" {{ $strength['event_category'] ? 'event_category='.$strength['event_category'] : '' }} {{ $strength['event_action'] ? 'event_action='.$strength['event_action'] : '' }} {{ $strength['event_label'] ? 'event_label='.$strength['event_label'] : '' }}></a>
							<a target="_blank" href="{{ $strength['iosLink'] }}" class="button app-store ios" {{ $strength['event_category'] ? 'event_category='.$strength['event_category'] : '' }} {{ $strength['event_action'] ? 'event_action='.$strength['event_action'] : '' }} {{ $strength['event_label'] ? 'event_label='.$strength['event_label'] : '' }}></a>
						@else
							<a href="{{ $strength['buttonHref'] ? $strength['buttonHref'] : 'javascript:;' }}" class="button {{ $strength['completed'] ? 'completed' : '' }}" {{ $strength['target'] == true ? 'target="_blank"' : '' }} {{ $strength['event_category'] ? 'event_category='.$strength['event_category'] : '' }} {{ $strength['event_action'] ? 'event_action='.$strength['event_action'] : '' }} {{ $strength['event_label'] ? 'event_label='.$strength['event_label'] : '' }} >{{ $strength['buttonText'] }}</a>
						@endif								
					</div>
					
					<div class="strenght-progress">
						<p>{!! nl2br(trans('trp.strength.progress')) !!}: <span><span class="strength-current">{{ $user->getStrengthCompleted('trp') }}</span>/<span class="strength-total">{{ count($user->getStrengthPlatform('trp')) }}</span></span></p>
					</div>
				</div>
			@endforeach
		</div>
	</div>
	<div class="strength-scale-wrapper">
		@foreach($user->getStrengthPlatform('trp') as $strength)
			<div class="strength-divider" style="left: {{ (100 / count($user->getStrengthPlatform('trp'))) * $loop->iteration }}%"></div>
		@endforeach
		<div class="strength-tick {{ $user->getStrengthCompleted('trp') == 0 ? 'zero' : '' }}" style="left: {{ (100 / count($user->getStrengthPlatform('trp'))) * $user->getStrengthCompleted('trp') }}%"></div>
		<div class="strength-star {{ $user->getStrengthCompleted('trp') == count($user->getStrengthPlatform('trp')) ? 'full' : '' }}"></div>
		<div class="strength-scale">
			<div class="strength-scale-inner" style="width: {{ (100 / count($user->getStrengthPlatform('trp'))) * $user->getStrengthCompleted('trp') }}% "></div>
		</div>
	</div>
</div>