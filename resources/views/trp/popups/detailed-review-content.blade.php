<div class="details-wrapper">
	<div class="review review-wrapper" review-id="{{ $review->id }}">
		<div class="review-header">
			<div class="review-avatar" style="background-image: url('{{ $review->user->getImageUrl(true) }}');"></div>
			<span class="review-name">{{ !empty($review->user->self_deleted) ? ($review->verified ? trans('trp.common.verified-patient') : trans('trp.common.deleted-user')) : $review->user->name }}: </span>
			@if($review->verified)
				<div class="trusted-sticker mobile-sticker tooltip-text" text="{!! nl2br(trans('trp.common.trusted-tooltip', ['name' => $item->getName() ])) !!}">
					{!! nl2br(trans('trp.common.trusted-review')) !!}
				    <i class="fas fa-info-circle"></i>
				</div>
			@endif
			@if($review->title)
				<span class="review-title">
			    	“{{ $review->title }}”
				</span>
			@endif
			@if($review->verified)
				<div class="trusted-sticker tooltip-text" text="{!! nl2br(trans('trp.common.trusted-tooltip', ['name' => $item->getName() ])) !!}">
					{!! nl2br(trans('trp.common.trusted-review')) !!}
				    <i class="fas fa-info-circle"></i>
				</div>
			@endif
		</div>
		<div class="review-rating">
			<div class="ratings average">
				<div class="stars">
					<div class="bar" style="width: {{ !empty($review->team_doctor_rating) && ($item->id == $review->dentist_id) ? $review->team_doctor_rating/5*100 : $review->rating/5*100 }}%;">
					</div>
				</div>
				<span class="rating">
					({{ !empty($review->team_doctor_rating) && ($item->id == $review->dentist_id) ? $review->team_doctor_rating : $review->rating }})
				</span>
			</div>
			<span class="review-date">
				{{ $review->created_at ? $review->created_at->toFormattedDateString() : '-' }}
			</span>
			@if(!empty($review->treatments))
				@foreach($review->treatments as $t)
					<span class="treatment">• {!! App\Models\Review::handleTreatmentTooltips(trans('trp.treatments.'.$t)) !!}</span>
				@endforeach
			@endif
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
		</div>

		<div class="review-footer flex flex-mobile break-mobile">
			<div class="col">
				@if(!$review->reply && !empty($user) && ($review->dentist_id==$user->id || $review->clinic_id==$user->id) )
					<a class="reply-review" href="javascript:;">
						<span>
							{!! nl2br(trans('trp.popup.view-review-popup.reply')) !!}
						</span>
					</a>
				@endif

				<a class="thumbs-up {!! ($my_upvotes && in_array($review->id, $my_upvotes) ) ? 'voted' : '' !!}" href="javascript:;">
					<img src="{{ url('img-trp/thumbs-up'.(($my_upvotes && in_array($review->id, $my_upvotes)) ? '-color' : '').'.png') }}">
					<span>
						{{ intval($review->upvotes) }}
					</span>
				</a>
				<a class="thumbs-down {!! ($my_downvotes && in_array($review->id, $my_downvotes)) ? 'voted' : '' !!}" href="javascript:;">
					<img src="{{ url('img-trp/thumbs-down'.(($my_downvotes && in_array($review->id, $my_downvotes)) ? '-color' : '').'.png') }}">
					<span>
						{{ intval($review->downvotes) }}
					</span>
				</a>

				<a class="share-review" href="javascript:;" data-popup="popup-share" share-href="{{ $item->getLink() }}?review_id={{ $review->id }}" >
					<img src="{{ url('img-trp/share-review.png') }}">
					<span>
						{!! trans('trp.common.share') !!}
					</span>
				</a>
			</div>
		</div>

		@if(!$review->reply && !empty($user) && ($review->dentist_id==$user->id || $review->clinic_id==$user->id) )
			<div class="review-replied-wrapper reply-form" style="display: none;">
				<div class="review">
    				<div class="review-header">
		    			<div class="review-avatar" style="background-image: url('{{ $item->getImageUrl(true) }}');"></div>
		    			<span class="review-name">{{ $item->getName() }}</span>
	    			</div>
					<div class="review-content">
						<form method="post" action="{{ $item->getLink() }}reply/{{ $review->id }}" class="reply-form-element">
							{!! csrf_field() !!}
							<textarea class="input" name="reply" placeholder="{!! nl2br(trans('trp.popup.view-review-popup.enter-reply')) !!}"></textarea>
							<button class="button" type="submit" name="">{!! nl2br(trans('trp.popup.view-review-popup.submit')) !!}</button>
							<div class="alert alert-warning" style="display: none;">
								{!! nl2br(trans('trp.popup.view-review-popup.reply-error')) !!}
							</div>
						</form>
					</div>
				</div>
			</div>
		@elseif($review->reply)
			<div class="review-replied-wrapper">
				<div class="review">
					<div class="review-header">
		    			<div class="review-avatar" style="background-image: url('{{ $item->getImageUrl(true) }}');"></div>
		    			<span class="review-name">{{ $item->getName() }}</span>
		    			<span class="review-date">
							{{ $review->replied_at ? $review->replied_at->toFormattedDateString() : '-' }}
						</span>
	    			</div>
					<div class="review-content">
						{!! nl2br($review->reply) !!}
					</div>

					<div class="review-footer">
						<div class="col">
							<a class="thumbs-up {!! ($my_upvotes && in_array($review->id, $my_upvotes) ) ? 'voted' : '' !!}" href="javascript:;">
								<img src="{{ url('img-trp/thumbs-up'.(($my_upvotes && in_array($review->id, $my_upvotes)) ? '-color' : '').'.png') }}">
								<span>
									{{ intval($review->upvotes_reply) }}
								</span>
							</a>
							<a class="thumbs-down {!! ($my_downvotes && in_array($review->id, $my_downvotes) ) ? 'voted' : '' !!}" href="javascript:;">
								<img src="{{ url('img-trp/thumbs-down'.(($my_downvotes && in_array($review->id, $my_downvotes)) ? '-color' : '').'.png') }}">
								<span>
									{{ intval($review->downvotes_reply) }}
								</span>
							</a>
						</div>
					</div>
				</div>
			</div>
		@endif
	</div>
