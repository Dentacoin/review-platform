@extends('admin')


@section('content')

<h1 class="page-header">{{ empty($item) ? trans('admin.page.'.$current_page.'.new.category.title') : trans('admin.page.'.$current_page.'.edit.category.title') }}</h1>
<!-- end page-header -->

<div class="row">
    <!-- begin col-6 -->
    <div class="col-md-12 ui-sortable">
        {{ Form::open(array('id' => 'category-add', 'class' => 'form-horizontal', 'method' => 'post', 'files' => true)) }}

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
                <div class="tab-content clearfix">
                    <div class="col-md-6">
                        @foreach($langs as $code => $lang_info)
                            <div class="lang-tab tab-pane fade{{ $loop->first ? ' active in' : '' }}" data-lang="{{ $code }}" id="nav-tab-{{ $code }}">
                                <div class="form-group">
                                    <label class="col-md-2 control-label">{{ trans('admin.page.'.$current_page.'.category.name') }}</label>
                                    <div class="col-md-10">
                                        {{ Form::text('category-name-'.$code, !empty($item) ? $item->{'name:'.$code} : null, array('maxlength' => 128, 'class' => 'form-control')) }}
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        <div class="form-group">
                            <label class="col-md-2 control-label">Color</label>
                            <div class="col-md-10">
                                {{ Form::text('color', !empty($item) ? $item->color : null, array('class' => 'form-control')) }}
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="featured" class="col-md-2 control-label" style="padding-top: 0px; max-width: 200px;">Icon</label>
                            <div class="col-md-10">
                                {{ Form::file('icon', ['id' => 'icon', 'accept' => 'image/gif, image/jpg, image/jpeg, image/png']) }}<br/>
                                * Square PNG Image, Min Width 50px, up to 2 MB<br/>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="featured" class="col-md-2 control-label" style="padding-top: 0px; max-width: 200px;">&nbsp;</label>
                            @if(!empty($item) && $item->hasimage)
                                <div class="col-md-10">
                                    <a target="_blank" href="{{ $item->getImageUrl() }}">
                                        <img src="{{ $item->getImageUrl(true) }}" style="background: #2f7de1; width: 50px;" />
                                    </a>
                                    <br/>
                                    <a href="{{ url('cms/vox/categories/edit/'.$item->id.'/delpic') }}">Delete photo</a>
                                </div>
                            @endif
                        </div>

                        <div class="form-group">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-block btn-success">{{ empty($item) ? trans('admin.page.'.$current_page.'.new.category.submit') : trans('admin.page.'.$current_page.'.edit.category.submit') }}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>


@endsection