@if(!$is_ajax)

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

        <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
        @if(config('langs')[App::getLocale()]['rtl'])
        	<link rel="stylesheet" href="//cdn.rawgit.com/morteza/bootstrap-rtl/v3.3.4/dist/css/bootstrap-rtl.min.css" crossorigin="anonymous">
        @else
        	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
        @endif
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">

		{!! config('langs')[App::getLocale()]['font'] !!}
		<link rel="stylesheet" type="text/css" href="{{ url('/bxslider/jquery.bxslider.css').'?ver='.$cache_version }}" />
		<link rel="stylesheet" type="text/css" href="{{ url('/css/app.css').'?ver='.$cache_version }}" />
		<link rel="stylesheet" type="text/css" href="{{ url('/css/style.css').'?ver='.$cache_version }}" />
		<link rel="stylesheet" type="text/css" href="{{ url('/css/lightbox.css').'?ver='.$cache_version }}" />

		<style type="text/css">
			body {
				{!! config('langs')[App::getLocale()]['font_css'] !!}
			}
		</style>
		
        @if(!empty($csscdn) && is_array($csscdn))
            @foreach($csscdn as $file)
				<link rel="stylesheet" type="text/css" href="{{ $file }}" />
            @endforeach
        @endif

		@if($current_page=='register')
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

    </head>

    <body class="page-{{ $current_page }} sp-{{ $current_subpage }} {{ !empty($satic_page) ? 'page-page' : '' }} {{ (config('langs')[App::getLocale()]['rtl']) ? 'rtl' : 'ltr' }}">
		<noscript>
			<img height="1" width="1" src="https://www.facebook.com/tr?id=2010503399201502&ev=PageView&noscript=1"/>
		</noscript>
    
	    <header class="header">
	       	<nav class="navbar navbar-default navbar-fixed-top">
  				<div class="container">
				    <div class="navbar-header">
						<a class="navbar-brand" href="{{ getLangUrl('/') }}">
							<img src="{{ url('/img/logo.png') }}" alt="{{ trans('front.social.site-name') }}">
						</a>
						<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#main-menu" aria-expanded="false">
							<span class="sr-only">Toggle navigation</span>
							<span class="icon-bar"></span>
							<span class="icon-bar"></span>
							<span class="icon-bar"></span>
						</button>
				    </div>
				    <div class="collapse navbar-collapse" id="main-menu">
				    	<ul class="nav navbar-nav">
				        	<li {!! $current_page=='index' ? 'class="active"' : '' !!} >
	                            <a href="{{ getLangUrl('/') }}">
	                            	{{ trans('front.common.home') }}
	                            </a>
	                        </li>
				        	<li {!! $current_page=='dentists' || $current_page=='dentist' ? 'class="active"' : '' !!} >
	                            <a href="{{ getLangUrl('dentists') }}">
	                            	{{ trans('front.common.search') }}
	                            </a>
	                        </li>
				            @foreach ($pages_header as $key => $page)
				                <li {!! $current_page==$page->slug ? 'class="active"' : '' !!} >
				                    <a href="{{ getLangUrl($page['slug']) }}" role="button">{{ $page['title'] }}</a>
				                </li>
				            @endforeach
	                        @if(empty($user))
					        	<li>
		                            <a href="{{ url('MetaMaskInstructions.pdf') }}" target="_blank">
		                            	{{ trans('front.common.metamask-instructions') }}
		                            </a>
		                        </li>
	                        @endif
				        </ul>

						<ul class="nav navbar-nav navbar-right">
							<li class="dcn-info">
								@if($user)
									<a href="{{ getLangUrl('profile/wallet') }}">
										<span id="header-balance">{{ $user->getTrpBalance() }}</span> DCN  | <span id="header-usd">${{ sprintf('%.2F', $user->getTrpBalance() * $dcn_price) }}</span>
									</a>
								@endif
								<p class="{{ $user ? '' : 'mt' }}">
									1 DCN = $<span id="header-rate">{{ sprintf('%.4F', $dcn_price) }}</span> 
									<span id="header-change">({{ $dcn_change }}%)</span>
								</p>
							</li>
				        	<li class="dropdown" >
	                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
	                            	@if($user)
		                            	<img class="header-avatar" src="{{ $user->getImageUrl(true) }}">
		                            	{{ $user->getNameShort() }}
	                            	@else
	                            		{{ trans('front.common.profile') }}
	                            	@endif
						          	<span class="caret"></span>
	                            </a>
						        <ul class="dropdown-menu">
									@if($user)
								    	<li  {!! $current_page=='profile' && $current_subpage=='home' ? 'class="active"' : '' !!} >
				                            <a href="{{ $user->is_dentist ? $user->getLink() : getLangUrl('profile') }}">
				                            	{{ trans('front.common.profile') }}
				                            </a>
								    	</li>
								    	<li  {!! $current_page=='profile' && $current_subpage=='info' ? 'class="active"' : '' !!} >
				                            <a href="{{ getLangUrl('profile/info') }}">
				                            	{{ trans('front.common.profile-info') }}
				                            </a>
								    	</li>
								    	@if($user->is_dentist)
									    	<li  {!! $current_page=='profile' && $current_subpage=='gallery' ? 'class="active"' : '' !!} >
					                            <a href="{{ getLangUrl('profile/gallery') }}">
					                            	{{ trans('front.common.gallery') }}
					                            </a>
									    	</li>
									    	<li  {!! $current_page=='profile' && $current_subpage=='invite' ? 'class="active"' : '' !!} >
					                            <a href="{{ getLangUrl('profile/invite') }}">
					                            	{{ trans('front.common.invite') }}
					                            </a>
									    	</li>
								    	@else
									    	<li  {!! $current_page=='profile' && $current_subpage=='reviews' ? 'class="active"' : '' !!} >
					                            <a href="{{ getLangUrl('profile/reviews') }}">
					                            	{{ trans('front.common.my-reviews') }}
					                            </a>
									    	</li>
								    	@endif
						            	<li>
				                            <a href="{{ getLangUrl('logout') }}">
				                            	{{ trans('front.common.log-out') }}
				                            </a>
								    	</li>
			                        @else
				                        <li  {!! $current_page=='login' ? 'class="active"' : '' !!} >
				                            <a href="{{ getLangUrl('login') }}">
				                            	{{ trans('front.common.log-in') }}
				                            </a>
				                        </li>
				                        <li  {!! $current_page=='register' ? 'class="active"' : '' !!} >
				                            <a href="{{ getLangUrl('register') }}">
				                            	{{ trans('front.common.register') }}
				                            </a>
				                        </li>
			                        @endif
						        </ul>
	                        </li>

				            <li class="dropdown lang-dropdown">
					          	<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
						          	{{ config('langs')[App::getLocale()]['name'] }}
						          	<span class="caret"></span>
					          	</a>
						        <ul class="dropdown-menu">
						            @foreach (config('langs') as $key => $lang)
								    	<li>
								    		<a href="{{ url($key) }}">{{ $lang['name'] }}</a>
								    	</li>
								    @endforeach
						        </ul>
					        </li>
						</ul>
					</div>
			    </div>
		    </div>
	    </header>

	    <div class="site-content">
