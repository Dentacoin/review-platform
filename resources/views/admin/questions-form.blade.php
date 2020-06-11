@extends('admin')


@section('content')

<h1 class="page-header">{{ empty($item) ? 'Add question' : 'Edit question' }}</h1>
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
                    <h4 class="panel-title">{{ empty($item) ? 'Add question' : 'Edit question' }}</h4>
                </div>
                <div class="panel-body">
                    {!! csrf_field() !!}
                    <div class="form-group">
                        <label class="col-md-3 control-label">{{ trans('admin.page.'.$current_page.'.order') }}</label>
                        <div class="col-md-9">
                            {{ Form::text('order', !empty($item) ? $item->order : null, array('class' => 'form-control')) }}
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
                        <div class="lang-tab tab-pane fade{{ $loop->first ? ' active in' : '' }}" data-lang="{{ $code }}" id="nav-tab-{{ $code }}">
                            <div class="form-group">
                                <label class="col-md-3 control-label">{{ trans('admin.page.'.$current_page.'.question') }}</label>
                                <div class="col-md-9">
                                    {{ Form::text('question-'.$code, !empty($item) ? $item->{'question:'.$code} : null, array('maxlength' => 256, 'class' => 'form-control')) }}
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label">{{ trans('admin.page.'.$current_page.'.label') }}</label>
                                <div class="col-md-9">
                                    {{ Form::text('label-'.$code, !empty($item) ? $item->{'label:'.$code} : null, array('maxlength' => 256, 'class' => 'form-control')) }}
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label">{{ trans('admin.page.'.$current_page.'.answers') }}</label>
                                <div class="col-md-9 answers-div">
                                    @if(!empty($item) && !empty($item->{'options:'.$code}))
                                        @foreach(json_decode( $item->{'options:'.$code}, true ) as $answer)
                                            <div class="form-group">
                                                <div class="col-md-5">
                                                    {{ Form::text('options-1-'.$code.'[]', $answer[0], array('maxlength' => 256, 'class' => 'form-control')) }}
                                                </div>
                                                <div class="col-md-5">
                                                    {{ Form::text('options-2-'.$code.'[]', $answer[1], array('maxlength' => 256, 'class' => 'form-control')) }}
                                                </div>
                                                <div class="col-md-2">
                                                    <a class="btn btn-sm btn-default remove-answer" href="javascript:;"><i class="fa fa-remove"> </i></a>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                    <a class="btn btn-sm btn-primary add-answer" href="javascript:;"><i class="fa fa-plus"> </i></a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="form-group">
                <label class="col-md-10 control-label"></label>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-block btn-sm btn-success">{{ empty($item) ? 'Add' : 'Save' }}</button>
                </div>
            </div>

        </form>

        <div id="answer-template" style="display: none;">
            <div class="form-group">
                <div class="col-md-5">
                    {{ Form::text('options-1-code[]', '', array('maxlength' => 256, 'class' => 'form-control')) }}
                </div>
                <div class="col-md-5">
                    {{ Form::text('options-2-code[]', '', array('maxlength' => 256, 'class' => 'form-control')) }}
                </div>
                <div class="col-md-2">
                    <a class="btn btn-sm btn-default remove-answer" href="javascript:;"><i class="fa fa-remove"> </i></a>
                </div>
            </div>
        </div>

    </div>
</div>


@endsection