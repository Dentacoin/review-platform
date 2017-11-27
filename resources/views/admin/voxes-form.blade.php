@extends('admin')


@section('content')

<h1 class="page-header">
    {{ empty($item) ? trans('admin.page.'.$current_page.'.new.title') : trans('admin.page.'.$current_page.'.edit.title') }}
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
                    <h4 class="panel-title">{{ empty($item) ? trans('admin.page.'.$current_page.'.new.title') : trans('admin.page.'.$current_page.'.edit.title') }}</h4>
                </div>
                <div class="panel-body">
                    {!! csrf_field() !!}
                    <div class="form-group">
                        <label class="col-md-3 control-label">{{ trans('admin.page.'.$current_page.'.reward') }}</label>
                        <div class="col-md-9">
                            {{ Form::text('reward', !empty($item) ? $item->reward : '', array('class' => 'form-control')) }}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">{{ trans('admin.page.'.$current_page.'.duration') }}</label>
                        <div class="col-md-9">
                            {{ Form::text('duration', !empty($item) ? $item->duration : '', array('class' => 'form-control')) }}
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-3 control-label">{{ trans('admin.page.'.$current_page.'.type') }}</label>
                        <div class="col-md-9">
                            {{ Form::select('type', $types, !empty($item) ? $item->type : null, array('class' => 'form-control')) }}
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
                                <label class="col-md-3 control-label">{{ trans('admin.page.'.$current_page.'.lang-title') }}</label>
                                <div class="col-md-9">
                                    {{ Form::text('title-'.$code, !empty($item) ? $item->{'title:'.$code} : null, array('maxlength' => 256, 'class' => 'form-control input-title')) }}
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label">{{ trans('admin.page.'.$current_page.'.lang-description') }}</label>
                                <div class="col-md-9">
                                    {{ Form::textarea('description-'.$code, !empty($item) ? $item->{'description:'.$code} : null, array('maxlength' => 2048, 'class' => 'form-control input-description')) }}
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



            @if(!empty($item) && $item->questions->isNotEmpty())
                <h3>Questions</h3>
                <div class="panel panel-inverse">
                    <div class="panel-heading">
                        <h4 class="panel-title">{{ trans('admin.page.'.$current_page.'.questions') }}</h4>
                    </div>
                    <div class="tab-content">

                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>{{ trans('admin.page.'.$current_page.'.question-num') }}</th>
                                    <th>{{ trans('admin.page.'.$current_page.'.question-title') }}</th>
                                    <th>{{ trans('admin.page.'.$current_page.'.question-control') }}</th>
                                    <th>{{ trans('admin.page.'.$current_page.'.question-edit') }}</th>
                                    <th>{{ trans('admin.page.'.$current_page.'.question-delete') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($item->questions as $question)
                                <tr>
                                    <td>{{ $question->order }}</td>
                                    <td>{{ $question->question }}</td>
                                    <td>{{ trans( 'admin.common.'.( $question->is_control ? 'yes' : 'no' ) ).( $question->go_back ? ' -> go back to '.$question->go_back : '' ) }}</td>
                                    <td>
                                        <a class="btn btn-sm btn-success" href="{{ url('cms/'.$current_page.'/edit/'.$item->id.'/question/'.$question->id) }}">
                                            {{ trans('admin.page.'.$current_page.'.questions-edit') }}
                                        </a>
                                    </td>
                                    <td>
                                        <a class="btn btn-sm btn-success" onclick="return confirm('{{ trans('admin.common.sure') }}')" href="{{ url('cms/'.$current_page.'/edit/'.$item->id.'/question-del/'.$question->id) }}">
                                            {{ trans('admin.page.'.$current_page.'.questions-delete') }}
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif


        {{ Form::close() }}

        @if(!empty($item))
            <h3>Add question</h3>
            @include('admin.parts.vox-question', [
                'question' => null,
                'next' => $item->questions->count()+1
            ])
        @endif

    </div>
</div>

@endsection