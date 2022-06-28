@extends('admin')

@section('content')

    <h1 class="page-header">Whitelist IPs</h1>

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <div class="panel-heading-btn">
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                    </div>
                    <h4 class="panel-title">Whitelisting IPs</h4>
                </div>
                <div class="panel-body">
                    <div class="panel-body">
                        @include('admin.parts.table', [
                            'table_id' => 'whitelist',
                            'table_fields' => [
                                'ip'            => array(),
                                'comment'       => array(),
                                'delete'		=> array('format' => 'delete'),
                            ],
                            'table_data' => $items,
                            'table_pagination' => false,
                            'pagination_link' => array()
                        ])
                    </div>
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
                    <h4 class="panel-title">Add new item</h4>
                </div>
                <div class="panel-body">
                    <div class="panel-body">
                        <form class="form-horizontal" method="post">
                            {!! csrf_field() !!}
                            
                            <div class="form-group">
                                <label class="col-md-2 control-label">
                                    IP
                                </label>
                                <div class="col-md-4">
                                    {{ Form::text('ip', old('ip'), array('class' => 'form-control')) }}
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-2 control-label">Notes (i.e. why you added it)</label>
                                <div class="col-md-4">
                                    {{ Form::textarea('comment', old('comment'), array('class' => 'form-control')) }}
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-md-2"></div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-block btn-sm btn-success">Save</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection