@extends('admin')

@section('content')

<h1 class="page-header">{{ trans('admin.page.'.$current_page.'.edit.page-title', ['name' => $item->name]) }}</h1>
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
                <h4 class="panel-title">{{ trans('admin.page.'.$current_page.'.edit') }}</h4>
            </div>
            <div class="panel-body">
                <form class="form-horizontal" id="admin-add" method="post" action="{{ url('cms/'.$current_page.'/edit/'.$item->id) }}">
                    @if($item->shortcodes())
                        <fieldset>
                            <legend>{{ trans('admin.page.'.$current_page.'.edit.shortcodes') }}</legend>
                            <div class="form-group">
                                @foreach($item->shortcodes() as $code)
                                    <label class="col-md-2 control-label">{{ $code }}</label>
                                @endforeach
                            </div>
                        </fieldset>
                    @endif
                	{!! csrf_field() !!}
                    @foreach($langs as $langkey => $lang)
                        <fieldset>
                            <legend>{{ $lang['name'] }}</legend>

                            <div class="form-group">
                                <label class="col-md-2 control-label">{{ trans('admin.page.'.$current_page.'.edit.subject') }}</label>
                                <div class="col-md-10">
                                    {{ Form::text('subject_'.$langkey, !empty($item) ? stripslashes($item->{'subject:'.$langkey}) : null, array('maxlength' => 256, 'class' => 'form-control')) }}
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-2 control-label">{{ trans('admin.page.'.$current_page.'.edit.title') }}</label>
                                <div class="col-md-10">
                                    {{ Form::text('title_'.$langkey, !empty($item) ? stripslashes($item->{'title:'.$langkey}) : null, array('maxlength' => 256, 'class' => 'form-control')) }}
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-2 control-label">{{ trans('admin.page.'.$current_page.'.edit.subtitle') }}</label>
                                <div class="col-md-10">
                                    {{ Form::text('subtitle_'.$langkey, !empty($item) ? stripslashes($item->{'subtitle:'.$langkey}) : null, array('maxlength' => 256, 'class' => 'form-control')) }}
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="col-md-2 control-label">{{ trans('admin.page.'.$current_page.'.edit.content') }}</label>
                                <div class="col-md-10">
                                    {{ Form::textarea('content_'.$langkey, !empty($item) ? stripslashes($item->{'content:'.$langkey}) : null, array('class' => 'form-control')) }}
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-2 control-label">Category</label>
                                <div class="col-md-10">
                                    {{ Form::text('category_'.$langkey, !empty($item) ? stripslashes($item->{'category:'.$langkey}) : null, array('maxlength' => 512, 'class' => 'form-control')) }}
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-2 control-label">SendGrid Template ID</label>
                                <div class="col-md-10">
                                    {{ Form::text('sendgrid_template_id_'.$langkey, !empty($item) ? stripslashes($item->{'sendgrid_template_id:'.$langkey}) : null, array('maxlength' => 256, 'class' => 'form-control')) }}
                                </div>
                            </div>
                        </fieldset>
                    @endforeach

                    <fieldset style="border-top: 1px solid black;padding-top: 30px;">

                        <div class="form-group">
                            <label class="col-md-2 control-label">Validate Email</label>
                            <div class="col-md-10">
                                <input type="checkbox" name="validate-email" class="form-control" value="1" id="validate-email" style="vertical-align: sub;width: 30px;" {!! !empty($item) && !empty($item->validate_email) ? 'checked="checked"' : '' !!} />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-2 control-label">Subscribe Category</label>
                            <div class="col-md-10">
                                {{ Form::select('subscribe_category', ['' => '-'] + config('email-categories'), !empty($item) ? $item->subscribe_category : '', array('class' => 'form-control')) }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-2 control-label">Note</label>
                            <div class="col-md-10">
                                {{ Form::textarea('note', !empty($item) ? $item->note : null, array('maxlength' => 255, 'class' => 'form-control')) }}
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-11 control-label"></label>
                            <div class="col-md-1">
                                <button type="submit" class="btn btn-block btn-sm btn-success form-control">{{ trans('admin.page.'.$current_page.'.edit.save') }}</button>
                            </div>
                        </div>
                    </fieldset>
                </form>
            </div>
        </div>
        <!-- end panel -->
    </div>
</div>


@endsection