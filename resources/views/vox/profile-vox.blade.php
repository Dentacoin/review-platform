@extends('vox')

@section('content')

	<div class="container">
		<div class="col-md-3">
			@include('vox.template-parts.profile-menu')
		</div>
		<div class="col-md-9">

			<h2 class="page-title">
				<img src="{{ url('new-vox-img/profile-vox.png') }}" />
				{{ trans('vox.page.profile.vox.title') }}
				
			</h2>

			@if($histories->isNotEmpty())
		        <div class="history-section black-line-title form-horizontal">
	                <h4 class="bold">
	                	{{ trans('vox.page.profile.vox.list-title') }}
	                </h4>
	            	<table class="table">
	            		<thead>
	            			<tr>
		            			<th>
		            				
		            				{{ trans('vox.page.profile.vox.list-date') }}
		            			</th>
		            			<th>
		            				
		            				{{ trans('vox.page.profile.vox.list-questionnaire') }}
		            			</th>
		            			<th>
		            				
		            				{{ trans('vox.page.profile.vox.list-stats') }}
		            			</th>
		            			<th>
		            				DCN
		            			</th>
	            			</tr>
	            		</thead>
	            		<tbody>
							@foreach($histories as $completed)
								@if( $completed->vox )
		            				<tr>
		            					<td>
		            						{{ $completed->created_at->toDateString().', '.$completed->created_at->toTimeString() }}
		            					</td>
		            					<td>
											{{ $completed->vox->title }}
		            					</td>
		            					<td>
		            						@if($completed->vox->stats_questions->isNotEmpty())
											<a href="{{ $completed->vox->getStatsList() }}" target="_blank">
												{{ trans('vox.page.profile.vox.list-check') }}
											</a>
											@endif
		            					</td>
		            					<td>
		            						{{ $completed->reward ? $completed->reward : '-' }}
		            					</td>
		            				</tr>
	            				@endif
	            			@endforeach
	            		</tbody>
	            	</table>
			  	</div>
		  	@endif




        	@if($payouts->isNotEmpty())
		        <div class="payputs-section black-line-title">
	                <h4 class="bold">
	                	Payouts
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
	            			@foreach( $payouts as $trans )
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

		  	@if($user->bans->isNotEmpty())

	        	<div class="bans-section black-line-title">
	                <h4 class="bold">
	                	{{ trans('vox.page.profile.vox.bans-title') }}
	                </h4>
	            	<table class="table">
	            		<thead>
	            			<tr>
		            			<th>
		            				{{ trans('vox.page.profile.vox.ban-date') }}
		            			</th>
	            				<th>
		            				{{ trans('vox.page.profile.vox.ban-duration') }}
	            					
	            				</th>
		            			<th>
		            				{{ trans('vox.page.profile.vox.ban-reason') }}
		            			</th>
		            			<th>
		            				{{ trans('vox.page.profile.vox.ban-expires') }}
		            			</th>
	            			</tr>
	            		</thead>
	            		<tbody>
	            			@foreach( $user->bans as $ban )
	            				<tr>
	            					<td>
	            						{{ $ban->created_at->toDateString().', '.$ban->created_at->toTimeString() }}
	            					</td>
	            					<td>
	            						{{ $ban->created_at->diffInHours($ban->expires)  }}h
	            					</td>
	            					<td>
	            						{{ trans('vox.page.profile.vox.ban-reason-'.$ban->type) }}
	            					</td>
	            					<td>
	            						@if($ban->expires==null)
	            							{{ trans('vox.page.profile.vox.ban-permanent') }}
	            						@elseif($ban->expires->lt( Carbon\Carbon::now() ))
	            							{{ trans('vox.page.profile.vox.ban-expired') }}
	            						@else
	            							{{ str_pad(\Carbon\Carbon::now()->diffInHours($ban->expires), 2, "0", STR_PAD_LEFT)}}:{{ str_pad(\Carbon\Carbon::now()->diffInMinutes($ban->expires) % 60 , 2, "0", STR_PAD_LEFT)}}:{{ str_pad(\Carbon\Carbon::now()->diffInSeconds($ban->expires) % 60 , 2, "0", STR_PAD_LEFT)}}
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




@if($current_ban)
	
	<div class="popup banned active">
		<div class="wrapper">
			<img src="{{ url('new-vox-img/mistakes'.$prev_bans.'.png') }}" class="zman" />
			<div class="inner">
				<h2>You've been banned for answering inconsistently.</h2>
				@if($prev_bans!=4)
					<h3>Time left until you can return to DentaVox:</h3>
					<div class="countdown">
						<div class="hours-countdown">
							COUNTDOWN: <span>{{ $time_left }}</span>
						</div>
					</div>
				@else
					
				@endif
				<div class="alternatives">
					@if($prev_bans==1)
						<div>
							Too long of a wait? Try the Dentacare app and earn DCN for keeping a good oral hygiene!
						</div>
						<a href="https://dentacare.dentacoin.com/" target="_blank">
							<img src="{{ url('new-vox-img/bans-dentacare.png') }}" />
						</a>
					@elseif($prev_bans==2)
						<div>
							Too long of a wait? Earn DCN by giving your opinion on dentists you have visited and suggesting improvements! [Trusted Reviews logo]
						</div>
						<a href="https://reviews.dentacoin.com/" target="_blank">
							<img src="{{ url('new-vox-img/bans-trp.png') }}" />
						</a>
					@elseif($prev_bans==3)
						<div>
							Too long of a wait? Try the Dentacare app and earn DCN for keeping a good oral hygiene!
						</div>
						<a href="https://dentacare.dentacoin.com/" target="_blank">
							<img src="{{ url('new-vox-img/bans-dentacare.png') }}" />
						</a>
					@else
						<div>
							You can no longer return to DentaVox. Still, you can use the other Dentacoin tools to earn DCN by contributing to the global dental community. 
						</div>
						<a href="https://dentacare.dentacoin.com/" target="_blank">
							<img src="{{ url('new-vox-img/bans-dentacare.png') }}" />
						</a>
						<a href="https://reviews.dentacoin.com/" target="_blank">
							<img src="{{ url('new-vox-img/bans-trp.png') }}" />
						</a>
					@endif
				</div>

				<div class="bans-received">
					Bans received:
					<div class="flex">
						@for($i=1;$i<=4;$i++)
							<img src="{{ url('new-vox-img/popup-sign-'.($i==4 ? ($prev_bans==4 ? '4' : '5') : ( $i<=$prev_bans ? $i : '0' )).'.png') }}" />
						@endfor
					</div>
				</div>
			</div>
			<a class="closer x">
				<i class="fas fa-times"></i>
			</a>
		</div>
	</div>
@endif

@endsection