<div class="tab-container" id="more-info">
    <h2 class="mont">
        More info
        {{-- {!! nl2br(trans('trp.page.user.about-who',[
            'name' => $item->getNames()
        ])) !!} --}}
    </h2>

    @if(!$item->is_clinic && ($item->education_info || $loggedUserAllowEdit))
    {{-- @if($item->education_info || $loggedUserAllowEdit) --}}
        <div class="tab-inner-section">
            <div class="education-wrapper">
                <h3>
                    Education and background
                    @if($loggedUserAllowEdit)
                        <a href="javascript:;" class="edit-field-button tooltip-text toggle-section" toggle-section="education-wrapper" text="{{ $item->education_info ? 'Edit' : 'Add' }} your education and background">
                            <img src="{{ url('img-trp/pencil.svg') }}" width="20" height="17">
                        </a>
                    @endif
                </h3>

                @if($item->education_info)
                    <div class="chosen-education">
                        @foreach($item->education_info as $educationInfo)
                            •&nbsp;&nbsp;&nbsp;{{ $educationInfo }} <br/>
                        @endforeach
                    </div>
                @endif

                @if($loggedUserAllowEdit)
                    {{ Form::open([
                        'class' => 'edit-education-info-form',
                        'method' => 'post', 
                        'url' => getLangUrl('profile/info/'.($editing_branch_clinic ? $editing_branch_clinic->id : '')) 
                    ]) }}
                        {!! csrf_field() !!}
                        <div class="edit-field" style="display: flex;">
                            <div style="flex:1;">

                                <div class="education-wrap">
                                    @if($item->education_info)
                                        @foreach($item->education_info as $educationInfo)
                                            <div class="flex flex-mobile">
                                                <input type="text" name="education_info[]" autocomplete="off" class="input" value="{{ $educationInfo }}" maxlength="300">
                                                <a href="javascript:;" class="remove-education-info">
                                                    <img class="close-icon" src="{{ url('img-trp/close-icon-blue.png') }}" width="10"/>
                                                </a>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="flex flex-mobile">
                                            <input type="text" name="education_info[]" autocomplete="off" class="input" value="" maxlength="300">
                                            <a href="javascript:;" class="remove-education-info">
                                                <img class="close-icon" src="{{ url('img-trp/close-icon-blue.png') }}" width="10"/>
                                            </a>
                                        </div>
                                    @endif
                                                                                                                                
                                    <a href="javascript:;" class="add-education-info">
                                        + Add another education / career highlight
                                    </a>
                                </div>
                            </div>

                            <button type="submit" class="save-field">
                                <img src="{{ url('img-trp/white-check.svg') }}" width="20" height="15"/>
                            </button>
                        </div>
                        <input type="hidden" name="field" value="education_info"/>
                        <input type="hidden" name="json" value="1" />
                    {!! Form::close() !!}
                @endif
            </div>
        </div>
    @endif

    @if($item->experience || $item->languages || $item->founded_at || $loggedUserAllowEdit)
        <div class="tab-inner-section flex">
            @if($item->is_clinic)
                @if(($item->founded_at || $loggedUserAllowEdit))
                    <div class="founded-wrapper">
                        <h3>
                            Founded
                            @if($loggedUserAllowEdit)
                                <a href="javascript:;" class="edit-field-button tooltip-text toggle-section" toggle-section="founded-wrapper" text="{{ $item->founded_at ? 'Edit' : 'Add' }} foundation date">
                                    <img src="{{ url('img-trp/pencil.svg') }}" width="20" height="17">
                                </a>
                            @endif
                        </h3>

                        @if($item->founded_at)
                            <div class="chosen-founded">
                                {{ $item->founded_at ? date('d M Y', $item->founded_at->timestamp) : '' }}
                            </div>
                        @endif

                        @if($loggedUserAllowEdit)
                            {{ Form::open([
                                'class' => 'edit-founded-form',
                                'method' => 'post', 
                                'url' => getLangUrl('profile/info/'.($editing_branch_clinic ? $editing_branch_clinic->id : '')) 
                            ]) }}
                                {!! csrf_field() !!}
                                <div class="founded-flex flex">
                                    <img src="{{ url('img-trp/calendar.svg') }}" width="22" class="calendar"/>
                                    <input type="text" class="form-control datepicker" name="founded_at" value="{{ $item->founded_at ? date('d M Y', $item->founded_at->timestamp) : '' }}" autocomplete="off">
                                    <button type="submit" class="save-field">
                                        <img src="{{ url('img-trp/white-check.svg') }}" width="20" height="15"/>
                                    </button>
                                </div>
                                <input type="hidden" name="field" value="founded_at"/>
                                <input type="hidden" name="json" value="1" />
                            {!! Form::close() !!}
                        @endif
                    </div>
                @endif
            @else
                @if(($item->experience || $loggedUserAllowEdit))
                    <div class="experience-wrapper">
                        <h3>
                            Experience
                            @if($loggedUserAllowEdit)
                                <a href="javascript:;" class="edit-field-button tooltip-text toggle-section" toggle-section="experience-wrapper" text="{{ $item->experience ? 'Edit' : 'Add' }} experience">
                                    <img src="{{ url('img-trp/pencil.svg') }}" width="20" height="17">
                                </a>
                            @endif
                        </h3>

                        @if($item->experience)
                            <div class="chosen-experience">
                                <span class="bubble">
                                    {{ config('trp.experience')[$item->experience] }}
                                </span>
                            </div>
                        @endif

                        @if($loggedUserAllowEdit)
                            {{ Form::open([
                                'class' => 'edit-experience-form',
                                'method' => 'post', 
                                'url' => getLangUrl('profile/info/'.($editing_branch_clinic ? $editing_branch_clinic->id : '')) 
                            ]) }}
                                {!! csrf_field() !!}
                                <div class="experiences-list">
                                    @foreach(config('trp.experience') as $k => $v)
                                        <label class="bubble {{ $k == $item->experience ? 'active' : '' }}" for="cat-{{ $k }}">
                                            {{ $v }}
                                            <input 
                                                type="radio"
                                                id="cat-{{ $k }}" 
                                                name="experience" 
                                                class="checkbox"
                                                value="{{ $k }}"
                                                {{ $k == $item->experience ? 'checked="checked"' : '' }}
                                            >
                                        </label>
                                    @endforeach
                                </div>
                                <input type="hidden" name="field" value="experience"/>
                                <input type="hidden" name="json" value="1" />
                            {!! Form::close() !!}
                        @endif
                    </div>
                @endif
            @endif
            @if($item->languages || $loggedUserAllowEdit)
                <div class="languages-wrapper {{ $item->experience || $item->founded_at || $loggedUserAllowEdit ? '' : 'without-padding' }}">
                    <h3>
                        Languages spoken <img src="{{ url('img-trp/info-dark-gray.png') }}" class="tooltip-text" text="{!! $item->is_clinic ? 'Languages spoken in the dental practice.' : 'Languages spoken by the dentist.' !!}"/>

                        @if($loggedUserAllowEdit)
                            <a href="javascript:;" class="edit-field-button tooltip-text toggle-section" toggle-section="languages-wrapper" text="{{ $item->languages ? 'Edit' : 'Add' }} languages">
                                <img src="{{ url('img-trp/pencil.svg') }}" width="20" height="17">
                            </a>
                        @endif
                    </h3>
                    @if($item->languages)
                        @foreach(config('trp.languages') as $language)
                            @if(in_array(strtolower($language), $item->languages))
                                <span class="bubble">
                                    {{ $language }}
                                    <a href="javascript:;" class="remove-lang" language="{{ strtolower($language) }}">
                                        <img class="close-icon" src="{{ url('img-trp/close-icon-blue.png') }}" width="10"/>
                                    </a>
                                </span>
                            @endif
                        @endforeach
                    @endif
                    @if($loggedUserAllowEdit)
                        {{ Form::open([
                            'class' => 'edit-languages', 
                            'method' => 'post', 
                            'url' => getLangUrl('profile/info/'.($editing_branch_clinic ? $editing_branch_clinic->id : '')),
                            'remove-url' => getLangUrl('profile/lang-delete/'.($editing_branch_clinic ? $editing_branch_clinic->id : '')) 
                        ]) }}
                            {!! csrf_field() !!}
                            <select id="dentist-languages" name="languages" {!! !$item->languages || (count($item->languages) != count(config('trp.languages'))) ? '' : 'style="display:none;"' !!}>
                                <option value="">+ Add languages</option>
                                @foreach(config('trp.languages') as $l => $language)
                                    <option {!! !$item->languages || !in_array($l, $item->languages) ? '' : 'class="hidden-option"' !!} value="{{ $l }}">{{ $language }}</option>
                                @endforeach
                            </select>
                            <input type="hidden" name="field" value="languages" />
                            <input type="hidden" name="json" value="1" />
                        {!! Form::close() !!}
                    @endif
                </div>
            @endif
        </div>
    @endif
</div>