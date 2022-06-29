
<div class="flex work-hours {{ $withoutUser ? 'force-wrap' : '' }}">
    @php
        $hours = [];
        for($i=0;$i<=23;$i++) {
            $h = str_pad($i, 2, "0", STR_PAD_LEFT);
            $hours[$h] = $h;
        }

        $minutes = [
            '00' => '00',
            '10' => '10',
            '20' => '20',
            '30' => '30',
            '40' => '40',
            '50' => '50',
        ];

        $week_days = [
            1 => 'Mon',
            'Tue',
            'Wed',
            'Thu',
            'Fri',
            'Sat',
            'Sun',
        ];
    @endphp
    @foreach($week_days as $w => $week_day)
        <div class="col {{ date('w') == $w ? 'active' : '' }} col-{{ $w }}">
            <p class="month">
                {{ $week_day }}
            </p>
            @if($loggedUserAllowEdit)
                <div class="edit-working-hours-wrapper">
                    <div class="edit-working-hours-wrap">
                        {{-- <input type="text" class="input" style="width: 56px;padding: 0px 4px;text-align: center;"/> --}}
                        {{ Form::select( 
                            'work_hours['.$w.'][0][0]', 
                            $hours,
                            !$withoutUser && !empty($user->work_hours[$w][0]) ? explode(':', $user->work_hours[$w][0])[0] : '' , 
                            array(
                                'class' => !$withoutUser && !empty($user->work_hours[$w]) ? 'input' : 'input grayed', 
                                'placeholder' => 'HH',
                            ) 
                        ) }}
                        {{ Form::select( 
                            'work_hours['.$w.'][0][1]', 
                            $minutes,
                            !$withoutUser && !empty($user->work_hours[$w][0]) ? explode(':', $user->work_hours[$w][0])[1] : '' , 
                            array(
                                'class' => !$withoutUser && !empty($user->work_hours[$w]) ? 'input' : 'input grayed', 
                                'placeholder' => 'MM',
                            ) 
                        ) }}
                        <div class="line-break"></div>
                        {{ Form::select( 
                            'work_hours['.$w.'][1][0]', 
                            $hours,
                            !$withoutUser && !empty($user->work_hours[$w][1]) ? explode(':', $user->work_hours[$w][1])[0] : '' , 
                            array(
                                'class' => !$withoutUser && !empty($user->work_hours[$w]) ? 'input' : 'input grayed', 
                                'placeholder' => 'HH',
                            ) 
                        ) }}
                        {{ Form::select( 
                            'work_hours['.$w.'][1][1]', 
                            $minutes,
                            !$withoutUser && !empty($user->work_hours[$w][1]) ? explode(':', $user->work_hours[$w][1])[1] : '' , 
                            array(
                                'class' => !$withoutUser && !empty($user->work_hours[$w]) ? 'input' : 'input grayed', 
                                'placeholder' => 'MM',
                            ) 
                        ) }}

                    </div>

                    <label class="checkbox-label {{ !$withoutUser && empty($user->work_hours[$w]) ? 'active' : '' }}" for="day-{{ $w }}"> 
                        {{ Form::checkbox( 'day_'.$w, 1, '', [
                            'id' => 'day-'.$w, 
                            'class' => 'special-checkbox work-hour-cb', 
                            !$withoutUser && empty($user->work_hours[$w]) ? 'checked' : 'something' => 'checked'
                        ]) }}
                        <div class="checkbox-square">✓</div>
                        Closed
                    </label>

                    @if($w == 1)
                        <label class="checkbox-label" for="all-days-equal"> 
                            {{ Form::checkbox( 'all-days-equal', 1, '', array( 'id' => 'all-days-equal', 'class' => 'special-checkbox all-days-equal') ) }}
                            <div class="checkbox-square">✓</div>
                            {{-- {!! nl2br(trans('trp.popup.popup-wokring-time.user-same-hours')) !!} --}}
                            Apply to all
                        </label>
                    @endif
                </div>
            @endif
            <div class="working-hours-wrap">
                @if($dentistWorkHours)
                    @if(array_key_exists($w, $dentistWorkHours))
                        <p>
                            @foreach($dentistWorkHours[$w] as $k => $work_hours)
                                {{ $work_hours }} {!! $loop->last ? '' : ' - ' !!}
                            @endforeach
                        </p>
                    @else
                        <p>Closed</p>
                    @endif    
                @else
                    <p>HH:MM-HH:MM</p>
                @endif
            </div>
        </div>
    @endforeach
</div>