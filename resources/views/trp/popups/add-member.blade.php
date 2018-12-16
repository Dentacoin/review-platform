<div class="popup fixed-popup" id="add-team-popup">
	<div class="popup-inner inner-white">
		<div class="popup-pc-buttons">
			<a href="javascript:;" class="close-popup"><i class="fas fa-times"></i></a>
		</div>

		<div class="popup-mobile-buttons">
			<a href="javascript:;" class="close-popup">< back</a>
		</div>
		<h2>Add Team Member</h2>

		<h4 class="popup-title">Show the world your dream team! </h4>

		<p class="popup-desc">
			Inviting all dentists in your clinic to link their profile with your main clinic's profile will make it easier for patients to find the right specialist. 
		</p>

		<div class="search-dentist">
			<i class="fas fa-search"></i>
			<input type="text" class="input" name="search-dentist" placeholder="Search for registered dentists...">
		</div>

		<div id="invite-option-email" class="invite-content">
			<p class="info">
				<img src="img/info.png"/> You couldn't find anything? Invite your team members to register via email.
			</p>

			{!! Form::open(array('method' => 'post', 'id' => 'invite-patient-form', 'class' => 'search-dentist-form', 'url' => getLangUrl('profile/invite') )) !!}
				<div class="flex">
					<div class="col">
						<input type="text" name="name" placeholder="Name" class="input">
					</div>
					<div class="col">
						<input type="email" name="email" placeholder="Email address" class="input">
					</div>
				</div>

				<!--
				<a href="javascript:;" class="add-dentist">+ Add another dentist</a>
			-->
				<div class="tac">
					<input type="submit" class="button" value="Send">
				</div>
			{!! Form::close() !!}

			<h4 class="popup-title">Invited dentists:</h4>

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
				<button type="submit" class="button">Add to your team</button>
			</div>
		</div>
	</div>
</div>