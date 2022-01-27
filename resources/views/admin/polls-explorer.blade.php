@extends('admin')

@section('content')

<h1 class="page-header" id="respondents-sort">
    Daily Polls Respondents Explorer
</h1>
<!-- end page-header -->

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <div class="panel-heading-btn">
                    <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                </div>
                <h4 class="panel-title">Pick daily poll</h4>
            </div>
            <div class="panel-body">
                <form method="get" action="{{ url('cms/vox/polls-explorer') }}" >
                    <div class="row">
                        <div class="col-md-12">
                            <select class="form-control select2" name="question" id="explorer-survey">
                                <option value="">Select daily poll</option>
                                @foreach($polls as $d_poll)
                                    <option value="{{ $d_poll->id }}" {!! !empty($poll_id) && $d_poll->id==$poll_id ? 'selected="selected"' : '' !!}>{{ $d_poll->question }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


@if(!empty($poll_id))
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <div class="panel-heading-btn">
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                    </div>
                    <h4 class="panel-title">"{{ $poll->question }}" Respondents</h4>
                </div>
                <div class="panel-body">
                    {!! csrf_field() !!}
                    @include('admin.parts.table', [
                        'table_id' => 'respondents',
                        'table_fields' => [
                            'created_at'   => array('format' => 'datetime', 'label' => 'Date Taken','order' => true, 'orderKey' => 'taken'),
                            'user.name'    => array('template' => 'admin.parts.table-respondents-user-name', 'label' => 'Name','order' => true, 'orderKey' => 'name'),
                            'country_id'   => array('template' => 'admin.parts.table-item-user-country', 'label' => 'Country','order' => true, 'orderKey' => 'name'),
                        ],
                        'table_data' => $respondents,
                        'table_pagination' => false,
                        'pagination_link' => array()
                    ])             
                </div>
                
            </div>
        </div>
    </div>

    @if($poll->status != 'scheduled')
        <a class="btn btn-primary" href="{{ url('en/daily-polls/'.$poll_date.'/stats') }}" target="_blank">See Stats</a><br/><br/>
    @endif

    @if(!empty($poll->users_percentage))
        <div class="flex">
            <div class="col-md-3" style="border: 1px solid black;padding-top: 10px;padding-bottom: 10px;background-color: white;">
                <b> Current users percentage :</b> <br/><br/>

                @foreach($poll->users_percentage as $c => $up)
                    <p {!! 20 <= intval($up) ? 'style="color:red;"' : '' !!}> {{ App\Models\Country::find($c)->name }} : {{ $up }}% <p/>
                @endforeach
            </div>
        </p>
    @endif
@endif

@endsection