@extends('admin')

@section('content')

    <div class="flex" style="justify-content: space-between;">
        <h1 class="page-header">
            Admin History Actions
        </h1>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <div class="panel-heading-btn">
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                    </div>
                    <h4 class="panel-title"> Admin History Filter</h4>
                </div>
                <div class="panel-body users-filters">
                    <div class="trans-history-wrapper">
                        <img src="{{ url('img/info.png') }}" style="max-width: 15px;">
            
                        <div class="trans-history">
                            Admins: <br/>
                            @foreach($admins as $admin)
                                {{ $admin->name ?? $admin->username }} ( ID: {{ $admin->id }}) <br/>
                            @endforeach
                        </div>
                    </div>
                    <form method="get" action="{{ url('cms/admins/actions-history/') }}">
                        <div class="row" style="margin-bottom: 10px;">
                            <div class="col-md-2">
                                <input type="text" class="form-control" name="search-admin-id" value="{{ $search_admin_id }}" placeholder="Search admin ID">
                            </div>
                            <div class="col-md-2">
                                <input type="submit" class="btn btn-sm btn-primary btn-block" name="search" value="Search">
                            </div>
                        </div>
                    </form>
                </div>
                
                <style type="text/css">
                    .trans-history-wrapper .trans-history {
                        display: none;
                        position: absolute;
                        border: 1px solid black;
                        padding: 10px;
                        border-radius: 5px;
                        background: white;
                        z-index: 100;
                    }

                    .trans-history-wrapper:hover .trans-history {
                        display: block;
                    }
                </style>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <h4 class="panel-title">Admin History Actions</h4>
                </div>
                <div class="panel-body">
                    <div class="row table-responsive-md">
                        <table class="table table-striped table-question-list">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Admin</th>
                                    <th>Action</th>
                                    <th>User/Transaction/Ban Appeal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($actions as $action)
                                    <tr>
                                        <td>
                                            {{ date('d.m.Y, H:i:s', $action->created_at->timestamp) }}
                                        </td>
                                        <td>
                                            <a href="{{ url('cms/admins/admins/edit/'.$action->admin_id) }}">{{ $action->admin->name }}</a>
                                        </td>
                                        <td>
                                            @if(isset($action->status))
                                                @if(isset($action->new_status))
                                                    changed from "{{ isset(config('user-statuses')[$action->status]) ? config('user-statuses')[$action->status] : config('patient-statuses')[$action->status] }}" to "{{ isset(config('user-statuses')[$action->new_status]) ? config('user-statuses')[$action->new_status] : config('patient-statuses')[$action->new_status] }}"
                                                @else
                                                    @if(isset($action->user_id))
                                                        old status "{{ isset(config('user-statuses')[$action->status]) ? config('user-statuses')[$action->status] : config('patient-statuses')[$action->status] }}"
                                                    @else
                                                        @if(isset($action->old_status))
                                                            changed from "{{ config('transaction-statuses')[$action->old_status] }}" to "{{ config('transaction-statuses')[$action->status] }}"
                                                        @else
                                                            old status "{{ config('transaction-statuses')[$action->status] }}"
                                                        @endif
                                                    @endif
                                                @endif
                                            @elseif(isset($action->patient_status))
                                                @if(isset($action->new_patient_status))
                                                    changed status from "{{ config('patient-statuses')[$action->patient_status] }}" to "{{ config('patient-statuses')[$action->new_patient_status] }}"
                                                @else
                                                    old patient status "{{ config('patient-statuses')[$action->patient_status] }}"
                                                @endif
                                            @elseif(isset($action->gender))
                                                @if(isset($action->new_gender))
                                                    changed gender from {{ $action->gender == 'm' ? 'male' : 'female' }} to {{ $action->new_gender == 'm' ? 'male' : 'female' }}
                                                @else
                                                    old gender {{ $action->gender == 'm' ? 'male' : 'female' }}
                                                @endif
                                            @elseif(isset($action->birthyear))
                                                @if(isset($action->new_birthyear))
                                                    changed birth year from {{ $action->birthyear }} to {{ $action->new_birthyear }}
                                                @else
                                                    old birth year {{ $action->birthyear }}
                                                @endif
                                            @elseif(isset($action->phone))
                                                @if(isset($action->new_phone))
                                                    changed phone number from {{ $action->phone }} to {{ $action->new_phone }}
                                                @else
                                                    old phone number {{ $action->phone }}
                                                @endif
                                            @elseif(isset($action->country_id))
                                                @if(isset($action->new_country_id))
                                                    changed country from {{ $action->country->name }} to {{ $action->newCountry->name }}
                                                @else
                                                    old country {{ $action->country->name }}
                                                @endif
                                            @elseif(isset($action->civic_email))
                                                @if(isset($action->new_civic_email))
                                                    changed civic email from {{ $action->civic_email }} to {{ $action->new_civic_email }}
                                                @else
                                                    old civic email {{ $action->civic_email }}
                                                @endif
                                            @elseif(isset($action->fb_id))
                                                @if(isset($action->new_fb_id))
                                                    changed Facebook ID from {{ $action->fb_id }} to {{ $action->new_fb_id }}
                                                @else
                                                    old Facebook ID {{ $action->fb_id }}
                                                @endif
                                            @elseif(isset($action->history))
                                                @if(isset($action->new_history))
                                                    {!! $action->new_history !!}
                                                @endif
                                                {!! $action->history !!}
                                            @elseif(isset($action->action))
                                                @if($action->action == 'view')
                                                    view user's profile
                                                @elseif($action->action == 'approve' && !empty($action->ban_appeal_id))
                                                    approved ban appeal
                                                @elseif($action->action == 'export')
                                                    exported users with <a href="{{ $action->info }}">query</a>
                                                @endif
                                            @endif
                                        </td>
                                        <td>
                                            @if(isset($action->user_id) && !isset($action->ban_appeal_id))
                                                <a href="{{ url('cms/users/users/edit/'.$action->user_id) }}">{{ $action->user ? $action->user->getNames() : 'User' }}</a>
                                            @elseif(isset($action->transaction_id))
                                                <a href="{{ url('cms/transactions/?search-id='.$action->transaction_id) }}">Transaction</a>
                                            @elseif(isset($action->ban_appeal_id))
                                                <a href="{{ url('cms/ban_appeals/?search-id='.$action->ban_appeal_id) }}">Ban Appeal</a>
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

    <div style="text-align: center;"> 
        {{ $actions->render() }}
    </div>

@endsection