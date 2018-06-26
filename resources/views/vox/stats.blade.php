@extends('vox')

@section('content')


		<div class="container page-statistics">

			<div class="row">
				<div class="col-md-7 should-be-sticky stats-col">
					<p class="statistics-title bold">
						{{ trans('vox.page.'.$current_page.'.statistics', [
							'name' => $vox->title
						]) }}
					</p>
<!--
					<div class="questions-dots">
						<div class="dot" id="current-question-bar" data-count="{{ $vox->questionsReal->count() }}" style="width: {{ 100/$vox->questionsReal->count() }}%;"></div>
					</div>
-->
					<div class="all-titles">
							<div class="statistics-question">{{ $question->question }}</div>
					</div>

<!--
					<div class="response response-mobile tac clearfix" style="display: block;">
						<a href="{{ getLangUrl('stats/'.$vox->id.'/'.$prev) }}" class="previous previous-question">
							<< {{ trans('vox.page.'.$current_page.'.previous') }}
						</a>
						<a href="{{ getLangUrl('stats/'.$vox->id.'/'.$next) }}" class="next next-question">
							{{ trans('vox.page.'.$current_page.'.next') }} >>
						</a>
					</div>
-->

					<div class="row">
						<form action="{{ getLangUrl('stats/'.$vox->id) }}" method="get" class="form-inline tac" id="stats-form">
							<div class="col-md-7">
								<p class="statistics-subquestion">
									{{ trans('vox.page.'.$current_page.'.for-period') }}
								</p>
							  	<div class="form-group">
								    <input class="form-control datepicker" id="date-from" name="start" type="text" value="{{ $start }}" data-date="{{ $start }}" data-date-format="dd-mm-yyyy">
								</div>
								<i class="fa fa-minus"></i>
							  	<div class="form-group">
								    <input class="form-control datepicker" id="date-to" name="end" type="text" value="{{ $end }}" data-date="{{ $end }}" data-date-format="dd-mm-yyyy">
								</div>
								<div class="form-group">
				                    <button type="submit" class="btn btn-primary form-control">
				                    	{{ trans('vox.page.'.$current_page.'.get-it') }}
				                    </button>
								</div>
							</div>
							<div class="col-md-5">
								<p class="statistics-subquestion">
									{{ trans('vox.page.'.$current_page.'.country') }}
								</p>
							  	<div class="form-group">
									{{ Form::select( 'country_id' , ['' => trans('vox.common.all')] + \App\Models\Country::get()->pluck('name', 'id')->toArray() , $country , array('class' => 'form-control', 'id' => 'country') ) }}
								</div>
								<div class="form-group">
				                    <button type="submit" class="btn btn-primary form-control">
				                    	{{ trans('vox.page.'.$current_page.'.get-it') }}
				                    </button>
								</div>
							</div>
		  				</form>
					</div>
					<div class="row">
						<div class="col-md-12">
							<div id="statistic" style="width: 100%;"></div>
						</div>
					</div>
					<div class="all-questions">
						<div class="statistics-block" data-id="{{ $question->id }}">
							<div class="row response response-stats">
								@if(!empty($answer_aggregates))
									<div class="clearfix">
										<div class="col-md-7 col-xs-6  col-sm-6">
											<p class="country blue">{{ $country ? $countryobj->name : trans('vox.common.all') }}</p>
										</div>
										<div class="col-md-5 col-xs-6  col-sm-6">
											<p class="responded blue">
												{{ trans('vox.page.'.$current_page.'.responded', [
													'count' => array_sum($answer_aggregates)
												]) }}
											</p>
										</div>
									</div>
									@foreach( json_decode($question->answers, true) as $i => $ans )
										<div class="clearfix">
											<div class="col-md-7 col-xs-6  col-sm-6">
												<p style="color: {{ $colors[$loop->index] }}; {{ $my_answer && $my_answer==($i+1) ? 'font-weight: bold;' : '' }}">{{ $ans.( $my_answer && $my_answer==($i+1) ? ' *' : '') }}</p>
											</div>
											<div class="col-md-5 col-xs-6  col-sm-6">
												<p style="color: {{ $colors[$loop->index] }}; {{ $my_answer && $my_answer==($i+1) ? 'font-weight: bold;' : '' }}">{{ !empty($answer_aggregates[$loop->index+1]) ? $answer_aggregates[$loop->index+1] : '0' }} votes</p>
											</div>
										</div>
									@endforeach
									@if($my_answer)
									<div class="clearfix">
										<div class="col-md-12">
											<i>
												{{ trans('vox.page.'.$current_page.'.your-answer') }}
											</i>
										</div>
									</div>
									@endif
								@else
									<div class="alert alert-info">
										{{ trans('vox.page.'.$current_page.'.no-results', [
											'start' => $start,
											'end' => $end,
											'country' => $country ? $countryobj->name : trans('vox.common.all'),
										]) }}
									</div>
								@endif
							</div>
						</div>
					</div>

					<div class="response response-mobile tac clearfix">
						<p class="blue tac">
							{{ trans('vox.page.'.$current_page.'.change-questions') }}
						</p>
						<a href="{{ getLangUrl('stats/'.$vox->id.'/'.$prev) }}" class="previous previous-question">
							<< {{ trans('vox.page.'.$current_page.'.previous') }}
						</a>
						<a href="{{ getLangUrl('stats/'.$vox->id.'/'.$next) }}" class="next next-question">
							{{ trans('vox.page.'.$current_page.'.next') }} >></a>
					</div>
					<div class="response tac clearfix">
						<p class="blue tac">
							{{ trans('vox.page.'.$current_page.'.share') }}
						</p>
						<a href="{{ getLangUrl('stats/'.$vox->id.'/'.$prev) }}" class="previous previous-question"><< {{ trans('vox.page.'.$current_page.'.previous') }}</a>
						<a href="javascript:;" class="share fb"><i class="fa fa-facebook"></i></a>
						<a href="javascript:;" class="share twt"><i class="fa fa-twitter"></i></a>
						<a href="{{ getLangUrl('stats/'.$vox->id.'/'.$next) }}" class="next next-question">{{ trans('vox.page.'.$current_page.'.next') }} >></a>
					</div>
				</div>

				<div class="col-md-5 questions-list">
					<div class="another-questions">
						<h3 class="statistics-title bold">
							{{ trans('vox.common.questionnaires') }}
						</h3>

						@foreach( $cats as $cat )
							@if($cat->voxes->isNotEmpty())
								<h4 class="category-title">{{ $cat->name }}</h4>
								<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">

									@foreach($cat->voxes as $loopvox)
										<div class="panel panel-default">
											<div class="panel-heading" role="tab" id="headingOne">
												<h4 class="panel-title">
													<a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse-{{ $loopvox->vox->id }}" aria-expanded="true" aria-controls="collapse-{{ $loopvox->vox->id }}" class="clearfix">
														@if(in_array($loopvox->vox->id, $answered))
															<i class="fa fa-check"></i> 
														@endif
														{{ $loopvox->vox->title }} 
														<span>
															<i class="fa fa-clock-o"></i> {{ $loopvox->vox->formatDuration() }}
															<i class="fa fa-trophy"></i> {{ $loopvox->vox->getRewardTotal() }} DCN
														</span>
													</a>
												</h4>
											</div>
											<div id="collapse-{{ $loopvox->vox->id }}" class="panel-collapse collapse {{ $loopvox->vox->id==$vox->id ? 'in' : '' }}" role="tabpanel" aria-labelledby="headingOne">
												<ul class="list-group"> 
													@foreach($loopvox->vox->questions as $loopquestion)
														<li class="list-group-item {{ $loopquestion->id == $question->id ? 'selected' : '' }}">
															<a href="{{ getLangUrl('stats/'.$loopvox->vox->id.'/'.$loopquestion->id) }}">
																{{ $loopquestion->question }}
															</a>
														</li>
													@endforeach
												</ul>
											</div>
										</div>
									@endforeach
								</div>
							@endif
						@endforeach
