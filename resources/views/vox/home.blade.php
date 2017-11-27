@extends('vox')

@section('content')

	
		<div class="main-title">
			<h1 class="bold title">
				@if($rewarded_for_first)
					{{ trans('vox.page.home.title-rewarded') }}
				@else
					{{ trans('vox.page.home.title') }}
				@endif
			</h1>
		</div>
		<div class="container">
			<div class="another-questions">

	  			@include('front.errors')
	  			
				@if($user->is_verified || $user->fb_id)
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
								@foreach($voxes as $vox)
									<div class="another-question {{ $loop->first ? 'active' : '' }}">
										<div class="another-question-header clearfix">
											<div class="left">
												<span class="bold">{{ $vox->reward }} DCN</span>
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
											<a class="opinion" href="{{ getLangUrl('questionnaire/'.$vox->id) }}">
												{{ trans('vox.common.take-the-test') }}
											</a>
										</div>
									</div>
								@endforeach
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
					<div class="alert alert-info" style="text-align: left;">
						{!! nl2br(trans('vox.common.verify-email')) !!}
					</div>
				@endif
			</div>
		</div>
    	
    	
@endsection