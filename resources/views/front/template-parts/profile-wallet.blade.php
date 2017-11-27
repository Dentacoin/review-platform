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
            <p>
                {!! nl2br(trans('front.page.profile.wallet.instructions', [
                    'link' => '<a href="'.url('MetaMaskInstructions.pdf').'" target="_blank">',
                    'endlink' => '</a>',
                ])) !!}
            </p>

            <form class="form-horizontal" id="balance-form" method="POST" action="{{ getLangUrl('profile/balance') }}">
                {{ Form::token() }}

                <div class="form-group">
                    <label class="col-md-3 control-label">{{ trans('front.page.profile.balance.address') }}</label>
                    <div class="col-md-9">
                        {{ Form::text( 'balance-address', '', array('class' => 'form-control', 'id' => 'transfer-balance-address' )) }}
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary btn-block">
                            {{ trans('front.page.profile.balance.submit') }}
                        </button>
                    </div>
                </div>

            </form>

            <div class="alert alert-success" id="balance-succcess" style="display: none;">
                {{ trans('front.page.profile.balance.succcess') }}
                <b id="balance-amount"></b>
                <br/>
                <a href="" target="_blank">
                    {{ trans('front.page.profile.balance.etherscan') }}
                </a>
            </div>
            <div class="alert alert-warning" id="balance-error" style="display: none;">
                {{ trans('front.page.profile.balance.error') }}<br/>
                <span id="balance-reason">
                </span>
            </div>
    	</div>
    </div>
</div>