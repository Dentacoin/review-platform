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
						<img src="{{ url('img-trp/dentacoin-trusted-reviews-add-practice-icon.png') }}" alt="Banner image of smiling female dentist on Dentacoin Trusted Reviews"/>
						{{-- {{ trans('trp.alt-tags.add-practice') }} --}}
					</div>
				</div>
				<div class="col">
					{{-- <h2>{!! nl2br(trans('trp.page.index.first-rated-dentist.title')) !!}</h2> --}}
					<h2 class="mont">BECOME A TOP-RATED DENTIST</h2>
					<h4>
						{!! nl2br(trans('trp.page.index.first-rated-dentist.subtitle')) !!}
					</h4>
					<ul>
						<li><span class="circle"></span>Attract new patients and build relationships of trust</li>
						<li><span class="circle"></span>Get to the top of search results and reach more patients</li>
						<li><span class="circle"></span>Learn from patient feedback and achieve excellence</li>
					</ul>
					<div class="tac-tablet">
						<a href="{{ getLangUrl('welcome-dentist') }}" class="transparent-white-button">
							{{-- {!! nl2br(trans('trp.page.index.first-rated-dentist.button')) !!} --}}
							LIST YOUR PRACTICE
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
					{{-- {!! nl2br(trans('trp.page.index.usp-3-content')) !!} --}}

					Dentacoin Trusted Reviews is the first Blockchain-based platform for dental treatment reviews, developed by the <a href="https://dentacoin.com/" class="text" target="_blank">Dentacoin Foundation</a>. It rewards Patients for sharing their valuable feedback, and Dentists - for actively  improving their dental care services. They both receive Dentacoin (DCN) - the cryptocurrency created especially for the dental industry.
					<br/><br/>
					The DCN tokens collected can be stored in a digital wallet, exchanged to other currencies or used to pay for dental services in <a href="https://dentacoin.com/users#section-google-map" class="text" target="_blank">multiple partner venues</a> across the world.
				</p>
			</div>
			
			<div class="col">
				<img src="{{ url('img-trp/how-it-works.png') }}" alt="Smiling male dentist and female patient on Dentacoin Trusted Reviews"/>
			</div>
		</div>
	</div>
@endif
