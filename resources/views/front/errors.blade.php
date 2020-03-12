@if (count($errors) > 0)
    <!-- Form Error List -->
    <div class="alert alert-warning">
        <strong>Please fix the following errors</strong>
        <br><br>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
@if(Session::has('error-message'))
    <!-- Form Error List -->
    <div class="alert alert-warning">
        <strong>{!! Session::get('error-message') !!}</strong>
    </div>
@endif
@if(request('error-message'))
    <!-- Form Error List -->
    <div class="alert alert-warning">
        <strong>{!! str_replace( '[/endlink]', '</a>', str_replace('[startlink]', '<a href="https://vox.dentacoin.com/en/registration">', request('error-message'))) !!}</strong>
    </div>
@endif
@if(Session::has('success-message'))
    <!-- Form Success List -->
    <div class="alert alert-success">
        <strong>{!! Session::get('success-message') !!}</strong>
    </div>
@endif
@if(request('success-message'))
    <!-- Form Success List -->
    <div class="alert alert-success">
        <strong>{!! request('success-message') !!}</strong>
    </div>
@endif