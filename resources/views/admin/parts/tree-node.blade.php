<li data-jstree='{ "opened":true @if(!$item->is_category), "icon" : "fa fa-file fa-lg text-primary" @endif }' >
    <a href="{{ url('cms/pages/edit/'.$item->id) }}">
        {{ $item->title }}
        @if($item->child_pages->isEmpty())
            <span class="remover" data-href="{{ url('cms/pages/delete/'.$item->id) }}">
                <i class="fa fa-remove"></i>
            </span>
        @endif
    </a>
    @if(!$item->child_pages->isEmpty())
    	<ul>
    	@foreach($item->child_pages as $cp)
			@include('admin.parts.tree-node', [
                'item' => $cp
            ])
    	@endforeach
		</ul>
    @endif
</li>
