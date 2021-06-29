@extends('admin')

@section('content')

<h1 class="page-header">
    {{ trans('admin.page.'.$current_page.'.title') }}
    
    <a class="btn btn-primary pull-right" id="table-sort" href="javascript:;" alternate="Done">Sort Surveys</a>
    <a class="btn btn-info pull-right" href="{{ $are_all_results_shown ? url('cms/vox/list/show-individual-results') : url('cms/vox/list/show-all-results') }}" style="margin-right: 10px;">Show {{ $are_all_results_shown ? 'Individual' : 'All' }} Results</a>
</h1>
<!-- end page-header -->

@if(!empty($error))
   <i class="fa fa-exclamation-triangle err-vox" data-toggle="modal" data-target="#errorsModal"></i>
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
                            <div class="results"  style="display: none;">
                            </div>
                        </div>
                    </div>
                </div>
        		<div class="panel-body">
					@include('admin.parts.table', [
						'table_id' => 'voxs',
						'table_fields' => $table_fields,
                        'table_data' => $voxes,
						'table_pagination' => false,
                        'pagination_link' => array()
					])
                </div>
            </div>
        </div>
    </div>
</div>

@if(!empty($error))
    <div id="errorsModal" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Errors</h4>
                </div>
                <div class="modal-body">
                    @foreach($error_arr as $key => $value)
                        {{ $key+1 }}. <a href="{{ isset($value['link']) ?? 'javascript:;'  }}" target="_blank">{{ $value['error'] }}</a><br/>
                    @endforeach

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>

        </div>
    </div>
@endif

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

@endsection