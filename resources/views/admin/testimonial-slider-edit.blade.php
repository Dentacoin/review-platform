@extends('admin')

@section('content')

    <h1 class="page-header"> 
        Edit Testimonial
    </h1>

    <div class="row">
        <div class="col-md-12">
            {!! Form::open(array(
                'url' => url('cms/trp/testimonials/edit/'.$item->id), 
                'method' => 'post', 
                'class' => 'form-horizontal',
                'files' => true
            )) !!}
                {!! csrf_field() !!}

                <div class="panel panel-inverse panel-with-tabs" data-sortable-id="ui-unlimited-tabs-1">
                    <div class="panel-heading p-0">
                        <div class="panel-heading-btn m-r-10 m-t-10">
                            <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-success" data-click="panel-expand" data-original-title="" title=""><i class="fa fa-expand"></i></a>
                        </div>
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
                                    <label class="col-md-2 control-label" style="max-width: 200px;">Name</label>
                                    <div class="col-md-6">
                                        {{ Form::text('name-'.$code, $item->{'name:'.$code}, array('maxlength' => 128, 'class' => 'form-control')) }}
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-2 control-label" style="max-width: 200px;">Position</label>
                                    <div class="col-md-6">
                                        {{ Form::text('job-'.$code, $item->{'job:'.$code}, array('maxlength' => 128, 'class' => 'form-control')) }}
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-2 control-label" style="max-width: 200px;">Quote</label>
                                    <div class="col-md-6">
                                        {{ Form::textarea('description-'.$code, $item->{'description:'.$code}, array('maxlength' => 2048, 'class' => 'form-control')) }}
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-2 control-label" style="max-width: 200px;">Alt image text</label>
                                    <div class="col-md-6">
                                        {{ Form::text('alt_image_text-'.$code, $item->{'alt_image_text:'.$code}, array('maxlength' => 128, 'class' => 'form-control')) }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        <div class="form-group">
                            <label class="col-md-2 control-label" style="max-width: 200px;">Image</label>
                            <div class="col-md-2">
                                <label for="add-avatar" class="image-label" style="background-image: url('{{ $item->getImageUrl()}}');border-radius: 50%;">
                                    <div class="loader">
                                        <i class="fas fa-circle-notch fa-spin"></i>
                                    </div>
                                    <input type="file" name="image" id="add-avatar" class="no-cropper" upload-url="{{ url('cms/trp/testimonials/edit/'.$item->id.'/addavatar') }}">
                                </label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-2 control-label" style="max-width: 200px;"></label>
                            <div class="col-md-5">
                                Required image size: 160x160px; <br/>
                                Format: png, jpg, jpeg
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-6 col-md-offset-2">
                                <button type="submit" name="update" class="btn btn-block btn-sm btn-success form-control"> {{ trans('admin.common.save') }} </button>
                            </div>
                            <div class="col-md-6"></div>
                        </div>
                    </div>
                </div>
            {!! Form::close() !!}
        </div>
    </div>

@endsection