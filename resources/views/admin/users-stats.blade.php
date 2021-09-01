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
				<h4 class="panel-title">Answered questions</h4>
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
				                    	@foreach($user_types as $key => $type)
					                    	<th style="width: 33%;">
					                    		{{ $type->is_dentist == 1 ? 'Dentists Total' : 'Patients' }}
					                    	</th>
					                    @endforeach
					                    <th style="width: 33%;">
					                    	Dentists Partners
					                    </th>
				                    </tr>
				                </thead>
				                <tbody>
				                	<tr>
					                	@foreach($user_types as $key => $type)
					                    	<td style="width: 33%;">{{ $type->total }}</td>
					                    @endforeach
					                    @foreach($dentist_partners as $key => $partner)
					                    	<td style="width: 33%;">{{ $partner->total }}</td>
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
				                    	<th style="width: 33%;">Country</th>
				                    	<th style="width: 33%;">Users in this country</th>
					                    <th style="width: 33%;"></th>
				                    </tr>
				                </thead>
				                <tbody>
					                @foreach($users_country as $key => $c)

				                		<tr>
					                    	<td style="width: 33%;">
					                    		{{ !empty($c->country_id) ? $c->country->name : '-' }}
					                    	</td>
					                    	<td style="width: 33%;">
					                    		{{ $c->total }}
					                    	</td>
					                    	<td style="width: 33%;"></td>
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

@endsection