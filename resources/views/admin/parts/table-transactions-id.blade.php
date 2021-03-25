{{$item->id}}

@if($item->history->isNotEmpty())
	@if($item->history->count() == 1 && $item->history->first()->from_creating)

	@else
		<div class="trans-history-wrapper">
			<img src="{{ url('img/info.png') }}" style="max-width: 15px;">

			<div class="trans-history">
				History: <br/>
				@foreach($item->history as $history)
					<div>
						@if(!empty($history->sended_at))
							- Sended at: {{ $history->sended_at }} <br/>
						@endif
						@if(!empty($history->address))
							- Address: {{ $history->address }} <br/>
						@endif
						@if(!empty($history->tx_hash))
							- Tx hash: {{ $history->tx_hash }} <br/>
						@endif
						@if(!empty($history->allowance_hash))
							- Allowance hash: {{ $history->allowance_hash }} <br/>
						@endif
						@if(!empty($history->nonce))
							- Nonce: {{ $history->nonce }} <br/>
						@endif
						@if(!empty($history->status))
							- Status: {{ $history->status }} <br/>
						@endif
						@if(!empty($history->message))
							- PS Message: {{ $history->message }} <br/>
						@endif
						@if(!empty($history->history_message))
							{{ $history->history_message }} <br/>
						@endif
					</div>
				@endforeach
			</div>
		</div>
	@endif
@endif