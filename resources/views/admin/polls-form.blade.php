@extends('admin')


@section('content')

	<h1 class="page-header">
	    {{ empty($item) ? 'Add new Daily Poll' : 'Edit Daily Poll' }}
		@if(!empty($item) && !empty($item->users_percentage))
		    <a class="btn btn-sm btn-success form-control user-b" style="max-width: 200px; float: right;" href="javascript:;" data-toggle="modal" data-target="#infoModal">Show restricted countries</a>
		@endif
	</h1>

	<!-- end page-header -->

	<div class="row">
	    <!-- begin col-6 -->
	    <div class="col-md-12 ui-sortable">
	        {{ Form::open(array('id' => 'poll-add', 'class' => 'form-horizontal', 'method' => 'post', 'files' => true, 'class' => 'form-horizontal polls-form')) }}
	            {!! csrf_field() !!}

	            <div class="panel panel-inverse panel-with-tabs custom-tabs">
	                <div class="panel-heading p-0">
	                    <!-- begin nav-tabs -->
	                    <div class="tab-overflow overflow-right">
	                        <ul class="nav nav-tabs nav-tabs-inverse">
	                            @foreach($langs as $code => $lang_info)
	                                <li class="{{ $loop->first ? 'active' : '' }}">
	                                    <a href="javascript:;" lang="{{ $code }}" data-toggle="tab" aria-expanded="false">{{ $lang_info['name'] }}</a>
	                                </li>
	                            @endforeach
	                        </ul>
	                    </div>
	                </div>
	                <div class="tab-content">
	                    <div class="form-group clearfix">
	                        <label class="col-md-2 control-label">Question</label>
	                        <div class="col-md-5" style="display: flex;"> 
	                            @foreach($langs as $code => $lang_info)
	                                <div class="tab-pane fade{{ $loop->first ? ' active in' : '' }} lang-{{ $code  }} " style="flex: 1;">
	                                    {{ Form::textarea('question-'.$code, !empty($item) ? $item->{'question:'.$code} : '', array('maxlength' => 2048, 'class' => 'form-control input-title', 'style' => 'max-height: 34px;')) }}
	                                </div>
	                            @endforeach
	                        </div>
	                        @if(!empty($item) && !empty($item->launched_at))
		                        <div class="col-md-5">
		                        	<a class="btn btn-primary pull-right" href="{{ url('en/daily-polls/'.$poll_date) }}" target="_blank">Test mode</a>
		                        </div>
		                    @endif
	                    </div>
	                    
	                	<div class="form-group">
	                       	<label class="col-md-2 control-label">Calendar date</label>
			                <div class="col-md-5">
			                    {{ Form::text('launched_at', !empty($item) && $item->launched_at ? date('Y-m-d', $item->launched_at->timestamp ) : date('Y-m-d', \App\Models\Poll::orderby('id','desc')->first()->launched_at->timestamp + 86400), array('class' => 'form-control polldatepicker', 'autocomplete' => 'off')) }}
			                </div>
			            </div>
	                        
	                    @if(!empty($item))
		                	<div class="form-group">
				            	<label class="col-md-2 control-label">Status</label>
				                <div class="col-md-5">
				                    {{ Form::select('status', $statuses, $item->status, array('class' => 'form-control')) }} 
				                </div>
				            </div>
				        @endif

			            <div class="form-group">
	                        <label class="col-md-2 control-label" style="padding-top: 0px; ">Category</label>
	                        <div class="col-md-10">
	                            @foreach($categories as $k => $cat)
	                                <label class="col-md-3" for="cat-{{ $k }}">
	                                    <input type="radio" name="category" value="{{ $k }}" id="cat-{{ $k }}" {!! !empty($item) && ($item->category == $k) ? 'checked="checked"' : '' !!} >
	                                    {{ $cat }}
	                                </label>
	                            @endforeach
	                        </div>
	                    </div>

	                    <div class="form-group clearfix">
	                        <label class="col-md-2 control-label">Scale</label>
	                        <div class="col-md-2">
	                            {{ Form::select('scale-id', ['' => '-'] + $scales, !empty($item) ? $item->scale_id : null, array('class' => 'form-control scale-input')) }}
	                        </div>
	                    </div>

	                    <div class="form-group clearfix">
	                        <label class="col-md-2 control-label" for="dont_randomize_answers">Don’t randomize answers</label>
	                        <div class="col-md-1">
	                            <input type="checkbox" name="dont_randomize_answers" class="form-control" value="1" id="dont_randomize_answers" style="vertical-align: sub;width: 30px;" {!! !empty($item) && !empty($item->dont_randomize_answers) ? 'checked="checked"' : '' !!} />
	                        </div>                        
	                    </div>

	                    @foreach($langs as $code => $lang_info)
	                        <div class="tab-pane questions-pane fade{{ $loop->first ? ' active in' : '' }} lang-{{ $code  }}" lang="{{ $code }}">
	                            <div class="form-group answers-group">
	                       			<label class="col-md-2 control-label">Answers</label>
	                                <div class="col-md-10 answers-list answers-draggable">
	                                    @if(!empty($item) && !empty($item->{'answers:'.$code}) )
	                                        @foreach(json_decode($item->{'answers:'.$code}, true) as $key => $ans)
	                                            <div class="flex input-group">
	                                                <div class="col">
	                                                    {{ Form::text('answers-'.$code.'[]', $ans, array('maxlength' => 2048, 'class' => 'form-control poll-answers', 'placeholder' => 'Answer', 'style' => 'display: inline-block; width: calc(100% - 60px);')) }}
	                                                    
	                                                    <div class="input-group-btn" style="display: inline-block;">
	                                                        <button class="btn btn-default btn-remove-answer" type="button" style="height: 34px;">
	                                                            <i class="glyphicon glyphicon-remove"></i>
	                                                        </button>
	                                                    </div>
	                                                </div>
	                                            </div>
	                                        @endforeach
	                                    @else
	                                        <div class="input-group">
	                                            {{ Form::text('answers-'.$code.'[]', '', array('maxlength' => 2048, 'class' => 'form-control poll-answers', 'placeholder' => 'Answer')) }}
	                                            <div class="input-group-btn">
	                                                <button class="btn btn-default btn-remove-answer" type="button" style="height: 34px;">
	                                                    <i class="glyphicon glyphicon-remove"></i>
	                                                </button>
	                                            </div>
	                                        </div>
	                                    @endif
	                                </div>
	                            </div>
	                            <div class="form-group answers-group-add">
	                                <label class="col-md-2 control-label"></label>
	                                <div class="col-md-10">	                    
				                        How ANSWER tooltips work: <br/>
				                        Do you [includes cigars, e-cigarettes and any other tobacco products]smoke cigarettes[/]?<br/>
				                        <br/>
	                                    <a href="javascript:;" class="btn btn-success btn-block btn-add-answer">Add new answer</a>
	                                </div>
	                            </div>
	                        </div>
	                    @endforeach

	                    <div class="form-group" style="margin-top: 60px;">
	                        <div class="col-md-12">
	                            <button type="submit" class="btn btn-block btn-success">Save</button>
	                        </div>
	                    </div>

	                </div>
	            </div>

	        {{ Form::close() }}
	    </div>
	</div>

	@if(!empty($item))
		<a class="btn btn-primary btn-block" href="javascript: $('#import-poll-answers').show();">
		    Import answers
		</a>
		<div id="import-poll-answers" style="display: none;">
		    <div class="panel panel-inverse">
		        <div class="tab-content">
		            <div class="row">
		                <div class="col-md-12">
		                    <h4>Import answers</h4>
		                    <form class="form-horizontal" id="import-poll-answers-form" method="post" action="{{ url('cms/vox/polls/edit/'.$item->id.'/import') }}" enctype="multipart/form-data">
		                        {!! csrf_field() !!}
		                        <div class="row">
		                            <div class="col-md-6">
		                                <input type="file" class="btn-block form-control" name="table" accept=".xls, .xlsx" />
		                            </div>
		                            <div class="col-md-6">
		                                <button type="submit" class="btn btn-success btn-block">
		                                    Import
		                                </button>
		                            </div>
		                        </div>
		                    </form>
		                    <br/>
		                    <a href="{{ url('poll-import-template.xlsx') }}">Download sample</a>
		                </div>
		            </div>
		        </div>
		    </div>
		</div>
	@endif

	<div style="display: none;">
	    <div class="flex input-group ui-sortable-handle" id="input-group-template">
	        <div class="col">
	            {{ Form::text('something', '', array('maxlength' => 2048, 'class' => 'form-control answer-name poll-answers', 'placeholder' => 'Answer', 'style' => 'display: inline-block; width: calc(100% - 60px);')) }}
	            <div class="input-group-btn" style="display: inline-block;">
	                <button class="btn btn-default btn-remove-answer" type="button" style="height: 34px;">
	                    <i class="glyphicon glyphicon-remove"></i>
	                </button>
	            </div>
	        </div>
	    </div>
	</div>

	@if(!empty($item) && !empty($item->users_percentage))
	    <div id="infoModal" class="modal fade" role="dialog">
		    <div class="modal-dialog">
		        <!-- Modal content-->
		        <div class="modal-content">
		            <div class="modal-header">
		                <button type="button" class="close" data-dismiss="modal">&times;</button>
		                <h4 class="modal-title">Restricted countries</h4>
		            </div>
		            <div class="modal-body">
	                    @foreach($item->users_percentage as $c => $up)
	                        <p {!! intval($up) > 20 ? 'style="color:red;"' : '' !!}> {{ App\Models\Country::find($c)->name }} : {{ $up }}% <p/>
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