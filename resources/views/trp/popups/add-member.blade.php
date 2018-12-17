<div class="popup fixed-popup" id="add-team-popup">
	<div class="popup-inner inner-white">
		<div class="popup-pc-buttons">
			<a href="javascript:;" class="close-popup"><i class="fas fa-times"></i></a>
		</div>

		<div class="popup-mobile-buttons">
			<a href="javascript:;" class="close-popup">< back</a>
		</div>
		<h2>
			{!! nl2br(trans('trp.popup.add-team-popup.title')) !!}
			
		</h2>

		<h4 class="popup-title">
			{!! nl2br(trans('trp.popup.add-team-popup.subtitle')) !!}
			
		</h4>

		<p class="popup-desc">
			{!! nl2br(trans('trp.popup.add-team-popup.hint')) !!}
			
		</p>

		<div class="search-dentist">
			<i class="fas fa-search"></i>
			<input type="text" class="input" name="search-dentist" placeholder="{!! nl2br(trans('trp.popup.add-team-popup.search')) !!}">
		</div>

		<div id="invite-option-email" class="invite-content">
			<p class="info">
				<img src="img/info.png"/>
				{!! nl2br(trans('trp.popup.add-team-popup.invite')) !!}
				 
			</p>

			{!! Form::open(array('method' => 'post', 'id' => 'invite-patient-form', 'class' => 'search-dentist-form', 'url' => getLangUrl('profile/invite') )) !!}
				<div class="flex">
					<div class="col">
						<input type="text" name="name" placeholder="{!! nl2br(trans('trp.popup.add-team-popup.name')) !!}" class="input">
					</div>
					<div class="col">
						<input type="email" name="email" placeholder="{!! nl2br(trans('trp.popup.add-team-popup.email')) !!}" class="input">
					</div>
				</div>

				<!--
				<a href="javascript:;" class="add-dentist">+ Add another dentist</a>
			-->
				<div class="tac">
					<input type="submit" class="button" value="{!! nl2br(trans('trp.popup.add-team-popup.send')) !!}">
				</div>
			{!! Form::close() !!}

			<h4 class="popup-title">
				{!! nl2br(trans('trp.popup.add-team-popup.list')) !!}
				
			</h4>

			<div class="invited-dentists">
				<div class="flex">
					<div class="flex-3">
						Mr. Kenneth Burrett
					</div>
					<div class="flex-7">
						kennyburrett@aol.com
					</div>
					<div class="flex-2 tar">
						<a class="remove-dentist" href="javascript:;">
							<i class="fas fa-times-circle"></i>
							Remove
						</a>
					</div>
				</div>
				<div class="flex">
					<div class="flex-3">
						Mr. Kenneth Burrett
					</div>
					<div class="flex-7">
						kennyburrett@aol.com
					</div>
					<div class="flex-2 tar">
						<a class="remove-dentist" href="javascript:;">
							<i class="fas fa-times-circle"></i>
							Remove
						</a>
					</div>
				</div>
			</div>

			<div class="tac">
				<button type="submit" class="button">
					{!! nl2br(trans('trp.popup.add-team-popup.add')) !!}
					
				</button>
			</div>
		</div>
	</div>
</div>