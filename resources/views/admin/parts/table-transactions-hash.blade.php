<div class="edit-mode">
	<form action="{{ url('cms/transactions/edit') }}" class="edit-trans-form">
		<input type="hidden" name="trans-id" value="{{ $item->id }}">
		@if($item->is_paid_by_the_user)
			<div class="edit-mode">
				Approval: <textarea name="allowance_hash">{{ $item->allowance_hash }}</textarea> <br/>
				Funds sent: <textarea name="tx_hash">{{ $item->tx_hash }}</textarea>
			</div>
		@else
			@if($item->tx_hash)
				<div class="edit-mode">
					<textarea name="tx_hash">{{ $item->tx_hash }}</textarea>
				</div>
			@else
				<div class="edit-mode">
					<textarea name="tx_hash">{{ $item->tx_hash }}</textarea>
				</div>
			@endif
		@endif

		<button type="submit" class="btn btn-success submit-edit" href="javascript:;">Save</button>
	</form>
</div>
<div class="normal-mode">
	@if($item->is_paid_by_the_user)
		<div class="normal-mode">
			Approval: {!! $item->allowance_hash ? '<a href="https://etherscan.io/tx/'.$item->allowance_hash.'" target="_blank">'.$item->allowance_hash.'</a>' : '-' !!} <br/>
			Funds sent: {!! $item->tx_hash ? '<a href="https://etherscan.io/tx/'.$item->tx_hash.'" target="_blank">'.$item->tx_hash.'</a>' : '-' !!}
		</div>
	@else
		@if($item->tx_hash)
			<div class="normal-mode">
				<a href="https://etherscan.io/tx/{{ $item->tx_hash }}" target="_blank">
					{{ $item->tx_hash }}
				</a>
			</div>
		@else
			<div class="normal-mode">
				-
			</div>
		@endif
	@endif
</div>