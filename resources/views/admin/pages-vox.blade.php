@extends('admin')

@section('content')

	<h1 class="page-header">
	    DentaVox Pages
	</h1>

	<div class="row">
	    <div class="col-md-12">
	        <div class="panel panel-inverse">
	            <div class="panel-heading">
	                <div class="panel-heading-btn">
	                    <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
	                </div>
	                <h4 class="panel-title">DentaVox Pages</h4>
	            </div>
	            <div class="panel-body">
	            	<!-- <h4 style="margin-bottom: 30px;">Home page - <a href="https://dentavox.dentacoin.com/" target="_blank">https://dentavox.dentacoin.com/</a></h4> -->
		            @include('admin.parts.table', [
						'table_id' => 'admins',
						'table_fields' => [
							'name'			    => array('width' => '20%'),
							'url'			    => array('width' => '100%'),
							'update'			=> array('format' => 'update'),
						],
                        'table_data' => $pages,
						'table_pagination' => false,
                        'pagination_link' => array()
					])

					@if($admin->id == 1)
	                    <div class="form-group">
	                        <label class="col-md-10 control-label"></label>
	                        <div class="col-md-1">
	                            <a href="{{ url('cms/pages/vox/add') }}" class="btn btn-sm btn-success">Add</a>
	                        </div>
	                    </div>
	                @endif
	            </div>
	        </div>
	    </div>
	</div>

@endsection