@extends('vox')

@section('content')

	<div class="taken-survey-wrapper">
		<div class="container">
			@if(!empty($admin))
				<a href="{{ $vox->getLink() }}?testmode=1&goback=1&q-id={{ request('q-id') ?? '0' }}" class="go-back-admin">&laquo; Back</a>
			@endif
			<div class="flex">
				<div class="col">
					<img class="taken-survey-image" src="{{ url('new-vox-img/dentavox-man-taken-survey.jpg') }}" alt="Dentavox man taken survey" width="550" height="507">
				</div>
				<div class="col taken-survey-description">
					<h3>{!! trans('vox.page.taken-questionnaire.title') !!}</h3>
					<p>
						{!! trans('vox.page.taken-questionnaire.description', [
							'title' => '<span>'.$vox->title.'</span>'
						]) !!}
					</p>
				</div>
			</div>
		</div>

		@include('vox.template-parts.related-voxes', [
			'related_voxes' => $related_voxes,
			'suggested_voxes' => $suggested_voxes,
		])

		@include('vox.template-parts.stats-video', [
			'vox' => $vox,
			'related_voxes' => $related_voxes,
			'suggested_voxes' => $suggested_voxes
		])

		@include('vox.template-parts.suggested-voxes', [
			'related_voxes' => $related_voxes,
			'suggested_voxes' => $suggested_voxes,
		])

	</div>

@endsection