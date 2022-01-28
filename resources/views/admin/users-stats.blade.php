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
								<form method="post" id="answered-questions-count-form" action="{{ url('cms/users/answered-questions-count/') }}" date="from {{ date('d-m-Y', Carbon::now()->firstOfMonth()->timestamp) }} to {{ date('d-m-Y') }}">
									{!! csrf_field() !!}
									<input type="hidden" name="search_from" value="{{ Carbon::now()->firstOfMonth() }}"/>
									<input type="hidden" name="search_to" value="{{ Carbon::now() }}"/>
									<input type="submit" class="btn btn-info" value="Check count from {{ date('d-m-Y', Carbon::now()->firstOfMonth()->timestamp) }} to {{ date('d-m-Y') }}"/>
									<p style="display: none;color: #f77147;">it may take a few minutes</p>
								</form>
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
                    <h4 class="panel-title">Search</h4>
                </div>
                <div class="panel-body users-filters">
					<form method="get" action="{{ url('cms/users/users_stats/') }}" style="margin-bottom: 40px;">
						<div class="row custom-row" style="margin-bottom: 10px;">
							<div class="col-md-2">
								<input type="text" class="form-control datepicker" name="search_users_from" value="{{ $search_users_from }}" placeholder="Search from" autocomplete="off">
							</div>
							<div class="col-md-2">
								<input type="text" class="form-control datepicker" name="search_users_to" value="{{ $search_users_to }}" placeholder="Search to" autocomplete="off">
							</div>
							<div class="col-md-2">
								<input type="submit" class="btn btn-sm btn-primary btn-block" value="Search">
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

					<div class="row">
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
															<th>Patients</th>
															<th>Dentists/Clinics</th>
															<th>Partners</th>
															<th>Total</th>
														</tr>
													</thead>
													<tbody>
														<tr>
															<td>{{ $user_types[0]->total }}</td>
															<td>{{ $user_types[1]->total }}</td>
															<td>{{ $user_types[1]->partners }}</td>
															<td>{{ $user_types[0]->total + $user_types[1]->total }}</td>
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
															<th>Total</th>
															<th>Patients</th>
															<th>Dentists</th>
															<th>Clinics</th>
															<th>Approved <br/> Dentists</th>
															<th>Approved <br/> Clinics</th>
															<th>Partners</th>
														</tr>
													</thead>
													<tbody>
														@foreach($countries as $c)
															<tr>
																<td>{{ $c->country_id ? $countriesArray[$c->country_id] : '-' }}</td>
																<td>{{ $c->total }}</td>
																<td>{{ $c->patients }}</td>
																<td>{{ $c->dentists }}</td>
																<td>{{ $c->clinics }}</td>
																<td>{{ $c->approved_dentists }}</td>
																<td>{{ $c->approved_clinics }}</td>
																<td>{{ $c->partners }}</td>
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
					
					@if(!request('show-all'))
						<a class="btn btn-info btn-block" href="{{ empty($_SERVER['REQUEST_URI']) ? url('cms/users/users_stats/?show-all=1') : (str_contains($_SERVER['REQUEST_URI'], '?') ? 'https://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'].'&show-all=1' : 'https://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'].'?show-all=1') }}">Show All Demographics</a>
					@endif

					@if(request('show-all'))
						<div class="row">
							<div class="col-md-12">
								<div class="panel panel-inverse">
									<div class="panel-heading">
										<div class="panel-heading-btn">
											<a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
										</div>
										<h4 class="panel-title">Users Marital Status</h4>
									</div>
									<div class="panel-body">
										<div class="dataTables_wrapper">
											<div class="row">
												<div class="col-sm-12">
													<table class="table table-striped">
														<thead>
															<tr>
																<th>Marital Status</th>
																<th>Total</th>
																<th>Patients</th>
																<th>Dentists</th>
																<th>Clinics</th>
																<th>Partners</th>
															</tr>
														</thead>
														<tbody>
															@foreach($marital_statuses as $m)
																@php( $marital_status = $m->marital_status ? config('vox.details_fields.marital_status.values')[$m->marital_status] : '-')
																<tr>
																	<td>{{ $marital_status }}</td>
																	<td>{{ $m->total }}</td>
																	<td>{{ $m->patients }}</td>
																	<td>{{ $m->dentists }}</td>
																	<td>{{ $m->clinics }}</td>
																	<td>{{ $m->partners }}</td>
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

						<div class="row">
							<div class="col-md-12">
								<div class="panel panel-inverse">
									<div class="panel-heading">
										<div class="panel-heading-btn">
											<a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
										</div>
										<h4 class="panel-title">Users Children</h4>
									</div>
									<div class="panel-body">
										<div class="dataTables_wrapper">
											<div class="row">
												<div class="col-sm-12">
													<table class="table table-striped">
														<thead>
															<tr>
																<th>Children</th>
																<th>Total</th>
																<th>Patients</th>
																<th>Dentists</th>
																<th>Clinics</th>
																<th>Partners</th>
															</tr>
														</thead>
														<tbody>
															@foreach($children as $ch)
																@php( $childs = $ch->children ? config('vox.details_fields.children.values')[$ch->children] : '-')
																<tr>
																	<td>{{ $childs }}</td>
																	<td>{{ $ch->total }}</td>
																	<td>{{ $ch->patients }}</td>
																	<td>{{ $ch->dentists }}</td>
																	<td>{{ $ch->clinics }}</td>
																	<td>{{ $ch->partners }}</td>
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

						<div class="row">
							<div class="col-md-12">
								<div class="panel panel-inverse">
									<div class="panel-heading">
										<div class="panel-heading-btn">
											<a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
										</div>
										<h4 class="panel-title">Users Household Children</h4>
									</div>
									<div class="panel-body">
										<div class="dataTables_wrapper">
											<div class="row">
												<div class="col-sm-12">
													<table class="table table-striped">
														<thead>
															<tr>
																<th>Household Children</th>
																<th>Total</th>
																<th>Patients</th>
																<th>Dentists</th>
																<th>Clinics</th>
																<th>Partners</th>
															</tr>
														</thead>
														<tbody>
															@foreach($household_children as $h_ch)
																@php( $h_childs = $h_ch->household_children !== null ? config('vox.details_fields.household_children.values')[strval($h_ch->household_children)] : '-')
																<tr>
																	<td>{{ $h_childs }}</td>
																	<td>{{ $h_ch->total }}</td>
																	<td>{{ $h_ch->patients }}</td>
																	<td>{{ $h_ch->dentists }}</td>
																	<td>{{ $h_ch->clinics }}</td>
																	<td>{{ $h_ch->partners }}</td>
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

						<div class="row">
							<div class="col-md-12">
								<div class="panel panel-inverse">
									<div class="panel-heading">
										<div class="panel-heading-btn">
											<a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
										</div>
										<h4 class="panel-title">Users Education</h4>
									</div>
									<div class="panel-body">
										<div class="dataTables_wrapper">
											<div class="row">
												<div class="col-sm-12">
													<table class="table table-striped">
														<thead>
															<tr>
																<th>Education</th>
																<th>Total</th>
																<th>Patients</th>
																<th>Dentists</th>
																<th>Clinics</th>
																<th>Partners</th>
															</tr>
														</thead>
														<tbody>
															@foreach($education as $e)
																@php( $ed = !empty($e->education) ? config('vox.details_fields.education.values')[$e->education] : '-')
																<tr>
																	<td>{{ $ed }}</td>
																	<td>{{ $e->total }}</td>
																	<td>{{ $e->patients }}</td>
																	<td>{{ $e->dentists }}</td>
																	<td>{{ $e->clinics }}</td>
																	<td>{{ $e->partners }}</td>
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

						<div class="row">
							<div class="col-md-12">
								<div class="panel panel-inverse">
									<div class="panel-heading">
										<div class="panel-heading-btn">
											<a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
										</div>
										<h4 class="panel-title">Users Еmployment</h4>
									</div>
									<div class="panel-body">
										<div class="dataTables_wrapper">
											<div class="row">
												<div class="col-sm-12">
													<table class="table table-striped">
														<thead>
															<tr>
																<th>Employment</th>
																<th>Total</th>
																<th>Patients</th>
																<th>Dentists</th>
																<th>Clinics</th>
																<th>Partners</th>
															</tr>
														</thead>
														<tbody>
															@foreach($employment as $e)
																@php( $em = !empty($e->employment) ? config('vox.details_fields.employment.values')[$e->employment] : '-')
																<tr>
																	<td>{{ $em }}</td>
																	<td>{{ $e->total }}</td>
																	<td>{{ $e->patients }}</td>
																	<td>{{ $e->dentists }}</td>
																	<td>{{ $e->clinics }}</td>
																	<td>{{ $e->partners }}</td>
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

						<div class="row">
							<div class="col-md-12">
								<div class="panel panel-inverse">
									<div class="panel-heading">
										<div class="panel-heading-btn">
											<a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
										</div>
										<h4 class="panel-title">Users Job</h4>
									</div>
									<div class="panel-body">
										<div class="dataTables_wrapper">
											<div class="row">
												<div class="col-sm-12">
													<table class="table table-striped">
														<thead>
															<tr>
																<th>Job</th>
																<th>Total</th>
																<th>Patients</th>
																<th>Dentists</th>
																<th>Clinics</th>
																<th>Partners</th>
															</tr>
														</thead>
														<tbody>
															@foreach($job as $j)
																@php( $u_job = !empty($j->job) ? config('vox.details_fields.job.values')[$j->job] : '-')
																<tr>
																	<td>{{ $u_job }}</td>
																	<td>{{ $j->total }}</td>
																	<td>{{ $j->patients }}</td>
																	<td>{{ $j->dentists }}</td>
																	<td>{{ $j->clinics }}</td>
																	<td>{{ $j->partners }}</td>
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

						<div class="row">
							<div class="col-md-12">
								<div class="panel panel-inverse">
									<div class="panel-heading">
										<div class="panel-heading-btn">
											<a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
										</div>
										<h4 class="panel-title">Users Job Title</h4>
									</div>
									<div class="panel-body">
										<div class="dataTables_wrapper">
											<div class="row">
												<div class="col-sm-12">
													<table class="table table-striped">
														<thead>
															<tr>
																<th>Job Title</th>
																<th>Total</th>
																<th>Patients</th>
																<th>Dentists</th>
																<th>Clinics</th>
																<th>Partners</th>
															</tr>
														</thead>
														<tbody>
															@foreach($job_title as $j)
																@php( $u_job = !empty($j->job_title) ? config('vox.details_fields.job_title.values')[$j->job_title] : '-')
																<tr>
																	<td>{{ $u_job }}</td>
																	<td>{{ $j->total }}</td>
																	<td>{{ $j->patients }}</td>
																	<td>{{ $j->dentists }}</td>
																	<td>{{ $j->clinics }}</td>
																	<td>{{ $j->partners }}</td>
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

						<div class="row">
							<div class="col-md-12">
								<div class="panel panel-inverse">
									<div class="panel-heading">
										<div class="panel-heading-btn">
											<a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
										</div>
										<h4 class="panel-title">Users Income</h4>
									</div>
									<div class="panel-body">
										<div class="dataTables_wrapper">
											<div class="row">
												<div class="col-sm-12">
													<table class="table table-striped">
														<thead>
															<tr>
																<th>Income</th>
																<th>Total</th>
																<th>Patients</th>
																<th>Dentists</th>
																<th>Clinics</th>
																<th>Partners</th>
															</tr>
														</thead>
														<tbody>
															@foreach($income as $j)
																@php( $u_job = !empty($j->income) ? config('vox.details_fields.income.values')[$j->income] : '-')
																<tr>
																	<td>{{ $u_job }}</td>
																	<td>{{ $j->total }}</td>
																	<td>{{ $j->patients }}</td>
																	<td>{{ $j->dentists }}</td>
																	<td>{{ $j->clinics }}</td>
																	<td>{{ $j->partners }}</td>
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
					@endif
				</div>
			</div>
		</div>
	</div>

@endsection