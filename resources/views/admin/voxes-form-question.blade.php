@extends('admin')


@section('content')

	<h1 class="page-header">
	    {{ trans('admin.page.'.$current_page.'.edit-question') }}
	</h1>
	<!-- end page-header -->

	<div class="row">
        @include('admin.parts.vox-question')
	</div>

@endsection