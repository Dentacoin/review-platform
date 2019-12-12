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
                        <input type="text" class="form-control" name="results-number" value="{{ $results_number }}" placeholder="Results ( enter 0 to show all )">
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
@endsection