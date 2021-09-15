<div class="popup fixed-popup popup-with-background recommend-popup close-on-shield active" id="recommend-popup" scss-load="vox-popup-recommend-dv">
	<div class="popup-inner inner-white">
		<a href="javascript:;" class="closer">
			<img src="{{ url('new-vox-img/close-popup.png') }}">
		</a>
		<div class="flex flex-mobile flex-center break-tablet">
			<div class="content">
				<p class="h1">
					{!! nl2br(trans('vox.popup.recommend.title')) !!}
				</p>

				<form class="form" action="{{ getLangUrl('recommend') }}" method="post" id="recommend-form">
					{!! csrf_field() !!}

					<div class="hide-on-success">
						<div class="recommend-icons flex alert-after">
							<label for="scale-1">
								<img src="{{ url('new-vox-img/face-1.svg') }}">
								<input class="recommend-radio" type="radio" name="scale" id="scale-1" value="1">
							</label>
							<label for="scale-2">
								<img src="{{ url('new-vox-img/face-2.svg') }}">
								<input class="recommend-radio" type="radio" name="scale" id="scale-2" value="2">
							</label>
							<label for="scale-3">
								<img src="{{ url('new-vox-img/face-3.svg') }}">
								<input class="recommend-radio" type="radio" name="scale" id="scale-3" value="3">
							</label>
							<label for="scale-4">
								<img src="{{ url('new-vox-img/face-4.svg') }}">
								<input class="recommend-radio" type="radio" name="scale" id="scale-4" value="4">
							</label>
							<label for="scale-5">
								<img src="{{ url('new-vox-img/face-5.svg') }}">
								<input class="recommend-radio" type="radio" name="scale" id="scale-5" value="5">
							</label>
						</div>

						<div class="hide-happy" style="display: none;">
							<div class="modern-field alert-after">
								<textarea name="description" id="recommend-description" class="modern-input" autocomplete="off" readonly onfocus="this.removeAttribute('readonly');"></textarea>
								<label for="recommend-description">
									<span>{!! nl2br(trans('vox.popup.recommend.share')) !!}</span>
								</label>
							</div>

							<div class="tac">
								<button type="submit" id="recommend-button" class="blue-button">{!! nl2br(trans('vox.popup.recommend.send')) !!}</button>
							</div>
						</div>
					</div>
					<div class="alert alert-success" style="display: none;">{!! nl2br(trans('vox.popup.recommend.success')) !!}</div>
					<div class="alert alert-warning" style="display: none; margin-top: 20px;">{!! nl2br(trans('vox.popup.recommend.error')) !!}</div>
				</form>

				<div class="recommend-fb" style="display: none;">
					<h4>{!! nl2br(trans('vox.popup.recommend.recommend-fb')) !!}</h4>
					<a class="blue-button" href="https://www.facebook.com/pg/dentavox.dentacoin/reviews/?ref=page_internal" target="_blank">{!! nl2br(trans('vox.popup.recommend.post')) !!}</a>
					<video id="myVideoRecommend" playsinline muted loop src="{{ url('new-vox-img/recommend.mp4') }}" type="video/mp4" controls=""></video>
				</div>
			</div>
		</div>
	</div>
</div>