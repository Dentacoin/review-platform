<!DOCTYPE html>
<html>
    <head>
        <base href="{{ url('/') }}">
        <meta charset="utf-8">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="robots" content="noindex">
        <meta name="google-site-verification" content="b0VE72mRJqqUuxWJZklHQnvRZV4zdJkDymC0RD9hPhE" />


        <title>{!! $title !!}</title>

		<style type="text/css">

			@font-face {
			    font-family: 'Nunito';
			    src: url("fonts/Nunito-Bold.ttf") format("truetype");
			    font-weight: 700;
			    font-style: bold;
			}
			@font-face {
			    font-family: 'Nunito';
			    src: url("fonts/Nunito-Regular.ttf") format("truetype");
			    font-weight: normal;
			    font-style: normal;
			}

			body {
			    font-family: 'Nunito';
			}

			h1 {
				font-family: 'Nunito';
				font-size: 22px;
			    text-transform: uppercase;
			    color: #020202;
			    text-align: center;
			    margin-bottom: 45px;
			    position: relative;
			    margin-top: -60px;
			    display: block;
			}

			#to-export img {
				display: block;
				width: 100%;
				max-width: 100%;
			}

			/*@media screen and (max-width: 1200px) {
				#to-export img {
					display: block;
					width: auto;
					max-width: auto;
					max-height: 400px;
				}
			}*/

			@page { margin: 0px; }
			body { margin: 0px; }

			.footer-expander {
				position: relative;
			}

			.footer-expander p {
				position: absolute;
				top: 40px;
				width: 100%;
				text-align: center;
				font-size: 7px;
			}

			footer {
				position: absolute;
				bottom: 0px;
			}
		</style>
    </head>

    <body class="page-stats ltr logged-in">
		
		<div class="above-fold">
			<img src="{{ url('new-vox-img/pdf-header.png') }}" style="max-width: 100%;">

			<div class="site-content">
		   		<div class="page-statistics" id="to-export">
		   			<div class="stats" style="margin-top: -15px;">
						{!! $data !!}
					</div>
				</div>
			</div>
		</div>
		
		<footer>			
			<div class="footer-expander">
				<p>
					Source survey: "{!! $original_title !!}" <br/>
					Base: {{ $respondents }} respondents, {{ $period }}
				</p>
				<img src="{{ url('new-vox-img/pdf-footer.png') }}" style="max-width: 100%;">
			</div>
		</footer>
    </body>
</html>