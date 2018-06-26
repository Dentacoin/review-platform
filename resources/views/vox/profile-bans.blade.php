@extends('vox')

@section('content')

	<div class="container">

		@if(!empty($ban_info))
		<br/>
		<br/>
			<div class="alert alert-info">
				@if($ban_info->expires===null)
					{!! nl2br(trans('vox.page.banned.hint')) !!}
				@else
					{!! nl2br(trans('vox.page.banned.hint-temporary', ['expires' => $ban_info->expires->toDateString() ])) !!}
				@endif
			</div>
		@else
			<a href="{{ getLangUrl('/') }}" class="questions-back">
				<i class="fa fa-arrow-left"></i> 
				{{ trans('vox.common.questionnaires') }}
			</a>
		@endif


		<div class="col-md-3">
			@include('vox.template-parts.profile-menu')
		</div>
		<div class="col-md-9">

        	<div class="panel panel-default personal-panel">
	            <div class="panel-heading">
	                <h3 class="panel-title bold">
	                	{{ trans('vox.page.profile.title-bans') }}
	                </h3>
	            </div>
            	<div class="panel-body">
	            	<table class="table">
	            		<thead>
	            			<tr>
		            			<th>
		            				{{ trans('vox.page.profile.'.$current_subpage.'.list-date') }}
		            			</th>
		            			<th>
		            				{{ trans('vox.page.profile.'.$current_subpage.'.list-expires') }}
		            			</th>
		            			<th>
		            				{{ trans('vox.page.profile.'.$current_subpage.'.list-reason') }}
		            			</th>
	            			</tr>
	            		</thead>
	            		<tbody>
	            			@foreach( $user->bans as $ban )
	            				<tr>
	            					<td>
	            						{{ $ban->created_at->toTimeString().' '.$ban->created_at->toDateString() }}
	            					</td>
	            					<td>
	            						@if($ban->expires==null)
	            							{{ trans('vox.page.profile.'.$current_subpage.'.ban-permanent') }}
	            						@elseif($ban->expires->lt( Carbon\Carbon::now() ))
	            							{{ trans('vox.page.profile.'.$current_subpage.'.ban-expired') }}
	            						@else
	            							{{ trans('vox.page.profile.'.$current_subpage.'.ban-until', [
	            								'expires' => $ban->expires->toDateString()
	            							]) }}
	            						@endif
	            					</td>
	            					<td>
	            						{{ trans('vox.page.profile.'.$current_subpage.'.ban-reason-'.$ban->type) }}
	            					</td>
	            				</tr>
	            			@endforeach
	            		</tbody>
	            	</table>
	            </div>
	        </div>
		</div>
	</div>

@endsection