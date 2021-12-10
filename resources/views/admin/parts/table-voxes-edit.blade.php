<a class="btn btn-sm btn-primary" href="{{ url('cms/'.$current_page.'/edit/'.$item->id) }}">
    @if($admin->role!='support')
        <i class="fa fa-pencil"></i>
    @else
        <i class="fa fa-eye"></i>
    @endif
</a>

@if($admin->role!='support')
{{-- <br/>
    <a class="btn btn-sm btn-success diplicate-q-button" onclick="return confirm('Are you sure you want to duplicate this survey?');" href="{{ url('cms/vox/duplicate/'.$item->id) }}" style="margin-top: 5px;">
        <i class="fa fa-paste"></i>
    </a> --}}
@endif