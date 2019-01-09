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
        <meta property="og:site_name" content="{{ trans('front.social.site-name') }}" />
        
        <meta name="twitter:card" content="summary" />
        <meta name="twitter:title" content="{{ $social_title }}" />
        <meta name="twitter:description" content="{{ $social_description }}" />
        <meta name="twitter:image" content="{{ $social_image }}"/>
        
        <meta name="fb:app_id" content="1906201509652855"/>


		<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.1.0/css/all.css" integrity="sha384-lKuwvrZot6UHsBSfcMvOkWwlCMgc0TaWr+30HWe3a4ltaBwTZhyTEggF5tJv8tbt" crossorigin="anonymous">
		<link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,700&amp;subset=cyrillic" rel="stylesheet">
		<link rel="stylesheet" href="https://unpkg.com/flickity@2/dist/flickity.min.css">

		<link rel="stylesheet" type="text/css" href="{{ url('/css/new-style-trp.css').'?ver='.$cache_version }}" />
		<link rel="stylesheet" type="text/css" href="{{ url('/css/ids.css').'?ver='.$cache_version }}" />
		
        @if(!empty($css) && is_array($css))
            @foreach($css as $file)
				<link rel="stylesheet" type="text/css" href="{{ url('/css/'.$file).'?ver='.$cache_version }}" />
            @endforeach
        @endif

        @if(!empty($csscdn) && is_array($csscdn))
            @foreach($csscdn as $file)
				<link rel="stylesheet" type="text/css" href="{{ $file }}" />
            @endforeach
        @endif

		@if(empty($user))
			<script src='https://www.google.com/recaptcha/api.js'></script>
		@endif

		<!-- Global site tag (gtag.js) - Google Analytics -->
		<script async src="https://www.googletagmanager.com/gtag/js?id=UA-108398439-1"></script>
		<script>
		  window.dataLayer = window.dataLayer || [];
		  function gtag(){dataLayer.push(arguments);}
		  gtag('js', new Date());

		  gtag('config', 'UA-108398439-1');
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
		<link rel="apple-touch-icon" sizes="57x57" href="{{ url('trp-fav/apple-icon-57x57.png') }}">
		<link rel="apple-touch-icon" sizes="60x60" href="{{ url('trp-fav/apple-icon-60x60.png') }}">
		<link rel="apple-touch-icon" sizes="72x72" href="{{ url('trp-fav/apple-icon-72x72.png') }}">
		<link rel="apple-touch-icon" sizes="76x76" href="{{ url('trp-fav/apple-icon-76x76.png') }}">
		<link rel="apple-touch-icon" sizes="114x114" href="{{ url('trp-fav/apple-icon-114x114.png') }}">
		<link rel="apple-touch-icon" sizes="120x120" href="{{ url('trp-fav/apple-icon-120x120.png') }}">
		<link rel="apple-touch-icon" sizes="144x144" href="{{ url('trp-fav/apple-icon-144x144.png') }}">
		<link rel="apple-touch-icon" sizes="152x152" href="{{ url('trp-fav/apple-icon-152x152.png') }}">
		<link rel="apple-touch-icon" sizes="180x180" href="{{ url('trp-fav/apple-icon-180x180.png') }}">
		<link rel="icon" type="image/png" sizes="192x192"  href="{{ url('trp-fav/android-icon-192x192.png') }}">
		<link rel="icon" type="image/png" sizes="32x32" href="{{ url('trp-fav/favicon-32x32.png') }}">
		<link rel="icon" type="image/png" sizes="96x96" href="{{ url('trp-fav/favicon-96x96.png') }}">
		<link rel="icon" type="image/png" sizes="16x16" href="{{ url('trp-fav/favicon-16x16.png') }}">
		<link rel="manifest" href="{{ url('trp-fav/manifest.json') }}">
		<meta name="msapplication-TileColor" content="#ffffff">
		<meta name="msapplication-TileImage" content="{{ url('trp-fav/ms-icon-144x144.png') }}">
		<meta name="theme-color" content="#ffffff">
		<meta name="google-site-verification" content="EYJZsVUi75Q_McP0FuWfCOqc09pAzItF084_LvsRkcY" />

    </head>

    <body class="page-{{ $current_page }} sp-{{ $current_subpage }} {{ !empty($extra_body_class) ? $extra_body_class : '' }} {{ !empty($satic_page) ? 'page-page' : '' }} {{ (config('langs')[App::getLocale()]['rtl']) ? 'rtl' : 'ltr' }}">
		<header class="header">
	       	<nav class="navbar navbar-default navbar-fixed-top">
  				<div class="container">
				    <div class="navbar-header">
						<a class="logo" href="{{ getLangUrl('/') }}"></a>
						<div class="header-info">

	                        @if($current_page=='profile')
	                        	<a href="javascript:;" class="profile-menu-mobile">
	                        	</a>
							@endif
								
							@if($current_page=='dentist')
								<i class="fas fa-search"></i>
							@endif

	                        @if(!empty($user))
								<a href="{{ getLangUrl('profile') }}" class="profile-btn">
									<span class="name">
										{{ $user->getNameShort() }}
									</span>
									<img src="{{ $user->getImageUrl(true) }}" {!! $user->hasimage ? '' : 'class="default-avatar"' !!}>
								</a>

								<!-- <a class="header-a" href="{{ getLangUrl('logout') }}"><i class="fas fa-sign-out-alt"></i></a> -->							
								<div class="expander-wrapper{!! $user->hasimage ? ' has-image' : '' !!}">
									<div class="expander">
										<a href="{{ getLangUrl('logout') }}">
											<i class="fas fa-power-off"></i>
											Log out
										</a>
										<a class="btn" href="{{ getLangUrl('profile') }}">
											My Account
										</a>
									</div>
								</div>
	                        @else
	                        	@if($current_page=='welcome-dentist')
	                        		<a href="{{ getLangUrl('/') }}" class="button-dentists">
										For patients
									</a>
	                        	@else
									<a href="{{ getLangUrl('welcome-dentist') }}" class="button-dentists">
										For dentists
									</a>
								@endif
								<a href="javascript:;" class="button-sign-in" data-popup="popup-login">
									Log in
								</a>
	                        @endif

						</div>
				    </div>
			    </div>
		    </div>
	    </header>

	    <div class="site-content">
			@yield('content')
		</div>

		<div class="footer-expander">
			<footer>
				<div class="container clearfix">
					<a href="https://dentacoin.com/" target="_blank" class="footer-logo col-md-3 flex break-mobile flex-center">
						<img src="{{ url('img-trp/dc-logo.png') }}">
						<p class="bold">
							Powered by Dentacoin
						</p>
					</a>
					<div class="footer-text col-md-6 tac">
						<div class="footer-menu">
							<a href="{{ getLangUrl('faq') }}">FAQ</a>
							<a href="https://dentacoin.com/privacy-policy/" target="_blank">Privacy Policy</a>
							<a href="https://dentavox.dentacoin.com/" target="_blank">Dentavox</a>
							<a href="https://dentacare.dentacoin.com/" target="_blank">Dentacare App</a>
						</div>
						<small>
							Copyright © 2018. Dentacoin Foundation. All rights reserved
						</small>
					</div>
					<div class="socials col-md-3">
						Stay in the loop: &nbsp;
						<a class="social" href="https://t.me/dentacoin" target="_blank"><i class="fab fa-telegram-plane"></i></a>
						<a class="social" href="https://www.facebook.com/DentaVox-1578351428897849/" target="_blank"><i class="fab fa-facebook-f"></i></a>
					</div>
				</div>
			</footer>
		</div>


		<a id="ids" href="https://ids.dentacoin.com/" target="_blank">
			<i class="fas fa-times-circle"></i>
		</a>


		@include('trp/popups/share')
		@if(empty($user))
			@include('trp/popups/register')
			@include('trp/popups/dentist-verification')
			@include('trp/popups/banned')
		@endif


        <script src="https://code.jquery.com/jquery-3.3.1.min.js"  integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="  crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/js-cookie@2/src/js.cookie.min.js"></script>
        <script src="https://unpkg.com/flickity@2/dist/flickity.pkgd.min.js"></script>
		<script src="{{ url('/js-trp/main.js').'?ver='.$cache_version }}"></script>
		
        @if( $current_page=='dentist' )
			<script src="//vjs.zencdn.net/6.4.0/video.min.js"></script>
			<script src="//cdn.WebRTC-Experiment.com/RecordRTC.js"></script>
			<script src="//webrtc.github.io/adapter/adapter-latest.js"></script>
        @endif
        @if(!empty($js) && is_array($js))
            @foreach($js as $file)
                <script src="{{ url('/js-trp/'.$file).'?ver='.$cache_version }}"></script>
            @endforeach
        @endif
        @if(!empty($jscdn) && is_array($jscdn))
            @foreach($jscdn as $file)
                <script src="{{ $file }}"></script>
            @endforeach
        @endif
        
        <script type="text/javascript">
        	var areYouSure = '{{ trans('front.common.sure') }}';
        	var lang = '{{ App::getLocale() }}';
        	var user_id = {{ !empty($user) ? $user->id : 'null' }};
        	var images_path = '{{ url('img-trp') }}';
        </script>

        @if(!$user || $new_auth)
        	
			<div id="fb-root"></div>
			<script>(function(d, s, id) {
			var js, fjs = d.getElementsByTagName(s)[0];
			if (d.getElementById(id)) return;
			js = d.createElement(s); js.id = id;
			js.src = 'https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.12&appId=1906201509652855&autoLogAppEvents=1';
			fjs.parentNode.insertBefore(js, fjs);
			}(document, 'script', 'facebook-jssdk'));</script>
        @endif
    </body>
</html>