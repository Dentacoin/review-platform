@extends('vox')

@section('content')

	<div class="taken-survey-wrapper">
		<div class="container">
			<div class="flex">
				<div class="col">
					<img class="taken-survey-image" src="{{ url('new-vox-img/dentavox-man-taken-survey.jpg') }}" alt="Dentavox man taken survey" width="550" height="507">
				</div>
				<div class="col taken-survey-description">
					<h3>{!! trans('vox.page.restricted-questionnaire.title') !!}</h3>
					<p>
						{!! $res_desc !!} 
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
			'suggested_voxes' => true,
		])

		@include('vox.template-parts.suggested-voxes', [
			'related_voxes' => $related_voxes,
			'suggested_voxes' => $suggested_voxes,
		])

	</div>

@endsection