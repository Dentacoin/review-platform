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
                <h4 class="panel-title">Ban Appeals</h4>
            </div>
            <div class="panel-body">
        		<div class="panel-body">
					<div class="dataTables_wrapper">
					    <div class="row">
					    	<div class="col-sm-12 table-responsive-md">
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
					                    		</td>
					                    		<td>
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
					                    			{{ $types[$item->type] }}
					                    		</td>
					                    		<td>
					                    			{{ date('d.m.Y, H:i:s', $item->created_at->timestamp) }}
					                    		</td>
					                    		<td>
					                    			@if($item->status == 'new')
						                    			<a class="btn btn-sm btn-primary" href="{{ url('cms/'.$current_page.'/approve/'.$item->id) }}">
			                                                Approve
			                                            </a>
						                    			<a class="btn btn-sm btn-danger" href="{{ url('cms/'.$current_page.'/reject/'.$item->id) }}">
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
</div>

@endsection