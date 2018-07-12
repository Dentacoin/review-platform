@extends('vox')

@section('content')

		<div class="container container-ribbon">
			<div class="ribbon clearfix">
				<div class="left-t"></div>
				<div class="right-t"></div>
					<b>{{ $users_count }}</b>
					{{ trans('vox.page.index.users-count') }}
			</div>
		</div>
		<div class="full">
			<p class="first-absolute">
				{{ trans('vox.page.'.$current_page.'.title') }}
			</p>
			<p class="second-absolute">
				{{ trans('vox.page.'.$current_page.'.title-voice') }}
			</p>
		</div>
		<div class="container">
			<div class="questions">
				<div class="questions-dots">
					<div class="dot" id="current-question-bar" style="width: 0%;"></div>
				</div>
				<div class="triangle"></div>
				<div class="row questions-header clearfix">
					<div class="col-md-6">
						<span class="bold">
							{!! trans('vox.common.estimated_time', [
								'time' => '<span id="current-question-num"></span>'
							]) !!}
						</span>
					</div>
					<div class="col-md-6 tar">
						<span class="bold">
							<span id="dcn-test-reward-before">
								{!! trans('vox.common.dcn_to_be_collected') !!}: {{ $vox->getRewardTotal() }}
							</span>
							<span id="dcn-test-reward-after" style="display: none;">
								{!! trans('vox.common.dcn_collected') !!}:
								<span id="current-question-reward">
									
								</span>
							</span>
						</span>
					</div>
				</div>

				@foreach( $vox->questions as $question )
					<div class="question-group" data-id="{{ $question->id }}" {!! $loop->first && !$has_test ? '' : 'style="display: none;"' !!} >
						<div class="question">
							{!! nl2br($question->question) !!}
						</div>
						<div class="answers">
							@foreach(json_decode($question->answers, true) as $answer)
								<a href="javascript:;" class="answer" data-num="{{ $loop->index+1 }}">{{ $answer }}</a>
							@endforeach
						</div>
					</div>
				@endforeach
				
				<div class="question-done" {!! $has_test ? '' : 'style="display: none;"' !!}>
					<div class="question tac">
						{!! nl2br(trans('vox.page.'.$current_page.'.thank-you')) !!}
					</div>
				</div>

				<!-- <div class="question-hints" {!! $has_test ? 'style="display: none;"' : ''  !!}>
					<p class="hint">
						{{ trans('vox.page.'.$current_page.'.finish-all', ['reward' => $vox->getRewardTotal()]) }}
					</p>
				</div> -->
			</div>
		</div>

		<script type="text/javascript">
			var vox = {
				count: {{ $vox->questions->count() }},
				reward: {{ $vox->getRewardTotal() }},
				current: {{ $has_test ? $vox->questions->count() : '1' }}
			}
		</script>


		<div id="first-test-done" class="modal fade" role="dialog">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-body">
						<p class="popup-title">
							{{ trans('vox.page.'.$current_page.'.good-job') }}
						</p>
						<p class="popup-second-title">
							{{ trans('vox.page.'.$current_page.'.just-won') }}
						</p>
						<div class="price">
							<img src="{{ url('img/dc-logo.png') }}"/>
							<span class="coins">{{ $vox->getRewardTotal() }} DCN</span>
						</div>
						<p class="popup-third-title">
							{{ trans('vox.page.'.$current_page.'.ready-to-get') }}
						</p>

						<div class="popup-buttons">
							<a class="popup-button" data-toggle="modal" data-target="#loginPopup" href="javascript:;">
								{{ trans('vox.page.'.$current_page.'.log-in') }}
							</a>
							<a class="popup-button" data-toggle="modal" data-target="#registerPopup" href="javascript:;">
								{{ trans('vox.page.'.$current_page.'.register') }}
							</a>
						</div>
					</div>
				</div>
			</div>
		</div>
    	
@endsection