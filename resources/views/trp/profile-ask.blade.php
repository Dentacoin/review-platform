@extends('trp')

@section('content')

	<div class="blue-background"></div>

	<div class="container flex break-tablet">
		<div class="col">
			@include('trp.parts.profile-menu')
		</div>
		<div class="flex-3">

			<h2 class="page-title">
				<img src="{{ url('new-vox-img/profile-asks.png') }}" />
	            {{ trans('trp.page.profile.'.$current_subpage.'.title') }}
			</h2>

			@if($user->asks->isNotEmpty())		        
				<div class="form-horizontal">
					<div class="black-line-title">
			            <h4 class="bold">
			            	{{ trans('trp.page.profile.'.$current_subpage.'.title') }}
			            </h4>
			        </div>

		        	<table class="table">
	            		<thead>
	            			<tr>
		            			<th>
		            				{{ trans('trp.page.profile.'.$current_subpage.'.list-date') }}
		            			</th>
		            			<th>
		            				{{ trans('trp.page.profile.'.$current_subpage.'.list-name') }}
		            			</th>
		            			<th>
		            				{{ trans('trp.page.profile.'.$current_subpage.'.list-status') }}
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
	            							<a class="btn btn-primary compact" href="{{ getLangUrl('profile/asks/accept/'.$ask->id) }}">
	            								<i class="fas fa-thumbs-up"></i>
		            							{{ trans('trp.page.profile.'.$current_subpage.'.accept') }}
	            							</a>
	            							<a class="btn btn-inactive compact" href="{{ getLangUrl('profile/asks/deny/'.$ask->id) }}">
	            								<i class="fas fa-thumbs-down"></i>
		            							{{ trans('trp.page.profile.'.$current_subpage.'.deny') }}
	            							</a>
	            						@else
	            							<span class="label label-{{ $ask->status=='yes' ? 'success' : 'warning' }}">
		            							{{ trans('trp.page.profile.'.$current_subpage.'.status-'.$ask->status) }}
	            							</span>
	            						@endif
	            					</td>
	            				</tr>
	            			@endforeach
	            		</tbody>
	            	</table>
				</div>
			@endif


		</div>
	</div>

@endsection