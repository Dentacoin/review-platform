@extends('admin')

@section('content')

<h1 class="page-header">
    {{ trans('admin.page.'.$current_page.'.title') }}

    @if($stopped_validations)
    	<a href="{{ url('cms/email_validations/email_validations/start') }}" class="btn btn-success pull-right" style="margin-right: 10px;">Start email validation</a>
    @else
    	<a href="{{ url('cms/email_validations/email_validations/stop') }}" class="btn btn-danger pull-right" style="margin-right: 10px;">Stop email validation</a>
    @endif
</h1>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <div class="panel-heading-btn">
                    <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                </div>
                <h4 class="panel-title"> Search Email Validations </h4>
            </div>
            <div class="panel-body users-filters">
                <form method="get" action="{{ url('cms/email_validations/email_validations') }}">
                    <div class="row custom-row" style="margin-bottom: 10px;">
                    <div class="row" style="margin-bottom: 10px;">  
                        <div class="col-md-2">
                            <input type="text" class="form-control" name="search-email" value="{{ $search_email }}" placeholder="Email">
                        </div>                      
                        <div class="col-md-2">
                            <select class="form-control" id="search-valid" name="search-valid">
                                <option value="all" {!! 'all'==$search_valid ? 'selected="selected"' : '' !!}>All</option>
                                <option value="valid" {!! 'valid'==$search_valid ? 'selected="selected"' : '' !!}>Valid</option>
                                <option value="invalid" {!! 'invalid'==$search_valid ? 'selected="selected"' : '' !!}>Risky/Invalid</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="submit" class="btn btn-sm btn-primary btn-block" name="search" value="Search">
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- end page-header -->
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <div class="panel-heading-btn">
                    <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                </div>
                <h4 class="panel-title">{{ trans('admin.page.'.$current_page.'.title') }}</h4>
            </div>
            <div class="panel-body">
        		<div class="panel-body">
					<table class="table table-striped table-question-list">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Email</th>
                                <th>Score</th>
                                <th>Is valid?</th>
                                <th>Update</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($validations as $validation)
                                <tr>
                                    <td>
                                        {{ $validation->created_at ? $validation->created_at->toDateTimeString() : '' }}
                                    </td>
                                    <td>
                                        {{ $validation->email }}
                                    </td>
                                    <td>
                                        {{ isset(json_decode($validation->meta, true)['result']) ? (json_decode($validation->meta, true)['result']['score'].' ('.json_decode($validation->meta, true)['result']['verdict'].')') : '' }}
                                    </td>
                                    <td>
                                        {!! $validation->valid ? '<span class="label label-success">'.trans('admin.common.yes').'</span>' : '<span class="label label-warning">'.trans('admin.common.no').'</span>' !!}
                                    </td>
                                    <td>
                                    	@if(!$validation->valid)
	                                        <a class="btn btn-sm btn-primary" href="{{ url('cms/email_validations/email_validations/valid/'.$validation->id) }}">
											    Мark as valid
											</a>
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



@if($total_pages > 1)
    <nav aria-label="Page navigation" style="text-align: center;">
        <ul class="pagination">
            <li class="{{ ($page <= 1 ?  'disabled' : '' ) }}">
                <a class="page-link" href="{{ url('cms/email_validations/email_validations/?page=1'.$pagination_link) }}" aria-label="Previous">
                    <span aria-hidden="true"> << </span>
                </a>
            </li>
            <li class="{{ ($page <= 1 ?  'disabled' : '' ) }}">
                <a class="page-link prev" href="{{ url('cms/email_validations/email_validations/?page='.($page>1 ? $page-1 : '1').$pagination_link) }}"  aria-label="Previous">
                    <span aria-hidden="true"> < </span>
                </a>
            </li>
            @for($i=$start; $i<=$end; $i++)
                <li class="{{ ($i == $page ?  'active' : '') }}">
                    <a class="page-link" href="{{ url('cms/email_validations/email_validations/?page='.$i.$pagination_link) }}">{{ $i }}</a>
                </li>
            @endfor
            <li class="{{ ($page >= $total_pages ? 'disabled' : '') }}">
                <a class="page-link next" href="{{ url('cms/email_validations/email_validations/?page='.($page < $total_pages ? $page+1 :  $total_pages).$pagination_link) }}" aria-label="Next"> <span aria-hidden="true"> > </span> </a>
            </li>
            <li class="{{ ($page >= $total_pages ? 'disabled' : '') }}">
                <a class="page-link" href="{{ url('cms/email_validations/email_validations/?page='.$total_pages.$pagination_link) }}" aria-label="Next"> <span aria-hidden="true"> >> </span>  </a>
            </li>
        </ul>
    </nav>
@endif

@endsection
