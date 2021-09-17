@extends('vox')

@section('content')

	<div class="container daily-polls-wrapper">
		{!! csrf_field() !!}

		<a class="back-home" href="{{ getLangUrl('/') }}">
			{!! nl2br(trans('vox.daily-polls.popup.back')) !!}
		</a>
		<h1>{!! nl2br(trans('vox.daily-polls.title')) !!}</h1>

		{{-- <div class="monthly-description tac" style="margin-top: 0px; {!! !empty($monthly_descr) ? '' : 'display:none;' !!}">
			<div class="container">
				<p>
					@if(!empty($monthly_descr))
						{{ $monthly_descr->description }}
					@endif
				</p>
			</div>
		</div> --}}

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
	
	<script type="text/javascript">
		var poll_date_id = {{ isset($poll) && !empty($poll) ? $poll->id : 0 }};
		var poll_stats = {!! isset($poll_stats) ? $poll_stats : 0 !!};
		var poll_open = {!! isset($poll_open) ? $poll_open : 0 !!};
	</script>
    	
@endsection