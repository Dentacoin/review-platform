@extends('admin')

@section('content')

<h1 class="page-header">Ban Appeals</h1>
<!-- end page-header -->



<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <div class="panel-heading-btn">
                    <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                </div>
                <h4 class="panel-title"> Ban Appeals filter</h4>
            </div>
            <div class="panel-body users-filters">
                <form method="get" action="{{ url('cms/'.$current_page) }}" id="users-filter-form">
                    <div class="row" style="margin-bottom: 10px;">
                        <div class="col-md-1">
                            <input type="text" class="form-control" name="search-user-id" value="{{ $search_user_id }}" placeholder="User ID">
                        </div>
                        <div class="col-md-2">
                            <input type="text" class="form-control" name="search-email" value="{{ $search_email }}" placeholder="User Email">
                        </div>
                        <div class="col-md-2">
                            <input type="text" class="form-control" name="search-name" value="{{ $search_name }}" placeholder="User Name">
                        </div>
                        <div class="col-md-2">
                        	<select class="form-control" name="search-type">
                                <option value="">Ban Type</option>
                                @foreach($types as $k => $type)
                                	<option value="trp" {!! $k==$search_type ? 'selected="selected"' : '' !!}>{{ $type }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                        	<select class="form-control" name="search-status">
                                <option value="">Ban Appeal Status</option>
                                <option value="new" {!! 'new'==$search_status ? 'selected="selected"' : '' !!}>New</option>
                                <option value="approved" {!! 'approved'==$search_type ? 'selected="selected"' : '' !!}>Approved</option>
                                <option value="rejected" {!! 'rejected'==$search_type ? 'selected="selected"' : '' !!}>Rejected</option>
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
                <div class="panel-heading-btn">
                    <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                </div>
                <h4 class="panel-title">Ban Appeals</h4>
            </div>
            <div class="panel-body">
				<div class="dataTables_wrapper">
				    <div class="row">
				    	<div class="col-sm-12 table-responsive-md">
                    		<p>Total count: {{ $total_count }}</p>
				    		<table class="table table-striped">
				                <thead>
				                    <tr>
				                    	<th>User</th>
				                    	<th>Link</th>
				                    	<th>Image</th>
				                    	<th>Description</th>
				                    	<th>Type</th>
				                    	<th>Date</th>
				                    	<th>Actions</th>
				                    </tr>
				                </thead>
				                <tbody>
				                	@foreach($items as $item)
				                    	<tr>
				                    		<td>
				                    			<a href="{{ url('cms/users/edit/'.$item->user->id) }}">
													{{ $item->user->name }}
												</a>
                                                @if($item->user->allBanAppeals->count() > 1)
                                                    <br/> <span style="color: red;"> + {{ $item->user->allBanAppeals->count() - 1 }} ban{{ $item->user->allBanAppeals->count() - 1 > 1 ? 's' : ''}}</span>
                                                @endif
				                    		</td>
				                    		<td style="word-break: break-all;">
				                    			{{ $item->link }}
				                    		</td>
				                    		<td>
				                    			<a href="{{ $item->getImageUrl() }}" data-lightbox="banappeal{{ $item->id }}">
				                    				<img src="{{ $item->getImageUrl(true) }}" style="max-width: 30px;">
				                    			</a>
				                    		</td>
				                    		<td>
				                    			{{ $item->description }}
				                    		</td>
				                    		<td>
				                    			{{ $item->user->patient_status == 'deleted' && $item->id < 23 ? 'Deleted' : $types[$item->type] }}
				                    		</td>
				                    		<td>
				                    			{{ date('d.m.Y, H:i:s', $item->created_at->timestamp) }}
				                    		</td>
				                    		<td>
				                    			@if($item->status == 'new')
					                    			<a class="btn btn-sm btn-primary approve-appeal" href="javascript:;" data-toggle="modal" data-target="#approvedModal" appeal-id="{{ $item->id }}">
		                                                Approve
		                                            </a>
					                    			<a class="btn btn-sm btn-danger reject-appeal" href="javascript:;" data-toggle="modal" data-target="#rejectedModal" appeal-id="{{ $item->id }}">
		                                                Reject
		                                            </a>
		                                        @elseif($item->status == 'approved')
		                                        	Approved
		                                        @elseif($item->status == 'rejected')
		                                        	Rejected
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
</div>

@if($total_pages > 1)
    <nav aria-label="Page navigation" style="text-align: center;">
        <ul class="pagination">
            <li class="{{ ($page <= 1 ?  'disabled' : '' ) }}">
                <a class="page-link" href="{{ url('cms/ban_appeals/?page=1'.$pagination_link) }}" aria-label="Previous">
                    <span aria-hidden="true"> << </span>
                </a>
            </li>
            <li class="{{ ($page <= 1 ?  'disabled' : '' ) }}">
                <a class="page-link prev" href="{{ url('cms/ban_appeals/?page='.($page>1 ? $page-1 : '1').$pagination_link) }}"  aria-label="Previous">
                    <span aria-hidden="true"> < </span>
                </a>
            </li>
            @for($i=$start; $i<=$end; $i++)
                <li class="{{ ($i == $page ?  'active' : '') }}">
                    <a class="page-link" href="{{ url('cms/ban_appeals/?page='.$i.$pagination_link) }}">{{ $i }}</a>
                </li>
            @endfor
            <li class="{{ ($page >= $total_pages ? 'disabled' : '') }}">
                <a class="page-link next" href="{{ url('cms/ban_appeals/?page='.($page < $total_pages ? $page+1 :  $total_pages).$pagination_link) }}" aria-label="Next"> <span aria-hidden="true"> > </span> </a>
            </li>
            <li class="{{ ($page >= $total_pages ? 'disabled' : '') }}">
                <a class="page-link" href="{{ url('cms/ban_appeals/?page='.$total_pages.$pagination_link) }}" aria-label="Next"> <span aria-hidden="true"> >> </span>  </a>
            </li>
        </ul>
    </nav>
@endif

<div id="rejectedModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Reject Appeal</h4>
            </div>
            <div class="modal-body">
                <form action="{{ url('cms/ban_appeals/reject/') }}" original-action="{{ url('cms/ban_appeals/reject/') }}" method="post">
                    <textarea class="form-control" name="rejected_reason" placeholder="Write the reason why you want to reject this appeal"></textarea>
                    <button type="submit" class="btn btn-primary btn-block" style="margin-top: 20px;">Reject</button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>

    </div>
</div>

<div id="approvedModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Approve appeal</h4>
            </div>
            <div class="modal-body">
                <form action="{{ url('cms/ban_appeals/approve/') }}" original-action="{{ url('cms/ban_appeals/approve/') }}" method="post">
                    <textarea class="form-control" name="approved_reason" placeholder="Write the reason why you want to approve this appeal"></textarea>
                    <button type="submit" class="btn btn-primary btn-block" style="margin-top: 20px;">Approve</button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>

    </div>
</div>

@endsection