@extends('admin')

@section('content')

    <h1 class="page-header">Users Bans</h1>
    <!-- end page-header -->

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <div class="panel-heading-btn">
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                    </div>
                    <h4 class="panel-title">Bans filter</h4>
                </div>
                <div class="panel-body users-filters">
                    <form method="get" action="{{ url('cms/users/bans/') }}" id="users-bans-form">
                        <div class="row custom-row" style="margin-bottom: 10px;">
                            <div class="col-md-1">
                                <input type="text" class="form-control" name="search-user-id" value="{{ $search_user_id }}" placeholder="User ID">
                            </div>
                            <div class="col-md-2">
                                <input type="text" class="form-control" name="search-email" value="{{ $search_email }}" placeholder="User Email">
                            </div>
                            <div class="col-md-2">
                                <select class="form-control" name="search-type">
                                    <option value="">Ban Type</option>
                                    <option value="too-fast" {!! 'too-fast'==$search_type ? 'selected="selected"' : '' !!}>Vox Too fast</option>
                                    <option value="mistakes" {!! 'mistakes'==$search_type ? 'selected="selected"' : '' !!}>Vox Mistakes</option>
                                    <option value="reviews" {!! 'reviews'==$search_type ? 'selected="selected"' : '' !!}>TRP Reviews</option>
                                    <option value="spam-review" {!! 'spam-review'==$search_type ? 'selected="selected"' : '' !!}>TRP Spam Reviews</option>
                                </select>
                            </div>                            
                            <div class="col-md-2">
                                <input type="submit" class="btn btn-sm btn-primary btn-block" value="Search">
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <h4 class="panel-title">Users Bans</h4>
                </div>
                <div class="panel-body" id="link">
                    @include('admin.parts.table', [
                        'table_id' => 'bans',
                        'table_fields' => [
                            'created_at'        => array('format' => 'datetime', 'label' => 'Received'),
                            'type'              => array(),
                            'user'              => array('template' => 'admin.parts.table-recommend-user'),
                            'duration'          => array('template' => 'admin.parts.table-bans-duration'),
                            // 'domain'            => array(),
                            'expires'           => array('template' => 'admin.parts.table-bans-expires'),
                            'ban_for'           => array('template' => 'admin.parts.table-bans-for'),
                            'question_id'       => array('template' => 'admin.parts.table-user-ban-question', 'label' => 'Question'),
                            'answer'            => array('label' => 'Answer'),
                            'ban_info'          => array('format' => 'nl2br', 'label' => 'Info'),
                            'admin_description' => array('label' => 'Description'),
                        ],
                        'table_data' => $items,
                        'table_pagination' => false,
                        'pagination_link' => array()
                    ])
                </div>
            </div>
        </div>
    </div>

    @if($total_pages > 1)
        <nav aria-label="Page navigation" style="text-align: center;">
            <ul class="pagination">
                <li class="{{ ($page <= 1 ?  'disabled' : '' ) }}">
                    <a class="page-link" href="{{ url('cms/users/bans/?page=1'.$pagination_link) }}" aria-label="Previous">
                        <span aria-hidden="true"> << </span>
                    </a>
                </li>
                <li class="{{ ($page <= 1 ?  'disabled' : '' ) }}">
                    <a class="page-link prev" href="{{ url('cms/users/bans/?page='.($page>1 ? $page-1 : '1').$pagination_link) }}"  aria-label="Previous">
                        <span aria-hidden="true"> < </span>
                    </a>
                </li>
                @for($i=$start; $i<=$end; $i++)
                    <li class="{{ ($i == $page ?  'active' : '') }}">
                        <a class="page-link" href="{{ url('cms/users/bans/?page='.$i.$pagination_link) }}">{{ $i }}</a>
                    </li>
                @endfor
                <li class="{{ ($page >= $total_pages ? 'disabled' : '') }}">
                    <a class="page-link next" href="{{ url('cms/users/bans/?page='.($page < $total_pages ? $page+1 :  $total_pages).$pagination_link) }}" aria-label="Next"> <span aria-hidden="true"> > </span> </a>
                </li>
                <li class="{{ ($page >= $total_pages ? 'disabled' : '') }}">
                    <a class="page-link" href="{{ url('cms/users/bans/?page='.$total_pages.$pagination_link) }}" aria-label="Next"> <span aria-hidden="true"> >> </span>  </a>
                </li>
            </ul>
        </nav>
    @endif
@endsection