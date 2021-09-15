@extends('admin')

@section('content')

<div class="flex" style="justify-content: space-between;">
    <h1 class="page-header">
        Invites
    </h1>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <div class="panel-heading-btn">
                    <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                </div>
                <h4 class="panel-title"> Invites filter</h4>
            </div>
            <div class="panel-body users-filters">
                <form method="get" action="{{ url('cms/'.$current_page) }}" id="users-filter-form">
                    <div class="row custom-row" style="margin-bottom: 10px;">
                        <div class="col-md-1">
                            <input type="text" class="form-control" name="search-user-id" value="{{ $search_user_id }}" placeholder="Inviter ID">
                        </div>
                        <div class="col-md-2">
                            <input type="text" class="form-control" name="search-email" value="{{ $search_email }}" placeholder="Inviter Email">
                        </div>
                        <div class="col-md-2">
                            <input type="text" class="form-control" name="search-name" value="{{ $search_name }}" placeholder="Inviter Name">
                        </div>
                        <div class="col-md-2">
                            <select class="form-control" name="search-inviter-type">
                                <option value="">Inviter type</option>
                                <option value="dentists" {!! 'dentists'==$search_inviter_type ? 'selected="selected"' : '' !!}>Dentists and Clinics</option>
                                <option value="patients" {!! 'patients'==$search_inviter_type ? 'selected="selected"' : '' !!}>Patients</option>
                            </select>
                        </div>
                        <div class="col-md-1">
                            <input type="text" class="form-control" name="search-invited-id" value="{{ $search_invited_id }}" placeholder="Invited ID">
                        </div>
                        <div class="col-md-2">
                            <input type="text" class="form-control" name="search-invited-email" value="{{ $search_invited_email }}" placeholder="Invited Email">
                        </div>
                        <div class="col-md-2">
                            <input type="text" class="form-control" name="search-invited-name" value="{{ $search_invited_name }}" placeholder="Invited Name">
                        </div>
                    </div>
                    <div class="row custom-row">
                        <div class="col-md-2">
                            <select class="form-control" name="search-platform">
                                <option value="">Platform</option>
                                @foreach(config('platforms') as $key => $platform)
                                    <option value="{{ $key }}" {!! $key==$search_platform ? 'selected="selected"' : '' !!}>{{ $platform['name'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <input type="text" class="form-control datepicker" name="search-from" value="{{ $search_from }}" placeholder="Search from" autocomplete="off">
                        </div>
                        <div class="col-md-2">
                            <input type="text" class="form-control datepicker" name="search-to" value="{{ $search_to }}" placeholder="Search to" autocomplete="off">
                        </div>
                        <div class="col-md-2">
                            <select class="form-control" name="search-for-verification">
                                <option value="">Ask for verification</option>
                                <option value="yes" {!! 'yes'==$search_for_verification ? 'selected="selected"' : '' !!}>Yes</option>
                                <option value="no" {!! 'no'==$search_for_verification ? 'selected="selected"' : '' !!}>No</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label for="exclude-test" style="display: flex;align-items: center;margin-top: 7px;font-weight: normal;">
                                <input id="exclude-test" type="checkbox" name="exclude-test" value="1" {!! !empty($exclude_test) ? 'checked="checked"' : '' !!} style="margin-top: 0px;margin-right: 4px;" />
                                Exclude Test
                            </label>
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
                <h4 class="panel-title">Invites</h4>
            </div>
            <div class="panel-body" id="link" link="{{ url('cms/update-blog-preferences') }}">
                <div class="row table-responsive-md">
                    <p>Total count: {{ $total_count }}</p>
                    <table class="table table-striped table-question-list">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Inviter</th>
                                <th>Invited email</th>
                                <th>Invited name</th>
                                <th>Registered from invite</th>
                                <th>Ask for verification</th>
                                <th>Platform</th>
                                <th>Rewarded</th>
                                <th>Suspicious email</th>
                                <th>For HubApp</th>
                                @if($admin->role!='support')
                                    <th>Delete</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $invite)
                                <tr>
                                    <td>
                                        {{ date('d.m.Y, H:i:s', $invite->created_at->timestamp) }}
                                    </td>
                                    <td>
                                        <a href="{{ url('cms/users/users/edit/'.$invite->user_id) }}"> {{ !empty($invite->user) ? $invite->user->name : 'unknown' }}</a>
                                    </td>
                                    <td>
                                        {!! $invite->invited_email !!}
                                    </td>
                                    <td>
                                        {!! $invite->invited_name !!}
                                    </td>
                                    <td>
                                        {!! $invite->invited_id ? '<a href="'.url('cms/users/users/edit/'.$invite->invited_id).'">'.(!empty($invite->invited) ? $invite->invited->name : 'unknown').'</a>' : '-' !!}
                                    </td>
                                    <td>
                                        {!! $invite->invited_id ? (App\Models\UserAsk::where('user_id', $invite->invited_id)->where('dentist_id', $invite->user_id)->where('status', 'yes')->first() ? '🗸' : '') : '' !!}
                                    </td>
                                    <td>
                                        {!! $invite->platform ? config('platforms')[$invite->platform]['name'] : '' !!}
                                    </td>
                                    <td>
                                        {!! $invite->rewarded ? '<span class="label label-success">Yes</span>' : '<span class="label label-warning">No</span>' !!}
                                    </td>
                                    <td>
                                        {!! $invite->suspicious_email ? '<span class="label label-danger">Yes</span>' : '' !!}
                                    </td>
                                    <td>
                                        {!! $invite->for_dentist_patients ? '<span class="label label-success">Yes</span>' : '' !!}
                                    </td>
                                    @if($admin->role!='support')
                                        <td>
                                            <a class="btn btn-sm btn-deafult" href="{{ url('cms/invites/delete/'.$invite->id) }}" onclick="return confirm('Are you sure you want to DELETE this?');">{{ trans('admin.table.delete') }}</a>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>                    
                </div>
            </div>
        </div>
    </div>
</div>

@if($total_pages > 1)
    <nav aria-label="Page navigation" style="text-align: center;">
        <ul class="pagination">
            <li class="{{ ($page <= 1 ?  'disabled' : '' ) }}">
                <a class="page-link" href="{{ url('cms/invites/?page=1'.$pagination_link) }}" aria-label="Previous">
                    <span aria-hidden="true"> << </span>
                </a>
            </li>
            <li class="{{ ($page <= 1 ?  'disabled' : '' ) }}">
                <a class="page-link prev" href="{{ url('cms/invites/?page='.($page>1 ? $page-1 : '1').$pagination_link) }}"  aria-label="Previous">
                    <span aria-hidden="true"> < </span>
                </a>
            </li>
            @for($i=$start; $i<=$end; $i++)
                <li class="{{ ($i == $page ?  'active' : '') }}">
                    <a class="page-link" href="{{ url('cms/invites/?page='.$i.$pagination_link) }}">{{ $i }}</a>
                </li>
            @endfor
            <li class="{{ ($page >= $total_pages ? 'disabled' : '') }}">
                <a class="page-link next" href="{{ url('cms/invites/?page='.($page < $total_pages ? $page+1 :  $total_pages).$pagination_link) }}" aria-label="Next"> <span aria-hidden="true"> > </span> </a>
            </li>
            <li class="{{ ($page >= $total_pages ? 'disabled' : '') }}">
                <a class="page-link" href="{{ url('cms/invites/?page='.$total_pages.$pagination_link) }}" aria-label="Next"> <span aria-hidden="true"> >> </span>  </a>
            </li>
        </ul>
    </nav>
@endif

@endsection