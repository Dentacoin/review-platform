<div class="panel panel-default">
	<div class="panel-heading">
		<h3 class="panel-title tac bold">
			{{ $user->name }}
		</h3>
	</div>
	<div class="panel-body">
		<p class="tac city">
			{{ trans('vox.page.profile.balance') }}
		</p>
		<div class="price">
			<img src="{{ url('img-vox/dc-logo.png') }}"/>
			<span class="coins" id="menu-balance">{{ $user->getVoxBalance() }}</span>
		</div>
	</div>
</div>

<div class="list-group">

	@foreach($menu as $key => $profile_menu)
		<a href="{{ getLangUrl('profile/'.$key) }}" class="list-group-item {!! $current_subpage == $key ? 'active' : '' !!}">
			{{ $profile_menu }}
		</a>
	@endforeach
</div>	