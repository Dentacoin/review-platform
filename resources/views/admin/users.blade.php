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
                <form method="get" action="{{ url('cms/'.$current_page) }}" >
                    <div class="row">
                        <div class="col-md-2">
                            <input type="text" class="form-control" name="search-name" value="{{ $search_name }}" placeholder="{{ trans('admin.page.'.$current_page.'.title-filter-name') }}">
                        </div>
                        <div class="col-md-2">
                            <input type="text" class="form-control" name="search-email" value="{{ $search_email }}" placeholder="{{ trans('admin.page.'.$current_page.'.title-filter-email') }}">
                        </div>
                        <div class="col-md-2">
                            <input type="text" class="form-control" name="search-phone" value="{{ $search_phone }}" placeholder="{{ trans('admin.page.'.$current_page.'.title-filter-phone') }}">
                        </div>
                        <div class="col-md-2">
                            <input type="text" class="form-control" name="search-id" value="{{ $search_id }}" placeholder="{{ trans('admin.page.'.$current_page.'.title-filter-id') }}">
                        </div>
                        <div class="col-md-2">
                            <input type="text" class="form-control" name="search-address" value="{{ $search_address }}" placeholder="{{ trans('admin.page.'.$current_page.'.title-filter-address') }}">
                        </div>
                        <div class="col-md-2">
                            <input type="text" class="form-control" name="search-tx" value="{{ $search_tx }}" placeholder="{{ trans('admin.page.'.$current_page.'.title-filter-tx') }}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2">
                            <input type="text" class="form-control" name="results-number" value="{{ $results_number }}" placeholder="Results ( enter 0 to show all )">
                        </div>
                        <div class="col-md-2">
                            <input type="text" class="form-control" name="search-ip-address" value="{{ $search_ip_address }}" placeholder="IP Address">
                        </div>
                        <div class="col-md-2">
                            <input type="text" class="form-control" name="search-register-from" value="{{ $search_register_from }}" placeholder="{{ trans('admin.page.'.$current_page.'.title-filter-register-from') }}">
                        </div>
                        <div class="col-md-2">
                            <input type="text" class="form-control" name="search-register-to" value="{{ $search_register_to }}" placeholder="{{ trans('admin.page.'.$current_page.'.title-filter-register-to') }}">
                        </div>
                        <div class="col-md-4">
                            <input type="submit" class="btn btn-sm btn-primary btn-block" name="search" value="{{ trans('admin.page.'.$current_page.'.title-filter-submit') }}">
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
                <h4 class="panel-title">{{ trans('admin.page.'.$current_page.'.title') }}</h4>
            </div>
            <div class="panel-body">
        		<div class="panel-body">
                    <form method="post" action="{{ url('cms/users/mass-delete') }}" >
                        {!! csrf_field() !!}
    					@include('admin.parts.table', [
    						'table_id' => 'users',
    						'table_fields' => [
                                'selector'                => array('format' => 'selector'),
                                'id'                => array(),
                                'name'              => array(),
                                'email'              => array(),
                                'phone'              => array('template' => 'admin.parts.table-users-phone'),
                                'city_id'                => array('format' => 'city'),
    							'country_id'				=> array('format' => 'country'),
                                'is_dentist'                => array('format' => 'bool'),
                                'is_partner'                => array('format' => 'bool'),
                                'ratings'                => array('template' => 'admin.parts.table-users-ratings'),
                                'link'                => array('template' => 'admin.parts.table-users-link'),
                                'login'                => array('template' => 'admin.parts.table-users-login'),
    							'update'			=> array('format' => 'update'),
    							'delete'			=> array('format' => 'delete'),
    						],
                            'table_data' => $users,
    						'table_pagination' => false,
                            'pagination_link' => array()
    					])

                        <button type="submit" name="mass-delete" value="1" class="btn btn-block btn-primary" onclick="return confirm('Are you sure?');">Delete selected users</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection