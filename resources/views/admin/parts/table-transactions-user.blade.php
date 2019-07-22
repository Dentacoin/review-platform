@if($item->type=='mobident')
	<span title="{{ addslashes($item->mobident->email.' / '.$item->mobident->city.' / '.$item->mobident->address) }}">{{ $item->mobident->name }} (Mobident)</span>
@else
	<a href="{{ url('/cms/users/edit/'.$item->user_id) }}">
		{{ !empty($item->user) ? $item->user->name : ''  }}
	</a>
@endif