@extends('admin')

@section('content')

<h1 class="page-header">
    {{ trans('admin.page.'.$current_page.'.title') }}

    @if($platform == 'trp')
        <a href="{{ url('cms/emails/trp/send-engagement-email') }}" onclick="return confirm('Are you sure you want to send this email?');" class="btn btn-primary pull-right">Send Re-engagament dentists without reviews last 30 days</a>
        <a href="{{ url('cms/emails/trp/send-monthly-email') }}" class="btn btn-primary pull-right" style="margin-right: 10px;">Send Monthly Email To Petya</a>
    @endif
    
</h1>

@include('admin.errors')

<!-- end page-header -->
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <div class="panel-heading-btn">
                    <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                </div>
                <h4 class="panel-title">{{ trans('admin.page.'.$current_page.'.title') }}</h4>
            </div>
            <div class="panel-body">
        		<div class="panel-body">
					@include('admin.parts.table', [
						'table_id' => 'emails-system',
						'table_fields' => [
							'name'				=> array(),
							'update'			=> array('format' => 'update'),
						],
                        'table_data' => $templates,
						'table_pagination' => false,
					])
                </div>
            </div>
        </div>
    </div>
</div>

@endsection