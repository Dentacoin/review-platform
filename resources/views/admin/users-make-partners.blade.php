@extends('admin')

@section('content')

    <h1 class="page-header">To be Partners</h1>
    <!-- end page-header -->

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <h4 class="panel-title">To be Partners</h4>
                </div>
                <div class="panel-body" id="link">
                    <div class="row table-responsive-md">
                        <table class="table table-striped table-question-list">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Profile</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($items as $item)
                                    <tr>
                                        <td>
                                            {{ $item->id }}
                                        </td>
                                        <td>
                                            <a href="{{ url('cms/users/users/edit/'.$item->id) }}">{{ $item->getNames() }}</a>
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
@endsection