@extends('admin')


@section('content')

	<h1 class="page-header">
	    {{ trans('admin.page.'.$current_page.'.edit-question') }}
	</h1>

	@if(!empty($error))
	   <i class="fa fa-exclamation-triangle" data-toggle="modal" data-target="#errorsModal" style="color: red;font-size: 20px;margin-bottom: 20px;"></i>
	@endif
	<!-- end page-header -->

	<div class="row">
        @include('admin.parts.vox-question')
	</div>

	@if(!empty($error))
	    <div id="errorsModal" class="modal fade" role="dialog">
	        <div class="modal-dialog">
	            <!-- Modal content-->
	            <div class="modal-content">
	                <div class="modal-header">
	                    <button type="button" class="close" data-dismiss="modal">&times;</button>
	                    <h4 class="modal-title">Errors</h4>
	                </div>
	                <div class="modal-body">
	                    @foreach($error_arr as $key => $value)
	                        {{ $key+1 }}. <a href="{{ isset($value['link']) ?? 'javascript:;'  }}" target="_blank">{{ $value['error'] }}</a><br/>
	                    @endforeach

	                </div>
	                <div class="modal-footer">
	                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
	                </div>
	            </div>

	        </div>
	    </div>
	@endif

@endsection