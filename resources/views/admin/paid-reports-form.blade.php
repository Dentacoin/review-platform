@extends('admin')


@section('content')

	<h1 class="page-header">
	    {{ empty($item) ? 'Add new paid report' : 'Edit paid report' }}
	</h1>

	<!-- end page-header -->

	<div class="row">
	    <!-- begin col-6 -->
	    <div class="col-md-12 ui-sortable">
	        {{ Form::open(array(
                'class' => 'form-horizontal', 
                'method' => 'post', 
                'files' => true, 
                'class' => 'form-horizontal reports-form'
            )) }}
	            {!! csrf_field() !!}

	            <div class="panel panel-inverse panel-with-tabs custom-tabs">
	                <div class="panel-heading p-0">
	                    <!-- begin nav-tabs -->
	                    <div class="tab-overflow overflow-right">
	                        <ul class="nav nav-tabs nav-tabs-inverse">
	                            @foreach($langs as $code => $lang_info)
	                                <li class="{{ $loop->first ? 'active' : '' }}">
	                                    <a href="javascript:;" lang="{{ $code }}" data-toggle="tab" aria-expanded="false">{{ $lang_info['name'] }}</a>
	                                </li>
	                            @endforeach
	                        </ul>
	                    </div>
	                </div>
	                <div class="tab-content row">
                        <div class="col-md-6">
                            <div class="form-group clearfix">
                                <label class="col-md-2 control-label">Main Title</label>
                                <div class="col-md-10" style="display: flex;"> 
                                    @foreach($langs as $code => $lang_info)
                                        <div class="tab-pane fade{{ $loop->first ? ' active in' : '' }} lang-{{ $code  }} " style="flex: 1;">
                                            {{ Form::text('main-title-'.$code, !empty($item) ? $item->{'main_title:'.$code} : '', array(
                                                'maxlength' => 1024, 
                                                'class' => 'form-control input-title', 
                                                'id' => 'edit-main-title'
                                            )) }}
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="form-group clearfix">
                                <label class="col-md-2 control-label">Title</label>
                                <div class="col-md-10" style="display: flex;"> 
                                    @foreach($langs as $code => $lang_info)
                                        <div class="tab-pane fade{{ $loop->first ? ' active in' : '' }} lang-{{ $code  }} " style="flex: 1;">
                                            {{ Form::text('title-'.$code, !empty($item) ? $item->{'title:'.$code} : '', array(
                                                'maxlength' => 1024, 
                                                'class' => 'form-control input-title', 
                                                'id' => 'edit-title'
                                            )) }}
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="form-group clearfix">
                                <label class="col-md-2 control-label">Slug</label>
                                <div class="col-md-8">
                                    @foreach($langs as $code => $lang_info)
                                        <div class="tab-pane fade{{ $loop->first ? ' active in' : '' }} lang-{{ $code  }} " style="flex: 1;">
                                            {{ Form::text('slug-'.$code, !empty($item) ? $item->{'slug:'.$code} : '', array(
                                                'maxlength' => 1024, 
                                                'class' => 'form-control input-title', 
                                                'id' => 'edit-slug'
                                            )) }}
                                        </div>
                                    @endforeach
                                </div>
                                <div class="col-md-2" style="padding: 0px;">
                                    <a class="btn btn-success" id="generate-slug" href="javascript:;">auto-generate</a>
                                </div>
                            </div>
                                
                            <div class="form-group">
                                <label class="col-md-2 control-label">Status</label>
                                <div class="col-md-10">
                                    {{ Form::select('status', $statuses, !empty($item) ? $item->status : null, array('class' => 'form-control')) }} 
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-2 control-label">Price</label>
                                <div class="col-md-4">
                                    {{ Form::number('price', !empty($item) ? $item->price : '', array('class' => 'form-control input-title')) }}
                                </div>
                            </div>

                            @foreach($langs as $code => $lang_info)
                                <div class="tab-pane checkists-pane fade{{ $loop->first ? ' active in' : '' }} lang-{{ $code  }}" lang="{{ $code }}">
                                    <div class="form-group answers-group-poll">
                                        <label class="col-md-2 control-label">Checklists</label>
                                        <div class="col-md-10 checkist-list">
                                            @if(!empty($item) && !empty($item->{'checklists:'.$code}) )
                                                @foreach(json_decode($item->{'checklists:'.$code}, true) as $key => $checklist)
                                                    <div class="flex input-group first-group" style="display: flex; align-items: center;">
                                                        <img class="check" src="{{ url('new-vox-img/green-check.png') }}" style="max-width: 20px; margin-right: 5px;"/>
                                                        <input type="text" name="checklists-{{ $code }}[]" value="{{ $checklist }}" maxlengt="1024" class="form-control paid-checklist" placeholder="Checklist"/>
                                                        <div class="input-group-btn">
                                                            <button class="btn btn-default btn-remove-checklist" type="button" style="height: 34px;">
                                                                <i class="glyphicon glyphicon-remove"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @else
                                                <div class="input-group first-group" style="display: flex; align-items: center;">
                                                    <img class="check" src="{{ url('new-vox-img/green-check.png') }}" style="max-width: 20px; margin-right: 5px;"/>
                                                    <input type="text" name="checklists-{{ $code }}[]" maxlengt="1024" class="form-control paid-checklist" placeholder="Checklist"/>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group answers-group-add-poll">
                                        <label class="col-md-2 control-label"></label>
                                        <div class="col-md-10">
                                            <a href="javascript:;" class="btn btn-success btn-block btn-checklist-answer" style="max-width: 161px;margin-left: 24px;">Add new checklist</a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            <div class="form-group">
                                <label class="col-md-2 control-label">Pages Number</label>
                                <div class="col-md-4">
                                    <input type="number" name="pages_count" class="form-control" value="{{ !empty($item) ? $item->pages_count : '' }}"/>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-2 control-label" style="padding-top: 0px; max-width: 200px;">Languages</label>
                                <div class="col-md-10">
                                    @foreach($languages as $kk => $ll)
                                        <label class="col-md-3" for="lang-{{ $kk }}">
                                            <input type="checkbox" name="languages[]" value="{{ $kk }}" id="lang-{{ $kk }}" {!! !empty($item) && in_array($kk, $item->languages) ? 'checked="checked"' : '' !!}/>
                                            {{ $ll }}
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-2 control-label" style="padding-top: 0px; max-width: 200px;">Formats</label>
                                <div class="col-md-10">
                                    @foreach($formats as $kk => $ff)
                                        <label class="col-md-3" for="format-{{ $kk }}">
                                            <input type="checkbox" name="download_format[]" value="{{ $kk }}" id="format-{{ $kk }}" {!! !empty($item) && in_array($kk, $item->download_format) ? 'checked="checked"' : '' !!}/>
                                            {{ $ff }}
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="featured" class="col-md-2 control-label" style="padding-top: 0px;">Single page image</label>
                                <div class="col-md-10">
                                    {{ Form::file('photo', [
                                        'id' => 'photo', 
                                        'accept' => 'image/jpg, image/jpeg, image/png'
                                    ]) }}
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="featured" class="col-md-2 control-label" style="padding-top: 0px;">All reports page image</label>
                                <div class="col-md-10">
                                    {{ Form::file('photo-all-reports', [
                                        'id' => 'photo-all-reports', 
                                        'accept' => 'image/jpg, image/jpeg, image/png'
                                    ]) }}
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="featured" class="col-md-2 control-label" style="padding-top: 0px;">Social image</label>
                                <div class="col-md-10">
                                    {{ Form::file('photo-social', [
                                        'id' => 'photo-social', 
                                        'accept' => 'image/jpg, image/jpeg, image/png'
                                    ]) }}
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="featured" class="col-md-2 control-label" style="padding-top: 0px;">&nbsp;</label>
                                @if(!empty($item) && $item->hasimage)
                                    <div class="form-group">
                                        <div class="col-md-3">
                                            Single page image<br/>
                                            <a target="_blank" href="{{ $item->getImageUrl() }}">
                                                <img src="{{ $item->getImageUrl() }}" style="width: 100%;" />
                                            </a>
                                        </div>
                                        <div class="col-md-3">
                                            All reports page image<br/>
                                            <a target="_blank" href="{{ $item->getImageUrl('all-reports') }}">
                                                <img src="{{ $item->getImageUrl('all-reports') }}" style="width: 100%;" />
                                            </a>
                                        </div>
                                        <div class="col-md-3">
                                            Social image<br/>
                                            <a target="_blank" href="{{ $item->getImageUrl('social') }}">
                                                <img src="{{ $item->getImageUrl('social') }}" style="width: 100%;" />
                                            </a>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div class="form-group">
                                <label class="col-md-2 control-label" style="padding-top: 0px; max-width: 200px;">Sample pages</label>
                                <div class="col-md-10">
                                    <div class="sample-list">
                                        @if(!empty($item) && $item->photos->isNotEmpty() )
                                            @foreach($item->photos as $photo)
                                                <div class="flex input-group first-group" style="display: flex; align-items: center;">
                                                    <a target="_blank" href="{{ $photo->getImageUrl() }}">
                                                        <img src="{{ $photo->getImageUrl('thumb') }}" style="max-width: 100px;margin-right: 10px;" />
                                                    </a>
                                                    <div class="input-group-btn">
                                                        <button class="btn btn-default btn-delete-sample-page" type="button" style="height: 34px;" url="{{ url('cms/vox/paid-reports/delete-gallery-photo/'.$photo->id) }}">
                                                            <i class="glyphicon glyphicon-remove"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="input-group first-group" style="display: flex; align-items: center;">
                                                {{ Form::file('gallery[]', ['accept' => 'image/jpg, image/jpeg, image/png']) }}
                                            </div>
                                        @endif
                                    </div>
                                    <div class="form-group answers-group-add-poll">
                                        <div class="col-md-10">
                                            <a href="javascript:;" class="btn btn-success btn-block btn-add-sample-page" style="max-width: 200px; margin-top: 20px;">Add new sample photo</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            
                            @foreach($langs as $code => $lang_info)
                                <div class="tab-pane contents-pane fade{{ $loop->first ? ' active in' : '' }} lang-{{ $code  }}" lang="{{ $code }}">
                                    <div class="form-group">
                                        <label class="col-md-2 control-label">Short description</label>
                                        <div class="col-md-10">
                                            {{ Form::textarea('short-description-'.$code, !empty($item) ? $item->{'short_description:'.$code} : '', array(
                                                'maxlength' => 2048, 
                                                'class' => 'form-control input-title', 
                                                'style' => 'max-height: 114px;', 
                                                'id' => 'paid-report-short-descr'
                                            )) }}
                                        </div>
                                    </div>
                                    <div class="form-group answers-group-poll">
                                        <div class="col-md-2"></div>
                                        <div class="col-md-10">
                                            <div class="row" style="display: flex; align-items: center;">
                                                <label class="control-label" style="width: 70%; text-align: center;">Text</label>
                                                <label class="control-label" style="margin: 0px 4px;">Main</label>
                                                <label class="control-label" style="margin: 0px 8px;">Page</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group answers-group-poll">
                                        <label class="col-md-2 control-label">Table of contents</label>
                                        <div class="col-md-10 contents-list contents-draggable">
                                            @if(!empty($item) && !empty(json_decode($item->{'table_contents:'.$code}, true)) )
                                                @foreach(json_decode($item->{'table_contents:'.$code}, true) as $key => $content)
                                                    <div class="flex input-group first-group">
                                                        <input type="text" name="table_contents-{{ $code }}[]" value="{{ $content['content'] }}" maxlengt="1024" class="form-control" style="width: 70%;"/>
                                                        <input type="checkbox" class="form-control is-main" name="main-{{$code}}[]" value="{{ $content['is_main'] }}" style="width: 30px; text-align: center; margin: 0px 5px;" {!! $content['is_main'] ? 'checked="checked"' : '' !!}/>
                                                        <input type="number" name="page-{{$code}}[]" value="{{ $content['page'] }}" style="width: 40px;padding: 2px;text-align: center;" class="form-control"/>
                                                        <div class="input-group-btn ">
                                                            <button class="btn btn-default btn-remove-contents" type="button" style="height: 34px;">
                                                                <i class="glyphicon glyphicon-remove"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @else
                                                <div class="input-group first-group" style="display: flex; align-items: center;">
                                                    <input type="text" name="table_contents-{{ $code }}[]" maxlengt="1024" class="form-control" style="width: 70%;"/>
                                                    <input type="checkbox" class="form-control is-main" name="main-{{$code}}[]" value="1" style="width: 30px; text-align: center; margin: 0px 5px;"/>
                                                    <input type="number" name="page-{{$code}}[]" style="width: 40px;padding: 2px;text-align: center;" class="form-control"/>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group answers-group-add-poll">
                                        <label class="col-md-2 control-label"></label>
                                        <div class="col-md-10">
                                            <a href="javascript:;" class="btn btn-success btn-block btn-add-table-contents" style="max-width: 161px;">Add new</a>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-2 control-label">Methodology</label>
                                        <div class="col-md-10">
                                            {{ Form::textarea('methodology-'.$code, !empty($item) ? $item->{'methodology:'.$code} : '', array(
                                                'class' => 'form-control input-title', 
                                                'style' => 'max-height: 114px;', 
                                                'id' => 'methodology'
                                            )) }}
                                        </div>
                                    </div>
        
                                    <div class="form-group">
                                        <label class="col-md-2 control-label">Summary</label>
                                        <div class="col-md-10">
                                            {{ Form::textarea('summary-'.$code, !empty($item) ? $item->{'summary:'.$code} : '', array(
                                                'class' => 'form-control input-title', 
                                                'style' => 'max-height: 114px;', 
                                                'id' => 'summary'
                                            )) }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="form-group" style="margin-top: 60px;">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-block btn-success">Save</button>
                            </div>
                        </div>
	                </div>
	            </div>

	        {{ Form::close() }}
	    </div>
	</div>

	<div style="display: none;">

        <div class="input-group first-group" id="input-group-template" style="display: flex; align-items: center;margin-top: 5px;">
            <img class="check" src="{{ url('new-vox-img/green-check.png') }}" style="max-width: 20px; margin-right: 5px;"/>
	        {{ Form::text('something', '', array('maxlength' => 1024, 'class' => 'form-control paid-checklist', 'placeholder' => 'Checklist')) }}
            
            <div class="input-group-btn">
                <button class="btn btn-default btn-remove-checklist" type="button" style="height: 34px;">
                    <i class="glyphicon glyphicon-remove"></i>
                </button>
            </div>
        </div>

        <div class="flex input-group first-group ui-sortable-handle" id="input-group-templatee" style="margin-top: 5px;">
            {{ Form::text('something', '', array('maxlength' => 1024, 'class' => 'form-control table-contents', 'style' => 'width: 70%;')) }}
            <input type="checkbox" class="form-control main-contents is-main" name="something" value="1" style="width: 30px; text-align: center; margin: 0px 5px;"/>
            <input type="number" class="form-control page-contents" name="something" style="width: 40px;padding: 2px;text-align: center;"/>
            <div class="input-group-btn ">
                <button class="btn btn-default btn-remove-contents" type="button" style="height: 34px;">
                    <i class="glyphicon glyphicon-remove"></i>
                </button>
            </div>
        </div>

        <div class="input-group first-group" id="input-group-sample-pages" style="display: flex; align-items: center;">
            {{ Form::file('gallery[]', ['accept' => 'image/jpg, image/jpeg, image/png']) }}
            <div class="input-group-btn">
                <button class="btn btn-default btn-remove-sample-page" type="button" style="height: 34px;">
                    <i class="glyphicon glyphicon-remove"></i>
                </button>
            </div>
        </div>
	</div>

    <style type="text/css">
        #cke_advanced_236,
        #cke_target_184,
        #cke_42,
        #cke_18,
        #cke_39,
        #cke_38,
        #cke_26,
        #cke_19,
        #cke_13,
        #cke_5,
        .cke_button__image {
            display: none !important;
        }

        body.cke_editable.cke_editable_themed.cke_contents_ltr,
        .cke_wysiwyg_frame.cke_reset {
            height: 100%;
        }

    </style>

@endsection