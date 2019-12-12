@extends('vox')

@section('content')

<div class="container">


	<div class="col-md-3">
		@include('vox.template-parts.profile-menu')
	</div>
	<div class="col-md-9">

		<h2 class="page-title">
			<img src="{{ url('new-vox-img/profile-invite.png') }}" />
			@if($user->is_dentist)
				{{ trans('vox.page.profile.invite.title-dentist') }}
			@else
				{{ trans('vox.page.profile.invite.title-patient') }}
			@endif
		</h2>

<!-- 		@if(!$user->dcn_address)
			<div class="form-horizontal">
                <div class="alert alert-info" id="wallet-needed">
					@if($user->is_dentist)
						{!! nl2br(trans('vox.page.profile.invite.no-address-dentist', [
							'link' => '<a href="'.getLangUrl('profile').'">',
							'endlink' => '</a>',
						])) !!}                		
					@else
						{!! nl2br(trans('vox.page.profile.invite.no-address-patient', [
							'link' => '<a href="'.getLangUrl('profile').'">',
							'endlink' => '</a>',
						])) !!}
					@endif
				</div>
			</div>
		@endif -->


		<div class="form-horizontal">

			<div id="invite-wrapper">

				<h3>
					@if($user->is_dentist)
						{!! nl2br(trans('vox.page.profile.invite.subtitle-dentist')) !!}
					@else
						{!! nl2br(trans('vox.page.profile.invite.subtitle-patient')) !!}
					@endif
				</h3>

				@if($user->is_dentist)
					{!! nl2br(trans('vox.page.profile.invite.hint-dentist')) !!}
				@else
					{!! nl2br(trans('vox.page.profile.invite.hint-patient')) !!}
				@endif

				<b>
					{!! nl2br(trans('vox.page.profile.invite.hint-2')) !!}
    			</b>

    			<div class="steps flex">
    				<div class="step tal">
    					<div class="number">01</div>

    					<p>
    						{{ trans('vox.page.profile.invite.option-link-hint') }}
	    				</p>

						<a href="javascript:;" for="option-link" class="btn btn-inactive{!! $user->dcn_address ? '' : ' no-hover' !!}">
							{{ trans('vox.page.profile.invite.option-link') }}
						</a>
    				</div>
    				<div class="step tac">
    					<div class="number">02</div>
    					
    					<p>
    						{{ trans('vox.page.profile.invite.option-email-hint') }}
    						
    					</p>

						<a href="javascript:;" for="option-email" class="btn btn-inactive{!! $user->dcn_address ? '' : ' no-hover' !!}">
							{{ trans('vox.page.profile.invite.option-email') }}
					  	</a>
    				</div>
    				<div class="step tar">
    					<div class="number">03</div>
    					
    					<p>
    						{{ trans('vox.page.profile.invite.option-contacts-hint') }}
    					</p>

						<a href="javascript:;" for="option-contacts" class="btn btn-inactive{!! $user->dcn_address ? '' : ' no-hover' !!}">
							{{ trans('vox.page.profile.invite.option-contacts') }}
					  	</a>
    				</div>
    			</div>

				<div style="display: none;" id="option-link" class="option-div">

					<div class="form-group clearfix">
					  	<div class="col-md-12">
					  		<p>
                				{!! nl2br(trans('vox.page.profile.invite.instructions-link')) !!}				
                				<br/>						  			
                				<br/>						  						  			
					  		</p>
					  	</div>
					</div>
					<div class="form-group clearfix">
					  	<div class="col-md-10">
		                    {{ Form::text( 'link', getLangUrl('invite/?info='.base64_encode(App\Models\User::encrypt(json_encode(array('user_id' => $user->id, 'hash' => $user->get_invite_token()))))), array('class' => 'form-control select-me' ) ) }}
					  	</div>
					  	<div class="col-md-2">
		                    <a class="btn btn-primary nom copy-invite-link">
		                    	<i class="fa fa-copy"></i> 
		                    	{!! nl2br(trans('vox.page.profile.invite.copy')) !!}
		                    </a>
					  	</div>
					</div>

				</div>

				<div style="display: none;" id="option-email" class="option-div">
					<div class="form-group clearfix">
					  	<div class="col-md-12">
					  		<p>
                				{!! nl2br(trans('vox.page.profile.invite.instructions-email')) !!}
                				<br/>						  			
                				<br/>						  			
					  		</p>
					  	</div>
						{!! Form::open(array('method' => 'post', 'class' => 'form-horizontal clearfix', 'id' => 'invite-patient-form' )) !!}
							<div class="col-md-5">
		                        {{ Form::text( 'name', '', array('class' => 'form-control', 'id' => 'invite-name', 'placeholder' => $user->is_dentist ? trans('vox.page.profile.invite.email-name') : trans('vox.page.profile.invite-dentist.email-name') ) ) }}
							</div>
							<div class="col-md-5">
		                        {{ Form::text( 'email', '', array('class' => 'form-control', 'id' => 'invite-email', 'placeholder' => $user->is_dentist ? trans('vox.page.profile.invite.email-email') : trans('vox.page.profile.invite-dentist.email-email') ) ) }}
							</div>
							<div class="col-md-2">
								{{ Form::submit( trans('vox.page.profile.invite.email-submit'), array('class' => 'form-control btn btn-primary nom' ) ) }}
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
                				{!! nl2br(trans('vox.page.profile.invite.instructions-contacts')) !!}	
                				<br/>						  			
                				<br/>						  									  			
					  		</p>
					  	</div>
					  	<div class="col-md-4">
					  		<a class="btn btn-primary btn-block btn-google btn-share-contacts nom" data-netowrk="google" href="javascript:;">
					  			<i class="fab fa-google"></i>
					  			GMail
					  		</a>
					  	</div>
					  	<div class="col-md-4">
					  		<a class="btn btn-primary btn-block btn-yahoo btn-share-contacts nom" data-netowrk="yahoo" href="javascript:;">
					  			<i class="fab fa-yahoo"></i>
					  			Yahoo
					  		</a>
					  	</div>
					  	<div class="col-md-4">
					  		<a class="btn btn-primary btn-block btn-outlook btn-share-contacts nom" data-netowrk="windows" href="javascript:;">
					  			<i class="fab fa-windows"></i>
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
		                				{!! nl2br(trans('vox.page.profile.'.$current_subpage.'.contacts-choose')) !!}	
		                				<br/>						  			
		                				<br/>						  									  			
							  		</p>
							  	</div>					  		
							  	<div class="col-md-12">
		                        	{{ Form::text( 'search-contacts', '', array('class' => 'form-control', 'id' => 'search-contacts', 'placeholder' => trans('vox.page.profile.'.$current_subpage.'.contacts-search') ) ) }}
		                        	<br/>
							  	</div>
						  		<div class="col-md-12" id="contacts-results-list">

						  		</div>
						  		<div class="col-md-12">
						  			<br/>
							  		<button type="submit" class="btn btn-primary btn-block btn-outlook">
                						{!! nl2br(trans('vox.page.profile.'.$current_subpage.'.contacts-invite-btn')) !!}	
							  		</button>
						  		</div>
						  	</div>
						</div>
					  	<div id="contacts-alert" class="alert" style="display: none;">
					  	</div>
					  	<div id="contacts-error" class="alert alert-info" style="display: none;">
					  		{!! nl2br(trans('vox.page.profile.'.$current_subpage.'.contacts-error')) !!}	
					  	</div>
					  	<div id="contacts-results-empty" class="alert alert-info" style="display: none;">
					  		{!! nl2br(trans('vox.page.profile.'.$current_subpage.'.contacts-empty')) !!}	
					  	</div>
					{!! Form::close() !!}

				</div>

			</div>

		</div>

		@if($user->invites->isNotEmpty())

			<div class="form-horizontal">
				<div class="black-line-title">
		            <h4 class="bold">
		            	{{ trans('vox.page.profile.'.$current_subpage.'.list-title') }}
		            </h4>
		        </div>

	        	<table class="table">
	        		<thead>
	        			<tr>
	            			<th>
	            				{{ trans('vox.page.profile.'.$current_subpage.'.list-date') }}
	            			</th>
	            			<th>
	            				{{ trans('vox.page.profile.'.$current_subpage.'.list-name') }}
	            			</th>
	            			<th>
	            				{{ trans('vox.page.profile.'.$current_subpage.'.list-email') }}
	            			</th>
	            			<th>
	            				{{ trans('vox.page.profile.'.$current_subpage.'.list-status') }}
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
	            							{{ trans('vox.common.yes') }}	            								
	        							</span>
	        						@else
	        							<span class="label label-warning">
	            							{{ trans('vox.common.no') }}	            								
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