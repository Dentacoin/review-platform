@extends('admin')

@section('content')

<h1 class="page-header">{{ trans('admin.page.'.$current_page.'.title') }}</h1>
<!-- end page-header -->


<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <div class="panel-heading-btn">
                    <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                </div>
                <h4 class="panel-title"> {{ trans('admin.page.'.$current_page.'.title-filter') }} </h4>
            </div>
            <div class="panel-body">
                <form method="get" action="{{ url('cms/'.$current_page) }}" >
                    <div class="row">
                        <div class="col-md-3">
                            <input type="text" class="form-control datepicker" name="search_from" value="{{ $search_from }}" placeholder="{{ trans('admin.page.'.$current_page.'.title-filter-from') }}">
                        </div>
                        <div class="col-md-3">
                            <input type="text" class="form-control datepicker" name="search_to" value="{{ $search_to }}" placeholder="{{ trans('admin.page.'.$current_page.'.title-filter-to') }}">
                        </div>
                        <div class="col-md-3">
                            <select name="search_group" class="form-control">
                                @foreach($groups as $g)
                                    <option {!! $search_group==$g ? 'selected="selected"' : '' !!} value="{{ $g }}">{{ trans('admin.common.group-'.$g) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="submit" class="btn btn-sm btn-primary btn-block" name="search" value="{{ trans('admin.page.'.$current_page.'.title-filter-submit') }}">
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
                <h4 class="panel-title">{{ trans('admin.page.'.$current_page.'.title') }}</h4>
            </div>
            <div class="panel-body">
        		<div class="panel-body">
                    <table class="table table-striped spending-table">
                        <thead>
                            <tr>
                                <th>{{ trans('admin.common.period') }}</th>
                                @foreach($types as $type)
                                    <th>{{ $type }}</th>
                                @endforeach
                                <th>{{ trans('admin.common.total') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transactions as $period => $row)
                                <tr>
                                    <td>{{ $period }}</td>
                                    @foreach($types as $type)
                                        <td>{{ isset($row[$type]) ? number_format($row[$type], 0, '.', ' ') : '-' }}</td>
                                    @endforeach                 
                                    <td>{{ number_format(array_sum($row), 0, '.', ' ') }}</td>
                                </tr>
                            @endforeach
                            
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>&nbsp;</th>
                                @foreach($types as $type)
                                    <th>{{ isset($totals[$type]) ? number_format($totals[$type], 0, '.', ' ') : '-' }}</th>
                                @endforeach
                                    <th>{{ number_format(array_sum($totals), 0, '.', ' ') }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection