<li data-jstree='{ "opened":true @if(!$item->is_category), "icon" : "fa fa-file fa-lg text-primary" @endif }' >
    <a>
        <span data-id="{{ $item->id }}">
            {{ $item->title }}
        </span>
    </a>
    @if(!$item->child_pages->isEmpty())
    	<ul>
    	@foreach($item->child_pages as $cp)
			@include('admin.parts.tree-node-menu', [
                'item' => $cp
            ])
    	@endforeach
		</ul>
    @endif
</li>
