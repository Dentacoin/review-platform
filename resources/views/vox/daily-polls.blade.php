@extends('vox')

@section('content')

	<div class="container daily-polls-wrapper">
		{!! csrf_field() !!}

		<a class="back-home" href="{{ getLangUrl('/') }}">
			{!! nl2br(trans('vox.daily-polls.popup.back')) !!}
		</a>
		<h1>{!! nl2br(trans('vox.daily-polls.title')) !!}</h1>

		@php
			$calendar = new App\Helpers\Calendar();
		@endphp

		<div id="append-calendar" link="{{ getLangUrl('polls-calendar-html') }}">
			{!! $calendar->show() !!}
		</div>

		<div class="monthly-description tac" style="{!! !empty($monthly_descr) ? '' : 'display:none;' !!}">
			<div class="container">
				<h2>{!! nl2br(trans('vox.daily-polls.monthly-polls')) !!}</h2>
				<p>
					@if(!empty($monthly_descr))
						{{ $monthly_descr->description }}
					@endif
				</p>
			</div>
		</div>
	</div>
    	
@endsection