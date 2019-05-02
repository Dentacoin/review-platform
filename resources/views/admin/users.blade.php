@extends('admin')

@section('content')

<h1 class="page-header">
    {{ trans('admin.page.'.$current_page.'.title') }}
    <a href="javascript:;" class="btn btn-primary pull-right btn-export">Export</a>
    <a href="javascript:;" class="btn btn-primary pull-right btn-export-fb" style="margin-right: 10px;">FB Export</a>
</h1>
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
                <form method="get" action="{{ url('cms/'.$current_page) }}" id="users-filter-form">
                    <div class="row" style="margin-bottom: 10px;">                        
                        <div class="col-md-2">
                            <select class="form-control" name="search-platform">
                                @foreach($user_platforms as $p => $platform)
                                    <option value="{{ $p }}" {!! $p==$search_platform ? 'selected="selected"' : '' !!}>{{ $platform }}</option>
                                @endforeach
                            </select>
                        </div>                        
                        <div class="col-md-2">
                            <select class="form-control" name="search-type">
                                @foreach($user_types as $k => $type)
                                    <option value="{{ $k }}" {!! $k==$search_type ? 'selected="selected"' : '' !!}>{{ $type }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-control" name="search-status">
                                @foreach($user_statuses as $k => $type)
                                    <option value="{{ $k }}" {!! $k==$search_status ? 'selected="selected"' : '' !!}>{{ $type }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row custom-row" style="margin-bottom: 10px;">
                        <div class="col-md-2" style="display: none;">
                            <input type="text" class="form-control" name="results-number" value="{{ $results_number }}" placeholder="Results ( enter 0 to show all )">
                        </div>
                        <div class="col-md-1">
                            <input type="text" class="form-control datepicker" name="search-register-from" value="{{ $search_register_from }}" placeholder="{{ trans('admin.page.'.$current_page.'.title-filter-register-from') }}" autocomplete="off">
                        </div>
                        <div class="col-md-1">
                            <input type="text" class="form-control datepicker" name="search-register-to" value="{{ $search_register_to }}" placeholder="{{ trans('admin.page.'.$current_page.'.title-filter-register-to') }}" autocomplete="off">
                        </div>
                        <div class="col-md-2">
                            <input type="text" class="form-control datepicker" name="search-login-after" value="{{ $search_login_after }}" placeholder="Logged in after" autocomplete="off">
                        </div>
                        <div class="col-md-2">
                            <input type="text" class="form-control" name="search-login-number" value="{{ $search_login_number }}" placeholder="Logins number (after date selected)">
                        </div>
                        <div class="col-md-2 {!! !empty($trp_hidden) ? 'filter-hidden' : '' !!}">
                            <input type="text" class="form-control" name="search-review" value="{{ $search_review }}" placeholder="Review (0 -no reviews; number)">
                        </div>
                        <div class="col-md-2 {!! !empty($vox_hidden) ? 'filter-hidden' : '' !!}">
                            <input type="text" class="form-control" name="search-surveys-taken" value="{{ $search_surveys_taken }}" placeholder="Surveys taken (0 -no surveys; number)">
                        </div>
                    </div>
                    <div class="row" style="margin-bottom: 10px;">
                        <div class="col-md-1">
                            <input type="text" class="form-control" name="search-id" value="{{ $search_id }}" placeholder="{{ trans('admin.page.'.$current_page.'.title-filter-id') }}">
                        </div>
                        <div class="col-md-2">
                            <input type="text" class="form-control" name="search-name" value="{{ $search_name }}" placeholder="{{ trans('admin.page.'.$current_page.'.title-filter-name') }}">
                        </div>
                        <div class="col-md-2">
                            <input type="text" class="form-control" name="search-email" value="{{ $search_email }}" placeholder="{{ trans('admin.page.'.$current_page.'.title-filter-email') }}">
                        </div>
                        <div class="col-md-2">
                            <select class="form-control" name="search-country">
                                <option value="">All Countries</option>
                                @foreach( $countries as $country )
                                    <option value="{{ $country->id }}" {!! $country->id==$search_country ? 'selected="selected"' : '' !!}>{{ $country->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="text" class="form-control" name="search-ip-address" value="{{ $search_ip_address }}" placeholder="IP Address">
                        </div>
                        <!-- <div class="col-md-2">
                            <input type="text" class="form-control" name="search-phone" value="{{ $search_phone }}" placeholder="{{ trans('admin.page.'.$current_page.'.title-filter-phone') }}">
                        </div> -->
                        <div class="col-md-3">
                            <input type="text" class="form-control" name="search-address" value="{{ $search_address }}" placeholder="{{ trans('admin.page.'.$current_page.'.title-filter-address') }}">
                        </div>
                    </div>
                    <div class="row">                        
                        <div class="col-md-12">
                            <input type="submit" class="btn btn-sm btn-primary btn-block" name="search" value="{{ trans('admin.page.'.$current_page.'.title-filter-submit') }}">
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>



<div class="row with-limits">
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
                    <span>Results shown <input type="text" class="form-control" name="results-number2" value="{{ $results_number ? $results_number : 50 }}" style="display: inline-block;width: 60px;"> </span>
                    out of total {{ $total_count }} profiles that match this search
                </div>
                <!-- <b>
                    Showing {{ $users->isNotEmpty() ? $users->count() : '0' }} out of total {{ $total_count }} profiles that match this search
                </b> -->
                <form method="post" action="{{ url('cms/users/mass-delete') }}" >
                    {!! csrf_field() !!}
					@include('admin.parts.table', [
						'table_id' => 'users',
						$table_fields,
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
@endsection