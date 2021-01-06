<!-- <a target="_blank" href="{{ $item->getLink() }}">
	{{ trans('admin.common.link-to-site') }}
</a>
&nbsp;
&middot;
&nbsp;
<a target="_blank" href="{{ url('cms/users/loginas/'.$item->id) }}">
	{{ trans('admin.common.login-as') }}
</a> -->

@if($item->id == 113928)
@else
	<a target="_blank" href="{{ url('cms/users/loginas/'.$item->id.(!empty(request()->input('search-platform')) ? '/'.request()->input( 'search-platform' ) : '')) }}">
		{{ trans('admin.common.login-as') }}
	</a>
@endif