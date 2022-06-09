@if($withoutUser)
    {!! Form::open([
        'method' => 'post', 
        'class' => 'invite-dentist-form', 
        'url' => getLangUrl('invite-dentist') 
    ]) !!}
        {!! csrf_field() !!}

        <input type="hidden" name="last_user_id" value=""/>
        <input type="hidden" name="last_user_hash" value=""/>
@endif

    <div class="dentist-suggester-wrapper suggester-wrapper">
        <input 
        type="text" 
        name="invitedentist" 
        class="input dentist-suggester suggester-input" 
        value="" 
        autocomplete="off"
        placeholder="Search for registered dental professionals">

        <div class="suggest-results"></div>

        <input 
        type="hidden" 
        class="suggester-hidden" 
        name="dentist_id" 
        value="" 
        url="{{ getLangUrl('invite-dentist') }}"/>
    </div>

    <div class="alert alert-success alert-success-d" style="display: none; margin-top: 20px;"></div>
    <div class="alert alert-warning alert-warning-d" style="display: none; margin-top: 20px;"></div>

    @if($withoutUser)
        <a href="javascript:;" class="invite-manual">
            <img src="{{ url('img-trp/add-icon-in-button.png') }}" width="27"/>
            Invite non-registered dentist via email
        </a>
        
    {!! Form::close() !!}

@else
    <p class="popup-desc">
        {!! nl2br(trans('trp.popup.add-team-popup.invite')) !!}:
    </p>
@endif

<div class="add-team-manual" style="{{ $withoutUser ? 'display: none;' : '' }}">
    {!! Form::open([
        'method' => 'post', 
        'files'=> true, 
        'class' => 'search-dentist-form add-team-member-form', 
        'url' => getLangUrl('profile/invite-new') 
    ]) !!}
        {!! csrf_field() !!}

        @if($withoutUser)
            <input type="hidden" name="last_user_id" value=""/>
            <input type="hidden" name="last_user_hash" value=""/>
        @endif

        <div class="flex">
            <input type="hidden" name="check-for-same" class="check-for-same"/>
            <div class="upload-image-wrapper">
                <label for="add-avatar-member" class="image-label team-label-image">
                    <div class="plus-gallery-image">
                        <img class="add-gallery-icon" src="{{ url('img-trp/add-icon.png') }}">
                        <span>
                            Add image
                            
                            <img 
                            src="{{ url('img-trp/info-dark-gray.png') }}" 
                            class="tooltip-text" text="Required resolution: 150x150px<br/> Max. image size: 2 MB"/>
                        </span>
                    </div>
                    <div class="loader">
                        <i></i>
                    </div>
                    <input 
                    type="file" 
                    name="image" 
                    class="add-avatar-member" 
                    accept="image/png,image/jpeg,image/jpg" 
                    id="add-avatar-member" 
                    upload-url="{{ getLangUrl('register/upload') }}"/>
                    <input type="hidden" name="avatar" class="avatar"/>
                </label>
                
                <div class="cropper-container add-team-cropper"></div>
                <div class="avatar-name-wrapper">
                    <span class="avatar-name"></span>
                    <button class="destroy-croppie" type="button">×</button>
                </div>
                <div class="alert alert-warning image-big-error" style="display: none; margin-top: 20px;">The file you selected is large. Max size: 2MB.</div>
            </div>
            <div class="col">
                <div class="modern-field">
                    <input 
                    type="text" 
                    class="modern-input team-member-name" 
                    id="team-member-name" 
                    name="name"/>
                    <label for="team-member-name">
                        {{-- <span>{{ trans('trp.popup.verification-popup.add-team-name') }}</span> --}}
                        <span>Enter name</span>
                    </label>
                </div>
                
                <div class="modern-field alert-after">
                    <select name="team-job" id="team-member-job" class="modern-input team-member-job">
                        @foreach(config('trp.team_jobs') as $k => $v)
                            <option value="{{ $k }}">{{ trans('trp.team-jobs.'.$k) }}</option>
                        @endforeach
                    </select>
                    <label for="team-member-job">
                        {{-- <span>{{ trans('trp.popup.verification-popup.add-team-position') }}:</span> --}}
                        <span>Select Position:</span>
                    </label>
                </div>
            </div>
        </div>
        <div class="flex">
            <div class="col mail-col dentist-col" style="display: none;">
                <div class="modern-field">
                <input 
                type="email" 
                class="modern-input team-member-email" 
                id="team-member-email" 
                name="email" 
                placeholder="{{ trans('trp.common.optional') }}"/>
                <label for="team-member-email">
                    {{-- <span>{{ trans('trp.popup.verification-popup.add-team-email') }}</span> --}}
                    <span>Enter Email:</span>
                </label>
            </div>
            </div>
            <div class="col specializations-col dentist-col" style="display: none;">
                <div class="modern-field alert-after">
                    <select name="team-speciality" id="team-member-speciality" class="modern-input team-member-speciality">
                        @foreach(config('categories') as $k => $v)
                            <option value="{{ $k }}">{{ trans('trp.categories.'.$v) }}</option>
                        @endforeach
                    </select>
                    <label for="team-member-speciality">
                        {{-- <span>{{ trans('trp.popup.verification-popup.add-team-position') }}:</span> --}}
                        <span>Select specialty:</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="alert member-alert" style="display: none; margin-top: 20px;"></div>
        <div class="tac">
            @if($withoutUser)
                <a href="javascript:;" class="invite-existing-dentist">Cancel</a>
            @endif
            {{-- <input type="submit" class="button" value="{{ trans('trp.popup.verification-popup.add-team-button') }}"> --}}
            <input type="submit" class="green-button" value="Invite dentist">
        </div>
    {!! Form::close() !!}
</div>