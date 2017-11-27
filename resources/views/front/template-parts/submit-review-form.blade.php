<div class="panel-body review rating-panel">
	<p>
		{{ trans('front.page.dentist.review-form-hint') }}
	</p>
</div>
<input type="hidden" id="review-confirm-action" value="{{ $item->getLink() }}">
{!! Form::open(array('url' => $item->getLink(), 'id' => 'write-review-form', 'method' => 'post', 'class' => 'form-horizontal')) !!}
	@foreach($questions as $qid => $question)
		<div class="panel-body review rating-panel">
			<div class="form-group">
				<div class="col-md-12">
					<h3>
						<span>{{ $question->order }}.</span> {{ str_replace('{name}', $item->name, $question->question) }}
					</h3>
				</div>
			</div>
	    	@foreach(json_decode($question['options'], true) as $i => $option)
	        	<div class="rating-line clearfix">
	            	<div class="rating-left">
	            		{{ $option[0] }}
	            	</div>

					<div class="ratings">
						<div class="stars">
							<div class="bar" style="width: {{ $my_review ? getStarWidth(json_decode($my_review->answers[$qid]->options, true)[$i]) : 0 }}px;">
							</div>
							<input type="hidden" name="option[{{ $question['id'] }}][]" value="{{ $my_review ? json_decode($my_review->answers[$qid]->options, true)[$i] : '' }}" />
							<div class="rating" style="display: none;">
								{{ trans('front.page.dentist.review-form-answer-all') }}
							</div>
						</div>
					</div>

	            	<div class="rating-right">
	            		{{ $option[1] }}
	            	</div>

	        	</div>
	    	@endforeach
		</div>
	@endforeach
	<div class="panel-body review rating-panel">
		<div class="form-group">
			<div class="col-md-12">
				<h3>
					
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

                {{ Form::textarea( 'answer', $my_review ? $my_review->answer : '', array( 'id' => 'review-answer', 'class' => 'form-control', 'placeholder' => trans( 'front.page.dentist.review-form-last-question-placeholder' ) )) }}
			</div>
		</div>
		<div class="form-group">
			<div class="col-md-12">
	            <p>
	            	@if($my_dcn_address)
	            		{!! nl2br(trans('front.page.dentist.review-form-submit-hint-hasaddress', ['address' => $my_dcn_address])) !!}
	            	@else
	            		{!! nl2br(trans('front.page.dentist.review-form-submit-hint')) !!}
	            	@endif
	            </p>

	            @if(!$my_dcn_address)
	                {{ Form::text( 'reviewer-address', $user->register_reward ? $user->register_reward : '', array( 'id' => 'reviewer-address', 'class' => 'form-control', 'placeholder' => trans( 'front.page.dentist.review-form-address-placeholder' ) )) }}
	                <br/>
                @endif

	            {{ Form::submit( trans('front.page.dentist.review-form-submit'), array('class' => 'btn btn-primary btn-block', 'id' => 'review-submit-button' )) }}

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
	            	<a class="etherscan-link" target="_blank" href="{{ $item->getLink() }}" style="display: block; margin: 10px 0px;">
	            		{{ trans('front.page.dentist.review-form-etherscan') }}
	            	</a>
	            </div>
	            <div class="alert alert-info" id="review-confirmed" style="display: none;">
	            	{{ trans('front.page.dentist.review-form-done') }}
	            	<a href="{{ $item->getLink() }}" style="display: block; margin: 10px 0px;">
	            		{{ trans('front.page.dentist.review-form-my-review') }}
	            	</a>
	            	<a class="etherscan-link" target="_blank" href="{{ $item->getLink() }}" style="display: block; margin: 10px 0px;">
	            		{{ trans('front.page.dentist.review-form-etherscan') }}
	            	</a>
	            </div>
			</div>
		</div>
	</div>
{!! Form::close() !!}