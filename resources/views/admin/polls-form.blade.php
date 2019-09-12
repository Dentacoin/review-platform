@extends('admin')


@section('content')

	<h1 class="page-header">
	    {{ empty($item) ? 'Add new Daily Poll' : 'Edit Daily Poll' }}
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
			                    {{ Form::text('launched_at', !empty($item) && $item->launched_at ? date('Y-m-d', $item->launched_at->timestamp ) : null, array('class' => 'form-control polldatepicker', 'autocomplete' => 'off')) }}
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
	                        <label class="col-md-2 control-label" style="padding-top: 0px; max-width: 200px;">Category</label>
	                        <div class="col-md-10">
	                            @foreach($categories as $k => $cat)
	                                <label class="col-md-3" for="cat-{{ $k }}">

	                                    <input type="radio" name="category" value="{{ $k }}" id="cat-{{ $k }}" {!! !empty($item) && ($item->category == $k) ? 'checked="checked"' : '' !!} >
	                                    
	                                    {{ $cat }}
	                                </label>
	                            @endforeach
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

@endsection

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
