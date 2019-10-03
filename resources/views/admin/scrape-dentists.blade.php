@extends('admin')

@section('content')

<h1 class="page-header">{{ trans('admin.page.'.$current_page.'.title') }}</h1>
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
                <h4 class="panel-title">Search dentists</h4>
            </div>
            <div class="panel-body">
                <form class="form-horizontal" id="scrape-dentists" method="post" action="{{ url('cms/'.$current_page) }}">
                    {!! csrf_field() !!}
                    <div class="form-group">
                        <label class="col-md-1 control-label">City</label>
                        <div class="col-md-5">
                            {{ Form::text( 'address', '', array('class' => 'form-control', 'autocomplete' => 'off' )) }}
                        </div>
                    </div>                    
                    <div class="form-group">
                        <div class="col-md-4"></div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-sm btn-success btn-block scrape-submit">Submit</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <!-- end panel -->
    </div>
</div>

@if(!empty($scrapes))
    <div class="row">
        <!-- begin col-6 -->
        <div class="col-md-12">
            <!-- begin panel -->
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <div class="panel-heading-btn">
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                    </div>
                    <h4 class="panel-title">Dentist scrapes</h4>
                </div>
                <div class="panel-body">
                    <div class="row">
                        @foreach($scrapes as $scrape)
                            <div class="col-md-12" style="margin-bottom: 20px;">
                                <div class="col-md-10">
                                    Scraping dentists from "{{ $scrape->name }}".. {{ $scrape->requests }} from {{ $scrape->requests_total }} requests...
                                    @if($scrape->completed)
                                        COMPLETED
                                    @endif
                                    <br>
                                </div>
                                <div class="col-md-2">
                                    <a href="{{ url('cms/scrape-google-dentists/'.$scrape->id) }}" class="btn btn-sm btn-success btn-block">Download</a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <!-- end panel -->
        </div>
    </div>
@endif

@endsection