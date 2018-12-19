@extends('trp')

@section('content')

<div class="blue-background"></div>

<div class="container flex break-tablet">

	<div class="col">
		@include('trp.parts.profile-menu')
	</div>
	<div class="flex-3">

		<h2 class="page-title">
			<img src="{{ url('new-vox-img/profile-invite.png') }}" />
			@if($user->is_dentist)
				{{ trans('trp.page.profile.invite.title-dentist') }}
			@else
				{{ trans('trp.page.profile.invite.title-patient') }}
			@endif
		</h2>

		@if(!$user->dcn_address)
			<div class="form-horizontal">
                <div class="alert alert-info" id="wallet-needed">
					@if($user->is_dentist)
						{!! nl2br(trans('trp.page.profile.invite.no-address-dentist', [
							'link' => '<a href="'.getLangUrl('profile').'">',
							'endlink' => '</a>',
						])) !!}                		
					@else
						{!! nl2br(trans('trp.page.profile.invite.no-address-patient', [
							'link' => '<a href="'.getLangUrl('profile').'">',
							'endlink' => '</a>',
						])) !!}
					@endif
				</div>
			</div>
		@endif


		<div class="form-horizontal">

			<div id="invite-wrapper">

				<h3>
					@if($user->is_dentist)
						{!! nl2br(trans('trp.page.profile.invite.subtitle-dentist')) !!}
					@else
						{!! nl2br(trans('trp.page.profile.invite.subtitle-patient')) !!}
					@endif
				</h3>

				<p>
					@if($user->is_dentist)
						{!! nl2br(trans('trp.page.profile.invite.hint-dentist')) !!}
					@else
						{!! nl2br(trans('trp.page.profile.invite.hint-patient')) !!}
					@endif
				</p>

				@if(!$user->is_dentist)
					<b>
						{!! nl2br(trans('trp.page.profile.invite.hint-2')) !!}
	    			</b>

	    			<div class="steps flex">
	    				<div class="step tal">
	    					<div class="number">01</div>

	    					<p>
	    						{{ trans('trp.page.profile.invite.option-link-hint') }}
		    				</p>

							<a href="javascript:;" for="option-link" class="btn btn-inactive{!! $user->dcn_address ? '' : ' no-hover' !!}">
								{{ trans('trp.page.profile.invite.option-link') }}
							</a>
	    				</div>
	    				<div class="step tac">
	    					<div class="number">02</div>
	    					
	    					<p>
	    						{{ trans('trp.page.profile.invite.option-email-hint') }}
	    						
	    					</p>

							<a href="javascript:;" for="option-email" class="btn btn-inactive{!! $user->dcn_address ? '' : ' no-hover' !!}">
								{{ trans('trp.page.profile.invite.option-email') }}
						  	</a>
	    				</div>
	    				<div class="step tar">
	    					<div class="number">03</div>
	    					
	    					<p>
	    						{{ trans('trp.page.profile.invite.option-contacts-hint') }}
	    					</p>

							<a href="javascript:;" for="option-contacts" class="btn btn-inactive{!! $user->dcn_address ? '' : ' no-hover' !!}">
								{{ trans('trp.page.profile.invite.option-contacts') }}
						  	</a>
	    				</div>
	    			</div>

					<div style="display: none;" id="option-link" class="option-div">

						<div class="form-group clearfix">
					  		<p>
	            				{!! nl2br(trans('trp.page.profile.invite.instructions-link')) !!}				
	            				<br/>						  			
	            				<br/>						  						  			
					  		</p>
						</div>
						<div class="form-group clearfix flex break-tablet">
						  	<div class="flex-5">
			                    {{ Form::text( 'link', getLangUrl('invite/'.$user->id.'/'.$user->get_invite_token()), array('class' => 'form-control select-me' ) ) }}
						  	</div>
						  	<div class="col">
			                    <a class="btn btn-primary nom copy-invite-link">
			                    	<i class="fa fa-copy"></i> 
			                    	{!! nl2br(trans('trp.page.profile.invite.copy')) !!}
			                    </a>
						  	</div>
						</div>

					</div>

				@endif

				<div{!! $user->is_dentist ? '' : ' style="display: none;"' !!} id="option-email" class="option-div">
					<div class="form-group clearfix">
				  		<p>
            				{!! nl2br(trans('trp.page.profile.invite.instructions-email')) !!}
            				<br/>						  			
            				<br/>						  			
				  		</p>
						{!! Form::open(array('method' => 'post', 'class' => ($user->is_dentist ? '' : 'form-horizontal').' clearfix flex break-tablet', 'id' => 'invite-patient-form' )) !!}
							<div class="flex-5">
		                        {{ Form::text( 'name', '', array('class' => 'form-control', 'id' => 'invite-name', 'placeholder' => trans('trp.page.profile.invite.email-name') ) ) }}
							</div>
							<div class="flex-5">
		                        {{ Form::text( 'email', '', array('class' => 'form-control', 'id' => 'invite-email', 'placeholder' => trans('trp.page.profile.invite.email-email') ) ) }}
							</div>
							<div class="flex-2">
								{{ Form::submit( trans('trp.page.profile.invite.email-submit'), array('class' => 'form-control btn btn-primary nom' ) ) }}
							</div>
						{!! Form::close() !!}
						<div class="alert" id="invite-alert" style="display: none; margin-top: 20px;">
						</div>
					</div>

				</div>


				@if(!$user->is_dentist)
					<div style="display: none;" id="option-contacts" class="option-div">

						<div class="form-group clearfix">
					  		<p>
	            				{!! nl2br(trans('trp.page.profile.invite.instructions-contacts')) !!}	
	            				<br/>						  			
	            				<br/>						  									  			
					  		</p>
					  		<div class="flex break-tablet">
							  	<div class="col">
							  		<a class="btn btn-primary btn-block btn-google btn-share-contacts nom" data-netowrk="google" href="javascript:;">
							  			<i class="fab fa-google"></i>
							  			GMail
							  		</a>
							  	</div>
							  	<div class="col">
							  		<a class="btn btn-primary btn-block btn-yahoo btn-share-contacts nom" data-netowrk="yahoo" href="javascript:;">
							  			<i class="fab fa-yahoo"></i>
							  			Yahoo
							  		</a>
							  	</div>
							  	<div class="col">
							  		<a class="btn btn-primary btn-block btn-outlook btn-share-contacts nom" data-netowrk="windows" href="javascript:;">
							  			<i class="fab fa-windows"></i>
							  			Outlook / Hotmail
							  		</a>
							  	</div>
					  		</div>
						</div>
						{!! Form::open(array('method' => 'post', 'class' => 'form-horizontal clearfix', 'id' => 'share-contacts-form' )) !!}
							<input type="hidden" name="is_contacts" value="1" />
							<div class="form-group clearfix">
							  	<div id="contacts-results" style="display: none;">							  		
							  		<p>
		                				{!! nl2br(trans('trp.page.profile.'.$current_subpage.'.contacts-choose')) !!}	
		                				<br/>						  			
		                				<br/>						  									  			
							  		</p>

			                        {{ Form::text( 'search-contacts', '', array('class' => 'form-control', 'id' => 'search-contacts', 'placeholder' => trans('trp.page.profile.'.$current_subpage.'.contacts-search') ) ) }}
			                        <br/>
							  		
							  		<div id="contacts-results-list">

							  		</div>

						  			<br/>
							  		<button type="submit" class="btn btn-primary btn-block btn-outlook">
	            						{!! nl2br(trans('trp.page.profile.'.$current_subpage.'.contacts-invite-btn')) !!}	
							  		</button>
							  	</div>
							</div>
						  	<div id="contacts-alert" class="alert" style="display: none;">
						  	</div>
						  	<div id="contacts-error" class="alert alert-info" style="display: none;">
						  		{!! nl2br(trans('trp.page.profile.'.$current_subpage.'.contacts-error')) !!}	
						  	</div>
						  	<div id="contacts-results-empty" class="alert alert-info" style="display: none;">
						  		{!! nl2br(trans('trp.page.profile.'.$current_subpage.'.contacts-empty')) !!}	
						  	</div>
						{!! Form::close() !!}

					</div>

				@endif
			
			</div>

		</div>

		@if($user->invites->isNotEmpty())

			<div class="form-horizontal">
				<div class="black-line-title">
		            <h4 class="bold">
		            	{{ trans('trp.page.profile.'.$current_subpage.'.list-title') }}
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
	            				{{ trans('trp.page.profile.'.$current_subpage.'.list-email') }}
	            			</th>
	            			<th>
	            				{{ trans('trp.page.profile.'.$current_subpage.'.list-status') }}
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
	            							{{ trans('trp.common.yes') }}	            								
	        							</span>
	        						@else
	        							<span class="label label-warning">
	            							{{ trans('trp.common.no') }}	            								
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

<script type="text/javascript">
	var socials_redirect_url = '{{ url('socials.html') }}';
</script>

@endsection