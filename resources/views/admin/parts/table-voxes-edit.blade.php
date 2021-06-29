<a class="btn btn-sm btn-primary" href="{{ url('cms/'.$current_page.'/edit/'.$item->id) }}">
    @if($admin->role!='support')
        <i class="fa fa-pencil"></i>
    @else
        <i class="fa fa-eye"></i>
    @endif
</a>