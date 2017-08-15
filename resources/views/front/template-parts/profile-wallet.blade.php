<div class="panel panel-default">
    <div class="panel-heading">
        <h1 class="panel-title">
            {{ trans('front.page.profile.wallet.title') }}
        </h1>
    </div>
    <div class="panel-body panel-body-wallet">
    	<div id="has-wallet" style="display: none;">
    		<p>
    			You have MetaMask installed. And here's you address and balance:<br/>
    		</p>

    		<form class="form-horizontal">

	            <div class="form-group">
	                <label class="col-md-3 control-label">Wallet address</label>
	                <div class="col-md-9">
	                    {{ Form::text( 'wallet-address', '', array('class' => 'form-control', 'id' => 'wallet-address' )) }}
	                </div>
	            </div>

	            <div class="form-group">
	                <label class="col-md-3 control-label">Wallet balance</label>
	                <div class="col-md-9">
	                    {{ Form::text( 'wallet-balance', '', array('class' => 'form-control', 'id' => 'wallet-balance' )) }}
	                </div>
	            </div>

            </form>

    	</div>
    	<div id="has-no-wallet" style="display: none;">
    		You don't have MetaMask Installed. Instructions on how to install:<br/>
    		Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.
    	</div>
    </div>
</div>