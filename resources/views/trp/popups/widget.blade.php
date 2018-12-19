
<div class="popup fixed-popup" id="popup-widget">
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

		<p class="popup-desc">
			{!! nl2br(trans('trp.popup.popup-widget.hint')) !!}
		</p>

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

			<div id="option-mode">
		  		<h3>
		  			{!! nl2br(trans('trp.popup.popup-widget.step-1')) !!}
		  			
		  		</h3>
				<p>
					{!! nl2br(trans('trp.popup.popup-widget.step-1-hint')) !!}
					
				</p>

				<div class="widget-options">
			  		<div class="radio-label">
					  	<label class="active" for="mode-all">
							<i class="far fa-circle"></i>
					    	<input class="widget-radio" type="radio" name="answer" id="mode-all" value="0" checked="checked">
					    	{!! nl2br(trans('trp.popup.popup-widget.step-1-all')) !!}
					    	
					  	</label>
					</div>
					<div class="radio-label">
					  	<label for="mode-trusted">
							<i class="far fa-circle"></i>
					    	<input class="widget-radio" type="radio" name="answer" id="mode-trusted" value="1">
					    	{!! nl2br(trans('trp.popup.popup-widget.step-1-trusted')) !!}
					    	
					  	</label>
					</div>
			  	</div>		  					

			</div>

			<div id="widget-option-simple" class="widget-content" style="">
				<div id="option-iframe" class="option-div">
			  		<h3>
			  			{!! nl2br(trans('trp.popup.popup-widget.step-2-simple')) !!}
			  			
			  		</h3>
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
					<br/><br/>
					<p>
						<img src="{{ url('img-trp/iframe-instructions.png') }}" style="width: 100%;" />
					</p>
			  		<textarea class="input select-me">{{ getLangUrl('widget/'.$user->id.'/'.$user->get_widget_token().'/0') }}</textarea>

				</div>
				
			</div>

			<div id="widget-option-flexible" class="widget-content" style="display: none;">
				<div id="option-js" class="option-div">

			  		<h3>
			  			{!! nl2br(trans('trp.popup.popup-widget.step-2-flexible')) !!}
			  			
			  		</h3>
			  		<span class="option-span"><b>01</b>
			  			{!! nl2br(trans('trp.popup.popup-widget.step-2-flexible-1')) !!}
			  			
			  		</span>
					<span class="option-span"><b>02</b>
			  			{!! nl2br(trans('trp.popup.popup-widget.step-2-flexible-2')) !!}
			  			
			  		</span>
					<span class="option-span"><b>03</b>
			  			{!! nl2br(trans('trp.popup.popup-widget.step-2-flexible-3')) !!}
			  			
			  		</span>
					<br/><br/>
			  		<textarea class="input select-me">{{ getLangUrl('widget/'.$user->id.'/'.$user->get_widget_token().'/0') }}</textarea>

				</div>
			</div>

			<div id="widget-preview">
		  		<h3>
		  			{!! nl2br(trans('trp.popup.popup-widget.step-3')) !!}
		  			
		  		</h3>
				
			</div>
		</div>
	</div>
</div>


<script type="text/javascript">
	var widet_url = '{{ getLangUrl('widget/'.$user->id.'/'.$user->get_widget_token().'/{mode}') }}'
</script>