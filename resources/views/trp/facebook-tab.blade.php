<!DOCTYPE html>
<html>
    <head>
        <base href="{{ url('/') }}">
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="stylesheet" type="text/css" href="{{ url('css/fb-tab.css') }}" />
    </head>
    <body>

		<div id="trp-facebook-tab">
			<div class="reviews-header flex">

			</div>

			<div class="list-reviews-wrap">
				<div class="list-reviews">
			    </div>
			</div>

			<div class="reviews-footer">
				<img src="{{ url('img-trp/logo.png') }}">
			</div>
		</div>
		{!! csrf_field() !!}

		<script src="{{ url('/js/jquery-3.4.1.min.js') }}"></script>
		<script type="text/javascript">
		    var signedRequest = "{{ request('signed_request') }}";
		</script>
		<script src="{{ url('/js/fb-tab.js') }}"></script>
	</body>
</html>