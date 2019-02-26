@extends('admin')

@section('content')

<h1 class="page-header">
    Export survey data
</h1>
<!-- end page-header -->


<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <div class="panel-heading-btn">
                    <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                </div>
                <h4 class="panel-title"> Pick a survey </h4>
            </div>
            <div class="panel-body">
                <form method="post" action="{{ url('cms/vox/export-survey-data') }}" id="export-survey-data-form">
                    {!! csrf_field() !!}
                    <div class="row" style="margin-bottom: 20px;">
                        <div class="col-md-6">
                            <input type="text" class="form-control datepicker" name="date-from" value="" placeholder="Date from">
                        </div>
                        <div class="col-md-6">
                            <input type="text" class="form-control datepicker" name="date-to" value="" placeholder="Date to">
                        </div>
                    </div>
                    <div class="row" style="margin-bottom: 20px;">
                        <div class="col-md-6">
                            <select class="form-control" name="country_id">
                                <option value="">-</option>
                                @foreach(\App\Models\Country::get() as $country)
                                    <option value="{{ $country->id }}" code="{{ $country->code }}" >{{ $country->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <select class="form-control" name="survey">
                                @foreach($voxes as $vox)
                                    <option value="{{ $vox->id }}">{{ $vox->title }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row" style="margin-bottom: 20px;">
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-sm btn-success btn-block">Submit</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


@endsection