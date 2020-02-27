@extends('front')

@section('content')

<div class="container">
	<div class="col-md-3">
		@include('front.template-parts.profile-menu')
	</div>
	<div class="col-md-9">
	@include('front.errors')

		@if( $user->status!='approved' && $user->status!='added_by_clinic_claimed' && $user->status!='test' )
			<div class="alert alert-info">
				{{ trans('front.page.profile.reward.wait-for-approval') }}
			</div>
		@elseif( $user->loggedFromBadIp() )
			<div class="alert alert-info">
				{!! trans('front.page.profile.'.$current_subpage.'.bad-ip') !!}
			</div>
		@else
			@if(!$user->dcn_address)
				<div class="panel panel-default" id="reward-widget">
				    <div class="panel-heading">
				        <h1 class="panel-title">
				            {{ trans('front.page.profile.reward.transfer-title') }}
				        </h1>
				    </div>
				    <div class="panel-body panel-body-reward">
			    		<p>
			    			{!! nl2br(trans('front.page.profile.reward.transfer-hint')) !!}<br/>
			    		</p>

			    		<form class="form-horizontal" id="reward-form" method="POST" action="{{ getLangUrl('profile/reward') }}">
			    			{{ Form::token() }}

				            <div class="form-group">
				                <label class="col-md-3 control-label">{{ trans('front.page.profile.reward.transfer-address') }}</label>
				                <div class="col-md-9">
				                    {{ Form::text( 'reward-address', '', array('class' => 'form-control fill-address', 'id' => 'transfer-reward-address' )) }}
				                </div>
				            </div>
				            <div class="form-group">
				                <div class="col-md-12">
				                    <button type="submit" class="btn btn-primary btn-block" data-loading="{{ trans('front.common.loading') }}">
				                    	{{ trans('front.page.profile.reward.transfer-submit') }}
				                    </button>
				                </div>
				            </div>

			            </form>

		                <div class="alert alert-warning" id="reward-invalid" style="display: none;">
		                	{{ trans('front.page.profile.reward.transfer-invalid') }}
		                </div>
		                <div class="alert alert-warning" id="reward-error" style="display: none;">
		                </div>
				    </div>
				</div>
			@else

				<div class="panel panel-default" id="withdraw-widget">
				    <div class="panel-heading">
				        <h1 class="panel-title">
				            {{ trans('front.page.profile.wallet.withdraw-title') }}
				        </h1>
				    </div>
				    <div class="panel-body panel-body-reward">

			    		@if(!$user->civic_kyc)
			    			<div id="civic-widget">
					    		<p class="personal-description">
									{!! nl2br(trans('vox.page.'.$current_page.'.home.civic-hint')) !!}
			                	</p>
			                	<p class="personal-description">
			                		{!! nl2br(trans('vox.page.'.$current_page.'.home.civic-buttons')) !!}
			                	</p>
			                	<p  class="personal-description">
			                		<a href="https://play.google.com/store/apps/details?id=com.civic.sip" target="_blank" class="civic-download civic-android"></a>
			                		<a href="https://itunes.apple.com/us/app/civic-secure-identity/id1141956958?mt=8" target="_blank" class="civic-download civic-ios"></a>
			                	</p>
			                	<p class="personal-description">
			                		{!! nl2br(trans('vox.page.'.$current_page.'.home.civic-login')) !!}
			                	</p>

			                	<p class="tac">
									<button id="signupButton" class="civic-button-a medium" type="button">
										<span style="color: white;">{!! nl2br(trans('vox.page.'.$current_page.'.home.civic-button')) !!}</span>
									</button>
								</p>

								<div id="civic-cancelled" class="alert alert-info" style="display: none;">
									{!! nl2br(trans('vox.page.'.$current_page.'.home.civic-cancelled')) !!}
								</div>
								<div id="civic-error" class="alert alert-warning" style="display: none;">
									{!! nl2br(trans('vox.page.'.$current_page.'.home.civic-error')) !!}
								</div>
								<div id="civic-weak" class="alert alert-warning" style="display: none;">
									{!! nl2br(trans('vox.page.'.$current_page.'.home.civic-weak')) !!}
								</div>
								<div id="civic-wait" class="alert alert-info" style="display: none;">
									{!! nl2br(trans('vox.page.'.$current_page.'.home.civic-wait')) !!}
								</div>
								<div id="civic-duplicate" class="alert alert-warning" style="display: none;">
									{!! nl2br(trans('vox.page.'.$current_page.'.home.civic-duplicate')) !!}
								</div>
								<input type="hidden" id="jwtAddress" value="{{ getLangUrl('profile/jwt') }}" />
							</div>
			    		@else
					    		<p>
					    			{!! nl2br(trans('front.page.profile.wallet.withdraw-hint')) !!}<br/>
					    		</p>

					    		<form class="form-horizontal" id="withdraw-form" method="POST" action="{{ getLangUrl('profile/withdraw') }}">
					    			{{ Form::token() }}

						            <div class="form-group">
						                <label class="col-md-3 control-label">{{ trans('front.page.profile.wallet.withdraw-address') }}</label>
						                <div class="col-md-9">
						                    {{ Form::text( 'withdraw-address', $user->dcn_address, array('class' => 'form-control', 'disabled' => 'disabled' )) }}
						                </div>
						            </div>
						            <div class="form-group">
						                <label class="col-md-3 control-label">{{ trans('front.page.profile.wallet.withdraw-balance') }}</label>
						                <div class="col-md-9">
						                    {{ Form::text( 'withdraw-balance', $user->getTotalBalance('trp'), array('class' => 'form-control', 'disabled' => 'disabled' )) }}
						                </div>
						            </div>
						            <div class="form-group">
						                <label class="col-md-3 control-label">{{ trans('front.page.profile.wallet.withdraw-amount') }}</label>
						                <div class="col-md-9">
						                    {{ Form::text( 'withdraw-amount', $user->getTotalBalance('trp'), array('class' => 'form-control', 'id' => 'transfer-withdraw-amount' )) }}
						                </div>
						            </div>
						            @if($user->isGasExpensive())
							            <div class="alert alert-warning">
							            	{{ trans('vox.page.profile.wallet-withdraw-gas') }}
				                        </div>
				                    @endif
						            <div class="form-group">
						                <div class="col-md-12">
						                    <button type="submit" class="btn btn-primary btn-block" data-loading="{{ trans('front.common.loading') }}">
						                    	{{ trans('front.page.profile.wallet.withdraw-submit') }}
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

				@include('front.template-parts.profile-wallet')

				
				<div class="panel panel-default" id="transfer-widget" style="display: none;">
				    <div class="panel-heading">
				        <h1 class="panel-title">
				            {{ trans('front.page.profile.wallet.transfer-title') }}
				        </h1>
				    </div>
				    <div class="panel-body panel-body-wallet">
			    		<p>
			    			{!! nl2br(trans('front.page.profile.wallet.transfer-hint', ['address' => '<b class="fill-address"></b>', 'balance' => '<b class="fill-balance"></b>'])) !!}<br/>
			    		</p>

			    		<form class="form-horizontal" id="transfer-form">

				            <div class="form-group">
				                <label class="col-md-3 control-label">{{ trans('front.page.profile.wallet.transfer-address') }}</label>
				                <div class="col-md-9">
				                    {{ Form::text( 'wallet-address', '', array('class' => 'form-control', 'id' => 'transfer-wallet-address' )) }}
				                </div>
				            </div>

				            <div class="form-group">
				                <label class="col-md-3 control-label">{{ trans('front.page.profile.wallet.transfer-amount') }}</label>
				                <div class="col-md-9">
				                    {{ Form::text( 'wallet-amount', '', array('class' => 'form-control', 'id' => 'transfer-wallet-amount' )) }}
				                </div>
				            </div>
				            <div class="form-group">
				                <div class="col-md-12">
				                    <button type="submit" class="btn btn-primary btn-block">
				                    	{{ trans('front.page.profile.wallet.transfer-submit') }}
				                    </button>
				                    <div class="alert alert-success" id="transfer-succcess" style="display: none;">
				                    	{{ trans('front.page.profile.wallet.transfer-succcess') }}
				                    </div>
				                    <div class="alert alert-warning" id="transfer-invalid" style="display: none;">
				                    	{{ trans('front.page.profile.wallet.transfer-invalid') }}
				                    </div>
				                    <div class="alert alert-warning" id="transfer-error" style="display: none;">
				                    	{{ trans('front.page.profile.wallet.transfer-error') }}<br/>
				                    	<span id="transfer-reason">
				                    	</span>
				                    </div>
				                </div>
				            </div>

			            </form>

				    </div>
				</div>
			@endif
		@endif

	</div>
</div>

@endsection