<a target="_blank" href="{{ $item->getLink() }}">
	{{ $item->name }}
</a>
<br/>
<a target="_blank" href="{{ url('cms/users/loginas/'.$item->id.(!empty(request()->input('search-platform')) ? '/'.request()->input( 'search-platform' ) : '')) }}">
	{{ trans('admin.common.login-as') }}
</a>