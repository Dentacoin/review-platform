@extends('front')

@section('content')

<div class="container">
	<div class="col-md-3">
		@include('front.template-parts.profile-menu')
	</div>
	<div class="col-md-9">

        <div class="panel panel-default">
            <div class="panel-heading">
                <h1 class="panel-title">
                    {{ trans('front.page.profile.'.$current_subpage.'.title') }}
                </h1>
            </div>
            <div class="panel-body">
	    		@if($user->is_verified)
	    			@if($user->register_reward)
		                <p>
		                	{{ trans('front.page.profile.'.$current_subpage.'.hint') }}
		                </p>

						{!! Form::open(array('method' => 'post', 'class' => 'form-horizontal clearfix', 'id' => 'invite-patient-form' )) !!}
							<div class="col-md-5">
		                        {{ Form::text( 'name', '', array('class' => 'form-control', 'id' => 'invite-name', 'placeholder' => trans('front.page.profile.'.$current_subpage.'.name') ) ) }}
							</div>
							<div class="col-md-5">
		                        {{ Form::text( 'email', '', array('class' => 'form-control', 'id' => 'invite-email', 'placeholder' => trans('front.page.profile.'.$current_subpage.'.email') ) ) }}
							</div>
							<div class="col-md-2">
								{{ Form::submit( trans('front.page.profile.'.$current_subpage.'.submit'), array('class' => 'form-control btn btn-primary' ) ) }}
							</div>
						{!! Form::close() !!}
						<div class="alert" id="invite-alert" style="display: none; margin-top: 20px;">
						</div>
	    			@else
	    				<p>
		                	{!! nl2br(trans('front.page.profile.'.$current_subpage.'.claim-reward-first')) !!}
	    				</p>
	    				<a href="{{ getLangUrl('profile/reward') }}" class="btn btn-primary btn-block">
		                	{{ trans('front.page.profile.'.$current_subpage.'.claim-reward-button') }}
	    				</a>
	    			@endif

				@else
                    @include('front.template-parts.verify-email', [
                    	'cta' => trans('front.page.profile.reward.not-verified',[
                    		'email' => '<b>'.$user->email.'</b>'
                    	])
                    ])
				@endif
			</div>
		</div>

		@if($user->invites->isNotEmpty())
	        <div class="panel panel-default">
	            <div class="panel-heading">
	                <h1 class="panel-title">
	                    {{ trans('front.page.profile.'.$current_subpage.'.list-title') }}
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
		            				{{ trans('front.page.profile.'.$current_subpage.'.list-email') }}
		            			</th>
		            			<th>
		            				{{ trans('front.page.profile.'.$current_subpage.'.list-status') }}
		            			</th>
	            			</tr>
	            		</thead>
	            		<tbody>
	            			@foreach( $user->invites as $inv )
	            				<tr>
	            					<td>
	            						{{ $inv->created_at->toDateString() }}
	            					</td>
	            					<td>
	            						{{ $inv->invited_name }}
	            					</td>
	            					<td>
	            						{{ $inv->invited_email }}
	            					</td>
	            					<td>
	            						@if($inv->invited_id)
	            							<span class="label label-success">
		            							{{ trans('front.common.yes') }}	            								
	            							</span>
	            						@else
	            							<span class="label label-warning">
		            							{{ trans('front.common.no') }}	            								
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