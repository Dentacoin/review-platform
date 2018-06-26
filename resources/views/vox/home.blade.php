@extends('vox')

@section('content')

		<div class="container">
			<div class="another-questions">

	  			@include('front.errors')
	  			
				@if($user->email && $user->is_verified)
						<h3 class="bold">
							{{ trans('vox.page.home.try-another') }}
						</h3>
						<div class="gray-border"></div>
						<div class="questions-menu">
							<span class="bold">
								{{ trans('vox.page.home.sort') }}
							</span>
							@foreach($sorts as $key => $val)
								<a href="{{ getLangUrl('/') }}?sort={{ $key }}" {!! $key==$sort ? 'class="active"' : '' !!}>{{ $val }}</a>
							@endforeach
						</div>
						<a href="javascript:;" class="triangle-up"></a>
						<div class="questions-wrapper" id="questions-wrapper">
							<div class="questions-inner" id="questions-inner">

								@if($sort=='category')
									@foreach( $cats as $cat )
										@if($cat->voxes->isNotEmpty())
											<h3 class="category-title">{{ $cat->name }}</h3>
											@foreach( $cat->voxes as $vox_to_cat)
												<div class="another-question {{ $loop->parent->first && $loop->first ? 'active' : '' }}">
													<div class="another-question-header clearfix">
														<div class="left">
															<span class="bold">{{ $vox_to_cat->vox->getRewardTotal() }} DCN</span>
														</div>
														<div class="right">
															<p>{{ $vox_to_cat->vox->formatDuration() }}</p>
															<p>
																{{ trans('vox.common.questions-count', ['count' => $vox_to_cat->vox->questions->count()]) }}
															</p>
														</div>
													</div>
													<h4 class="bold">{{ $vox_to_cat->vox->title }}</h4>
													<div class="another-question-content">
														<p class="question-description">{{ $vox_to_cat->vox->description }}</p>
														<a class="statistics" href="{{ getLangUrl('stats/'.$vox_to_cat->vox->id) }}">
															{{ trans('vox.common.check-statictics') }}
														</a>
														@if(!in_array($vox_to_cat->vox_id, $taken))
															<a class="opinion" href="{{ $vox_to_cat->vox->getLink() }}">
																{{ trans('vox.common.take-the-test') }}
															</a>
														@endif
													</div>
												</div>
											@endforeach
										@endif
									@endforeach
								@else
									@foreach( $voxes as $vox)
										<div class="another-question {{ $loop->first ? 'active' : '' }}">
											<div class="another-question-header clearfix">
												<div class="left">
													<span class="bold">{{ $vox->getRewardTotal() }} DCN</span>
												</div>
												<div class="right">
													<p>{{ $vox->formatDuration() }}</p>
													<p>
														{{ trans('vox.common.questions-count', ['count' => $vox->questions->count()]) }}
													</p>
												</div>
											</div>
											<h4 class="bold">{{ $vox->title }}</h4>
											<div class="another-question-content">
												<p class="question-description">{{ $vox->description }}</p>
												<a class="statistics" href="{{ getLangUrl('stats/'.$vox->id) }}">
													{{ trans('vox.common.check-statictics') }}
												</a>
												@if(!in_array($vox->id, $taken))
													<a class="opinion" href="{{ $vox->getLink() }}">
														{{ trans('vox.common.take-the-test') }}
													</a>
												@endif
											</div>
										</div>
									@endforeach
								@endif
							</div>
						</div>
						<a href="javascript:;" class="triangle-down"></a>

						@if($user->vox_rewards->isNotEmpty())
							<br/>
							<br/>
							<div class="alert alert-info">
								{{ trans('vox.page.home.looking-for-stats') }}
								<br/>
								<a href="{{ getLangUrl('profile') }}">
									{{ trans('vox.page.home.looking-for-stats-link') }}
								</a>
							</div>
						@endif
				@else

		        	<div class="panel panel-default personal-panel">
			            <div class="panel-heading">
			                <h3 class="panel-title bold">
			                	{{ trans('vox.common.no-email-title') }}
			                </h3>
			            </div>
		            	<div class="panel-body">
		                    @include('vox.template-parts.verify-email', [
		                    	'cta' => trans('vox.page.home.no-email')
		                    ])
		            	</div>
		            </div>
				@endif
			</div>
		</div>
    	
    	
@endsection