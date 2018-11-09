<a target="_blank" href="{{ $item->getLink() }}">
	{{ trans('admin.common.link-to-site') }}
</a>
&nbsp;
&middot;
&nbsp;
<a target="_blank" href="{{ url('cms/users/loginas/'.$item->id) }}">
	{{ trans('admin.common.login-as') }}
</a>