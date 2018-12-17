<div class="popup fixed-popup" id="popup-ask-dentist">
	<div class="popup-inner inner-white">
		<div class="popup-pc-buttons">
			<a href="javascript:;" class="close-popup"><i class="fas fa-times"></i></a>
		</div>

		<div class="popup-mobile-buttons">
			<a href="javascript:;" class="close-popup">< back</a>
		</div>

		<div class="alert alert-info">
			Requesting an invitation from Dr. [XYZ] proves that you have been their patient and thus allows you to earn Dentacoin (DCN) for your review. Once you've sent the invite request, you must wait for approval by Dr. [XYZ]. Check your email inbox.
			<br/>
			<br/>
			<a href="{{ $item->getLink().'/ask' }}" class="button ask-dentist">
				SEND REQUEST
			</a>
		</div>
		<div class="alert alert-success ask-success" style="display: none;">
			Your request was sent. We'll let you know as soon as {{ $item->getName() }} approves it
		</div>

	</div>
</div>