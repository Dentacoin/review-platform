<div class="section-stats">
	<div class="container clearfix">
		<img src="{{ url('new-vox-img/dentavox-dental-statistics-icon.png') }}" alt="Dentavox dental statistics icon">
		<h3>
			{!! $vox->has_stats && !empty($related_vox) ? trans('vox.page.questionnaire.curious-related-survey', ['title' => $vox->title]) : trans('vox.page.questionnaire.curious-related-surveys') !!}
		</h3>
		<a href="{{ $vox->has_stats && !empty($related_vox) ? $vox->getStatsList() : getLangUrl('dental-survey-stats') }}" class="check-stats">
			{{ trans('vox.common.check-statictics') }}
		</a>
	</div>
</div>

@if($user->platform != 'external')
	<a href="https://account.dentacoin.com/invite?platform=dentavox" class="sticky-invite">
		<div class="sticky-box">
			<img src="{{ url('new-vox-img/invite-icon.png') }}">
			<p>
				{{ $user->is_dentist ? trans('vox.page.questionnaire.invite-patients') : trans('vox.page.questionnaire.invite-friends') }}
			</p>
		</div>
	</a>
@endif