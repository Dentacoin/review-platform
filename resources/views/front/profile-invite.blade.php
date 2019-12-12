@extends('front')

@section('content')

<div class="container">
	<div class="col-md-3">
		@include('front.template-parts.profile-menu')
	</div>
	<div class="col-md-9">


		@if( $user->status!='approved' && $user->status!='added_approved' && $user->status!='admin_imported' )
			<div class="alert alert-info">
				{{ trans('front.page.profile.'.$current_subpage.'.wait-for-approval') }}
			</div>
		@elseif( $user->loggedFromBadIp() )
			<div class="alert alert-info">
				{!! trans('front.page.profile.'.$current_subpage.'.bad-ip') !!}
			</div>
		@else
	        <div class="panel panel-default">
	            <div class="panel-heading">
	                <h1 class="panel-title">
		    			@if($user->is_dentist)
	                    	{{ trans('front.page.profile.'.$current_subpage.'.title') }}
	                    @else
	                    	{{ trans('front.page.profile.'.$current_subpage.'.title-patient') }}
	                    @endif
	                </h1>
	            </div>
	            <div class="panel-body">
	    			@if($user->dcn_address)
		                <p>
	    					@if($user->is_dentist)
		                		{!! nl2br(trans('front.page.profile.'.$current_subpage.'.hint')) !!}
		                	@else
		                		{!! nl2br(trans('front.page.profile.'.$current_subpage.'.hint-patient')) !!}
		                	@endif
		                </p>
		                <p>
	    					{!! nl2br(trans('front.page.profile.'.$current_subpage.'.info')) !!}
		                </p>


						<div class="form-group">
							<div class="col-md-12">
							  	<div class="btn-group btn-group-justified">
									<label for="option-link" class="btn btn-default">
										{{ trans('front.page.profile.'.$current_subpage.'.option-link') }}
									</label>
									<label for="option-email" class="btn btn-default">
										{{ trans('front.page.profile.'.$current_subpage.'.option-email') }}
								  	</label>
									<label for="option-contacts" class="btn btn-default">
										{{ trans('front.page.profile.'.$current_subpage.'.option-contacts') }}
								  	</label>
								</div>
							</div>
						</div>

						<br/>
						<br/>

						<div style="display: none;" id="option-link" class="option-div">

							<div class="form-group clearfix">
							  	<div class="col-md-12">
							  		<p>
		                				{!! nl2br(trans('front.page.profile.'.$current_subpage.'.instructions-link')) !!}				
		                				<br/>						  			
		                				<br/>						  						  			
							  		</p>
							  	</div>
							  	<div class="col-md-12">
				                    {{ Form::text( 'link', getLangUrl('invite/?info='.base64_encode(App\Models\User::encrypt(json_encode(array('user_id' => $user->id, 'hash' => $user->get_invite_token()))))), array('class' => 'form-control select-me' ) ) }}
							  	</div>
							</div>

						</div>

						<div style="display: none;" id="option-email" class="option-div">
							<div class="form-group clearfix">
							  	<div class="col-md-12">
							  		<p>
		                				{!! nl2br(trans('front.page.profile.'.$current_subpage.'.instructions-email')) !!}
		                				<br/>						  			
		                				<br/>						  			
							  		</p>
							  	</div>
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
							</div>

						</div>

						<div style="display: none;" id="option-contacts" class="option-div">

							<div class="form-group clearfix">
							  	<div class="col-md-12">
							  		<p>
		                				{!! nl2br(trans('front.page.profile.'.$current_subpage.'.instructions-contacts')) !!}	
		                				<br/>						  			
		                				<br/>						  									  			
							  		</p>
							  	</div>
							  	<div class="col-md-4">
							  		<a class="btn btn-primary btn-block btn-google btn-share-contacts" data-netowrk="google" href="javascript:;">
							  			<i class="fa fa-google"></i>
							  			GMail
							  		</a>
							  	</div>
							  	<div class="col-md-4">
							  		<a class="btn btn-primary btn-block btn-yahoo btn-share-contacts" data-netowrk="yahoo" href="javascript:;">
							  			<i class="fa fa-yahoo"></i>
							  			Yahoo
							  		</a>
							  	</div>
							  	<div class="col-md-4">
							  		<a class="btn btn-primary btn-block btn-outlook btn-share-contacts" data-netowrk="windows" href="javascript:;">
							  			<i class="fa fa-windows"></i>
							  			Outlook / Hotmail
							  		</a>
							  	</div>
							</div>
							{!! Form::open(array('method' => 'post', 'class' => 'form-horizontal clearfix', 'id' => 'share-contacts-form' )) !!}
								<input type="hidden" name="is_contacts" value="1" />
								<div class="form-group clearfix">
								  	<div id="contacts-results" style="display: none;">							  		
									  	<div class="col-md-12">
									  		<p>
				                				{!! nl2br(trans('front.page.profile.'.$current_subpage.'.contacts-choose')) !!}	
				                				<br/>						  			
				                				<br/>						  									  			
									  		</p>
									  	</div>					  		
									  	<div class="col-md-12">
				                        	{{ Form::text( 'search-contacts', '', array('class' => 'form-control', 'id' => 'search-contacts', 'placeholder' => trans('front.page.profile.'.$current_subpage.'.search-contacts') ) ) }}
				                        	<br/>
									  	</div>
								  		<div class="col-md-12" id="contacts-results-list">

								  		</div>
								  		<div class="col-md-12">
								  			<br/>
									  		<button type="submit" class="btn btn-primary btn-block btn-outlook">
		                						{!! nl2br(trans('front.page.profile.'.$current_subpage.'.contacts-invite-btn')) !!}	
									  		</a>
								  		</div>
								  	</div>
								</div>
							  	<div id="contacts-alert" class="alert" style="display: none;">
							  	</div>
							  	<div id="contacts-error" class="alert alert-info" style="display: none;">
							  		{!! nl2br(trans('front.page.profile.'.$current_subpage.'.contacts-error')) !!}	
							  	</div>
							  	<div id="contacts-results-empty" class="alert alert-info" style="display: none;">
							  		{!! nl2br(trans('front.page.profile.'.$current_subpage.'.contacts-empty')) !!}	
							  	</div>
							{!! Form::close() !!}

						</div>

	    			@else
	    				<p>
	    					@if($user->is_dentist)
		                		{!! nl2br(trans('front.page.profile.'.$current_subpage.'.claim-reward-first')) !!}
	    					@else
		                		{!! nl2br(trans('front.page.profile.'.$current_subpage.'.claim-reward-first-patient')) !!}
	    					@endif
	    				</p>
	    				<a href="{{ getLangUrl('profile/reward') }}" class="btn btn-primary btn-block">
		                	{{ trans('front.page.profile.'.$current_subpage.'.claim-reward-button') }}
	    				</a>
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
		@endif



	</div>
</div>

<script type="text/javascript">
	var socials_redirect_url = '{{ url('socials.html') }}';
</script>

@endsection