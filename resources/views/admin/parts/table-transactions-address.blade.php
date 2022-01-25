<div style="display: flex;align-items: center;"> 
    {{ $item->address }} {!! $item->userWalletAddress ? ($item->userWalletAddress->is_deprecated ? '' : '<img style="max-width: 13px;margin-left: 10px;" src="'.url('img/alert-small-success.png').'"') : '' !!}
</div>