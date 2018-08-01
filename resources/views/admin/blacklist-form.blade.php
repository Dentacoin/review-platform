<form class="form-horizontal" method="post">
	{!! csrf_field() !!}
    
    <div class="form-group">
        <label class="col-md-2 control-label">
            Pattern (use * as wildcard)
        </label>
        <div class="col-md-4">
            {{ Form::text('pattern', !empty($item) ? $item->pattern : '', array('class' => 'form-control')) }}
        </div>
        <label class="col-md-2 control-label">Field to look in</label>
        <div class="col-md-4">
            {{ Form::select('field', [
                'name' => 'Name',
                'email' => 'E-Mail address'
            ], !empty($item) ? $item->field : null, array('class' => 'form-control')) }}
        </div>
    </div>

    <div class="form-group">
        <label class="col-md-2 control-label">Notes (i.e. why you added it)</label>
        <div class="col-md-4">
            {{ Form::text('comments', !empty($item) ? $item->comments : null, array('class' => 'form-control')) }}
        </div>
    </div>

    <div class="form-group">
        <div class="col-md-12">
            <button type="submit" class="btn btn-block btn-sm btn-success">Save</button>
        </div>
    </div>
</form>