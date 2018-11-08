@extends('admin')

@section('content')

<h1 class="page-header">{{ trans('admin.page.'.$current_page.'.title') }}</h1>
<!-- end page-header -->


<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <div class="panel-heading-btn">
                    <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                </div>
                <h4 class="panel-title"> {{ trans('admin.page.'.$current_page.'.title-filter') }} </h4>
            </div>
            <div class="panel-body">
                <form class="col-md-12" method="get" action="{{ url('cms/'.$current_page) }}" >
                    <div class="col-md-2">
                        <input type="text" class="form-control" name="search-name-dentist" value="{{ $search_name_dentist }}" placeholder="Dentist/Clinic name">
                    </div>
                    <div class="col-md-2">
                        <input type="text" class="form-control" name="search-name-user" value="{{ $search_name_user }}" placeholder="Patient name">
                    </div>
                    <div class="col-md-1">
                        <input type="text" class="form-control" name="search-reviews-to" value="{{ $search_reviews_to }}" placeholder="Written before date">
                    </div>
                    <div class="col-md-1">
                        <input type="text" class="form-control" name="search-reviews-from" value="{{ $search_reviews_from }}" placeholder="Written after date">
                    </div>
                    <div class="col-md-2">
                        <input type="text" class="form-control" name="search-answer" value="{{ $search_answer }}" placeholder="Review content">
                    </div>
                    <div class="col-md-2">
                        <input type="text" class="form-control" name="results-number" value="{{ $results_number }}" placeholder="Results ( enter 0 to show all )">
                    </div>
                    <input type="submit" class="btn btn-sm btn-primary col-md-1" name="search" value="Search">
                    <input type="submit" class="btn btn-secondary btn-sm col-md-1" name="search-deleted" value="Search Deleted">
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
                <form method="post" action="{{ url('cms/reviews/mass-delete') }}" >
    				@include('admin.parts.table', [
    					'table_id' => 'users',
    					'table_fields' => [
                            'selector'                => array('format' => 'selector'),
                            'created_at'        => array('format' => 'datetime'),
                            'user'              => array('template' => 'admin.parts.table-reviews-user'),
                            'dentist'           => array('template' => 'admin.parts.table-reviews-dentist'),
                            'rating'            => array(),
                            'verified'              => array('format' => 'bool'),
                            'answer'              => array('format' => 'break-word'),
                            'status'              => array(),
    						'delete'			=> array('format' => 'delete'),
    					],
                        'table_data' => $reviews,
    					'table_pagination' => false,
                        'pagination_link' => array()
    				])

                    <button type="submit" name="mass-delete" value="1" class="btn btn-block btn-primary" onclick="return confirm('Are you sure?');">Delete selected reviews</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection