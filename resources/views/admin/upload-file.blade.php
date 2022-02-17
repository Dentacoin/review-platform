@extends('admin')

@section('content')

    <h1 class="page-header">Upload</h1>
    <!-- end page-header -->

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <h4 class="panel-title">Upload</h4>
                </div>
                <div class="panel-body" id="link">

                    <form method='post' action='' enctype='multipart/form-data'>
                        <input type="file" name="file[]" id="file" multiple>
                        <input type='submit' name='submit' value='Upload'>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection