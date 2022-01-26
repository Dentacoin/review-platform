@extends('admin')

@section('content')

    <h1 class="page-header">
        {{ trans('admin.page.'.$current_page.'.title') }}

        @if($admin->role == 'super_admin' && $platform == 'trp')
            <a href="{{ url('cms/emails/trp/send-engagement-email') }}" onclick="return confirm('Are you sure you want to send this email?');" class="btn btn-primary pull-right">Send Re-engagament dentists without reviews last 30 days</a>
            <a href="{{ url('cms/emails/trp/send-monthly-email') }}" class="btn btn-primary pull-right" style="margin-right: 10px;">Send Monthly Email To Petya</a>
        @endif
        
    </h1>

    @if($admin->role == 'super_admin')
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
                                        <option value="">Platform</option>
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
                                <div class="col-md-2">
                                    <input type="text" class="form-control" name="search-sendgrid-id" value="{{ $search_sendgrid_id }}" placeholder="Sendgrid ID">
                                </div>
                                <div class="col-md-2">
                                    <input type="text" class="form-control" name="search-name" value="{{ $search_name }}" placeholder="Name">
                                </div>                  
                                <div class="col-md-2">
                                    <select class="form-control" name="search-category">
                                        <option value="">Subscribe Category</option>
                                        @foreach(config('email-categories') as $key => $category)
                                            <option value="{{ $key }}" {!! $key==$search_category ? 'selected="selected"' : '' !!}>{{ $category }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-1">
                                    <label for="without-category" style="display: flex;align-items: center;margin-top: 7px;font-weight: normal;">
                                        <input id="without-category" type="checkbox" name="without-category" value="1" {!! !empty($without_category) ? 'checked="checked"' : '' !!} style="margin-top: 0px;margin-right: 4px;" />
                                        No category
                                    </label>
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
    @endif

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
                                'sendgrid_template_id'  => array(),
                                'validate_email'  => array('template' => 'admin.parts.table-emails-validate'),
                                'note'  => array(),
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

    @if($platform == 'support')

        <div class="row">
            <!-- begin col-6 -->
            <div class="col-md-12 ui-sortable">
                <form class="form-horizontal" method="post" action="{{ url('cms/emails/add') }}">
                    {!! csrf_field() !!}

                    <div class="panel panel-inverse panel-with-tabs custom-tabs">
                        <div class="panel-heading p-0">
                            <!-- begin nav-tabs -->
                            <div class="tab-overflow overflow-right">
                                <ul class="nav nav-tabs nav-tabs-inverse">
                                    @foreach($langs as $code => $lang_info)
                                        <li class="{{ $loop->first ? 'active' : '' }}">
                                            <a href="javascript:;" lang="{{ $code }}" data-toggle="tab" aria-expanded="false">{{ $lang_info['name'] }}</a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                        <div class="tab-content">
                            <div class="form-group">
                                <label class="col-md-2 control-label">Template name ( only for cms info )</label>
                                <div class="col-md-10">
                                    {{ Form::text('name', null, array('maxlength' => 256, 'class' => 'form-control')) }}
                                </div>
                            </div>
                            @foreach($langs as $code => $lang_info)
                                <div class="tab-pane fade{{ $loop->first ? ' active in' : '' }} lang-{{ $code  }}">
                                    <div class="form-group">
                                        <label class="col-md-2 control-label">Subject line</label>
                                        <div class="col-md-10">
                                            {{ Form::text('subject_'.$code, null, array('maxlength' => 256, 'class' => 'form-control')) }}
                                        </div>
                                    </div>
        
                                    <div class="form-group">
                                        <label class="col-md-2 control-label">Title</label>
                                        <div class="col-md-10">
                                            {{ Form::text('title_'.$code, null, array('maxlength' => 256, 'class' => 'form-control')) }}
                                        </div>
                                    </div>
        
                                    <div class="form-group">
                                        <label class="col-md-2 control-label">Subtitle</label>
                                        <div class="col-md-10">
                                            {{ Form::text('subtitle_'.$code, null, array('maxlength' => 256, 'class' => 'form-control')) }}
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="col-md-2 control-label">Content</label>
                                        <div class="col-md-10">
                                            {{ Form::textarea('content_'.$code, null, array('class' => 'form-control')) }}
                                        </div>
                                    </div>
        
                                    <div class="form-group">
                                        <label class="col-md-2 control-label">Category</label>
                                        <div class="col-md-10">
                                            {{ Form::text('category_'.$code, null, array('maxlength' => 512, 'class' => 'form-control')) }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            <input type="hidden" name="type" value="support">

                            <div class="form-group">
                                <label class="col-md-2 control-label">Note</label>
                                <div class="col-md-10">
                                    {{ Form::textarea('note', null, array('maxlength' => 255, 'class' => 'form-control')) }}
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-10 control-label"></label>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-block btn-sm btn-success">Submit</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif

@endsection
