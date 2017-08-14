<li data-jstree='{ "opened":true }' >
    <span data-id="{{ $item->id }}">
        {{ $item->title }}
    </span>
    @if(!empty($children))
    	<ul>
        	@foreach($children as $cp)
    			@include('admin.parts.tree-node-menu', [
                    'item' => $cp
                ])
        	@endforeach
		</ul>
    @endif
</li>
