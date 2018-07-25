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

			@if($histories->isNotEmpty())
		        <div class="panel panel-default">
		            <div class="panel-heading">
		                <h3 class="panel-title bold">
	                		{{ trans('vox.page.profile.my-questionnaires') }}
		                </h3>
		            </div>
		            <div class="panel-body">
		            	<table class="table">
		            		<thead>
		            			<tr>
			            			<th>
			            				{{ trans('vox.page.profile.'.$current_subpage.'.list-date') }}
			            			</th>
			            			<th>
			            				{{ trans('vox.page.profile.'.$current_subpage.'.list-time') }}
			            			</th>
			            			<th>
			            				{{ trans('vox.page.profile.'.$current_subpage.'.list-questionnaire') }}
			            			</th>
			            			<th>
			            				{{ trans('vox.page.profile.'.$current_subpage.'.list-time-spent') }}
			            			</th>
			            			<th>
			            				{{ trans('vox.page.profile.'.$current_subpage.'.list-reward') }}
			            			</th>
		            			</tr>
		            		</thead>
		            		<tbody>
								@foreach($histories as $completed)
		            				<tr>
		            					<td>
		            						{{ $completed->created_at->toDateString() }}
		            					</td>
		            					<td>
		            						{{ $completed->created_at->toTimeString() }}
		            					</td>
		            					<td>
											<a href="{{ $completed->vox->getLink() }}" target="_blank">
												{{ $completed->vox->title }}
											</a>
		            					</td>
		            					<td>
		            						{{ gmdate("H:i:s", intVal($completed->seconds)) }}
		            					</td>
		            					<td>
		            						{{ $completed->reward ? $completed->reward : '-' }}
		            					</td>
		            				</tr>
		            			@endforeach
		            		</tbody>
		            	</table>
						
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
							<div class="col-md-12 tac">
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