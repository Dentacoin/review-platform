@extends('trp')

@section('content')

	<div class="container">
		<div class="signin-top">
	    	<h2>
	    		{{ trans('trp.page.index-dentist.title') }}
	    		
	    	</h2>
	    	<p>
	    		{!! nl2br(trans('trp.page.index-dentist.subtitle')) !!}
	    		
	    	</p>

			<a href="javascript:;" class="button button-sign-up-dentist" data-popup="popup-register">
				{{ trans('trp.page.index-dentist.signup') }}
			</a>

	    </div>
    </div>

    <div class="signin-form-wrapper">
    	<img src="{{ url('img-trp/signin-laptop.png') }}">
    	<div class="container clearfix">
    		<form class="signin-form">

				<div class="form-inner">
					<input type="text" name="name" placeholder="{{ trans('trp.page.index-dentist.name') }}" class="input">
					<input type="email" name="email" placeholder="{{ trans('trp.page.index-dentist.email') }}" class="input">
					<input type="password" name="password" placeholder="{{ trans('trp.page.index-dentist.password') }}" class="input">
					<div class="tac">
						<input type="submit" value="{{ trans('trp.page.index-dentist.signup') }}" class="button button-sign-up-dentist">
					</div>
				</div>

				<p class="have-account">
					{!! nl2br(trans('trp.page.index-dentist.have-account', [
						'link' => '<a href="javascript:;" data-popup="popup-login">',
						'endlink' => '</a>',
					])) !!}
					
				</p>

    		</form>
    	</div>
    </div>

    <div class="container section-dentist-info">
    	<h2 class="tac">
    		{!! nl2br(trans('trp.page.index-dentist.usp-title')) !!}
    	</h2>

    	<div class="clearfix">
			<div class="flex flex-bottom">
				<div class="col col-icon">
	    			<img src="{{ url('img-trp/grow-icon.png') }}">
	    		</div>
	    		<div class="col">
	    			<h3>
	    				{!! nl2br(trans('trp.page.index-dentist.usp.step-1-title')) !!}
	    			</h3>
	    			<p>
	    				{!! nl2br(trans('trp.page.index-dentist.usp.step-1-description')) !!}
		    		</p>
	    		</div>
    		</div>
			<div class="flex flex-bottom">
				<div class="col col-icon">
	    			<img src="{{ url('img-trp/expertise.png') }}">
	    		</div>
	    		<div class="col">
	    			<h3>
	    				{!! nl2br(trans('trp.page.index-dentist.usp.step-2-title')) !!}
	    			</h3>
	    			<p>
	    				{!! nl2br(trans('trp.page.index-dentist.usp.step-2-description')) !!}
		    		</p>
	    		</div>
    		</div>
			<div class="flex flex-bottom">
				<div class="col col-icon">
	    			<img src="{{ url('img-trp/ranking.png') }}">
	    		</div>
	    		<div class="col">
	    			<h3>
	    				{!! nl2br(trans('trp.page.index-dentist.usp.step-3-title')) !!}
	    			</h3>
	    			<p>
	    				{!! nl2br(trans('trp.page.index-dentist.usp.step-3-description')) !!}
		    		</p>
	    		</div>
    		</div>
			<div class="flex flex-bottom">
				<div class="col col-icon">
	    			<img src="{{ url('img-trp/present.png') }}">
	    		</div>
	    		<div class="col">
	    			<h3>
	    				{!! nl2br(trans('trp.page.index-dentist.usp.step-4-title')) !!}
	    			</h3>
	    			<p>
	    				{!! nl2br(trans('trp.page.index-dentist.usp.step-4-description')) !!}
	    			</p>
	    		</div>
    		</div>
    	</div>
    </div>

    <div class="container section-how">

    	<h2 class="tac">
    		{!! nl2br(trans('trp.page.index-dentist.how-works-title', [
				'firstblue' => '<span class="h2">',
				'secondblue' => '<span class="h1">',
				'endblue' => '</span>',
			])) !!}
    	</h2>


    	<div class="clearfix">
    		<div class="left">
    			<div class="how-block flex flex-center">
	    			<span class="h1">01</span>
	    			<p>
	    				{!! nl2br(trans('trp.page.index-dentist.step-1', [
							'link' => '<a href="javascript:;" data-popup="popup-register">',
							'endlink' => '</a>',
						])) !!}
	    				
	    			</p>
	    		</div>
    			<div class="how-block flex flex-center">
	    			<span class="h1">02</span>
	    			<p>
	    				{!! nl2br(trans('trp.page.index-dentist.step-2')) !!}
	    				
	    			</p>
	    		</div>
    			<div class="how-block flex flex-center">
	    			<span class="h1">03</span>
	    			<p>
	    				{!! nl2br(trans('trp.page.index-dentist.step-3')) !!}
	    				
	    			</p>
	    		</div>
    		</div>
    		<div class="right">		    			
    			<div class="how-block flex flex-center">
	    			<span class="h1">04</span>
	    			<p>
	    				{!! nl2br(trans('trp.page.index-dentist.step-4', [
							'link' => '<a href="https://wallet.dentacoin.com/" target="_blank">',
							'endlink' => '</a>',
						])) !!}
	    				
	    			</p>
	    		</div>
    			<div class="how-block flex flex-center">
	    			<span class="h1">05</span>
	    			<p>
	    				{!! nl2br(trans('trp.page.index-dentist.step-5')) !!}
	    				
	    			</p>
	    		</div>
    			<div class="how-block flex flex-center">
	    			<span class="h1">06</span>
	    			<p>
	    				{!! nl2br(trans('trp.page.index-dentist.step-6')) !!}
	    				
	    			</p>
	    		</div>
    		</div>
    	</div>
    </div>

    <div class="section-learn">
    	<div class="container">
    		<h2>
    			{!! nl2br(trans('trp.page.index-dentist.cta')) !!}
    			
    		</h2>
    		<a href="javascript:;" class="button button-white button-sign-up-dentist" data-popup="popup-register">
    			{!! nl2br(trans('trp.page.index-dentist.signup')) !!}
    		</a>
    	</div>
    </div>
@endsection