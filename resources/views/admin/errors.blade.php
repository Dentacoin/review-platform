@if (count($errors) > 0)
    <!-- Form Error List -->
    <div class="alert alert-danger m-b-15">
        <strong>Error</strong>
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
    <div class="alert alert-danger m-b-15">
        <strong>{{ Session::get('error-message') }}</strong>
    </div>
@endif
@if(Session::has('success-message'))
    <!-- Form Success List -->
    <div class="alert alert-success m-b-15">
        <strong>{{ Session::get('success-message') }}</strong>
    </div>
@endif

<div id="error-message" class="alert alert-danger m-b-15" style="display: none;">
</div>
