@extends('admin')

@section('content')
    
    <div class="flex" style="justify-content: space-between;">
        <h1 class="page-header">Users with transactions below 7 days after 18-08-2020</h1>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <div class="panel-heading-btn">
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                    </div>
                    <h4 class="panel-title">Users with transactions below 7 days after 18-08-2020</h4>
                </div>
                <div class="panel-body">
            		<div class="panel-body">
                        <div class="table-responsive">
        					<div class="dataTables_wrapper">
							    <div class="row">
							    	<div class="col-sm-12 table-responsive-md">
							    		<table class="table table-striped">
							                <thead>
							                    <tr>
						                           	<th>
						                                User
						                            </th>
						                           	<th>
						                                User Status
						                            </th>
							                    </tr>
							                </thead>
							                <tbody>
							                	@foreach($users as $user)
								                	<tr>
								                		<td><a href="{{ url('cms/users/edit/'.$user) }}" target="_blank">{{ App\Models\User::find($user)->name }}</a></td>
								                		<td>
								                			@if(App\Models\User::find($user)->is_dentist)
																<span class="label label-{{ config('user-statuses-classes')[App\Models\User::find($user)->status] }}">{{ config('user-statuses')[App\Models\User::find($user)->status] }}</span>
															@else
																@if(!empty(App\Models\User::find($user)->patient_status))
																	<span class="label label-{{ config('user-statuses-classes')[App\Models\User::find($user)->patient_status] }}">{{ config('patient-statuses')[App\Models\User::find($user)->patient_status] }}</span>
																@endif
															@endif
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
        </div>
    </div>

@endsection