</div>

<div class="overview-wrapper">
	<div class="mobile-tac">
		<h4 class="black-left-line">
			{!! nl2br(trans('trp.popup.view-review-popup.overview')) !!}
		</h4>
	</div>

	<div class="review-container clearfix">
		@foreach($review->answers as $answer)
			@if((!empty($review->team_doctor_rating) && ($item->id == $review->dentist_id) && ($answer->question_id == 4 || $answer->question_id == 6 || $answer->question_id == 7 )) ||
				(!empty($review->team_doctor_rating) && ($item->id == $review->clinic_id)) ||
				empty($review->team_doctor_rating)
			)

				<div class="overview-column">
					<p>{{ $answer->question['label'] }}</p>
					<div class="ratings">
						<div class="stars">
							<div class="bar" style="width: {{ array_sum(json_decode($answer->options, true)) / count(json_decode($answer->options, true)) / 5 * 100 }}%;">
							</div>
						</div>
						<span class="rating">
							({{ array_sum(json_decode($answer->options, true)) / count(json_decode($answer->options, true)) }}) 
						</span>
					</div>
				</div>
			@endif
		@endforeach
	</div>
</div>
<div class="detailed-review-wrapper">
	<div class="mobile-tac">
		<h4 class="black-left-line">
			{!! nl2br(trans('trp.popup.view-review-popup.detailed-review')) !!}
		</h4>
	</div>

	<div class="review-container">

		@foreach($review->answers as $answer)
			@if((!empty($review->team_doctor_rating) && ($item->id == $review->dentist_id) && ($answer->question_id == 4 || $answer->question_id == 6 || $answer->question_id == 7 )) ||
				(!empty($review->team_doctor_rating) && ($item->id == $review->clinic_id)) ||
				empty($review->team_doctor_rating)
			)

				<div class="detailed-container">
					<div class="detailed-title tac">
						<span>{{ $answer->question['label'] }}</span>
						<div class="ratings tac">
							<div class="stars">
								<div class="bar" style="width: {{ array_sum(json_decode($answer->options, true)) / count(json_decode($answer->options, true)) / 5 * 100 }}%;">
								</div>
							</div>
							<span class="rating">
								({{ array_sum(json_decode($answer->options, true)) / count(json_decode($answer->options, true)) }}) 
							</span>
						</div>
					</div>
					@if($answer->question_id == 4 && !empty($review->team_doctor_rating) && ($item->id == $review->clinic_id) && !empty($review->dentist_id) && !empty(App\Models\User::find($review->dentist_id)))
						<div class="treating-dentist">{!! nl2br(trans('trp.popup.view-review-popup.treating-dentist')) !!}: <a href="{{ !empty(App\Models\User::find($review->dentist_id)->email) ? App\Models\User::find($review->dentist_id)->getLink() : 'javascript:;' }}">{{ App\Models\User::find($review->dentist_id)->getName() }}</a></div>
					@endif

	            	@foreach(json_decode($answer->question['options'], true) as $i => $option)
						<div class="clearfix flex-mobile">
							<div class="detailed-review-column">
		                		{{ $option[0] }}
							</div>
							<div class="detailed-review-column">
								<div class="ratings tac">
									<div class="stars">
										<div class="bar" style="width: {{ json_decode($answer->options, true)[$i] / 5 * 100 }}%;">
										</div>
									</div>
								</div>
							</div>
							<div class="detailed-review-column tar">
		                		{{ $option[1] }}
							</div>
						</div>
	            	@endforeach
				</div>
			@endif
		@endforeach
	</div>
</div>