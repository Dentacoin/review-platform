@extends('admin')

@section('content')

    <h1 class="page-header">Users Registration Statsistics</h1>
    <!-- end page-header -->

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <div class="panel-heading-btn">
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                    </div>
                    <h4 class="panel-title">Users Registration Statsistics</h4>
                </div>
                <div class="panel-body">
                    <div class="panel-body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    @foreach(current($table) as $k => $v)
                                        <th>{{ $v['label'] }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($table as $row)
                                    <tr>
                                    @foreach($row as $v)
                                        <td>{{ $v['value'] }}</td>
                                    @endforeach                                
                                    </tr>
                                @endforeach
                                
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection