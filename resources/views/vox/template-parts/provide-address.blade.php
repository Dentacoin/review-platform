<form class="form-horizontal" id="provide-form" method="POST" action="{{ getLangUrl('profile/address') }}">
	{{ Form::token() }}

    <div class="form-group">
    	<div class="col-md-12">
    		<p>
    			{!! nl2br(trans('vox.common.provide-address-hint', [
    				'link' => '<a class="text" href="'.url('DentavoxMetamask.pdf').'" target="_blank">',
    				'endlink' => '</a>',
    			])) !!}
    		</p>
    		<br/>
    	</div>
        <label class="col-md-3 control-label">{{ trans('vox.common.provide-address-address') }}</label>
        <div class="col-md-9">
            {{ Form::text( 'provide-address', '', array('class' => 'form-control', 'id' => 'provide-address' )) }}
        </div>
    </div>
    <div class="form-group">
        <div class="col-md-12">
            <button type="submit" class="btn btn-primary btn-block" data-loading="{{ trans('front.common.loading') }}">
            	{{ trans('vox.common.provide-address-submit') }}
            </button>
        </div>
    </div>

</form>

<div class="alert alert-success" id="provide-succcess" style="display: none;">
</div>
<div class="alert alert-warning" id="provide-invalid" style="display: none;">
</div>