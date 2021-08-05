@extends('admin')


@section('content')

<h1 class="page-header">Edit question</h1>
<!-- end page-header -->

<div class="row">
    <!-- begin col-6 -->
    <div class="col-md-12 ui-sortable">
        {{ Form::open(array('id' => 'question-edit', 'class' => 'form-horizontal', 'method' => 'post')) }}

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
                        <div class="lang-tab tab-pane fade{{ $loop->first ? ' active in' : '' }}" data-lang="{{ $code }}" id="nav-tab-{{ $code }}">
                            <div class="form-group">
                                <label class="col-md-2 control-label">Question</label>
                                <div class="col-md-10">
                                    {{ Form::text('question-'.$code, $item->{'question:'.$code}, array('maxlength' => 512, 'class' => 'form-control', 'id' => 'edit-question')) }}
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-2 control-label">Slug</label>
                                <div class="col-md-8">
                                    {{ Form::text('slug-'.$code, $item->{'slug:'.$code}, array('maxlength' => 128, 'class' => 'form-control', 'id' => 'edit-slug')) }}
                                </div>
                                <div class="col-md-2">
                                	<a class="btn btn-success" id="generate-slug" href="javascript:;">Generate</a>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-2 control-label">Content</label>
                                <div class="col-md-10">
                                    <textarea name="answer-{{ $code }}" class="form-control textarea-ckeditor" contenteditable="true" id="answer">{{ $item->{'content:'.$code} }}</textarea>
                                </div>
                            </div>
                            <div class="form-group clearfix">
                                <label class="col-md-2 control-label"></label>
                                <p class="col-md-10">
                                    * If you want embed video from youtube video:<br/>
                                    1. copy {{ '<div class="video-wrapper"><iframe src="https://www.youtube.com/embed/tgbNymZ7vqY" allowfullscreen="" frameborder="0" height="720" width="1280"></iframe></div>'}}<br/>
                                    2. click on "Source" and paste it where you want.<br/>
                                    3. change the video id (in this case is "tgbNymZ7vqY").<br/>
                                </p>
                            </div>
                        </div>
                    @endforeach

                    <div class="form-group clearfix">
                        <label class="col-md-2 control-label">Category</label>
                        <div class="col-md-10">
                            {{ Form::select('category_id', $categories->pluck('name', 'id')->toArray(), $item->category_id, array('class' => 'form-control', 'id' => 'question-category')) }}
                        </div>
                    </div>
                    <div class="form-group clearfix">
                        <label class="col-md-2 control-label" for="main">Main</label>
                        <div class="col-md-10">
                            <input type="checkbox" name="is_main" class="form-control" value="1" id="main" {{ $item->is_main ? 'checked="checked"' : '' }} style="vertical-align: sub;width: 30px;" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="col-md-10 control-label"></label>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-block btn-sm btn-success">Edit</button>
                </div>
            </div>

        </form>

    </div>
</div>

<style type="text/css">
    
    #cke_advanced_236,
    #cke_target_184,
    #cke_42,
	#cke_18,
	#cke_39,
	#cke_38,
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