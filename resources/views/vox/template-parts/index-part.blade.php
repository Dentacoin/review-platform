<div class="section-work">
	<div class="container">

		<p class="h2-bold">{!! nl2br(trans('vox.page.index.how-works.title')) !!}</p>
		<h2>{!! nl2br(trans('vox.page.index.how-works.subtitle')) !!}</h2>

		<p class="work-desc">
			{!! nl2br(trans('vox.page.index.how-works.description')) !!}			
		</p>

		<div class="flex">
			<div class="col tac" style="{{ $user ? 'margin-left: 12%' : '' }}">
				<div class="image-wrapper warm-image">
					<img src="{{ url('new-vox-img/dentavox-surveys-warm-up-icon.png') }}" alt="Dentavox surveys warm-up icon">
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
				<div class="col tac">
					<div class="image-wrapper sign-image">
						<img src="{{ url('new-vox-img/dentavox-surveys-signup-icon.png') }}" alt="Dentavox surveys signup icon">
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
			<div class="col tac">
				<div class="image-wrapper grab-image">
					<img src="{{ url('new-vox-img/dentavox-surveys-rewards-icon.png') }}" alt="Dentavox surveys rewards icon">
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
			<div class="col tac">
				<div class="image-wrapper no-image">
					<img src="{{ url('new-vox-img/dentavox-take-dental-surveys-icon.png') }}" alt="Dentavox take dental surveys icon">
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
			<div class="col">
				<a class="black-button" href="{{ getLangUrl('welcome-survey') }}">
					{!! nl2br(trans('vox.page.index.start')) !!}
				</a>
			</div>
		</div>
	</div>
</div>

<div class="section-reasons">
	<div class="container">
		<p class="h2-bold">{!! nl2br(trans('vox.page.index.reasons.title')) !!}</p>
		<h2>{!! nl2br(trans('vox.page.index.reasons.subtitle')) !!}</h2>

		<div class="reasons-wrap flex flex-center">
			<div class="col reason-number">
				<div>01</div>
			</div>
			<div class="col reason-title">
				<h4>{!! nl2br(trans('vox.page.index.reasons.1.title')) !!}</h4>
			</div>
			<div class="col reason-desc">
				<p>
					{!! nl2br(trans('vox.page.index.reasons.1.content')) !!}
				</p>
			</div>
		</div>
		<div class="reasons-wrap flex flex-center">
			<div class="col reason-number">
				<div>02</div>
			</div>
			<div class="col reason-title">
				<h4>{!! nl2br(trans('vox.page.index.reasons.2.title')) !!}</h4>
			</div>
			<div class="col reason-desc">
				<p>
					{!! nl2br(trans('vox.page.index.reasons.2.content',[
						"link" => '<a href="https://dentacoin.com/partner-network" target="_blank">',
						"endlink" => '</a>'
					])) !!}						
				</p>
			</div>
		</div>
		<div class="reasons-wrap flex flex-center">
			<div class="col reason-number">
				<div>03</div>
			</div>
			<div class="col reason-title">
				<h4>{!! nl2br(trans('vox.page.index.reasons.3.title')) !!}</h4>
			</div>
			<div class="col reason-desc">
				<p>
					{!! nl2br(trans('vox.page.index.reasons.3.content')) !!}
				</p>
			</div>
		</div>
		<div class="reasons-wrap flex flex-center">
			<div class="col reason-number">
				<div>04</div>
			</div>
			<div class="col reason-title">
				<h4>{!! nl2br(trans('vox.page.index.reasons.4.title')) !!}</h4>
			</div>
			<div class="col reason-desc">
				<p>
					{!! nl2br(trans('vox.page.index.reasons.4.content')) !!}
				</p>
			</div>
		</div>
		<div class="reasons-wrap flex flex-center">
			<div class="col reason-number">
				<div>05</div>
			</div>
			<div class="col reason-title">
				<h4>{!! nl2br(trans('vox.page.index.reasons.5.title')) !!}</h4>
			</div>
			<div class="col reason-desc">
				<p>
					{!! nl2br(trans('vox.page.index.reasons.5.content')) !!}
				</p>
			</div>
		</div>
	</div>
</div>