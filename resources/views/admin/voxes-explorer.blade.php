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
                <h4 class="panel-title">Survey Respondents Explorer</h4>
            </div>
            <div class="panel-body">
                <form method="get" action="{{ url('cms/vox/explorer') }}" >
                    <div class="row">
                        <div class="col-md-12">
                            <select class="form-control select2" name="question" id="explorer-survey">
                                @foreach($voxes as $survey)
                                    <option value="{{ $survey->id }}" {!! $survey->id==$vox_id ? 'selected="selected"' : '' !!}>{{ $survey->title }}</option>
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
                <h4 class="panel-title"> Pick a question </h4>
            </div>
            <div class="panel-body">
                <form method="get" action="{{ url('cms/vox/explorer') }}" vox-id="{{ $vox_id }}">
                    <div class="row">
                        <div class="col-md-12">
                            <select class="form-control select2" name="question" id="explorer-question">
                                <option value="">(All survey respondents)</option>
                                @foreach($vox->questions as $q)
                                    <option value="{{ $q->id }}" {!! !empty($question) && $q->id==$question->id ? 'selected="selected"' : '' !!}>{{ $q->question }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@if(!$question)
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <div class="panel-heading-btn">
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                    </div>
                    <h4 class="panel-title">"{{ $vox->title }}" Respondents</h4>
                </div>
                <div class="panel-body">
                    <div>
                        {!! csrf_field() !!}
                        @include('admin.parts.table', [
                            'table_id' => 'respondents',
                            'table_fields' => [
                                'user.id'                => array('label' => 'ID'),
                                'user.name'              => array('label' => 'Name'),
                                'user.country_id'                => array('format' => 'country', 'label' => 'Country'),
                                'user.type'                => array('template' => 'admin.parts.table-users-type', 'label' => 'User/Dentist'),
                                'user.status'                => array('template' => 'admin.parts.table-users-status', 'label' => 'Status'),
                                'user.created_at'                => array('format' => 'datetime', 'label' => 'Registered'),
                                'user.login'                => array('template' => 'admin.parts.table-respondents-login', 'label' => 'Actions'),
                            ],
                            'table_data' => $respondents,
                            'table_pagination' => false,
                            'pagination_link' => array()
                        ])
                    </div>                
                </div>
            </div>
        </div>
    </div>
@endif


@if($question)
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
                                'user.id'                => array('label' => 'ID'),
                                'user.name'              => array('label' => 'Name'),
                                'user.country_id'                => array('format' => 'country', 'label' => 'Country'),
                                'user.type'                => array('template' => 'admin.parts.table-users-type', 'label' => 'User/Dentist'),
                                'user.status'                => array('template' => 'admin.parts.table-users-status', 'label' => 'Status'),
                                'user.created_at'                => array('format' => 'datetime', 'label' => 'Registered'),
                                'user.login'                => array('template' => 'admin.parts.table-respondents-login', 'label' => 'Actions'),
                            ],
                            'table_data' => $question_respondents,
                            'table_pagination' => false,
                            'pagination_link' => array()
                        ])
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif


@if($total_pages > 1)
    <nav aria-label="Page navigation" style="text-align: center;">
        <ul class="pagination">
            <li class="{{ ($page <= 1 ?  'disabled' : '' ) }}">
                <a class="page-link" href="{{ url('cms/vox/explorer/'.$vox_id.(!empty($question) ? '/'.$question->id : '').'?page=1') }}" aria-label="Previous">
                    <span aria-hidden="true"> << </span>
                </a>
            </li>
            <li class="{{ ($page <= 1 ?  'disabled' : '' ) }}">
                <a class="page-link prev" href="{{ url('cms/vox/explorer/'.$vox_id.(!empty($question) ? '/'.$question->id : '').'?page='.($page>1 ? $page-1 : '1')) }}"  aria-label="Previous">
                    <span aria-hidden="true"> < </span>
                </a>
            </li>
            @for($i=$start; $i<=$end; $i++)
                <li class="{{ ($i == $page ?  'active' : '') }}">
                    <a class="page-link" href="{{ url('cms/vox/explorer/'.$vox_id.(!empty($question) ? '/'.$question->id : '').'?page='.$i) }}">{{ $i }}</a>
                </li>
            @endfor
            <li class="{{ ($page >= $total_pages ? 'disabled' : '') }}">
                <a class="page-link next" href="{{ url('cms/vox/explorer/'.$vox_id.(!empty($question) ? '/'.$question->id : '').'?page='.($page < $total_pages ? $page+1 :  $total_pages)) }}" aria-label="Next"> <span aria-hidden="true"> > </span> </a>
            </li>
            <li class="{{ ($page >= $total_pages ? 'disabled' : '') }}">
                <a class="page-link" href="{{ url('cms/vox/explorer/'.$vox_id.(!empty($question) ? '/'.$question->id : '').'?page='.$total_pages) }}" aria-label="Next"> <span aria-hidden="true"> >> </span>  </a>
            </li>
        </ul>
    </nav>
@endif


@endsection