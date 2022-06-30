@if(empty($user))

	<div class="add-practice">
		<div class="container">
			<div class="flex flex-mobile">
				<div class="col">
					<div class="practice-image">
						<img 
							class="pc-practice-img" 
							src="{{ url('img-trp/dentacoin-trusted-reviews-add-practice-icon.png') }}" 
							alt="{{ trans('trp.alt-tags.add-practice') }}"
						/>
					</div>
					<div class="mobile-practice-img">
						<img src="{{ url('img-trp/dentacoin-trusted-reviews-add-practice-icon.png') }}" alt="{{ trans('trp.alt-tags.add-practice') }}"/>
					</div>
				</div>
				<div class="col">
					<h2 class="mont">{!! nl2br(trans('trp.page.index.first-rated-dentist.title')) !!}</h2>
					<h4>
						{!! nl2br(trans('trp.page.index.first-rated-dentist.subtitle')) !!}
					</h4>
					<ul>
						<li><span class="circle"></span>{!! nl2br(trans('trp.page.index.first-rated-dentist.description-1')) !!}</li>
						<li><span class="circle"></span>{!! nl2br(trans('trp.page.index.first-rated-dentist.description-2')) !!}</li>
						<li><span class="circle"></span>{!! nl2br(trans('trp.page.index.first-rated-dentist.description-3')) !!}</li>
					</ul>
					<div class="tac-tablet">
						<a href="{{ getLangUrl('welcome-dentist') }}" class="transparent-white-button">
							{!! nl2br(trans('trp.page.index.first-rated-dentist.button')) !!}
						</a>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="how-works-section">
		<div class="container flex">
			<div class="col">
				<h2 class="mont">
					{!! nl2br(trans('trp.page.index.usp-3-title')) !!}
				</h2>
				<p>
					{!! nl2br(trans('trp.page.index.usp-3-content')) !!}
				</p>
			</div>
			
			<div class="col">
				<img src="{{ url('img-trp/how-it-works.png') }}" alt="{{ trans('trp.alt-tags.how-it-works') }}"/>
			</div>
		</div>
	</div>
@endif