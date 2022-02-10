@extends('admin')

@section('content')

<h1 class="page-header">{{ trans('admin.page.'.$current_page.'.categories.title') }}</h1>
<!-- end page-header -->


<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <div class="panel-heading-btn">
                    <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                </div>
                <h4 class="panel-title">{{ trans('admin.page.'.$current_page.'.categories.title') }}</h4>
            </div>
            <div class="panel-body">
        		<div class="panel-body">
					<div class="dataTables_wrapper">
					    <div class="row">
					    	<div class="col-sm-12">
					    		<table class="table table-striped">
					                <thead>
					                    <tr>
					                       	<th>
					                            {{ trans('admin.page.'.$current_page.'.table.category.name') }}
					                        </th>
					                       	<th>
					                            Number of Surveys
					                        </th>
					                       	<th>
					                            Surveys
					                        </th>
											<th>
												Number of Daily Polls
											</th>
					                        <th>
					                            {{ trans('admin.page.'.$current_page.'.table.category.update') }}
					                        </th>
					                        <th>
					                            {{ trans('admin.page.'.$current_page.'.table.category.delete') }}
					                        </th>
					                    </tr>
					                </thead>
					                <tbody>
					                	@foreach($categories as $row)
					                    	<tr>
					                            <td>
					                                {{ $row->name }}
					                            </td>
					                            <td>
					                                {{ $row->voxes->count() }}
					                            </td>
					                            <td>
					                            	@foreach($row->voxes as $vox)
														<a href="{{ url('cms/vox/edit/'.$vox->vox_id) }}" target="_blank">
															{{ $vox->vox->title }}
														</a>
														<br/>
					                                @endforeach
					                            </td>
					                            <td>
					                                {{ $row->polls->count() }}
					                            </td>
					                            <td>
					                                <a class="btn btn-sm btn-primary" href="{{ url('cms/'.$current_page.'/categories/edit/'.$row->id) }}">{{ trans('admin.table.edit') }}</a>
					                            </td>
					                            <td>
					                                <a class="btn btn-sm btn-deafult" href="{{ url('cms/'.$current_page.'/categories/delete/'.$row->id) }}">{{ trans('admin.table.delete') }}</a>
					                            </td>
					                    	</tr>
					                    @endforeach
					                </tbody>
					            </table>
					        </div>
					    </div>
					</div>

                    <div class="form-group">
                        <div class="col-md-2">
                            <a href="{{ url('cms/'.$current_page.'/categories/add') }}" class="btn btn-sm btn-success">{{ trans('admin.page.'.$current_page.'.categories.add') }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection