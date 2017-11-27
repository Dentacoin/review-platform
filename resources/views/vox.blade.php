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
		<link rel="stylesheet" type="text/css" href="{{ url('/css/app.css') }}" />
		<link rel="stylesheet" type="text/css" href="{{ url('/css/flickity.min.css') }}" />
		<link rel="stylesheet" type="text/css" href="{{ url('/css/datepicker.css') }}" />
		<link rel="stylesheet" type="text/css" href="{{ url('/css/style-vox.css') }}" />

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

    </head>

    <body class="page-{{ $current_page }} sp-{{ $current_subpage }} {{ !empty($satic_page) ? 'page-page' : '' }} {{ (config('langs')[App::getLocale()]['rtl']) ? 'rtl' : 'ltr' }}">
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
					</div>
					@if(!empty($user))
						<div class="header-right">
							<a class="header-a" href="{{ getLangUrl('profile') }}">
								{{ $user->name }}
							</a>
							<a class="header-a" href="{{ getLangUrl('logout') }}"><i class="fa fa-sign-out "></i></a>
							<p><a href="{{ getLangUrl('profile/wallet') }}">
								<span id="header-balance">{{ $user->getVoxBalance() }}</span> DCN
							</a></p>
						</div>
					@else
						<a href="javascript:;" data-toggle="modal" data-target="#loginPopup" class="sign-in">
							{{ trans('vox.header.sign-in') }}
						</a>
					@endif
				</div>
			</div>
		</header>


		<div class="site-content">
	   
			@yield('content')

		</div>

		@if(empty($user))
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
							<a href="{{ getLangUrl('login/facebook') }}" class="button-facebook">
								<i class="fa fa-facebook"></i> 
								{{ trans('vox.popup.login.sign-in-facebook') }}
							</a>
							<form action="{{ getLangUrl('login') }}" method="post" id="login-form">
								{!! csrf_field() !!}
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


							<a class="button-facebook" href="{{ getLangUrl('register/facebook') }}">
								<i class="fa fa-facebook"></i> 
								{{ trans('vox.popup.register.facebook') }}
							</a>
							<br/>
							<br/>
                			@include('front.errors')
						</div>
					</div>
				</div>
			</div>
		@endif


		<footer>
			<div class="container clearfix">
				<a href="{{ getLangUrl('/') }}" class="footer-logo">
					<img src="{{ url('img-vox/dc-logo.png') }}">
					<p class="bold">
						{{ trans('vox.footer.company-name') }}
					</p>
				</a>
				<div class="footer-text">
					{{ trans('vox.footer.company-info') }}
				</div>
				<div class="socials">
					<select id="language-selector" class="form-control lang-select" name="languages">
			            @foreach (config('langs') as $key => $lang)
							<option {!! App::getLocale()==$key ? 'selected="selected"' : '' !!} value="{{ $key }}">{{ $lang['name'] }}</option>
					    @endforeach
					</select>
					<a class="social" href="javascript:;"><i class="fa fa-facebook"></i></a>
					<a class="social" href="javascript:;"><i class="fa fa-twitter"></i></a>
				</div>
			</div>
		</footer>

        <script src="https://code.jquery.com/jquery-3.1.0.min.js" integrity="sha256-cCueBR6CsyA4/9szpPfrX3s49M9vUU5BgtiJj06wt/s=" crossorigin="anonymous"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
		<script src="{{ url('/js-vox/bootstrap-datepicker.js') }}"></script>
		<script src="{{ url('/js-vox/flickity.pkgd.min.js') }}"></script>
		<script src="{{ url('/js-vox/main.js') }}"></script>
		@if(!empty($plotly))
			<script src="https://cdn.plot.ly/plotly-latest.min.js"></script>
		@endif
        @if(!empty($js) && is_array($js))
            @foreach($js as $file)
                <script src="{{ url('/js-vox/'.$file) }}"></script>
            @endforeach
        @endif
        <script type="text/javascript">
        	var lang = '{{ App::getLocale() }}';
        </script>
    </body>
</html>