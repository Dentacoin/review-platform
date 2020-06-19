@extends('admin')

@section('content')

	<h1 class="page-header">
	    Trusted Reviews Pages
	</h1>

	<div class="row">
	    <div class="col-md-12">
	        <div class="panel panel-inverse">
	            <div class="panel-heading">
	                <div class="panel-heading-btn">
	                    <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
	                </div>
	                <h4 class="panel-title">Trusted Reviews Pages</h4>
	            </div>
	            <div class="panel-body">
	            	<!-- <h4 style="margin-bottom: 30px;">Home page - <a href="https://dentavox.dentacoin.com/" target="_blank">https://dentavox.dentacoin.com/</a></h4> -->
		            @include('admin.parts.table', [
						'table_id' => 'admins',
						'table_fields' => [
							'name'			    => array('width' => '20%'),
							'url'			    => array('width' => '100%'),
							'update'			=> array('template' => 'admin.parts.table-voxes-edit'),
						],
                        'table_data' => $pages,
						'table_pagination' => false,
                        'pagination_link' => array()
					])

					@if($admin->id == 1)
	                    <div class="form-group">
	                        <label class="col-md-10 control-label"></label>
	                        <div class="col-md-1">
	                            <a href="{{ url('cms/pages/trp/add') }}" class="btn btn-sm btn-success">Add</a>
	                        </div>
	                    </div>
	                @endif
	            </div>
	        </div>
	    </div>
	</div>

	<!-- begin row -->
	<div class="row">
	    <!-- begin col-6 -->
	    <div class="col-md-6">
	        <!-- begin panel -->
	        <div class="panel panel-inverse">
	            <div class="panel-heading">
	                <div class="panel-heading-btn">
	                    <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"></a>
	                </div>
	                <h4 class="panel-title">Export Translations</h4>
	            </div>
	            <div class="panel-body">
	                <form class="form-horizontal" method="post" action="{{ url('cms/pages/trp/export') }}">
	                    {!! csrf_field() !!}
	                    <div class="form-group">
	                        <div class="col-md-12">
	                            Use this feature to download a translation table. You will receive a three column file. <br/>
								Don't edit anything in the first one. <br/>
								The second contains phrases in the language "FROM". <br/>
								In the <b>third column</b>, complete the translations. If there are any, they will be filled in there so you can easily edit them. <br/>
	                        </div>
	                    </div>
	                    <div class="form-group">
	                        <label class="col-md-4 control-label">From</label>
	                        <div class="col-md-8">
	                            <select class="form-control" name="from">
	                                @foreach($langs as $key => $lang_info)
	                                    <option value="{{ $key }}">{{ $lang_info['name'] }}</option>
	                                @endforeach
	                            </select>
	                        </div>
	                    </div>
	                    <div class="form-group">
	                        <label class="col-md-4 control-label">To</label>
	                        <div class="col-md-8">
	                            <select class="form-control" name="to">
	                                @foreach($langs as $key => $lang_info)
	                                    <option value="{{ $key }}">{{ $lang_info['name'] }}</option>
	                                @endforeach
	                            </select>
	                        </div>
	                    </div>
	                    <div class="form-group">
	                        <div class="col-md-12">
	                            <button type="submit" name="export" value="1" class="btn btn-block btn-success">Download</button>
	                        </div>
	                    </div>
	                </form>
	            </div>
	        </div>
	        <!-- end panel -->
	    </div>
	    <!-- begin col-6 -->
	    <div class="col-md-6">
	        <!-- begin panel -->
	        <div class="panel panel-inverse">
	            <div class="panel-heading">
	                <div class="panel-heading-btn">
	                    <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"></a>
	                </div>
	                <h4 class="panel-title">Import Translations</h4>
	            </div>
	            <div class="panel-body">

	                <form class="form-horizontal" method="post" enctype="multipart/form-data" action="{{ url('cms/pages/trp/import') }}">
	                    {!! csrf_field() !!}
	                    <div class="form-group">
	                        <div class="col-md-12">
	                        	Select a translation file (previously generated by the system). <br/>
								Then select the languages ​​that were <b>used when exporting</b> the template. <br/>
								Check again just in case and click the "Upload" button. <br/>
	                        </div>
	                    </div>
	                    <div class="form-group">
	                        <label class="col-md-4 control-label">From</label>
	                        <div class="col-md-8">
	                            <select class="form-control" name="source">
	                                @foreach($langs as $key => $lang_info)
	                                    <option value="{{ $key }}">{{ $lang_info['name'] }}</option>
	                                @endforeach
	                            </select>
	                        </div>
	                    </div>
	                    <div class="form-group">
	                        <label class="col-md-4 control-label">To</label>
	                        <div class="col-md-8">
	                            <select class="form-control" name="from">
	                                @foreach($langs as $key => $lang_info)
	                                    <option value="{{ $key }}" >{{ $lang_info['name'] }}</option>
	                                @endforeach
	                            </select>
	                        </div>
	                    </div>
	                    <div class="form-group">
	                        <label class="col-md-4 control-label">Choose file</label>
	                        <div class="col-md-8">
	                            <input type="file" name="table" accept=".xls" />
	                        </div>
	                    </div>
	                    <div class="form-group">
	                        <div class="col-md-12">
	                            <button type="submit" name="import" class="btn btn-block btn-success" value="1">Upload</button>
	                        </div>
	                    </div>
	                </form>
	            </div>
	        </div>
	        <!-- end panel -->
	    </div>
	</div>

@endsection