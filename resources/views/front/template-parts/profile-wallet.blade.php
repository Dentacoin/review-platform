<div class="panel panel-default">
    <div class="panel-heading">
        <h1 class="panel-title">
            {{ trans('front.page.profile.wallet.title') }}
        </h1>
    </div>
    <div class="panel-body panel-body-wallet">
    	<div id="has-wallet" style="display: none;">
    		<p>
                {{ trans('front.page.profile.wallet.hint') }}<br/>
    		</p>

    		<form class="form-horizontal">

	            <div class="form-group">
	                <label class="col-md-3 control-label">
                        {{ trans('front.page.profile.wallet.address') }}
                    </label>
	                <div class="col-md-9">
	                    {{ Form::text( 'wallet-address', '', array('class' => 'form-control', 'id' => 'wallet-address' )) }}
	                </div>
	            </div>

	            <div class="form-group">
	                <label class="col-md-3 control-label">
                        {{ trans('front.page.profile.wallet.balance') }}
                    </label>
	                <div class="col-md-9">
	                    {{ Form::text( 'wallet-balance', '', array('class' => 'form-control', 'id' => 'wallet-balance' )) }}
	                </div>
	            </div>

            </form>

    	</div>
    	<div id="has-no-wallet" style="display: none;">
            {!! nl2br(trans('front.page.profile.wallet.instructions')) !!}
    	</div>
    </div>
</div>