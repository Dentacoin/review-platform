@extends('admin')

@section('content')

<h1 class="page-header">{{ trans('admin.page.'.$current_page.'.title') }}</h1>
<!-- end page-header -->

<div class="row">
    <!-- begin col-6 -->
    <div class="col-md-12">
        <!-- begin panel -->
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <div class="panel-heading-btn">
                    <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                </div>
                <h4 class="panel-title">{{ trans('admin.page.'.$current_page.'.title') }}</h4>
            </div>
            <div class="panel-body">
                <form class="form-horizontal" id="admin-add" method="post" action="{{ url('cms/'.$current_page) }}">
                	{!! csrf_field() !!}
                    <div class="form-group">
                        <label class="col-md-4">{{ trans('admin.page.'.$current_page.'.type') }}</label>
                        <label class="col-md-4">{{ trans('admin.page.'.$current_page.'.amount') }}</label>
                        <label class="col-md-4">{{ trans('admin.page.'.$current_page.'.dcn') }}</label>
                    </div>
                    @foreach($rewards as $reward)
                        <div class="form-group">
                            <div class="col-md-4">
                                <input class="form-control" type="text" name="none" disabled="disabled" value="{{ trans('admin.page.'.$current_page.'.reward-'.$reward->reward_type) }}" />
                            </div>
                            <div class="col-md-4">
                                <input class="form-control" type="text" name="rewards[{{ $reward->reward_type }}]" value="{{ $reward->amount }}" />
                            </div>
                            <div class="col-md-4">
                                <input class="form-control" type="text" disabled="disabled" value="{{ $reward->dcn }}" />
                            </div>
                        </div>
                    @endforeach
                    @if($admin->role!='support')
                        <div class="form-group">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-sm btn-success btn-block">{{ trans('admin.page.'.$current_page.'.submit') }}</button>
                            </div>
                        </div>
                    @endif
                </form>
            </div>
        </div>
        <!-- end panel -->
    </div>
</div>

@endsection