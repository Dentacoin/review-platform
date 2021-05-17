<!-- <div class="section-work">
	<div class="container">

		<p class="h2-bold">{!! nl2br(trans('vox.page.index.how-works.title')) !!}</p>
		<h2>{!! nl2br(trans('vox.page.index.how-works.subtitle')) !!}</h2>

		<p class="work-desc">
			{!! nl2br(trans('vox.page.index.how-works.description')) !!}			
		</p>

		<div class="flex">
			<div class="col tac" style="{{ $user ? 'margin-left: 12%' : '' }}">
				<div class="image-wrapper warm-image">
					<img src="{{ url('new-vox-img/dentavox-surveys-warm-up-icon.png') }}" alt="Dentavox surveys warm-up icon" width="112" height="112">
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
						<img src="{{ url('new-vox-img/dentavox-surveys-signup-icon.png') }}" alt="Dentavox surveys signup icon" width="112" height="112">
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
					<img src="{{ url('new-vox-img/dentavox-surveys-rewards-icon.png') }}" alt="Dentavox surveys rewards icon" width="112" height="112">
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
					<img src="{{ url('new-vox-img/dentavox-take-dental-surveys-icon.png') }}" alt="Dentavox take dental surveys icon" width="112" height="112">
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

		<div class="tac">
			<a class="black-button check-welcome" href="{{ getLangUrl('welcome-survey') }}">
				{!! nl2br(trans('vox.page.index.start')) !!}
			</a>
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
						"link" => '<a href="https://dentacoin.com/users#section-google-map" target="_blank">',
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
</div> -->



<div class="make-money-wrapper index-container tac">
	<p class="h2-bold">START MAKING MONEY</p>
	<h2>with legitimate paid surveys</h2>

	<div class="flex flex-center flex-text-center break-mobile">
		<div class="col">
			<div class="img-wrap">
				<img src="{{ url('new-vox-img/forever-free-icon.svg') }}">
			</div>
			<h3>FOREVER FREE</h3>
			<p>It’s absolutely free and easy to register and use DentaVox.</p>
		</div>
		<div class="col">
			<div class="img-wrap">
				<img src="{{ url('new-vox-img/real-rewards-icon.svg') }}">
			</div>
			<h3>REAL REWARDS</h3>
			<p>We have distributed 10 billion DCN tokens to 82K+ users.</p>
		</div>
		<div class="col">
			<div class="img-wrap">
				<img src="{{ url('new-vox-img/rich-choice-icon.svg') }}">
			</div>
			<h3>RICH CHOICE</h3>
			<p>With two new surveys weekly, your rewards will never end.</p>
		</div>
	</div>
	<a class="blue-button new-style with-arrow" href="{{ getLangUrl('welcome-survey') }}">
		{!! nl2br(trans('vox.page.index.start')) !!} <img src="{{ url('new-vox-img/white-arrow-right.svg') }}">
	</a>
</div>

<div class="current-dcn-price-wrapper tac">
	<div class="container">
		<p class="h2-bold">IT GETS EVEN SWEETER:</p>
		<h2>Your rewards can grow in value!</h2>
		<span>Today’s price: <br/> 10000 DCN = {{ sprintf('%.2F', 10000 * $dcn_original_price) }}$</span>
	</div>
</div>

<div class="why-dentacoin-wrapper index-container">
	<div class="tac">
		<p class="h2-bold">WHY DENTACOIN REWARDS</p>
		<h2>… Well, what else?</h2>
	</div>
	<div class="flex break-mobile">
		<div class="col">
			<img src="{{ url('new-vox-img/why-dcn-rewards.png') }}">
		</div>
		<div class="col">
			<div class="flex flex-center why-dentacoin-first-reason break-mobile">
				<div class="why-number">1</div>
				<p><b>Everyone loves crypto</b><br/>
				and it has never been easier to get into it.</p>
			</div>
			<div class="flex flex-center break-mobile">
				<div class="why-number">2</div>
				<p><b>You can pay for dental services</b><br/>
				as DCN is the only dental coin.</p>
			</div>
			<div class="flex flex-center break-mobile">
				<div class="why-number">3</div>
				<p><b>You can exchange DCN</b><br/>
				to other crypto and traditional currencies.</p>
			</div>
			<div class="flex flex-center break-mobile">
				<div class="why-number">4</div>
				<p><b>DCN can grow in value</b><br/>
				if you store it and don’t sell it immediately.</p>
			</div>
		</div>
	</div>
</div>