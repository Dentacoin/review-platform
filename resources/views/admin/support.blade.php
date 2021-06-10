@extends('admin')

@section('content')

<h1 class="page-header">
    Support

    <!-- <a href="{{ url('cms/'.$current_page.'/content/add') }}" class="btn btn-sm btn-success pull-right">Add question</a> -->
</h1>
<!-- end page-header -->

@if($categories->isNotEmpty()) 
	<div class="row">
	    <!-- begin col-6 -->
	    <div class="ui-sortable">
	        {{ Form::open(array('id' => 'page-add', 'class' => 'form-horizontal', 'method' => 'post', 'files' => true)) }}

	            <div class="panel panel-inverse panel-with-tabs" data-sortable-id="ui-unlimited-tabs-1">
	                <div class="panel-heading p-0">
	                    <div class="panel-heading-btn m-r-10 m-t-10">
	                        <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-success" data-click="panel-expand" data-original-title="" title=""><i class="fa fa-expand"></i></a>
	                    </div>
	                    <!-- begin nav-tabs -->
	                    <div class="tab-overflow overflow-right">
	                        <ul class="nav nav-tabs nav-tabs-inverse">
	                            <li class="prev-button"><a href="javascript:;" data-click="prev-tab" class="text-success"><i class="fa fa-arrow-left"></i></a></li>
	                            @foreach($categories as $cat)
	                                <li class="{{ $loop->first ? 'active' : '' }}">
	                                	<a href="#nav-tab-{{ $cat->id }}" data-toggle="tab" aria-expanded="false">{{ $cat->name }}</a>
	                                </li>
	                            @endforeach
	                            <li class="next-button"><a href="javascript:;" data-click="next-tab" class="text-success"><i class="fa fa-arrow-right"></i></a></li>
	                        </ul>
	                    </div>
	                </div>
	                <div class="tab-content" style="padding: 0px;">
	                    @foreach($categories as $cat)
	                        <div class="tab-pane fade{{ $loop->first ? ' active in' : '' }}" id="nav-tab-{{ $cat->id }}">
	                            <div class="panel-body">
	                            	<div class="dataTables_wrapper">
									    <div class="cat">
									    	<div class="col-sm-12">
									    		<table class="table table-striped">
									                <thead>
									                    <tr>
									                       	<th>
									                            Question
									                        </th>
									                       	<!-- <th>
									                            Answer
									                        </th> -->
									                       	<th>
									                            Main
									                        </th>
									                        <th>
									                            Edit
									                        </th>
									                        <th>
									                            Delete
									                        </th>
									                    </tr>
									                </thead>
									                <tbody>
	                									@if($cat->questions->isNotEmpty())
										                	@foreach($cat->questions as $question)
										                    	<tr>
										                            <td>
										                                {{ $question->question }}
										                            </td>
										                            <!-- <td>
										                            	{!! nl2br($question->content) !!}
										                            	<textarea name="text" class="form-control textarea-ckeditor" contenteditable="true" id="{{ $question->id }}">{!! nl2br($question->content) !!}</textarea>
										                                <a class="btn btn-success save-changes" href="javascript:;" style="display: none;">Save</a>
										                            </td> -->
										                            <td>
										                            	{{ $question->is_main ? "Yes" : '' }}
										                            </td>
										                            <td>
										                                <a class="btn btn-sm btn-primary" href="{{ url('cms/'.$current_page.'/content/edit/'.$question->id) }}">{{ trans('admin.table.edit') }}</a>
										                            </td>
										                            <td>
										                                <a class="btn btn-sm btn-deafult delete-question" href="{{ url('cms/'.$current_page.'/content/delete/'.$question->id) }}" onclick="return confirm('Are you sure you want to DELETE this?');">{{ trans('admin.table.delete') }}</a>
										                            </td>
										                    	</tr>
										                    @endforeach
										                @endif
									                </tbody>
									            </table>
									        </div>
									    </div>
									</div>
	                            </div>
	                        </div>
	                    @endforeach
	                </div>
	            </div>

	        {{ Form::close() }}
	    </div>

	    <div class="row">
	    <!-- begin col-6 -->
		    <div class="col-md-12">
		        <!-- begin panel -->
		        <form id="add-support-question" method="post" action="{{ url('cms/support/content/add/') }}" edit-url="{{ url('cms/support/content/edit/') }}" delete-url="{{ url('cms/support/content/delete/') }}">
			        <div class="panel panel-inverse">
			            <div class="panel-heading">
			                <div class="panel-heading-btn">
			                    <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
			                </div>
			                <h4 class="panel-title">Add a new question</h4>
			            </div>
			            <div class="panel-body">
		                    <div class="form-group clearfix">
		                        <label class="col-md-1 control-label">Question</label>
		                        <div class="col-md-11">
		                            {{ Form::text('question', null, array('maxlength' => 512, 'class' => 'form-control', 'id' => 'question')) }}
		                        </div>
		                    </div>
		                    <div class="form-group clearfix">
		                        <label class="col-md-1 control-label">Slug</label>
		                        <div class="col-md-11">
		                            {{ Form::text('slug', null, array('maxlength' => 128, 'class' => 'form-control', 'id' => 'slug')) }}
		                        </div>
		                    </div>
		                    <div class="form-group clearfix">
		                        <label class="col-md-1 control-label">Answer</label>
		                        <div class="col-md-11">
		                            <textarea name="answer" class="form-control textarea-ckeditor" contenteditable="true" id="answer"></textarea>
		                        </div>
		                    </div>
		                    <div class="form-group clearfix">
		                        <label class="col-md-1 control-label">Category</label>
		                        <div class="col-md-11">
		                            {{ Form::select('category_id', $categories->pluck('name', 'id')->toArray(), $categories->first()->name, array('class' => 'form-control', 'id' => 'question-category')) }}
		                        </div>
		                    </div>
		                    <div class="form-group clearfix">
		                        <label class="col-md-1 control-label" for="main">Main</label>
		                        <div class="col-md-11">
		                            <input type="checkbox" name="is_main" class="form-control" value="1" id="is_main" style="vertical-align: sub;width: 30px;" />
		                        </div>
		                    </div>

		                    <label class="alert alert-danger" id="question-error" style="display: none;margin-top: 10px;"></label>
		                </div>
		            </div>

		            <div class="form-group">
		                <label class="col-md-10 control-label"></label>
		                <div class="col-md-2">
		                    <button type="submit" class="btn btn-block btn-sm btn-success">Submit</button>
		                </div>
		            </div>
		        </form>
	        </div>

	    </div>
	</div>

	<style type="text/css">
		.panel .nav>li>a {
		    color: white !important;
		}

		.panel .nav>li.active>a {
			color: #242a30 !important;
		}

		#cke_42,
		#cke_18,
		#cke_39,
		#cke_38,
		#cke_26,
		#cke_19,
		#cke_13,
		#cke_5,
		.cke_button__image {
			display: none !important;
		}

		body.cke_editable.cke_editable_themed.cke_contents_ltr,
		.cke_wysiwyg_frame.cke_reset {
			height: 100%;
		}

	</style>
@else 
	
	<div>
        <label class="alert alert-warning">First you need to add categories from <a href="{{ url('cms/support/categories') }}">here</a></label>
    </div>

@endif

@endsection