<!--
						<div class="gray-border"></div>
						<a href="javascript:;" class="triangle-up"></a>
						<div class="questions-wrapper" id="questions-wrapper">
							<div class="questions-inner" id="questions-inner">
								@foreach($voxes as $loopvox)
									<div class="another-question {{ $loopvox->id==$vox->id ? 'active' : '' }}">
										<div class="another-question-header clearfix">
											<div class="left">
												<span class="bold">{{ $loopvox->getRewardTotal() }} DCN</span>
											</div>
											<div class="right">
												<p></p>
												<p>
													{{ trans('vox.common.questions-count', ['count' => $loopvox->questions->count()]) }}
												</p>
											</div>
										</div>
										<h4 class="bold">{{ $loopvox->title }}</h4>
										<div class="another-question-content">
											<p class="question-description">{{ $loopvox->description }}</p>
											<a class="statistics" href="{{ getLangUrl('stats/'.$loopvox->id) }}">{{ trans('vox.common.check-statictics') }}</a>
											@if(!in_array($loopvox->id, $answered))
												@if(!empty($user))
													<a class="opinion" href="{{  $loopvox->getLink() }}">{{ trans('vox.common.take-the-test') }}</a>
												@else
													<a class="opinion" href="javascript:;" data-toggle="modal" data-target="#has-to-login">{{ trans('vox.common.take-the-test') }}</a>
												@endif
											@endif
										</div>
									</div>
								@endforeach
							</div>
						</div>
						<a href="javascript:;" class="triangle-down"></a>
-->
					</div>
				</div>
			</div>
		</div>




		<script type="text/javascript">
			var chart_data = {!! json_encode($chart_data) !!};
		</script>

		<div id="has-to-login" class="modal fade" role="dialog">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-body">
						<p class="popup-second-title">
							{{ trans('vox.page.'.$current_page.'.register-title') }}
						</p>
						<p class="popup-third-title">
							{{ trans('vox.page.'.$current_page.'.register-subtitle') }}
						</p>

						<div class="popup-buttons">
							<a class="popup-button" data-toggle="modal" data-target="#loginPopup" href="javascript:;">{{ trans('vox.page.'.$current_page.'.register-log-in') }}</a>
							<a class="popup-button" data-toggle="modal" data-target="#registerPopup" href="javascript:;">{{ trans('vox.page.'.$current_page.'.register-register') }}</a>
						</div>
					</div>
				</div>
			</div>
		</div>
    	
@endsection