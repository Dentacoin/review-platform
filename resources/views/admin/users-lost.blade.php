@extends('admin')

@section('content')

    <h1 class="page-header">Lost/Deleted Users</h1>
    <!-- end page-header -->

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <h4 class="panel-title">Lost/Deleted Users</h4>
                </div>
                <div class="panel-body">
                    <div class="row table-responsive-md">
                        <p>Registered {{ $registered }} from {{ $total_count }}</p>

                        <table class="table table-striped table-question-list">
                            <thead>
                                <tr>
                                    <th>Email</th>
                                    <th>Registered user</th>
                                    <th>Registered at</th>
                                    <th>Has vip access?</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($items as $item)
                                    <tr>
                                        <td>
                                            {{ $item->email }}
                                        </td>
                                        <td>
                                            @if(!empty($item->user))
                                                <a href="{{ url('cms/users/users/edit/'.$item->user_id) }}">
                                                    {{ $item->user->name }}
                                                </a>
                                            @elseif(!empty($item->emailUser))
                                                <a href="{{ url('cms/users/users/edit/'.$item->emailUser->id) }}">
                                                    {{ $item->emailUser->email }}
                                                </a>
                                            @endif
                                        </td>
                                        <td>
                                            @if(!empty($item->user))
                                                {{ date('d.m.Y, H:i:s', $item->user->created_at->timestamp) }}
                                            @elseif(!empty($item->emailUser))
                                                {{ date('d.m.Y, H:i:s', $item->emailUser->created_at->timestamp) }}
                                            @endif
                                        </td>
                                        <td>
                                            @if(!empty($item->user))
                                                {{ $item->user->vip_access ? 'Yes' : 'No' }}
                                            @elseif(!empty($item->emailUser))
                                                {{ $item->emailUser->vip_access ? 'Yes' : 'No' }}
                                            @endif
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

    @if($total_pages > 1)
        <nav aria-label="Page navigation" style="text-align: center;">
            <ul class="pagination">
                <li class="{{ ($page <= 1 ?  'disabled' : '' ) }}">
                    <a class="page-link" href="{{ url('cms/users/lost_users/?page=1'.$pagination_link) }}" aria-label="Previous">
                        <span aria-hidden="true"> << </span>
                    </a>
                </li>
                <li class="{{ ($page <= 1 ?  'disabled' : '' ) }}">
                    <a class="page-link prev" href="{{ url('cms/users/lost_users/?page='.($page>1 ? $page-1 : '1').$pagination_link) }}"  aria-label="Previous">
                        <span aria-hidden="true"> < </span>
                    </a>
                </li>
                @for($i=$start; $i<=$end; $i++)
                    <li class="{{ ($i == $page ?  'active' : '') }}">
                        <a class="page-link" href="{{ url('cms/users/lost_users/?page='.$i.$pagination_link) }}">{{ $i }}</a>
                    </li>
                @endfor
                <li class="{{ ($page >= $total_pages ? 'disabled' : '') }}">
                    <a class="page-link next" href="{{ url('cms/users/lost_users/?page='.($page < $total_pages ? $page+1 :  $total_pages).$pagination_link) }}" aria-label="Next"> <span aria-hidden="true"> > </span> </a>
                </li>
                <li class="{{ ($page >= $total_pages ? 'disabled' : '') }}">
                    <a class="page-link" href="{{ url('cms/users/lost_users/?page='.$total_pages.$pagination_link) }}" aria-label="Next"> <span aria-hidden="true"> >> </span>  </a>
                </li>
            </ul>
        </nav>
    @endif
@endsection