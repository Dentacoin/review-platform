@if( $item->clinic_id )
	<a href="{{ url('/cms/users/edit/'.$item->clinic_id) }}">
		{{ $item->clinic->name }}
	</a>
@endif
@if( $item->dentist_id )
	<a href="{{ url('/cms/users/edit/'.$item->dentist_id) }}">
		{{ $item->dentist->name }}
	</a>
@endif
