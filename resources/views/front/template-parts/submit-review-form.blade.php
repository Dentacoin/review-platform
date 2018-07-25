@if($item->id == $user->id)
	<p>
		{{ trans('front.page.dentist.review-form-hint-self') }}
	</p>
@else

	<div class="panel-body review rating-panel">
		<p>
			{{ trans('front.page.dentist.review-form-hint') }}
		</p>
	</div>
	<input type="hidden" id="review-confirm-action" value="{{ $item->getLink() }}">
	{!! Form::open(array('url' => $item->getLink(), 'id' => 'write-review-form', 'method' => 'post', 'class' => 'form-horizontal')) !!}

		@if($item->is_dentist && !$item->is_clinic && $item->my_workplace_approved->isNotEmpty())	

			<div class="form-group clearfix">
				<div class="form-group">
					<div class="col-md-12">
						<h3>
							{{ trans('front.page.dentist.dentist-visit', ['name' => $item->getName() ]) }}
						</h3>
					</div>
				</div>
		        <div class="col-md-12">		        
		            <select name="dentist_clinics" class="form-control">
						<option value="">{{ trans('front.page.dentist.dentist-cabinet') }}</option>
						@foreach($item->my_workplace_approved as $workplace)
							<option value="{{ $workplace->clinic->id }}">{{ $workplace->clinic->getName() }}</option>
						@endforeach
					</select>
		        </div>
		    </div>

		@endif

		@foreach($questions as $qid => $question)
			@if($item->is_clinic && $item->teamApproved->isNotEmpty() && $loop->iteration == 4 )

				<div class="form-group clearfix">
			        <div class="form-group">
						<div class="col-md-12">
							<h3>
								{{ trans('front.page.dentist.dentist-treat') }}
							</h3>
						</div>
					</div>
			        <div class="col-md-12">
			            <select name="clinic_dentists" class="form-control" id="clinic_dentists">
							<option value="">{{ trans('front.page.dentist.dentist-not-remembered') }}</option>
							@foreach($item->teamApproved as $team)
								<option value="{{ $team->clinicTeam->id }}">{{ $team->clinicTeam->getName() }}</option>
							@endforeach
						</select>
			        </div>
			    </div>
			@endif

			<div class="panel-body review rating-panel {{ $item->is_clinic && $item->team->isNotEmpty() && $item->team->count() > 1 && $loop->iteration == 4 ? 'hidden-review-question' : '' }}" {{ $item->is_clinic && $item->team->isNotEmpty() && $item->team->count() > 1 && $loop->iteration == 4 ? 'style=display:none;' : '' }}>
				<div class="form-group">
					<div class="col-md-12">
						<h3>
							{{ str_replace('{name}', $item->name, $question->question) }}
						</h3>
					</div>
				</div>
		    	@foreach(json_decode($question['options'], true) as $i => $option)
		        	<div class="rating-line clearfix">
		            	<div class="rating-left">
		            		{{ $option[0] }}
		            	</div>

		            	<div class="rating-right">
		            		{{ $option[1] }}
		            	</div>

						<div class="ratings">
							<div class="stars">
								<div class="bar" style="width: {{ $my_review ? getStarWidth(json_decode($my_review->answers[$qid]->options, true)[$i]) : 0 }}px;">
								</div>
								<input type="hidden" name="option[{{ $question['id'] }}][]" value="{{ $my_review ? json_decode($my_review->answers[$qid]->options, true)[$i] : '' }}" />
							</div>
						</div>
		        	</div>
		    	@endforeach
				<div class="rating" style="display: none;">
					{{ trans('front.page.dentist.review-form-answer-all') }}
				</div>
			</div>
		@endforeach
		<div class="panel-body review rating-panel">
			<div class="form-group">
				<div class="col-md-12">
					<h3 class="last-question">
						
						<span>
							{{ trans('front.page.dentist.review-form-last') }}:
						</span> 
						{{ trans('front.page.dentist.review-form-last-question', ['name' => $item->getName()]) }}
					</h3>
				</div>
			</div>
			<div class="form-group">
				<div class="col-md-12">

	                <div class="alert alert-warning" id="review-answer-error" style="display: none;">
	                	{{ trans( 'front.page.dentist.review-form-last-question-invalid' ) }}
	                </div>

	                <div class="alert alert-warning" id="review-error" style="display: none;">
	                	{{ trans('front.page.dentist.review-form-answer-all') }}
	                </div>
	                <div class="alert alert-warning" id="review-short-text" style="display: none;">
	                	{{ trans('front.page.dentist.review-form-text-short') }}
	                </div>


					<ul class="nav nav-tabs nav-justified" id="review-type-nav">
						<li class="active">
							<a href="javascript:;" data-type="text">
								{{ trans('front.page.dentist.review-form-type-text') }}
							</a>
						</li>
						<li>
							<a href="javascript:;" data-type="video">
								{{ trans('front.page.dentist.review-form-type-video') }}
							</a>
						</li>
					</ul>

					<div id="review-option-text" class="review-type-content">
						{{ Form::textarea( 'answer', $my_review ? $my_review->answer : '', array( 'id' => 'review-answer', 'class' => 'form-control', 'placeholder' => trans( 'front.page.dentist.review-form-last-question-placeholder' ) )) }}	
					</div>
					<div id="review-option-video" class="review-type-content" style="display: none;">
						@if($my_review && $my_review->youtube_id)
							<div class="alert alert-info">
								{{ trans('front.page.dentist.review-form-video-already-shot') }}
							</div>
							<div class="videoWrapper">
								<iframe width="560" height="315" src="https://www.youtube.com/embed/{{ $my_review->youtube_id }}" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
							</div>
						@else
							<p>
								{!! nl2br(trans('front.page.dentist.review-form-video-hint')) !!}
							</p>
							<label for="video-agree">
								<input type="checkbox" id="video-agree" />
								{!! nl2br(trans('front.page.dentist.review-form-video-agree')) !!}
							</label>
							<div class="alert alert-warning" style="display: none;" id="video-not-agree">
								{{ trans('front.page.dentist.review-form-video-not-agree') }}
							</div>
							<video id="myVideo" class="video-js vjs-default-skin"></video>

							<div class="custom-controls">
								<div class="alert alert-warning" style="display: none;" id="video-error">
									{{ trans('front.page.dentist.review-form-video-error') }}
								</div>
								<div class="alert alert-warning" style="display: none;" id="video-denied">
									{{ trans('front.page.dentist.review-form-video-denied') }}
								</div>
								<div class="alert alert-warning" style="display: none;" id="video-short">
									{{ trans('front.page.dentist.review-form-video-short') }}
								</div>
								<a href="javascript:;" id="init-video" class="btn btn-primary">
									<i class="fa fa-video-camera"></i>
									{{ trans('front.page.dentist.review-form-video-allow') }}
								</a>
								<a href="javascript:;" id="start-video" class="btn btn-primary" style="display: none;">
									<i class="fa fa-film"></i>
									{{ trans('front.page.dentist.review-form-video-start') }}
								</a>
								<a href="javascript:;" id="stop-video" class="btn btn-primary" style="display: none;">
									<i class="fa fa-stop-circle"></i>
									{{ trans('front.page.dentist.review-form-video-stop') }}
								</a>
								<div id="video-progress" style="display: none;">
									{!! trans('front.page.dentist.review-form-video-processing',[
										'percent' => '<span id="video-progress-percent"></span>'
									]) !!}
								</div>
								<span id="video-youtube" style="display: none;">
									{{ trans('front.page.dentist.review-form-video-youtube') }}
								</span>
								<div class="alert alert-success" style="display: none;" id="video-uploaded">
									{{ trans('front.page.dentist.review-form-video-uploaded') }}
								</div>
							</div>
						@endif
						<input type="hidden" id="youtube_id" name="youtube_id" value="{{ $my_review ? $my_review->youtube_id : '' }}" />
					</div>

				</div>
			</div>
			<div class="form-group">
				<div class="col-md-12">

	                <button type="submit" class="btn btn-primary col-md-4 col-md-offset-4" id="review-submit-button" data-loading="{{ trans('front.common.loading') }}">
	                	{{ trans('front.page.dentist.review-form-submit') }}
	                </button>

	                <div class="alert alert-warning" id="review-crypto-error" style="display: none;">
	                	{{ trans('front.page.dentist.review-form-crypto-error') }}
		            	<span class="error-info" style="display: block; margin: 10px 0px;">
		            	</span>
	                </div>
		            <div class="alert alert-info" id="review-pending" style="display: none;">
		            	{{ trans('front.page.dentist.review-form-pending') }}
		            	<a href="{{ $item->getLink() }}" style="display: block; margin: 10px 0px;">
		            		{{ trans('front.page.dentist.review-form-my-review') }}
		            	</a>
		            </div>
		            <div class="alert alert-info" id="review-confirmed" style="display: none;">
		            	{{ trans('front.page.dentist.review-form-done') }}
		            	<a href="{{ $item->getLink() }}" style="display: block; margin: 10px 0px;">
		            		{{ trans('front.page.dentist.review-form-my-review') }}
		            	</a>
		            	<a class="etherscan-link" target="_blank" href="" style="display: block; margin: 10px 0px;">
		            		{{ trans('front.page.dentist.review-form-etherscan') }}
		            	</a>
		            </div>
				</div>
			</div>
		</div>
	{!! Form::close() !!}


@endif