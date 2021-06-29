<a target="_blank" href="{{ $item->user->getLink() }}">
	{{ trans('admin.common.link-to-site') }}
</a>
&nbsp;
&middot;
&nbsp;
<a target="_blank" href="{{ url('cms/users/users/loginas/'.$item->user->id) }}">
	{{ trans('admin.common.login-as') }}
</a>