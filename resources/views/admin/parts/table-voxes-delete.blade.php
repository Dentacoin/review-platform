@if($item->id != 11 && $item->id != 34)
    <a class="btn btn-sm btn-deafult" href="{{ url('cms/'.$current_page.'/delete/'.$item->id) }}" onclick="return confirm('Are you sure you want to DELETE this?');">
        {{ trans('admin.table.delete') }}
    </a>
@endif