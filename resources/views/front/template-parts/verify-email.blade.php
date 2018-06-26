<p>
	{!! nl2br($cta) !!}
</p>
<div class="form-group clearfix" @if($user->email) style="display:none;" @endif>
	<form id="set-email-form" method="post" action="{{ getLangUrl('profile/setEmail') }}">
	  	{!! csrf_field() !!}
		<div class="col-md-12">
			<input type="text" name="email" id="verify-email" value="" placeholder="{{ trans('front.page.verify.email') }}" class="form-control" />
		</div>
		<div class="col-md-12">
			<br/>
			<button type="submit" class="btn btn-primary btn-block" id="btn-resend" data-alt-text="{{ trans('front.page.verify.resent') }}" style="margin-top: 0px;">
				{{ trans('front.page.verify.resend') }}
			</button>
		</div>
		<div class="col-md-12">
			<div class="alert alert-warning" id="set-email-error" style="display: none;">
			</div>
		</div>
	</form>
</div>
<div class="form-group clearfix" id="email-refresh" @if(!$user->email) style="display: none;" @endif >
	<div class="col-md-12">
		<p>
			{!! trans('front.page.verify.verify', [
				'email' => '<b id="verify-email-span">'.$user->email.'</b>'
			]) !!}
		</p>
		<br/>
	</div>
	<div class="col-md-12">
		<a class="btn btn-primary btn-block" href="javascript: window.location.reload();">
			{{ trans('front.page.verify.refresh') }}
		</a>
	</div>
</div>