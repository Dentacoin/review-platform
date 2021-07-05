@extends('admin')

@section('content')

	<h1 class="page-header">Test Voxes</h1>
	<!-- end page-header -->

	@foreach($arr as $k => $questions)
		<div class="row">
	        <div class="col-md-12">
	            <div class="panel panel-inverse">
	                <div class="panel-heading">
	                    <div class="panel-heading-btn">
	                        <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
	                    </div>
	                    <h4 class="panel-title">{{ $questions['name'] }}</h4>
	                </div>
	                <div class="panel-body">
	                    <div class="dataTables_wrapper">
	                        <div class="row">
	                            <div class="col-sm-12 table-responsive-md">
	                                <table class="table table-striped">
	                                    <thead>
	                                        <tr>
	                                            <th>Question Order</th>
	                                            @if($k == 'questions_cross_check')
	                                            	<th>Cross check</th>
	                                            @endif
	                                            <th>Vox ID</th>
	                                            <th>Test vox</th>
	                                            <th>Open vox in cms</th>
	                                        </tr>
	                                    </thead>
	                                    <tbody>
	                                        @foreach($questions['value'] as $question)
	                                            <tr>
	                                                <td>
	                                                    {{ $question->order }}
	                                                </td>
	                                                @if($k == 'questions_cross_check')
		                                            	<td>{{ $question->cross_check }}</td>
		                                            @endif
	                                                <td>
	                                                    {{ $question->vox_id }}
	                                                </td>
	                                                <td>
	                                                    <a href="{{ $question->vox->getLink().'?testmode=1' }}" target="_blank" class="btn btn-info btn-sm">Test Survey</a>
	                                                </td>
	                                                <td>
	                                                    <a href="{{ url('cms/vox/edit/'.$question->vox_id) }}" target="_blank" class="btn btn-primary btn-sm">Open Survey</a>
	                                                </td>
	                                            </tr>
	                                        @endforeach
	                                    </tbody>
	                                </table>
	                            </div>
	                        </div>
	                    </div>
	                </div>
	            </div>
	        </div>
	    </div>
	@endforeach

@endsection