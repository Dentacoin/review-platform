@extends('admin')

@section('content')

    <h1 class="page-header">Admins Messages</h1>
    <!-- end page-header -->
    
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <h4 class="panel-title">Admins Messages</h4>
                </div>
                <div class="panel-body">
                    <div class="row table-responsive-md">
                        <table class="table table-striped table-question-list">
                            <thead>
                                <tr>
                                    <th>Admin</th>
                                    <th>Message</th>
                                    <th>Is read?</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(App\Models\AdminMessage::orderBy('id', 'desc')->get() as $message)
                                    <tr>
                                        <td>
                                            {{ $message->admin->name }}
                                        </td>
                                        <td>
                                            {{ $message->message }}
                                        </td>
                                        <td>
                                            {!! $message->is_read ? '<span class="label label-success">'.trans('admin.common.yes').'</span>' : '<span class="label label-warning">'.trans('admin.common.no').'</span>' !!}
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

    <div class="row">
        <!-- begin col-6 -->
        <div class="col-md-12">
            <!-- begin panel -->
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <div class="panel-heading-btn">
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                    </div>
                    <h4 class="panel-title">Add Message</h4>
                </div>
                <div class="panel-body">
                    <form class="form-horizontal" id="admin-message-add" method="post" action="{{ url('cms/admins/add-message') }}">
                        {!! csrf_field() !!}
                        
                        <div class="form-group">
                            <label class="col-md-2 control-label">Admin</label>
                            <div class="col-md-4">
                                <select class="form-control" name="admin_id">
                                    <option value="">-</option>
                                    @foreach(App\Models\Admin::get() as $admin)
                                        <option value="{{ $admin->id }}">{{ $admin->name ?? $admin->username }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="col-md-2 control-label">Message</label>
                            <div class="col-md-4">
                                {{ Form::textarea('message', '', array('class' => 'form-control')) }}
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-4 col-md-offset-2">
                                <button type="submit" class="btn btn-block btn-success">{{ trans('admin.page.'.$current_page.'.submit') }}</button>
                            </div>
                            <div class="col-md-6"></div>
                        </div>
                    </form>
                </div>
            </div>
            <!-- end panel -->
        </div>
    </div>

@endsection