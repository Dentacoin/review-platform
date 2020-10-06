@extends('trp')

@section('content')

	<div class="welcome-dentist-section">
		<div class="signin-top">
	    	<h1>
	    		{{ trans('trp.page.index-dentist.title') }}
	    	</h1>

	    	<p>
	    		{!! nl2br(trans('trp.page.index-dentist.subtitle')) !!}
	    	</p>

			<div class="ratings biggest">
				<div class="stars">
					<div class="bar" style="width: 100%;">
					</div>
				</div>
			</div>

			<div class="tac button-wrap">
				<a href="javascript:;" class="button button-sign-up-dentist open-dentacoin-gateway dentist-register">
	    			{!! nl2br(trans('trp.page.index-dentist.signup')) !!}
	    		</a>
	    	</div>

	    </div>

	    <div class="signin-form-wrapper">
	    	<img src="{{ url('img-trp/dentacoin-trusted-reviews-dentist-front-page.png') }}" alt="{{ trans('trp.alt-tags.index') }}">
	    	<div class="container clearfix">
	    		<form class="signin-form tablet-fixes">

					<div class="form-inner">
						<div class="modern-field">
							<input type="email" name="email" id="dentist-mail" class="modern-input" autocomplete="off" readonly onfocus="this.removeAttribute('readonly');">
							<label for="dentist-mail">
								<span>{{ trans('trp.page.index-dentist.email') }}</span>
							</label>
						</div>
						
						<div class="modern-field">
							<input type="password" name="password" id="dentist-pass" class="modern-input" autocomplete="off">
							<label for="dentist-pass">
								<span>{{ trans('trp.page.index-dentist.password') }}</span>
							</label>
						</div>
						
						<div class="modern-field">
							<input type="password" name="password-repeat" id="dentist-pass-repeat" class="modern-input" autocomplete="off">
							<label for="dentist-pass-repeat">
								<span>{{ trans('trp.page.index-dentist.repeat-password') }}</span>
							</label>
						</div>

						<div class="tac">
							<input type="submit" value="{{ trans('trp.page.index-dentist.signup') }}" class="button button-sign-up-dentist">
						</div>
					</div>

					<p class="have-account">
						{!! nl2br(trans('trp.page.index-dentist.have-account', [
							'link' => '<a href="javascript:;" class="open-dentacoin-gateway dentist-login">',
							'endlink' => '</a>',
						])) !!}					
					</p>

	    		</form>
	    	</div>
	    </div>
	</div>

	<div class="section-dentist-info-wrap">
	    <div class="container section-dentist-info">
	    	<h2 class="tac">
	    		{!! nl2br(trans('trp.page.index-dentist.usp-title')) !!}
	    	</h2>

	    	<div class="flex">
	    		<div class="col tac">
	    			<img src="{{ url('img-trp/dentacoin-attract-new-patients-icon.png') }}" alt="{{ trans('trp.alt-tags.attract-new-patients') }}">
	    			<div class="info-padding">
		    			<h3>{!! nl2br(trans('trp.page.index-dentist.usp.step-1-title')) !!}</h3>
		    			<p>{!! nl2br(trans('trp.page.index-dentist.usp.step-1-description')) !!}</p>
		    		</div>
	    		</div>
	    		<div class="col tac">
	    			<img src="{{ url('img-trp/dentacoin-get-more-reviews-icon.png') }}" alt="{{ trans('trp.alt-tags.more-reviews') }}">   
	    			<div class="info-padding"> 			
		    			<h3>{!! nl2br(trans('trp.page.index-dentist.usp.step-2-title')) !!}</h3>
		    			<p>{!! nl2br(trans('trp.page.index-dentist.usp.step-2-description')) !!}</p>
		    		</div>
	    		</div>
	    	</div>

	    	<div class="flex">
	    		<div class="col tac">
	    			<img src="{{ url('img-trp/dentacoin-better-google-ranking-icon.png') }}" alt="{{ trans('trp.alt-tags.better-google-ranking') }}">
	    			<div class="info-padding">
		    			<h3>{!! nl2br(trans('trp.page.index-dentist.usp.step-3-title')) !!}</h3>
		    			<p>{!! nl2br(trans('trp.page.index-dentist.usp.step-3-description')) !!}</p>
		    		</div>
	    		</div>
	    		<div class="col tac">
	    			<img src="{{ url('img-trp/dentacoin-better-online-reputation-icon.png') }}" alt="{{ trans('trp.alt-tags.better-online-reputation') }}">
	    			<div class="info-padding">
		    			<h3>{!! nl2br(trans('trp.page.index-dentist.usp.step-4-title')) !!}</h3>
		    			<p>{!! nl2br(trans('trp.page.index-dentist.usp.step-4-description')) !!}</p>
		    		</div>
	    		</div>
	    	</div>

	    	<div class="tac button-wrap">
				<a href="javascript:;" class="button button-sign-up-dentist open-dentacoin-gateway dentist-register">
	    			{!! nl2br(trans('trp.page.index-dentist.signup')) !!}
	    		</a>
	    	</div>

			<div class="tac">
				<a href="javascript:;" class="button button-yellow magnet-popup" id="open-magnet" data-url="{{ getLangUrl('lead-magnet-session') }}">{{ trans('trp.page.index-dentist.button-lead-magnet') }}</a>
			</div>
	    </div>
	</div>

	<div id="to-append"></div>
	
@endsection