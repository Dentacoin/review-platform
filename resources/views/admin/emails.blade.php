@extends('admin')

@section('content')

<h1 class="page-header">
    {{ trans('admin.page.'.$current_page.'.title') }}

    @if($platform == 'trp')
        <a href="{{ url('cms/emails/trp/send-engagement-email') }}" onclick="return confirm('Are you sure you want to send this email?');" class="btn btn-primary pull-right">Send Re-engagament dentists without reviews last 30 days</a>
        <a href="{{ url('cms/emails/trp/send-monthly-email') }}" class="btn btn-primary pull-right" style="margin-right: 10px;">Send Monthly Email To Petya</a>
    @endif
    
</h1>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <div class="panel-heading-btn">
                    <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                </div>
                <h4 class="panel-title"> Search Emails </h4>
            </div>
            <div class="panel-body users-filters">
                <form method="get" action="{{ url('cms/emails') }}" id="users-filter-form">
                    <div class="row custom-row" style="margin-bottom: 10px;">
                    <div class="row" style="margin-bottom: 10px;">                        
                        <div class="col-md-2">
                            <select class="form-control" id="search-platform" name="search-platform">
                                <option value="">-</option>
                                <option value="trp" {!! 'trp'==$search_platform ? 'selected="selected"' : '' !!}>TRP</option>
                                <option value="vox" {!! 'vox'==$search_platform ? 'selected="selected"' : '' !!}>Dentavox</option>
                                <option value="dentacare" {!! 'dentacare'==$search_platform ? 'selected="selected"' : '' !!}>Dentacare</option>
                                <option value="assurance" {!! 'assurance'==$search_platform ? 'selected="selected"' : '' !!}>Assurance</option>
                                <option value="dentacoin" {!! 'dentacoin'==$search_platform ? 'selected="selected"' : '' !!}>Dentacoin</option>
                                <option value="dentists" {!! 'dentists'==$search_platform ? 'selected="selected"' : '' !!}>Dentists</option>
                                <option value="common" {!! 'common'==$search_platform ? 'selected="selected"' : '' !!}>Common</option>
                            </select>
                        </div>
                        <div class="col-md-1">
                            <input type="text" class="form-control" name="search-id" value="{{ $search_id }}" placeholder="ID">
                        </div>
                        <div class="col-md-3">
                            <input type="text" class="form-control" name="search-sendgrid-id" value="{{ $search_sendgrid_id }}" placeholder="Sendgrid ID">
                        </div>
                        <div class="col-md-2">
                            <input type="text" class="form-control" name="search-name" value="{{ $search_name }}" placeholder="Name">
                        </div>
                        <div class="col-md-2">
                            <label for="without-category" style="display: flex;align-items: center;margin-top: 7px;font-weight: normal;">
                                <input id="without-category" type="checkbox" name="without-category" value="1" {!! !empty($without_category) ? 'checked="checked"' : '' !!} style="margin-top: 0px;margin-right: 4px;" />
                                No category
                            </label>
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

@include('admin.errors')

<!-- end page-header -->
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
					@include('admin.parts.table', [
						'table_id' => 'emails-system',
						'table_fields' => [
                            'name'              => array(),
                            'email_categories'  => array('template' => 'admin.parts.table-emails-categories'),
                            'update'            => array('template' => 'admin.parts.table-emails-edit'),
						],
                        'table_data' => $templates,
						'table_pagination' => false,
					])
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
