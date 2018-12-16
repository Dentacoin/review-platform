
<div class="popup fixed-popup" id="popup-widget">
	<div class="popup-inner inner-white">
		<div class="popup-pc-buttons">
			<a href="javascript:;" class="close-popup"><i class="fas fa-times"></i></a>
		</div>

		<div class="popup-mobile-buttons">
			<a href="javascript:;" class="close-popup">< back</a>
		</div>
		<h2>Display Your Reviews on Your Website</h2>

		<h4 class="popup-title">Trusted Reviews Website Widget</h4>

		<p class="popup-desc">
			Add your patients' reviews to your website!<br/>
			It’s super easy and requires (almost) no coding skills. Just choose one of the two options below and follow the instructions.
		</p>

		<div class="popup-tabs widget-tabs flex flex-mobile">
			<a class="active col" href="javascript:;" data-widget="simple">
				Simple Widget
				<p>for Wordpress websites, requires no coding</p>
			</a>
			<a class="col" href="javascript:;" data-widget="flexible">
				Flexible Widget
				<p>from any website, a bit of coding required</p>
			</a>
		</div>

		<div class="widget-wrapper">

			<div id="option-mode">
		  		<h3>1. Choose which reviews to show</h3>
				<p>
					We can show either all your reviews, or just those from your verified patients (trusted reviews)						
				</p>

				<div class="widget-options">
			  		<div class="radio-label">
					  	<label class="active" for="mode-all">
							<i class="far fa-circle"></i>
					    	<input class="widget-radio" type="radio" name="answer" id="mode-all" value="0" checked="checked">
					    	Display ALL reviews
					  	</label>
					</div>
					<div class="radio-label">
					  	<label for="mode-trusted">
							<i class="far fa-circle"></i>
					    	<input class="widget-radio" type="radio" name="answer" id="mode-trusted" value="1">
					    	Display ONLY TRUSTED reviews
					  	</label>
					</div>
			  	</div>		  					

			</div>

			<div id="widget-option-simple" class="widget-content" style="">
				<div id="option-iframe" class="option-div">
			  		<h3>2. Place the code below in your website</h3>
					<span class="option-span"><b>01</b>Copy the code below</span>
					<span class="option-span"><b>02</b>Log in to your website's Wordpress Admin Panel</span>
					<span class="option-span"><b>03</b>Open the Page or Post where you want to place the widget</span>
					<span class="option-span"><b>04</b>Switch the content editor to TEXT</span>
					<span class="option-span"><b>05</b>Paste the code where you want the widget to show</span>
					<span class="option-span"><b>06</b>Don't forget to hit the Update button</span>
					<br/><br/>
					<p>
						<img src="img/iframe-instructions.png" style="width: 100%;" />
					</p>
			  		<textarea class="input select-me">{{ getLangUrl('widget/'.$user->id.'/'.$user->get_widget_token().'/0') }}</textarea>

				</div>
				
			</div>

			<div id="widget-option-flexible" class="widget-content" style="display: none;">
				<div id="option-js" class="option-div">

			  		<h3>2. Place the code below in your website</h3>
			  		<span class="option-span"><b>01</b>Copy the code below and paste it in your website's HTML code.</span>
					<span class="option-span"><b>02</b>The widget will be displayed where you've pasted the code. I.e. if you want it in the Footer or in the Sidebar - paste the code in the appropriate place in your page.</span>
					<span class="option-span"><b>03</b>The widget will take the width of its parent element and has a resopnsive (mobile friendly) behavior. </span>
					<br/><br/>
			  		<textarea class="input select-me">{{ getLangUrl('widget/'.$user->id.'/'.$user->get_widget_token().'/0') }}</textarea>

				</div>
			</div>

			<div id="widget-preview">
		  		<h3>3. Reload your website and you should see the widget</h3>
				
			</div>
		</div>
	</div>
</div>


<script type="text/javascript">
	var widet_url = '{{ getLangUrl('widget/'.$user->id.'/'.$user->get_widget_token().'/{mode}') }}'
</script>