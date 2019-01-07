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

        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.1.0/css/all.css" integrity="sha384-lKuwvrZot6UHsBSfcMvOkWwlCMgc0TaWr+30HWe3a4ltaBwTZhyTEggF5tJv8tbt" crossorigin="anonymous">
        @if(config('langs')[App::getLocale()]['rtl'])
        	<link rel="stylesheet" href="//cdn.rawgit.com/morteza/bootstrap-rtl/v3.3.4/dist/css/bootstrap-rtl.min.css" crossorigin="anonymous">
        @else
        	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
        @endif
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">

		{!! config('langs')[App::getLocale()]['font'] !!}
		<link rel="stylesheet" type="text/css" href="{{ url('/css/app.css').'?ver='.$cache_version }}" />
		<link rel="stylesheet" type="text/css" href="{{ url('/css/flickity.min.css').'?ver='.$cache_version }}" />
		<link rel="stylesheet" type="text/css" href="{{ url('/css/new-style-vox.css').'?ver='.$cache_version }}" />
		<link rel="stylesheet" type="text/css" href="{{ url('/css/ids.css').'?ver='.$cache_version }}" />
		<!-- <link rel="stylesheet" type="text/css" href="{{ url('/css/style-vox.css').'?ver='.$cache_version }}" /> -->

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

    <body class="page-{{ $current_page }} sp-{{ $current_subpage }} {{ !empty($satic_page) ? 'page-page' : '' }} {{ (config('langs')[App::getLocale()]['rtl']) ? 'rtl' : 'ltr' }} {{ !empty($user) ? 'logged-in' : 'logged-out' }}">
		<noscript>
			<img height="1" width="1" src="https://www.facebook.com/tr?id=2010503399201502&ev=PageView&noscript=1"/>
		</noscript>
		
		<div class="above-fold">
			<header>
				<div class="container">
					<div class="navbar clearfix">
						<a href="{{ getLangUrl('/') }}" class="logo col-md-4">
							<img src="{{ url('new-vox-img/logo-vox.png') }}" class="desktop">
							<img src="{{ url('new-vox-img/logo-vox-mobile.png') }}" class="mobile">
						</a>
						<div class="header-title col-md-4">
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
						<div class="header-right col-md-4 tar flex">
							@if( $user && $user->status!='approved' )
							@elseif($user)
								<div class="user-and-price header-a">
									<a class="my-name" href="{{ getLangUrl('profile') }}">
										Hello, {{ $user->getName() }}
									</a>
									<a href="{{ getLangUrl('profile') }}">
										<span id="header-balance">{{ $user->getVoxBalance() }}</span> DCN  | <span id="header-usd">${{ sprintf('%.2F', $user->getVoxBalance() * $dcn_price) }}</span>
									</a>
								</div>
								<a class="header-a" href="{{ getLangUrl('profile/info') }}" >
									<img class="header-avatar{!! $user->hasimage ? '' : ' default' !!}" src="{{ $user->getImageUrl(true) }}">
								</a>

								<!-- <a class="header-a" href="{{ getLangUrl('logout') }}"><i class="fas fa-sign-out-alt"></i></a> -->							
								<div class="expander{!! $user->hasimage ? ' has-image' : '' !!}">
									<a href="{{ getLangUrl('logout') }}">
										<i class="fas fa-power-off"></i>
										Log out
									</a>
									<a class="btn" href="{{ getLangUrl('profile') }}">
										My Account
									</a>
									@if($user->status!='approved')
										<span>
											* You cannot access your Profile until it's approved.
										</span>
									@endif
								</div>
							@elseif($current_page=='welcome-survey')
								@if($prev_user)
									<div class="twerk-it">
										<div class="user-and-price header-a">
											<span class="tar">
												Already been here?
											</span>
											<br/>
											<a class="my-name" style="font-weight: bold;" href="{{ getLangUrl('login') }}">
												Log into your Profile!
											</a>
										</div>
										<a class="header-a" href="{{ getLangUrl('login') }}">
											<img class="header-avatar{!! $prev_user->hasimage ? '' : ' default' !!}" src="{{ $prev_user->getImageUrl(true) }}">
										</a>
									</div>

								@endif
							@elseif( $current_page!='register' )
								<span class="dcn-rate">
									1 DCN = $<span id="header-rate">{{ sprintf('%.4F', $dcn_price) }}</span> 
									<!-- <span id="header-change" style="color: #{{ $dcn_change>0 ? '4caf50' : 'e91e63' }};">({{ $dcn_change }}%)</span> -->
								</span>
								<a href="{{ getLangUrl('login') }}" class="start-button">
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



        @if(!empty($user) && $new_auth)
			<div class="new-auth active">
				<div class="wrapper">
					<div class="inner">
						@include('front.errors')
						<h2>
							{!! trans('vox.page.auth.after-login.title') !!}
						</h2>
						<div class="flex break-mobile">
							<p>
								<b>
									{!! trans('vox.page.auth.after-login.dear', [
										'name' => '<span class="blue">'.$user->getName().'</span>'
									]) !!}
								</b>
								<br/>
								<br/>
								@if( $user->grace_end && $user->grace_end->timestamp+86400*31 < time() )
									{!! nl2br(trans('vox.page.auth.after-login.hint-expired')) !!}
								@else
									{!! nl2br(trans('vox.page.auth.after-login.hint-grace',[
										'days' => floor(($user->grace_end->timestamp+86400*31 - time()) / 86400)
									])) !!}
								@endif

							</p>
								
							<div class="form-group buttons">
							  	<div class="col-md-12 text-center">
									<div class="fb-button-inside">
										<a href="{{ getLangUrl('login/facebook') }}" class="">
										</a>
										<div class="fb-login-button" data-max-rows="1" data-size="large" data-button-type="continue_with" data-show-faces="false" data-auto-logout-link="false" data-use-continue-as="false"></div>
									</div>
								</div>

							  	<div class="col-md-12 text-center">

									<form action="{{ getLangUrl('login') }}" method="post">
										<div class="civic-button" id="register-civic-button">
											<i class="fa fa-circle-o-notch fa-spin fa-fw"></i>
											Continue with Civic
										</div>
										{!! csrf_field() !!}
									</form>
									<input type="hidden" id="jwtAddress" value="{{ getLangUrl('login/civic') }}" />
								</div>

								@if( $user->grace_end && $user->grace_end->timestamp+86400*31 < time() )
								@else
								  	<div class="col-md-12 text-center">
										<div class="grace-button" id="grace-button">
											{!! trans('vox.page.auth.after-login.button-grace') !!}
											
										</div>
								  	</div>
								@endif

							</div>
						</div>

						<div id="civic-cancelled" class="alert alert-info" style="display: none;">
							{!! nl2br(trans('front.common.civic.cancelled')) !!}
						</div>
						<div id="civic-error" class="alert alert-warning" style="display: none;">
							{!! nl2br(trans('front.common.civic.error')) !!}
							<span></span>
						</div>
						<div id="civic-weak" class="alert alert-warning" style="display: none;">
							{!! nl2br(trans('front.common.civic.weak')) !!}
						</div>
						<div id="civic-wait" class="alert alert-info" style="display: none;">
							{!! nl2br(trans('front.common.civic.wait')) !!}
						</div>
					</div>
				</div>
			</div>

			<div id="fb-root"></div>
			<script>(function(d, s, id) {
			var js, fjs = d.getElementsByTagName(s)[0];
			if (d.getElementById(id)) return;
			js = d.createElement(s); js.id = id;
			js.src = 'https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.12&appId=1906201509652855&autoLogAppEvents=1';
			fjs.parentNode.insertBefore(js, fjs);
			}(document, 'script', 'facebook-jssdk'));</script>
        @endif
		
		<div class="footer-expander">
			<footer>
				<div class="container clearfix">
					<a href="https://dentacoin.com/" target="_blank" class="footer-logo col-md-3 flex flex-center">
						<img src="{{ url('img-vox/dc-logo.png') }}">
						<p class="bold">
							{{ trans('vox.footer.company-name') }}
						</p>
					</a>
					<div class="footer-text col-md-6 tac">
						<div class="footer-menu">
							<a href="{{ getLangUrl('faq') }}">{{ trans('vox.footer.faq') }}</a>
							<a href="https://dentacoin.com/privacy-policy/" target="_blank">{{ trans('vox.footer.privacy') }}</a>
							<a href="https://reviews.dentacoin.com/" target="_blank">{{ trans('vox.footer.trp') }}</a>
							<a href="https://dentacare.dentacoin.com/" target="_blank">{{ trans('vox.footer.dentacare') }}</a>
						</div>
						<small>
							{{ trans('vox.footer.copyrights') }}
						</small>
					</div>
					<div class="socials col-md-3">
						{{ trans('vox.footer.socials') }}
						 &nbsp;
						<a class="social" href="https://t.me/dentacoin" target="_blank"><i class="fab fa-telegram-plane"></i></a>
						<a class="social" href="https://www.facebook.com/DentaVox-1578351428897849/" target="_blank"><i class="fab fa-facebook-f"></i></a>
					</div>
				</div>
			</footer>
		</div>

		<a id="ids" href="https://ids.dentacoin.com/" target="_blank">
			<i class="fas fa-times-circle"></i>
		</a>

        <script  src="https://code.jquery.com/jquery-3.3.1.min.js"  integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="  crossorigin="anonymous"></script>
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
        	var user_id = {{ !empty($user) ? $user->id : 'null' }};
        </script>
    </body>
</html>