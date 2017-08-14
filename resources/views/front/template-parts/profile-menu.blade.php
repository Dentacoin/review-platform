<div class="panel panel-default">
	<div class="panel-heading">
		<h3 class="panel-title">
			{{ $user['name'] }}
		</h3>
	</div>
	<div class="panel-body">
		{{ Form::open(array('id' => 'avatar-add', 'class' => 'avatar-add form-horizontal '.( $user->hasimage ? 'has-image' : '' ), 'method' => 'post', 'url' => getLangUrl('profile/avatar'), 'files' => true)) }}
			<label for="add-avatar" id="avatar-uplaoder" class="user-avatar">
				{{ Form::file('image', ['id' => 'add-avatar']) }}		
				<div class="label">
					{{ trans('front.page.profile.add-avatar') }}
				</div>
				<div class="loader">
					<i class="fa fa-circle-o-notch fa-spin fa-3x fa-fw"></i>
					{{ trans('front.page.profile.avatar-uploading') }}
				</div>
				<img src="{{ $user->getImageUrl(true) }}" alt="{{ $user->name }}">
			</label>
			<div class="changer btn btn-block btn-primary">
				<i class="fa fa-pencil"></i>
				{{ trans('front.page.profile.change-avatar') }}
			</div>
			<a href="{{ getLangUrl('profile/remove-avatar') }}" class="deleter btn btn-block btn-default">
				<i class="fa fa-remove"></i>
				{{ trans('front.page.profile.remove-avatar') }}
			</a>
		{{ Form::close() }}
	
		<p>
			<i class="fa fa-user"></i>
			@if($user['is_dentist'] == 1) 
				{{ trans('front.common.dentist') }}
			@else
				{{ trans('front.common.patient') }}
			@endif
		</p>
		
		@if(!empty($user->country)) 
		<p>
			<i class="fa fa-map-marker"></i>
			@if(!empty($user->city)) 
				{{ $user->city->name }}, 
			@endif
			{{ $user->country->name }}
		</p>
		@endif
	</div>
</div>

<div class="list-group">
	@foreach($menu as $key => $profile_menu)
		<a href="{{ getLangUrl('profile/'.$key) }}" class="list-group-item {!! $current_subpage == $key ? 'active' : '' !!}">
			{{ $profile_menu }}
		</a>
	@endforeach
	@if($user->is_dentist)
		<a href="{{ $user->getLink() }}" class="list-group-item">
			{{ trans('front.page.profile.public-profile') }}
		</a>
	@endif
</div>