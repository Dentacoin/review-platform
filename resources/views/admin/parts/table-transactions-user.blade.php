@if($item->type=='mobident')
	<span title="{{ addslashes($item->mobident->email.' / '.$item->mobident->city.' / '.$item->mobident->address) }}">{{ $item->mobident->name }} (Mobident)</span>
@else
	<a href="{{ url('/cms/users/users/edit/'.$item->user_id) }}">
		{{ !empty($item->user) ? $item->user->name : ''  }}
	</a>

	@if($item->status == 'first')
		<div class="user-info-wrapper">
			<div class="img-wrap user-info" user-id="{{ $item->user_id }}">
		        <img src="{{ url('img/info-green.png') }}" style="max-width: 15px;">
		    </div>

		    <div class="user-info-tooltip">
		    </div>
		</div>
	@endif
@endif