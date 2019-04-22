@extends('admin')

@section('content')

<h1 class="page-header" id="respondents-sort">
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
                <h4 class="panel-title">Pick a survey</h4>
            </div>
            <div class="panel-body">
                <form method="get" action="{{ url('cms/vox/explorer') }}" >
                    <div class="row">
                        <div class="col-md-12">
                            <select class="form-control select2" name="question" id="explorer-survey">
                                <option value="">Select survey</option>
                                @foreach($voxes as $survey)
                                    <option value="{{ $survey->id }}" {!! !empty($vox_id) && $survey->id==$vox_id ? 'selected="selected"' : '' !!}>{{ $survey->title }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>


@if(!empty($vox_id))
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
                                    'created_at'                => array('format' => 'datetime', 'label' => 'Date Taken','order' => true, 'orderKey' => 'taken'),
                                    'user.id'                => array('label' => 'ID'),
                                    'user.name'              => array('template' => 'admin.parts.table-respondents-user-name', 'label' => 'Name','order' => true, 'orderKey' => 'name'),
                                    'user.country_id'                => array('format' => 'country', 'label' => 'Country', 'order' => true, 'orderKey' => 'country'),
                                    'user.type'                => array('template' => 'admin.parts.table-respondents-usertype', 'label' => 'User Type','order' => true, 'orderKey' => 'type'),
                                    'user.created_at'                => array('format' => 'datetime', 'label' => 'Registered'),
                                    'user.logins'          => array('template' => 'admin.parts.table-logins-device', 'label' => 'Device'),
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

    @if(!$question)
        @if($total_pages > 1)
            <nav aria-label="Page navigation" style="text-align: center;">
                <ul class="pagination">
                    <li class="{{ ($page <= 1 ?  'disabled' : '' ) }}">
                        <a class="page-link" href="{{ url('cms/vox/explorer/'.$vox_id.(!empty($question) ? '/'.$question->id : '').'?page=1'.(!empty(request()->input('country')) ? '&country='.request()->input( 'country' ) : '').(!empty(request()->input('name')) ? '&name='.request()->input( 'name' ) : '').(!empty(request()->input('taken')) ? '&taken='.request()->input( 'taken' ) : '').(!empty(request()->input('type')) ? '&type='.request()->input( 'type' ) : '')) }}" aria-label="Previous">
                            <span aria-hidden="true"> << </span>
                        </a>
                    </li>
                    <li class="{{ ($page <= 1 ?  'disabled' : '' ) }}">
                        <a class="page-link prev" href="{{ url('cms/vox/explorer/'.$vox_id.(!empty($question) ? '/'.$question->id : '').'?page='.($page>1 ? $page-1 : '1').(!empty(request()->input('country')) ? '&country='.request()->input( 'country' ) : '').(!empty(request()->input('name')) ? '&name='.request()->input( 'name' ) : '').(!empty(request()->input('taken')) ? '&taken='.request()->input( 'taken' ) : '').(!empty(request()->input('type')) ? '&type='.request()->input( 'type' ) : '')) }}"  aria-label="Previous">
                            <span aria-hidden="true"> < </span>
                        </a>
                    </li>
                    @for($i=$start; $i<=$end; $i++)
                        <li class="{{ ($i == $page ?  'active' : '') }}">
                            <a class="page-link" href="{{ url('cms/vox/explorer/'.$vox_id.(!empty($question) ? '/'.$question->id : '').'?page='.$i.(!empty(request()->input('country')) ? '&country='.request()->input( 'country' ) : '').(!empty(request()->input('name')) ? '&name='.request()->input( 'name' ) : '').(!empty(request()->input('taken')) ? '&taken='.request()->input( 'taken' ) : '').(!empty(request()->input('type')) ? '&type='.request()->input( 'type' ) : '')) }}">{{ $i }}</a>
                        </li>
                    @endfor
                    <li class="{{ ($page >= $total_pages ? 'disabled' : '') }}">
                        <a class="page-link next" href="{{ url('cms/vox/explorer/'.$vox_id.(!empty($question) ? '/'.$question->id : '').'?page='.($page < $total_pages ? $page+1 :  $total_pages).(!empty(request()->input('country').(!empty(request()->input('name')) ? '&name='.request()->input( 'name' ) : '')) ? '&country='.request()->input( 'country' ) : '').(!empty(request()->input('taken')) ? '&taken='.request()->input( 'taken' ) : '').(!empty(request()->input('type')) ? '&type='.request()->input( 'type' ) : '')) }}" aria-label="Next"> <span aria-hidden="true"> > </span> </a>
                    </li>
                    <li class="{{ ($page >= $total_pages ? 'disabled' : '') }}">
                        <a class="page-link" href="{{ url('cms/vox/explorer/'.$vox_id.(!empty($question) ? '/'.$question->id : '').'?page='.$total_pages.(!empty(request()->input('country')) ? '&country='.request()->input( 'country' ) : '').(!empty(request()->input('name')) ? '&name='.request()->input( 'name' ) : '').(!empty(request()->input('taken')) ? '&taken='.request()->input( 'taken' ) : '').(!empty(request()->input('type')) ? '&type='.request()->input( 'type' ) : '')) }}" aria-label="Next"> <span aria-hidden="true"> >> </span>  </a>
                    </li>
                </ul>
            </nav>
        @endif
    @endif


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
                                    'created_at'                => array('format' => 'datetime', 'label' => 'Date Taken','order' => true, 'orderKey' => 'taken'),
                                    'user.id'                => array('label' => 'ID'),
                                    'user.name'              => array('template' => 'admin.parts.table-respondents-user-name', 'label' => 'Name','order' => true, 'orderKey' => 'name'),
                                    'user.country_id'                => array('format' => 'country', 'label' => 'Country', 'order' => true, 'orderKey' => 'country'),
                                    'user.type'                => array('template' => 'admin.parts.table-respondents-usertype', 'label' => 'User Type','order' => true, 'orderKey' => 'type'),
                                    'user.created_at'                => array('format' => 'datetime', 'label' => 'Registered'),
                                    'user.logins'          => array('template' => 'admin.parts.table-logins-device', 'label' => 'Device'),
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

    @if($question)
        @if($total_pages > 1)
            <nav aria-label="Page navigation" style="text-align: center;">
                <ul class="pagination">
                    <li class="{{ ($page <= 1 ?  'disabled' : '' ) }}">
                        <a class="page-link" href="{{ url('cms/vox/explorer/'.$vox_id.(!empty($question) ? '/'.$question->id : '').'?page=1'.(!empty(request()->input('country')) ? '&country='.request()->input( 'country' ) : '').(!empty(request()->input('name')) ? '&name='.request()->input( 'name' ) : '').(!empty(request()->input('taken')) ? '&taken='.request()->input( 'taken' ) : '').(!empty(request()->input('type')) ? '&type='.request()->input( 'type' ) : '')) }}" aria-label="Previous">
                            <span aria-hidden="true"> << </span>
                        </a>
                    </li>
                    <li class="{{ ($page <= 1 ?  'disabled' : '' ) }}">
                        <a class="page-link prev" href="{{ url('cms/vox/explorer/'.$vox_id.(!empty($question) ? '/'.$question->id : '').'?page='.($page>1 ? $page-1 : '1').(!empty(request()->input('country')) ? '&country='.request()->input( 'country' ) : '').(!empty(request()->input('name')) ? '&name='.request()->input( 'name' ) : '').(!empty(request()->input('taken')) ? '&taken='.request()->input( 'taken' ) : '').(!empty(request()->input('type')) ? '&type='.request()->input( 'type' ) : '')) }}"  aria-label="Previous">
                            <span aria-hidden="true"> < </span>
                        </a>
                    </li>
                    @for($i=$start; $i<=$end; $i++)
                        <li class="{{ ($i == $page ?  'active' : '') }}">
                            <a class="page-link" href="{{ url('cms/vox/explorer/'.$vox_id.(!empty($question) ? '/'.$question->id : '').'?page='.$i.(!empty(request()->input('country')) ? '&country='.request()->input( 'country' ) : '').(!empty(request()->input('name')) ? '&name='.request()->input( 'name' ) : '').(!empty(request()->input('taken')) ? '&taken='.request()->input( 'taken' ) : '').(!empty(request()->input('type')) ? '&type='.request()->input( 'type' ) : '')) }}">{{ $i }}</a>
                        </li>
                    @endfor
                    <li class="{{ ($page >= $total_pages ? 'disabled' : '') }}">
                        <a class="page-link next" href="{{ url('cms/vox/explorer/'.$vox_id.(!empty($question) ? '/'.$question->id : '').'?page='.($page < $total_pages ? $page+1 :  $total_pages).(!empty(request()->input('country')) ? '&country='.request()->input( 'country' ) : '').(!empty(request()->input('name')) ? '&name='.request()->input( 'name' ) : '').(!empty(request()->input('taken')) ? '&taken='.request()->input( 'taken' ) : '').(!empty(request()->input('type')) ? '&type='.request()->input( 'type' ) : '')) }}" aria-label="Next"> <span aria-hidden="true"> > </span> </a>
                    </li>
                    <li class="{{ ($page >= $total_pages ? 'disabled' : '') }}">
                        <a class="page-link" href="{{ url('cms/vox/explorer/'.$vox_id.(!empty($question) ? '/'.$question->id : '').'?page='.$total_pages.(!empty(request()->input('country')) ? '&country='.request()->input( 'country' ) : '').(!empty(request()->input('name')) ? '&name='.request()->input( 'name' ) : '').(!empty(request()->input('taken')) ? '&taken='.request()->input( 'taken' ) : '').(!empty(request()->input('type')) ? '&type='.request()->input( 'type' ) : '')) }}" aria-label="Next"> <span aria-hidden="true"> >> </span>  </a>
                    </li>
                </ul>
            </nav>
        @endif
    @endif

@endif

@endsection