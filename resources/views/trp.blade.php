<!DOCTYPE html>
<html>
    <head>
        <base href="{{ url('/') }}">
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
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

		<link rel="stylesheet" type="text/css" href="{{ url('/css/new-style-trp.css').'?ver='.$cache_version }}" />
		
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
		<script>
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

    <body class="page-{{ $current_page }} sp-{{ $current_subpage }} {{ !empty($extra_body_class) ? $extra_body_class : '' }} {{ !empty($satic_page) ? 'page-page' : '' }} {{ (config('langs')[App::getLocale()]['rtl']) ? 'rtl' : 'ltr' }}">
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
								<a href="https://account.dentacoin.com/?platform=trusted-reviews" class="profile-btn">
									<span class="name">
										{{ $user->getNameShort() }}
									</span>
									<img src="{{ $user->getImageUrl(true) }}" {!! $user->hasimage ? '' : 'class="default-avatar"' !!}>
								</a>

								<!-- <a class="header-a" href="{{ getLangUrl('logout') }}"><i class="fas fa-sign-out-alt"></i></a> -->							
								<div class="expander-wrapper{!! $user->hasimage ? ' has-image' : '' !!}">
									<div class="expander">
										<a href="javascript:;" class="close-explander">Close<span>X</span></a>
										<div class="expander-content">
											@foreach(getDentacoinHubApplications() as $dcn_platform)
										        <a href="{{ $dcn_platform->link ? $dcn_platform->link : 'javascript:;' }}" target="_blank" class="platform-icon">
										            <figure class="text-center" itemtype="http://schema.org/ImageObject">
										               	<img src="{{ $dcn_platform->media_name }}" itemprop="contentUrl" alt="{{ $dcn_platform->media_alt }}"> 
										               	<figcaption>{{ $dcn_platform->title }}</figcaption>
										            </figure>
										        </a>
										    @endforeach
										</div>
										<div class="expander-footer">
											<div class="col">
												<a href="{{ getLangUrl('logout') }}">
													<i class="fas fa-power-off"></i>
													Log out
												</a>
											</div>
											<div class="col">
												<a class="btn" href="https://account.dentacoin.com/?platform=trusted-reviews">
													My Account
												</a>
											</div>
										</div>
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
								<a href="javascript:;" class="button-sign-in {{ $current_page!='welcome-dentist' ? 'button-login-patient' : '' }}" data-popup="popup-login">
									{{ $current_page=='welcome-dentist' ? 'Log in' : 'Sign in' }}
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

		<div class="footer-expander {{ !empty($gray_footer) ? 'gray-footer' : '' }}">
			<footer>
				<div class="container clearfix">
					<a href="https://dentacoin.com/" target="_blank" class="footer-logo col-md-3 flex break-mobile flex-center">
						<img src="{{ url('img-trp/dc-logo.png') }}" alt="Dentacoin logo">
						<p class="bold">
							Powered by Dentacoin
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
							Copyright © 2019. Dentacoin Foundation. All rights reserved
						</small>
					</div>
					<div class="socials col-md-3">
						Stay in the loop: &nbsp;
						<a class="social" href="https://t.me/dentacoin" target="_blank"><i class="fab fa-telegram-plane"></i></a>
						<a class="social" href="https://www.facebook.com/dentacoin.trusted.reviews/" target="_blank"><i class="fab fa-facebook-f"></i></a>
					</div>
				</div>

				@if($current_page == 'welcome-dentist' && !empty($_COOKIE['marketing_cookies']) )
					<script>
			            (function(w,d,t,u,n,a,m){
			                if(typeof w['AriticTrackingObject'] !== 'undefined') return;w['AriticTrackingObject']=n;
			                w[n]=w[n]||function(){(w[n].q=w[n].q||[]).push(arguments)},a=d.createElement(t),
			                m=d.getElementsByTagName(t)[0];a.async=1;a.src=u;m.parentNode.insertBefore(a,m)
			            })(window,document,'script','https://dentacoin.ariticapp.com/ma/atc.js','at');

			        </script> 
			        <script type="text/javascript" src="https://dentacoin.ariticapp.com/ma/patc.js"></script>
			        <script type="text/javascript">
				        _aaq.push(['trackPageView']);
				    </script>

				    <script type="text/javascript">
					    function LeadMagenet() {
					    	setTimeout( function() {
					    		_aaq.push(['setContactFields', {
			                        firstname:document.getElementById("magnet-name").value,
			                        website:document.getElementById("magnet-website").value,
			                        email:document.getElementById("magnet-email").value,
			                    }]);

                    			//_aaq.push('rememberConsentGiven', false, 3);
                    			_aaq.push(['trackPageView']);
                    		}, 5000);
						}
					</script>
				@endif
			</footer>
		</div>

		@if($current_page != 'profile')
			<div class="bottom-drawer">
				<!-- <div id="cookiebar" >
					<p>
						{!! nl2br( trans('trp.common.cookiebar-hint',[
							'link' => '<a href="https://dentacoin.com/privacy-policy" target="_blank">',
							'endlink' => '</a>',
						]) ) !!}
					</p>
					<a class="accept" href="javascript:;">
						{!! nl2br( trans('trp.common.cookiebar-button') ) !!}
					</a>
				</div> -->

				@if(empty($user) && empty($_COOKIE['performance_cookies']) && empty($_COOKIE['marketing_cookies']) && empty($_COOKIE['strictly_necessary_policy']) && empty($_COOKIE['functionality_cookies']))
					<div class="privacy-policy-cookie">
						<div class="container-cookie flex">
							<div class="cookies-text">
								This site uses cookies. Find out more on how we use cookies in our <a href="https://dentacoin.com/privacy-policy" target="_blank">Privacy Policy</a>. | <a href="javascript:;" class="adjust-cookies">Adjust cookies</a>
							</div>
							<a href="javascript:;" class="accept-all">Accept all cookies</a>
						</div>
						<div id="customize-cookies" class="customize-cookies" style="display: none;">
							<button class="close-customize-cookies-icon close-customize-cookies-popup">×</button>
							<div class="tac"><img src="/img-trp/cookie-icon.svg" alt="Cookie icon" class="cookie-icon"/></div>
							<div class="tac cookie-popup-title">Select cookies to accept:</div>
							<div class="cookies-options-list flex">
								<div class="cookie-checkbox-wrapper">
									<label class="cookie-label active" for="strictly-necessary-cookies">
										<i class="far fa-square checkbox-icon"></i>
										<input checked disabled id="strictly-necessary-cookies" type="checkbox" class="cookie-checkbox">
										<span class="gray">Strictly necessary</span> 
										<i class="fas fa-info-circle info-cookie tooltip-text" text="Cookies essential to navigate around the website and use its features. Without them, you wouldn’t be able to use basic services like signup or login."></i>
									</label>
								</div>
								<div class="cookie-checkbox-wrapper">
									<label class="cookie-label active" for="performance-cookies">
										<i class="far fa-square checkbox-icon"></i>
										<input checked id="performance-cookies" type="checkbox" class="cookie-checkbox">
										<span>Performance cookies</span> 
										<i class="fas fa-info-circle info-cookie tooltip-text" text="These cookies collect data for statistical purposes on how visitors use a website, they don’t contain personal data and are used to improve user experience."></i>
									</label>
								</div>
								<div class="cookie-checkbox-wrapper">
									<label class="cookie-label active" for="functionality-cookies">
										<i class="far fa-square checkbox-icon"></i>
										<input checked id="functionality-cookies" type="checkbox" class="cookie-checkbox">
										<span>Functionality cookies</span> 
										<i class="fas fa-info-circle info-cookie tooltip-text" text="These cookies allow users to customise how a website looks for them; they can remember usernames, preferences, etc."></i>
									</label>
								</div>
								<div class="cookie-checkbox-wrapper">
									<label class="cookie-label active" for="marketing-cookies">
										<i class="far fa-square checkbox-icon"></i>
										<input checked id="marketing-cookies" type="checkbox" class="cookie-checkbox">
										<span>Marketing cookies</span> 
										<i class="fas fa-info-circle info-cookie tooltip-text" text="Marketing cookies are used e.g. to deliver advertisements more relevant to you or limit the number of times you see an advertisement."></i>
									</label>
								</div>
							</div>
							<div class="flex actions">
								<a href="javascript:;" class="close-cookie-button close-customize-cookies-popup">CANCEL</a>
								<a href="javascript:;" class="custom-cookie-save">SAVE</a>
							</div>
							<div class="custom-triangle"></div>
						</div>
					</div>
				@endif

			</div>
		@endif

		<div class="tooltip-window" style="display: none;"></div>

		@include('trp/popups/share')
		@if(empty($user))
			@include('trp/popups/register')
			@include('trp/popups/dentist-verification')
			@include('trp/popups/banned')
			@include('trp/popups/suspended')
			@if($current_page == 'welcome-dentist' || $current_page == 'dentist')
				@include('trp/popups/claim-profile')
			@endif
		@else
			@include('trp/popups/invite-new-dentist')
			@include('trp/popups/invite-new-dentist-success')
		@endif


		<link rel="stylesheet" type="text/css" href="{{ url('/font-awesome/css/all.min.css') }}" />
		
		@if( $current_page=='dentist' )
			<link rel="stylesheet" type="text/css" href="{{ url('/css/lightbox.css').'?ver='.$cache_version }}" />
		@endif

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
			  "description": "Through creating and implementing the first Blockchain-based platform for trusted dental treatment reviews, Dentacoin Trusted Reviews allows patients voices to be heard and provides dentists with access to up-to-date, extremely valuable market research data and qualified patient feedback - the most powerful tool to improve service quality and to establish a loyal patient base.",
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
			  "description": "Dentacoin Trusted Reviews is the first platform for detailed, verified and incentivized dental treatment reviews. Patients are invited by their dentists, verified through Blockchain-based identity system and rewarded for providing valuable feedback. Dentists have the chance to improve upon the feedback received and are incentivized for willing to do so.",
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

        <script src="https://cdn.jsdelivr.net/npm/js-cookie@2/src/js.cookie.min.js"></script>
		<script src="{{ url('/js-trp/main.js').'?ver='.$cache_version }}"></script>
		<script src="{{ url('/js/both.js').'?ver='.$cache_version }}"></script>
		
        @if( $current_page=='dentist' )
			<script src="//vjs.zencdn.net/6.4.0/video.min.js"></script>
			<script src="//cdn.WebRTC-Experiment.com/RecordRTC.js"></script>
			<script src="//webrtc.github.io/adapter/adapter-latest.js"></script>
			<script src="{{ url('/js/lightbox.js').'?ver='.$cache_version }}"></script>
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
    </body>
</html>