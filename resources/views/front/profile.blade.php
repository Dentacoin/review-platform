@extends('front')

@section('content')

<div class="container">
	@include('front.errors')

	<div class="col-md-3">
		@include('front.template-parts.profile-menu')
	</div>
	<div class="col-md-9">

		<div class="strength-line">
			<p>{{ trans('front.page.strength.title') }}: <b>{{ $user->is_dentist ? trans('front.page.strength.dentist.level-'.$user->getStrengthNumber()) : trans('front.page.strength.patient.level-'.$user->getStrengthNumber()) }}</b></p>
			<div class="empty-line level{{ $user->getStrengthNumber() }} {{ $user->is_dentist ? '' : 'patient-strength'}}">
				<div class="cutter">
					<div class="full-line">
					</div>
				</div>
			</div>
		</div>

		@if(!array_search(false, $user->getStrength())===false)
			<div class="strength-wrapper col-md-12 clearfix">
				<h3>{{ trans('front.page.strength.strength-title') }}</h3>

				<div class="strenght-repeat">
					<div class="strength-box bxslider clearfix">
						@foreach($user->getStrength() as $key => $val)
							@if(!$val)
								<div class="strength-inner">
									<div class="strength-border">
										<img src="{{ url('/img/'.$key.'.png') }}">
										<h4>{{ trans('front.page.strength.'.$key.'-title') }}</h4>
										<p>{{ trans('front.page.strength.'.$key.'-hint') }}</p>
									</div>

									<a class="btn btn-primary" href="{{ $buttons_link[$key] }}">
										{{ trans('front.page.strength.'.$key.'-btn') }}
									</a>
								</div>
							@endif
						@endforeach
					</div>
				</div>
			</div>
		@endif

		<!-- <div class="panel panel-default" style="clear: both;">
			<div class="panel-heading">
				<h1 class="panel-title">
					{{ trans('front.page.profile.title') }}
				</h1>
			</div>
			<div class="panel-body panel-profile-dashboard">
				<p>
					{{ trans('front.page.profile.hint') }}
				</p>

				<div class="form-group clearfix">
					<div class="col-md-6">
						<div class="panel panel-success">
							<div class="panel-heading">
								<h3 class="panel-title">
									{{ trans('front.page.profile.alert-avatar-title') }}
								</h3>
							</div>
							<div class="panel-body">
								@if($needs_avatar)
									{{ trans('front.page.profile.alert-avatar') }}
									<a class="btn btn-primary btn-block" href="javascript:$('#avatar-uplaoder').click();">
										{{ trans('front.page.profile.alert-avatar-btn') }}
									</a>
								@else
									{{ trans('front.page.profile.alert-avatar-done') }}
								@endif
							</div>
						</div>				
					</div>
					<div class="col-md-6">
						<div class="panel panel-success">
							<div class="panel-heading">
								<h3 class="panel-title">
									{{ trans('front.page.profile.alert-reward-title') }}
								</h3>
							</div>
							<div class="panel-body">
								@if($no_reward)
									{{ trans('front.page.profile.alert-reward') }}
									<a class="btn btn-primary btn-block" href="{{ getLangUrl('profile/wallet') }}">
										{{ trans('front.page.profile.alert-reward-btn') }}
									</a>
								@else
									@if($user->is_dentist)
										{{ trans('front.page.profile.alert-reward-done') }}
									@else
										{{ trans('front.page.profile.alert-reward-done-patient') }}
									@endif
								@endif
							</div>
						</div>				
					</div>
				</div>

				<div class="form-group clearfix">
					<div class="col-md-6">
						<div class="panel panel-success">
							<div class="panel-heading">
								<h3 class="panel-title">
									{{ trans('front.page.profile.alert-invite-title') }}
								</h3>
							</div>
							<div class="panel-body">
								@if($no_invites)
									{{ trans('front.page.profile.alert-invite') }}
									<a class="btn btn-primary btn-block" href="{{ getLangUrl('profile/invite') }}">
										{{ trans('front.page.profile.alert-invite-btn') }}
									</a>
								@else
									{{ trans('front.page.profile.alert-invite-done') }}
								@endif
							</div>
						</div>				
					</div>

					@if($user->is_dentist)
						<div class="col-md-6">
							<div class="panel panel-success">
								<div class="panel-heading">
									<h3 class="panel-title">
										{{ trans('front.page.profile.alert-address-title') }}
									</h3>
								</div>
								<div class="panel-body">
									@if($no_address)
										{{ trans('front.page.profile.alert-address') }}
										<a class="btn btn-primary btn-block" href="{{ getLangUrl('profile/info') }}">
											{{ trans('front.page.profile.alert-address-btn') }}
										</a>
									@else
										{{ trans('front.page.profile.alert-address-done') }}
									@endif
								</div>
							</div>				
						</div>
					@else
						<div class="col-md-6">
							<div class="panel panel-success">
								<div class="panel-heading">
									<h3 class="panel-title">
										{{ trans('front.page.profile.alert-reviews-title') }}
									</h3>
								</div>
								<div class="panel-body">
									@if($no_reviews)
										{{ trans('front.page.profile.alert-reviews') }}
										<a class="btn btn-primary btn-block" href="{{ getLangUrl('search') }}">
											{{ trans('front.page.profile.alert-reviews-btn') }}
										</a>
									@else
										{{ trans('front.page.profile.alert-reviews-done') }}
									@endif
								</div>
							</div>				
						</div>
					@endif
				</div>

			</div>
		</div> -->

		@include('front.template-parts.profile-wallet')
		@include('front.template-parts.profile-reviews')
	</div>
</div>

@endsection