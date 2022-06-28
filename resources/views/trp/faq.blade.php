@extends('trp')

@section('content')

	<div class="faq-title-wrapper">
		<div class="container">
			<div class="faq-image-wrapper flex flex-center">
				<div class="faq-image">
					<img src="{{ url('img-trp/faq-image.png') }}" width="220" height="253"/>
				</div>
				<div>
					<h1 class="mont">
						{!! nl2br(trans('trp.page.faq.title')) !!}
					</h1>
				</div>
			</div>
			<div class="faq-sections-title flex">
				@foreach($content as $block)
					<a class="faq-section col {{ $loop->index == 0 ? 'active' : '' }}" href="javascript:;" id="section-{{ $loop->iteration }}">
						<img src="{{ url('img-trp/faq-'.strtolower(explode(' ',$block['title'])[0]).'.svg') }}" width="62" height="60"/>
						<p>{{ $block['title'] }}</p>
					</a>
				@endforeach
			</div>
		</div>
	</div>

	<div class="faq-wrapper">
		<div class="container">
			@foreach($content as $block)
				<div class="questions {{ $loop->index == 0 ? 'active' : '' }} section-{{ $loop->iteration }}">
					@if(isset($block['questions']))
						@foreach( $block['questions'] as $question )
							<div class="question {{ $loop->first ? 'active' : '' }}">
				                <a class="question-title" href="javascript:;">
				                	{{ $question[0] }}
									<img src="{{ url('img-trp/caret-green.svg') }}"/>
				                </a>
				                <div class="question-description clearfix">
									<p>{!! nl2br($question[1]) !!}</p>
				                </div>
				            </div>
						@endforeach
					@endif
				</div>
			@endforeach
		</div>
	</div>

@endsection