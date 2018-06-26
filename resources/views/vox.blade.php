<!DOCTYPE html>
<html>
    <head>
        <base href="{{ url('/') }}">
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="google-site-verification" content="b0VE72mRJqqUuxWJZklHQnvRZV4zdJkDymC0RD9hPhE" />


        <title>{{ $seo_title }}</title>
        <meta name="description" content="{{ $seo_description }}">
        <link rel="canonical" href="{{ $canonical }}" />

        <meta property="og:locale" content="{{ App::getLocale() }}" />
        <meta property="og:title" content="{{ $social_title }}"/>
        <meta property="og:description" content="{{ $social_description }}"/>
        <meta property="og:image" content="{{ $social_image }}"/>
        <meta property="og:site_name" content="{{ trans('vox.social.site-name') }}" />
        
        <meta name="twitter:card" content="summary" />
        <meta name="twitter:title" content="{{ $social_title }}" />
        <meta name="twitter:description" content="{{ $social_description }}" />
        <meta name="twitter:image" content="{{ $social_image }}"/>

        <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
        @if(config('langs')[App::getLocale()]['rtl'])
        	<link rel="stylesheet" href="//cdn.rawgit.com/morteza/bootstrap-rtl/v3.3.4/dist/css/bootstrap-rtl.min.css" crossorigin="anonymous">
        @else
        	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
        @endif
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">

		{!! config('langs')[App::getLocale()]['font'] !!}
		<link rel="stylesheet" type="text/css" href="{{ url('/css/app.css').'?ver='.$cache_version }}" />
		<link rel="stylesheet" type="text/css" href="{{ url('/css/flickity.min.css').'?ver='.$cache_version }}" />
		<link rel="stylesheet" type="text/css" href="{{ url('/css/datepicker.css').'?ver='.$cache_version }}" />
		<link rel="stylesheet" type="text/css" href="{{ url('/css/style-vox.css').'?ver='.$cache_version }}" />

        @if(!empty($csscdn) && is_array($csscdn))
            @foreach($csscdn as $file)
				<link rel="stylesheet" type="text/css" href="{{ $file }}" />
            @endforeach
        @endif

		<style type="text/css">
			body {
				{!! config('langs')[App::getLocale()]['font_css'] !!}
			}
		</style>

		<script src='https://www.google.com/recaptcha/api.js'></script>

		<!-- Global site tag (gtag.js) - Google Analytics -->
		<script async src="https://www.googletagmanager.com/gtag/js?id=UA-108398439-2"></script>
		<script>
			window.dataLayer = window.dataLayer || [];
			function gtag(){dataLayer.push(arguments);}
			gtag('js', new Date());

			gtag('config', 'UA-108398439-2');
		</script>

		<!-- Facebook Pixel Code -->
		<script>
			!function(f,b,e,v,n,t,s)
			{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
			n.callMethod.apply(n,arguments):n.queue.push(arguments)};
			if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
			n.queue=[];t=b.createElement(e);t.async=!0;
			t.src=v;s=b.getElementsByTagName(e)[0];
			s.parentNode.insertBefore(t,s)}(window,document,'script',
			'https://connect.facebook.net/en_US/fbevents.js');
			fbq('init', '2010503399201502'); 
			fbq('track', 'PageView');
			@if($just_registered)
            	fbq('track', 'CompleteRegistration');
            @endif
		</script>
		<!-- End Facebook Pixel Code -->

		<link rel="apple-touch-icon" sizes="57x57" href="{{ url('vox-fav/apple-icon-57x57.png') }}">
		<link rel="apple-touch-icon" sizes="60x60" href="{{ url('vox-fav/apple-icon-60x60.png') }}">
		<link rel="apple-touch-icon" sizes="72x72" href="{{ url('vox-fav/apple-icon-72x72.png') }}">
		<link rel="apple-touch-icon" sizes="76x76" href="{{ url('vox-fav/apple-icon-76x76.png') }}">
		<link rel="apple-touch-icon" sizes="114x114" href="{{ url('vox-fav/apple-icon-114x114.png') }}">
		<link rel="apple-touch-icon" sizes="120x120" href="{{ url('vox-fav/apple-icon-120x120.png') }}">
		<link rel="apple-touch-icon" sizes="144x144" href="{{ url('vox-fav/apple-icon-144x144.png') }}">
		<link rel="apple-touch-icon" sizes="152x152" href="{{ url('vox-fav/apple-icon-152x152.png') }}">
		<link rel="apple-touch-icon" sizes="180x180" href="{{ url('vox-fav/apple-icon-180x180.png') }}">
		<link rel="icon" type="image/png" sizes="192x192"  href="{{ url('vox-fav/android-icon-192x192.png') }}">
		<link rel="icon" type="image/png" sizes="32x32" href="{{ url('vox-fav/favicon-32x32.png') }}">
		<link rel="icon" type="image/png" sizes="96x96" href="{{ url('vox-fav/favicon-96x96.png') }}">
		<link rel="icon" type="image/png" sizes="16x16" href="{{ url('vox-fav/favicon-16x16.png') }}">
		<link rel="manifest" href="{{ url('vox-fav/manifest.json') }}">
		<meta name="msapplication-TileColor" content="#ffffff">
		<meta name="msapplication-TileImage" content="{{ url('vox-fav/ms-icon-144x144.png') }}">
		<meta name="theme-color" content="#ffffff">

    </head>

    <body class="page-{{ $current_page }} sp-{{ $current_subpage }} {{ !empty($satic_page) ? 'page-page' : '' }} {{ (config('langs')[App::getLocale()]['rtl']) ? 'rtl' : 'ltr' }}">
		<noscript>
			<img height="1" width="1" src="https://www.facebook.com/tr?id=2010503399201502&ev=PageView&noscript=1"/>
		</noscript>
		
		<header>
			<div class="container">
				<div class="navbar clearfix">
					<a href="{{ getLangUrl('/') }}" class="logo">
						<img src="{{ url('img-vox/logo.png') }}">
					</a>
					<div class="header-title">
						<a href="{{ getLangUrl('/') }}">
							<img src="{{ url('img-vox/text-logo.png') }}">
						</a>
						<span>
							{!! trans('vox.header.question-count', ['count' => '<b id="header_questions">'.number_format($header_questions, 0, '', ' ').'</b>' ]) !!}
						</span>
					</div>
					<div class="header-right">
						@if(!empty($user))
								<a class="header-a" href="{{ getLangUrl('profile') }}">
									{{ $user->name }}
								</a>
								<a class="header-a" href="{{ getLangUrl('logout') }}"><i class="fa fa-sign-out "></i></a>
								<p><a href="{{ getLangUrl('profile/wallet') }}">
									<span id="header-balance">{{ $user->getVoxBalance() }}</span> DCN  | <span id="header-usd">${{ sprintf('%.2F', $user->getVoxBalance() * $dcn_price) }}</span>
								</a></p>
						@else
							<a href="javascript:;" data-toggle="modal" data-target="#loginPopup" class="sign-in">
								{{ trans('vox.header.sign-in') }}
							</a>
						@endif
						<p>
							1 DCN = $<span id="header-rate">{{ sprintf('%.4F', $dcn_price) }}</span> 
							<span id="header-change" style="color: #{{ $dcn_change>0 ? '4caf50' : 'e91e63' }};">({{ $dcn_change }}%)</span>
						</p>
					</div>
				</div>
			</div>
		</header>


		<div class="site-content">
	   
			@yield('content')

		</div>

		@if(empty($user))
			<div id="fb-root"></div>
			<script>(function(d, s, id) {
			var js, fjs = d.getElementsByTagName(s)[0];
			if (d.getElementById(id)) return;
			js = d.createElement(s); js.id = id;
			js.src = 'https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.12&appId=1906201509652855&autoLogAppEvents=1';
			fjs.parentNode.insertBefore(js, fjs);
			}(document, 'script', 'facebook-jssdk'));</script>


			<div id="loginPopup" class="modal fade" role="dialog">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-body">
							<button type="button" class="close" data-dismiss="modal">&times;</button>
							<p class="popup-title">
								{{ trans('vox.popup.login.title') }}
							</p>
							<b><p class="popup-second-title">
								{{ trans('vox.popup.login.subtitle') }}
							</p><b>
							<p class="sign-title">
								{{ trans('vox.popup.login.sign-in') }}
							</p>
							<div class="fb-button-inside">
								<a href="{{ getLangUrl('login/facebook') }}" class="">
								</a>
								<div class="fb-login-button" data-max-rows="1" data-size="large" data-button-type="continue_with" data-show-faces="false" data-auto-logout-link="false" data-use-continue-as="false"></div>
							</div>

							<form action="{{ getLangUrl('login') }}" method="post" id="login-form">
								{!! csrf_field() !!}
								<div class="form-group">
									{{ trans('vox.popup.login.or') }}
								</div>
								<div class="form-group">
									<input type="text" class="form-control" name="email" id="email" placeholder="{{ trans('vox.popup.login.placeholder-email') }}">
								</div>
								<div class="form-group">
									<input type="password" class="form-control" name="password" id="password" placeholder="{{ trans('vox.popup.login.placeholder-password') }}">
								</div>
							    <input id="remember" type="hidden" name="remember" value="1" >

								<div class="alert alert-warning" id="login-error" style="display: none;">
									{{ trans('vox.popup.login.error') }}
								</div>
								<button type="submit" class="btn btn-primary">
									{{ trans('vox.popup.login.button') }}
								</button>
                				@include('front.errors')
							</form>
							<a href="javascript:;" data-toggle="modal" data-target="#registerPopup" class="blue-link">
								{{ trans('vox.popup.login.register') }}
							</a>
							<br/>
							<br/>
							<a href="{{ getLangUrl('forgot-password') }}" class="blue-link">
								{{ trans('vox.popup.login.recover-password') }}
							</a>
						</div>
					</div>
				</div>
			</div>

			<div id="registerPopup" class="modal fade" role="dialog">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-body">
							<button type="button" class="close" data-dismiss="modal">&times;</button>
							<b><p class="popup-second-title">
								{{ trans('vox.popup.register.title') }}
							</p><b>
								<p class="sign-title">
									{{ trans('vox.popup.register.subtitle') }}
								</p>
								<p>
									{!! nl2br(trans('vox.popup.register.fb-only')) !!}
								</p>

							<!--
							<br/>
							<br/>
							<div class="alert alert-info" >
								We are currently improving our fake accounts detection mechanism. Therefore, no new registrations are possible until further notice. Please enter your contact information for updates:
							</div>

							<div class="form-group">
							  	<div class="col-md-12" style="float: none; margin-bottom: 10px;">
							    	<input type="email" id="stop-email" name="email" class="form-control" placeholder="Your email address">
							    </div>
							</div>
						  	<div class="form-group">
							  	<div class="col-md-12" style="float: none; margin-bottom: 10px;">
							    	<input type="text" id="stop-name" name="name" class="form-control" placeholder="Your name">
							    </div>
							</div>
						  	<div class="form-group">
						  		<div class="col-md-12" style="float: none; margin-bottom: 10px;">
							    	<button type="submit" id="stop-submit" name="submit" class="btn btn-primary btn-block">Keep me posted</button>
							    </div>
							</div>
							-->

							<label for="read-privacy" class="reg-privacy">
								<input id="read-privacy" type="checkbox" name="read-privacy">
								{!! nl2br(trans('vox.popup.register.agree-privacy', [
									'privacylink' => '<a href="'.getLangUrl('privacy').'">', 
									'endprivacylink' => '</a>'
								])) !!}
							</label>

							<div class="fb-button-inside" style="display: none;">
								<a href="{{ getLangUrl('register/facebook') }}" class="">
								</a>
								<div class="fb-login-button" data-max-rows="1" data-size="large" data-button-type="continue_with" data-show-faces="false" data-auto-logout-link="false" data-use-continue-as="false"></div>
							</div>

							<br/>
							<br/>
                			@include('front.errors')
						</div>
					</div>
				</div>
			</div>
		@endif

        @if($user && !$user->gdpr_privacy)
			<div id="gdprPopupVox" class="modal active" role="dialog">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-body">
							<img src="{{ url('img/popups/GDPR-policy.png') }}">
							<h2>
								{{ trans('vox.popup.gdpr.title') }}
							</h2>
							<p>
								{!! nl2br(trans('vox.popup.gdrp.description', [
									'gdrplink' => '<a href="https://www.eugdpr.org/" target="_blank">' ,
									'endgdrplink' => '</a>' ,
									'privacylink' => '<a href="https://dentacoin.com/privacy/" target="_blank">', 
									'endprivacylink' => '</a>'
								])) !!}
							</p>

							<a href="javascript:;" class="agree-gdpr">{{ trans('vox.popup.gdpr.agree') }}</a>
						</div>
					</div>
				</div>
			</div>
		@elseif(!empty($welcome_test))
			<div class="new-popup popup-welcome">
				<div class="new-popup-wrapper">
					<div class="step">
						<img src="{{ url('img/popups/tutorial-1.jpg') }}" />
						<h2>
							{{ trans('vox.welcome.title') }}
						</h2>
						<p>
							{!! nl2br(trans('vox.welcome.content')) !!}
							
						</p>
						<a class="active-btn step-btn" href="{{ $welcome_test }}">{{ trans('vox.welcome.button') }}</a>
					</div>
				</div>
			</div>
		@elseif($show_tutorial)
			<div class="new-popup popup-tutorial">
				<div class="new-popup-wrapper">
					<div class="step">
						<img src="{{ url('img/popups/tutorial-1.jpg') }}" />
						<h2>
							{{ trans('vox.tutorial.step1.title') }}
						</h2>
						<p>
							{!! nl2br(trans('vox.tutorial.step1.content')) !!}
							
						</p>
						<a class="active-btn step-btn">{{ trans('vox.tutorial.next') }}</a>
					</div>
					<div class="step" style="display: none;">
						<img src="{{ url('img/popups/tutorial-2.jpg') }}" />
						<h2>
							{{ trans('vox.tutorial.step2.title') }}							
						</h2>
						<p>
							{!! nl2br(trans('vox.tutorial.step2.content')) !!}
						</p>
						<a class="active-btn step-btn">{{ trans('vox.tutorial.next') }}</a>
					</div>
					<div class="step" style="display: none;">
						<img src="{{ url('img/popups/tutorial-3.jpg') }}" />
						<h2>
							{{ trans('vox.tutorial.step3.title') }}
						</h2>
						<p>
							{!! nl2br(trans('vox.tutorial.step3.content')) !!}
						</p>
						<a class="active-btn step-btn">{{ trans('vox.tutorial.next') }}</a>
					</div>
					<div class="step" style="display: none;">
						<img src="{{ url('img/popups/tutorial-4.jpg') }}" />
						<h2>
							{{ trans('vox.tutorial.step4.title') }}
						</h2>
						<p>
							{!! nl2br(trans('vox.tutorial.step4.content')) !!}
						</p>
						<a class="active-btn step-btn">{{ trans('vox.tutorial.next') }}</a>
					</div>
					<div class="step" style="display: none;">
						<img src="{{ url('img/popups/tutorial-5.jpg') }}" />
						<h2>
							{{ trans('vox.tutorial.step5.title') }}
						</h2>
						<p>
							{!! nl2br(trans('vox.tutorial.step5.content')) !!}
						</p>
						<a class="active-btn step-btn">{{ trans('vox.tutorial.finish') }}</a>
					</div>
					<a href="javascript:;" class="closer">
						<i class="fa fa-remove"></i>
					</a>
				</div>
			</div>
		@endif

		<footer>
			<div class="container clearfix">
				<a href="https://dentacoin.com/" target="_blank" class="footer-logo">
					<img src="{{ url('img-vox/dc-logo.png') }}">
					<p class="bold">
						{{ trans('vox.footer.company-name') }}
					</p>
				</a>
				<div class="footer-text">
					{{ trans('vox.footer.company-info') }}
					<br/>
					<a href="https://dentacoin.com/privacy/" target="_blank">{{ trans('vox.footer.privacy') }}</a>
				</div>
				<div class="socials">
					<select id="language-selector" class="form-control lang-select" name="languages" style="display: none;">
			            @foreach (config('langs') as $key => $lang)
							<option {!! App::getLocale()==$key ? 'selected="selected"' : '' !!} value="{{ $key }}">{{ $lang['name'] }}</option>
					    @endforeach
					</select>
					<br/>
					Follow us on &nbsp;
					<a class="social" href="https://www.facebook.com/DentaVox-1578351428897849/"><i class="fa fa-facebook"></i></a>
					<!--
						<a class="social" href="javascript:;"><i class="fa fa-twitter"></i></a>
					-->
				</div>
			</div>
		</footer>

		@if(empty($_COOKIE['show-update']))
			<div class="alert alert-warning alert-update" style="text-align: center;">
				UPDATE IN PROGRESS: We’re improving DentaVox and working on new surveys. There might be some temporary technical issues. Sorry for any inconvenience and thank you for your understanding!
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
		@endif

        <script src="https://code.jquery.com/jquery-3.1.0.min.js" integrity="sha256-cCueBR6CsyA4/9szpPfrX3s49M9vUU5BgtiJj06wt/s=" crossorigin="anonymous"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/js-cookie@2/src/js.cookie.min.js"></script>
		<script src="{{ url('/js-vox/bootstrap-datepicker.js').'?ver='.$cache_version }}"></script>
		<script src="{{ url('/js-vox/flickity.pkgd.min.js').'?ver='.$cache_version }}"></script>
		<script src="{{ url('/js-vox/main.js').'?ver='.$cache_version }}"></script>
		@if(!empty($plotly))
			<script src="https://cdn.plot.ly/plotly-latest.min.js"></script>
		@endif
        @if(!empty($js) && is_array($js))
            @foreach($js as $file)
                <script src="{{ url('/js-vox/'.$file).'?ver='.$cache_version }}"></script>
            @endforeach
        @endif
        @if(!empty($jscdn) && is_array($jscdn))
            @foreach($jscdn as $file)
                <script src="{{ $file }}"></script>
            @endforeach
        @endif
        <script type="text/javascript">
        	var lang = '{{ App::getLocale() }}';
        </script>
    </body>
</html>