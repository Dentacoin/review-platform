@extends('admin')

@section('content')

<h1 class="page-header">IPs with more than 1 user in the last 31 days</h1>
<!-- end page-header -->

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <div class="panel-heading-btn">
                    <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                </div>
                <h4 class="panel-title">Scammers</h4>
            </div>
            <div class="panel-body">
        		<div class="panel-body">
                    @foreach($list as $ip => $users)
                        <h3>
                            <a href="{{ url('cms/users?search-ip-address='.$ip.'&search=Search') }}">
                                {{ $ip }}
                            </a>
                        </h3>
                        @foreach($users as $user)
                            <a target="_blank" {!! $user->deleted_at ? 'style="text-decoration: line-through;"' : '' !!} href="{{ url('cms/users/edit/'.$user->id) }}"><b>{{ $user->getName() }}</b> ({{ $user->email }})</a><br/>
                        @endforeach
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection