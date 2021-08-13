@extends('admin')

@section('content')

	<h1 class="page-header">
		Paid Reports
		
		<a class="btn btn-primary pull-right" href="{{ url('cms/vox/paid-reports/add') }}" style="margin-right: 10px;">Add new paid report</a>
	</h1>
	<!-- end page-header -->

	<div class="row">
		<div class="col-md-12">
			<div class="panel panel-inverse">
				<div class="panel-heading">
					<div class="panel-heading-btn">
						<a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
					</div>
					<h4 class="panel-title">Search</h4>
				</div>
				<div class="panel-body users-filters">
					<form method="get" action="{{ url('cms/vox/paid-reports') }}" id="users-filter-form">
						<div class="row" style="margin-bottom: 10px;">
							<div class="col-md-2">
								<input type="text" class="form-control" name="search-title" value="{{ $search_title }}" placeholder="Title/Main title">
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

	{{ Form::open(array('class' => 'form-horizontal', 'method' => 'post')) }}
		<div class="row">
		    <div class="col-md-12">
		        <div class="panel panel-inverse">
		            <div class="panel-heading">
		                <h4 class="panel-title">Paid Reports</h4>
		            </div>
		            <div class="tab-content">

		                <table class="table table-striped table-question-list">
		                    <thead>
		                        <tr>
		                            <th>Main Title</th>
		                            <th>Title</th>
		                            <th>Status</th>
		                            <th>Edit</th>
		                            <th>Delete</th>
		                        </tr>
		                    </thead>
		                    <tbody>
		                        @foreach($reports as $report)
		                            <tr>
		                                <td>
		                                    {{ $report->main_title }}
		                                </td>
		                                <td>
		                                    {{ $report->title }}
		                                </td>
		                                <td>
		                                    {!! $statuses[$report->status] !!}
		                                </td>	
		                                <td>
		                                    <a class="btn btn-sm btn-success" href="{{ url('cms/vox/paid-reports/edit/'.$report->id) }}">
		                                        <i class="fa fa-pencil"></i>
		                                    </a>
		                                </td>
		                                <td>
		                                    <a class="btn btn-sm btn-success" onclick="return confirm('{{ trans('admin.common.sure') }}')" href="{{ url('cms/vox/paid-reports/delete/'.$report->id) }}">
		                                        <i class="fa fa-remove"></i>
		                                    </a>
		                                </td>
		                            </tr>
		                        @endforeach
		                    </tbody>
		                </table>
		            </div>
		        </div>
		    </div>
		</div>
	{{ Form::close() }}

	@if($total_pages > 1)
        <nav aria-label="Page navigation" style="text-align: center;">
            <ul class="pagination">
                <li class="{{ ($page <= 1 ?  'disabled' : '' ) }}">
                    <a class="page-link" href="{{ url('cms/vox/paid-reports/?page=1'.$pagination_link) }}" aria-label="Previous">
                        <span aria-hidden="true"> << </span>
                    </a>
                </li>
                <li class="{{ ($page <= 1 ?  'disabled' : '' ) }}">
                    <a class="page-link prev" href="{{ url('cms/vox/paid-reports/?page='.($page>1 ? $page-1 : '1').$pagination_link) }}"  aria-label="Previous">
                        <span aria-hidden="true"> < </span>
                    </a>
                </li>
                @for($i=$start; $i<=$end; $i++)
                    <li class="{{ ($i == $page ?  'active' : '') }}">
                        <a class="page-link" href="{{ url('cms/vox/paid-reports/?page='.$i.$pagination_link) }}">{{ $i }}</a>
                    </li>
                @endfor
                <li class="{{ ($page >= $total_pages ? 'disabled' : '') }}">
                    <a class="page-link next" href="{{ url('cms/vox/paid-reports/?page='.($page < $total_pages ? $page+1 :  $total_pages).$pagination_link) }}" aria-label="Next"> <span aria-hidden="true"> > </span> </a>
                </li>
                <li class="{{ ($page >= $total_pages ? 'disabled' : '') }}">
                    <a class="page-link" href="{{ url('cms/vox/paid-reports/?page='.$total_pages.$pagination_link) }}" aria-label="Next"> <span aria-hidden="true"> >> </span>  </a>
                </li>
            </ul>
        </nav>
    @endif

@endsection