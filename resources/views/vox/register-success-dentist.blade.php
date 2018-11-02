@extends('vox')

@section('content')

	<div class="section-register-success success-dentist">

		<div class="container">
			<div class="col-md-12 tac">
				<img src="{{ url('new-vox-img/welcome-dentist.png') }}">
				<div class="right-content">

					@if( $request_sent )
            			<div>
            				<br/>
            				<div class="alert alert-success">
            					{!! trans('vox.page.welcome-to-dentavox.approval-request-sent') !!}
            				</div>
            			</div>
					@endif
					<h2>
						{!! nl2br( trans('vox.page.welcome-to-dentavox.approval-request-title') ) !!}
					</h2>

					<h4>
						{!! nl2br( trans('vox.page.welcome-to-dentavox.approval-request-subtitle') ) !!}
					</h4>

					@if( !$request_sent )
						<form method="POST" class="tac clearfix">
							{!! csrf_field() !!}
							<button type="submit" class="blue-button" href="javascript:;">
								{!! nl2br( trans('vox.page.welcome-to-dentavox.approval-request-button') ) !!}
							</button>
						</form>
        			@endif
				</div>
			</div>
		</div>
	</div>

@endsection