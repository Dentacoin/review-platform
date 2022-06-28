@extends('admin')

@section('content')

    <h1 class="page-header">{{ trans('admin.page.'.$current_page.'.edit.title') }}</h1>
    <!-- end page-header -->

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <div class="panel-heading-btn">
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                    </div>
                    <h4 class="panel-title">{{ trans('admin.page.'.$current_page.'.edit.title') }}</h4>
                </div>
                <div class="panel-body">
                    {!! Form::open([
                        'url' => !isset($my_profile) ? url('cms/admins/admins/edit/'.$item->id) : url('cms/admins/update-profile'), 
                        'method' => 'post', 
                        'class' => 'form-horizontal'
                    ]) !!}

                        {!! csrf_field() !!}

                        @if(!isset($my_profile))
                            <div class="form-group">
                                <label class="col-md-2 control-label">ID</label>
                                <div class="col-md-4">
                                    {{ Form::text('id', $item->id, array('class' => 'form-control', 'disabled' => 'disabled') ) }}
                                </div>
                                <label class="col-md-2 control-label">{{ trans('admin.page.'.$current_page.'.comments') }}</label>
                                <div class="col-md-4">
                                    {{ Form::text('comments', $item->comments, array('class' => 'form-control')) }}
                                </div>
                            </div>
                        @endif

                        <div class="form-group">
                            <label class="col-md-2 control-label">{{ trans('admin.page.'.$current_page.'.username') }}</label>
                            <div class="col-md-4">
                                {{ Form::text('username', $item->username, array('class' => 'form-control')) }}
                            </div>
                            <label class="col-md-2 control-label">{{ trans('admin.page.'.$current_page.'.password') }}</label>
                            <div class="col-md-4">
                                {{ Form::text('password', '', array('class' => 'form-control')) }}
                            </div>
                        </div>

                        @if(!isset($my_profile))
                            <div class="form-group">
                                <label class="col-md-2 control-label">Name</label>
                                <div class="col-md-4">
                                    {{ Form::text('name', $item->name, array('class' => 'form-control')) }}
                                </div>
                                <label class="col-md-2 control-label">{{ trans('admin.page.'.$current_page.'.role') }}</label>
                                <div class="col-md-4">
                                    {{ Form::select('role', $roles, $item->role, array('class' => 'form-control')) }}
                                </div>
                            </div>
                        @endif

                        <div class="form-group">
                            <label class="col-md-2 control-label">{{ trans('admin.page.'.$current_page.'.email') }}</label>
                            <div class="col-md-4">
                                {{ Form::text('email', $item->email, array('class' => 'form-control')) }}
                            </div>
                            <label class="col-md-2 control-label">Website account ID</label>
                            <div class="col-md-4">
                                {{ Form::text('user_id', $item->user_id, array('class' => 'form-control')) }}
                            </div>
                        </div>                    

                        @if(!isset($my_profile))
                            <div class="form-group">
                                <label class="col-md-2 control-label">{{ trans('admin.page.'.$current_page.'.lang_from') }}</label>
                                <div class="col-md-4">
                                    {{ Form::select('lang_from', $langslist, $item->lang_from, array('class' => 'form-control')) }}
                                </div>
                                <label class="col-md-2 control-label">{{ trans('admin.page.'.$current_page.'.lang_to') }}</label>
                                <div class="col-md-4">
                                    {{ Form::select('lang_to', $langslist, $item->lang_to, array('class' => 'form-control')) }}
                                </div>
                            </div>
                        @endif

                        @if(!isset($my_profile))
                            <div class="form-group">
                                <label class="col-md-2 control-label">{{ trans('admin.page.'.$current_page.'.text_domain') }}</label>
                                <div class="col-md-4">
                                    @foreach($domainlist as $k => $v)
                                        <label for="dl-{{ $k }}">
                                            <input id="dl-{{ $k }}" type="checkbox" name="text_domain[]" value="{{ $k }}" {!! in_array($k, explode(',', $item->text_domain)) ? 'checked="checked"' : '' !!} />
                                            {{ $v }}
                                        </label>
                                        <br/>
                                    @endforeach
                                </div>
                                @if($item->role != 'super_admin')
                                    <label class="col-md-2 control-label">Email template platform access</label>
                                    <div class="col-md-4">
                                        @foreach(config('email-templates-platform') as $k => $v)
                                            <label for="email-{{ $k }}">
                                                <input id="email-{{ $k }}" type="checkbox" name="email_template_type[]" value="{{ $k }}" {!! in_array($k, $item->email_template_type) ? 'checked="checked"' : '' !!} />
                                                {{ $v }}
                                            </label>
                                            <br/>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endif

                        <div class="form-group">
                            <div class="col-md-8"></div>
                            <div class="col-md-4">
                                <button type="submit" name="update" class="btn btn-block btn-sm btn-success form-control">{{ trans('admin.page.'.$current_page.'.save') }}</button>
                            </div>
                        </div>

                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>

@endsection