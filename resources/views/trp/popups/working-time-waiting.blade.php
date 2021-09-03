<div class="popup fixed-popup popup-wokring-time active removable" id="popup-wokring-time-waiting" empty-hours scss-load="trp-popup-working-time">
	<div class="popup-inner inner-white">
		<div class="popup-pc-buttons">
			<a href="javascript:;" class="close-popup">
                <img src="{{ url('img/close-icon.png') }}"/>
            </a>
		</div>

		<div class="popup-mobile-buttons">
			<a href="javascript:;" class="close-popup">< {!! nl2br(trans('trp.common.back')) !!}</a>
		</div>
		<h2>
            {!! nl2br(trans('trp.popup.popup-wokring-time.title')) !!}
        </h2>

		{!! Form::open(array('method' => 'post', 'url' => getLangUrl('add-working-hours') )) !!}
			{!! csrf_field() !!}

			<input type="hidden" name="last_user_id" value="">
			<input type="hidden" name="last_user_hash" value="">
			
			@for($day=1;$day<=7;$day++)
				<h4 class="popup-title tac">{{ date('l', strtotime("Sunday +{$day} days")) }}</h4>

                <div class="day-wrapper">
                    <div class="popup-desc" >
                        <label for="day-{{ $day }}"> 
                            {{ Form::checkbox( 'day-'.$day, 1, '', array( 'id' => 'day-'.$day, 'class' => 'work-hour-cb' ) ) }}
                        </label>
                        {{ Form::select( 
                            'work_hours['.$day.'][0][0]', 
                            $hours,
                            '' , 
                            array(
                                'class' => 'input grayed', 
                                'placeholder' => 'HH',
                            ) 
                        ) }}
                        {{ Form::select( 
                            'work_hours['.$day.'][0][1]', 
                            $minutes,
                            '' , 
                            array(
                                'class' => 'input grayed', 
                                'placeholder' => 'MM',
                            ) 
                        ) }}
                        <div class="separator"></div> 
                        {{ Form::select( 
                            'work_hours['.$day.'][1][0]', 
                            $hours,
                            '' , 
                            array(
                                'class' => 'input grayed', 
                                'placeholder' => 'HH',
                            ) 
                        ) }}
                        {{ Form::select( 
                            'work_hours['.$day.'][1][1]', 
                            $minutes,
                            '' , 
                            array(
                                'class' => 'input grayed', 
                                'placeholder' => 'MM',
                            ) 
                        ) }}
                    </div>

                    @if($day == 1)
                        <a href="javascript:;" class="all-days-equal">{!! nl2br(trans('trp.popup.popup-wokring-time.user-same-hours')) !!}</a>
                    @endif
                </div>

			@endfor

			<div class="alert" style="display: none; margin-top: 20px;">
			</div>
			
			
			<input type="hidden" name="json" value="1" />
			<input type="hidden" name="field" value="work_hours" />

		{!! Form::close() !!}
	</div>
</div>