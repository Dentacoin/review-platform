<div class="popup fixed-popup" id="popup-widget">
	<div class="popup-inner inner-white">
		<div class="popup-pc-buttons">
			<a href="javascript:;" class="close-popup"><i class="fas fa-times"></i></a>
		</div>

		<div class="popup-mobile-buttons">
			<a href="javascript:;" class="close-popup">< {!! nl2br(trans('trp.common.back')) !!}</a>
		</div>
		<h2 class="hide-fb">{!! nl2br(trans('trp.popup.popup-widget.title')) !!}</h2>
		<h2 class="show-fb" style="display: none;">{!! nl2br(trans('trp.popup.popup-widget.facebook-title')) !!}</h2>

		<h4 class="popup-title hide-fb">{!! nl2br(trans('trp.popup.popup-widget.subtitle')) !!}</h4>
		<h4 class="popup-title show-fb" style="display: none;">{!! nl2br(trans('trp.popup.popup-widget.facebook-subtitle')) !!}</h4>

		<div class="widget-step widget-step-1">
			<p class="popup-desc">
				{!! nl2br(trans('trp.popup.popup-widget.hint')) !!}
			</p>

			<h3 class="widget-step-title">{!! nl2br(trans('trp.popup.popup-widget.step1.title')) !!}</h3>
			<p class="step-description">{!! nl2br(trans('trp.popup.popup-widget.step1.subtitle')) !!}</p>

			<div class="mobile-radios modern-radios active">
				<div class="radio-label">
				  	<label for="widget-carousel" class="active flex mobile-tac">
						<span class="modern-radio">
							<span></span>
						</span>
				    	<input class="type-radio" type="radio" name="widget-layout" id="widget-carousel" value="carousel" checked="checked">
				    	<img class="layout-img" src="{{ url('img-trp/widget-carousel.png') }}" alt="Dentacoin trusted reviews carousel widget preview">
				    	<div class="widget-option">
				    		<p layout-text="{!! nl2br(trans('trp.popup.popup-widget.layout.carousel')) !!}">• {!! nl2br(trans('trp.popup.popup-widget.layout.carousel')) !!}</p>
				    		<div class="select-wrap flex flex-mobile">
				    			<span>{!! nl2br(trans('trp.popup.popup-widget.layout.carousel.results')) !!}: </span>
				    			<select name="slide-results" {!! $item->reviews_in_standard()->count() < 4 ? 'cant-select' : '' !!}>
				    				<option value="1" selected="selected">1</option>
				    				<option value="3">3</option>
				    			</select>
				    		</div>
				    		<div class="alert mobile-alert alert-warning slider-alert" style="display: none;">
								{!! nl2br(trans('trp.popup.popup-widget.layout.carousel.error')) !!}
							</div>
				    	</div>
				  	</label>
				</div>
				<div class="radio-label">
				  	<label for="widget-list" class="flex mobile-tac">
						<span class="modern-radio">
							<span></span>
						</span>
				    	<input class="type-radio" type="radio" name="widget-layout" id="widget-list" value="list">
				    	<img class="layout-img" src="{{ url('img-trp/widget-list.png') }}" alt="Dentacoin trusted reviews list widget preview">
				    	<div class="widget-option">
				    		<p layout-text="{!! nl2br(trans('trp.popup.popup-widget.layout.list')) !!}">• {!! nl2br(trans('trp.popup.popup-widget.layout.list')) !!}</p>
				    		<div class="select-wrap flex flex-mobile">
				    			<span>{!! nl2br(trans('trp.popup.popup-widget.layout.list.width')) !!}: </span>
				    			<input type="number" name="list-width" value="100" min="0"><span>%</span>
				    		</div>
				    		<div class="select-wrap flex flex-mobile">
				    			<span>{!! nl2br(trans('trp.popup.popup-widget.layout.list.height')) !!}: </span>
				    			<input type="number" name="list-height" value="450" min="0"><span>px</span>
				    		</div>
				    	</div>
				  	</label>
				</div>
				<div class="radio-label">
				  	<label for="widget-badge" class="flex mobile-tac">
						<span class="modern-radio">
							<span></span>
						</span>
				    	<input class="type-radio" type="radio" name="widget-layout" id="widget-badge" value="badge">
				    	<img class="layout-img" src="{{ url('img-trp/widget-badge.png') }}" alt="Dentacoin trusted reviews badge widget preview">
				    	<div class="widget-option">
				    		<p layout-text="{!! nl2br(trans('trp.popup.popup-widget.layout.badge')) !!}">• {!! nl2br(trans('trp.popup.popup-widget.layout.badge')) !!}</p>					    		
				    		<div class="select-wrap flex flex-mobile">
				    			<span>{!! nl2br(trans('trp.popup.popup-widget.layout.badge.style')) !!}: </span>
				    			<select name="badge">
				    				<option value="macro" selected="selected">{!! nl2br(trans('trp.popup.popup-widget.layout.badge.style.macro')) !!}</option>
				    				<option value="mini">{!! nl2br(trans('trp.popup.popup-widget.layout.badge.style.mini')) !!}</option>
				    			</select>
				    		</div>
				    	</div>
				  	</label>
				</div>
				<div class="radio-label">
				  	<label for="widget-fb" class="flex mobile-tac">
						<span class="modern-radio">
							<span></span>
						</span>
				    	<input class="type-radio" type="radio" name="widget-layout" id="widget-fb" value="fb">
				    	<img class="layout-img" src="{{ url('img-trp/fb-tab.png') }}" alt="Dentacoin trusted reviews facebook tab preview">
				    	<div class="widget-option">
				    		<p layout-text="{!! nl2br(trans('trp.popup.popup-widget.layout.fb')) !!}">• {!! nl2br(trans('trp.popup.popup-widget.layout.fb')) !!}</p>
				    	</div>
				  	</label>
				</div>
			</div>

			<div class="tac widget-button-next-wrap">
				<a href="javascript:;" class="button widget-button widget-layout-button" to-step="2">{!! trans('trp.common.next') !!} > </a>
			</div>
		</div>
		<div class="widget-step widget-step-2" style="display: none;">

			<h3 class="widget-step-title hide-fb">{!! nl2br(trans('trp.popup.popup-widget.step2.title')) !!}</h3>
			<h3 class="widget-step-title show-fb" style="display: none; margin-bottom: 30px !important;">{!! nl2br(trans('trp.popup.popup-widget.step2-facebook.title')) !!}</h3>
			<p class="step-description hide-fb"><!-- {!! nl2br(trans('trp.popup.popup-widget.step2.subtitle')) !!} --> {!! trans('trp.popup.popup-widget.selected-layout') !!}: <text id="selected-layout">Carousel</text></p>

			<div class="tac">
				<img id="selected-image-layout" src="{{ url('img-trp/widget-carousel.png') }}">
			</div>

			<div class="facebook-tab show-fb">
				<p class="tab-info"><img src="{{ url('img-trp/info.svg') }}">{!! trans('trp.popup.popup-widget.fb-info') !!}</p>
				<div class="modern-field alert-after">
					<input type="text" name="fb-page-id" id="fb-page-id" class="modern-input" autocomplete="off" readonly onfocus="this.removeAttribute('readonly');">
					<label for="fb-page-id">
						<span>{!! trans('trp.popup.popup-widget.fb-page-id') !!}</span>
					</label>
					<p>{!! trans('trp.popup.popup-widget.fb-page-id.info') !!}</p>
				</div>
			</div>

			<div class="select-reviews">
				<h4 class="widget-subtitle">• {!! nl2br(trans('trp.popup.popup-widget.reviews')) !!}</h3>

				<div class="mobile-radios modern-radios">
					<div class="radio-label">
					  	<label for="аll-reviews" class="active first-label hidden-option-wrap open">
							<span class="modern-radio">
								<span></span>
							</span>
					    	<input class="type-radio-widget-first" type="radio" name="review-type" id="аll-reviews" value="all" checked="checked">
					    	<span>{!! nl2br(trans('trp.popup.popup-widget.reviews.all')) !!}</span>
					    	<a href="javascript:;" class="open-hidden-option"><i class="fas fa-caret-down"></i></a>

					    	<div class="hidden-option active">
					    		<p>{!! nl2br(trans('trp.popup.popup-widget.reviews.results')) !!}</p>

					    		<div class="option-checkboxes">
					    			<label for="аll-reviews-all" class="active">
										<span class="modern-radio">
											<span></span>
										</span>
								    	<input class="type-radio-widget" type="radio" name="all-reviews-option" id="аll-reviews-all" value="all" checked="checked">
								    	{!! nl2br(trans('trp.popup.popup-widget.reviews.results.all')) !!}
								    </label>
					    			<label for="аll-reviews-last-fifteen">
										<span class="modern-radio">
											<span></span>
										</span>
								    	<input class="type-radio-widget" type="radio" name="all-reviews-option" id="аll-reviews-last-fifteen" value="15">
								    	{!! nl2br(trans('trp.popup.popup-widget.reviews.results.fifteen')) !!}
								    </label>
					    			<label for="аll-reviews-last-fifth">
										<span class="modern-radio">
											<span></span>
										</span>
								    	<input class="type-radio-widget" type="radio" name="all-reviews-option" id="аll-reviews-last-fifth" value="5">
								    	{!! nl2br(trans('trp.popup.popup-widget.reviews.results.five')) !!}
								    </label>
					    		</div>
					    	</div>
					  	</label>
					</div>
					<div class="radio-label" id="trusted-chosen" trusted-reviews-count="{!! $item->reviews_in_standard()->where('verified', 1)->count() !!}">
					  	<label for="trusted-reviews" class="first-label hidden-option-wrap">
							<span class="modern-radio">
								<span></span>
							</span>
					    	<input class="type-radio-widget-first" type="radio" name="review-type" id="trusted-reviews" value="trusted">
					    	<span>{!! nl2br(trans('trp.popup.popup-widget.reviews.trusted')) !!}</span>
					    	<img class="tooltip-text info" text="{!! nl2br(trans('trp.popup.popup-widget.reviews.trusted.hint')) !!}" src="{{ url('img-trp/info-light.png') }}">
					    	<a href="javascript:;" class="open-hidden-option"><i class="fas fa-caret-down"></i></a>

					    	<div class="hidden-option">
					    		<p>{!! nl2br(trans('trp.popup.popup-widget.reviews.results')) !!}</p>

					    		<div class="option-checkboxes">
					    			<label for="trusted-reviews-all" class="active">
										<span class="modern-radio">
											<span></span>
										</span>
								    	<input class="type-radio-widget" type="radio" name="trusted-reviews-option" id="trusted-reviews-all" value="all" checked="checked">
								    	{!! nl2br(trans('trp.popup.popup-widget.reviews.results.all')) !!}
								    </label>
					    			<label for="trusted-reviews-last-fifteen">
										<span class="modern-radio">
											<span></span>
										</span>
								    	<input class="type-radio-widget" type="radio" name="trusted-reviews-option" id="trusted-reviews-last-fifteen" value="15">
								    	{!! nl2br(trans('trp.popup.popup-widget.reviews.results.fifteen')) !!}
								    </label>
					    			<label for="trusted-reviews-last-fifth">
										<span class="modern-radio">
											<span></span>
										</span>
								    	<input class="type-radio-widget" type="radio" name="trusted-reviews-option" id="trusted-reviews-last-fifth" value="5">
								    	{!! nl2br(trans('trp.popup.popup-widget.reviews.results.five')) !!}
								    </label>
					    		</div>
					    	</div>
					  	</label>
					</div>
					<div class="radio-label">
					  	<label for="custom-trusted" class="first-label hidden-option-wrap">
							<span class="modern-radio">
								<span></span>
							</span>
					    	<input class="type-radio-widget-first" type="radio" name="review-type" id="custom-trusted" value="custom">
					    	<span>{!! nl2br(trans('trp.popup.popup-widget.reviews.custom')) !!}</span>
					    	<a href="javascript:;" class="open-hidden-option"><i class="fas fa-caret-down"></i></a>

					    	<div class="hidden-option">
					    		<p>
					    			{!! nl2br(trans('trp.popup.popup-widget.reviews.custom.selected', [
					    				'count' => '<text id="custom-reviews-length">0</text>'
					    			])) !!}
					    		</p>

					    		<a href="javascript:;" data-popup="select-reviews-popup" class="button">{!! nl2br(trans('trp.popup.popup-widget.reviews.custom.button')) !!}</a>
					    	</div>
					  	</label>
					</div>
				</div>
			</div>

			<div class="alert mobile-alert alert-warning widget-custom-reviews-alert" style="display: none;">
				{!! nl2br(trans('trp.popup.popup-widget.reviews.custom.error')) !!}
			</div>
			<div class="alert mobile-alert alert-warning widget-custom-reviews-fb-alert" style="display: none;">
				{!! nl2br(trans('trp.popup.popup-widget.reviews.fb-custom.error')) !!}
			</div>

			<div class="alert fbtab-alert" style="display: none; margin-top: 20px; margin-bottom: 20px;">{!! nl2br(trans('trp.popup.popup-widget.reviews.fb.error')) !!}</div>

			<div class="tac get-widget-code-wrap">
				<a href="javascript:;" class="button widget-button back-widget" to-step="1">< {!! nl2br(trans('trp.page.invite.popup.success.button')) !!}</a>
				<a href="javascript:;" class="button get-widget-code hide-fb">{!! nl2br(trans('trp.popup.popup-widget.get-code')) !!}</a>
				<a href="javascript:;" class="button fb-tab-submit show-fb" fb-url="{{ getLangUrl('dentist-fb-tab') }}">{!! nl2br(trans('trp.popup.popup-widget.done')) !!}</a>
			</div>

			<div class="widget-last-step" style="display: none;">

				<h4 class="widget-subtitle">• {!! nl2br(trans('trp.popup.popup-widget.widget-options.title')) !!}</h4>
				<div class="alert mobile-alert alert-warning widget-tab-alert" style="display: none;">
					{!! nl2br(trans('trp.popup.popup-widget.widget-options.subtitle')) !!}
				</div>

				<div class="popup-tabs widget-tabs flex flex-mobile">
					<a class="active col" href="javascript:;" data-widget="simple">
						{!! nl2br(trans('trp.popup.popup-widget.simple')) !!}
						
						<p>
							{!! nl2br(trans('trp.popup.popup-widget.simple-hint')) !!}
						</p>
					</a>
					<a class="col" href="javascript:;" data-widget="flexible">
						{!! nl2br(trans('trp.popup.popup-widget.flexible')) !!}
						
						<p>
							{!! nl2br(trans('trp.popup.popup-widget.flexible-hint')) !!}
						</p>
					</a>
				</div>

				<div class="widget-wrapper">

					<div id="widget-option-simple" class="widget-content" style="">
						<div id="option-iframe" class="option-div">
							<span class="option-span"><b>01</b>
								{!! nl2br(trans('trp.popup.popup-widget.step-2-simple-1')) !!}
							</span>
							<span class="option-span"><b>02</b>
								{!! nl2br(trans('trp.popup.popup-widget.step-2-simple-2')) !!}
							</span>
							<span class="option-span"><b>03</b>
								{!! nl2br(trans('trp.popup.popup-widget.step-2-simple-3')) !!}
							</span>
							<span class="option-span"><b>04</b>
								{!! nl2br(trans('trp.popup.popup-widget.step-2-simple-4')) !!}
							</span>
							<span class="option-span"><b>05</b>
								{!! nl2br(trans('trp.popup.popup-widget.step-2-simple-5')) !!}
							</span>
							<span class="option-span"><b>06</b>
								{!! nl2br(trans('trp.popup.popup-widget.step-2-simple-6')) !!}
							</span>
							<span class="option-span"><b>07</b>
					  			{!! nl2br(trans('trp.popup.popup-widget.step-3')) !!}				  			
					  		</span>
							<br/><br/>
							<p>
								<a href="{{ url('img-trp/iframe-instructions.png') }}" data-lightbox="widget-lightbox" class="instructions-image">
									<img src="{{ url('img-trp/iframe-instructions.png') }}" style="width: 100%;" />
									<img class="magnifier" src="{{ url('img-trp/magnifier.png') }}">
								</a>
							</p>
							<div class="widget-code-wrap">
								<a href="javascript:;" class="copy-widget">
									<i class="far fa-copy"></i>
								</a>
					  			<textarea class="input select-me">{{ getLangUrl('widget/'.$user->id.'/'.$user->get_widget_token().'/0') }}</textarea>
					  		</div>
						</div>					
					</div>

					<div id="widget-option-flexible" class="widget-content" style="display: none;">
						<div id="option-js" class="option-div">
					  		<span class="option-span"><b>01</b>
					  			{!! nl2br(trans('trp.popup.popup-widget.step-2-flexible-1')) !!}
					  		</span>
							<span class="option-span"><b>02</b>
					  			{!! nl2br(trans('trp.popup.popup-widget.step-2-flexible-2')) !!}
					  		</span>
							<span class="option-span"><b>03</b>
					  			{!! nl2br(trans('trp.popup.popup-widget.step-2-flexible-3')) !!}				  			
					  		</span>
							<span class="option-span"><b>04</b>
					  			{!! nl2br(trans('trp.popup.popup-widget.step-3')) !!}				  			
					  		</span>
							<br/><br/>
							<div class="widget-code-wrap">
								<a href="javascript:;" class="copy-widget">
									<i class="far fa-copy"></i>
								</a>
					  			<textarea class="input select-me">{{ getLangUrl('widget/'.$user->id.'/'.$user->get_widget_token().'/0') }}</textarea>
					  		</div>
						</div>
					</div>
				</div>
				<div class="tac">
					<a href="javascript:;" class="button widget-button back-widget" to-step="1">< {!! nl2br(trans('trp.page.invite.popup.success.button')) !!}</a>
					<a href="javascript:;" class="button close-popup">{!! nl2br(trans('trp.popup.popup-widget.done')) !!}</a>
				</div>
			</div>


		</div>
	</div>
