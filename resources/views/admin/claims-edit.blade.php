@extends('admin')

@section('content')

    <h1 class="page-header">Claim Dentist Profile For <a href="{{ url('cms/users/users/edit/'.$item->user->id) }}"> {{ $item->user->name }} </a></h1>

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <div class="panel-heading-btn">
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                    </div>
                    <h4 class="panel-title">Claim Request Edit</h4>
                </div>
                <div class="panel-body">
                    {!! Form::open(array(
                        'url' => url('cms/claims/edit/'.$item->id), 
                        'method' => 'post', 
                        'class' => 'form-horizontal')
                    ) !!}

                        {!! csrf_field() !!}

                        <div class="form-group">
                            <label class="col-md-2 control-label">Name</label>
                            <div class="col-md-4">
                                {{ Form::text('name', $item->name, array('class' => 'form-control')) }}
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="col-md-2 control-label">Email</label>
                            <div class="col-md-4">
                                {{ Form::email('email', $item->email, array('class' => 'form-control')) }}
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="col-md-2 control-label">Job</label>
                            <div class="col-md-4">
                                {{ Form::text('job', $item->job, array('class' => 'form-control')) }}
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-2 control-label">Phone</label>
                            <div class="col-md-4">
                                {{ Form::text('phone', $item->phone, array('class' => 'form-control')) }}
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-2 control-label">Explain</label>
                            <div class="col-md-4">
                                {{ Form::textarea('explain_related', $item->explain_related, array('class' => 'form-control')) }}
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="col-md-2 control-label">Status</label>
                            <div class="col-md-4">
                                {{ Form::select('status', $statuses, $item->status, array('class' => 'form-control')) }}
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-2"></div>
                            <div class="col-md-4">
                                <button type="submit" name="update" class="btn btn-block btn-sm btn-success form-control">Save</button>
                            </div>
                        </div>

                    {!! Form::close() !!}
                </div>
            </div>
            <!-- end panel -->
        </div>
    </div>

@endsection