@extends('admin')

@section('content')
    
    <div class="flex" style="justify-content: space-between;">
        <h1 class="page-header">Transactions with amount above the user balance (last 30 days)</h1>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <div class="panel-heading-btn">
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                    </div>
                    <h4 class="panel-title">Transactions with amount above the user balance (last 30 days)</h4>
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
						                            <th><span class="pull-right">Checked</span></th>
							                    </tr>
							                </thead>
							                <tbody>
							                	@foreach($scammers as $scam)
								                	<tr style="{{ $scam->checked ? 'opacity: 0.5;' : '' }}">
								                		<td style="width: 40%;"><a href="{{ url('cms/users/edit/'.$scam->user_id) }}" target="_blank">{{ $scam->user->name }}</a></td>
								                		<td style="width: 40%;">
								                			@if($scam->user->is_dentist)
																<span class="label label-{{ config('user-statuses-classes')[$scam->user->status] }}">{{ config('user-statuses')[$scam->user->status] }}</span>
															@else
																@if(!empty($scam->user->patient_status))
																	<span class="label label-{{ config('user-statuses-classes')[$scam->user->patient_status] }}">{{ config('patient-statuses')[$scam->user->patient_status] }}</span>
																@endif
															@endif
								                		</td>
								                		<td style="width: 100%;">
								                			@if(!$scam->checked)
								                				<a href="{{ url('cms/transactions/scammers-balance/'.$scam->id) }}" class="btn btn-info pull-right">Checked</a>
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