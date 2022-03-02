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
                                	<option value="{{ $k }}" {!! $k==$search_type ? 'selected="selected"' : '' !!}>{{ $type }}</option>
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
                        <div class="col-md-1">
                            <select class="form-control" name="pending">
                                <option value="">All</option>
                                <option value="pending" {!! 'pending'==$pending ? 'selected="selected"' : '' !!}>Pending</option>
                                <option value="no-pending" {!! 'no-pending'==$pending ? 'selected="selected"' : '' !!}>No Pending</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="submit" class="btn btn-sm btn-primary btn-block" value="Search">
                        </div>
                    </div>
                    <div class="row custom-row" style="margin-bottom: 10px;">
                        <div class="col-md-1">
                            <input type="text" class="form-control datepicker" name="search-from" value="{{ $search_from }}" placeholder="From" autocomplete="off">
                        </div>
                        <div class="col-md-1">
                            <input type="text" class="form-control datepicker" name="search-to" value="{{ $search_to }}" placeholder="To" autocomplete="off">
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
				                    	<tr appeal-id="{{ $item->id }}">
				                    		<td>
                                                @if(!empty($item->user))
    				                    			<a href="{{ url('cms/users/users/edit/'.$item->user->id) }}">
    													{{ $item->user->name }}
    												</a>
                                                    @if($item->user->allBanAppeals->count() > 1)
                                                        <br/> <span style="color: red;"> + {{ $item->user->allBanAppeals->count() - 1 }} ban{{ $item->user->allBanAppeals->count() - 1 > 1 ? 's' : ''}}</span>
                                                    @endif
                                                    @if($item->pending_fields)
                                                        <br/> <b>Pending</b>
                                                    @endif
                                                    <div class="ban-appeal-wrapper">
                                                        <div class="img-wrap ban-appeal-info" user-id="{{ $item->user->id }}">
                                                            <img src="{{ url('img/info.png') }}" style="max-width: 15px;">
                                                        </div>

                                                        <div class="ban-appeal-tooltip">
                                                        </div>
                                                    </div>
                                                @else
                                                    deleted user from the database
                                                @endif
				                    		</td>
				                    		<td style="word-break: break-all;">
                                                @if(filter_var($item->link, FILTER_VALIDATE_URL) === FALSE)
                                                    {{ $item->link }}
                                                @else
                                                    <a href="{{ $item->link }}" target="_blank">{{ $item->link }}</a>
                                                @endif
				                    		</td>
				                    		<td>
				                    			<a href="{{ url('cms/images/appeals/'.$cur_item->id) }}" data-lightbox="banappeal{{ $item->id }}">
				                    				<img src="{{ url('cms/images/appeals/'.$cur_item->id.'/1') }}" style="max-width: 30px;">
				                    			</a>
				                    		</td>
				                    		<td style="word-break: break-all;">
				                    			{{ $item->description }}
				                    		</td>
				                    		<td>
				                    			{{ !empty($item->user) ? $item->user->patient_status == 'deleted' && $item->id < 23 ? 'Deleted' : $types[$item->type] : '-' }}

                                                @if($item->type == 'deleted' && !empty($item->user->deletedReasonAction->first()))
                                                    <br/>
                                                    <p style="color: red;">{{ $item->user->deletedReasonAction->first()->reason }}</p>
                                                @elseif($item->type == 'suspicious_admin' && !empty($suspicious_reason_action))
                                                    <br/>
                                                    <p style="color: red;">{{ $item->user->suspiciousReasonAction->first()->reason }}</p>
                                                @endif
				                    		</td>
				                    		<td>
				                    			{{ date('d.m.Y, H:i:s', $item->created_at->timestamp) }}
				                    		</td>
				                    		<td class="actions">
				                    			@if($item->status == 'new')
					                    			<a class="btn btn-sm btn-primary approve-appeal" href="javascript:;" data-toggle="modal" data-target="#approvedModal" appeal-id="{{ $item->id }}">
		                                                Approve
		                                            </a>
					                    			<a class="btn btn-sm btn-danger reject-appeal" href="javascript:;" data-toggle="modal" data-target="#rejectedModal" appeal-id="{{ $item->id }}">
		                                                Reject
		                                            </a>

                                                    @if($item->pending_fields)
                                                        <a class="btn btn-sm btn-warning pending-appeal" disabled="disabled" href="javascript:;">
                                                            Pending
                                                        </a>
                                                    @else
                                                        <a class="btn btn-sm btn-warning pending-appeal" href="javascript:;" data-toggle="modal" data-target="#pendingModal" appeal-id="{{ $item->id }}">
                                                            Pending
                                                        </a>
                                                    @endif
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
                <form class="ban-appeal-form" action="{{ url('cms/ban_appeals/reject/') }}" original-action="{{ url('cms/ban_appeals/reject/') }}" method="post" appeal-id="">
                    <label for="multiple-accounts" style="display: block;">
                        <input type="radio" name="reject_radio" id="multiple-accounts" value="Multiple accounts">
                        Multiple accounts
                    </label>
                    <label for="fake-fb-profile" style="display: block;">
                        <input type="radio" name="reject_radio" id="fake-fb-profile" value="Fake FB profile">
                        Fake FB profile
                    </label>
                    <label for="no-image-on-fb" style="display: block;">
                        <input type="radio" name="reject_radio" id="no-image-on-fb" value="No image on FB profile">
                        No image on FB profile
                    </label>
                    <label for="reject-other" style="display: block;">
                        <input type="radio" name="reject_radio" id="reject-other" value="Other">
                        Other
                    </label>

                    <textarea style="display: none;" class="form-control" name="rejected_reason" placeholder="Write the reason why you want to reject this appeal"></textarea>
                    <button type="submit" class="btn btn-primary btn-block" style="margin-top: 20px;">Reject</button>

                    <label class="alert alert-danger appeal-error" style="display: none;margin-top: 10px;"></label>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>

    </div>
