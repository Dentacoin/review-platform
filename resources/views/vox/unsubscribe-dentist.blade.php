@extends('vox')

@section('content')

	<div class="blue-background"></div>
	<div class="unsubscribe-wrapper container">
		<div class="alert alert-info">{!! !empty($incomplete_alert) ? 'You unsubscribed successfully from marketing emails!' : nl2br(trans('trp.page.unsubscribe.alert-success')) !!}</div>
	</div>

	<style type="text/css">
		.unsubscribe-wrapper {
			padding-top: 100px;
		    padding-bottom: 100px;
		}
	</style>

@endsection