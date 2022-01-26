@extends('admin')

@section('content')

<h1 class="page-header">VPN Ips</h1>
<!-- end page-header -->



<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <div class="panel-heading-btn">
                    <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                </div>
                <h4 class="panel-title">VPN Ips filter</h4>
            </div>
            <div class="panel-body users-filters">
                <form method="get" action="{{ url('cms/ips/vpn/') }}" id="users-filter-form">
                    <div class="row custom-row" style="margin-bottom: 10px;">
                        <div class="col-md-2">
                            <input type="text" class="form-control" name="search-ip" value="{{ $search_ip }}" placeholder="IP">
                        </div>
                        <div class="col-md-1">
                            <input type="text" class="form-control datepicker" name="search-from" value="{{ $search_from }}" placeholder="From" autocomplete="off">
                        </div>
                        <div class="col-md-1">
                            <input type="text" class="form-control datepicker" name="search-to" value="{{ $search_to }}" placeholder="To" autocomplete="off">
                        </div>
                        <div class="col-md-2">
                            <input type="submit" class="btn btn-sm btn-primary btn-block" value="Search">
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
                <h4 class="panel-title">VPN Ips</h4>
            </div>
            <div class="panel-body">
				<div class="dataTables_wrapper">
				    <div class="row">
				    	<div class="col-sm-12 table-responsive-md">
                    		<p>Total count: {{ $total_count }}</p>
				    		<table class="table table-striped">
				                <thead>
				                    <tr>
				                    	<th>Date</th>
				                    	<th>IP</th>
				                    </tr>
				                </thead>
				                <tbody>
				                	@foreach($items as $item)
				                    	<tr>
				                    		
				                    		<td>
				                    			{{ date('d.m.Y, H:i:s', $item->created_at->timestamp) }}
				                    		</td>
				                    		<td>
				                    			{{ $item->ip }}
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
                <a class="page-link" href="{{ url('cms/ips/vpn/?page=1'.$pagination_link) }}" aria-label="Previous">
                    <span aria-hidden="true"> << </span>
                </a>
            </li>
            <li class="{{ ($page <= 1 ?  'disabled' : '' ) }}">
                <a class="page-link prev" href="{{ url('cms/ips/vpn/?page='.($page>1 ? $page-1 : '1').$pagination_link) }}"  aria-label="Previous">
                    <span aria-hidden="true"> < </span>
                </a>
            </li>
            @for($i=$start; $i<=$end; $i++)
                <li class="{{ ($i == $page ?  'active' : '') }}">
                    <a class="page-link" href="{{ url('cms/ips/vpn/?page='.$i.$pagination_link) }}">{{ $i }}</a>
                </li>
            @endfor
            <li class="{{ ($page >= $total_pages ? 'disabled' : '') }}">
                <a class="page-link next" href="{{ url('cms/ips/vpn/?page='.($page < $total_pages ? $page+1 :  $total_pages).$pagination_link) }}" aria-label="Next"> <span aria-hidden="true"> > </span> </a>
            </li>
            <li class="{{ ($page >= $total_pages ? 'disabled' : '') }}">
                <a class="page-link" href="{{ url('cms/ips/vpn/?page='.$total_pages.$pagination_link) }}" aria-label="Next"> <span aria-hidden="true"> >> </span>  </a>
            </li>
        </ul>
    </nav>
@endif

@endsection