<div class="tab-container" id="locations">
    <h2 class="mont">
        Location{{ $item->branches->isNotEmpty() ? 's' : '' }}
        {{-- {!! nl2br(trans('trp.page.user.about-who',[
            'name' => $item->getNames()
        ])) !!} --}}

        @if($loggedUserAllowEdit)
            <a class="edit-field-button edit-locations tooltip-text" text="{{ $item->accepted_payment ? 'Edit' : 'Add' }} location{{ $item->branches->isNotEmpty() ? 's' : '' }}">
                <img src="{{ url('img-trp/pencil.svg') }}" width="20" height="17"/>
            </a>
        @endif
    </h2>

    <div class="tab-inner-section location-section">
        <div class="col">

            @php
                $branchesWithLocation = 0;

                if($item->branches->isNotEmpty()) {
                    foreach($item->branches as $itemBranch) {
                        if($itemBranch->branchClinic->lat && $itemBranch->branchClinic->lon) {
                            $branchesWithLocation++;
                        }
                    }
                }
            @endphp

            @if($branchesWithLocation)
                <div class="address-flickity-wrapper">
                    <p class="carousel-status">1 of {{ $item->lat && $item->lon ? $branchesWithLocation+1 : $branchesWithLocation }}</p>
                    <div class="address-flickity">
                        @if($item->lat && $item->lon)
                            <div class="address-slider">
                                <p class="branch-name">{{ $item->getNames() }}</p>
                                <p class="edited-field" id="value-address-map" style="display: inline-block;">
                                    {{ $item->address ? $item->address.', ' : '' }} {{ $item->country_id ? $item->country->name : '' }}
                                </p>
                                <div class="map-container map-for-branch profile-map" id="profile-map-0" lat="{{ $item->lat }}" lon="{{ $item->lon }}"></div>
                            </div>
                        @endif
                        @foreach($item->branches as $itemBranch)
                            @if($itemBranch->branchClinic->lat && $itemBranch->branchClinic->lon)
                                <div class="address-slider">
                                    <p class="branch-name">{{ $itemBranch->branchClinic->getNames() }}</p>
                                    <p class="edited-field" id="value-address-map" style="display: inline-block;">
                                        {{ $itemBranch->branchClinic->address ? $itemBranch->branchClinic->address.', ' : '' }} {{ $itemBranch->branchClinic->country->name }}
                                    </p>
                                    <div class="map-container map-for-branch profile-map" id="profile-map-{{ $loop->iteration }}" lat="{{ $itemBranch->branchClinic->lat }}" lon="{{ $itemBranch->branchClinic->lon }}"></div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @else
            
                @if( ($item->lat && $item->lon) || $loggedUserAllowEdit )
                    <div class="edit-field map-address">
                        <p class="edited-field" id="value-address-map" style="display: inline-block;">
                            {{ $item->address ? $item->address.', ' : '' }} {{ $item->country_id ? $item->country->name : '' }}
                        </p>

                        @if($loggedUserAllowEdit)
                            {{ Form::open([
                                'class' => 'edit-wrapper address-suggester-wrapper-input', 
                                'method' => 'post', 
                                'url' => getLangUrl('profile/info/'.($editing_branch_clinic ? $editing_branch_clinic->id : '')) 
                            ]) }}
                                {!! csrf_field() !!}

                                <select name="country_id" id="dentist-country1" class="modern-input country-select" style="display: none">
                                    @if(!$country_id)
                                        <option>-</option>
                                    @endif
                                    @if(!empty($countries))
                                        @foreach( $countries as $country )
                                            <option value="{{ $country->id }}" code="{{ $country->code }}" {!! $country_id==$country->id ? 'selected="selected"' : '' !!} >{{ $country->name }}</option>
                                        @endforeach
                                    @endif
                                </select>

                                <div class="flex flex-mobile flex-center">
                                    <input 
                                    type="text" 
                                    name="address" 
                                    class="input address-suggester-input" 
                                    autocomplete="off" 
                                    placeholder="{!! nl2br(trans('trp.page.user.city-street')) !!}" 
                                    value="{{ $user->address }}"
                                    >
                                    <button type="submit" class="save-field">
                                        <img src="{{ url('img-trp/white-check.svg') }}" width="20" height="15"/>
                                    </button>
                                </div>
                                <div class="suggester-map-div" {!! $user->lat ? 'lat="'.$user->lat.'" lon="'.$user->lon.'"' : '' !!} style="height: 350px; display: none; margin: 10px 0px;">
                                </div>
                                <div class="alert alert-info geoip-confirmation mobile secondary-info" style="display: none; margin: 10px 0px;">
                                    {!! nl2br(trans('trp.common.check-address')) !!}
                                </div>
                                <div class="alert alert-warning geoip-hint mobile secondary-info" style="display: none; margin: 10px 0px;">
                                    {!! nl2br(trans('trp.common.invalid-address')) !!}
                                </div>
                                <div class="alert alert-warning different-country-hint mobile secondary-info" style="display: none; margin: -10px 0px 10px;">
                                    {!! nl2br(trans('trp.page.user.invalid-country')) !!}
                                </div>
                                
                                <div class="alert alert-warning mobile" style="display: none;"></div>
                                <input type="hidden" name="field" value="address" />
                                <input type="hidden" name="json" value="1" />
                                <input type="hidden" name="for_map" value="1" />
                            {!! Form::close() !!}
                        @endif
                    </div>
                    <div class="map-container profile-map" id="profile-map-0" lat="{{ $item->lat }}" lon="{{ $item->lon }}"></div>
                @endif
            @endif
        </div>
        @if($item->photos->isNotEmpty() || ($loggedUserAllowEdit) )
            <div class="gallery-slider col {!! count($item->photos) > 1 ? 'with-arrows' : '' !!}">
                @if($loggedUserAllowEdit)
                    @if($item->photos->count() < 10)
                        <div class="slider-wrapper add-gallery-wrapper">
                            {{ Form::open([
                                'class' => 'gallery-add', 
                                'method' => 'post', 
                                'files' => true
                            ]) }}
                                <label for="add-gallery-photo" class="add-gallery-image slider-image cover image-label dont-count" guided-action="photos">
                                    <div class="plus-gallery-image">
                                        <img class="add-gallery-icon" src="{{ url('img-trp/add-icon.png') }}"/>
                                        <span>
                                            Add image
                                            {{-- {!! nl2br(trans('trp.page.user.reviews-image')) !!} --}}
                                            <img src="{{ url('img-trp/info-dark-gray.png') }}" class="tooltip-text" text="Required resolution: 1920x1080px<br/> Max. image size: 2 MB"/>
                                        </span>
                                    </div>
                                    <div class="loader">
                                        <i></i>
                                    </div>
                                    <input 
                                        type="file" 
                                        name="image" 
                                        id="add-gallery-photo" 
                                        upload-url="{{ getLangUrl('profile/gallery/'.($editing_branch_clinic ? $editing_branch_clinic->id : '')) }}" 
                                        sure-trans="{!! trans('trp.page.user.gallery-sure') !!}" 
                                        accept="image/png,image/jpeg,image/jpg"
                                    >
                                </label>
                            {!! Form::close() !!}
                        </div>
                    @else
                        <div class="slider-wrapper add-gallery-wrapper">
                            <label class="add-gallery-image slider-image cover image-label">
                                <div class="plus-gallery-image">
                                    <span>
                                        Limit reached
                                    </span>
                                </div>
                            </label>
                        </div>
                    @endif
                @endif
                <div class="gallery-flickity">
                    @foreach($item->photos as $photo)
                        @if($photo)
                            <a href="{{ $photo->getImageUrl() }}" data-lightbox="user-gallery" class="slider-wrapper" photo-id="{{ $photo->id }}">
                                <div class="slider-image cover" style="background-image: url('{{ $photo->getImageUrl(true) }}')">
                                    @if( ($loggedUserAllowEdit) )
                                        <div class="delete-gallery delete-button" sure="{!! trans('trp.page.user.gallery-sure') !!}">
                                            <img class="close-icon" src="{{ url('img/close-icon-white.png') }}"/>
                                        </div>
                                    @endif
                                </div>
                            </a>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    @if($workingTime || $loggedUserAllowEdit)
        <div class="tab-inner-section open-hours-section">
            <h3>
                Open hours
                @if($loggedUserAllowEdit)
                    <a class="edit-field-button tooltip-text" text="{{ $workingTime ? 'Edit' : 'Add' }} open hours">
                        <img src="{{ url('img-trp/pencil.svg') }}" width="20" height="17"/>
                    </a>
                @endif
            </h3>
            @if($loggedUserAllowEdit)
                {!! Form::open([
                    'class' => 'edit-working-hours-form',
                    'method' => 'post', 
                    'url' => getLangUrl('profile/info/'.($editing_branch_clinic ? $editing_branch_clinic->id : '')) 
                ]) !!}
                    {!! csrf_field() !!}
            @endif

                @include('trp.parts.open-hours', [
                    'withoutUser' => false,
                ])
    
            @if( ($loggedUserAllowEdit) )
                <input type="hidden" name="json" value="1" />
                <input type="hidden" name="field" value="work_hours" />
                <button type="submit" class="blue-button">
                    {!! nl2br(trans('trp.page.user.save')) !!}
                </button>
                {!! Form::close() !!}
            @endif
            
        </div>
    @endif
</div>