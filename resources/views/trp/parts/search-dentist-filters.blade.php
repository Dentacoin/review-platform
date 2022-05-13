<span href="javascript:;" class="filter {{ !empty($searchCategories) ? 'active' : '' }}">
    Speciality
    <div class="caret-down"></div>

    <div class="filter-options">
        @foreach( config('categories') as $cat_id => $cat )
            @php
                $active = !empty($searchCategories) && in_array($cat, $searchCategories) ? true : false;
            @endphp
            <label 
                class="checkbox-label{!! $active ? ' active' : '' !!}" 
                for="checkbox-filter-{{ $cat }}"
            >
                <div class="flex flex-mobile flex-center space-between">
                    <div>
                        <input type="checkbox" 
                            class="special-checkbox specializations"
                            id="checkbox-filter-{{ $cat }}" 
                            value="{{ $cat }}" 
                            {!! $active ? 'checked="checked"' : '' !!}
                        >
                        <div class="checkbox-square">✓</div>
                        {{ trans('trp.categories.'.$cat) }}

                        @if(in_array($cat, ['orthodontists', 'periodontists', 'pediatric-dentists', 'endodontists']))
                            <div class="specialization-info">
                                <img class="" src="{{ url('img-trp/info-gray.svg') }}"/>

                                @if($cat == 'orthodontists')
                                    <p class="info-tooltip">Specialized in the prevention, diagnosis, and correction of mal-positioned teeth and misaligned bites.</p>
                                @elseif($cat == 'periodontists')
                                    <p class="info-tooltip">Specialized in the prevention, diagnosis, and treatment of gum disease and the placement of dental implants.</p>
                                @elseif($cat == 'pediatric-dentists')
                                    <p class="info-tooltip short">Specialized in the prevention, diagnosis, and treatment of children’s dental issues.</p>
                                @elseif($cat == 'endodontists')
                                    <p class="info-tooltip">Specialized in the prevention, diagnosis and treatment of diseases related to the pulp / root canal.</p>
                                @endif
                            </div>
                        @endif
                    </div>
                    <span class="filter-count">
                        ({{ isset($dentistSpecialications[$cat_id]) ? $dentistSpecialications[$cat_id] : 0 }})
                    </span>
                </div>
            </label>
        @endforeach

        <div class="filter-buttons">
            <a class="clear-filters clear-specializations" href="javascript:;">
                {{-- {!! nl2br(trans('trp.page.search.reset')) !!} --}}
                Clear
            </a>
            <button type="submit" href="javascript:;" class="blue-button">
                {!! nl2br(trans('trp.page.search.apply')) !!}
            </button>
        </div>
    </div>
</span>
<span href="javascript:;" class="filter active">
    Type
    <div class="caret-down"></div>

    <div class="filter-options">
        @foreach($types as $key => $type)
            @php
                $active = empty($requestTypes) && $key == 'all' ? true : (!empty($requestTypes) && in_array($key, $requestTypes) ? true : false);
            @endphp
            <label class="checkbox-label {{ $active ? 'active' : '' }}" for="filter-dentists-{{$key}}">

                <div class="flex flex-mobile flex-center space-between">
                    <div>
                        <input 
                            type="checkbox" 
                            class="special-checkbox filter-type" 
                            name="types[]" 
                            id="filter-dentists-{{$key}}" 
                            value="{{$key}}" 
                            {!! $active ? 'checked="checked"' : '' !!}
                        >
                        <div class="checkbox-square">✓</div>
                        {{$type}}
                    </div>
                    <span class="filter-count">
                        ({{ isset($dentistTypes[$key]) ? $dentistTypes[$key] : 0 }})
                    </span>
                </div>
            </label>
        @endforeach

        <div class="filter-buttons">
            <a class="clear-filters" href="javascript:;">
                {{-- {!! nl2br(trans('trp.page.search.reset')) !!} --}}
                Clear
            </a>
            <button type="submit" href="javascript:;" class="blue-button">
                {!! nl2br(trans('trp.page.search.apply')) !!}
            </button>
        </div>
    </div>
</span>
{{-- <span href="javascript:;" class="filter">
    Insurance
</a> --}}
<span href="javascript:;" class="filter {{ !empty($requestRatings) ? 'active' : '' }}">
    Rating
    <div class="caret-down"></div>

    <div class="filter-options">

        @foreach($ratings as $key => $rating)
            @php
                $active = !empty($requestRatings) && in_array($key, $requestRatings) ? true : false;
            @endphp
            <label class="checkbox-label {{ $active ? 'active' : '' }}" for="ratings-{{$key}}">

                <div class="flex flex-mobile flex-center space-between">
                    <div>
                        <input 
                            type="checkbox" 
                            class="special-checkbox" 
                            name="ratings[]" 
                            id="ratings-{{$key}}" 
                            value="{{$key}}"
                            {!! $active ? 'checked="checked"' : '' !!}
                        >
                        <div class="checkbox-square">✓</div>
                        {{$rating}}
                    </div>
                    <span class="filter-count">
                        ({{ isset($dentistRatings[$key]) ? $dentistRatings[$key] : 0 }})
                    </span>
                </div>
            </label>
        @endforeach

        <div class="filter-buttons">
            <a class="clear-filters" href="javascript:;">
                {{-- {!! nl2br(trans('trp.page.search.reset')) !!} --}}
                Clear
            </a>
            <button type="submit" href="javascript:;" class="blue-button">
                {!! nl2br(trans('trp.page.search.apply')) !!}
            </button>
        </div>
    </div>
