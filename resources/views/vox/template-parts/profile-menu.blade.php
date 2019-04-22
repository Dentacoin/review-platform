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
	<div class="menu-list"> 
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

<div class="back-to-site">
	<a class="blue-button" href="{{ getLangUrl('/') }}">
		{{ trans('vox.page.profile.menu.extra.take-surveys') }}
	</a>
	<a  class="blue-button" href="{{ getLangUrl('dental-survey-stats') }}">
		{{ trans('vox.page.profile.menu.extra.check-stats') }}
	</a>
</div>

<div class="custom-survey">
	<p>
		{!! nl2br(trans('vox.page.profile.menu.extra.custom-survey.title')) !!}
	</p>

	<p>
		{!! nl2br(trans('vox.page.profile.menu.extra.custom-survey.description')) !!}
	</p>
</div>