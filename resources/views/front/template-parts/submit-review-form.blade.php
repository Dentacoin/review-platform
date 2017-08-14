<div class="panel-body review rating-panel">
	<p>
		{{ trans('front.page.dentist.review-form-hint') }}
	</p>
</div>
{!! Form::open(array('url' => $item->getLink(), 'id' => 'write-review-form', 'method' => 'post', 'class' => 'form-horizontal')) !!}
	@foreach($questions as $question)
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
							<div class="bar" style="width: 0px;">
							</div>
							<input type="hidden" name="option[{{ $question['id'] }}][]" value="" />
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

                {{ Form::textarea( 'answer', '', array( 'id' => 'review-answer', 'class' => 'form-control', 'placeholder' => trans( 'front.page.dentist.review-form-last-question-placeholder' ) )) }}
			</div>
		</div>
		<div class="form-group">
			<div class="col-md-12">
				<h3>
	                {{ Form::submit( trans('front.page.dentist.review-form-submit'), array('class' => 'btn btn-primary btn-block' )) }}
				</h3>
			</div>
		</div>
	</div>
{!! Form::close() !!}