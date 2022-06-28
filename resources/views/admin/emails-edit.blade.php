@extends('admin')

@section('content')

    <h1 class="page-header">{{ trans('admin.page.'.$current_page.'.edit.page-title', ['name' => $item->name]) }}</h1>
    <!-- end page-header -->

    <div class="row">
        <!-- begin col-6 -->
        <div class="col-md-12 ui-sortable">
            <form class="form-horizontal" id="admin-add" method="post" action="{{ url('cms/'.$current_page.'/edit/'.$item->id) }}">
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
                    <div class="tab-content">
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
                        @foreach($langs as $code => $lang_info)
                            <div class="tab-pane fade{{ $loop->first ? ' active in' : '' }} lang-{{ $code  }}">
                                <fieldset>        
                                    <div class="form-group">
                                        <label class="col-md-2 control-label">{{ trans('admin.page.'.$current_page.'.edit.subject') }}</label>
                                        <div class="col-md-10">
                                            {{ Form::text('subject_'.$code, !empty($item) ? stripslashes($item->{'subject:'.$code}) : null, array(
                                                'maxlength' => 256, 
                                                'class' => 'form-control'
                                            )) }}
                                        </div>
                                    </div>
        
                                    <div class="form-group">
                                        <label class="col-md-2 control-label">{{ trans('admin.page.'.$current_page.'.edit.title') }}</label>
                                        <div class="col-md-10">
                                            {{ Form::text('title_'.$code, !empty($item) ? stripslashes($item->{'title:'.$code}) : null, array(
                                                'maxlength' => 256, 
                                                'class' => 'form-control'
                                            )) }}
                                        </div>
                                    </div>
        
                                    <div class="form-group">
                                        <label class="col-md-2 control-label">{{ trans('admin.page.'.$current_page.'.edit.subtitle') }}</label>
                                        <div class="col-md-10">
                                            {{ Form::text('subtitle_'.$code, !empty($item) ? stripslashes($item->{'subtitle:'.$code}) : null, array(
                                                'maxlength' => 256, 
                                                'class' => 'form-control'
                                            )) }}
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="col-md-2 control-label">{{ trans('admin.page.'.$current_page.'.edit.content') }}</label>
                                        <div class="col-md-10">
                                            {{ Form::textarea('content_'.$code, !empty($item) ? stripslashes($item->{'content:'.$code}) : null, array(
                                                'class' => 'form-control'
                                            )) }}
                                        </div>
                                    </div>
        
                                    <div class="form-group">
                                        <label class="col-md-2 control-label">Category</label>
                                        <div class="col-md-10">
                                            {{ Form::text('category_'.$code, !empty($item) ? stripslashes($item->{'category:'.$code}) : null, array(
                                                'maxlength' => 512, 
                                                'class' => 'form-control'
                                            )) }}
                                        </div>
                                    </div>
        
                                    <div class="form-group">
                                        <label class="col-md-2 control-label">SendGrid Template ID</label>
                                        <div class="col-md-10">
                                            {{ Form::text('sendgrid_template_id_'.$code, !empty($item) ? stripslashes($item->{'sendgrid_template_id:'.$code}) : null, array(
                                                'maxlength' => 256, 
                                                'class' => 'form-control'
                                            )) }}
                                        </div>
                                    </div>
                                </fieldset>
                            </div>
                        @endforeach
                        <fieldset>

                            <div class="form-group">
                                <label class="col-md-2 control-label">Validate Email</label>
                                <div class="col-md-10">
                                    <input type="checkbox" name="validate-email" class="form-control" value="1" id="validate-email" style="vertical-align: sub;width: 30px;" {!! !empty($item) && !empty($item->validate_email) ? 'checked="checked"' : '' !!} />
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-2 control-label">Subscribe Category</label>
                                <div class="col-md-10">
                                    {{ Form::select('subscribe_category', ['' => '-'] + config('email-categories'), !empty($item) ? $item->subscribe_category : '', array(
                                        'class' => 'form-control'
                                    )) }}
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-2 control-label">Note</label>
                                <div class="col-md-10">
                                    {{ Form::textarea('note', !empty($item) ? $item->note : null, array(
                                        'maxlength' => 255, 
                                        'class' => 'form-control'
                                    )) }}
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-11 control-label"></label>
                                <div class="col-md-1">
                                    <button type="submit" class="btn btn-block btn-sm btn-success form-control">{{ trans('admin.page.'.$current_page.'.edit.save') }}</button>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                </div>
            </form>
        </div>
    </div>

@endsection