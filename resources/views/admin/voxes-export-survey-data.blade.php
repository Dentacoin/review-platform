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
                    <div class="row">
                        <div class="col-md-6">
                            <input type="text" class="form-control datepicker" name="date-from" value="" autocomplete="off" placeholder="Date from" style="margin-bottom: 10px;">
                            <input type="text" class="form-control datepicker" name="date-to" value="" autocomplete="off" placeholder="Date to" style="margin-bottom: 10px;">
                            <select class="form-control select2" name="survey">
                                @foreach($voxes as $vox)
                                    <option value="{{ $vox->id }}">{{ $vox->title }}</option>
                                @endforeach
                            </select>
                            <br/>
                            <div class="form-group clearfix" id="stat_standard" style="margin-top: 10px;">
                                <label class="col-md-2 control-label">Demographics</label>
                                <div class="col-md-10">
                                    @foreach( config('vox.stats_scales') as $k => $v)
                                        @if($k != 'gender' && $k != 'age' && $k != 'country_id')
                                            <label for="demographics-{{ $k }}">
                                                <input type="checkbox" name="demographics[]" value="{{ $k }}" id="demographics-{{ $k }}" style="vertical-align: sub;" />
                                                {{ trans('vox.page.stats.group-by-'.$k) }} &nbsp;&nbsp;&nbsp;&nbsp;
                                            </label>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <select class="form-control" name="country_id[]" multiple style="min-height: 300px;">
                                @foreach(\App\Models\Country::with('translations')->get() as $country)
                                    <option value="{{ $country->id }}" >{{ $country->name }}</option>
                                @endforeach
                            </select>
                            <p style="margin-top: 5px;">
                                * Hold down the control (ctrl) button to select multiple options <br>
                                * If you want to select all countries, don't select anything
                            </p>
                        </div>
                    </div>
                    <div class="row">
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