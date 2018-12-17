<div class="popup fixed-popup" id="popup-invite">
	<div class="popup-inner inner-white">
		<div class="popup-pc-buttons">
			<a href="javascript:;" class="close-popup"><i class="fas fa-times"></i></a>
		</div>

		<div class="popup-mobile-buttons">
			<a href="javascript:;" class="close-popup">< back</a>
		</div>
		<h2>Invite Patients</h2>

		<h4 class="popup-title">Learn and earn from your patients' feedback!</h4>

		<p class="popup-desc">
			Invite patients via email.<br/> 
			Each review, submitted after your invitation, will give you invaluable feedback to improve upon, as well as an amount of Dentacoin (DCN).
		</p>

		@if($user->dcn_address)
			@if(false)
				<div class="popup-tabs invite-tabs flex flex-mobile">
					<a class="active col" href="javascript:;" data-invite="email">
						Invite via email
					</a>
					<a class="col" href="javascript:;" data-invite="link">
						Get a referral link
					</a>
				</div>
			@endif
			<br/>
			<br/>

			<div id="invite-option-email" class="invite-content" style="">
				<p class="info">
					<img src="img/info.png"/>
					Just enter a name and email address and we’ll send an email with invitation link.
				</p>

				{!! Form::open(array('method' => 'post', 'id' => 'invite-patient-form', 'url' => getLangUrl('profile/invite') )) !!}
					{!! csrf_field() !!}
					<div class="flex">
						<div class="col">
							<input type="text" name="name" placeholder="Name" class="input" id="invite-name">
						</div>
						<div class="col">
							<input type="email" name="email" placeholder="Email address" id="invite-email" class="input">
						</div>
					</div>

					<div class="alert" id="invite-alert" style="display: none; margin-top: 20px;">
					</div>
					<!--
						<a href="javascript:;" class="add-patient">+ Add another patient</a>
					-->

					<div class="tac">
						<input type="submit" class="button" value="Send">
					</div>
				{!! Form::close() !!}
			</div>

			@if(false)
			<div id="invite-option-link" class="invite-content" style="display: none;">
				<p class="info">
					<img src="img/info.png"/>
					Below you’ll find your invitation link. Copy it and send it using your favorite instant messanger or social network.
				</p>

				<div class="flex">
					<div class="flex-10">
						<input type="text" id="invite-url" class="input select-me" name="link" value="{{ getLangUrl('invite/'.$user->id.'/'.$user->get_invite_token()) }}">
					</div>
					<div class="flex-2">
						<a class="copy-link button" href="javascript:;">
							Copy
						</a>
					</div>
				</div>
			</div>
			@endif
		@else
			<br/>
			<br/>
			<div class="invite-content" style="">
				<p class="info">
					<img src="img/info.png"/>
					<span>
						In order to send invitations and receive rewads, we need to know your DCN address. Enter yours below or <a href="{{ url('DentavoxMetamask.pdf') }}" target="_blank">click here</a> if you don't know how to create one.
					</span>
				</p>

				{!! Form::open(array('method' => 'post', 'id' => 'invite-no-address', 'url' => getLangUrl('profile') )) !!}
					{!! csrf_field() !!}
					<input type="text" name="vox-address" placeholder="Your DCN address" class="input" id="invite-name">
					<!--
						<a href="javascript:;" class="add-patient">+ Add another patient</a>
					-->

					<div class="tac">
						<input type="submit" class="button" value="Save">
					</div>

					<input type="hidden" name="json" value="1" />
					<div class="alert" id="invite-alert" style="display: none; margin-top: 20px;">
					</div>
				{!! Form::close() !!}
			</div>
		@endif
	</div>
</div>