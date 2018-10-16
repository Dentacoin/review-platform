@extends('vox')

@section('content')

	<div class="container">

		<div class="col-md-3">
			@include('vox.template-parts.profile-menu')
		</div>

		<div class="col-md-9">
			<h2 class="page-title">
				<img src="{{ url('new-vox-img/profile-wallet.png') }}" />
				My Wallet
			</h2>
		  						
		  	@include('front.errors')

			@if($user->loggedFromBadIp())

				<div class="alert alert-warning">
					{{ trans('vox.page.profile.wallet-bad-ip') }}
					<a id="bad-ip-appeal" href="{{ getLangUrl('appeal') }}"> {{ trans('vox.page.profile.wallet-bad-ip-button') }} </a>
				</div>

			@else

				<div class="form-horizontal black-line-title">
	                <h4 class="bold">
	                	My DCN Address
	                </h4>

                	<p class="personal-description">
                		You can have only one withdraw address. Use the field below to modify it.
                	</p>

					<form class="form-horizontal" method="post" id="balance-form">
		                {!! csrf_field() !!}
			            <div class="form-group">
			                <label class="col-md-2 control-label">
			                	{{ trans('vox.page.profile.wallet-address') }}
		                    </label>
			                <div class="col-md-8">
			                    <input class="form-control" id="vox-address" name="vox-address" type="text" value="{{ $user->vox_address }}">
			                </div>
			                <div class="col-md-2">
		                        <button type="submit" name="update" class="btn btn-primary form-control nom">
		                        	Apply
		                        </button>
			                </div>
			            </div>
					</form>
				</div>

	        	@if($user->vox_address)
					<div class="form-horizontal black-line-title">
		                <h4 class="bold">
		                	Withdraw DCN
		                </h4>

			    		@if(!$user->civic_id)
				    		<p class="personal-description">
								{!! nl2br(trans('vox.page.'.$current_page.'.civic-hint')) !!}
								<br/>
								<br/>
		                	</p>
		                	<p class="personal-description">
		                		{!! nl2br(trans('vox.page.'.$current_page.'.civic-buttons')) !!}
								<br/>
								<br/>
		                	</p>
		                	<p  class="personal-description">
		                		<a href="https://play.google.com/store/apps/details?id=com.civic.sip" target="_blank" class="civic-download civic-android"></a>
		                		<a href="https://itunes.apple.com/us/app/civic-secure-identity/id1141956958?mt=8" target="_blank" class="civic-download civic-ios"></a>
								<br/>
								<br/>
		                	</p>
		                	<p class="personal-description">
		                		{!! nl2br(trans('vox.page.'.$current_page.'.civic-login')) !!}
								<br/>
								<br/>
		                	</p>

							<button id="signupButton" class="civic-button-a medium" type="button">
								<span style="color: white;">{!! nl2br(trans('vox.page.'.$current_page.'.civic-button')) !!}</span>
							</button>

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
				    			<br/>
				    			<br/>
				    		</p>

				    		<form id="withdraw-form" method="post" action="{{ getLangurl('profile/withdraw') }}">
	                			{!! csrf_field() !!}

					            <div class="form-group">
					                <div class="col-md-9">
					                    <input class="form-control" id="wallet-amount" name="wallet-amount" type="text" value="" placeholder="{{ trans('vox.page.profile.wallet-withdraw-amount') }}">
					                </div>
									<div class="col-md-3">
				                        <button type="submit" name="update" class="btn btn-primary form-control nom" data-loading="{{ trans('vox.common.loading') }}">
				                        	{{ trans('vox.page.profile.wallet-withdraw-submit') }}
				                        </button>
									</div>
					            </div>
					            @if($user->isGasExpensive())
						            <div class="alert alert-warning">
						            	{{ trans('vox.page.profile.wallet-withdraw-gas') }}
			                        </div>
			                    @endif

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
				@endif


                @if($history->isNotEmpty())

					<div class="form-horizontal black-line-title">
		                <h4 class="bold">
		                	{{ trans('vox.page.profile.title-history') }}
		                </h4>
		            	<table class="table">
		            		<thead>
		            			<tr>
			            			<th>
			            				{{ trans('front.page.profile.history.list-date') }}
			            			</th>
			            			<th>
			            				{{ trans('front.page.profile.history.list-amount') }}
			            			</th>
			            			<th>
			            				{{ trans('front.page.profile.history.list-address') }}
			            			</th>
			            			<th>
			            				{{ trans('front.page.profile.history.list-status') }}
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
		            							{{ trans('front.page.profile.history.status-new') }}
		            						@elseif($trans->status=='failed')
		            							{{ trans('front.page.profile.history.status-failed') }}
		            						@elseif($trans->status=='unconfirmed')
		            							<a href="https://etherscan.io/tx/{{ $trans->tx_hash }}" target="_blank">
		            								{{ trans('front.page.profile.history.status-unconfirmed') }}
		            								<i class="fa fa-share-square-o"></i>
		            							</a>
		            						@elseif($trans->status=='completed')
		            							<a href="https://etherscan.io/tx/{{ $trans->tx_hash }}" target="_blank">
		            								{{ trans('front.page.profile.history.status-completed') }}		            								
		            								<i class="fa fa-share-square-o"></i>
		            							</a>
		            						@endif
		            					</td>
		            				</tr>
		            			@endforeach
		            		</tbody>
		            	</table>
			        </div>
	            @endif


			@endif
		</div>
	</div>

@endsection