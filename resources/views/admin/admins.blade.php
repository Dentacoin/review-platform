@extends('admin')

@section('content')

    <h1 class="page-header">{{ trans('admin.page.'.$current_page.'.title') }}</h1>

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
                        'table_id' => 'admins',
                        'table_fields' => [
                            'id'				=> array('name' => 'ID'),
                            'name'			    => array('name' => 'Name', 'label' => 'Name'),
                            'username'			=> array('name' => 'Username'),
                            'email'				=> array('name' => 'Email'),
                            'role'              => array('name' => 'Role'),
                            'comments'          => array('name' => 'Comments'),
                            'reset_auth'        => array(
                                'name' => 'Reset Two Factor Auth', 
                                'label' => 'Two Factor Authentication', 
                                'template' => 'admin.parts.table-admins-two-factor-auth'
                            ),
                            'update'			=> array('name' => 'Update', 'format' => 'update'),
                            'delete'			=> array('name' => 'Delete', 'format' => 'delete'),
                        ],
                        'table_data' => $admins_list,
                        'table_pagination' => false,
                        'pagination_link' => array()
                    ])
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
                    <h4 class="panel-title">{{ trans('admin.page.'.$current_page.'.add') }}</h4>
                </div>
                <div class="panel-body">
                    <form class="form-horizontal" id="admin-add" method="post" action="{{ url('cms/admins/admins/add') }}">
                        {!! csrf_field() !!}
                        
                        <div class="form-group">
                            <label class="col-md-2 control-label">{{ trans('admin.page.'.$current_page.'.username') }}</label>
                            <div class="col-md-4">
                                {{ Form::text('username', '', array('class' => 'form-control')) }}
                            </div>
                            <label class="col-md-2 control-label">{{ trans('admin.page.'.$current_page.'.password') }}</label>
                            <div class="col-md-4">
                                {{ Form::text('password', '', array('class' => 'form-control')) }}
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-2 control-label">Name</label>
                            <div class="col-md-4">
                                {{ Form::text('name', '', array('class' => 'form-control')) }}
                            </div>
                            <label class="col-md-2 control-label">{{ trans('admin.page.'.$current_page.'.role') }}</label>
                            <div class="col-md-4">
                                {{ Form::select('role', $roles, null, array('class' => 'form-control')) }}
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-2 control-label">{{ trans('admin.page.'.$current_page.'.email') }}</label>
                            <div class="col-md-4">
                                {{ Form::text('email', '', array('class' => 'form-control')) }}
                            </div>
                            <div class="col-md-2"></div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-block btn-success">{{ trans('admin.page.'.$current_page.'.submit') }}</button>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-8"></div>
                            <div class="col-md-4">
                                Roles info: <br/>
                                - super admin - All permissions <br/>
                                - admins - everything except: <br/>
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- logs <br/>
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- activity history <br/>
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- email templates <br/>
                                - translator - only translations <br/>
                                - voxer - only voxes <br/>
                                - support - access to: <br/>
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- Users <br/>
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- IPs <br/>
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- Whitelist <br/>
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- Blacklist <br/>
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- Invites <br/>
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- Transactions <br/>
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- Rewards <br/>
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- Email Validations <br/>
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- Trusted Reviews <br/>
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- Voxes <br/>
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- Support <br/>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection