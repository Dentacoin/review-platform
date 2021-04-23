@extends('admin')

@section('content')

<h1 class="page-header">Add a new branch for existing clinics</h1>
<!-- end page-header -->

<div class="row">
    <!-- begin col-6 -->
    <div class="col-md-12">
        <!-- begin panel -->
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <div class="panel-heading-btn">
                    <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                </div>
                <h4 class="panel-title">Add a new branch for existing clinics</h4>
            </div>
            <div class="panel-body">
                <form class="form-horizontal" method="post" action="{{ url('cms/trp/add-clinic-branch') }}">

                    <div class="form-group">
                        <label class="col-md-2 control-label">Main Clinic ID</label>
                        <div class="col-md-2">
                            {{ Form::text('main_clinic_id', null, array('class' => 'form-control')) }}
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-2 control-label">Branch Clinic ID</label>
                        <div class="col-md-2">
                            {{ Form::text('branch_clinic_id', null, array('class' => 'form-control')) }}
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-11 control-label"></label>
                        <div class="col-md-1">
                            <button type="submit" class="btn btn-block btn-sm btn-success form-control">Save</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <!-- end panel -->
    </div>
</div>


@endsection