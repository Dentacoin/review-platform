@extends('admin')

@section('content')

	<h1 class="page-header">
		Meetings
	</h1>
	<!-- end page-header -->

	{{ Form::open(array('class' => 'form-horizontal', 'method' => 'post')) }}
		<div class="row">
		    <div class="col-md-12">
		        <div class="panel panel-inverse">
		            <div class="panel-heading">
		                <h4 class="panel-title">Meetings</h4>
		            </div>
		            <div class="tab-content">
		                <table class="table table-striped table-question-list">
		                    <thead>
		                        <tr>
		                            <th>Title</th>
		                            <th>Edit</th>
		                        </tr>
		                    </thead>
		                    <tbody>
		                        @foreach($meetings as $meeting)
		                            <tr>
		                                <td>
		                                    {{ config('meetings')[$meeting->slug] }}
		                                </td>
		                                <td>
		                                    <a class="btn btn-sm btn-success" href="{{ url('cms/meetings/edit/'.$meeting->id) }}">
		                                        <i class="fa fa-pencil"></i>
		                                    </a>
		                                </td>
		                            </tr>
		                        @endforeach
		                    </tbody>
		                </table>
		            </div>
		        </div>
		    </div>
		</div>
	{{ Form::close() }}

	@if($total_pages > 1)
        <nav aria-label="Page navigation" style="text-align: center;">
            <ul class="pagination">
                <li class="{{ ($page <= 1 ?  'disabled' : '' ) }}">
                    <a class="page-link" href="{{ url('cms/vox/paid-reports/?page=1'.$pagination_link) }}" aria-label="Previous">
                        <span aria-hidden="true"> << </span>
                    </a>
                </li>
                <li class="{{ ($page <= 1 ?  'disabled' : '' ) }}">
                    <a class="page-link prev" href="{{ url('cms/vox/paid-reports/?page='.($page>1 ? $page-1 : '1').$pagination_link) }}"  aria-label="Previous">
                        <span aria-hidden="true"> < </span>
                    </a>
                </li>
                @for($i=$start; $i<=$end; $i++)
                    <li class="{{ ($i == $page ?  'active' : '') }}">
                        <a class="page-link" href="{{ url('cms/vox/paid-reports/?page='.$i.$pagination_link) }}">{{ $i }}</a>
                    </li>
                @endfor
                <li class="{{ ($page >= $total_pages ? 'disabled' : '') }}">
                    <a class="page-link next" href="{{ url('cms/vox/paid-reports/?page='.($page < $total_pages ? $page+1 :  $total_pages).$pagination_link) }}" aria-label="Next"> <span aria-hidden="true"> > </span> </a>
                </li>
                <li class="{{ ($page >= $total_pages ? 'disabled' : '') }}">
                    <a class="page-link" href="{{ url('cms/vox/paid-reports/?page='.$total_pages.$pagination_link) }}" aria-label="Next"> <span aria-hidden="true"> >> </span>  </a>
                </li>
            </ul>
        </nav>
    @endif

@endsection