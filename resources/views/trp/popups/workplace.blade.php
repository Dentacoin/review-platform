<div class="popup fixed-popup" id="popup-wokrplace">
	<div class="popup-inner inner-white">
		<div class="popup-pc-buttons">
			<a href="javascript:;" class="close-popup"><i class="fas fa-times"></i></a>
		</div>

		<div class="popup-mobile-buttons">
			<a href="javascript:;" class="close-popup">< back</a>
		</div>
		<h2>
			{!! nl2br(trans('trp.popup.popup-wokrplace.title')) !!}
			
		</h2>

		<h4 class="popup-title">
			{!! nl2br(trans('trp.popup.popup-wokrplace.subtitle')) !!}
			
		</h4>

		<p class="popup-desc">
			{!! nl2br(trans('trp.popup.popup-wokrplace.hint')) !!}
			
		</p>

		<div class="search-dentist clinic-suggester-wrapper suggester-wrapper">
			<i class="fas fa-search"></i>
			<input type="text" class="input clinic-suggester suggester-input" name="search-clinic" placeholder="{!! nl2br(trans('trp.popup.popup-wokrplace.search')) !!}">
			<div class="suggest-results">
			</div>
			<input type="hidden" class="suggester-hidden" name="clinic_id" value="">
		</div>

		<div class="alert" id="clinic-add-result" style="display: none; margin-bottom: 20px;">
		</div>

		@if($user->my_workplace->isNotEmpty())
			<div id="workplaces-list" class="invite-content">
				<h4 class="popup-title">
					{!! nl2br(trans('trp.popup.popup-wokrplace.list')) !!}
				</h4>

				<div class="invited-dentists">
					@foreach( $user->my_workplace as $workplace )
						<div class="flex">
							<div class="flex-7">
								<a href="{{ $workplace->clinic->getLink() }}">
									{{ $workplace->clinic->getName() }}
								</a>
								@if( !$workplace->approved )
									({!! nl2br(trans('trp.popup.popup-wokrplace.pending')) !!})
								@endif
							</div>
							<div class="flex-2 tar">
								<a class="remove-dentist" href="{{ getLangUrl('profile/clinics/delete/'.$workplace->clinic->id) }}">
									<i class="fas fa-times-circle"></i>
									{!! nl2br(trans('trp.popup.popup-wokrplace.remove')) !!}
								</a>
							</div>
						</div>
					@endforeach
				</div>
			</div>
		@endif
	</div>
</div>