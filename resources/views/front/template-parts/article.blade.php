<div class="col-md-3">
	<a href="{{ getLangUrl($category['slug'].'/'.$article['slug']) }}">
		<div class="thumbnail">
			@if($article['youtube_id'])
				<div class="play">
					<span class="glyphicon glyphicon-play"></span>
				</div>
			@endif
			<img src="{{ $article->getImageUrl(true) }}"/>
			<div class="caption">
				<h3>{{ $article['title'] }}</h3>
				<p>{{ $article['publish_on']->format('H:i d.m.Y')}}</p>
			</div>
		</div>
	</a>
</div>