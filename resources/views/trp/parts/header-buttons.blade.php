
@if(empty($user))

    <a href="{{ getLangUrl('welcome-dentist') }}" class="transparent-blue-button">
        {{-- {!! trans('trp.header.for-dentists') !!} --}}
        List your practice
    </a>
    <div class="header-buttons-wrapper">
        <a href="javascript:;" class="header-text-link open-dentacoin-gateway {{ $current_page!='welcome-dentist' ? 'patient-login' : 'dentist-login' }}">
            {{ trans('trp.header.login') }}
        </a>
        @if($current_page!='welcome-dentist')
            <a href="javascript:;" class="header-text-link open-dentacoin-gateway {{ $current_page!='welcome-dentist' ? 'patient-register' : 'dentist-register' }}">
                {{-- {{ trans('trp.header.signin') }} --}}
                Sign up
            </a>
        @endif
    </div>
@endif