@extends('admin')

@section('content')

<h1 class="page-header">
    Survey Respondents Explorer
</h1>
<!-- end page-header -->


<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <div class="panel-heading-btn">
                    <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                </div>
                <h4 class="panel-title"> Pick a question </h4>
            </div>
            <div class="panel-body">
                <form method="get" action="{{ url('cms/vox/explorer') }}" >
                    <div class="row">
                        <div class="col-md-12">
                            <select class="form-control" name="question" id="explorer-question">
                                @foreach($voxes as $vox)
                                    <optgroup label="{{ $vox->title }}">
                                        @foreach($vox->questions as $q)
                                            <option value="{{ $q->id }}" {!! $q->id==$question->id ? 'selected="selected"' : '' !!}>{{ $q->question }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>



<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <div class="panel-heading-btn">
                    <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                </div>
                <h4 class="panel-title">"{{ $question->question }}" Respondents</h4>
            </div>
            <div class="panel-body">
        		<div class="panel-body">
                    {!! csrf_field() !!}
					@include('admin.parts.table', [
						'table_id' => 'respondents',
						'table_fields' => [
                            'id'                => array('label' => 'ID'),
                            'name'              => array('label' => 'Name'),
							'country_id'				=> array('format' => 'country', 'label' => 'Country'),
                            'type'                => array('template' => 'admin.parts.table-users-type', 'label' => 'Type'),
                            'status'                => array('template' => 'admin.parts.table-users-status', 'label' => 'Status'),
                            'created_at'                => array('format' => 'datetime', 'label' => 'Registered'),
                            'login'                => array('template' => 'admin.parts.table-users-login', 'label' => 'Actions'),
						],
                        'table_data' => $question->respondents->take(50)->pluck('user'),
						'table_pagination' => false,
                        'pagination_link' => array()
					])
                </div>
            </div>
        </div>
    </div>
</div>
@endsection