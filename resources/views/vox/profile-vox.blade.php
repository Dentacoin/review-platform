@extends('vox')

@section('content')

	<div class="container">
		<div class="col-md-9">

			<h2 class="page-title">
				<img src="{{ url('new-vox-img/profile-vox.png') }}" />
				{{ trans('vox.page.profile.vox.title') }}
			</h2>

	        <div class="history-section form-horizontal">
				<div class="black-line-title">
	                <h4 class="bold">
	                	{{ trans('vox.page.profile.vox.list-title') }}
	                </h4>
	            </div>
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
	            				DCN
	            			</th>
            			</tr>
            		</thead>
            		<tbody>

						@if($histories->isNotEmpty())
							@foreach($histories as $completed)
								@if( $completed->vox )
		            				<tr>
		            					<td>
		            						{{ !empty($completed->created_at) ? ($completed->created_at->toDateString().', '.$completed->created_at->toTimeString()) : '-' }}
		            					</td>
		            					<td>
											{{ $completed->vox->title }}
		            					</td>
		            					<td>
		            						{{ $completed->reward ? $completed->reward : '-' }}
		            					</td>
		            				</tr>
	            				@endif
	            			@endforeach
		  				@endif
            		</tbody>
            	</table>
		  	</div>

		  	@if($more_surveys)
			  	<div class="alert alert-info">				
					You haven't taken any surveys yet. Just pick a topic and start earning more DCN!
				</div>

				@if($latest_voxes->count())

					<div class="section-recent-surveys">
						<h4>Latest Surveys:</h4>

						<div class="swiper-container">
						    <div class="swiper-wrapper">
						    	@foreach($latest_voxes as $survey)
							      	<div class="swiper-slide">
							      		<div class="slider-inner">
								    		<div class="slide-padding">
								      			<div class="cover" style="background-image: url('{{ $survey->getImageUrl() }}');" alt='{{ trans("vox.page.stats.title-single", ["name" => $survey->title]) }}'>
								      				@if($survey->featured)
								      					<img class="featured-img doublecoin" src="{{ url('new-vox-img/dentavox-dentacoin-flipping-coin.gif') }}" alt="Dentavox dentacoin flipping coin">
								      				@endif
								      			</div>
								      			<div class="vox-header clearfix">
													<div class="flex first-flex">
														<div class="col left">
															<h4 class="survey-title bold">{{ $survey->title }}</h4>
														</div>
													</div>
													<div class="flex first-flex">
														<div class="col right">
															<span class="bold">{{ !empty($survey->complex) ? 'max ' : '' }} {{ $survey->getRewardTotal() }} DCN</span>
															<p>{{ $survey->formatDuration() }}</p>
														</div>					
													</div>
													<div class="flex second-flex">
														<div class="col right">
															<div class="btns">
																<a class="opinion blue-button" href="{{ $survey->getLink() }}">
																	{{ trans('vox.common.take-the-test') }}
																</a>
															</div>
														</div>
													</div>
												</div>
									      	</div>
								      	</div>
								    </div>
						      	@endforeach
						    </div>
						</div>
					</div>
				@endif

			@endif

        	@if($user->vox_bans->isNotEmpty())
	        	<div class="bans-section">

					<div class="black-line-title">
		                <h4 class="bold">
		                	{{ trans('vox.page.profile.vox.bans-title') }}
		                </h4>
		            </div>
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
	            			@foreach( $user->vox_bans as $ban )
	            				<tr>
	            					<td>
	            						{{ $ban->created_at ? ($ban->created_at->toDateString().', '.$ban->created_at->toTimeString()) : '' }}
	            					</td>
	            					<td>
	            						{{ $ban->created_at ? $ban->created_at->diffInHours($ban->expires) : 0  }}h
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
					<h2>{{ $ban_reason }}</h2>
					@if($prev_bans!=4 && !empty($current_ban->expires))
						<h3>
							{!! trans('vox.page.bans.banned-time-left') !!}:
						</h3>
						<div class="countdown">
							<div class="hours-countdown">
								{!! trans('vox.page.bans.bans-countdown') !!}:
								<span>{{ $time_left }}</span>
							</div>
						</div>
					@else
						<br/>
						<br/>
					@endif
					<div class="alternatives">
						<div>
							{!! $ban_alternatives !!}							
						</div>
						{!! $ban_alternatives_buttons !!}
					</div>

					<div class="bans-received">
						{!! trans('vox.page.bans.bans-received') !!}:
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


    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>

@endsection