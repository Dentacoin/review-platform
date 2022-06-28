@extends('admin')


@section('content')

	<h1 class="page-header">
	    {{ empty($item) ? 'Add new Daily Poll Monthly Description' : 'Edit Daily Poll Monthly Description' }}
	</h1>

	<div class="row">
	    <div class="col-md-12 ui-sortable">
	        {{ Form::open(array(
				'id' => 'poll-month-add', 
				'class' => 'form-horizontal', 
				'method' => 'post', 
				'files' => true, 
				'class' => 'form-horizontal'
			)) }}
	            {!! csrf_field() !!}

	            <div class="panel panel-inverse panel-with-tabs custom-tabs">
	                <div class="panel-heading p-0">
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
	                	<div class="form-group">
	                       	<label class="col-md-2 control-label">Calendar date</label>
			                <div class="col-md-5">
			                    <select name="month" class="form-control" style="width: 50%; float: left; text-transform: capitalize;">
                                    @foreach(config('months') as $m => $month)
                                        <option value="{{ $m }}" {{ !empty($item) && $item->month == $m ? 'selected="selected"' : ($m == date('n') ? 'selected="selected"' : '') }}>{{ $month }}</option>
                                    @endforeach
                                </select>
                                <select name="year" class="form-control" style="width: 50%; float: left;">
                                    @for($i=date('Y')+1;$i>=2019;$i--)
                                        <option value="{{ $i }}" {{ !empty($item) && $item->year == $i ? 'selected="selected"' : ($i == date('Y') ? 'selected="selected"' : '') }}>{{ $i }}</option>
                                    @endfor
                                </select>     
			                </div>
			            </div>
	                    <div class="form-group clearfix">
	                        <label class="col-md-2 control-label">Description</label>
	                        <div class="col-md-5" style="display: flex;"> 
	                            @foreach($langs as $code => $lang_info)
	                                <div class="tab-pane fade{{ $loop->first ? ' active in' : '' }} lang-{{ $code  }} " style="flex: 1;">
	                                    {{ Form::textarea('description-'.$code, !empty($item) ? $item->{'description:'.$code} : '', array(
											'maxlength' => 1024, 
											'class' => 'form-control input-title'
										)) }}
	                                </div>
	                            @endforeach
	                        </div>
	                    </div>

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