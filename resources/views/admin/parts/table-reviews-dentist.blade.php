@if( $item->clinic_id )
	<a href="{{ url('/cms/users/users/edit/'.$item->clinic_id) }}">
		{{ $item->clinic ? $item->clinic->name : 'Deleted clinic' }}
	</a>
@endif
@if( $item->dentist_id )
	<a href="{{ url('/cms/users/users/edit/'.$item->dentist_id) }}">
		{{ $item->dentist ? $item->dentist->name : 'Deleted dentist' }}
	</a>
@endif
