@extends('admin')

@section('content')

    <div class="flex" style="justify-content: space-between;">
        <h1 class="page-header">
            {{ trans('admin.page.'.$current_page.'.title') }}
        </h1>
        <div>
            <a href="javascript:;" class="btn btn-primary pull-right btn-export">Export</a>
            <a href="javascript:;" class="btn btn-primary pull-right btn-export-fb" style="margin-right: 10px;">FB Export</a>
            <a href="javascript:;" class="btn btn-primary pull-right btn-export-sendgrid" style="margin-right: 10px;">SendGrid Export</a>
            <a href="{{ url('cms/users/add') }}" class="btn btn-primary pull-right" style="margin-right: 10px;">Add dentist</a>
            <a href="{{ url('cms/users/import') }}" class="btn btn-primary pull-right" style="margin-right: 10px;">Import dentists</a>
        </div>
    </div>
    <!-- end page-header -->

    <style type="text/css">
        @media screen and (max-width: 767px) {
            .flex {
                display: block;
            }

            .flex a {
                float: none !important;
                margin-bottom: 10px;
            }
        }
    </style>

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <div class="panel-heading-btn">
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                    </div>
                    <h4 class="panel-title"> {{ trans('admin.page.'.$current_page.'.title-filter') }} </h4>
                </div>
                <div class="panel-body users-filters">
                    <form method="get" action="{{ url('cms/'.$current_page) }}" id="users-filter-form">
                        <div class="row custom-row" style="margin-bottom: 10px;">
                            <div class="col-md-2">
                                <select class="form-control select2" multiple name="exclude-countries[]">
                                    @foreach( $countries as $country )
                                        <option value="{{ $country->id }}" {{ !empty($exclude_countries) && in_array($country->id, $exclude_countries) ? 'selected="selected"' : '' }}>{{ $country->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <style type="text/css">
                                .select2-container {
                                    width: 100% !important;
                                }
                            </style>

                            <div class="col-md-2">
                                <label for="exclude-permaban" style="display: flex;align-items: center;margin-top: 7px;font-weight: normal;">
                                    <input id="exclude-permaban" type="checkbox" name="exclude-permaban" value="1" {!! !empty($exclude_permaban) ? 'checked="checked"' : '' !!} style="margin-top: 0px;margin-right: 4px;" />
                                    Exclude Permaban
                                </label>
                            </div>

                            <style type="text/css">
                                .select2-container--default .select2-selection--multiple {
                                    height: auto !important;
                                }
                            </style>
                        </div>
                        <div class="row" style="margin-bottom: 10px;">                        
                            <div class="col-md-2">
                                <select class="form-control" id="search-platform" name="search-platform">
                                    @foreach($user_platforms as $p => $platform)
                                        <option value="{{ $p }}" {!! $p==$search_platform ? 'selected="selected"' : '' !!}>{{ $platform }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select class="form-control select2type" multiple name="search-type[]">
                                    @foreach($user_types as $k => $type)
                                        <option value="{{ $k }}" {{ !empty($search_type) && in_array($k, $search_type) ? 'selected="selected"' : '' }}>{{ $type }}</option>
                                    @endforeach
                                </select>

                                <!-- <select class="form-control" name="search-type">
                                    @foreach($user_types as $k => $type)
                                        <option value="{{ $k }}" {!! $k==$search_type ? 'selected="selected"' : '' !!}>{{ $type }}</option>
                                    @endforeach
                                </select> -->
                            </div>
                            <div class="col-md-2">
                                <select class="form-control" name="search-status">
                                    @foreach($user_statuses as $k => $type)
                                        <option value="{{ $k }}" {!! $k==$search_status ? 'selected="selected"' : '' !!}>{{ $type }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select class="form-control" name="registered-platform">
                                    <option value="">Registered platform</option>
                                    <option value="trp" {!! 'trp'==$registered_platform ? 'selected="selected"' : '' !!}>Trusted Reviews</option>
                                    <option value="vox" {!! 'vox'==$registered_platform ? 'selected="selected"' : '' !!}>DentaVox</option>
                                    <option value="dentists" {!! 'dentists'==$registered_platform ? 'selected="selected"' : '' !!}>Dentists</option>
                                    <option value="dentacoin" {!! 'dentacoin'==$registered_platform ? 'selected="selected"' : '' !!}>Dentacoin</option>
                                    <option value="assurance" {!! 'assurance'==$registered_platform ? 'selected="selected"' : '' !!}>Assurance</option>
                                    <option value="external" {!! 'external'==$registered_platform ? 'selected="selected"' : '' !!}>External</option>
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
                                <input type="text" class="form-control" name="search-login-number" value="{{ $search_login_number }}" placeholder="Min logins number (after date selected)">
                            </div>
                            <div class="col-md-2 filter trp-filter">
                                <input type="text" class="form-control" name="search-review" value="{{ $search_review }}" placeholder="Review (0 -no reviews; number)">
                            </div>
                            <div class="col-md-2 filter trp-filter">
                                <select class="form-control" name="search-dentist-claims">
                                    <option value="">Profile claim</option>
                                    <option value="waiting" {!! $search_dentist_claims == 'waiting' ? 'selected="selected"' : '' !!}>Pending</option>
                                    <option value="approved" {!! $search_dentist_claims == 'approved' ? 'selected="selected"' : '' !!}>Approved</option>
                                    <option value="rejected" {!! $search_dentist_claims == 'rejected' ? 'selected="selected"' : '' !!}>Rejected</option>
                                    <option value="suspicious" {!! $search_dentist_claims == 'suspicious' ? 'selected="selected"' : '' !!}>Suspicious</option>
                                </select>                            
                            </div>
                            <div class="col-md-2 filter trp-filter">
                                <label for="fb-tab" style="display: flex;align-items: center;margin-top: 7px;color: #767676;font-weight: normal;">
                                    <input id="fb-tab" type="checkbox" name="fb-tab" value="1" {!! !empty($fb_tab) ? 'checked="checked"' : '' !!} style="margin-top: 0px;margin-right: 4px;" />
                                    With fb page tab
                                </label>
                            </div>
                            <div class="col-md-2 filter vox-filter">
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
                    <form method="post" action="{{ url('cms/users/mass-reject') }}" id="mass-delete-form" mass-delete-action="{{ url('cms/users/mass-delete') }}" mass-reject-action="{{ url('cms/users/mass-reject') }}">
                        {!! csrf_field() !!}
    					@include('admin.parts.table', [
    						'table_id' => 'users',
    						$table_fields,
                            'table_data' => $users,
    						'table_pagination' => false,
                            'pagination_link' => array(),
                            'users' => true
    					])

                        <input type="hidden" name="mass-delete-reasons">

                        <div style="display: flex">
                            <button type="submit" name="mass-delete" value="1" class="btn btn-danger" style="flex: 1" id="mass-delete-button">Delete selected users</button>
                            <button type="submit" name="mass-reject" value="1" class="btn btn-warning" style="flex: 1" id="mass-reject-button">Reject selected dentists</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div id="deleteModal" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Delete user</h4>
                </div>
                <div class="modal-body">
                    <form action="{{ url('cms/users/delete/') }}" original-action="{{ url('cms/users/delete/') }}" method="post">
                        <textarea class="form-control" name="deleted_reason" placeholder="Write the reason why you want to delete this user"></textarea>
                        <button type="submit" class="btn btn-primary btn-block" style="margin-top: 20px;">Delete</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>

        </div>
    </div>

    <div id="massDeleteModal" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Delete users</h4>
                </div>
                <div class="modal-body">
                    <textarea class="form-control" name="deleted_reason" placeholder="Write the reason why you want to delete this users. This reason will be saved for all selected users."></textarea>
                    <button type="button" class="btn btn-primary btn-block" id="massDeleteModalButton" style="margin-top: 20px;">Delete selected users</button>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>

        </div>
    </div>

    <style type="text/css">
        tr {
            font-size: 12px;
        }
    </style>

@endsection
