@extends('admin')

@section('content')

<div id="leads">
    <h1 class="page-header">
        Lead Magnet
        <a href="javascript:;" class="btn btn-primary pull-right btn-export-lead">Export</a>
    </h1>

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <h4 class="panel-title">Lead Magnet Results</h4>
                </div>
                <div class="panel-body">
                    <div class="row table-responsive-md">

                        <table class="table table-striped table-question-list">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Website</th>
                                    <th>Country</th>
                                    <th title="What is the main priority for your practice management?" style="color: #2196F3;">Q 1</th>
                                    <th title="What is your primary online tool for collecting patient reviews?" style="color: #2196F3;">Q 2</th>
                                    <th title="Do you typically ask your patients to leave an online review?" style="color: #2196F3;">Q 3</th>
                                    <th title="How frequently do you invite patients to leave a review?" style="color: #2196F3;">Q 4</th>
                                    <th title="Do you reply to online reviews?" style="color: #2196F3;">Q 5</th>
                                    <th>Total<br/></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($leads as $lead)
                                    <tr>
                                        <td>
                                            {{ $lead->created_at->toDateTimeString() }}
                                        </td>
                                        <td>
                                            {{ $lead->name }}
                                        </td>
                                        <td>
                                            {{ $lead->email }}
                                        </td>
                                        <td>
                                            {{ $lead->website }}
                                        </td>
                                        <td>
                                            {!! \App\Models\Country::find($lead->country_id)->name !!}
                                        </td>
                                        <td>
                                            {{ !empty($lead->answers) ? config('trp.lead_magnet')[1][json_decode($lead->answers, true)[1]] : '' }}
                                        </td>
                                        <td style="max-width: 160px;">
                                            {{ !empty($lead->answers) ? config('trp.lead_magnet')[2][json_decode($lead->answers, true)[2]] : '' }}
                                        </td>
                                        <td>
                                            @if(!empty($lead->answers) && !empty(json_decode($lead->answers, true)[3]))
                                                @foreach(json_decode($lead->answers, true)[3] as $ans_3)
                                                    -{{ config('trp.lead_magnet')[3][$ans_3] }}<br/>
                                                @endforeach
                                            @endif
                                        </td>
                                        <td>
                                            {{ !empty($lead->answers) && !empty(json_decode($lead->answers, true)[4]) ? config('trp.lead_magnet')[4][json_decode($lead->answers, true)[4]] : '' }}
                                        </td>
                                        <td>
                                            {{ !empty($lead->answers) ? config('trp.lead_magnet')[5][json_decode($lead->answers, true)[5]] : '' }}
                                        </td>
                                        <td>
                                            @if(!empty($lead->total) || $lead->total === 0)
                                                {{ $lead->total }}%
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection