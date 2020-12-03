<!DOCTYPE html>
<html>
    <head>
        <base href="{{ url('/') }}">
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="height=device-height, width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no, target-densitydpi=device-dpi">
        <meta name="google-site-verification" content="b0VE72mRJqqUuxWJZklHQnvRZV4zdJkDymC0RD9hPhE" />
        
        @if(!empty($noIndex))
        	<meta name="robots" content="noindex">
        @endif

        <title>{{ $seo_title }}</title>
        <meta name="description" content="{{ $seo_description }}">
        <link rel="canonical" href="{{ $canonical }}" />

        @if(!empty($og_url))
			<meta property="og:url" content="{{ $og_url }}" />
		@endif

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

        <meta name="csrf-token" content="{{ csrf_token() }}"/>

		<link rel="stylesheet" type="text/css" href="{{ url('/css/new-style-trp.css').'?ver='.$cache_version }}" />
		
		<link rel="preload" href="{{ url('font-awesome/webfonts/fa-brands-400.woff2') }}" as="font" crossorigin>
		<link rel="preload" href="{{ url('font-awesome/webfonts/fa-solid-900.woff2') }}" as="font" crossorigin>
		<link rel="preload" href="{{ url('fonts/Calibri-Light.woff2') }}" as="font" crossorigin>
		<link rel="preload" href="{{ url('fonts/Calibri-Bold.woff2') }}" as="font" crossorigin>
		<link rel="preload" href="{{ url('fonts/Calibri.woff2') }}" as="font" crossorigin>
		
        @if(!empty($css) && is_array($css))
            @foreach($css as $file)
				<link rel="stylesheet" type="text/css" href="{{ url('/css/'.$file).'?ver='.$cache_version }}" />
            @endforeach
        @endif

		<!-- Global site tag (gtag.js) - Google Analytics -->
		<script async src="https://www.googletagmanager.com/gtag/js?id=UA-108398439-1"></script>
		<script>
		  	window.dataLayer = window.dataLayer || [];
		  	function gtag(){dataLayer.push(arguments);}
		  	gtag('js', new Date());

		  	@if(empty($_COOKIE['performance_cookies']))
				gtag('config', 'UA-108398439-1', {'anonymize_ip': true});
			@else
				gtag('config', 'UA-108398439-1');
			@endif
		</script>

		<!-- Facebook Pixel Code -->
		<script id="pixel-code">
			!function(f,b,e,v,n,t,s)
			{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
			n.callMethod.apply(n,arguments):n.queue.push(arguments)};
			if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
			n.queue=[];t=b.createElement(e);t.async=!0;
			t.src=v;s=b.getElementsByTagName(e)[0];
			s.parentNode.insertBefore(t,s)}(window,document,'script',
			'https://connect.facebook.net/en_US/fbevents.js');
			@if(empty($_COOKIE['marketing_cookies']))
				fbq('consent', 'revoke');
			@else
				fbq('consent', 'grant');
			@endif
			fbq('init', '2010503399201502'); 
			fbq('init', '2366034370318681');
			fbq('track', 'PageView');
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
		<meta name="msapplication-TileColor" content="#ffffff">
		<meta name="msapplication-TileImage" content="{{ url('trp-fav/ms-icon-144x144.png') }}">
		<meta name="theme-color" content="#ffffff">
		<meta name="google-site-verification" content="EYJZsVUi75Q_McP0FuWfCOqc09pAzItF084_LvsRkcY" />

    </head>

    <body class="page-{{ $current_page }} sp-{{ $current_subpage }} {{ !empty($extra_body_class) ? $extra_body_class : '' }} {{ !empty(session('first_guided_tour')) ? 'guided-tour' : '' }}">
    	
    	@if(!empty($_COOKIE['marketing_cookies']) )
	    	<!-- Load Facebook SDK for JavaScript -->
		    <div id="fb-root"></div>
		    <script>
		        window.fbAsyncInit = function() {
		          	FB.init({
		          		appId: '1906201509652855',
		            	xfbml: true,
		            	version: 'v7.0',
		          	});
		        };

		        (function(d, s, id) {
		        	var js, fjs = d.getElementsByTagName(s)[0];
		        	if (d.getElementById(id)) return;
	        		js = d.createElement(s); js.id = id;
		        	js.src = 'https://connect.facebook.net/en_US/sdk/xfbml.customerchat.js';
		        	fjs.parentNode.insertBefore(js, fjs);
		      	}(document, 'script', 'facebook-jssdk'));
		    </script>
	    @endif

      	<!-- Your Chat Plugin code -->
      	<div class="fb-customerchat"
        attribution=setup_tool
        page_id="127981491160115"
        greeting_dialog_display="hide"
  		logged_in_greeting="👋  {!! !empty($user) ? trans('trp.chatbox.greeting.login',['name' => $user->getNameShort() ]) : trans('trp.chatbox.greeting.not-login')  !!}"
  		logged_out_greeting="👋  {!! !empty($user) ? trans('trp.chatbox.greeting.login',['name' => $user->getNameShort() ]) : trans('trp.chatbox.greeting.not-login')  !!}">
      	</div>

    	<div id="site-url" url="{{ empty($_SERVER['REQUEST_URI']) ? getLangUrl('/') : 'https://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'] }}"></div>
		<header class="header">
	       	<nav class="navbar navbar-default navbar-fixed-top">
  				<div class="container">
				    <div class="navbar-header">
						<a class="logo" href="{{ getLangUrl('/') }}"></a>
						<div class="header-info">								
							@if($current_page=='dentist')
								<i class="fas fa-search"></i>
							@endif

	                        @if(!empty($user))
								<a href="javascript:;" class="profile-btn" id="header-avatar">
									<span class="name">
										{{ $user->getNameShort() }}
									</span>
									<img src="{{ $user->getImageUrl(true) }}" {!! $user->hasimage ? '' : 'class="default-avatar"' !!}>
								</a>
	                        @else
	                        	@if($current_page=='welcome-dentist')
	                        		<a href="{{ getLangUrl('/') }}" class="button-dentists">
										{!! trans('trp.header.for-patients') !!}
									</a>
	                        	@else
									<a href="{{ getLangUrl('welcome-dentist') }}" class="button-dentists">
										{!! trans('trp.header.for-dentists') !!}
									</a>
								@endif
								<a href="javascript:;" class="button-sign-in open-dentacoin-gateway {{ $current_page!='welcome-dentist' ? 'patient-login' : 'dentist-login' }}">
									{{ $current_page=='welcome-dentist' ? trans('trp.header.login') : trans('trp.header.signin') }}
								</a>
	                        @endif
	                        @if(!empty($admin) && count(config('langs')) > 1 && $current_page != 'dentists')
		                        <div class="lang-wrapper">
		                        	<a href="javascript:;">({{ strtoupper(App::getLocale()) }} <i class="fas fa-angle-down"></i>)</a>
			                        <div class="lang-menu">
			                        	@foreach( config('langs') as $key => $lang)
			                        		@if($key != App::getLocale())
			                        			<a href="{{ getLangUrl(substr($_SERVER['REQUEST_URI'], 4), $key) }}">{{ $lang['name'] }} ({{ strtoupper($key) }})</a>
			                        		@endif
			                        	@endforeach
			                        </div>
			                    </div>
			                @endif
						</div>
				    </div>
			    </div>
		    </div>
	    </header>

	    <div class="site-content">
			@yield('content')
		</div>

		<footer class="{{ !empty($gray_footer) ? 'gray-footer' : '' }}">
			<div class="container clearfix">
				<a href="https://dentacoin.com/" target="_blank" class="footer-logo col-md-3 flex break-mobile flex-center">
					<img src="{{ url('img-trp/dc-logo.png') }}" alt="Dentacoin logo">
					<p class="bold">
						{!! trans('trp.footer.powered') !!}
					</p>
				</a>
				<div class="footer-text col-md-6 tac">
					<div class="footer-menu">
						<a href="https://reviews.dentacoin.com/blog/" target="_blank">Blog</a>
						<a href="{{ getLangUrl('faq') }}">FAQ</a>
						<a href="https://dentacoin.com/privacy-policy/" target="_blank">Privacy Policy</a>
						<a href="https://dentavox.dentacoin.com/" target="_blank">Dentavox</a>
						<a href="https://dentacare.dentacoin.com/" target="_blank">Dentacare App</a>
					</div>
					<small>
						{!! trans('trp.footer.copyright', [
							'year' => date('Y')
						]) !!}
					</small>
				</div>
				<div class="socials col-md-3">
					{!! trans('trp.footer.stay') !!}: &nbsp;
					<a class="social" href="https://t.me/dentacoin" target="_blank"><i class="fab fa-telegram-plane"></i></a>
					<a class="social" href="https://www.facebook.com/dentacoin.trusted.reviews/" target="_blank"><i class="fab fa-facebook-f"></i></a>
				</div>
			</div>
		</footer>

		@if(!$without_banner)
			<div class="bottom-drawer">
				<a href="https://dentacoin.com/holiday-calendar/2020" target="_blank" class="christmas-banner">
					<video class="banner-video" playsinline autoplay muted loop src="{{ url('img/dentacoin-christmas-calendar-banner.mp4') }}" type="video/mp4" style="width: 100%;margin-bottom: -6px;" controls=""></video>
					<img class="close-banner" id="banner-pc" src="{{ url('new-vox-img/close-popup.png') }}">
					<!-- <img src="{{ url('img/christmas-banner.gif') }}"> -->
				</a>
				<a href="https://dentacoin.com/holiday-calendar/2020" target="_blank" class="christmas-banner mobile-christmas-banner">
					<img class="close-banner" id="banner-mobile" src="{{ url('new-vox-img/close-popup.png') }}">
					<video class="banner-video" playsinline autoplay muted loop src="{{ url('img/dentacoin-christmas-calendar-banner-mobile.mp4') }}" type="video/mp4" style="width: 100%;margin-bottom: -6px;" controls=""></video>
				</a>
			</div>
		@endif

		<div class="tooltip-window" style="display: none;"></div>

		<link rel="stylesheet" type="text/css" href="{{ url('/font-awesome/css/all.min.css') }}" />

        @if(!empty($csscdn) && is_array($csscdn))
            @foreach($csscdn as $file)
				<link rel="stylesheet" type="text/css" href="{{ $file }}" />
            @endforeach
        @endif

		@if($current_page == 'index')
			<script type='application/ld+json'> 
			{
			  	"@context": "http://www.schema.org",
			  	"@type": "Corporation",
			  	"name": "Dentacoin Trusted Reviews",
			  	"description": "{{ trans('trp.schema.description') }}",
			  	"logo": "https://dentacoin.com/assets/uploads/trusted-reviews.svg",
			  	"image": "https://dentacoin.com/assets/uploads/trusted-reviews.svg",
			  	"url": "https://reviews.dentacoin.com/",
			  	"sameAs": ["https://www.facebook.com/dentacoin.trusted.reviews/"],
			  	"contactPoint": {
			    	"@type": "ContactPoint",
			   		"email": "reviews@dentacoin.com",
			    	"url": "https://reviews.dentacoin.com",
			    	"contactType": "customer service"
			    },
			  	"address": {
			    	"@type": "PostalAddress",
			    	"streetAddress": "Wim Duisenbergplantsoen 31, ",
			    	"addressLocality": "Maastricht",
			    	"postalCode": "6221 SE ",
			    	"addressCountry": "Netherlands"
			    },
			    "foundingDate": "08/08/2017",
			    "founders": [
			    {
			        "@type": "Person",
			        "jobTitle": "Founder",
			        "familyName": "Dimitrakiev",
			        "givenName": "Dimitar ",
			        "honorificPrefix": "Prof. Dr. ",
			        "sameAs": "https://www.linkedin.com/in/dimitar-dimitrakiev/"
			        },
			    {
			        "@type": "Person",
			        "familyName": "Grenzebach",
			        "givenName": "Philipp",
			        "jobTitle": "Co-Founder & Business Developer",
			        "sameAs": "https://www.linkedin.com/in/philipp-g-986861146/"
			    },
			    {
			        "@type": "Person",
			        "familyName": "Grenzebach",
			        "givenName": "Jeremias",
			        "jobTitle": "Co-Founder & Core Developer",
			        "sameAs": "https://twitter.com/neptox"
			    }
			    ],
			  	"owns": {
			   		"@type": "Product",
			  		"name": "Dentacoin Trusted Reviews",
			  		"image": "https://dentacoin.com/assets/uploads/trusted-reviews.svg",
			  		"description": "{{ trans('trp.schema.owns.description') }}",
			  		"aggregateRating": {
			    		"@type": "AggregateRating",
			    		"ratingValue": "5",
			    		"ratingCount": "26"
			  		}
				}
			}
			</script>
		@endif

		<script src="{{ url('/js/jquery-3.4.1.min.js') }}"></script>

		@if(empty($user))
			<script src="https://dentacoin.com/assets/libs/dentacoin-login-gateway/js/init.js"></script>
			<script type="text/javascript">
				dcnGateway.init({
					'platform' : '{!! strpos($_SERVER['HTTP_HOST'], 'urgent') !== false ? 'urgent.reviews' : 'trusted-reviews' !!}',
					'forgotten_password_link' : 'https://account.dentacoin.com/forgotten-password?platform=trusted-reviews'
				});
			</script>
		@else
			@if($user->platform != 'external')
				<link rel="stylesheet" type="text/css" href="https://dentacoin.com/assets/libs/dentacoin-package/css/style.css">
				<script src="https://dentacoin.com/assets/libs/dentacoin-package/js/init.js"></script>

				<script type="text/javascript">
					if(typeof dcnHub !== 'undefined') {

						var miniHubParams = {
							'element_id_to_bind' : 'header-avatar',
							'platform' : 'trusted-reviews',
							'log_out_link' : 'https://{!! strpos($_SERVER['HTTP_HOST'], 'urgent') !== false ? 'urgent.reviews' : 'reviews' !!}.dentacoin.com/user-logout'
						};

						miniHubParams.type_hub = '{{ $user->is_dentist ? 'mini-hub-dentists' : 'mini-hub-patients' }}';
						dcnHub.initMiniHub(miniHubParams);
					}
				</script>
			@endif
		@endif

		@if(empty($user) && empty($_COOKIE['performance_cookies']) && empty($_COOKIE['marketing_cookies']) && empty($_COOKIE['strictly_necessary_policy']) && empty($_COOKIE['functionality_cookies']))
			<script src="https://dentacoin.com/assets/libs/dentacoin-package/js/init.js"></script>
			<link rel="stylesheet" type="text/css" href="https://dentacoin.com/assets/libs/dentacoin-package/css/style-cookie.css">

			<script type="text/javascript">
				if (typeof dcnCookie !== 'undefined') {
					dcnCookie.init({
						'google_app_id': 'UA-108398439-1',
						'fb_app_id': '2010503399201502',
						'second_fb_app_id': '2366034370318681',
					});
				}
			</script>
		@endif

		@if(!empty($trackEvents))
	        <script type="text/javascript">
	        	jQuery(document).ready(function($){
		        	@foreach ($trackEvents as $event)
		        		fbq('track', '{{ $event['fb'] }}');
						gtag('event', '{{ $event['ga_action'] }}', {
							'event_category': '{{ $event['ga_category'] }}',
							'event_label': '{{ $event['ga_label'] }}',
						});
		        	@endforeach
			    });
			</script>
		@endif
		@if(!empty( $markLogin )) 
			@include('sso')
		@endif
		@if(!empty( $markLogout )) 
			@include('sso-logout')
		@endif
		
		<script src="{{ url('/js/cookie.min.js') }}"></script>
		<script src="{{ url('/js-trp/main.js').'?ver='.$cache_version }}"></script>
		
        @if(!empty($jscdn) && is_array($jscdn))
            @foreach($jscdn as $file)
                <script src="{{ $file }}"></script>
            @endforeach
        @endif
		
        @if(!empty($js) && is_array($js))
            @foreach($js as $file)
                <script src="{{ url('/js-trp/'.$file).'?ver='.$cache_version }}"></script>
            @endforeach
        @endif
        
        <script type="text/javascript">
        	var lang = '{{ App::getLocale() }}';
        	var user_id = {{ !empty($user) ? $user->id : 'null' }};
        	var images_path = '{{ url('img-trp') }}';
        </script>
    </body>
</html>