</div>

<div id="pendingModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Pending Appeal</h4>
            </div>
            <div class="modal-body">
                <form class="ban-appeal-form" action="{{ url('cms/ban_appeals/pending/') }}" original-action="{{ url('cms/ban_appeals/pending/') }}" method="post" appeal-id="">

                    <label for="image" style="display: flex;align-items: center;margin-top: 7px;font-weight: normal;">
                        <input id="image" type="checkbox" name="pending_info[]" value="image" style="margin-top: 0px;margin-right: 4px;" />
                        Image
                    </label>
                    <label for="link" style="display: flex;align-items: center;margin-top: 7px;font-weight: normal;">
                        <input id="link" type="checkbox" name="pending_info[]" value="link"  style="margin-top: 0px;margin-right: 4px;" />
                        Link
                    </label>

                    <button type="submit" class="btn btn-primary btn-block" style="margin-top: 20px;">Send email</button>
                    <label class="alert alert-danger appeal-error" style="display: none;margin-top: 10px;"></label>
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
                <form class="ban-appeal-form" action="{{ url('cms/ban_appeals/approve/') }}" original-action="{{ url('cms/ban_appeals/approve/') }}" method="post" appeal-id="">
                    <label for="legit-proof" style="display: block;">
                        <input type="radio" name="approve_radio" id="legit-proof" value="Legit proof">
                        Legit proof
                    </label>
                    <label for="leaving-one-account" style="display: block;">
                        <input type="radio" name="approve_radio" id="leaving-one-account" value="Dupl accounts, leaving only one">
                        Dupl accounts, leaving only one
                    </label>
                    <label for="monitor-dupl-accounts" style="display: block;">
                        <input type="radio" name="approve_radio" id="monitor-dupl-accounts" value="Monitor for multiple accounts">
                        Monitor for multiple accounts
                    </label>
                    <label for="approve-other" style="display: block;">
                        <input type="radio" name="approve_radio" id="approve-other" value="Other">
                        Other
                    </label>
                    <textarea style="display: none;" class="form-control" name="approved_reason" placeholder="Write a reason why you want to approve this appeal"></textarea>
                    <button type="submit" class="btn btn-primary btn-block" style="margin-top: 20px;">Approve</button>
                    <label class="alert alert-danger appeal-error" style="display: none;margin-top: 10px;"></label>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>

    </div>
</div>

<style type="text/css">
    .ban-appeal-wrapper .ban-appeal-tooltip {
        display: none;
        position: absolute;
        border: 1px solid black;
        padding: 10px;
        border-radius: 5px;
        background: white;
        z-index: 1000;
    }

    .ban-appeal-wrapper .img-wrap {
        padding: 5px;
        padding-left: 0px;
    }

    .ban-appeal-wrapper:hover .ban-appeal-tooltip {
        display: block;
    }

</style>

@endsection