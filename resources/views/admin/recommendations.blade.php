@extends('admin')

@section('content')

<h1 class="page-header">Recommendations</h1>
<!-- end page-header -->


<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <div class="panel-heading-btn">
                    <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                </div>
                <h4 class="panel-title"> Filter </h4>
            </div>
            <div class="panel-body">
                <form class="col-md-12" method="get" action="{{ url('cms/vox/recommendations') }}" >
                    <div class="col-md-2">
                        <input type="text" class="form-control" name="search-user-id" value="{{ $search_user_id }}" placeholder="User ID">
                    </div>
                    <div class="col-md-2">
                        <input type="text" class="form-control" name="search-name-user" value="{{ $search_name_user }}" placeholder="User name">
                    </div>
                    <div class="col-md-2">
                        <input type="text" class="form-control" name="search-scale" value="{{ $search_scale }}" placeholder="Scale number">
                    </div>
                    <input type="submit" class="btn btn-sm btn-primary col-md-1" name="search" value="Search">
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
                <h4 class="panel-title">{{ trans('admin.page.'.$current_page.'.title') }}</h4>
            </div>
            <div class="panel-body">
                <div>
                    Recommendations count: {{ $total_count }}
                </div>
                <div class="panel-body">
    				@include('admin.parts.table', [
    					'table_id' => 'users',
    					'table_fields' => [
                            'created_at'        => array('format' => 'datetime'),
                            'user'              => array('template' => 'admin.parts.table-recommend-user','label' => 'Name'),
                            'scale'            => array('label' => 'Scale'),
                            'description'       => array('format' => 'break-word','label' => 'Comment'),
    					],
                        'table_data' => $recommendations,
    					'table_pagination' => false,
                        'pagination_link' => array()
    				])
                </div>
            </div>
        </div>
    </div>
</div>

@if($total_pages > 1)
    <nav aria-label="Page navigation" style="text-align: center;">
        <ul class="pagination">
            <li class="{{ ($page <= 1 ?  'disabled' : '' ) }}">
                <a class="page-link" href="{{ url('cms/vox/recommendations/?page=1'.$pagination_link) }}" aria-label="Previous">
                    <span aria-hidden="true"> << </span>
                </a>
            </li>
            <li class="{{ ($page <= 1 ?  'disabled' : '' ) }}">
                <a class="page-link prev" href="{{ url('cms/vox/recommendations/?page='.($page>1 ? $page-1 : '1').$pagination_link) }}"  aria-label="Previous">
                    <span aria-hidden="true"> < </span>
                </a>
            </li>
            @for($i=$start; $i<=$end; $i++)
                <li class="{{ ($i == $page ?  'active' : '') }}">
                    <a class="page-link" href="{{ url('cms/vox/recommendations/?page='.$i.$pagination_link) }}">{{ $i }}</a>
                </li>
            @endfor
            <li class="{{ ($page >= $total_pages ? 'disabled' : '') }}">
                <a class="page-link next" href="{{ url('cms/vox/recommendations/?page='.($page < $total_pages ? $page+1 :  $total_pages).$pagination_link) }}" aria-label="Next"> <span aria-hidden="true"> > </span> </a>
            </li>
            <li class="{{ ($page >= $total_pages ? 'disabled' : '') }}">
                <a class="page-link" href="{{ url('cms/vox/recommendations/?page='.$total_pages.$pagination_link) }}" aria-label="Next"> <span aria-hidden="true"> >> </span>  </a>
            </li>
        </ul>
    </nav>
@endif

@endsection