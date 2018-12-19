@extends('trp')

@section('content')

	<div class="faq-title">
		<div class="container">
			<h1>
				{!! nl2br(trans('trp.page.faq.title')) !!}
			</h1>
		</div>
	</div>

	<div class="faq-wrapper">
		<div class="container">
			
			@foreach($content as $block)
				<h2>{{ $block['title'] }}</h2>
				<div class="questions">
					@foreach( $block['questions'] as $question )
						<div class="question">
			                <a class="question-title" href="javascript:;">
			                	<span>{{ str_pad($loop->iteration, 2, "0", STR_PAD_LEFT) }}</span>{{ $question[0] }}
			                </a>
			                <div class="question-description clearfix">
								<p>{!! nl2br($question[1]) !!}</p>
			                </div>
			            </div>
					@endforeach
				</div>
			@endforeach
		    	

		</div>
	</div>

@endsection