@extends('vox')

@section('content')
	
	<div class="faq-wrapper">
		@foreach($content as $block)
			<div class="faq-block">
				<div class="container">
					<div class="ball{!! count($block['questions'])<=6 ? ' bigger' : '' !!}"></div> 
					@if($loop->first)
						<h1>{{ $block['title'] }}</h1>
					@else
						<h2>{{ $block['title'] }}</h2>
					@endif
					<div class="flex">
						@foreach( $block['questions'] as $question )
							<div class="col">
								<h3> <span>&bull;</span> {{ $question[0] }}</h3>
								<p>{!! nl2br($question[1]) !!}</p>
							</div>
							@if($loop->iteration && $loop->iteration%2==0 && !$loop->last)
								</div>
								<div class="flex">
							@endif
						@endforeach
					</div> 
				</div>
			</div>
		@endforeach
	</div>
    	
@endsection