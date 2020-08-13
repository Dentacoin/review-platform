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
                    <div class="row" style="margin-bottom: 10px;">
                        <div class="col-md-1">
                            <input type="text" class="form-control" name="search-user-id" value="{{ $search_user_id }}" placeholder="Inviter ID">
                        </div>
                        <div class="col-md-2">
                            <input type="text" class="form-control" name="search-email" value="{{ $search_email }}" placeholder="Inviter Email">
                        </div>
                        <div class="col-md-2">
                            <input type="text" class="form-control" name="search-name" value="{{ $search_name }}" placeholder="Inviter Name">
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

                    <table class="table table-striped table-question-list">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Inviter</th>
                                <th>Invited email</th>
                                <th>Invited name</th>
                                <th>Registered from invite</th>
                                <th>Platform</th>
                                <th>Rewarded</th>
                                <th>Delete</th>
                            </tr>
                        </thead>
                        <tbody>
                        	@foreach($items as $invite)
	                            <tr>
                                    <td>
                                        {{ date('d.m.Y, H:i:s', $invite->created_at->timestamp) }}
                                    </td>
                                    <td>
                                        <a href="{{ url('cms/users/edit/'.$invite->user_id) }}"> {{ !empty($invite->user) ? $invite->user->name : 'unknown' }}</a>
                                    </td>
                                    <td>
                                        {!! $invite->invited_email !!}
                                    </td>
                                    <td>
                                        {!! $invite->invited_name !!}
                                    </td>
                                    <td>
                                        {!! $invite->invited_id ? '<a href="'.url('cms/users/edit/'.$invite->invited_id).'">'.(!empty($invite->invited) ? $invite->invited->name : 'unknown').'</a>' : '-' !!}
                                    </td>
                                    <td>
                                        {!! $invite->platform ? config('platforms')[$invite->platform]['name'] : '' !!}
                                    </td>
                                    <td>
                                        {!! $invite->rewarded ? '<span class="label label-success">Yes</span>' : '<span class="label label-warning">No</span>' !!}
                                    </td>
                                    <td>
                                        <a class="btn btn-sm btn-deafult" href="{{ url('cms/invites/delete/'.$invite->id) }}" onclick="return confirm('Are you sure you want to DELETE this?');">{{ trans('admin.table.delete') }}</a>
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