@endif
@yield('content')
@if(!$is_ajax)
		</div>

		<footer class="footer">
			<div class="container">
				<div class="form-group">
		            <div class="col-md-12 logo-col">
		                <a class="logo" href="{{ url('/') }}">
		                    <img src="{{ url('/img/logo.png') }}" alt="">
		                </a>
		                around the web
		            </div>
		        </div>
				<div class="form-group icons-col">
		            <div class="col-md-2">
		            </div>
		            <div class="col-md-1">
		            	<a href="mailto:admin@dentacoin.com" data-toggle="tooltip" data-placement="top" title="Email: admin@dentacoin.com">
		            		<img src="{{ url('img/socials/mail.png') }}">
                        </a>
		            </div>
		            <div class="col-md-1">
		            	<a href="https://fb.me/dentacoin.trusted.reviews" target="_blank" data-toggle="tooltip" data-placement="top" title="Facebook">
		            		<img src="{{ url('img/socials/facebook.png') }}">
                        </a>
		            </div>
		            <div class="col-md-1">
		            	<a href="https://twitter.com/dentacoin" target="_blank" data-toggle="tooltip" data-placement="top" title="Twitter">
		            		<img src="{{ url('img/socials/twitter.png') }}">
                        </a>
		            </div>
		            <div class="col-md-1">
		            	<a href="https://t.me/dentacoin" target="_blank" data-toggle="tooltip" data-placement="top" title="Telegram">
		            		<img src="{{ url('img/socials/telegram.png') }}">
                        </a>
		            </div>
		            <div class="col-md-1">
		            	<a href="https://github.com/Dentacoin" target="_blank"  title="Github" data-toggle="tooltip" data-placement="top" title="GitHub">
		            		<img src="{{ url('img/socials/github.png') }}">
                        </a>
		            </div>
		            <div class="col-md-1">
		            	<a href="https://steemit.com/@dentacoin" target="_blank" data-toggle="tooltip" data-placement="top" title="Steemit">
		            		<img src="{{ url('img/socials/steemit.png') }}">
                        </a>
		            </div>
		            <div class="col-md-1">
		            	<a href="https://medium.com/@dentacoin/" target="_blank" data-toggle="tooltip" data-placement="top" title="Medium">
		            		<img src="{{ url('img/socials/medium.png') }}">
                        </a>
		            </div>
		            <div class="col-md-1">
		            	<a href="https://www.reddit.com/r/Dentacoin/" target="_blank" data-toggle="tooltip" data-placement="top" title="Reddit">
		            		<img src="{{ url('img/socials/reddit.png') }}">
                        </a>
		            </div>
		            <div class="col-md-2">
		            </div>
		        </div>
				<div class="form-group col-links">
		            <div class="col-md-12">
			            @foreach ($pages_footer as $key => $page)
			                <a href="{{ getLangUrl($page['slug']) }}" role="button">{{ $page['title'] }}</a>
			            @endforeach
						<a href="https://dentacoin.com/privacy/" target="_blank">{{ trans('front.footer.privacy') }}</a>
	                </div>
		        </div>
				<div class="form-group col-copyrights">
		            <div class="col-md-12">
		            	{{ trans('front.common.copyrights') }}
		            </div>
		        </div>
	        </div>
        </footer>

        @if($current_page=='dentist')

			<div class="modal fade" tabindex="-1" id="trusted-modal" role="dialog" aria-labelledby="gridSystemModalLabel">
				<div class="modal-dialog" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
							<h4 class="modal-title" id="gridSystemModalLabel">
								{{ trans('front.page.dentist.trusted-title') }}
							</h4>
						</div>
						<div class="modal-body">
							<p>
								{!! trans('front.page.dentist.trusted-hint') !!}
		                	</p>
		                	<a href="#" target="_blank" class="btn btn-primary btn-block">
		                		{!! trans('front.page.dentist.trusted-more') !!}
		                	</a>
			  			</div>
			  		</div>
			  	</div>
			</div>
        @endif


        @if($user && !$user->gdpr_privacy)
        	<div class="modal active" tabindex="-1" id="gdprPopup" role="dialog" aria-labelledby="gridSystemModalLabel">
				<div class="modal-dialog" role="document">
					<div class="modal-content">
						<div class="modal-body">
							<img src="{{ url('img/popups/GDPR-policy.png') }}">
							<h2>
								{{ trans('front.page.gdpr.title') }}
							</h2>
							<p>
								{!! nl2br(trans('front.page.gdpr.description', [
									'gdrplink' => '<a href="https://www.eugdpr.org/" target="_blank">' ,
									'endgdrplink' => '</a>' ,
									'privacylink' => '<a href="https://dentacoin.com/privacy/" target="_blank">', 
									'endprivacylink' => '</a>'
								])) !!}
							</p>

							<a href="javascript:;" class="agree-gdpr">{{ trans('front.page.gdpr.agree') }}</a>
						</div>
					</div>
				</div>
			</div>
        @endif
			

        @if( $current_page=='dentist' || ($current_page=='profile' && !empty($current_subpage) && $current_subpage=='invite' ) )
			<div class="modal fade" tabindex="-1" id="no-wallet-modal" role="dialog" aria-labelledby="gridSystemModalLabel">
				<div class="modal-dialog" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
							<h4 class="modal-title" id="gridSystemModalLabel">
								{{ trans('front.page.dentist.no-wallet-title') }}
							</h4>
						</div>
						<div class="modal-body">
							<p>
								{!! trans('front.page.dentist.no-wallet-hint') !!}
		                	</p>
							<p>
								<div class="videoWrapper">
									<iframe width="560" height="315" src="https://www.youtube-nocookie.com/embed/{!! trans('front.page.dentist.no-wallet-youtube') !!}?rel=0&amp;controls=1&amp;showinfo=0" frameborder="0" allowfullscreen></iframe>
								</div>
		                	</p>
			  			</div>
			  		</div>
			  	</div>
			</div>
        @endif

        @if(!empty($user) && $new_auth)
			<div class="new-auth">
				<div class="wrapper">
					<div class="inner">
						@include('front.errors')
						<h2>
							{!! trans('front.page.auth.after-login.title') !!}
						</h2>
						<div class="flex break-mobile">
							<p>
								<b>
									{!! trans('front.page.auth.after-login.dear', [
										'name' => '<span class="blue">'.$user->getName().'</span>'
									]) !!}
								</b><br/>
								<br/>
								@if( $user->grace_end && $user->grace_end->timestamp+86400*31 < time() )
									{!! nl2br(trans('front.page.auth.after-login.hint-expired')) !!}
								@else
									{!! nl2br(trans('front.page.auth.after-login.hint-grace',[
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
											{!! trans('front.page.auth.after-login.button-grace') !!}
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

        @endif


        <script  src="https://code.jquery.com/jquery-3.3.1.min.js"  integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="  crossorigin="anonymous"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
		<script src="{{ url('/js/lightbox.js').'?ver='.$cache_version }}"></script>
		<script src="{{ url('/js/main.js').'?ver='.$cache_version }}"></script>
        @if( $current_page=='dentist' )
			<script src="//vjs.zencdn.net/6.4.0/video.min.js"></script>
			<script src="//cdn.WebRTC-Experiment.com/RecordRTC.js"></script>
			<script src="//webrtc.github.io/adapter/adapter-latest.js"></script>
        @endif
        @if(!empty($js) && is_array($js))
            @foreach($js as $file)
                <script src="{{ url('/js/'.$file).'?ver='.$cache_version }}"></script>
            @endforeach
        @endif
        @if(!empty($jscdn) && is_array($jscdn))
            @foreach($jscdn as $file)
                <script src="{{ $file }}"></script>
            @endforeach
        @endif
        
		<script src="{{ url('/bxslider/jquery.bxslider.js').'?ver='.$cache_version }}"></script>
        <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBPkGoYKU_yq1H6Z5IjojyDO-WoLOTSsjs&libraries=places&callback=initMap&language=en" async defer></script>
        <script type="text/javascript">
        	var areYouSure = '{{ trans('front.common.sure') }}';
        	var lang = '{{ App::getLocale() }}';
        	var user_id = {{ !empty($user) ? $user->id : 'null' }};
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
@endif