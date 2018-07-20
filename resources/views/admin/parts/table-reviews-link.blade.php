@if( $item->dentist_id )
	<a target="_blank" href="{{ url('/cms/users/edit/'.$item->dentist_id) }}">
		Dentist
	</a>
@endif
@if( $item->clinic_id )
	<a target="_blank" href="{{ url('/cms/users/edit/'.$item->clinic_id) }}">
		Clinic
	</a>
@endif
