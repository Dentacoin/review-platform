@extends('admin')


@section('content')

<h1 class="page-header">{{ empty($item) ? trans('admin.page.'.$current_page.'.new.title') : trans('admin.page.'.$current_page.'.edit.title') }}</h1>
<!-- end page-header -->

<div class="row">
    <!-- begin col-6 -->
    <div class="col-md-12 ui-sortable">
        {{ Form::open(array('id' => 'page-add', 'class' => 'form-horizontal', 'method' => 'post', 'files' => true)) }}


            <!-- begin panel -->
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <div class="panel-heading-btn">
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                    </div>
                    <h4 class="panel-title">{{ empty($item) ? trans('admin.page.'.$current_page.'.new.title') : trans('admin.page.'.$current_page.'.edit.title') }}</h4>
                </div>
                <div class="panel-body">
                    {!! csrf_field() !!}
                    <div class="form-group">
                        <label class="col-md-3 control-label">{{ trans('admin.page.'.$current_page.'.header') }}</label>
                        <div class="col-md-9">
                            {{ Form::text('header', !empty($item) ? $item->header : null, array('class' => 'form-control')) }}
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-3 control-label">{{ trans('admin.page.'.$current_page.'.footer') }}</label>
                        <div class="col-md-9">
                            {{ Form::text('footer', !empty($item) ? $item->footer : null, array('class' => 'form-control')) }}
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-3 control-label">{{ trans('admin.page.'.$current_page.'.image') }}</label>
                        <div class="col-md-9">
                            {{ Form::file('image', ['id' => 'image-input']) }}<br/>
                            @if(!empty($item) && $item->hasimage)
                                <a target="_blank" href="{{ $item->getImageUrl() }}">
                                    <img src="{{ $item->getImageUrl(true) }}" />
                                </a>
                                <a class="btn btn-sm btn-success" href="{{ url('cms/'.$current_page.'/edit/'.$item->id.'/removepic') }}" >
                                    {{ trans('admin.page.'.$current_page.'.remove-pic') }}
                                </a>
                            @endif
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
                            @foreach(config('langs') as $code => $lang_info)
                                <li class="{{ $loop->first ? 'active' : '' }}"><a href="#nav-tab-{{ $code }}" data-toggle="tab" aria-expanded="false">{{ $lang_info['name'] }}</a></li>
                            @endforeach

                            <li class="next-button"><a href="javascript:;" data-click="next-tab" class="text-success"><i class="fa fa-arrow-right"></i></a></li>
                        </ul>
                    </div>
                </div>
                <div class="tab-content">
                    
                    @foreach(config('langs') as $code => $lang_info)
                        <div class="tab-pane fade{{ $loop->first ? ' active in' : '' }}" id="nav-tab-{{ $code }}">
                            <div class="form-group">
                                <label class="col-md-3 control-label">{{ trans('admin.page.'.$current_page.'.slug') }}</label>
                                <div class="col-md-9">
                                    {{ Form::text('slug-'.$code, !empty($item) ? $item->{'slug:'.$code} : null, array('maxlength' => 256, 'class' => 'form-control')) }}
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label">{{ trans('admin.page.'.$current_page.'.page-title') }}</label>
                                <div class="col-md-9">
                                    {{ Form::text('title-'.$code, !empty($item) ? $item->{'title:'.$code} : null, array('maxlength' => 256, 'class' => 'form-control')) }}
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label">{{ trans('admin.page.'.$current_page.'.seo-title') }}</label>
                                <div class="col-md-9">
                                    {{ Form::text('seo-title-'.$code, !empty($item) ? $item->{'seo_title:'.$code} : null, array('class' => 'form-control')) }}
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label">{{ trans('admin.page.'.$current_page.'.description') }}</label>
                                <div class="col-md-9">
                                    {{ Form::textarea('description-'.$code, !empty($item) ? $item->{'description:'.$code} : null, array('maxlength' => 512, 'class' => 'form-control')) }}
                                </div>
                            </div>


                            <div class="content-blocks">
                                @if(!empty($item) && !empty( $item->{'content:'.$code} ) && is_array( json_decode($item->{'content:'.$code}, true )) )
                                    @foreach( json_decode($item->{'content:'.$code}, true) as $block )
                                        @if($block['type']=='html')
                                            <div class="panel panel-inverse template template-add-html">
                                                <div class="panel-heading">
                                                    <div class="panel-heading-btn">
                                                    </div>
                                                    <h4 class="panel-title">{{ trans('admin.page.'.$current_page.'.add-html-title') }}</h4>
                                                </div>
                                                <div class="panel-body">
                                                    <div class="form-group">
                                                        <label class="col-md-2 control-label">{{ trans('admin.page.'.$current_page.'.block-padding') }}</label>
                                                        <div class="col-md-4">
                                                            @include('admin.parts.block-paddings', ['padding' => !empty($block['padding']) ? $block['padding'] : null ]  )
                                                        </div>
                                                        <label class="col-md-2 control-label">{{ trans('admin.page.'.$current_page.'.background-color') }}</label>
                                                        <div class="col-md-4">
                                                            {{ Form::text('background', !empty($block['background']) ? $block['background'] : null, array('class' => 'form-control background colorpicker', 'data-colorpicker-guid' => rand(1, 99999999) )) }}
                                                            @include('admin.parts.theme-colors')
                                                        </div>
                                                    </div>

                                                    <div class="form-group ck-holder" style="
                                                        {!! !empty($block['background']) ? 'background-color: '.$block['background'].'; ' : ''  !!}
                                                    " >
                                                        <div class="col-md-12">
                                                            <div  class="page-content-div" id="html-block-{{ $code }}-{{ $loop->index }}" contenteditable="true">
                                                                {!! $block['content'] !!}
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group block-btns">
                                                        <div class="col-md-12">
                                                            <a class="btn btn-sm btn-success move-up"><i class="fa fa-arrow-circle-up"></i></a>
                                                            <a class="btn btn-sm btn-success move-down"><i class="fa fa-arrow-circle-down"></i></a>
                                                            <a class="btn btn-sm btn-default remove-block"><i class="fa fa-remove"></i></a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                        @elseif($block['type']=='html-2')
                                            <div class="panel panel-inverse template template-add-html-2">
                                                <div class="panel-heading">
                                                    <div class="panel-heading-btn">
                                                    </div>
                                                    <h4 class="panel-title">{{ trans('admin.page.'.$current_page.'.add-html-title') }}</h4>
                                                </div>
                                                <div class="panel-body">
                                                    <div class="form-group">
                                                        <label class="col-md-2 control-label">{{ trans('admin.page.'.$current_page.'.block-padding') }}</label>
                                                        <div class="col-md-4">
                                                            @include('admin.parts.block-paddings', ['padding' => !empty($block['padding']) ? $block['padding'] : null ]  )
                                                        </div>
                                                        <label class="col-md-2 control-label">{{ trans('admin.page.'.$current_page.'.background-color') }}</label>
                                                        <div class="col-md-4">
                                                            {{ Form::text('background', !empty($block['background']) ? $block['background'] : null, array('class' => 'form-control background background-main colorpicker', 'data-colorpicker-guid' => rand(1, 99999999))) }}
                                                            @include('admin.parts.theme-colors')
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="col-md-2 control-label">{{ trans('admin.page.'.$current_page.'.column-color') }}</label>
                                                        <div class="col-md-4">
                                                            {{ Form::text('background', $block['columns'][0]['background'], array('class' => 'form-control background-1 colorpicker', 'data-col-id' => 1)) }}
                                                            @include('admin.parts.theme-colors')
                                                        </div>
                                                        <label class="col-md-2 control-label">{{ trans('admin.page.'.$current_page.'.column-color') }}</label>
                                                        <div class="col-md-4">
                                                            {{ Form::text('background', $block['columns'][1]['background'], array('class' => 'form-control background-2 colorpicker', 'data-col-id' => 2)) }}
                                                            @include('admin.parts.theme-colors')
                                                        </div>
                                                    </div>
                                                    <div class="form-group ck-holder" style="
                                                        {!! !empty($block['background']) ? 'background-color: '.$block['background'].'; ' : ''  !!}
                                                    " >
                                                        <div class="col-md-6 ck-col ck-col-1">
                                                            <div class="page-content-div page-content-div-1" style="
                                                        {!! !empty($block['columns'][0]['background']) ? 'background-color: '.$block['columns'][0]['background'].'; ' : ''  !!}
                                                        " id="html-block-{{ $code }}-{{ $loop->index }}-1" contenteditable="true">
                                                                {!! $block['columns'][0]['content'] !!}
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6 ck-col ck-col-2">
                                                            <div class="page-content-div page-content-div-2" style="
                                                        {!! !empty($block['columns'][1]['background']) ? 'background-color: '.$block['columns'][1]['background'].'; ' : ''  !!}
                                                        " id="html-block-{{ $code }}-{{ $loop->index }}-2" contenteditable="true">
                                                                {!! $block['columns'][1]['content'] !!}
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group block-btns">
                                                        <div class="col-md-12">
                                                            <a class="btn btn-sm btn-success move-up"><i class="fa fa-arrow-circle-up"></i></a>
                                                            <a class="btn btn-sm btn-success move-down"><i class="fa fa-arrow-circle-down"></i></a>
                                                            <a class="btn btn-sm btn-default remove-block"><i class="fa fa-remove"></i></a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @elseif($block['type']=='html-3')
                                            <div class="panel panel-inverse template template-add-html-3">
                                                <div class="panel-heading">
                                                    <div class="panel-heading-btn">
                                                    </div>
                                                    <h4 class="panel-title">{{ trans('admin.page.'.$current_page.'.add-html-title') }}</h4>
                                                </div>
                                                <div class="panel-body">
                                                    <div class="form-group">

                                                        <label class="col-md-1 control-label">{{ trans('admin.page.'.$current_page.'.block-padding') }}</label>
                                                        <div class="col-md-5">
                                                            @include('admin.parts.block-paddings', ['padding' => !empty($block['padding']) ? $block['padding'] : null ]  )
                                                        </div>
                                                        <label class="col-md-1 control-label">{{ trans('admin.page.'.$current_page.'.background-color') }}</label>
                                                        <div class="col-md-5">
                                                            {{ Form::text('background', !empty($block['background']) ? $block['background'] : null, array('class' => 'form-control background background-main colorpicker')) }}
                                                            @include('admin.parts.theme-colors')
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="col-md-1 control-label">{{ trans('admin.page.'.$current_page.'.column-color') }}</label>
                                                        <div class="col-md-3">
                                                            {{ Form::text('background', $block['columns'][0]['background'], array('class' => 'form-control background-1 colorpicker', 'data-col-id' => 1)) }}
                                                            @include('admin.parts.theme-colors')
                                                        </div>
                                                        <label class="col-md-1 control-label">{{ trans('admin.page.'.$current_page.'.column-color') }}</label>
                                                        <div class="col-md-3">
                                                            {{ Form::text('background', $block['columns'][1]['background'], array('class' => 'form-control background-2 colorpicker', 'data-col-id' => 2)) }}
                                                            @include('admin.parts.theme-colors')
                                                        </div>
                                                        <label class="col-md-1 control-label">{{ trans('admin.page.'.$current_page.'.column-color') }}</label>
                                                        <div class="col-md-3">
                                                            {{ Form::text('background', $block['columns'][2]['background'], array('class' => 'form-control background-3 colorpicker', 'data-col-id' => 3)) }}
                                                            @include('admin.parts.theme-colors')
                                                        </div>
                                                    </div>
                                                    <div class="form-group ck-holder" style="
                                                        {!! !empty($block['background']) ? 'background-color: '.$block['background'].'; ' : ''  !!}
                                                    " >
                                                        <div class="col-md-4 ck-col ck-col-1">
                                                            <div class="page-content-div page-content-div-1" style="
                                                        {!! !empty($block['columns'][0]['background']) ? 'background-color: '.$block['columns'][0]['background'].'; ' : ''  !!}
                                                        " id="html-block-{{ $code }}-{{ $loop->index }}-1" contenteditable="true">
                                                                {!! $block['columns'][0]['content'] !!}
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4 ck-col ck-col-2">
                                                            <div class="page-content-div page-content-div-2" style="
                                                        {!! !empty($block['columns'][1]['background']) ? 'background-color: '.$block['columns'][1]['background'].'; ' : ''  !!}
                                                        " id="html-block-{{ $code }}-{{ $loop->index }}-2" contenteditable="true">
                                                                {!! $block['columns'][1]['content'] !!}
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4 ck-col ck-col-3">
                                                            <div class="page-content-div page-content-div-3" style="
                                                        {!! !empty($block['columns'][2]['background']) ? 'background-color: '.$block['columns'][2]['background'].'; ' : ''  !!}
                                                        " id="html-block-{{ $code }}-{{ $loop->index }}-3" contenteditable="true">
                                                                {!! $block['columns'][2]['content'] !!}
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group block-btns">
                                                        <div class="col-md-12">
                                                            <a class="btn btn-sm btn-success move-up"><i class="fa fa-arrow-circle-up"></i></a>
                                                            <a class="btn btn-sm btn-success move-down"><i class="fa fa-arrow-circle-down"></i></a>
                                                            <a class="btn btn-sm btn-default remove-block"><i class="fa fa-remove"></i></a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @elseif($block['type']=='html-4')
                                            <div class="panel panel-inverse template template-add-html-4">
                                                <div class="panel-heading">
                                                    <div class="panel-heading-btn">
                                                    </div>
                                                    <h4 class="panel-title">{{ trans('admin.page.'.$current_page.'.add-html-title') }}</h4>
                                                </div>
                                                <div class="panel-body">
                                                    <div class="form-group">
                                                        <label class="col-md-1 control-label">{{ trans('admin.page.'.$current_page.'.block-padding') }}</label>
                                                        <div class="col-md-5">
                                                            @include('admin.parts.block-paddings', ['padding' => !empty($block['padding']) ? $block['padding'] : null ]  )
                                                        </div>
                                                        <label class="col-md-1 control-label">{{ trans('admin.page.'.$current_page.'.background-color') }}</label>
                                                        <div class="col-md-5">
                                                            {{ Form::text('background', !empty($block['background']) ? $block['background'] : null, array('class' => 'form-control background background-main colorpicker')) }}
                                                            @include('admin.parts.theme-colors')
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="col-md-3 control-label">{{ trans('admin.page.'.$current_page.'.column-color') }}</label>
                                                        <label class="col-md-3 control-label">{{ trans('admin.page.'.$current_page.'.column-color') }}</label>
                                                        <label class="col-md-3 control-label">{{ trans('admin.page.'.$current_page.'.column-color') }}</label>
                                                        <label class="col-md-3 control-label">{{ trans('admin.page.'.$current_page.'.column-color') }}</label>
                                                    </div>
                                                    <div class="form-group">
                                                        <div class="col-md-3">
                                                            {{ Form::text('background', $block['columns'][0]['background'], array('class' => 'form-control background-1 colorpicker', 'data-col-id' => 1)) }}
                                                            @include('admin.parts.theme-colors')
                                                        </div>
                                                        <div class="col-md-3">
                                                            {{ Form::text('background', $block['columns'][1]['background'], array('class' => 'form-control background-2 colorpicker', 'data-col-id' => 2)) }}
                                                            @include('admin.parts.theme-colors')
                                                        </div>
                                                        <div class="col-md-3">
                                                            {{ Form::text('background', $block['columns'][2]['background'], array('class' => 'form-control background-3 colorpicker', 'data-col-id' => 3)) }}
                                                            @include('admin.parts.theme-colors')
                                                        </div>
                                                        <div class="col-md-3">
                                                            {{ Form::text('background', $block['columns'][3]['background'], array('class' => 'form-control background-4 colorpicker', 'data-col-id' => 4)) }}
                                                            @include('admin.parts.theme-colors')
                                                        </div>
                                                    </div>
                                                    <div class="form-group ck-holder" style="
                                                        {!! !empty($block['background']) ? 'background-color: '.$block['background'].'; ' : ''  !!}
                                                    " >
                                                        <div class="col-md-3 ck-col ck-col-1">
                                                            <div class="page-content-div page-content-div-1" style="
                                                        {!! !empty($block['columns'][0]['background']) ? 'background-color: '.$block['columns'][0]['background'].'; ' : ''  !!}
                                                        " id="html-block-{{ $code }}-{{ $loop->index }}-1" contenteditable="true">
                                                                {!! $block['columns'][0]['content'] !!}
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3 ck-col ck-col-2">
                                                            <div class="page-content-div page-content-div-2" style="
                                                        {!! !empty($block['columns'][1]['background']) ? 'background-color: '.$block['columns'][1]['background'].'; ' : ''  !!}
                                                        " id="html-block-{{ $code }}-{{ $loop->index }}-2" contenteditable="true">
                                                                {!! $block['columns'][1]['content'] !!}
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3 ck-col ck-col-3">
                                                            <div class="page-content-div page-content-div-3" style="
                                                        {!! !empty($block['columns'][2]['background']) ? 'background-color: '.$block['columns'][2]['background'].'; ' : ''  !!}
                                                        " id="html-block-{{ $code }}-{{ $loop->index }}-3" contenteditable="true">
                                                                {!! $block['columns'][2]['content'] !!}
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3 ck-col ck-col-3">
                                                            <div class="page-content-div page-content-div-4" style="
                                                        {!! !empty($block['columns'][3]['background']) ? 'background-color: '.$block['columns'][3]['background'].'; ' : ''  !!}
                                                        " id="html-block-{{ $code }}-{{ $loop->index }}-4" contenteditable="true">
                                                                {!! $block['columns'][3]['content'] !!}
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group block-btns">
                                                        <div class="col-md-12">
                                                            <a class="btn btn-sm btn-success move-up"><i class="fa fa-arrow-circle-up"></i></a>
                                                            <a class="btn btn-sm btn-success move-down"><i class="fa fa-arrow-circle-down"></i></a>
                                                            <a class="btn btn-sm btn-default remove-block"><i class="fa fa-remove"></i></a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @elseif($block['type']=='map')
                                            <div class="panel panel-inverse template template-add-map">
                                                <div class="panel-heading">
                                                    <div class="panel-heading-btn">
                                                    </div>
                                                    <h4 class="panel-title">{{ trans('admin.page.'.$current_page.'.add-map-title') }}</h4>
                                                </div>
                                                <div class="panel-body">
                                                    <div class="form-group">
                                                        <label class="col-md-2 control-label">{{ trans('admin.page.'.$current_page.'.map-address') }}</label>
                                                        <div class="col-md-4">
                                                            {{ Form::text('address', !empty($block['address']) ? $block['address'] : '', array('class' => 'form-control address')) }}
                                                            
                                                        </div>
                                                    </div>
                                                    <div class="form-group block-btns">
                                                        <div class="col-md-12">
                                                            <a class="btn btn-sm btn-success move-up"><i class="fa fa-arrow-circle-up"></i></a>
                                                            <a class="btn btn-sm btn-success move-down"><i class="fa fa-arrow-circle-down"></i></a>
                                                            <a class="btn btn-sm btn-default remove-block"><i class="fa fa-remove"></i></a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif


                                    @endforeach
                                @endif               
                            </div>

                            <div class="form-group">
                                <div class="col-md-12">
                                    <a class="btn btn-sm btn-success add-content" data-type="add-html">
                                        <i class="fa fa-plus-circle"></i> 
                                        {{ trans('admin.page.'.$current_page.'.add-html') }}
                                    </a>
                                    <a class="btn btn-sm btn-success add-content" data-type="add-html-2">
                                        <i class="fa fa-plus-circle"></i> 
                                        {{ trans('admin.page.'.$current_page.'.add-html-2') }}
                                    </a>
                                    <a class="btn btn-sm btn-success add-content" data-type="add-html-3">
                                        <i class="fa fa-plus-circle"></i> 
                                        {{ trans('admin.page.'.$current_page.'.add-html-3') }}
                                    </a>
                                    <a class="btn btn-sm btn-success add-content" data-type="add-html-4">
                                        <i class="fa fa-plus-circle"></i> 
                                        {{ trans('admin.page.'.$current_page.'.add-html-4') }}
                                    </a>
                                    <a class="btn btn-sm btn-success add-content" data-type="add-map">
                                        <i class="fa fa-plus-circle"></i> 
                                        {{ trans('admin.page.'.$current_page.'.add-map') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="form-group">
                <label class="col-md-10 control-label"></label>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-block btn-sm btn-success">{{ empty($item) ? trans('admin.page.'.$current_page.'.new.submit') : trans('admin.page.'.$current_page.'.edit.submit') }}</button>
                </div>
            </div>

        </form>

    </div>
</div>



<div class="templates">
    <div class="panel panel-inverse template template-add-map" style="display: none;">
        <div class="panel-heading">
            <div class="panel-heading-btn">
            </div>
            <h4 class="panel-title">{{ trans('admin.page.'.$current_page.'.add-map-title') }}</h4>
        </div>
        <div class="panel-body">
            <div class="form-group">
                <label class="col-md-2 control-label">{{ trans('admin.page.'.$current_page.'.map-address') }}</label>
                <div class="col-md-4">
                    {{ Form::text('address', null, array('class' => 'form-control address')) }}
                </div>
            </div>
            <div class="form-group block-btns">
                <div class="col-md-12">
                    <a class="btn btn-sm btn-success move-up"><i class="fa fa-arrow-circle-up"></i></a>
                    <a class="btn btn-sm btn-success move-down"><i class="fa fa-arrow-circle-down"></i></a>
                    <a class="btn btn-sm btn-default remove-block"><i class="fa fa-remove"></i></a>
                </div>
            </div>
        </div>
    </div>
    <div class="panel panel-inverse template template-add-html" style="display: none;">
        <div class="panel-heading">
            <div class="panel-heading-btn">
            </div>
            <h4 class="panel-title">{{ trans('admin.page.'.$current_page.'.add-html-title') }}</h4>
        </div>
        <div class="panel-body">
            <div class="form-group">
                <label class="col-md-2 control-label">{{ trans('admin.page.'.$current_page.'.block-padding') }}</label>
                <div class="col-md-4">
                    @include('admin.parts.block-paddings')
                </div>
                <label class="col-md-2 control-label">{{ trans('admin.page.'.$current_page.'.background-color') }}</label>
                <div class="col-md-4">
                    {{ Form::text('background', null, array('class' => 'form-control background colorpicker-template')) }}
                    @include('admin.parts.theme-colors')
                </div>
            </div>
            <div class="form-group ck-holder">
                <div class="col-md-12">
                    <div class="page-content-div" contenteditable="true">
                        {{ trans('admin.page.'.$current_page.'.add-html-example') }}
                    </div>
                </div>
            </div>
            <div class="form-group block-btns">
                <div class="col-md-12">
                    <a class="btn btn-sm btn-success move-up"><i class="fa fa-arrow-circle-up"></i></a>
                    <a class="btn btn-sm btn-success move-down"><i class="fa fa-arrow-circle-down"></i></a>
                    <a class="btn btn-sm btn-default remove-block"><i class="fa fa-remove"></i></a>
                </div>
            </div>
        </div>
    </div>
    <div class="panel panel-inverse template template-add-html-2" style="display: none;">
        <div class="panel-heading">
            <div class="panel-heading-btn">
            </div>
            <h4 class="panel-title">{{ trans('admin.page.'.$current_page.'.add-html-title') }}</h4>
        </div>
        <div class="panel-body">
            <div class="form-group">
                <label class="col-md-2 control-label">{{ trans('admin.page.'.$current_page.'.block-padding') }}</label>
                <div class="col-md-4">
                    @include('admin.parts.block-paddings')
                </div>
                <label class="col-md-2 control-label">{{ trans('admin.page.'.$current_page.'.background-color') }}</label>
                <div class="col-md-4">
                    {{ Form::text('color', null, array('class' => 'form-control background background-main colorpicker-template')) }}
                    @include('admin.parts.theme-colors')
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-2 control-label">{{ trans('admin.page.'.$current_page.'.column-color') }}</label>
                <div class="col-md-4">
                    {{ Form::text('background', null, array('class' => 'form-control background-1 colorpicker-template', 'data-col-id' => 1)) }}
                    @include('admin.parts.theme-colors')
                </div>
                <label class="col-md-2 control-label">{{ trans('admin.page.'.$current_page.'.column-color') }}</label>
                <div class="col-md-4">
                    {{ Form::text('background', null, array('class' => 'form-control background-2 colorpicker-template', 'data-col-id' => 2)) }}
                    @include('admin.parts.theme-colors')
                </div>
            </div>
            <div class="form-group ck-holder">
                <div class="col-md-6 ck-col ck-col-1">
                    <div class="page-content-div page-content-div-1" contenteditable="true">
                        {{ trans('admin.page.'.$current_page.'.add-html-example') }}
                    </div>
                </div>
                <div class="col-md-6 ck-col ck-col-2">
                    <div class="page-content-div page-content-div-2" contenteditable="true">
                        {{ trans('admin.page.'.$current_page.'.add-html-example') }}
                    </div>
                </div>
            </div>
            <div class="form-group block-btns">
                <div class="col-md-12">
                    <a class="btn btn-sm btn-success move-up"><i class="fa fa-arrow-circle-up"></i></a>
                    <a class="btn btn-sm btn-success move-down"><i class="fa fa-arrow-circle-down"></i></a>
                    <a class="btn btn-sm btn-default remove-block"><i class="fa fa-remove"></i></a>
                </div>
            </div>
        </div>
    </div>
    <div class="panel panel-inverse template template-add-html-3" style="display: none;">
        <div class="panel-heading">
            <div class="panel-heading-btn">
            </div>
            <h4 class="panel-title">{{ trans('admin.page.'.$current_page.'.add-html-title') }}</h4>
        </div>
        <div class="panel-body">
            <div class="form-group">
                <label class="col-md-1 control-label">{{ trans('admin.page.'.$current_page.'.block-padding') }}</label>
                <div class="col-md-5">
                    @include('admin.parts.block-paddings')
                </div>
                <label class="col-md-1 control-label">{{ trans('admin.page.'.$current_page.'.background-color') }}</label>
                <div class="col-md-5">
                    {{ Form::text('color', null, array('class' => 'form-control background background-main colorpicker-template')) }}
                    @include('admin.parts.theme-colors')
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-1 control-label">{{ trans('admin.page.'.$current_page.'.column-color') }}</label>
                <div class="col-md-3">
                    {{ Form::text('background', null, array('class' => 'form-control background-1 colorpicker-template', 'data-col-id' => 1)) }}
                    @include('admin.parts.theme-colors')
                </div>
                <label class="col-md-1 control-label">{{ trans('admin.page.'.$current_page.'.column-color') }}</label>
                <div class="col-md-3">
                    {{ Form::text('background', null, array('class' => 'form-control background-2 colorpicker-template', 'data-col-id' => 2)) }}
                    @include('admin.parts.theme-colors')
                </div>
                <label class="col-md-1 control-label">{{ trans('admin.page.'.$current_page.'.column-color') }}</label>
                <div class="col-md-3">
                    {{ Form::text('background', null, array('class' => 'form-control background-3 colorpicker-template', 'data-col-id' => 3)) }}
                    @include('admin.parts.theme-colors')
                </div>
            </div>
            <div class="form-group ck-holder">
                <div class="col-md-4 ck-col ck-col-1">
                    <div class="page-content-div page-content-div-1" contenteditable="true">
                        {{ trans('admin.page.'.$current_page.'.add-html-example') }}
                    </div>
                </div>
                <div class="col-md-4 ck-col ck-col-2">
                    <div class="page-content-div page-content-div-2" contenteditable="true">
                        {{ trans('admin.page.'.$current_page.'.add-html-example') }}
                    </div>
                </div>
                <div class="col-md-4 ck-col ck-col-3">
                    <div class="page-content-div page-content-div-3" contenteditable="true">
                        {{ trans('admin.page.'.$current_page.'.add-html-example') }}
                    </div>
                </div>
            </div>
            <div class="form-group block-btns">
                <div class="col-md-12">
                    <a class="btn btn-sm btn-success move-up"><i class="fa fa-arrow-circle-up"></i></a>
                    <a class="btn btn-sm btn-success move-down"><i class="fa fa-arrow-circle-down"></i></a>
                    <a class="btn btn-sm btn-default remove-block"><i class="fa fa-remove"></i></a>
                </div>
            </div>
        </div>
    </div>
    <div class="panel panel-inverse template template-add-html-4" style="display: none;">
        <div class="panel-heading">
            <div class="panel-heading-btn">
            </div>
            <h4 class="panel-title">{{ trans('admin.page.'.$current_page.'.add-html-title') }}</h4>
        </div>
        <div class="panel-body">
            <div class="form-group">
                <label class="col-md-1 control-label">{{ trans('admin.page.'.$current_page.'.block-padding') }}</label>
                <div class="col-md-5">
                    @include('admin.parts.block-paddings')
                </div>
                <label class="col-md-1 control-label">{{ trans('admin.page.'.$current_page.'.background-color') }}</label>
                <div class="col-md-5">
                    {{ Form::text('color', null, array('class' => 'form-control background background-main colorpicker-template')) }}
                    @include('admin.parts.theme-colors')
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-3 control-label">{{ trans('admin.page.'.$current_page.'.column-color') }}</label>
                <label class="col-md-3 control-label">{{ trans('admin.page.'.$current_page.'.column-color') }}</label>
                <label class="col-md-3 control-label">{{ trans('admin.page.'.$current_page.'.column-color') }}</label>
                <label class="col-md-3 control-label">{{ trans('admin.page.'.$current_page.'.column-color') }}</label>
            </div>
            <div class="form-group">
                <div class="col-md-3">
                    {{ Form::text('background', null, array('class' => 'form-control background-1 colorpicker-template', 'data-col-id' => 1 )) }}
                    @include('admin.parts.theme-colors')
                </div>
                <div class="col-md-3">
                    {{ Form::text('background', null, array('class' => 'form-control background-2 colorpicker-template', 'data-col-id' => 2)) }}
                    @include('admin.parts.theme-colors')
                </div>
                <div class="col-md-3">
                    {{ Form::text('background', null, array('class' => 'form-control background-3 colorpicker-template', 'data-col-id' => 3)) }}
                    @include('admin.parts.theme-colors')
                </div>
                <div class="col-md-3">
                    {{ Form::text('background', null, array('class' => 'form-control background-4 colorpicker-template', 'data-col-id' => 4)) }}
                    @include('admin.parts.theme-colors')
                </div>
            </div>
            <div class="form-group ck-holder">
                <div class="col-md-3 ck-col ck-col-1">
                    <div class="page-content-div page-content-div-1" contenteditable="true">
                        {{ trans('admin.page.'.$current_page.'.add-html-example') }}
                    </div>
                </div>
                <div class="col-md-3 ck-col ck-col-2">
                    <div class="page-content-div page-content-div-2" contenteditable="true">
                        {{ trans('admin.page.'.$current_page.'.add-html-example') }}
                    </div>
                </div>
                <div class="col-md-3 ck-col ck-col-3">
                    <div class="page-content-div page-content-div-3" contenteditable="true">
                        {{ trans('admin.page.'.$current_page.'.add-html-example') }}
                    </div>
                </div>
                <div class="col-md-3 ck-col ck-col-4">
                    <div class="page-content-div page-content-div-4" contenteditable="true">
                        {{ trans('admin.page.'.$current_page.'.add-html-example') }}
                    </div>
                </div>
            </div>
            <div class="form-group block-btns">
                <div class="col-md-12">
                    <a class="btn btn-sm btn-success move-up"><i class="fa fa-arrow-circle-up"></i></a>
                    <a class="btn btn-sm btn-success move-down"><i class="fa fa-arrow-circle-down"></i></a>
                    <a class="btn btn-sm btn-default remove-block"><i class="fa fa-remove"></i></a>
                </div>
            </div>
        </div>
    </div>
</div>



@endsection