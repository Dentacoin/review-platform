@extends('admin')

@section('content')

	<h1 class="page-header">
		Daily Polls Monthly Descriptions
		
		<a class="btn btn-primary pull-right" href="{{ url('cms/vox/polls-monthly-description/add') }}">Add new monthly description</a>
	</h1>

	<form method="get" action="{{ url('cms/vox/polls-monthly-description') }}">
        <div class="row custom-row" style="margin-bottom: 10px;">
            <div class="col-md-3">
                <select name="month" class="form-control" style="width: 50%; float: left; text-transform: capitalize;">
                	<option value="">Select month</option>
                    @foreach(config('months') as $m => $month_name)
                        <option value="{{ $m }}" {{ !empty($month) && $month == $m ? 'selected="selected"' : '' }} >{{ $month_name }}</option>
                    @endforeach
                </select>
                <select name="year" class="form-control" style="width: 50%; float: left;">
                	<option value="">Select year</option>
                    @for($i=date('Y')+1;$i>=2019;$i--)
                        <option value="{{ $i }}" {{ !empty($year) && $year == $i ? 'selected="selected"' : '' }}>{{ $i }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-2">
            	<input type="submit" class="btn btn-sm btn-primary btn-block" value="Search">
            </div>
        </div>
    </form>

	{{ Form::open(array('class' => 'form-horizontal', 'method' => 'post')) }}
		<div class="row">
		    <div class="col-md-12">
		        <div class="panel panel-inverse">
		            <div class="panel-heading">
		                <h4 class="panel-title">Daily Polls monthly Descriptions</h4>
		            </div>
		            <div class="tab-content">

		                <table class="table table-striped table-question-list">
		                    <thead>
		                        <tr>
		                        	<th>Date</th>
		                            <th>Description</th>
		                            <th>Edit</th>
		                            <th>Delete</th>
		                        </tr>
		                    </thead>
		                    <tbody>
		                        @foreach($descriptions as $description)
		                            <tr>
		                                <td style="width: 250px;">
		                                    {{ str_pad($description->month, 2, 0, STR_PAD_LEFT) }}.{{ $description->year }}
		                                </td>
		                                <td>
		                                	{{ $description->description }}
		                                </td>
		                                <td style="width: 50px;">
		                                    <a class="btn btn-sm btn-success" href="{{ url('cms/vox/polls-monthly-description/edit/'.$description->id) }}">
		                                        <i class="fa fa-pencil"></i>
		                                    </a>
		                                </td>
		                                <td style="width: 50px;">
		                                    <a class="btn btn-sm btn-success" onclick="return confirm('{{ trans('admin.common.sure') }}')" href="{{ url('cms/vox/polls-monthly-description/delete/'.$description->id) }}">
		                                        <i class="fa fa-remove"></i>
		                                    </a>
		                                </td>
		                            </tr>
		                        @endforeach
		                    </tbody>
		                </table>
		            </div>
		        </div>
    			<a class="btn btn-primary btn-block" href="{{ url('cms/vox/polls-monthly-description/add') }}">Add new monthly description</a>
		    </div>
		</div>
	{{ Form::close() }}

	@if($total_pages > 1)
        <nav aria-label="Page navigation" style="text-align: center;">
            <ul class="pagination">
                <li class="{{ ($page <= 1 ?  'disabled' : '' ) }}">
                    <a class="page-link" href="{{ url('cms/vox/polls-monthly-description/?page=1'.$pagination_link) }}" aria-label="Previous">
                        <span aria-hidden="true"> << </span>
                    </a>
                </li>
                <li class="{{ ($page <= 1 ?  'disabled' : '' ) }}">
                    <a class="page-link prev" href="{{ url('cms/vox/polls-monthly-description/?page='.($page>1 ? $page-1 : '1').$pagination_link) }}"  aria-label="Previous">
                        <span aria-hidden="true"> < </span>
                    </a>
                </li>
                @for($i=$start; $i<=$end; $i++)
                    <li class="{{ ($i == $page ?  'active' : '') }}">
                        <a class="page-link" href="{{ url('cms/vox/polls-monthly-description/?page='.$i.$pagination_link) }}">{{ $i }}</a>
                    </li>
                @endfor
                <li class="{{ ($page >= $total_pages ? 'disabled' : '') }}">
                    <a class="page-link next" href="{{ url('cms/vox/polls-monthly-description/?page='.($page < $total_pages ? $page+1 :  $total_pages).$pagination_link) }}" aria-label="Next"> <span aria-hidden="true"> > </span> </a>
                </li>
                <li class="{{ ($page >= $total_pages ? 'disabled' : '') }}">
                    <a class="page-link" href="{{ url('cms/vox/polls-monthly-description/?page='.$total_pages.$pagination_link) }}" aria-label="Next"> <span aria-hidden="true"> >> </span>  </a>
                </li>
            </ul>
        </nav>
    @endif

@endsection