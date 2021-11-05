@if( $item->question_id )
	<a href="{{ url('cms/vox/edit/'.$item->ban_for_id.'/question/'.$item->question_id.'/') }}">{{ $item->question ? $item->question->question : '' }}</a>
@endif