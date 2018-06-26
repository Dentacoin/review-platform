@foreach($item->answers as $answer)
	<div class="panel-body rating-panel">
		<h2>{{ $answer->question['label'] }}</h2>
    	@foreach(json_decode($answer->question['options'], true) as $i => $option)
        	<div class="rating-line clearfix">
            	<div class="rating-left">
            		{{ $option[0] }}
            	</div>

            	<div class="rating-right">
            		{{ $option[1] }}
            	</div>

				<div class="ratings">
					<div class="stars">
						<div class="bar" style="width: {{ getStarWidth(json_decode($answer->options, true)[$i]) }}px;">
						</div>
					</div>
				</div>
        	</div>
    	@endforeach
	</div>
@endforeach