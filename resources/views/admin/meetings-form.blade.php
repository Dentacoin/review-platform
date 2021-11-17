@extends('admin')


@section('content')

	<h1 class="page-header">
	    {{ config('meetings')[$item->slug] }}
	</h1>

	<!-- end page-header -->

	<div class="row">
	    <!-- begin col-6 -->
	    <div class="col-md-12 ui-sortable">
	        {{ Form::open(array('class' => 'form-horizontal', 'method' => 'post', 'files' => true, 'class' => 'form-horizontal reports-form')) }}
	            {!! csrf_field() !!}

	            <div class="panel panel-inverse">
	                <div class="tab-content row">
                        <div class="col-md-6">
                            
                            <div class="form-group clearfix">
                                <label class="col-md-2 control-label">Seo/Social Title</label>
                                <div class="col-md-10" style="display: flex;"> 
                                    {{ Form::text('seo_title', $item->seo_title, array('maxlength' => 128, 'class' => 'form-control input-title')) }}
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-2 control-label">Seo/Social description</label>
                                <div class="col-md-10">
                                    {{ Form::textarea('seo_description', $item->seo_description, array('maxlength' => 1024, 'class' => 'form-control input-title', 'style' => 'max-height: 114px;')) }}
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="featured" class="col-md-2 control-label" style="padding-top: 0px;">Add Social image</label>
                                <div class="col-md-10">
                                    {{ Form::file('photo', ['id' => 'photo', 'accept' => 'image/jpg, image/jpeg, image/png']) }}
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="featured" class="col-md-2 control-label" style="padding-top: 0px;">&nbsp;</label>
                                @if($item->hasimage)
                                    <div class="form-group">
                                        <div class="col-md-4">
                                            Social image<br/>
                                            <a target="_blank" href="{{ $item->getImageUrl() }}">
                                                <img src="{{ $item->getImageUrl() }}" style="width: 100%;" />
                                            </a>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div class="form-group">
                                <label class="col-md-2 control-label">Duration</label>
                                <div class="col-md-10">
                                    <input type="text" name="duration" class="form-control" value="{{ $item->duration }}" maxlength="64"/>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-2 control-label">Video title</label>
                                <div class="col-md-10">
                                    <input type="text" name="video_title" class="form-control" value="{{ $item->video_title }}" maxlength="128"/>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-2 control-label">YouTube video ID</label>
                                <div class="col-md-10">
                                    <input type="text" name="video_id" class="form-control" value="{{ $item->video_id }}" maxlength="20"/>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-2 control-label">HubSpot Iframe ID</label>
                                <div class="col-md-10">
                                    <input type="text" name="iframe_id" class="form-control" value="{{ $item->iframe_id }}" maxlength="64"/>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-2 control-label">Website URL</label>
                                <div class="col-md-10">
                                    <input type="text" name="website_url" class="form-control" value="{{ $item->website_url }}" maxlength="128"/>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="featured" class="col-md-2 control-label" style="padding-top: 0px;">Website image</label>
                                <div class="col-md-10">
                                    {{ Form::file('website-photo', ['id' => 'website-photo', 'accept' => 'image/jpg, image/jpeg, image/png']) }}
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="featured" class="col-md-2 control-label" style="padding-top: 0px;">&nbsp;</label>
                                @if($item->has_website_image)
                                    <div class="form-group">
                                        <div class="col-md-4">
                                            Website image<br/>
                                            <a target="_blank" href="{{ $item->getWebsiteImageUrl() }}">
                                                <img src="{{ $item->getWebsiteImageUrl() }}" style="width: 100%;" />
                                            </a>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">

                            <div class="form-group">
                                <label class="col-md-2 control-label">Checklists title</label>
                                <div class="col-md-10">
                                    <input type="text" name="checklist_title" class="form-control" value="{{ $item->checklist_title }}" maxlength="64"/>
                                </div>
                            </div>

                            <div class="form-group checklists-group">
                                <label class="col-md-2 control-label">Checklists</label>
                                <div class="col-md-10 checkist-list">
                                    @if(!empty($item->checklists) )
                                        @foreach(json_decode($item->checklists, true) as $key => $checklist)
                                            <div class="flex input-group first-group" style="display: flex; align-items: center;">
                                                <img class="check" src="{{ url('new-vox-img/green-check.png') }}" style="max-width: 20px; margin-right: 5px;"/>
                                                <input type="text" name="checklists[]" value="{{ $checklist }}" maxlengt="1024" class="form-control meeting-checklist" placeholder="Checklist"/>
                                                <div class="input-group-btn" style="width: auto;">
                                                    <button class="btn btn-default btn-remove-checklist" type="button" style="height: 34px;">
                                                        <i class="glyphicon glyphicon-remove"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="input-group first-group" style="display: flex; align-items: center;">
                                            <img class="check" src="{{ url('new-vox-img/green-check.png') }}" style="max-width: 20px; margin-right: 5px;"/>
                                            <input type="text" name="checklists[]" maxlengt="1024" class="form-control meeting-checklist" placeholder="Checklist"/>
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

                            <div class="form-group after-checklists-group">
                                <label class="col-md-2 control-label">After <br/>Checklist <br/>Info</label>
                                <div class="col-md-10 checkist-list">
                                    @if(!empty($item->after_checklist_info) )
                                        @foreach(json_decode($item->after_checklist_info, true) as $key => $after_checklist_info)
                                            <div class="flex input-group first-group" style="display: flex; align-items: center;">
                                                <input type="text" name="after_checklist_info[]" value="{{ $after_checklist_info }}" maxlengt="1024" class="form-control meeting-after-checklist" placeholder="After Checklist Info"/>
                                                <div class="input-group-btn" style="width: auto;">
                                                    <button class="btn btn-default btn-remove-after-checklist" type="button" style="height: 34px;">
                                                        <i class="glyphicon glyphicon-remove"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="input-group first-group" style="display: flex; align-items: center;">
                                            <input type="text" name="after_checklist_info[]" maxlengt="1024" class="form-control meeting-after-checklist" placeholder="After Checklist Info"/>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group answers-group-add-poll">
                                <label class="col-md-2 control-label"></label>
                                <div class="col-md-10">
                                    <a href="javascript:;" class="btn btn-success btn-block btn-after-checklist-answer" style="max-width: 211px;">Add new after Checklists info</a>
                                </div>
                            </div>

                        </div>
                        <div class="col-md-12">
                            <div class="form-group" style="margin-top: 60px;">
                                <button type="submit" class="btn btn-block btn-success">Save</button>
                            </div>
                        </div>
	                </div>
	            </div>

	        {{ Form::close() }}
	    </div>
	</div>

	<div style="display: none;">
        <div class="input-group first-group" id="input-group-template" style="display: flex; align-items: center;">
            <img class="check" src="{{ url('new-vox-img/green-check.png') }}" style="max-width: 20px; margin-right: 5px;"/>
	        {{ Form::text('something', '', array('maxlength' => 1024, 'class' => 'form-control meeting-checklist', 'placeholder' => 'Checklist')) }}
            
            <div class="input-group-btn" style="width: auto;">
                <button class="btn btn-default btn-remove-checklist" type="button" style="height: 34px;">
                    <i class="glyphicon glyphicon-remove"></i>
                </button>
            </div>
        </div>

        <div class="input-group first-group" id="input-group-template-afterlist" style="display: flex; align-items: center;">
	        {{ Form::text('something', '', array('maxlength' => 1024, 'class' => 'form-control meeting-after-checklist', 'placeholder' => 'After Checklists Info')) }}
            
            <div class="input-group-btn" style="width: auto;">
                <button class="btn btn-default btn-remove-checklist" type="button" style="height: 34px;">
                    <i class="glyphicon glyphicon-remove"></i>
                </button>
            </div>
        </div>
	</div>

@endsection