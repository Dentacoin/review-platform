@extends('admin')

@section('content')

<h1 class="page-header">Support Categories</h1>
<!-- end page-header -->

<div class="cat">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <div class="panel-heading-btn">
                    <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                </div>
                <h4 class="panel-title">Support Categories</h4>
            </div>
            <div class="panel-body">
        		<div class="panel-body">
					<div class="dataTables_wrapper">
					    <div class="cat">
					    	<div class="col-sm-12">
					    		<table class="table table-striped">
					                <thead>
					                    <tr>
					                       	<th>
					                            Category name
					                        </th>
					                        <th>
					                            Edit
					                        </th>
					                        <th>
					                            Delete
					                        </th>
					                    </tr>
					                </thead>
					                <tbody>
					                	@foreach($categories as $cat)
					                    	<tr>
					                            <td>
					                                {{ $cat->name }}
					                            </td>
					                            <td>
					                                <a class="btn btn-sm btn-primary" href="{{ url('cms/'.$current_page.'/categories/edit/'.$cat->id) }}">{{ trans('admin.table.edit') }}</a>
					                            </td>
					                            <td>
					                                <a class="btn btn-sm btn-deafult" href="{{ url('cms/'.$current_page.'/categories/delete/'.$cat->id) }}" onclick="return confirm('Are you sure you want to DELETE this?');">{{ trans('admin.table.delete') }}</a>
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
                            <a href="{{ url('cms/'.$current_page.'/categories/add') }}" class="btn btn-sm btn-success">Add category</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection