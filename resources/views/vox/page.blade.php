@extends('vox')

@section('content')

@if($page->slug=='index')
<div class="index">
	<div class="index-logo">
		<div class="slogan">
			{{ trans('front.index.slogan') }}
		</div>
		<a class="enter" href="javascript:;">
			{{ trans('front.index.enter') }}
		</a>
	</div>
</div>
@endif




<div class="main-content single-page">

	@if($page->hasimage)
		<img class="page-image" src="{{ $page->getImageUrl() }}">
	@endif

	@if(!empty($page['content']) && is_array(json_decode( $page['content'], true)))
		@foreach(json_decode( $page['content'], true) as $block_key => $block)

			@if($block['type']=='html')
				<div class="full-width {{ !empty($block['padding']) ? 'p-'.$block['padding'] : '' }}"  style="
					{!! !empty($block['image']) ? 'background-image: url('.$block['image']->piclink('big').'); ' : ''  !!}
					{!! !empty($block['background']) ? 'background-color: '.$block['background'].'; ' : ''  !!}
					{!! !empty($block['color']) ? 'color: '.$block['color'].'; ' : ''  !!}
				">
					<div class="container clearfix">
						{!! $block['content'] !!}
					</div>
				</div>


			@elseif($block['type']=='map')
				<div class="map" data-address="{{ $block['address'] }}">
				</div>


			@elseif($block['type']=='html-2')
				<div class="full-width {{ !empty($block['padding']) ? 'p-'.$block['padding'] : '' }}"  style="
					{!! !empty($block['image']) ? 'background-image: url('.$block['image']->piclink('big').'); ' : ''  !!}
					{!! !empty($block['background']) ? 'background-color: '.$block['background'].'; ' : ''  !!}
				">
					<div class="container clearfix">
						<div class="col-md-6" style="
	                        {!! !empty($block['columns'][0]['background']) ? 'background-color: '.$block['columns'][0]['background'].' ; ' : ''  !!}
	                	">
	                	{!! $block['columns'][0]['content'] !!}
	                	</div>
	                	<div class="col-md-6" style="
	                        {!! !empty($block['columns'][1]['background']) ? 'background-color: '.$block['columns'][1]['background'].' ; ' : ''  !!}
	                	">
	                	{!! $block['columns'][1]['content'] !!}
						</div>
					</div>

				</div>

			@elseif($block['type']=='html-3')
				<div class="full-width {{ !empty($block['padding']) ? 'p-'.$block['padding'] : '' }}"  style="
					{!! !empty($block['image']) ? 'background-image: url('.$block['image']->piclink('big').'); ' : ''  !!}
					{!! !empty($block['background']) ? 'background-color: '.$block['background'].'; ' : ''  !!}
				">
					<div class="container clearfix">
						<div class="col-md-4 {!! !empty($block['columns'][0]['class']) ? $block['columns'][0]['class'] : ''  !!}" style="
	                        {!! !empty($block['columns'][0]['background']) ? 'background-color: '.$block['columns'][0]['background'].' ; ' : ''  !!}
	                	">
	                	{!! $block['columns'][0]['content'] !!}
	                	</div>
	                	<div class="col-md-4 {!! !empty($block['columns'][1]['class']) ? $block['columns'][1]['class'] : ''  !!}" style="
	                        {!! !empty($block['columns'][1]['background']) ? 'background-color: '.$block['columns'][1]['background'].' ; ' : ''  !!}
	                	">
	                	{!! $block['columns'][1]['content'] !!}
						</div>
						<div class="col-md-4 {!! !empty($block['columns'][2]['class']) ? $block['columns'][2]['class'] : ''  !!}" style="
	                        {!! !empty($block['columns'][2]['background']) ? 'background-color: '.$block['columns'][2]['background'].' ; ' : ''  !!}
	                	">
	                	{!! $block['columns'][2]['content'] !!}
	                	</div>
		            </div>

	            </div>

			@elseif($block['type']=='html-4')
				<div class="full-width {{ !empty($block['padding']) ? 'p-'.$block['padding'] : '' }}"  style="
					{!! !empty($block['image']) ? 'background-image: url('.$block['image']->piclink('big').'); ' : ''  !!}
					{!! !empty($block['background']) ? 'background-color: '.$block['background'].'; ' : ''  !!}
				">
					<div class="container clearfix">
						<div class="col-md-3" style="
	                        {!! !empty($block['columns'][0]['background']) ? 'background-color: '.$block['columns'][0]['background'].' ; ' : ''  !!}
	                	">
	                	{!! $block['columns'][0]['content'] !!}
	                	</div>
	                	<div class="col-md-3" style="
	                        {!! !empty($block['columns'][1]['background']) ? 'background-color: '.$block['columns'][1]['background'].' ; ' : ''  !!}
	                	">
	                	{!! $block['columns'][1]['content'] !!}
						</div>
						<div class="col-md-3" style="
	                        {!! !empty($block['columns'][2]['background']) ? 'background-color: '.$block['columns'][2]['background'].' ; ' : ''  !!}
	                	">
	                	{!! $block['columns'][2]['content'] !!}
	                	</div>
	                	<div class="col-md-3" style="
	                        {!! !empty($block['columns'][3]['background']) ? 'background-color: '.$block['columns'][3]['background'].' ; ' : ''  !!}
	                	">
	                	{!! $block['columns'][3]['content'] !!}
						</div>

					</div>
				</div>

			@elseif($block['type']=='children')
			</div>
			<div class="main-content page-list">
				<div class="full-width">
					<div class="container clearfix">
						@if(!empty($block['title']))
							<h1> {{ $block['title'] }} </h1>
						@endif
						@foreach($page->child_pages as $blog_post)
							<a class="blog-post clearfix" href=" {{ getLangUrl($blog_post['slug']) }}" > 
								<img src="{{ !empty($blog_post->image) ? $blog_post->image->piclink('thumb') : url('front/img/no-pic.jpg') }}"> 
								<div> 
									<h2> {{ $blog_post['title'] }} </h2> 
									{{ $blog_post['description'] }}
								</div>  
							</a>
						@endforeach
					</div>
				</div>
			</div>
			<div class="main-content single-page">		
			@elseif($block['type']=='contact')
				<div class="full-width {{ !empty($block['padding']) ? 'p-'.$block['padding'] : '' }}">
					<div class="container clearfix">
						<form method="post" class="contact-form" action="{{ getLangUrl('ajax/contact') }}">
							{{ csrf_field() }}
							@if(!empty($block['title']))
								<div class="col-md-12">
									<h2 class="title"> {{ $block['title'] }} </h2>
			                	</div>
							@endif
							<div class="col-md-6">
								<textarea name="message" class="message" placeholder="{!! $block['message'] !!}"></textarea>
		                	</div>
		                	<div class="col-md-6">
		                		<select name="dropdown" class="dropdown">
		                			@if(!empty($block['dropdown']))
										@foreach( explode(',' , $block['dropdown']) as $option )
											<option value="{{ $loop->first ? '' : $option }}">
												{{ $option }}
											</option>
										@endforeach
									@endif
								</select>
		            			<input type="text" name="name" class="name" placeholder="{!! $block['name'] !!}"> 
		            			<input type="text" name="email" class="email" placeholder="{!! $block['email'] !!}">
		            			<input type="submit" value="{!! $block['submit'] !!}">
							</div>
		                	<div class="col-md-12">
								<div class="alert success" style="display: none;">
									{!! $block['success'] !!}
								</div>
								<div class="alert error" style="display: none;">
									{!! $block['error'] !!}
								</div>
							</div>
						</form>
					</div>
				</div>

			@elseif($block['type']=='gallery')
				@if($block['gallery_type']=='classic')
					<div class="full-width gallery-classic {{ !empty($block['padding']) ? 'p-'.$block['padding'] : '' }}">
						<div class="container">
							<div class="gallery clearfix">
								@foreach($block['images'] as $image)
									<a href="{{ $image->piclink('big') }}" data-lightbox="gallery-group-{{ $block_key }}" class="col-3">
										<img src="{{ $image->piclink('thumb') }}">
									</a>
								@endforeach
							</div>
						</div>
					</div>
				@elseif($block['gallery_type']=='tiles')
					<div class="full-width gallery-tiles clearfix {{ !empty($block['padding']) ? 'p-'.$block['padding'] : '' }}">
						<div class="container">
							@foreach($block['images'] as $image)
								<a href="{{ $image->piclink('big') }}" data-lightbox="gallery-group-{{ $block_key }}">
									<div class="dark">
									</div>
									<img src="{{ $image->piclink('thumb') }}">
								</a>
							@endforeach
						</div>
					</div>
				@endif

			@endif

		@endforeach
	@endif
</div>

@endsection