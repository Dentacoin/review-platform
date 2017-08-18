@extends('front')

@section('content')

<div class="container">
	<div class="col-md-3">
		@include('front.template-parts.profile-menu')
	</div>
	<div class="col-md-9">

        <div class="panel panel-default">
            <div class="panel-heading">
                <h1 class="panel-title">
                    {{ trans('front.page.profile.'.$current_subpage.'.title') }}
                </h1>
            </div>
            <div class="panel-body">
                <p>
                	{{ trans('front.page.profile.'.$current_subpage.'.hint') }}
                </p>

				{!! Form::open(array('method' => 'post', 'class' => 'form-horizontal clearfix', 'id' => 'invite-patient-form' )) !!}
					<div class="col-md-5">
                        {{ Form::text( 'name', '', array('class' => 'form-control', 'id' => 'invite-name', 'placeholder' => trans('front.page.profile.'.$current_subpage.'.name') ) ) }}
					</div>
					<div class="col-md-5">
                        {{ Form::text( 'email', '', array('class' => 'form-control', 'id' => 'invite-email', 'placeholder' => trans('front.page.profile.'.$current_subpage.'.email') ) ) }}
					</div>
					<div class="col-md-2">
						{{ Form::submit( trans('front.page.profile.'.$current_subpage.'.submit'), array('class' => 'form-control btn btn-primary' ) ) }}
					</div>
					<input type="hidden" name="invite-secret" id="invite-secret" value="">
				{!! Form::close() !!}
				<div class="alert alert-warning" id="invite-alert-secret" style="display: none; margin-top: 20px;">
                	{{ trans('front.page.profile.'.$current_subpage.'.accept-transaction') }}
				</div>
				<div class="alert" id="invite-alert" style="display: none; margin-top: 20px;">
				</div>
			</div>
		</div>
	</div>
</div>

@endsection