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
		  						
		  	@include('front.errors')

			@if($user->loggedFromBadIp())

				<div class="alert alert-warning">
					{{ trans('vox.page.profile.wallet-bad-ip') }}
					<a id="bad-ip-appeal" href="{{ getLangUrl('appeal') }}"> {{ trans('vox.page.profile.wallet-bad-ip-button') }} </a>
				</div>

			@else
	        	@if($user->vox_address)
				  	
				  	<div class="panel panel-default personal-panel">
			            <div class="panel-heading">
			                <h3 class="panel-title bold">
		                		{{ trans('vox.page.profile.wallet-address-balance') }}
			                </h3>
			            </div>
			            <div class="panel-body">

				    		<p class="personal-description">
				    			{!! nl2br(trans('vox.page.profile.wallet-hint')) !!}
				    		</p>

				    		<input type="hidden" id="balance-address" value="{{ getLangUrl('profile/balance') }}" />

				    		<form class="form-horizontal" method="post" id="balance-form">
	                    		{!! csrf_field() !!}

					            <div class="form-group">
					                <label class="col-md-3 control-label">
					                	{{ trans('vox.page.profile.wallet-address') }}
				                    </label>
					                <div class="col-md-9">
					                    <input class="form-control" id="vox-address" name="vox-address" type="text" value="{{ $user->vox_address }}">
					                </div>
					            </div>

					            <div class="form-group">
					                <label class="col-md-3 control-label">
					                	{{ trans('vox.page.profile.wallet-balance') }}
				                    </label>
					                <div class="col-md-9">
					                    <input class="form-control" id="my-balance" name="my-balance" type="text" value="" disabled="disabled">
					                </div>
					            </div>
								<div class="form-group">
									<div class="col-md-12">
				                        <button type="submit" name="update" class="btn btn-primary form-control">
				                        	{{ trans('vox.page.profile.wallet-submit') }}
				                        </button>
									</div>
								</div>

				            </form>

				  		</div>
				  	</div>


					<div class="panel panel-default personal-panel">
					    <div class="panel-heading">
					        <h3 class="panel-title bold">
				                {{ trans('vox.page.profile.wallet-withdraw-title') }}
					        </h3>
					    </div>
					    <div class="panel-body panel-body-wallet">
					    	<div id="has-wallet">

					    		@if(!$user->civic_id)
						    		<p class="personal-description">
										{!! nl2br(trans('vox.page.'.$current_page.'.civic-hint')) !!}
				                	</p>
				                	<p class="personal-description">
				                		{!! nl2br(trans('vox.page.'.$current_page.'.civic-buttons')) !!}
				                	</p>
				                	<p  class="personal-description">
				                		<a href="https://play.google.com/store/apps/details?id=com.civic.sip" target="_blank" class="civic-download civic-android"></a>
				                		<a href="https://itunes.apple.com/us/app/civic-secure-identity/id1141956958?mt=8" target="_blank" class="civic-download civic-ios"></a>
				                	</p>
				                	<p class="personal-description">
				                		{!! nl2br(trans('vox.page.'.$current_page.'.civic-login')) !!}
				                	</p>

				                	<p class="tac">
										<button id="signupButton" class="civic-button-a medium" type="button">
											<span style="color: white;">{!! nl2br(trans('vox.page.'.$current_page.'.civic-button')) !!}</span>
										</button>
									</p>

									<div id="civic-cancelled" class="alert alert-info" style="display: none;">
										{!! nl2br(trans('vox.page.'.$current_page.'.civic-cancelled')) !!}
									</div>
									<div id="civic-error" class="alert alert-warning" style="display: none;">
										{!! nl2br(trans('vox.page.'.$current_page.'.civic-error')) !!}
									</div>
									<div id="civic-weak" class="alert alert-warning" style="display: none;">
										{!! nl2br(trans('vox.page.'.$current_page.'.civic-weak')) !!}
									</div>
									<div id="civic-wait" class="alert alert-info" style="display: none;">
										{!! nl2br(trans('vox.page.'.$current_page.'.civic-wait')) !!}
									</div>
									<div id="civic-duplicate" class="alert alert-warning" style="display: none;">
										{!! nl2br(trans('vox.page.'.$current_page.'.civic-duplicate')) !!}
									</div>
									<input type="hidden" id="jwtAddress" value="{{ getLangUrl('profile/jwt') }}" />
					    		@else
						    		<p class="personal-description">
						    			{!! nl2br(trans('vox.page.profile.wallet-withdraw-hint',[
						    				'balance' => '<b>'.$user->getVoxBalance().'</b>',
						    				'minimum' => '<b>'.env('VOX_MIN_WITHDRAW').'</b>'
						    			])) !!}
						    		</p>

						    		<form id="withdraw-form" class="form-horizontal" method="post" action="{{ getLangurl('profile/withdraw') }}">
		                    			{!! csrf_field() !!}

							            <div class="form-group">
							                <label class="col-md-3 control-label">
				                				{{ trans('vox.page.profile.wallet-withdraw-amount') }}
						                    </label>
							                <div class="col-md-9">
							                    <input class="form-control" id="wallet-amount" name="wallet-amount" type="text" value="">
							                </div>
							            </div>
							            @if($user->isGasExpensive())
								            <div class="alert alert-warning">
								            	{{ trans('vox.page.profile.wallet-withdraw-gas') }}
					                        </div>
					                    @endif
										<div class="form-group">
											<div class="col-md-12">
						                        <button type="submit" name="update" class="btn btn-primary form-control" data-loading="{{ trans('vox.common.loading') }}">
						                        	{{ trans('vox.page.profile.wallet-withdraw-submit') }}
						                        </button>
											</div>
										</div>

				                        <div class="alert alert-success" style="display: none;" id="withdraw-pending">
				                        	{{ trans('vox.page.profile.wallet-withdraw-pending') }}
				                        </div>
				                        <div class="alert alert-success" style="display: none;" id="withdraw-success">
				                        	{{ trans('vox.page.profile.wallet-withdraw-success') }}
				                        	<a target="_blank">
				                        	</a>
				                        </div>
				                        <div class="alert alert-warning" style="display: none;" id="withdraw-error">
				                        	{{ trans('vox.page.profile.wallet-withdraw-error') }}
				                        	<div id="withdraw-reason">
				                        	</div>
				                        </div>
					            	</form>
					    		@endif


					    	</div>
					    </div>
					</div>
	        	@else
					<div class="panel panel-default personal-panel">
					    <div class="panel-heading">
					        <h3 class="panel-title bold">
				                {{ trans('vox.page.profile.wallet-new-address-title') }}
					        </h3>
					    </div>
					    <div class="panel-body panel-body-wallet">
					    	<div id="has-wallet">
					    		<p class="personal-description">
					    			{!! nl2br(trans('vox.page.profile.wallet-new-address-hint',[
					    				'link' => '<a target="_blank" href="'.url('DentavoxMetamask.pdf').'">',
					    				'endlink' => '</a>',
					    			])) !!}
					    		</p>

					    		<form class="form-horizontal" method="post">
		                    		{!! csrf_field() !!}

						            <div class="form-group">
						                <label class="col-md-3 control-label">
						                	{{ trans('vox.page.profile.wallet-new-address-address') }}
					                    </label>
						                <div class="col-md-9">
						                    <input class="form-control" id="vox-address" name="vox-address" type="text" value="">
						                </div>
						            </div>
									<div class="form-group">
										<div class="col-md-12">
					                        <button type="submit" name="update" class="btn btn-primary form-control">
					                        	{{ trans('vox.page.profile.wallet-new-address-submit') }}
					                        </button>
										</div>
									</div>

					            </form>
		  						
		  						@include('front.errors')

					    	</div>
					    </div>
					</div>
	        	@endif

			@endif
		</div>
	</div>

@endsection