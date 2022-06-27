@extends('admin')

@section('content')

    <h1 class="page-header">
        Answer scales

        <a class="btn btn-success pull-right" href="javascript:;" data-toggle="modal" data-target="#addScaleModal">
            Add Scale
        </a>
    </h1>

    <div class="row">
		<div class="col-md-12">
			<div class="panel panel-inverse">
				<div class="panel-heading">
					<div class="panel-heading-btn">
						<a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
					</div>
					<h4 class="panel-title">Answer scales</h4>
				</div>
				<div class="panel-body">
					<div class="panel-body">
                        <div class="row">
                            <div class="col-sm-12">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>
                                                Name
                                            </th>
                                            <th>
                                                Q's
                                            </th>
                                            <th>
                                                Edit
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($scales as $scale)
                                            <tr q-scale-id="{{ $scale->id }}">
                                                <td class="scale-title">
                                                    {{ $scale->title }}
                                                </td>
                                                <td>
                                                    {{ count(explode(',', $scale->answers)) }}
                                                </td>
                                                <td>
                                                    <a class="btn btn-sm btn-success edit-scale-button" href="javascript:;" data-toggle="modal" data-target="#editScaleModal" scale-id="{{ $scale->id }}" >
                                                        <i class="fa fa-pencil"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
					</div>
				</div>
			</div>
		</div>
	</div>

    <div id="editScaleModal" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Edit scale</h4>
                </div>
                <div class="modal-body">
                    
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div id="addScaleModal" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Add scale</h4>
                </div>
                <div class="modal-body">
                    
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

@endsection