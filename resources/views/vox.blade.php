<!DOCTYPE html>
<html>
    <head>
        <base href="{{ url('/') }}">
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="google-site-verification" content="b0VE72mRJqqUuxWJZklHQnvRZV4zdJkDymC0RD9hPhE" />

        @if(!empty($noindex))
        	<meta name="robots" content="noindex">
        @endif

        <title>{{ $seo_title }}</title>
        <meta name="description" content="{{ $seo_description }}">
        <link rel="canonical" href="{{ $canonical }}" />

        @if(!empty($keywords))
        	<meta name="keywords" content="{{ $keywords }}">
        @endif

        <meta property="og:locale" content="{{ App::getLocale() }}" />
        <meta property="og:title" content="{{ $social_title }}"/>
        <meta property="og:description" content="{{ $social_description }}"/>
        <meta property="og:image" content="{{ $social_image }}"/>
        <meta property="og:site_name" content="{{ trans('vox.social.site-name') }}" />
        
        <meta name="twitter:card" content="summary" />
        <meta name="twitter:title" content="{{ $social_title }}" />
        <meta name="twitter:description" content="{{ $social_description }}" />
        <meta name="twitter:image" content="{{ $social_image }}"/>

        <meta name="csrf-token" content="{{ csrf_token() }}"/>

		{!! config('langs')[App::getLocale()]['font'] !!}

		<link rel="stylesheet" type="text/css" href="{{ url('/css/new-style-vox.css').'?ver='.$cache_version }}" />

        @if(!empty($css) && is_array($css))
            @foreach($css as $file)
				<link rel="stylesheet" type="text/css" href="{{ url('/css/'.$file).'?ver='.$cache_version }}" />
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

			@if(empty($_COOKIE['performance_cookies']))
				gtag('config', 'UA-108398439-2', {'anonymize_ip': true});
			@else
				gtag('config', 'UA-108398439-2');
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
		<meta name="msapplication-TileColor" content="#ffffff">
		<meta name="msapplication-TileImage" content="{{ url('vox-fav/ms-icon-144x144.png') }}">
		<meta name="theme-color" content="#ffffff">

    </head>

    <body class="page-{{ $current_page }} sp-{{ $current_subpage }} {{ (config('langs')[App::getLocale()]['rtl']) ? 'rtl' : 'ltr' }} {{ !empty($user) ? 'logged-in' : 'logged-out' }} {{ !empty($custom_body_class) ? $custom_body_class : '' }}">
		<noscript>
			<img height="1" width="1" src="https://www.facebook.com/tr?id=2010503399201502&ev=PageView&noscript=1"/>
		 	<img height="1" width="1" src="https://www.facebook.com/tr?id=2366034370318681&ev=PageView&noscript=1"/>
		</noscript>

		<div id="site-url" url="{{ empty($_SERVER['REQUEST_URI']) || $_SERVER['REQUEST_URI'] == '/en/welcome-survey/' ? getLangUrl('/') : 'https://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'] }}"></div>
		
		<div class="above-fold">
			<header>
				<div class="container">
					<div class="navbar clearfix {{ !empty($user) && $user->platform == 'external' ? 'external-navbar' : '' }}">
						<a href="{{ getLangUrl('/') }}" class="logo">
							<img src="{{ url('new-vox-img/logo-vox.png') }}" alt="Dentavox logo" class="desktop">
							<img src="{{ url('new-vox-img/logo-vox-mobile.png') }}" alt="Dentavox logo mobile" class="mobile">
						</a>
						<div class="header-title">
							@if($current_page=='index')
								<table>
									<tr>
										<td class="tar"><b>{{ number_format($users_count, 0, '', ' ') }}</b></td>
										<td>{{ trans('vox.header.users-count') }}</td>
									</tr>
									<tr>
										<td class="tar"><b id="header_questions">{{ number_format($header_questions, 0, '', ' ') }}</b></td>
										<td>{!! trans('vox.header.question-count', ['count' => '' ]) !!}</td>
									</tr>
								</table>
							@endif
						</div>
						<div class="header-right tar flex">
							@if( !empty($user) && $user->status!='approved' && $user->status!='test' && $user->status!='added_by_clinic_claimed' && $user->status!='added_by_dentist_claimed' )
							@elseif($user)
								<div class="user-and-price header-a">
									@if( $user->platform=='external' )
										<r style="display: block; color: #38ace5;">Hello, {{ $user->getName() }}</r>
										<span id="header-balance">{{ $user->getTotalBalance() }}</span> DCN  | <span id="header-usd">${{ sprintf('%.2F', $user->getTotalBalance() * $dcn_price) }}</span>
									@else
										<a class="my-name" href="https://account.dentacoin.com/?platform=dentavox">
											Hello, {{ $user->getName() }}
										</a>
										<a href="https://account.dentacoin.com/?platform=dentavox">
											<span id="header-balance">{{ $user->getTotalBalance() }}</span> DCN  | <span id="header-usd">${{ sprintf('%.2F', $user->getTotalBalance() * $dcn_price) }}</span>
										</a>
									@endif
								</div>
								@if( $user->platform!='external' )
									<a class="header-a" href="https://account.dentacoin.com/?platform=dentavox" >
										<img class="header-avatar" src="{{ $user->getImageUrl(true) }}">
									</a>
								@endif

								<!-- <a class="header-a" href="{{ getLangUrl('logout') }}"><i class="fas fa-sign-out-alt"></i></a> -->							
								@if( $user->platform!='external' && !empty(getDentacoinHubApplications()) )
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
													<a class="btn" href="https://account.dentacoin.com/?platform=dentavox">
														My Account
													</a>
												</div>
											</div>
										</div>
									</div>
								@endif
							@elseif($current_page=='welcome-survey')
								@if(!empty($prev_user))
									<div class="twerk-it">
										<div class="user-and-price header-a">
											<span class="tar">
												Already been here?
											</span>
											<br/>
											<a class="my-name open-dentacoin-gateway patient-login" style="font-weight: bold;" href="javascript:;">
												Log into your Profile!
											</a>
										</div>
										<a class="header-a open-dentacoin-gateway patient-login" href="javascript:;">
											<img class="header-avatar" src="{{ $prev_user->getImageUrl(true) }}">
										</a>
									</div>

								@endif
							@elseif( $current_page!='register' || (!empty($session_polls) && $current_page=='register') )
								<span class="dcn-rate">
									1 DCN = $<span id="header-rate">{{ sprintf('%.6F', $dcn_price) }}</span> 
									<!-- <span id="header-change" style="color: #{{ $dcn_change>0 ? '4caf50' : 'e91e63' }};">({{ $dcn_change }}%)</span> -->
								</span>
								<a href="javascript:;" class="start-button open-dentacoin-gateway patient-login">
									Log in
								</a>
							@endif
						</div>
					</div>
				</div>
			</header>

			<div class="site-content">
		   
				@yield('content')

			</div>
		</div>


		@if(!empty($user))
			@include('vox.popups.recommend')
		@endif

		<style type="text/css">
			.page-profile .poll-bubble {
				display: none !important;
			}
		</style>

		@if((!empty($daily_poll) && empty($taken_daily_poll) && $current_page != 'questionnaire' && $current_page != 'register' && $current_page != 'login' && request()->getHost() != 'vox.dentacoin.com' && request()->getHost() != 'account.dentacoin.com' && empty($session_polls)) || $current_page == 'daily-polls' || !empty($closed_daily_poll) && $current_page != 'questionnaire')
			@if(!empty($user) && $user->loggedFromBadIp())

			@else
				@include('vox.popups.daily-poll')
			@endif
		@endif

		@if(!empty($unbanned))
			<div class="popup unbanned active">
				<div class="wrapper">
					<img src="{{ url('new-vox-img/back-from-ban'.$unbanned_times.'.png') }}" class="zman" />
					<div class="inner">
						<h2>
							{!! nl2br(trans('vox.page.bans.unbanned-title', [
								'name' => $user->getName()
							])) !!}
						</h2>
						<p>
							{!! $unbanned_text !!}
						</p>
						<div class="flex break-mobile">
							<div class="bans-received">
								{!! nl2br(trans('vox.page.bans.unbanned-received')) !!}
								<div class="flex">
									@for($i=1;$i<=4;$i++)
										<img src="{{ url('new-vox-img/popup-sign-'.($i==4 ? '5' : ( $i<=$unbanned_times ? $i : '0' )).'.png') }}" />
									@endfor
								</div>
							</div>
							<a class="btn closer btn-unban btn-unban-{{ $unbanned_times }}">
								{!! nl2br(trans('vox.page.bans.unbanned-button')) !!}
							</a>
						</div>
					</div>
					<a class="closer x">
						<i class="fas fa-times"></i>
					</a>
				</div>
			</div>
		@endif
		
		<div class="footer-expander">
			<footer>
				<div class="container flex flex-end">
					<a href="https://dentacoin.com/" target="_blank" class="footer-logo flex-3 flex flex-center">
						<img src="{{ url('img-vox/dc-logo.png') }}" alt="Dentacoin logo">
						<p class="bold">
							{{ trans('vox.footer.company-name') }}
						</p>
					</a>
					<div class="footer-text flex-6 tac">
						<div class="footer-menu">
							<a href="{{ getLangUrl('daily-polls') }}">{{ trans('vox.footer.daily-polls') }}</a>
							<a href="{{ getLangUrl('dental-survey-stats') }}">{{ trans('vox.footer.stats') }}</a>
							<a href="https://dentavox.dentacoin.com/blog/" target="_blank">{{ trans('vox.footer.blog') }}</a>
							<a href="{{ getLangUrl('faq') }}">{{ trans('vox.footer.faq') }}</a>
							<a class="privacy-item" href="https://dentacoin.com/privacy-policy/" target="_blank">{{ trans('vox.footer.privacy') }}</a>
						</div>
						<small>
							{{ trans('vox.footer.copyrights', [
								'year' => date('Y')
							]) }}
						</small>
					</div>
					<div class="socials flex-3">
						{{ trans('vox.footer.socials') }}
						 &nbsp;
						<a class="social" href="https://t.me/dentacoin" target="_blank"><i class="fab fa-telegram-plane"></i></a>
						<a class="social" href="https://www.facebook.com/DentaVox-1578351428897849/" target="_blank"><i class="fab fa-facebook-f"></i></a>
					</div>
					<a class="privacy-item-mobile" href="https://dentacoin.com/privacy-policy/" target="_blank">{{ trans('vox.footer.privacy') }}</a>
				</div>
			</footer>
		</div>

		<div class="bottom-drawer">

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

		<div class="tooltip-window" style="display: none;"></div>

		<div class="doublecoin-tooltip" style="display: none;">
			{!! nl2br( trans('vox.common.featured-tooltip') ) !!}
			<span></span>
		</div>

		<script type='application/ld+json'> 
			{
		  		"@context": "http://www.schema.org",
				"@type": "Corporation",
				"name": "DentaVox",
				"description": "DentaVox aims to improve global dental care by supplying the industry with valuable patient insights on various dental health topics. Respondents are rewarded with the first dedicated currency Dentacoin that can be used to cover preventive services and other treatments. DentaVox is a focal point for dental professionals, manufacturers and patients from all corners of the world.",
				"logo": "https://dentavox.dentacoin.com/new-vox-img/logo-vox.png",
				"image": "https://dentavox.dentacoin.com/new-vox-img/logo-vox.png",
				"url": "https://dentavox.dentacoin.com",
				"sameAs": ["https://www.facebook.com/dentavox.dentacoin/"],
				"address": {
	   				"@type": "PostalAddress",
				    "streetAddress": "Wim Duisenbergplantsoen 31, ",
				    "addressLocality": "Maastricht",
				    "postalCode": "6221 SE ",
				    "addressCountry": "Netherlands"
	    		},
			    "foundingDate": "03/22/2017",
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
					"name": "DentaVox",
					"image": "https://dentavox.dentacoin.com/new-vox-img/logo-vox.png",
					"description": "Take genuine paid surveys online and get rewarded! DentaVox is a market research platfom designed to provide valuable patient insights to the dental industry. Our large database with reliable dental statistics is available for free for anyone who's interested. Feel free to become a respondent yourself and help improve global dental care while also earning your first Dentacoin tokens with DentaVox.",
		  			"aggregateRating": {
					    "@type": "AggregateRating",
					    "ratingValue": "5",
					    "ratingCount": "31"
		  			}
				}
			}
		</script>

		<!-- css -->
        @if($current_page == 'daily-polls')
        	<link rel="stylesheet" type="text/css" href="{{ url('/calendar/core/main.min.css').'?ver='.$cache_version }}" />
        	<link rel="stylesheet" type="text/css" href="{{ url('/calendar/daygrid/main.min.css').'?ver='.$cache_version }}" />
        	<link rel="stylesheet" type="text/css" href="{{ url('/calendar/timegrid/main.min.css').'?ver='.$cache_version }}" />
        	<link rel="stylesheet" type="text/css" href="{{ url('/calendar/list/main.min.css').'?ver='.$cache_version }}" />
        @endif

        @if(!empty($csscdn) && is_array($csscdn))
            @foreach($csscdn as $file)
				<link rel="stylesheet" type="text/css" href="{{ $file }}" />
            @endforeach
        @endif

        <link rel="stylesheet" type="text/css" href="https://dentacoin.com/assets/libs/dentacoin-login-gateway/css/dentacoin-login-gateway-style.css?v={{ $cache_version }}"/>
		<link rel="stylesheet" type="text/css" href="{{ url('/font-awesome/css/all.min.css') }}" />
		<!-- end css -->

		<!-- js -->
		<script src="{{ url('/js/jquery-3.4.1.min.js') }}"></script>
		<script src="https://dentacoin.com/assets/libs/dentacoin-login-gateway/js/init.js?v={{ $cache_version }}"></script>

		@if(empty($user))
			<script type="text/javascript">
				dcnGateway.init({
					'platform' : '{!! strpos($_SERVER['HTTP_HOST'], 'urgent') !== false ? 'urgent.dentavox' : 'dentavox' !!}',
					'forgotten_password_link' : 'https://account.dentacoin.com/forgotten-password?platform=dentavox'
				});				
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
		@if(!empty( $markLogin ) || $current_page=='banned' ) 
			@if(!empty($user) && $user->platform == 'external')

			@else
				@include('sso')
			@endif
		@endif
		@if(!empty( $markLogout )) 
			@if(!empty($user) && $user->platform == 'external')

			@else
				@include('sso-logout')
			@endif
		@endif

		<script src="{{ url('/js/cookie.min.js') }}"></script>
		<script src="{{ url('/js-vox/main.js').'?ver='.$cache_version }}"></script>
		<script src="{{ url('/js/both.js').'?ver='.$cache_version }}"></script>
        @if(!empty($js) && is_array($js))
            @foreach($js as $file)
                <script src="{{ url('/js-vox/'.$file).'?ver='.$cache_version }}"></script>
            @endforeach
        @endif
        @if($current_page == 'daily-polls')
        	<script src="{{ url('/calendar/core/main.min.js') }}"></script>
        	<script src="{{ url('/calendar/interaction/main.min.js') }}"></script>
        	<script src="{{ url('/calendar/daygrid/main.min.js') }}"></script>
        	<script src="{{ url('/calendar/timegrid/main.min.js') }}"></script>
        	<script src="{{ url('/calendar/list/main.min.js') }}"></script>
        @endif
        @if(!empty($jscdn) && is_array($jscdn))
            @foreach($jscdn as $file)
                <script src="{{ $file }}"></script>
            @endforeach
        @endif
        <script type="text/javascript">
        	var images_path = '{{ url('img-trp') }}'; //Map pins
        	var lang = '{{ App::getLocale() }}';
        	var user_id = {{ !empty($user) ? $user->id : 'null' }};
        </script>
        <!-- endjs -->
    </body>
</html>