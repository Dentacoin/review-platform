@extends('admin')

@section('content')

	<h1 class="page-header">
		Users Statistics
	</h1>
	<!-- end page-header -->

	<div class="row">
        <div class="col-md-12">
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <div class="panel-heading-btn">
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                    </div>
                    <h4 class="panel-title">Search</h4>
                </div>
                <div class="panel-body users-filters">
					<form method="get" action="{{ url('cms/users/users_stats/') }}">
						<div class="row custom-row" style="margin-bottom: 10px;">
							<div class="col-md-3">
								<input type="text" class="form-control datepicker" name="search_users_from" value="{{ $search_users_from }}" placeholder="Search from" autocomplete="off">
							</div>
							<div class="col-md-3">
								<input type="text" class="form-control datepicker" name="search_users_to" value="{{ $search_users_to }}" placeholder="Search to" autocomplete="off">
							</div>
							<div class="col-md-2">
								<input type="submit" class="btn btn-sm btn-primary btn-block" name="search" value="Search">
							</div>
						</div>
						<div class="row custom-row" style="margin-bottom: 10px;">
							<div class="col-md-12">
								<a href="{{ url('cms/users/users_stats/?search_from=last-7') }}" style="margin-right: 10px;">Last 7 days</a>
								<a href="{{ url('cms/users/users_stats/?search_from=this-month') }}" style="margin-right: 10px;">This month</a>
								<a href="{{ url('cms/users/users_stats/?search_from=last-month') }}" style="margin-right: 10px;">Last month</a>
								<a href="{{ url('cms/users/users_stats/?search_from=this-year') }}" style="margin-right: 10px;">This year</a>
								<a href="{{ url('cms/users/users_stats/') }}" class="btn btn-sm btn-info">Clear filters</a>
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
					<h4 class="panel-title">DentaVox answered questions</h4>
				</div>
				<div class="panel-body">
					<div class="dataTables_wrapper">
						<div class="row">
							<div class="col-sm-12">
								<table class="table table-striped">
									<thead>
										<tr>
											<th style="width: 33%;"></th>
											<th style="width: 33%;">
												Count
											</th>
										</tr>
									</thead>
									<tbody>
										<tr>
											<td>Total</td>
											<td>{{ App\Models\VoxAnswer::getCount(false) }}</td>
										</tr>
										@foreach($answered_questions as $aq)
											<tr>
												<td>{{ strlen((string)$aq->month) == 1 ? '0'.$aq->month : $aq->month }}.{{ $aq->year }}</td>
												<td>{{ $aq->count }}</td>
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

	{{-- <div class="row">
		<div class="col-md-12">
			<div class="panel panel-inverse">
				<div class="panel-heading">
					<div class="panel-heading-btn">
						<a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
					</div>
					<h4 class="panel-title">User Types</h4>
				</div>
				<div class="panel-body">
					<div class="dataTables_wrapper">
						<div class="row">
							<div class="col-sm-12">
								<table class="table table-striped">
									<thead>
										<tr>
											<th>Dentists</th>
											<th>Clinics</th>
											<th>Dentists/Clinics</th>
											<th>Dentists Partners</th>
											<th>Clinics Partners</th>
											<th>All Partners</th>
											<th>Patients</th>
											<th>All types</th>
										</tr>
									</thead>
									<tbody>
										<tr>
											<td>{{ $dentists }}</td>
											<td>{{ $clinics }}</td>
											<td>{{ $dentists_clinics }}</td>
											<td>{{ $dentists_partners }}</td>
											<td>{{ $clinics_partners }}</td>
											<td>{{ $partners }}</td>
											<td>{{ $patients }}</td>
											<td>{{ $all_types }}</td>
										</tr>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div> --}}

	<div class="row">
		<div class="col-md-12">
			<div class="panel panel-inverse">
				<div class="panel-heading">
					<div class="panel-heading-btn">
						<a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
					</div>
					<h4 class="panel-title">Users Gender</h4>
				</div>
				<div class="panel-body">
					<div class="dataTables_wrapper">
						<div class="row">
							<div class="col-sm-12">
								<table class="table table-striped">
									<thead>
										<tr>
											@foreach($user_genders as $key => $g)
												<th style="width: 33%;">
													{{ $g->gender == 'm' ? 'Male' : ($g->gender == 'f' ? 'Female' : '-') }}
												</th>
											@endforeach
										</tr>
									</thead>
									<tbody>
										<tr>
											@foreach($user_genders as $key => $g)
												<td style="width: 33%;">{{ $g->total }}</td>
											@endforeach
										</tr>
									</tbody>
								</table>
							</div>
						</div>
					</div>
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
					<h4 class="panel-title">Users Country</h4>
				</div>
				<div class="panel-body">
					<div class="dataTables_wrapper">
						<div class="row">
							<div class="col-sm-12">
								<table class="table table-striped">
									<thead>
										<tr>
											<th>Country</th>
											<th>All types</th>
											<th>Dentists</th>
											<th>Clinics</th>
											<th>Dentists/Clinics</th>
											<th>Dentists Partners</th>
											<th>Clinics Partners</th>
											<th>All Partners</th>
											<th>Patients</th>
										</tr>
									</thead>
									<tbody>
										@foreach($countries as $c)
											<tr>
												<td>{{ $c['country_name'] }}</td>
												<td>{{ $c['total'] }}</td>
												<td>{{ $c['dentists'] }}</td>
												<td>{{ $c['clinics'] }}</td>
												<td>{{ $c['dentists_clinics'] }}</td>
												<td>{{ $c['dentists_partners'] }}</td>
												<td>{{ $c['clinics_partners'] }}</td>
												<td>{{ $c['partners'] }}</td>
												<td>{{ $c['patients'] }}</td>
											</tr>
										@endforeach

										{{-- "country_name" => "Afghanistan"
										"total" => 58
										"partners" => 0
										"patients" => 58
										"dentists" => 0
										"clinics" => 0
										"dentists_partners" => 0
										"clinics_partners" => 0 --}}
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

@endsection