@extends('admin')


@section('content')

<h1 class="page-header">
    {{ empty($item) ? trans('admin.page.'.$current_page.'.'.$current_subpage.'.new.title') : trans('admin.page.'.$current_page.'.'.$current_subpage.'.edit.title') }}
</h1>
<!-- end page-header -->

<div class="row">
    <!-- begin col-6 -->
    <div class="col-md-12 ui-sortable">
        {{ Form::open(array('id' => 'page-add', 'class' => 'form-horizontal', 'method' => 'post')) }}


            <!-- begin panel -->
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <div class="panel-heading-btn">
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                    </div>
                    <h4 class="panel-title">{{ empty($item) ? trans('admin.page.'.$current_page.'.'.$current_subpage.'.new.title') : trans('admin.page.'.$current_page.'.'.$current_subpage.'.edit.title') }}</h4>
                </div>
                <div class="panel-body">
                    {!! csrf_field() !!}
                    <div class="form-group">
                        <label class="col-md-3 control-label">{{ trans('admin.page.'.$current_page.'.'.$current_subpage.'.title-field') }}</label>
                        <div class="col-md-9">
                            {{ Form::text('title', !empty($item) ? $item->title : '', array('class' => 'form-control')) }}
                        </div>
                    </div>

                </div>
            </div>
            <!-- end panel -->

            <div class="panel panel-inverse panel-with-tabs" data-sortable-id="ui-unlimited-tabs-1">
                <div class="panel-heading p-0">
                    <div class="panel-heading-btn m-r-10 m-t-10">
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-success" data-click="panel-expand" data-original-title="" title=""><i class="fa fa-expand"></i></a>
                    </div>
                    <!-- begin nav-tabs -->
                    <div class="tab-overflow overflow-right">
                        <ul class="nav nav-tabs nav-tabs-inverse">
                            <li class="prev-button"><a href="javascript:;" data-click="prev-tab" class="text-success"><i class="fa fa-arrow-left"></i></a></li>
                            @foreach($langs as $code => $lang_info)
                                <li class="{{ $loop->first ? 'active' : '' }}"><a href="#nav-tab-{{ $code }}" data-toggle="tab" aria-expanded="false">{{ $lang_info['name'] }}</a></li>
                            @endforeach

                            <li class="next-button"><a href="javascript:;" data-click="next-tab" class="text-success"><i class="fa fa-arrow-right"></i></a></li>
                        </ul>
                    </div>
                </div>
                <div class="tab-content">
                    @foreach($langs as $code => $lang_info)
                        <div class="tab-pane fade{{ $loop->first ? ' active in' : '' }}" id="nav-tab-{{ $code }}">
                            <div class="form-group">
                                <label class="col-md-2 control-label">{{ trans('admin.page.'.$current_page.'.lang-answers') }}</label>
                                <div class="col-md-10">
                                    {{ Form::text('answers-'.$code, !empty($item) ? $item->translateOrNew($code)->answers : null, array('maxlength' => 256, 'class' => 'form-control', 'placeholder' => trans('admin.page.'.$current_page.'.'.$current_subpage.'.ansers-placeholder') )) }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="form-group">
                <label class="col-md-10 control-label"></label>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-sm btn-success btn-block">{{ empty($item) ? trans('admin.page.'.$current_page.'.new.submit') : trans('admin.page.'.$current_page.'.edit.submit') }}</button>
                </div>
            </div>


    </div>
</div>

@endsection