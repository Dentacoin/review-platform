<div class="popup no-image" id="add-team-popup" scss-load="trp-popup-verification" js-load="dentist-verification">
	<div class="popup-inner">
		<a href="javascript:;" class="close-popup">
			<img src="{{ url('img/close-icon.png') }}"/>
		</a>

		<h2 class="mont">
			Add Team Member
			{{-- {!! nl2br(trans('trp.popup.add-team-popup.title')) !!} --}}
		</h2>

		<p class="popup-desc">
			Add team member to your clinic profile:
		</p>

		<div id="clinic-add-team">
			@include('trp.parts.add-team-member', [
				'withoutUser' => false,
			])
		</div>
	</div>
</div>
<div class="popup no-image popup-existing-dentist" id="popup-existing-dentist">
	<div class="popup-inner inner-white">
		<div class="popup-pc-buttons">
			<a href="javascript:;" class="close-popup">
				<img src="{{ url('img/close-icon.png') }}"/>
			</a>
		</div>

		<div class="popup-mobile-buttons">
			<a href="javascript:;" class="close-popup">< {!! nl2br(trans('trp.common.back')) !!}</a>
		</div>
		<h2>
            {!! nl2br(trans('trp.popup.add-team-popup.existing-team-title')) !!}
        </h2>

        <div class="existing-dentists"></div>
	</div>
</div>