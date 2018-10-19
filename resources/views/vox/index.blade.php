@extends('vox')

@section('content')

	<div class="full">
		<p class="first-absolute">
			<span>
				{!! nl2br(trans('vox.page.index.title')) !!}
			</span>
			<br/>
			<a class="black-button" href="{{ getLangUrl('welcome-survey') }}">
				{!! nl2br(trans('vox.page.index.start')) !!}
			</a>
		</p>
		<a href="javascript:;" class="second-absolute">
			{!! nl2br(trans('vox.page.index.more')) !!}
		</a>
	</div>
	<div class="container section-work">

		<h2>
			{!! nl2br(trans('vox.page.index.how-works')) !!}
		</h2>		

		<div class="row">
			<div class="col-md-3 tac" style="{{ $user ? 'margin-left: 12%' : '' }}">
				<div class="image-wrapper warm-image">
					<img src="{{ url('new-vox-img/warm-up.png') }}">
				</div>
				<div>
					<h4>
						1. {!! nl2br(trans('vox.page.index.how-works.1.title')) !!}
					</h4>
					<p>
						{!! nl2br(trans('vox.page.index.how-works.1.content')) !!}
					</p>
				</div>
			</div>
			@if(!$user)
				<div class="col-md-3 tac">
					<div class="image-wrapper sign-image">
						<img src="{{ url('new-vox-img/sign-up.png') }}">
					</div>
					<div>
						<h4>
							2. {!! nl2br(trans('vox.page.index.how-works.2.title')) !!}
						</h4>
						<p>
							{!! nl2br(trans('vox.page.index.how-works.2.content')) !!}
						</p>
					</div>
				</div>
			@endif
			<div class="col-md-3 tac">
				<div class="image-wrapper grab-image">
					<img src="{{ url('new-vox-img/grab-reward.png') }}">
				</div>
				<div>
					<h4>
						{{ $user ? '2' : '3' }}. 
						{!! nl2br(trans('vox.page.index.how-works.3.title')) !!}
					</h4>
					@if($user)
						<p>
							{!! nl2br(trans('vox.page.index.how-works.3.content-logged')) !!}
						</p>
					@else
						<p>
							{!! nl2br(trans('vox.page.index.how-works.3.content')) !!}
						</p>
					@endif
				</div>
			</div>
			<div class="col-md-3 tac">
				<div class="image-wrapper no-image">
					<img src="{{ url('new-vox-img/take-surveys.png') }}">
				</div>
				<div>
					<h4>
						{{ $user ? '3' : '4' }}. 
						{!! nl2br(trans('vox.page.index.how-works.4.title')) !!}
					</h4>
					<p>
						{!! nl2br(trans('vox.page.index.how-works.4.content')) !!}
					</p>
				</div>
			</div>
		</div>

		<div class="row tac">
			<div class="col-md-12">
				<a class="black-button" href="{{ getLangUrl('welcome-survey') }}">
					{!! nl2br(trans('vox.page.index.start')) !!}
				</a>
			</div>
		</div>
	</div>

	<div class="section-stats">
		<div class="container">
			<img src="{{ url('new-vox-img/stats-front.png') }}">
			<h3>
				{!! nl2br(trans('vox.page.index.curious')) !!}
			</h3>
			<a href="{{ getLangUrl('dental-survey-stats') }}" class="check-stats">
				{{ trans('vox.common.check-statictics') }}
			</a>
		</div>
	</div>

	<div class="container section-about">
		<h2 class="tac">
			{!! nl2br(trans('vox.page.index.about')) !!}
		</h2>
		<h4>
			{!! nl2br(trans('vox.page.index.about.title')) !!}
		</h4>
		<p>
			{!! nl2br(trans('vox.page.index.about.content-1')) !!}
		</p>
		<p>
			{!! nl2br(trans('vox.page.index.about.content-2')) !!}
		</p>
	</div>
    	
@endsection