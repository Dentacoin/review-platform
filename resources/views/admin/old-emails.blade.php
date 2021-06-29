@extends('admin')

@section('content')

<h1 class="page-header">
    Old Emails
</h1>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <div class="panel-heading-btn">
                    <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                </div>
                <h4 class="panel-title"> Search Old Emails </h4>
            </div>
            <div class="panel-body users-filters">
                <form method="get" action="{{ url('cms/email_validations/old_emails') }}">
                    <div class="row custom-row" style="margin-bottom: 10px;">
                    <div class="row" style="margin-bottom: 10px;">  
                        <div class="col-md-2">
                            <input type="text" class="form-control" name="search-email" value="{{ $search_email }}" placeholder="Email">
                        </div>
                        <div class="col-md-2">
                            <input type="text" class="form-control" name="search-user-id" value="{{ $search_user_id }}" placeholder="User ID">
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

<!-- end page-header -->
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <div class="panel-heading-btn">
                    <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                </div>
                <h4 class="panel-title">Old Emails</h4>
            </div>
            <div class="panel-body">
        		<div class="panel-body">
					<table class="table table-striped table-question-list">
                        <thead>
                            <tr>
                                <th>User ID</th>
                                <th>User</th>
                                <th>Current Email</th>
                                <th>Old Email</th>
                                <th>Delete</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($olds as $old)
                                <tr style="{{ $old->user ? ($old->user->deleted_at ? 'opacity: 0.5;' : '') : '' }}">
                                    <td>
                                        {{ $old->user_id }}
                                    </td>
                                    <td>
                                        <a href="{{ url('cms/users/users/edit/'.$old->user_id) }}" target="_blank">{{ $old->user ? $old->user->name : 'deleted user' }}</a>
                                    </td>
                                    <td>
                                        {{ $old->user ? $old->user->email : '-' }}
                                    </td>
                                    <td>
                                        {{ $old->email }}
                                    </td>
                                    <th>
                                    	@if($admin->id == 14 || $admin->id == 15 || $admin->id == 1)
                                    		<a href="{{ url('cms/email_validations/old_emails/delete/'.$old->id) }}" class="btn btn-danger">delete</a>
                                    	@endif
                                    </th>
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
                <a class="page-link" href="{{ url('cms/email_validations/old_emails/?page=1'.$pagination_link) }}" aria-label="Previous">
                    <span aria-hidden="true"> << </span>
                </a>
            </li>
            <li class="{{ ($page <= 1 ?  'disabled' : '' ) }}">
                <a class="page-link prev" href="{{ url('cms/email_validations/old_emails/?page='.($page>1 ? $page-1 : '1').$pagination_link) }}"  aria-label="Previous">
                    <span aria-hidden="true"> < </span>
                </a>
            </li>
            @for($i=$start; $i<=$end; $i++)
                <li class="{{ ($i == $page ?  'active' : '') }}">
                    <a class="page-link" href="{{ url('cms/email_validations/old_emails/?page='.$i.$pagination_link) }}">{{ $i }}</a>
                </li>
            @endfor
            <li class="{{ ($page >= $total_pages ? 'disabled' : '') }}">
                <a class="page-link next" href="{{ url('cms/email_validations/old_emails/?page='.($page < $total_pages ? $page+1 :  $total_pages).$pagination_link) }}" aria-label="Next"> <span aria-hidden="true"> > </span> </a>
            </li>
            <li class="{{ ($page >= $total_pages ? 'disabled' : '') }}">
                <a class="page-link" href="{{ url('cms/email_validations/old_emails/?page='.$total_pages.$pagination_link) }}" aria-label="Next"> <span aria-hidden="true"> >> </span>  </a>
            </li>
        </ul>
    </nav>
@endif

@endsection
