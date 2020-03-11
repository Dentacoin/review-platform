<!DOCTYPE html>
<html>
    <head>
        <base href="{{ url('/') }}">
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="google-site-verification" content="b0VE72mRJqqUuxWJZklHQnvRZV4zdJkDymC0RD9hPhE" />

        <title></title>

		<link rel="stylesheet" type="text/css" href="{{ public_path('css/new-style-vox.css') }}" />
        <link rel="stylesheet" type="text/css" href="{{ public_path('css/daterangepicker.min.css') }}">
        <link rel="stylesheet" type="text/css" href="{{ public_path('css/vox-stats.css') }}">
    </head>

    <body class="page-stats ltr logged-in">
		
		<div class="above-fold">
			<header>
				<div class="container">
					<div class="navbar clearfix">
						<a href="{{ getLangUrl('/') }}" class="logo">
							<img src="{{ url('new-vox-img/logo-vox.png') }}" alt="Dentavox logo" class="desktop">
							<img src="{{ url('new-vox-img/logo-vox-mobile.png') }}" alt="Dentavox logo mobile" class="mobile">
						</a>
					</div>
				</div>
			</header>

			<div class="site-content">
		   		<div class="page-statistics">
		   			<div class="stats">
						{!! $data !!}
					</div>
				</div>
			</div>
		</div>
		
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

		<!-- <link rel="stylesheet" type="text/css" href="{{ url('/font-awesome/css/all.min.css') }}" /> -->
    </body>
</html>