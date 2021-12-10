@extends('admin')

@section('content')

    <h1 class="page-header">
        {{ trans('admin.page.'.$current_page.'.title') }}
        
        <a class="btn btn-primary pull-right" id="table-sort" href="javascript:;" alternate="Done" style="display: none;">Sort Surveys</a>
        <a class="btn btn-info pull-right" href="{{ $are_all_results_shown ? url('cms/vox/list/show-individual-results/') : url('cms/vox/list/show-all-results/') }}" style="margin-right: 10px;">{{ $are_all_results_shown ? 'Hide' : 'Show' }} All Results</a>
    </h1>
    <!-- end page-header -->

    @if(!empty($error))
        <div class="alert alert-danger">
            @foreach($error_arr as $key => $value)
                @foreach($value as $kk)
                    Vox ID {{ $key }}: {!! $kk !!} <br/>
                @endforeach
            @endforeach
        </div>
    @endif

    @if(!empty($questions_order_bug))
        <div class="alert alert-danger m-b-15">
            <b>Duplicated or missing order numbers</b>: <br/>
            @foreach($questions_order_bug_message as $key => $reorder)
                @foreach($reorder as $kk)
                    Vox ID {{ $key }}: {!! $kk !!}
                @endforeach
            @endforeach
        </div>
    @endif

    @if(!empty($without_translations))
        <div class="alert alert-warning">
            <b>Voxes without translations</b>: <br/>
            @foreach($without_translations as $without_trans)
                Vox ID {{ $without_trans }}<br/>
            @endforeach
        </div>
    @endif

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <div class="panel-heading-btn">
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                    </div>
                    <h4 class="panel-title">{{ trans('admin.page.'.$current_page.'.title') }}</h4>
                </div>
                <div class="panel-body">
                    <div class="row" style="display: flex;align-items: flex-end;">
                        <div class="col-md-6"> 
                            All voxes: {{ $voxes->count() }} <br/>
                            Active voxes: {{ $active_voxes_count }} <br/>
                            Hidden voxes: {{ $hidden_voxes_count }} <br/>
                        </div>
                        <div class="col-md-6">
                            <div class="search-questions-wrapper">
                                <label style="width: auto;display: flex;align-items: center;justify-content: flex-end;">
                                    <span style="font-weight: normal;">Search by keyword:</span>
                                    <input type="text" name="search-questions" id="search-questions" class="form-control" url="{{ url('cms/search-questions') }}" style="display: inline-block;max-width: 156px;margin-right: 17px;margin-left: 6px;height: 30px;">
                                </label>
                                <div class="results-wrapper" style="display: none;">
                                    <a href="javascript:;" class="close-results">X</a>
                                    <div class="results">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="panel-body">
                        <div class="row table-responsive">
                            <form method="post" action="{{ url('cms/vox/list') }}" id="translate-voxes">
                                {!! csrf_field() !!}
        
                                @include('admin.parts.table', [
                                    'table_id' => 'voxs',
                                    'table_fields' => $table_fields,
                                    'table_data' => $voxes,
                                    'table_pagination' => false,
                                    'pagination_link' => array()
                                ])
                                
                                <div style="display: none;">
                                    @foreach(config('langs-to-translate') as $code => $name)
                                        <label for="lang-{{ $code }}-2" style="display: block;">
                                            <input type="checkbox" name="languages[]" id="lang-{{ $code }}-2" value="{{ $code }}">
                                            {{ strtoupper($code) }}
                                        </label>
                                    @endforeach
                                    <button type="submit" name="mass-translate" value="1" class="btn btn-info" style="flex: 1" id="mass-translate-button">Translate selected voxes</button>
                                </div>
        
                                <div style="display: flex">
                                    <a class="btn btn-info translate-voxes-button" style="flex: 1" href="javascript:;" data-toggle="modal" data-target="#translateModal">
                                        Translate selected voxes
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="modal-error" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Errors</h4>
                </div>
                <div class="modal-body">
                    <p></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div id="translateModal" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Translate Voxes</h4>
                </div>
                <div class="modal-body">
                    <form id="languages-form" method="post">
                        @foreach(config('langs-to-translate') as $code => $name)
                            <label for="lang-{{ $code }}" style="display: block;">
                                <input type="checkbox" name="languages[]" id="lang-{{ $code }}" class="lang-checkbox" value="{{ $code }}">
                                {{ strtoupper($code) }}
                            </label>
                        @endforeach
                        <button type="submit" class="btn btn-primary btn-block" style="margin-top: 20px;">Translate</button>

                        <label class="alert alert-danger" style="display: none;margin-top: 10px;">Please, choose a language</label>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div id="hideSurveyModal" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Are you sure you want to hide this survey?</h4>
                </div>
                <div class="modal-body">
                    <form id="hide-survey-form-voxes" method="post" action="{{ url('cms/vox/hide-survey/') }}" original-action="{{ url('cms/vox/hide-survey/') }}">
                        {{ Form::text('hide-survey-confirm', null, array('class' => 'form-control input-title', 'placeholder' => 'Type HIDE to confirm')) }}
                        
                        <button type="submit" class="btn btn-primary btn-block" style="margin-top: 20px;">Submit</button>

                        <label class="alert alert-danger" style="display: none;margin-top: 10px;">If you want to hide the survey, please type HIDE</label>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <style type="text/css">
        .table-select-all {
            display: none;
        }
    </style>

@endsection