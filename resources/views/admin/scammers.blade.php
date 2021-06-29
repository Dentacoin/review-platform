@extends('admin')

@section('content')

<h1 class="page-header">IPs with more than 1 user</h1>
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
                            <a target="_blank" {!! $user->deleted_at ? 'style="text-decoration: line-through;"' : '' !!} href="{{ url('cms/users/users/edit/'.$user->id) }}"><b>{{ $user->getNames() }}</b> ({{ $user->email }})</a><br/>
                        @endforeach
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

@if($total_pages > 1)
    <nav aria-label="Page navigation" style="text-align: center;">
        <ul class="pagination">
            <li class="{{ ($page <= 1 ?  'disabled' : '' ) }}">
                <a class="page-link" href="{{ url('cms/ips/bad/?page=1') }}" aria-label="Previous">
                    <span aria-hidden="true"> << </span>
                </a>
            </li>
            <li class="{{ ($page <= 1 ?  'disabled' : '' ) }}">
                <a class="page-link prev" href="{{ url('cms/ips/bad/?page='.($page>1 ? $page-1 : '1')) }}"  aria-label="Previous">
                    <span aria-hidden="true"> < </span>
                </a>
            </li>
            @for($i=$start; $i<=$end; $i++)
                <li class="{{ ($i == $page ?  'active' : '') }}">
                    <a class="page-link" href="{{ url('cms/ips/bad/?page='.$i) }}">{{ $i }}</a>
                </li>
            @endfor
            <li class="{{ ($page >= $total_pages ? 'disabled' : '') }}">
                <a class="page-link next" href="{{ url('cms/ips/bad/?page='.($page < $total_pages ? $page+1 :  $total_pages)) }}" aria-label="Next"> <span aria-hidden="true"> > </span> </a>
            </li>
            <li class="{{ ($page >= $total_pages ? 'disabled' : '') }}">
                <a class="page-link" href="{{ url('cms/ips/bad/?page='.$total_pages) }}" aria-label="Next"> <span aria-hidden="true"> >> </span>  </a>
            </li>
        </ul>
    </nav>
@endif

@endsection