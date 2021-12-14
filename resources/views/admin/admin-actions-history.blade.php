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
                    <form method="get" action="{{ url('cms/admins/actions-history/') }}">
                        <div class="row" style="margin-bottom: 10px;">
                            <div class="col-md-2">
                                <select name="search-admin-id" class="form-control">
                                    <option value="">Search admin id</option>
                                    @foreach($admins as $admin)
                                        <option value="{{ $admin->id }}" {!! $search_admin_id == $admin->id ? 'selected="selected"' : '' !!}>{{ $admin->name ?? $admin->username }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="submit" class="btn btn-sm btn-primary btn-block" name="search" value="Search">
                            </div>
                        </div>
                    </form>
                </div>
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
                                    <th style="width: 25%;">Date</th>
                                    <th style="width: 25%;">Admin</th>
                                    <th style="width: 25%;">Action</th>
                                    <th style="width: 25%;">User/Transaction/Ban Appeal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($actions as $action)
                                    <tr>
                                        <td style="width: 25%;">
                                            {{ date('d.m.Y, H:i:s', $action->created_at->timestamp) }}
                                        </td>
                                        <td style="width: 25%;">
                                            <a href="{{ url('cms/admins/admins/edit/'.$action->admin_id) }}">{{ $action->admin->name }}</a>
                                        </td>
                                        <td style="width: 25%;">
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
                                        <td style="width: 25%;">
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