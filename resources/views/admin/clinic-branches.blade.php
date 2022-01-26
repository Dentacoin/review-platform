@extends('admin')

@section('content')

	<h1 class="page-header">
		Clinic Branches

		<a class="label label-info pull-right" href="{{ url('cms/trp/add-clinic-branch') }}">Add a new branch for existing clinics</a>
	</h1>

	<div class="row">
		<div class="col-md-12">
			<div class="panel panel-inverse">
				<div class="panel-heading">
					<div class="panel-heading-btn">
						<a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
					</div>
					<h4 class="panel-title">Clinic Branches</h4>
				</div>
				<div class="panel-body users-filters">
					<form method="get" action="{{ url('cms/trp/clinic-branches') }}">
						<div class="row" style="margin-bottom: 10px;">  
							<div class="col-md-2">
								<input type="text" class="form-control" name="search-id" value="{{ $search_id }}" placeholder="search clinic ID">
							</div>
							<div class="col-md-2">
								<input type="submit" class="btn btn-sm btn-primary btn-block" value="Search">
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>

	<!-- end page-header -->
	<div class="row">
		<div class="col-md-12">
			<div class="panel panel-inverse">
				<div class="panel-heading">
					<div class="panel-heading-btn">
						<a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
					</div>
					<h4 class="panel-title">Clinic Branches</h4>
				</div>
				<div class="panel-body">
					<table class="table table-striped table-question-list">
						<thead>
							<tr>
								<th>Clinic</th>
								<th>Branches</th>
							</tr>
						</thead>
						<tbody>
							@foreach($items as $clinic_user)
								@foreach($clinic_user->branches as $br)
									<tr>
										<td>
											<a href="{{ url('cms/users/users/edit/'.$clinic_user->id) }}">{{ $clinic_user->name }}</a>
										</td>
										<td>
											<a href="{{ url('cms/users/users/edit/'.$br->branchClinic->id) }}">{{ $br->branchClinic->name }}</a>
										</td>
									</tr>
								@endforeach
							@endforeach
						</tbody>
					</div>
				</div>
			</div>
		</div>
	</div>

@endsection