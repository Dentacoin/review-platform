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
        <meta property="og:site_name" content="{{ trans('trp.social.site-name') }}" />
        
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:site" content="@trustedreviews">
        <meta name="twitter:creator" content="@dentacoin">
        <meta name="twitter:title" content="{{ $social_title }}" />
        <meta name="twitter:description" content="{{ $social_description }}" />
        <meta name="twitter:image" content="{{ $social_image }}"/>
        
        <meta name="fb:app_id" content="1906201509652855"/>

        <meta name="csrf-token" content="{{ csrf_token() }}"/>

		<link rel="preload" href="{{ url('fonts/Lato-Black.woff2') }}" as="font" crossorigin>
		<link rel="preload" href="{{ url('fonts/Lato-Bold.woff2') }}" as="font" crossorigin>
		<link rel="preload" href="{{ url('fonts/Lato-Regular.woff2') }}" as="font" crossorigin>
		<link rel="preload" href="{{ url('fonts/Lato-Light.woff2') }}" as="font" crossorigin>

		<link rel="preconnect" href="https://fonts.googleapis.com">
		<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
		
		@if(in_array($current_page, ['review-score-results', 'dentist']))
			<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500,700;800&display=swap" rel="stylesheet">
		@else
			<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;800&display=swap" rel="stylesheet">
		@endif
		
		<link rel="stylesheet" type="text/css" href="{{ url('/css/new-style-trp.css').'?ver='.$cache_version }}" />
		@if($user)
			<link rel="stylesheet" type="text/css" href="{{ url('/css/trp-logged.css').'?ver='.$cache_version }}" />
			<link rel="stylesheet" type="text/css" href="{{ url('/css/trp-search-form.css').'?ver='.$cache_version }}" />
		@endif
		
        @if(!empty($css) && is_array($css))
            @foreach($css as $file)
			
				@if(!empty($user) && $file == 'trp-search-form.css')
				@else
					<link rel="stylesheet" type="text/css" href="{{ url('/css/'.$file).'?ver='.$cache_version }}" />
				@endif
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

    	<div id="site-url" url="{{ empty($_SERVER['REQUEST_URI']) ? getLangUrl('/') : 'https://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'] }}"></div>
		<header class="header">
	       	<nav class="navbar navbar-default navbar-fixed-top">
  				<div class="container">
				    <div class="navbar-header {{ !empty($user) ? '' : 'show-mobile-menu' }}">
						<a class="logo" href="{{ getLangUrl('/') }}"></a>
						<div class="header-info">
	                        @if(!empty($user))
								@if($current_page != 'index')
									<a href="javascript:;" class="search-dentists">
										<img class="fa-search" src="{{ url('img-trp/white-search.svg') }}" width="16" height="15"/>
									</a>
								@endif
								<a href="javascript:;" class="user-profile-info header-avatar" id="header-avatar">
									<span>Rewards: </span>
									<span class="user-balance">{{ number_format($user_total_balance) }} DCN</span>
									<img src="{{ $user_avatar }}" {!! $user->hasimage ? '' : 'class="default-avatar"' !!}>
									@if(!empty($has_review_notification))
										<div class="notification"></div>
									@endif
								</a>
	                        @else
								@include('trp.parts.header-buttons')
	                        @endif
	                        @if(!empty($admin) && count(config('langs.trp')) > 1 && $current_page != 'dentists')
		                        <div class="lang-wrapper">
		                        	<a href="javascript:;">({{ strtoupper(App::getLocale()) }} )</a>
			                        <div class="lang-menu">
			                        	@foreach( config('langs.trp') as $key => $lang)
			                        		@if($key != App::getLocale())
			                        			<a href="{{ getLangUrl(substr($_SERVER['REQUEST_URI'], 4), $key) }}">{{ $lang['name'] }} ({{ strtoupper($key) }})</a>
			                        		@endif
			                        	@endforeach
			                        </div>
			                    </div>
			                @endif
						</div>
						<a class="mobile-menu" href="javascript:;">
							<img src="{{ url('img-trp/mobile-menu.png') }}" width="44" height="22"/>
						</a>
				    </div>
			    </div>
				<div class="menu-primary-container">
					<a href="javascript:;" class="close-menu">
						<img src="{{ url('img-trp/close-menu.svg') }}"/>
					</a>
					@include('trp.parts.header-buttons')
				</div>
		    </div>
		    @if(!empty($user) && $user->is_clinic)
		    	@if($user->branches->isNotEmpty() && isset($clinicBranches))
		    		<input type="hidden" id="clinic-branches" value="{{ $clinicBranches }}">
		    	@endif
		    	<input type="hidden" id="add-branches-popup-link" value="https://reviews.dentacoin.com/en/dentist/{{$user->slug}}/?popup-loged=popup-branch">
		    @endif
	    </header>

	    <div class="site-content">
			@yield('content')
		</div>

		<footer class="{{ !empty($gray_footer) ? 'gray-footer' : '' }}">
			<div class="container clearfix">
				<a href="https://dentacoin.com/" target="_blank" class="footer-logo flex flex-mobile flex-center">
					<img src="{{ url('img-trp/mini-logo-white.svg') }}" alt="Dentacoin logo">
					<p class="bold">
						{!! trans('trp.footer.powered') !!}
					</p>
				</a>
				<div class="footer-text tac">
					<div class="footer-menu">
						<a href="{{ getLangUrl('faq') }}">{{ trans('trp.footer.faq') }}</a>
						<a href="https://support.dentacoin.com/" target="_blank">Help Center</a>
						<a href="https://dentacoin.com/privacy-policy/" target="_blank">{{ trans('trp.footer.privacy-policy') }}</a>
						<a href="https://dentacare.dentacoin.com/" target="_blank">{{ trans('trp.footer.dentacare') }}</a>
						<a href="https://dentavox.dentacoin.com/" target="_blank">{{ trans('trp.footer.vox') }}</a>
					</div>
					<small>
						{!! trans('trp.footer.copyright', [
							'year' => date('Y')
						]) !!}
					</small>
				</div>
				<div class="socials">
					{{-- {!! trans('trp.footer.stay') !!}: &nbsp; --}}
					Follow us: &nbsp;
					<div class="flex flex-mobile">
						<a class="social" href="https://www.facebook.com/dentacoin.trusted.reviews/" target="_blank">
							<img src="{{ url('img/social-network/socials-fb.svg') }}" width="30" height="30"/>
						</a>
						<a class="social" href="https://twitter.com/dentacoin/" target="_blank">
							<img src="{{ url('img/social-network/socials-twitter.svg') }}" width="30" height="30"/>
						</a>
						<a class="social" href="instagram.com/dentacoin_official/" target="_blank">
							<img src="{{ url('img/social-network/socials-instagram.svg') }}" width="30" height="30"/>
						</a>
					</div>
				</div>
			</div>
		</footer>

		@if(false)
			<div class="bottom-drawer">
			</div>
		@endif

		@if(!empty($user))
			<div class="search-results-popup" id="search-results-popup">
				<div class="container">
					<a href="javascript:;" class="close-search-popup">
						<img src="{{ url('img-trp/close-icon.png') }}"/>
					</a>

					@include('trp.parts.search-form')

				</div>
			</div>
		@endif

		<a href="https://support.dentacoin.com/" target="_blank" class="support-icon">
			<img src="{{ url('img/support-icon.png') }}"/ width="60" height="60">
		</a>

		<div class="tooltip-window" style="display: none;"></div>

		{{-- @if(!$without_banner)
			<div class="bottom-drawer">
				<a href="https://dentacoin.com/holiday-calendar/2021" target="_blank" class="christmas-banner">
					<video class="banner-video" playsinline autoplay muted loop src="https://dentacoin.com/assets/videos/dentacoin-christmas-calendar-banner-2021.mp4" type="video/mp4" style="width: 100%;margin-bottom: -6px;" controls=""></video>
					<img class="close-banner" id="banner-pc" src="{{ url('new-vox-img/close-popup.png') }}">
					<!-- <img src="{{ url('img/christmas-banner.gif') }}"> -->
				</a>
				<a href="https://dentacoin.com/holiday-calendar/2021" target="_blank" class="christmas-banner mobile-christmas-banner">
					<img class="close-banner" id="banner-mobile" src="{{ url('new-vox-img/close-popup.png') }}">
					<video class="banner-video" playsinline autoplay muted loop src="https://dentacoin.com/assets/videos/dentacoin-christmas-calendar-banner-mobile-2021.mp4" type="video/mp4" style="width: 100%;margin-bottom: -6px;" controls=""></video>
				</a>
			</div>
		@endif --}}

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
			<script src="https://dentacoin.com/assets/libs/dentacoin-login-gateway/js/init.js?ver={{$cache_version}}"></script>
			@if(strpos($_SERVER['HTTP_HOST'], 'urgent') !== false) 
				<script type="text/javascript">
					dcnGateway.init({
						'platform' : 'urgent.reviews',
						'forgotten_password_link' : 'https://account.dentacoin.com/forgotten-password?platform=trusted-reviews',
						'environment' : 'staging',
					});
				</script>
			@else
				<script type="text/javascript">
					dcnGateway.init({
						'platform' : 'trusted-reviews',
						'forgotten_password_link' : 'https://account.dentacoin.com/forgotten-password?platform=trusted-reviews',
					});
				</script>
			@endif
		@else
			@if($user->platform != 'external')
				<link rel="stylesheet" type="text/css" href="https://dentacoin.com/assets/libs/dentacoin-package/css/style.css?ver={{$cache_version}}">
				<script src="https://dentacoin.com/assets/libs/dentacoin-package/js/init.js?ver={{$cache_version}}"></script>

				<script type="text/javascript">
					if(typeof dcnHub !== 'undefined') {
						@if(strpos($_SERVER['HTTP_HOST'], 'urgent') !== false) 
							var miniHubParams = {
								'element_id_to_bind' : 'header-avatar',
								'platform' : 'trusted-reviews',
								'log_out_link' : 'https://{!! strpos($_SERVER['HTTP_HOST'], 'urgent') !== false ? 'urgent.reviews' : 'reviews' !!}.dentacoin.com/user-logout'
							};
						@else
							var miniHubParams = {
								'notifications_counter' : true,
								'element_id_to_bind' : 'header-avatar',
								'platform' : 'trusted-reviews',
								'log_out_link' : 'https://{!! strpos($_SERVER['HTTP_HOST'], 'urgent') !== false ? 'urgent.reviews' : 'reviews' !!}.dentacoin.com/user-logout'
							};
						@endif

						miniHubParams.type_hub = '{{ $user->is_dentist ? 'mini-hub-dentists' : 'mini-hub-patients' }}';
						dcnHub.initMiniHub(miniHubParams);
					}
				</script>
			@endif
		@endif

		@if(empty($user) && empty($_COOKIE['performance_cookies']) && empty($_COOKIE['marketing_cookies']) && empty($_COOKIE['strictly_necessary_policy']) && empty($_COOKIE['functionality_cookies']))
			<script src="https://dentacoin.com/assets/libs/dentacoin-package/js/init.js?ver={{$cache_version}}"></script>
			<link rel="stylesheet" type="text/css" href="https://dentacoin.com/assets/libs/dentacoin-package/css/style-cookie.css?ver={{$cache_version}}">

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
		@if(!empty( $markLogout )) 
			@include('sso-logout')
		@endif
		@if(!empty( $markLogin )) 
			@include('sso')
		@endif
		
		<script src="{{ url('/js/cookie.min.js') }}"></script>
		<script src="{{ url('/js-trp/main.js').'?ver='.$cache_version }}"></script>

		@if(!empty($user))
			<script src="{{ url('/js-trp/logged.js').'?ver='.$cache_version }}"></script>
			<script src="{{ url('/js-trp/search-form.js').'?ver='.$cache_version }}"></script>
		@endif
		
        @if(!empty($jscdn) && is_array($jscdn))
            @foreach($jscdn as $file)
                <script src="{{ $file }}"></script>
            @endforeach
        @endif
		
        @if(!empty($js) && is_array($js))
            @foreach($js as $file)
				@if(!empty($user) && $file == 'search-form.js')
				@else
                	<script src="{{ url('/js-trp/'.$file).'?ver='.$cache_version }}"></script>
				@endif
            @endforeach
        @endif
        
        <script type="text/javascript">
        	var lang = '{{ App::getLocale() }}';
        	var user_id = {{ !empty($user) ? $user->id : 'null' }};
        	var images_path = '{{ url('img-trp') }}';
        	var all_images_path = '{{ url('img') }}';
        	var lead_magnet_url = '{{ getLangUrl('review-score-test') }}';
        </script>
    </body>
</html>