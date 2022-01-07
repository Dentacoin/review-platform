@if($item->status == 'first')
    <a class="btn btn-primary btn-sm" href="{{ url('cms/transactions/bump/'.$item->id) }}">
        Approve
    </a>
    <a class="btn btn-danger btn-sm" href="{{ url('cms/transactions/stop/'.$item->id) }}" style="margin-top: 2px;">
        Reject
    </a>
    @if($item->user && !$item->user->is_dentist && $item->user->patient_status != 'suspicious_admin' && $item->user->patient_status != 'suspicious_badip')
        <a class="btn btn-warning make-user-suspicious btn-sm" href="javascript:;" data-toggle="modal" data-target="#suspiciousUserModal" user-id="{{ $item->user_id }}" style="margin-top: 2px;">
            Suspicious user
        </a>
    @endif
@endif