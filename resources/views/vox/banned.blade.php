@extends('vox')

@section('content')
	
		<div class="main-title">
			<h1 class="bold title">
				{{ trans('vox.page.banned.title') }}
			</h1>
		</div>
		<div class="container">
			<br/><br/>
			<div class="alert alert-info">
				@if($ban_expires)
					{!! nl2br(trans('vox.page.banned.hint-temporary', ['expires' => $ban_expires])) !!}
				@else
					{!! nl2br(trans('vox.page.banned.hint')) !!}
				@endif
			</div>
		</div>    	
    	
@endsection