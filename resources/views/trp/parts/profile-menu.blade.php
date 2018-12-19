<div class="profile-menu">
	<div class="heading flex flex-center">
		<img src="{{ $user->getImageUrl(true) }}" />
		<div>
			<b>
				{!! nl2br(trans('trp.page.profile.menu.welcome', [ 'name' => $user->getName() ])) !!}
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
				{!! nl2br(trans('trp.page.profile.menu.back-to-public')) !!}
				
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
			{!! nl2br(trans('trp.page.profile.menu.logout')) !!}
			
		</a>
	</div>
</div>