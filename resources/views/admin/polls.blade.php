@extends('admin')

@section('content')

<h1 class="page-header">
    Daily Polls
    
    <a class="btn btn-primary pull-right" href="{{ url('cms/vox/polls/add') }}">Add new daily poll</a>
</h1>
<!-- end page-header -->

	<form method="get" action="{{ url('cms/vox/polls') }}">
        <div class="row custom-row" style="margin-bottom: 10px;">
            <div class="col-md-3">
                <input type="text" class="form-control polldatepicker" name="search-polls-from" value="{{ $search_polls_from }}" placeholder="Polls from date" autocomplete="off">
            </div>
            <div class="col-md-3">
                <input type="text" class="form-control polldatepicker" name="search-polls-to" value="{{ $search_polls_to }}" placeholder="Polls to date" autocomplete="off">
            </div>
            <div class="col-md-2">
            	<input type="submit" class="btn btn-sm btn-primary btn-block" name="search" value="Search">
            </div>
        </div>
    </form>

	{{ Form::open(array('id' => 'polls-actions', 'class' => 'form-horizontal', 'method' => 'post')) }}
		<div class="row">
		    <div class="col-md-12">
		        <div class="panel panel-inverse">
		            <div class="panel-heading">
		                <h4 class="panel-title">Edit Daily Polls</h4>
		            </div>
		            <div class="tab-content">

		                <table class="table table-striped table-question-list">
		                    <thead>
		                        <tr>
		                        	<th>
			                        	@if( !request()->input('date') )
	                                       <a href="{{ url('cms/vox/polls/?date=asc') }}" class="order">Calendar date</a>
	                                    @elseif( request()->input('date')=='desc' )
	                                        <a href="{{ url('cms/vox/polls/?date=asc') }}" class="order">Calendar date</a>
	                                    @else
	                                        <a href="{{ url('cms/vox/polls/?date=desc') }}" class="order">Calendar date</a>
	                                    @endif
	                                </th>
		                            <th>Poll/Question</th>
		                            <th>Status</th>
		                            <th>Category</th>
		                            <th>Respondents</th>
		                            <th>Duplicate</th>
		                            <th>Edit</th>
		                            <th>Delete</th>
		                        </tr>
		                    </thead>
		                    <tbody class="polls-draggable">
		                        @foreach($polls as $poll)
		                            <tr poll-id="{{ $poll->id }}">
		                                <td style="width: 150px;">
		                                    <input type="text" class="form-control poll-date polldatepicker" data-qid="{{ $poll->id }}" name="launched_at" value="{{ $poll->launched_at ? date('Y-m-d', $poll->launched_at->timestamp ) : null }}" autocomplete="off" />
		                                </td>
		                                <td>
		                                    <textarea style="min-width: 360px;" class="form-control poll-question" data-qid="{{ $poll->id }}">{{ $poll->question }}</textarea>
		                                </td>
		                                <td>
		                                    {!! $statuses[$poll->status] !!}
		                                </td>	
		                                <td>
		                                    {!! App\Models\VoxCategory::find($poll->category)->name !!}
		                                </td>
                                        <td>
                                            <a href="{{ url('cms/vox/polls-explorer/'.$poll->id) }}" target="_blank">
                                                {!! $poll->respondentsCount() !!}
                                            </a>
                                        </td>
		                                <td>
		                                    <a class="btn btn-sm btn-success duplicate-poll-button" href="{{ url('cms/vox/polls/duplicate/'.$poll->id) }}">
		                                        <i class="fa fa-paste"></i>
		                                    </a>
		                                </td>
		                                <td>
		                                    <a class="btn btn-sm btn-success" href="{{ url('cms/vox/polls/edit/'.$poll->id) }}">
		                                        <i class="fa fa-pencil"></i>
		                                    </a>
		                                </td>
		                                <td>
		                                    <a class="btn btn-sm btn-success" onclick="return confirm('{{ trans('admin.common.sure') }}')" href="{{ url('cms/vox/polls/delete/'.$poll->id) }}">
		                                        <i class="fa fa-remove"></i>
		                                    </a>
		                                </td>
		                            </tr>
		                        @endforeach
		                    </tbody>
		                </table>
		            </div>
		        </div>
		        <a class="btn btn-primary btn-block" href="{{ url('cms/vox/polls/add') }}">
		            Add New Daily Poll
		        </a>
		    </div>
		</div>
	{{ Form::close() }}

	@if($total_pages > 1)
        <nav aria-label="Page navigation" style="text-align: center;">
            <ul class="pagination">
                <li class="{{ ($page <= 1 ?  'disabled' : '' ) }}">
                    <a class="page-link" href="{{ url('cms/vox/polls/?page=1'.$pagination_link) }}" aria-label="Previous">
                        <span aria-hidden="true"> << </span>
                    </a>
                </li>
                <li class="{{ ($page <= 1 ?  'disabled' : '' ) }}">
                    <a class="page-link prev" href="{{ url('cms/vox/polls/?page='.($page>1 ? $page-1 : '1').$pagination_link) }}"  aria-label="Previous">
                        <span aria-hidden="true"> < </span>
                    </a>
                </li>
                @for($i=$start; $i<=$end; $i++)
                    <li class="{{ ($i == $page ?  'active' : '') }}">
                        <a class="page-link" href="{{ url('cms/vox/polls/?page='.$i.$pagination_link) }}">{{ $i }}</a>
                    </li>
                @endfor
                <li class="{{ ($page >= $total_pages ? 'disabled' : '') }}">
                    <a class="page-link next" href="{{ url('cms/vox/polls/?page='.($page < $total_pages ? $page+1 :  $total_pages).$pagination_link) }}" aria-label="Next"> <span aria-hidden="true"> > </span> </a>
                </li>
                <li class="{{ ($page >= $total_pages ? 'disabled' : '') }}">
                    <a class="page-link" href="{{ url('cms/vox/polls/?page='.$total_pages.$pagination_link) }}" aria-label="Next"> <span aria-hidden="true"> >> </span>  </a>
                </li>
            </ul>
        </nav>
    @endif

@endsection