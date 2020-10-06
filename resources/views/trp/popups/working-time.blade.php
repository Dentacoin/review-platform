<!-- if change this, also change in Waiting for approval popup -->

<div class="popup fixed-popup popup-wokring-time" id="popup-wokring-time" {!! empty(strip_tags($user->getWorkHoursText())) ? 'empty-hours' : '' !!}>
	<div class="popup-inner inner-white">
		<div class="popup-pc-buttons">
			<a href="javascript:;" class="close-popup"><i class="fas fa-times"></i></a>
		</div>

		<div class="popup-mobile-buttons">
			<a href="javascript:;" class="close-popup">< {!! nl2br(trans('trp.common.back')) !!}</a>
		</div>
		<h2>
            {!! nl2br(trans('trp.popup.popup-wokring-time.title')) !!}
        </h2>

		{!! Form::open(array('method' => 'post', 'url' => getLangUrl('profile/info') )) !!}
			{!! csrf_field() !!}
		
			@for($day=1;$day<=7;$day++)
				<h4 class="popup-title tac">{{ date('l', strtotime("Sunday +{$day} days")) }}</h4>

                <div class="day-wrapper">
                    <div class="popup-desc" >
                        <label for="day-{{ $day }}"> 
                            {{ Form::checkbox( 'day-'.$day, 1, '', array( 'id' => 'day-'.$day, 'class' => 'work-hour-cb', !empty($user->work_hours[$day]) ? 'checked' : 'something' => 'checked' ) ) }}
                        </label>
                        {{ Form::select( 
                            'work_hours['.$day.'][0][0]', 
                            $hours,
                            !empty($user->work_hours[$day][0]) ? explode(':', $user->work_hours[$day][0])[0] : '' , 
                            array(
                                'class' => !empty($user->work_hours[$day]) ? 'input' : 'input grayed', 
                                'placeholder' => 'HH',
                            ) 
                        ) }}
                        {{ Form::select( 
                            'work_hours['.$day.'][0][1]', 
                            $minutes,
                            !empty($user->work_hours[$day][0]) ? explode(':', $user->work_hours[$day][0])[1] : '' , 
                            array(
                                'class' => !empty($user->work_hours[$day]) ? 'input' : 'input grayed', 
                                'placeholder' => 'MM',
                            ) 
                        ) }}
                        <div class="separator"></div> 
                        {{ Form::select( 
                            'work_hours['.$day.'][1][0]', 
                            $hours,
                            !empty($user->work_hours[$day][1]) ? explode(':', $user->work_hours[$day][1])[0] : '' , 
                            array(
                                'class' => !empty($user->work_hours[$day]) ? 'input' : 'input grayed', 
                                'placeholder' => 'HH',
                            ) 
                        ) }}
                        {{ Form::select( 
                            'work_hours['.$day.'][1][1]', 
                            $minutes,
                            !empty($user->work_hours[$day][1]) ? explode(':', $user->work_hours[$day][1])[1] : '' , 
                            array(
                                'class' => !empty($user->work_hours[$day]) ? 'input' : 'input grayed', 
                                'placeholder' => 'MM',
                            ) 
                        ) }}
                    </div>

                    @if($day == 1)
                        <a href="javascript:;" class="all-days-equal" style="display: none;">{!! nl2br(trans('trp.popup.popup-wokring-time.user-same-hours')) !!}</a>
                    @endif
                </div>

			@endfor

			<div class="alert" style="display: none; margin-top: 20px;">
			</div>
			
			
			<input type="hidden" name="json" value="1" />
			<input type="hidden" name="field" value="work_hours" />
			<!-- <div class="tac">
				<input type="submit" class="button" value="{!! nl2br(trans('trp.popup.popup-wokring-time.save')) !!}">
			</div> -->

		{!! Form::close() !!}
	</div>
</div>