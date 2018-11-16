@extends('front')

@section('content')

<div class="container">
	<div class="col-md-3">
		@include('front.template-parts.profile-menu')
	</div>
	<div class="col-md-9">
		
		<div class="panel panel-default" id="reward-widget">
		    <div class="panel-heading">
		        <h1 class="panel-title">
		            {{ trans('front.page.profile.reward.transfer-title') }}
		        </h1>
		    </div>
		    <div class="panel-body panel-body-reward">
	    		<p>
	    			{{ trans('front.page.profile.reward.transfer-hint') }}<br/>
	    		</p>

	    		<form class="form-horizontal" id="reward-form" method="POST" action="{{ getLangUrl('profile/reward') }}">
	    			{{ Form::token() }}

		            <div class="form-group">
		                <label class="col-md-3 control-label">{{ trans('front.page.profile.reward.transfer-address') }}</label>
		                <div class="col-md-9">
		                    {{ Form::text( 'reward-address', '', array('class' => 'form-control', 'id' => 'transfer-reward-address' )) }}
		                </div>
		            </div>
		            <div class="form-group">
		                <div class="col-md-12">
		                    <button type="submit" class="btn btn-primary btn-block" data-loading="{{ trans('front.common.loading') }}">
		                    	{{ trans('front.page.profile.reward.transfer-submit') }}
		                    </button>
		                </div>
		            </div>

	            </form>

	            <div class="alert alert-success" style="display: none;" id="reward-pending">
                	{{ trans('front.page.profile.reward.transfer-pending') }}
                </div>
                <div class="alert alert-success" id="reward-succcess" style="display: none;">
                    {{ trans('front.page.profile.reward.transfer-succcess') }}
                    <a href="" target="_blank"></a>
                </div>
                <div class="alert alert-warning" id="reward-invalid" style="display: none;">
                	{{ trans('front.page.profile.reward.transfer-invalid') }}
                </div>
                <div class="alert alert-warning" id="reward-error" style="display: none;">
                	{{ trans('front.page.profile.reward.transfer-error') }}<br/>
                	<span id="reward-reason">
                	</span>
                </div>
		    </div>
		</div>
	</div>
</div>

@endsection