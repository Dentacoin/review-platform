<div class="details-wrapper" review-id="{{ $review->id }}">
	@if(!empty($user) && $user->id==$item->id && !$review->verified && !empty($user->trusted))
		<a class="green-button verify-review" href="javascript:;">
			Verify patient
		</a>
	@endif
	<div class="review-avatar" style="background-image: url('{{ $review->user->getImageUrl(true) }}');"></div>
	<h2 class="mont">
		{{ !empty($review->user->self_deleted) ? ($review->verified ? trans('trp.common.verified-patient') : trans('trp.common.deleted-user')) : $review->user->name }}'s Review of 
		{{ $item->getNames() }}
		{{-- {!! nl2br(trans('trp.popup.view-review-popup.title', [ 'name' => $item->getNames() ])) !!} --}}
	</h2>

	<div class="tac">
		<div class="review-rating-new {{ $review->verified ? 'verified-review' : '' }}">
			<span class="rating mont">
				{{ number_format(!empty($review->team_doctor_rating) && ($item->id == $review->dentist_id) ? $review->team_doctor_rating : $review->rating, 1) }}
			</span>
			<div class="ratings big">
				<div class="stars">
					<div class="bar" style="width: {{ !empty($review->team_doctor_rating) && ($item->id == $review->dentist_id) ? $review->team_doctor_rating/5*100 : $review->rating/5*100 }}%;">
					</div>
				</div>
			</div>

			<div class="trusted tooltip-text" text="{!! nl2br(trans('trp.common.trusted-tooltip', ['name' => $item->getNames() ])) !!}" {!! $review->verified ? '' : 'style="display:none;"' !!}>
				<img src="{{ url('img-trp/mobile-logo-white.png') }}"/>
			</div>
		</div>
	</div>

	<div class="review-header">
		<div class="flex {{ $review->title ? '' : 'no-title' }}">
			@if($review->title)
				<h3 class="review-title mont">
					“{{ $review->title }}”
				</h3>
			@endif
			
			<a href="javascript:;" class="share-button" data-popup="popup-share">
				<img src="{{ url('img-trp/share-arrow.svg') }}"/>
				Share
			</a>
		</div>
		<p class="review-date">
			{{ $review->created_at ? $review->created_at->toFormattedDateString() : '-' }}
		</p>
	</div>
	
	<div class="review-content">
		@if(!empty($review->youtube_id))
			<div class="video-wrap">
				<div class="video-wrapper">
					<iframe src="https://www.youtube.com/embed/{{ $review->youtube_id }}" frameborder="0"></iframe>
				</div>
			</div>
		@else
			{!! nl2br($review->answer) !!}
		@endif

		@if($review->reply)
			<div class="review-replied-wrapper">
				<img class="review-avatar" src="{{ $item->getImageUrl(true) }}" width="33" height="33"/>
				<div>
					<p class="replied-info">
						<img src="{{ url('img-trp/reply-icon.svg') }}" width="15" height="13"/>Replied by {{ $item->getNames() }} {{ $review->replied_at ? 'on '.$review->replied_at->toFormattedDateString() : '' }}
					</p>
					<p class="review-content">{!! nl2br($review->reply) !!}</p>
				</div>
			</div>
		@endif
	</div>
	
	@if(!empty($review->treatments) || !empty($review->reviewForDentistAndClinic()))
		<div class="review-treatments flex">
			@if(!empty($review->treatments))
				<div class="treatments col {{ !empty($review->reviewForDentistAndClinic()) ? 'with-border' : '' }}">
					<h4>Dental services received:</h4>
					@foreach($review->treatments as $t)
						<span class="treatment">{!! App\Models\Review::handleTreatmentTooltips(trans('trp.treatments.'.$t)) !!}</span>
					@endforeach
				</div>
			@endif
			@if(!empty($review->reviewForDentistAndClinic()))
				<div class="review-dentist col">
					<h4>{{ $review->dentist_id == $item->id ? 'Clinic' : 'Treating dentist' }}:</h4>
					<a href="{{ $review->dentist_id == $item->id ? $review->clinic->getLink() : $review->dentist->getLink() }}" class="flex flex-mobile">
						<div class="review-dentist-avatar" style="background-image: url('{{ $review->dentist->getImageUrl(true) }}');"></div>
						<p>{{ $review->dentist_id == $item->id ? $review->clinic->getNames() : $review->dentist->getNames() }}</p>
					</a>
				</div>
			@endif
		</div>
	@endif

	<div class="review-answers">
		<h4>Rating breakdown:</h4>

		<div class="overview-wrapper">
			@php
				$reviewQuestions = App\Models\Question::with('translations')->orderBy('order', 'asc')->get();
				$ratingForDentistQuestions = App\Models\Review::$ratingForDentistQuestions;
				$oldRatingForDentistQuestions = App\Models\Review::$oldRatingForDentistQuestions;
				$oldReview = $review->answers->first()->rating ? false : true; //old reviews had options, new have rating
			@endphp

			{{-- if review is for dentist that works in clinic show only 3 answers --}}
			@if(!empty($review->team_doctor_rating) && ($item->id == $review->dentist_id))

				@if($oldReview)

					@foreach($reviewQuestions->whereIn('id', array_merge($ratingForDentistQuestions, $oldRatingForDentistQuestions)) as $question)
						@php
							$answer = $review->answers->where('question_id', $question->id)->first();
						@endphp
						<div class="overview-column">
							@if(!$answer)
								<div class="new-question">
									new
								</div>
							@endif
							<p>
								{{ $question['label'] }}
							</p>
							<div class="ratings big">
								<div class="stars">
									@if($answer)
										<div class="bar" style="width: {{ array_sum(json_decode($answer->options, true)) / count(json_decode($answer->options, true)) / 5 * 100 }}%;"></div>
									@else
										<div class="bar new" style="width: 0%;"></div>
									@endif
								</div>
							</div>
						</div>
					@endforeach

				@else

					@foreach($reviewQuestions->whereIn('id', $ratingForDentistQuestions) as $question)
						<div class="overview-column">
							<p>
								{{ $question['label'] }}
							</p>
							<div class="ratings big">
								<div class="stars">
									<div class="bar" style="width: {{ $review->answers->where('question_id', $question->id)->first()->rating / 5 * 100 }}%;"></div>
								</div>
							</div>
						</div>
					@endforeach

				@endif

			@else
			
				@if($oldReview)
					{{-- @foreach($review->answers as $answer) --}}
					@foreach($reviewQuestions as $question)
						@php
							$answer = $review->answers->where('question_id', $question->id)->first();
						@endphp

						<div class="overview-column">
							@if(!$answer)
								<div class="new-question">
									new
								</div>
							@endif
							<p>
								{{ $question['label'] }}
							</p>
							<div class="ratings big">
								<div class="stars">
									@if($answer)
										<div class="bar" style="width: {{ array_sum(json_decode($answer->options, true)) / count(json_decode($answer->options, true)) / 5 * 100 }}%;"></div>
									@else
										<div class="bar new" style="width: 0%;"></div>
									@endif
								</div>
							</div>
						</div>
					
					@endforeach
					
				@else
					
					@foreach($reviewQuestions->where('type', '!=', 'deprecated') as $question)
						<div class="overview-column">
							<p>
								{{ $question['label'] }}
							</p>
							<div class="ratings big">
								<div class="stars">
									<div class="bar" style="width: {{ $review->answers->where('question_id', $question->id)->first()->rating / 5 * 100 }}%;"></div>
								</div>
							</div>
						</div>
					@endforeach

				@endif

			@endif
			
		</div>
	</div>

	<div class="tac">
		<a href="javascript:;" class="close-popup blue-button">
			Close review
		</a>
	</div>
</div>