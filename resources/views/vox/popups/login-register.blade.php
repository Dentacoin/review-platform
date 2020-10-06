<div class="popup login-register active" id="login-register-popup">
	<div class="wrapper">
		<div class="inner vox-not-logged">
			<h2>
				{{ trans('vox.page.questionnaire.not-logged-title') }}
			</h2>

			<div class="flex break-mobile">
				<div class="col">
					<img src="{{ url('new-vox-img/vox-not-logged-register.png') }}" />
					<div class="flex flex-column">
						<h3>
							{{ trans('vox.page.questionnaire.not-logged-register-title') }}
						</h3>
						<p class="flex-1">
							{{ trans('vox.page.questionnaire.not-logged-register-content') }}
						</p>
						<a class="btn reg-but check-welcome" href="{{ getLangUrl('welcome-survey') }}">
							{{ trans('vox.page.questionnaire.not-logged-register-button') }}
						</a>
					</div>
				</div>
				<div class="col">
					<img src="{{ url('new-vox-img/vox-not-logged-login.png') }}" />
					<div class="flex flex-column">
						<h3>
							{{ trans('vox.page.questionnaire.not-logged-login-title') }}
						</h3>
						<p class="flex-1">
							{{ trans('vox.page.questionnaire.not-logged-login-content') }}
						</p>
						<a class="btn open-dentacoin-gateway patient-login" href="javascript:;">
							{{ trans('vox.page.questionnaire.not-logged-login-button') }}
						</a>
					</div>
				</div>
			</div>

		</div>
		<a class="closer x">
			<i class="fas fa-times"></i>
		</a>
	</div>
</div>