<div class="profile-menu">
	<div class="heading flex flex-center">
		<img src="{{ $user->getImageUrl(true) }}" />
		<div>
			<b>
				Welcome, <br/>
				{{ $user->name }}
			</b>
			@if(!empty($admin))
				Admin
			@endif
		</div>
	</div>
	<div class="mobile-shadow">
	</div>
	<div class="menu-list"> 
		@if($user->is_dentist)
			<a href="{{ $user->getLink() }}" class="list-item list-item-mobile">
				<img src="{{ url('new-vox-img/profile-profile.png') }}" />
				Back to public profile
			</a>
		@endif

		@foreach($menu as $key => $profile_menu)
			<a href="{{ getLangUrl('profile/'.$key) }}" class="list-item {!! $current_subpage == $key ? 'active' : '' !!}">
				<img src="{{ url('new-vox-img/profile-'.$key.'.png') }}" />
				{{ $profile_menu }}
			</a>
		@endforeach
		<a href="{{ getLangUrl('logout') }}" class="list-item">
			<img src="{{ url('new-vox-img/profile-logout.png') }}" />
			Log out
		</a>
	</div>
</div>