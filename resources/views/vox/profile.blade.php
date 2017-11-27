@extends('vox')

@section('content')

	<div class="container">

		<a href="{{ getLangUrl('/') }}" class="questions-back">
			<i class="fa fa-arrow-left"></i> 
			{{ trans('vox.common.questionnaires') }}
		</a>

		<div class="col-md-3">
			@include('vox.template-parts.profile-menu')
		</div>
		<div class="col-md-9">

			@if($user->vox_rewards->isNotEmpty())
		        <div class="panel panel-default">
		            <div class="panel-heading">
		                <h3 class="panel-title bold">
	                		{{ trans('vox.page.profile.my-questionnaires') }}
		                </h3>
		            </div>
		            <div class="panel-body hosizontal-scroll">
						<div class="another-questions">
							<div class="questions-wrapper">
		            			<div class="carousel" data-flickity='{ "wrapAround": true, "cellAlign": "left", "adaptiveHeight": "true" }'>
									@foreach($user->vox_rewards as $completed)
										<div class="another-question carousel-cell {!! $loop->first ? 'active' : '' !!}">
											<div class="another-question-header clearfix">
												<div class="left">
													<span class="bold">{{ $completed->reward }} DCN</span>
												</div>
												<div class="right">
													<p>{{ $completed->vox->formatDuration() }}</p>
													<p>
														{{ trans('vox.common.questions-count', ['count' => $completed->vox->questions->count()]) }}
													</p>
												</div>
											</div>
											<h4 class="bold">{{ $completed->vox->title }}</h4>
											<div class="another-question-content">
												<a class="statistics" href="{{ getLangUrl('questionnaire/'.$completed->vox->id) }}">
													{{ trans('vox.common.check-statictics') }}
												</a>
											</div>
										</div>
									@endforeach
								</div>
							</div>
						</div>
						<a href="{{ getLangUrl('/') }}" class="show-questions">
							{{ trans('vox.page.profile.show-all') }}
						</a>
					</div>
			  	</div>
		  	@endif
		  	<div class="panel panel-default">
	            <div class="panel-heading">
	                <h3 class="panel-title bold">
	                	{{ trans('vox.page.profile.send-idea-title') }}
	                </h3>
	            </div>
	            <div class="panel-body">
					<form method="post" class="form-horizontal" id="idea-form">
						{!! csrf_field() !!}
					  	<div class="form-group">
					  		<label class="control-label col-md-12">
					  			{{ trans('vox.page.profile.send-idea-hint') }}
					  		</label>
						  	<div class="col-md-12">
						    	<input type="text" class="form-control" name="idea" id="idea" />
						    </div>
						</div>
						<div class="form-group">
							<div class="col-md-12">
		                        <button type="submit" name="submit" class="btn btn-primary form-control">
		                        	{{ trans('vox.page.profile.send-idea-submit') }}
		                        </button>
		                        <div class="alert alert-success" id="idea-success" style="display: none;">
		                        	{{ trans('vox.page.profile.send-idea-success') }}
		                        	
		                        </div>
		                        <div class="alert alert-warning" id="idea-error" style="display: none;">
		                        	{{ trans('vox.page.profile.send-idea-error') }}
		                        	
		                        </div>
							</div>
						</div>
		    			
		  			</form>
		  		</div>
		  	</div>
		</div>
	</div>

@endsection