@extends('admin')

@section('content')

<h1 class="page-header">Support Contact</h1>
<!-- end page-header -->



<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <div class="panel-heading-btn">
                    <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                </div>
                <h4 class="panel-title">Support Contact filter</h4>
            </div>
            <div class="panel-body users-filters">
                <form method="get" action="{{ url('cms/support/contact') }}" id="users-filter-form">
                    <div class="row" style="margin-bottom: 10px;">
                        <div class="col-md-2">
                            <input type="text" class="form-control" name="search-user-id" value="{{ $search_user_id }}" placeholder="User ID">
                        </div>
                        <div class="col-md-2">
                            <input type="text" class="form-control" name="search-email" value="{{ $search_email }}" placeholder="User Email">
                        </div>
                        <div class="col-md-2">
                            <input type="text" class="form-control" name="search-name" value="{{ $search_name }}" placeholder="User Name">
                        </div>
                        <div class="col-md-2">
                        	<select class="form-control" name="search-platform">
                                <option value="">Platform</option>
                                @foreach(config('support.platforms') as $k => $platform)
                                	<option value="{{ $k }}" {!! $k==$search_platform ? 'selected="selected"' : '' !!}>{{ $platform }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                        	<select class="form-control" name="search-issue">
                                <option value="">Issue</option>
                                @foreach(config('support.issues') as $k => $issue)
                                	<option value="{{ $k }}" {!! $k==$search_issue ? 'selected="selected"' : '' !!}>{{ $issue }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="submit" class="btn btn-sm btn-primary btn-block" name="search" value="Search">
                        </div>
                    </div>
                    <div class="row custom-row" style="margin-bottom: 10px;">
                        <div class="col-md-1">
                            <input type="text" class="form-control datepicker" name="search-from" value="{{ $search_from }}" placeholder="From" autocomplete="off">
                        </div>
                        <div class="col-md-1">
                            <input type="text" class="form-control datepicker" name="search-to" value="{{ $search_to }}" placeholder="To" autocomplete="off">
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <div class="panel-heading-btn">
                    <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                </div>
                <h4 class="panel-title">Support Contact</h4>
            </div>
            <div class="panel-body">
				<div class="dataTables_wrapper">
				    <div class="row">
				    	<div class="col-sm-12 table-responsive-md">
                    		<p>Total count: {{ $total_count }}</p>
				    		<table class="table table-striped">
				                <thead>
				                    <tr>
				                    	<th>Date</th>
				                    	<th>User/Email</th>
				                    	<th>Platform</th>
				                    	<th>Issue</th>
				                    	<th>Description</th>
                                        <th>File</th>
                                        <th>Admin answer</th>
				                    </tr>
				                </thead>
				                <tbody>
				                	@foreach($items as $item)
				                    	<tr contact-id="{{ $item->id }}">
				                    		<td>
				                    			{{ date('d.m.Y, H:i:s', $item->created_at->timestamp) }}
				                    		</td>
				                    		<td>
                                                @if(!empty($item->user_id))
	                                                @if(!empty($item->user))
	    				                    			<a href="{{ url('cms/users/edit/'.$item->user_id) }}">
	    													{{ $item->user->name }}
	    												</a>
	    											@else
		    											deleted user from the database
	                                                @endif
                                                @else
                                                	@if($item->userEmail)
                                                		<a href="{{ url('cms/users/edit/'.$item->userEmail->id) }}">
	    													{{ $item->email }}
	    												</a>
	    											@else
                                                    	{{ $item->email }}
	                                                @endif
                                                @endif
				                    		</td>
				                    		<td>
                                                {{ config('support.platforms.'.$item->platform) }}
				                    		</td>
				                    		<td>
				                    			{{ config('support.issues.'.$item->issue) }}
				                    		</td>
				                    		<td style="word-break: break-all; max-width: 300px;">
				                    			{{ $item->description }}
				                    		</td>
                                            <td>
                                                @if(in_array($item->file_extension, $video_extensions))
                                                    <a href="{{ $item->getFileUrl() }}" class="html5lightbox">Video</a>
                                                @else
                                                    <a href="{{ $item->getFileUrl() }}" data-lightbox="contact{{ $item->id }}">
                                                        <img src="{{ $item->getFileUrl(true) }}" style="max-width: 30px;">
                                                    </a>
                                                @endif
                                            </td>
                                            <td class="actions">
                                                @if(empty($item->admin_answer) && empty($item->admin_answer_id))
                                                    <a class="btn btn-sm btn-primary answer-contact" href="javascript:;" data-toggle="modal" data-target="#answerModal" contact-id="{{ $item->id }}">
                                                        Answer
                                                    </a>
                                                @else
                                                    @if(!empty($item->admin_answer))
                                                        <a href="javascript;:" class="show-answer">Show answer</a>
                                                        <p style="display: none;">{!! nl2br($item->admin_answer) !!}</p>
                                                    @elseif(!empty($item->admin_answer_id))
                                                        Email: <a href="{{ url('cms/emails/edit/'.$item->admin_answer_id) }}">{{ $item->emailTemplate->name }}</a>
                                                    @endif
                                                @endif
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

@if($total_pages > 1)
    <nav aria-label="Page navigation" style="text-align: center;">
        <ul class="pagination">
            <li class="{{ ($page <= 1 ?  'disabled' : '' ) }}">
                <a class="page-link" href="{{ url('cms/support/contacts/?page=1'.$pagination_link) }}" aria-label="Previous">
                    <span aria-hidden="true"> << </span>
                </a>
            </li>
            <li class="{{ ($page <= 1 ?  'disabled' : '' ) }}">
                <a class="page-link prev" href="{{ url('cms/support/contacts/?page='.($page>1 ? $page-1 : '1').$pagination_link) }}"  aria-label="Previous">
                    <span aria-hidden="true"> < </span>
                </a>
            </li>
            @for($i=$start; $i<=$end; $i++)
                <li class="{{ ($i == $page ?  'active' : '') }}">
                    <a class="page-link" href="{{ url('cms/support/contacts/?page='.$i.$pagination_link) }}">{{ $i }}</a>
                </li>
            @endfor
            <li class="{{ ($page >= $total_pages ? 'disabled' : '') }}">
                <a class="page-link next" href="{{ url('cms/support/contacts/?page='.($page < $total_pages ? $page+1 :  $total_pages).$pagination_link) }}" aria-label="Next"> <span aria-hidden="true"> > </span> </a>
            </li>
            <li class="{{ ($page >= $total_pages ? 'disabled' : '') }}">
                <a class="page-link" href="{{ url('cms/support/contacts/?page='.$total_pages.$pagination_link) }}" aria-label="Next"> <span aria-hidden="true"> >> </span>  </a>
            </li>
        </ul>
    </nav>
@endif

<div id="answerModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Contact answer</h4>
            </div>
            <div class="modal-body">
                <form class="contact-form" action="{{ url('cms/support/contact/') }}" original-action="{{ url('cms/support/contact/') }}" method="post" contact-id="">
                    <p>Select email template:</p>
                    <select name="template-id" class="form-control select2">
                        <option value="">-</option>
                        @foreach(App\Models\EmailTemplate::where('type', 'support')->get() as $template)
                            <option value="{{ $template->id }}">{{ $template->name }}</option>
                        @endforeach
                    </select>

                    <br/>
                    <br/>
                    <p>OR</p>
                    <br/>

                    <p>Write an answer:</p>
                    <textarea class="form-control" name="answer" style="height: 100px;"></textarea>

                    <button type="submit" class="btn btn-primary btn-block" style="margin-top: 20px;">Send</button>
                    <label class="alert alert-danger contact-error" style="display: none;margin-top: 10px;"></label>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>

    </div>
</div>



<style type="text/css">
    .select2-container {
        width: 100% !important;
    }
</style>

@endsection