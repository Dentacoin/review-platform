<p>
	{!! nl2br($cta) !!}
</p>
<div class="form-group clearfix">
	<div class="col-md-6">
		<a class="btn btn-default btn-block" id="btn-resend" data-alt-text="{{ trans('front.page.verify.resent') }}" href="javascript:;">
			{{ trans('front.page.verify.resend') }}
		</a>
	</div>
	<div class="col-md-6">
		<a class="btn btn-default btn-block" href="{{ getLangUrl('profile/info') }}">
			{{ trans('front.page.verify.profile') }}
		</a>
	</div>
</div>
<div class="form-group clearfix">
	<div class="col-md-12">
		<a class="btn btn-primary btn-block" href="javascript: window.location.reload();">
			{{ trans('front.page.verify.refresh') }}
		</a>
	</div>
</div>