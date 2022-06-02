<div class="tab-container" id="about">
    <h2 class="mont">
        About
        {{-- {!! nl2br(trans('trp.page.user.about-who',[
            'name' => $item->getNames()
        ])) !!} --}}
    </h2>

    <div class="tab-inner-section checkbox-section specializations-section">
        @if($item->categories->isNotEmpty() || ($loggedUserAllowEdit))
            <h3>
                Specialities

                @if($loggedUserAllowEdit)
                    <a class="edit-field-button edit-specializations tooltip-text" text="{{ $item->categories->isNotEmpty() ? 'Edit' : 'Add' }} specialities">
                        <img src="{{ url('img-trp/pencil.svg') }}" width="20" height="17">
                    </a>
                @endif
            </h3>
            @if($loggedUserAllowEdit)
                {{ Form::open([
                    'class' => 'edit-checkboxes-form',
                    'method' => 'post', 
                    'url' => getLangUrl('profile/info/'.($editing_branch_clinic ? $editing_branch_clinic->id : '')) 
                ]) }}
                    {!! csrf_field() !!}
            @endif
            <div class="checkboxes-wrapper specializations">
                @foreach($item->categories as $specialization)
                    <label class="specialization" for="cat-{{ $specialization->category_id }}">
                        {{ trans('trp.categories.'.config('categories.'.$specialization->category_id)) }}
                        @if($loggedUserAllowEdit)
                            <input 
                                type="checkbox"
                                id="cat-{{ $specialization->category_id }}" 
                                name="specialization[]" 
                                value="{{ $specialization->category_id }}" 
                                checked="checked"
                            >
                            <a href="javascript:;" class="remove-checkbox">
                                <img class="close-icon" src="{{ url('img-trp/close-icon-blue.png') }}" width="10"/>
                            </a>
                        @endif
                    </label>
                @endforeach
            </div>
            @if($loggedUserAllowEdit)
                    <div class="checkboxes-wrapper specializations not-added">
                        @foreach($categories as $k => $v)
                            @if(!in_array($loop->index, $user->categories->pluck('category_id')->toArray()))
                                <label class="specialization" for="cat-{{ $k }}" >
                                    {{ $v }}
                                    <input 
                                        type="checkbox"
                                        id="cat-{{ $k }}" 
                                        name="specialization[]" 
                                        value="{{ array_search($k, config('categories')) }}" 
                                    >
                                    <a href="javascript:;" class="remove-checkbox">
                                        <img class="close-icon" src="{{ url('img-trp/close-icon-blue.png') }}" width="10"/>
                                    </a>
                                </label>
                            @endif
                        @endforeach
                    </div>
                    <input type="hidden" name="field" value="specialization"/>
                    <input type="hidden" name="json" value="1" />
                    <button type="submit" class="blue-button">
                        {!! nl2br(trans('trp.page.user.save')) !!}
                    </button>
                {!! Form::close() !!}
            @endif
        @endif
    </div>
    
    <div class="tab-inner-section">
        @if($item->description || ($loggedUserAllowEdit) )
            <h3>
                Introduction

                @if($loggedUserAllowEdit)
                    <a href="javascript:;" class="edit-field-button edit-description-button tooltip-text" text="Tell patients more about your dental practice.">
                        <img src="{{ url('img-trp/pencil.svg') }}" width="20" height="17">
                    </a>
                @endif
            </h3>
            <div class="about-content" role="presenter">
                <span class="value-here description" empty-value="{{ nl2br(trans('trp.page.user.description-empty')) }}">
                    {!! $item->description ? nl2br($item->description) : '' !!}
                </span>
            </div>
            @if($loggedUserAllowEdit)
                <div class="about-content" role="editor" id="edit-descr-container" style="display: none; padding: 5px;">
                    {{ Form::open([
                        'class' => 'edit-description', 
                        'method' => 'post', 
                        'url' => getLangUrl('profile/info/'.($editing_branch_clinic ? $editing_branch_clinic->id : '')) 
                    ]) }}
                        {!! csrf_field() !!}
                        <div class="flex">
                            {{-- {!! nl2br(trans('trp.page.user.description-placeholder')) !!} --}}
                            <textarea 
                            class="input" 
                            name="description" 
                            id="dentist-description" 
                            placeholder="Tell patients more about your dental practice. (max. 512 characters)"
                            >{{ $item->description }}</textarea>
                            <button type="submit" class="save-field skip-step">
                                <img src="{{ url('img-trp/white-check.svg') }}" width="20" height="15"/>
                            </button>
                        </div>
                        <input type="hidden" name="field" value="description" />
                        <input type="hidden" name="json" value="1" />
                        <div class="alert alert-warning" style="display: none;"></div>
                    {!! Form::close() !!}
                </div>
            @endif
        @endif
        
    </div>

    <div class="tab-inner-section checkbox-section payments-section">
        @if($item->accepted_payment || ($loggedUserAllowEdit))
            <h3>
                Payment methods

                @if($loggedUserAllowEdit)
                    <a class="edit-field-button edit-payments tooltip-text" text="{{ $item->accepted_payment ? 'Edit' : 'Add' }} payment methods">
                        <img src="{{ url('img-trp/pencil.svg') }}" width="20" height="17">
                    </a>
                @endif
            </h3>
            @if($loggedUserAllowEdit)
                {{ Form::open([
                    'class' => 'edit-checkboxes-form',
                    'method' => 'post', 
                    'url' => getLangUrl('profile/info/'.($editing_branch_clinic ? $editing_branch_clinic->id : '')) 
                ]) }}
                    {!! csrf_field() !!}
            @endif
            <div class="checkboxes-wrapper dentist-payments">
                @foreach(config('trp.accepted_payment') as $acceptedPayment)
                    @if(in_array($acceptedPayment, $item->accepted_payment))
                        <label class="payment {{ $item->wallet_addresses->isNotEmpty() && $item->is_partner && $acceptedPayment == 'dentacoin' ? 'open-my-account' : '' }}" for="payment-{{ $acceptedPayment }}">
                            <img src="{{ url('img-trp/payment-methods/'.$acceptedPayment.'.svg') }}"/>
                            {!! trans('trp.accepted-payments.'.$acceptedPayment) !!}
                            @if($loggedUserAllowEdit)
                                <input 
                                    type="checkbox"
                                    id="payment-{{ $acceptedPayment }}" 
                                    name="accepted_payment[]" 
                                    value="{{ $acceptedPayment }}"
                                    checked="checked" 
                                >
                                <a href="javascript:;" class="remove-checkbox">
                                    <img class="close-icon" src="{{ url('img-trp/close-icon-blue.png') }}" width="10"/>
                                </a>
                            @endif
                        </label>
                    @endif
                @endforeach
            </div>
            @if($loggedUserAllowEdit)
                    <div class="checkboxes-wrapper dentist-payments not-added">
                        @foreach(config('trp.accepted_payment') as $k => $acceptedPayment)
                            @if(!in_array($acceptedPayment, $user->accepted_payment))
                                <label class="payment" for="payment-{{ $acceptedPayment }}">
                                    <img src="{{ url('img-trp/payment-methods/'.$acceptedPayment.'.svg') }}"/>
                                    {!! trans('trp.accepted-payments.'.$acceptedPayment) !!}
                                    <input
                                        type="checkbox"
                                        id="payment-{{ $acceptedPayment }}" 
                                        name="accepted_payment[]" 
                                        value="{{ $acceptedPayment }}" 
                                    >
                                    <a href="javascript:;" class="remove-checkbox">
                                        <img class="close-icon" src="{{ url('img-trp/close-icon-blue.png') }}" width="10"/>
                                    </a>
                                </label>
                            @endif
                        @endforeach
                    </div>
                    <input type="hidden" name="field" value="accepted_payment" />
                    <input type="hidden" name="json" value="1" />
                    <button type="submit" class="blue-button">
                        {!! nl2br(trans('trp.page.user.save')) !!}
                    </button>
                    <div class="alert alert-warning" style="display: none;"></div>
                {!! Form::close() !!}
            @endif

            @if($loggedUserAllowEdit && $item->wallet_addresses->isEmpty() && $item->is_partner && !$editing_branch_clinic)
                <br/>
                <a href="javascript:;" class="open-popup-wallet-address"  data-popup="add-wallet-address">+ Аdd wallet address to receive DCN payments</a>
            @endif
        @endif
    </div>
</div>