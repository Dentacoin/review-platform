@foreach($answers as $key => $answer)
	<div class="all-votes">
	  	<div class="vote-answer">{{ $answer }}</div>
		<div class="vote-bar" data-votes="{{ !empty($answer_count[$key]) ? $answer_count[$key] : 0 }}"></div>
	</div>
@endforeach