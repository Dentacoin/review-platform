@extends('front')

@section('content')

<div class="container">
	<div class="col-md-3">
		@include('front.template-parts.profile-menu')
	</div>
	<div class="col-md-9">
		@include('front.template-parts.profile-wallet')
		
		<div class="panel panel-default" id="transfer-widget" style="display: none;">
		    <div class="panel-heading">
		        <h1 class="panel-title">
		            {{ trans('front.page.profile.wallet.transfer-title') }}
		        </h1>
		    </div>
		    <div class="panel-body panel-body-wallet">
	    		<p>
	    			{{ trans('front.page.profile.wallet.transfer-hint') }}<br/>
	    		</p>

	    		<form class="form-horizontal">

		            <div class="form-group">
		                <label class="col-md-3 control-label">{{ trans('front.page.profile.wallet.transfer-address') }}</label>
		                <div class="col-md-9">
		                    {{ Form::text( 'wallet-address', '', array('class' => 'form-control', 'id' => 'transfer-wallet-address' )) }}
		                </div>
		            </div>

		            <div class="form-group">
		                <label class="col-md-3 control-label">{{ trans('front.page.profile.wallet.transfer-amount') }}</label>
		                <div class="col-md-9">
		                    {{ Form::text( 'wallet-amount', '', array('class' => 'form-control', 'id' => 'transfer-wallet-amount' )) }}
		                </div>
		            </div>
		            <div class="form-group">
		                <div class="col-md-12">
		                    <a href="javascript:;" class="btn btn-primary btn-block" id="transfer-button">
		                    	{{ trans('front.page.profile.wallet.transfer-submit') }}
		                    </a>
		                    <div class="alert alert-success" id="transfer-succcess" style="display: none;">
		                    	{{ trans('front.page.profile.wallet.transfer-succcess') }}
		                    </div>
		                    <div class="alert alert-warning" id="transfer-error" style="display: none;">
		                    	{{ trans('front.page.profile.wallet.transfer-error') }}<br/>
		                    	<span id="transfer-reason">
		                    	</span>
		                    </div>
		                </div>
		            </div>

	            </form>

		    </div>
		</div>
	</div>
</div>

@endsection