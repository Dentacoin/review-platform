@extends('admin')

@section('content')

<h1 class="page-header">{{ trans('admin.page.'.$current_page.'.title') }}</h1>
<!-- end page-header -->


<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <div class="panel-heading-btn">
                    <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                </div>
                <h4 class="panel-title"> {{ trans('admin.page.'.$current_page.'.title-filter') }} </h4>
            </div>
            <div class="panel-body">
                <form class="col-md-12" method="get" action="{{ url('cms/'.$current_page) }}" >
                    <div class="col-md-1">
                        <input type="text" class="form-control" name="search-rate_from" value="{{ $search_rate_from }}" placeholder="{{ trans('admin.page.'.$current_page.'.title-filter-rate_from') }}">
                    </div>
                    <div class="col-md-1">
                        <input type="text" class="form-control" name="search-rate_to" value="{{ $search_rate_to }}" placeholder="{{ trans('admin.page.'.$current_page.'.title-filter-rate_to') }}">
                    </div>
                    <div class="col-md-2">
                        {{ Form::select( 'search-country_id' , [ '' => trans('admin.page.'.$current_page.'.title-filter-country') ] + \App\Models\Country::get()->pluck('name', 'id')->toArray() , $search_country_id , array('class' => 'form-control country-select') ) }}
                    </div>
                    <div class="col-md-2">
                        {{ Form::select( 'search-city_id' , $search_country_id ? [ '' => trans('admin.page.'.$current_page.'.title-filter-city') ] + \App\Models\City::where('country_id', $search_country_id)->get()->pluck('name', 'id')->toArray() : [] , $search_city_id , array('class' => 'form-control city-select') ) }}
                    </div>
                    <div class="col-md-1">
                        <input type="text" class="form-control" name="search-address" value="{{ $search_address }}" placeholder="{{ trans('admin.page.'.$current_page.'.title-filter-address') }}">
                    </div>
                    <div class="col-md-1">
                        <input type="text" class="form-control" name="search-tx" value="{{ $search_tx }}" placeholder="{{ trans('admin.page.'.$current_page.'.title-filter-tx') }}">
                    </div>
                    <input type="submit" class="btn btn-sm btn-primary col-md-2" name="search" value="{{ trans('admin.page.'.$current_page.'.title-filter-submit') }}">
                    <input type="submit" class="btn btn-secondary btn-sm col-md-2" name="search-deleted" value="{{ trans('admin.page.'.$current_page.'.title-filter-deleted') }}">
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
                <h4 class="panel-title">{{ trans('admin.page.'.$current_page.'.title') }}</h4>
            </div>
            <div class="panel-body">
				@include('admin.parts.table', [
					'table_id' => 'users',
					'table_fields' => [
                        'created_at'        => array('format' => 'datetime'),
                        'user'              => array('template' => 'admin.parts.table-reviews-user'),
                        'dentist'           => array('template' => 'admin.parts.table-reviews-dentist'),
                        'rating'            => array(),
                        'upvotes'            => array(),
                        'verified'              => array('format' => 'bool'),
                        'link'              => array('template' => 'admin.parts.table-reviews-link'),
                        'tx'              => array('template' => 'admin.parts.table-reviews-tx'),
                        'status'              => array(),
						'delete'			=> array('format' => 'delete'),
					],
                    'table_data' => $reviews,
					'table_pagination' => false,
                    'pagination_link' => array()
				])
            </div>
        </div>
    </div>
</div>
@endsection