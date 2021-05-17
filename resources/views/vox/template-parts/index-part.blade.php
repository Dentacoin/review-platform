<div class="make-money-wrapper index-container tac">
	<p class="h2-bold">{!! nl2br(trans('vox.page.index.make-money.title')) !!}</p>
	<h2>{!! nl2br(trans('vox.page.index.make-money.subtitle')) !!}</h2>

	<div class="flex flex-center flex-text-center break-mobile">
		<div class="col">
			<div class="img-wrap">
				<img src="{{ url('new-vox-img/forever-free-icon.svg') }}">
			</div>
			<h3>{!! nl2br(trans('vox.page.index.make-money.1.title')) !!}</h3>
			<p>{!! nl2br(trans('vox.page.index.make-money.1.subtitle')) !!}</p>
		</div>
		<div class="col">
			<div class="img-wrap">
				<img src="{{ url('new-vox-img/real-rewards-icon.svg') }}">
			</div>
			<h3>{!! nl2br(trans('vox.page.index.make-money.2.title')) !!}</h3>
			<p>{!! nl2br(trans('vox.page.index.make-money.2.subtitle')) !!}</p>
		</div>
		<div class="col">
			<div class="img-wrap">
				<img src="{{ url('new-vox-img/rich-choice-icon.svg') }}">
			</div>
			<h3>{!! nl2br(trans('vox.page.index.make-money.3.title')) !!}</h3>
			<p>{!! nl2br(trans('vox.page.index.make-money.3.subtitle')) !!}</p>
		</div>
	</div>
	<a class="blue-button new-style with-arrow" href="{{ getLangUrl('welcome-survey') }}">
		{!! nl2br(trans('vox.page.index.start')) !!} <img src="{{ url('new-vox-img/white-arrow-right.svg') }}">
	</a>
</div>

<div class="current-dcn-price-wrapper tac">
	<div class="container">
		<p class="h2-bold">{!! nl2br(trans('vox.page.index.dcn-price.title')) !!}</p>
		<h2>{!! nl2br(trans('vox.page.index.dcn-price.subtitle')) !!}</h2>
		<span>{!! nl2br(trans('vox.page.index.dcn-price.today')) !!}: <br/> 10000 DCN = {{ sprintf('%.2F', 10000 * $dcn_original_price) }}$</span>
	</div>
</div>

<div class="why-dentacoin-wrapper index-container">
	<div class="tac">
		<p class="h2-bold">{!! nl2br(trans('vox.page.index.why-dcn-rewards.title')) !!}</p>
		<h2>{!! nl2br(trans('vox.page.index.why-dcn-rewards.subtitle')) !!}</h2>
	</div>
	<div class="flex break-mobile">
		<div class="col">
			<img src="{{ url('new-vox-img/why-dcn-rewards.png') }}">
		</div>
		<div class="col">
			<div class="flex flex-center why-dentacoin-first-reason break-mobile">
				<div class="why-number">1</div>
				<p>{!! nl2br(trans('vox.page.index.why-dcn-rewards.1.description')) !!}</p>
			</div>
			<div class="flex flex-center break-mobile">
				<div class="why-number">2</div>
				<p>{!! nl2br(trans('vox.page.index.why-dcn-rewards.2.description')) !!}</p>
			</div>
			<div class="flex flex-center break-mobile">
				<div class="why-number">3</div>
				<p>{!! nl2br(trans('vox.page.index.why-dcn-rewards.3.description')) !!}</p>
			</div>
			<div class="flex flex-center break-mobile">
				<div class="why-number">4</div>
				<p>{!! nl2br(trans('vox.page.index.why-dcn-rewards.4.description')) !!}</p>
			</div>
		</div>
	</div>
</div>