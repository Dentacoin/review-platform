@extends('admin')

@section('content')

<h1 class="page-header">Reviews</h1>
<!-- end page-header -->


<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <div class="panel-heading-btn">
                    <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                </div>
                <h4 class="panel-title"> Filters </h4>
            </div>
            <div class="panel-body">
                
                @if($id)
                    <div class="row custom-row" style="margin-bottom: 10px;">
                        <div class="col-md-1">
                            <input type="text" class="form-control" name="id" value="{{ $id }}" placeholder="ID">
                        </div>
                    </div>
                @endif
                <form class="row" method="get" action="{{ url('cms/trp/'.$current_subpage) }}" >
                    <div class="col-md-2">
                        <input type="text" class="form-control" name="search-name-dentist" value="{{ $search_name_dentist }}" placeholder="Dentist/Clinic name">
                    </div>
                    <div class="col-md-2">
                        <input type="text" class="form-control" name="search-name-user" value="{{ $search_name_user }}" placeholder="Patient name">
                    </div>
                    <div class="col-md-2">
                        <input type="text" class="form-control datepicker" name="search-reviews-from" value="{{ $search_reviews_from }}" placeholder="From Date" style="min-width: auto !important;">
                    </div>
                    <div class="col-md-2">
                        <input type="text" class="form-control datepicker" name="search-reviews-to" value="{{ $search_reviews_to }}" placeholder="To Date" style="min-width: auto !important;">
                    </div>
                    <div class="col-md-2">
                        <input type="text" class="form-control" name="search-answer" value="{{ $search_answer }}" placeholder="Review content">
                    </div>
                    <input type="submit" class="btn btn-sm btn-primary col-md-1" value="Search" style="line-height: 31px;">
                    <input type="submit" class="btn btn-secondary btn-sm col-md-1" name="search-deleted" value="Search Deleted" style="line-height: 31px;">
                </form>
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
                <h4 class="panel-title">Reviews</h4>
            </div>
            <div class="panel-body">
                <p>Total count: {{ $total_count }}</p>
                <form method="post" action="{{ url('cms/trp/reviews/mass-delete') }}" >
    				@include('admin.parts.table', [
    					'table_id' => 'users',
    					'table_fields' => [
                            'selector'          => array('format' => 'selector'),
                            'created_at'        => array('format' => 'datetime'),
                            'user'              => array('template' => 'admin.parts.table-reviews-user'),
                            'dentist'           => array('template' => 'admin.parts.table-reviews-dentist'),
                            'rating'            => array(),
                            'verified'          => array('format' => 'bool'),
                            'answer'            => array('format' => 'break-word'),
                            'status'            => array(),
    						'delete'			=> array('format' => 'delete'),
    					],
                        'table_data' => $reviews,
    					'table_pagination' => false,
                        'pagination_link' => array()
    				])

                    <button type="submit" name="mass-delete" value="1" class="btn btn-block btn-primary" onclick="return confirm('Are you sure?');">Delete selected reviews</button>
                </form>
            </div>
        </div>
    </div>
</div>

@if($total_pages > 1)
    <nav aria-label="Page navigation" style="text-align: center;">
        <ul class="pagination">
            <li class="{{ ($page <= 1 ?  'disabled' : '' ) }}">
                <a class="page-link" href="{{ url('cms/trp/reviews/?page=1'.$pagination_link) }}" aria-label="Previous">
                    <span aria-hidden="true"> << </span>
                </a>
            </li>
            <li class="{{ ($page <= 1 ?  'disabled' : '' ) }}">
                <a class="page-link prev" href="{{ url('cms/trp/reviews/?page='.($page>1 ? $page-1 : '1').$pagination_link) }}"  aria-label="Previous">
                    <span aria-hidden="true"> < </span>
                </a>
            </li>
            @for($i=$start; $i<=$end; $i++)
                <li class="{{ ($i == $page ?  'active' : '') }}">
                    <a class="page-link" href="{{ url('cms/trp/reviews/?page='.$i.$pagination_link) }}">{{ $i }}</a>
                </li>
            @endfor
            <li class="{{ ($page >= $total_pages ? 'disabled' : '') }}">
                <a class="page-link next" href="{{ url('cms/trp/reviews/?page='.($page < $total_pages ? $page+1 :  $total_pages).$pagination_link) }}" aria-label="Next"> <span aria-hidden="true"> > </span> </a>
            </li>
            <li class="{{ ($page >= $total_pages ? 'disabled' : '') }}">
                <a class="page-link" href="{{ url('cms/trp/reviews/?page='.$total_pages.$pagination_link) }}" aria-label="Next"> <span aria-hidden="true"> >> </span>  </a>
            </li>
        </ul>
    </nav>
@endif

@endsection