</div>


<script type="text/javascript">
	var widet_url = '{{ getLangUrl('widget-new/'.$user->id.'/'.$user->get_widget_token()) }}'
</script>


<div class="popup fixed-popup" id="select-reviews-popup">
	<div class="popup-inner inner-white">
		<div class="popup-pc-buttons">
			<a href="javascript:;" class="close-popup"><i class="fas fa-times"></i></a>
		</div>

		<div class="popup-mobile-buttons">
			<a href="javascript:;" class="close-popup">< {!! nl2br(trans('trp.common.back')) !!}</a>
		</div>
		<h2>
			{!! nl2br(trans('trp.popup.popup-widget.title')) !!}
		</h2>

		<h4 class="popup-title">
			{!! nl2br(trans('trp.popup.popup-widget.subtitle')) !!}
			
		</h4>

		<div class="list-reviews">
			@foreach($item->reviews_in_standard() as $review)
				<label class="checkbox-label" for="widget-review-{{ $review->id }}">
					<div class="widget-checkbox">
						<input type="checkbox" name="widget-custom-review" id="widget-review-{{ $review->id }}" class="special-checkbox" value="{{ $review->id }}">
						<i class="far fa-square"></i>
					</div>
					<div class="list-review">
						<div class="list-review-left">
							<div class="review-avatar" style="background-image: url('{{ $review->user->getImageUrl(true) }}');"></div>
							<span class="review-date">
								{{ $review->created_at ? date('d/m/Y', $review->created_at->timestamp) : '-' }}
							</span>
						</div>
						<div class="list-review-right">
							@if($review->title)
				    			<span class="review-title">
				    				“{{ $review->title }}”
				    			</span>
			    			@endif
			    			<div class="ratings">
								<div class="stars">
									<div class="bar" style="width: {{ $review->rating/5*100 }}%;">
									</div>
								</div>
							</div>
							<div class="review-content">
								{!! nl2br($review->answer) !!}
							</div>
							<span class="review-name">{{ !empty($review->user->self_deleted) ? ($review->verified ? trans('trp.common.verified-patient') : trans('trp.common.deleted-user')) : $review->user->name }}</span>
							<span class="mobile-review-date">
								{{ $review->created_at ? date('d/m/Y', $review->created_at->timestamp) : '-' }}
							</span>
						</div>
					</div>
				</label>
	    	@endforeach
		</div>

		<div class="tac">
			<a href="javascript:;" class="button close-popup">{!! nl2br(trans('trp.popup.popup-widget.done')) !!}</a>
		</div>
	</div>
</div>

<div class="popup fixed-popup" id="facebook-tab-success">
	<div class="popup-inner inner-white">
		<div class="popup-pc-buttons">
			<a href="javascript:;" class="close-popup"><i class="fas fa-times"></i></a>
		</div>

		<div class="popup-mobile-buttons">
			<a href="javascript:;" class="close-popup">< {!! nl2br(trans('trp.common.back')) !!}</a>
		</div>
		<div class="header-success-tab tac">
			<img src="{{ url('img-trp/verification-check.png') }}">
			<h2>{!! nl2br(trans('trp.popup.popup-widget.success.title')) !!}</h2>
			<h4>{!! nl2br(trans('trp.popup.popup-widget.success.subtitle')) !!}</h4>
			<a href="javascript:;" class="button close-popup">{!! nl2br(trans('trp.popup.popup-claim-profile.thank-you.ok')) !!}</a>
		</div>
	</div>
</div>