@extends('front')

@section('content')

<div class="container">
	<div class="col-md-3">
		@include('front.template-parts.profile-menu')
	</div>
	<div class="col-md-9">

		@if($user->asks->isNotEmpty())
	        <div class="panel panel-default">
	            <div class="panel-heading">
	                <h1 class="panel-title">
	                    {{ trans('front.page.profile.'.$current_subpage.'.title') }}
	                </h1>
	            </div>
	            <div class="panel-body">
	            	<table class="table">
	            		<thead>
	            			<tr>
		            			<th>
		            				{{ trans('front.page.profile.'.$current_subpage.'.list-date') }}
		            			</th>
		            			<th>
		            				{{ trans('front.page.profile.'.$current_subpage.'.list-name') }}
		            			</th>
		            			<th>
		            				{{ trans('front.page.profile.'.$current_subpage.'.list-status') }}
		            			</th>
	            			</tr>
	            		</thead>
	            		<tbody>
	            			@foreach( $user->asks as $ask )
	            				<tr>
	            					<td>
	            						{{ $ask->created_at->toDateString() }}
	            					</td>
	            					<td>
	            						{{ $ask->user->name }}
	            					</td>
	            					<td>
	            						@if($ask->status=='waiting')
	            							<a class="btn btn-primary" href="{{ getLangUrl('profile/asks/accept/'.$ask->id) }}">
	            								<i class="fa fa-check"></i>
		            							{{ trans('front.page.profile.'.$current_subpage.'.accept') }}
	            							</a>
	            							<a class="btn btn-default" href="{{ getLangUrl('profile/asks/deny/'.$ask->id) }}">
	            								<i class="fa fa-remove"></i>
		            							{{ trans('front.page.profile.'.$current_subpage.'.deny') }}
	            							</a>
	            						@else
	            							<span class="label label-{{ $ask->status=='yes' ? 'success' : 'warning' }}">
		            							{{ trans('front.page.profile.'.$current_subpage.'.status-'.$ask->status) }}
	            							</span>
	            						@endif
	            					</td>
	            				</tr>
	            			@endforeach
	            		</tbody>
	            	</table>
				</div>
			</div>
		@endif


	</div>
</div>

@endsection