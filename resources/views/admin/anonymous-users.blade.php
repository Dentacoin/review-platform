@extends('admin')

@section('content')

<div class="flex" style="justify-content: space-between;">
    <h1 class="page-header">
        Not registered users
    </h1>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <div class="panel-heading-btn">
                    <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                </div>
                <h4 class="panel-title"> Not registered users filter</h4>
            </div>
            <div class="panel-body users-filters">
                <form method="get" action="{{ url('cms/'.$current_page) }}" id="users-filter-form">
                    <div class="row" style="margin-bottom: 10px;">
                        <div class="col-md-2">
                            <input type="text" class="form-control" name="search-email" value="{{ $search_email }}" placeholder=Email>
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
                <h4 class="panel-title">Not registered users</h4>
            </div>
            <div class="panel-body" id="link" link="{{ url('cms/update-blog-preferences') }}">
                <div class="row table-responsive-md">
                    <p>Total count: {{ $total_count }}</p>
                    <table class="table table-striped table-question-list">
                        <thead>
                            <tr>
                                <th>Email</th>
                                <th>Website notifications</th>
                                <th>Unsubscribe - Website notifications</th>
                                <th>Blog digest and insights</th>
                                <th>Unsubscribe - Blog digest and insights</th>
                                <th>Refresh</th>
                                <th>Delete</th>
                            </tr>
                        </thead>
                        <tbody>
                        	@foreach($users as $user)
	                            <tr>
	                                <td>
	                                    {{ $user->email }}
	                                </td>
	                                <td>
	                                    {!! !empty($user->website_notifications) ? implode(',', $user->website_notifications) : '' !!}
	                                </td>
	                                <td>
	                                    {!! !empty($user->unsubscribed_website_notifications) ? implode(',', $user->unsubscribed_website_notifications) : '' !!}
	                                </td>
	                                <td class="blog">
	                                    {!! !empty($user->blog) ? implode(',', $user->blog) : '' !!}
	                                </td>
                                    <td>
                                        {!! !empty($user->unsubscribed_blog) ? implode(',', $user->unsubscribed_blog) : '' !!}
                                    </td>
                                    <td>
                                        <a href="javascript:;" email="{{ App\Models\User::encrypt($user->email) }}" class="btn btn-primary preferences-button">Refresh blog preferences</a>
                                    </td>
                                    <td>
                                        <a class="btn btn-sm btn-deafult" href="{{ url('cms/anonymous_users/delete/'.$user->id) }}" onclick="return confirm('Are you sure you want to DELETE this?');">{{ trans('admin.table.delete') }}</a>
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
                <a class="page-link" href="{{ url('cms/anonymous_users/?page=1'.$pagination_link) }}" aria-label="Previous">
                    <span aria-hidden="true"> << </span>
                </a>
            </li>
            <li class="{{ ($page <= 1 ?  'disabled' : '' ) }}">
                <a class="page-link prev" href="{{ url('cms/anonymous_users/?page='.($page>1 ? $page-1 : '1').$pagination_link) }}"  aria-label="Previous">
                    <span aria-hidden="true"> < </span>
                </a>
            </li>
            @for($i=$start; $i<=$end; $i++)
                <li class="{{ ($i == $page ?  'active' : '') }}">
                    <a class="page-link" href="{{ url('cms/anonymous_users/?page='.$i.$pagination_link) }}">{{ $i }}</a>
                </li>
            @endfor
            <li class="{{ ($page >= $total_pages ? 'disabled' : '') }}">
                <a class="page-link next" href="{{ url('cms/anonymous_users/?page='.($page < $total_pages ? $page+1 :  $total_pages).$pagination_link) }}" aria-label="Next"> <span aria-hidden="true"> > </span> </a>
            </li>
            <li class="{{ ($page >= $total_pages ? 'disabled' : '') }}">
                <a class="page-link" href="{{ url('cms/anonymous_users/?page='.$total_pages.$pagination_link) }}" aria-label="Next"> <span aria-hidden="true"> >> </span>  </a>
            </li>
        </ul>
    </nav>
@endif

@endsection