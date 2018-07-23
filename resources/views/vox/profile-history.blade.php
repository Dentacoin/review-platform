@extends('vox')

@section('content')

	<div class="container">

		<a href="{{ getLangUrl('/') }}" class="questions-back">
			<i class="fa fa-arrow-left"></i> 
			{{ trans('vox.common.questionnaires') }}
		</a>

		<div class="col-md-3">
			@include('vox.template-parts.profile-menu')
		</div>
		<div class="col-md-9">

        	<div class="panel panel-default personal-panel">
	            <div class="panel-heading">
	                <h3 class="panel-title bold">
	                	{{ trans('vox.page.profile.title-history') }}
	                </h3>
	            </div>
            	<div class="panel-body">
                	@if($history->isEmpty())
	            		<div class="alert alert-info">
	                    	{{ trans('vox.page.profile.empty-history') }}
	            		</div>
	            	@else
		            	<table class="table">
		            		<thead>
		            			<tr>
			            			<th>
			            				{{ trans('front.page.profile.'.$current_subpage.'.list-date') }}
			            			</th>
			            			<th>
			            				{{ trans('front.page.profile.'.$current_subpage.'.list-amount') }}
			            			</th>
			            			<th>
			            				{{ trans('front.page.profile.'.$current_subpage.'.list-address') }}
			            			</th>
			            			<th>
			            				{{ trans('front.page.profile.'.$current_subpage.'.list-status') }}
			            			</th>
		            			</tr>
		            		</thead>
		            		<tbody>
		            			@foreach( $history as $trans )
		            				<tr>
		            					<td>
		            						{{ $trans->created_at->toDateString() }}
		            					</td>
		            					<td>
		            						{{ $trans->amount }} DCN
		            					</td>
		            					<td>
		            						<div class="vox-address">{{ $trans->address }}</div>
		            					</td>
		            					<td>
		            						@if($trans->status=='new')
		            							{{ trans('front.page.profile.'.$current_subpage.'.status-new') }}
		            						@elseif($trans->status=='failed')
		            							{{ trans('front.page.profile.'.$current_subpage.'.status-failed') }}
		            						@elseif($trans->status=='unconfirmed')
		            							<a href="https://etherscan.io/tx/{{ $trans->tx_hash }}" target="_blank">
		            								{{ trans('front.page.profile.'.$current_subpage.'.status-unconfirmed') }}
		            								<i class="fa fa-share-square-o"></i>
		            							</a>
		            						@elseif($trans->status=='completed')
		            							<a href="https://etherscan.io/tx/{{ $trans->tx_hash }}" target="_blank">
		            								{{ trans('front.page.profile.'.$current_subpage.'.status-completed') }}		            								
		            								<i class="fa fa-share-square-o"></i>
		            							</a>
		            						@endif
		            					</td>
		            				</tr>
		            			@endforeach
		            		</tbody>
		            	</table>
	            	@endif
	            </div>
	        </div>
		</div>
	</div>

@endsection