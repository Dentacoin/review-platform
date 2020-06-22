@extends('admin')

@section('content')

    <h1 class="page-header">
        Incomplete Dentist Registrations
        <a href="javascript:;" class="btn btn-primary pull-right btn-export">Export</a>
    </h1>

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <div class="panel-heading-btn">
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                    </div>
                    <h4 class="panel-title"> Filter Incompletes </h4>
                </div>
                <div class="panel-body">
                    <form method="get" action="{{ url('cms/incomplete/incomplete/') }}" >

                        <div class="row" style="margin-bottom: 10px;">
                            <div class="col-md-2">
                                <input type="text" class="form-control" name="search-name" value="{{ $search_name }}" placeholder="Name">
                            </div>
                            <div class="col-md-2">
                                <input type="text" class="form-control" name="search-email" value="{{ $search_email }}" placeholder="Email">
                            </div>
                            <div class="col-md-2">
                                <select class="form-control" name="search-country">
                                    <option value="">All Countries</option>
                                    @foreach( $countries as $country )
                                        <option value="{{ $country->id }}" {!! $country->id==$search_country ? 'selected="selected"' : '' !!}>{{ $country->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="text" class="form-control" name="search-phone" value="{{ $search_phone }}" placeholder="Phone">
                            </div>
                            <div class="col-md-2">
                                <input type="text" class="form-control" name="search-website" value="{{ $search_website }}" placeholder="Website">
                            </div>
                            <div class="col-md-2">
                                <input type="submit" class="btn btn-block btn-primary btn-block" name="search" value="Submit">
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
                    <div class="panel-heading-btn">
                        <a href="{{ url('/cms/incomplete/') }}?export=1" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                    </div>
                    <h4 class="panel-title">Incomplete Dentist Registrations</h4>
                </div>
                <div class="panel-body">
            		<div class="panel-body">
    					@include('admin.parts.table', [
    						'table_id' => 'incomplete',
    						'table_fields' => [
                                'id'                => array('label' => '#'),
                                'created_at'                => array('format' => 'datetime', 'label' => 'Date'),
                                'name'              => array('label' => 'Name'),
                                'email'              => array('label' => 'Email'),
                                'phone'              => array('label' => 'Phone'),
                                'country_id'                => array('format' => 'country', 'label' => 'Country'),
                                'website'                => array('label' => 'Website'),
                                'platform'                => array('label' => 'Platform'),
                                'completed'                => array('format' => 'bool', 'label' => 'Registered'),
                                'notified1'                => array('format' => 'bool', 'label' => 'Email 1h'),
                                'notified2'                => array('format' => 'bool', 'label' => 'Email 24h'),
                                'notified3'                => array('format' => 'bool', 'label' => 'Email 72h'),
                                'unsubscribed'               => array('format' => 'bool', 'label' => 'Unsubscribed'),
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

    @if($total_pages > 1)
        <nav aria-label="Page navigation" style="text-align: center;">
            <ul class="pagination">
                <li class="{{ ($page <= 1 ?  'disabled' : '' ) }}">
                    <a class="page-link" href="{{ url('cms/incomplete/incomplete/?page=1'.$pagination_link) }}" aria-label="Previous">
                        <span aria-hidden="true"> << </span>
                    </a>
                </li>
                <li class="{{ ($page <= 1 ?  'disabled' : '' ) }}">
                    <a class="page-link prev" href="{{ url('cms/incomplete/incomplete/?page='.($page>1 ? $page-1 : '1').$pagination_link) }}"  aria-label="Previous">
                        <span aria-hidden="true"> < </span>
                    </a>
                </li>
                @for($i=$start; $i<=$end; $i++)
                    <li class="{{ ($i == $page ?  'active' : '') }}">
                        <a class="page-link" href="{{ url('cms/incomplete/incomplete/?page='.$i.$pagination_link) }}">{{ $i }}</a>
                    </li>
                @endfor
                <li class="{{ ($page >= $total_pages ? 'disabled' : '') }}">
                    <a class="page-link next" href="{{ url('cms/incomplete/incomplete/?page='.($page < $total_pages ? $page+1 :  $total_pages).$pagination_link) }}" aria-label="Next"> <span aria-hidden="true"> > </span> </a>
                </li>
                <li class="{{ ($page >= $total_pages ? 'disabled' : '') }}">
                    <a class="page-link" href="{{ url('cms/incomplete/incomplete/?page='.$total_pages.$pagination_link) }}" aria-label="Next"> <span aria-hidden="true"> >> </span>  </a>
                </li>
            </ul>
        </nav>
    @endif

@endsection