</span>

@if(false)
    <span href="javascript:;" class="filter {{ !empty($requestAvailability) ? 'active' : '' }}">
        More filters
        <div class="caret-down"></div>

        <div class="filter-options longer">
            <div class="filter-inner">
                <div class="filter-title">
                    Appointment types:
                </div>
                <label class="checkbox-label disabled" for="virtual-visit">
                    <input type="checkbox" class="special-checkbox" name="visits[]" id="virtual-visit" value="virtual" disabled="disabled">
                    <div class="checkbox-square">✓</div>
                    Virtual 
                </label>
                <label class="checkbox-label disabled" for="on--visit">
                    <input type="checkbox" class="special-checkbox" name="visits[]" id="on--visit" value="on-site" disabled="disabled">
                    <div class="checkbox-square">✓</div>
                    On-site visit
                </label>
                <div class="filter-title">
                    Languages spoken:
                </div>
                @foreach($languages as $key => $l)
                    <label class="checkbox-label disabled" for="lang-{{$key}}">
                        <input type="checkbox" class="special-checkbox" name="languages[]" id="lang-{{$key}}" value="{{$key}}" disabled="disabled">
                        <div class="checkbox-square">✓</div>
                        {{$l}}
                    </label>
                @endforeach
                <div class="filter-title">
                    Experience:
                </div>
                @foreach($experiences as $key => $experience)
                    <label class="checkbox-label disabled" for="experience-{{$key}}">
                        <input type="checkbox" class="special-checkbox" name="experience[]" id="experience-{{$key}}" value="{{$key}}" disabled="disabled">
                        <div class="checkbox-square">✓</div>
                        {{$experience}}
                    </label>
                @endforeach
                <div class="filter-title">
                    Availability:
                </div>
                @foreach($availabilities as $key => $availability)
                    @php
                        $active = !empty($requestAvailability) && in_array($key, $requestAvailability) ? true : false;
                    @endphp
                    <label class="checkbox-label {!! $active ? 'active' : '' !!}" for="availability-{{$key}}">
                        <div class="flex flex-mobile flex-center space-between">
                            <div>
                                <input 
                                    type="checkbox" 
                                    class="special-checkbox" 
                                    name="availability[]" 
                                    id="availability-{{$key}}" 
                                    value="{{$key}}" 
                                    {!! $active ? 'checked="checked"' : '' !!}
                                >
                                <div class="checkbox-square">✓</div>
                                {{$availability}}
                            </div>
                            <span class="filter-count">
                                ({{ isset($dentistAvailability[$key]) ? $dentistAvailability[$key] : 0 }})
                            </span>
                        </div>
                    </label>
                @endforeach
            </div>
            <div class="filter-buttons">
                <a class="clear-filters" href="javascript:;">
                    {{-- {!! nl2br(trans('trp.page.search.reset')) !!} --}}
                    Clear
                </a>
                <button type="submit" href="javascript:;" class="blue-button">
                    {!! nl2br(trans('trp.page.search.apply')) !!}
                </button>
            </div>
        </div>

    </span>
@endif

<span class="break-mobile">
    <span class="sort-by-title">
        Sort by:
    </span>
    <span href="javascript:;" class="filter active">
        <span class="filter-order-active-text">{{ empty($requestOrder) ? 'Stars (highest first)' : $orders[$requestOrder] }}</span>
        <div class="caret-down"></div>

        <div class="filter-options">
            @foreach($orders as $key => $order)
                @php
                    $active = empty($requestOrder) && $key == 'avg_rating_desc' ? true : ($requestOrder == $key ? true : false);
                @endphp

                <label class="checkbox-label {!! $active ? 'active' : '' !!}" for="order-{{$key}}" label-text="{{$order}}">
                    <input 
                        type="radio" 
                        class="special-checkbox filter-order" 
                        name="order" 
                        id="order-{{$key}}" 
                        value="{{$key}}"
                        {!! $active ? 'checked="checked"' : '' !!}
                    >
                    <div class="checkbox-square">✓</div>
                    {{$order}}
                </label>
            @endforeach

            <div class="filter-buttons">
                <a class="clear-filters" href="javascript:;">
                    {{-- {!! nl2br(trans('trp.page.search.reset')) !!} --}}
                    Clear
                </a>
                <button type="submit" href="javascript:;" class="blue-button">
                    {!! nl2br(trans('trp.page.search.apply')) !!}
                </button>
            </div>
        </div>
    </span>
</span>
<a href="javascript:;" class="filter clear-all-filters">
    Clear Filters
</a>