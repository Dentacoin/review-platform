@extends('admin')

@section('content')

<h1 class="page-header">
    Incomplete Dentist Registrations (last 50)
    <a href="javascript:;" class="btn btn-primary pull-right btn-export">Export</a>
</h1>
<!-- end page-header -->



<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <div class="panel-heading-btn">
                    <a href="{{ url('/cms/incomplete/') }}?export=1" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                </div>
                <h4 class="panel-title">Incomplete Dentist Registrations (last 50)</h4>
            </div>
            <div class="panel-body">
        		<div class="panel-body">
					@include('admin.parts.table', [
						'table_id' => 'incomplete',
						'table_fields' => [
                            'id'                => array('label' => '#'),
                            'created_at'                => array('format' => 'datetime', 'label' => 'Date'),
                            'name'              => array('label' => '#'),
                            'email'              => array('label' => 'Email'),
                            'phone'              => array('label' => 'Phone'),
                            'country_id'				=> array('format' => 'country', 'label' => 'Country'),
                            'completed'                => array('format' => 'bool', 'label' => 'Registered'),
                            'notified1'                => array('format' => 'bool', 'label' => 'Email 1h'),
                            'notified2'                => array('format' => 'bool', 'label' => 'Email 24h'),
                            'notified3'                => array('format' => 'bool', 'label' => 'Email 72h'),
						],
                        'table_data' => $items,
						'table_pagination' => false,
                        'pagination_link' => array()
					])
                </div>
            </div>
        </div>
    </div>
</div>
@endsection