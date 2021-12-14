@extends('admin')

@section('content')

    <div class="flex" style="justify-content: space-between;">
        <h1 class="page-header">
            Admin History Actions
        </h1>
    </div>


    {{-- ///check admin roles visibility
    ///check admin roles visibility
    ///check admin roles visibility
    ///check admin roles visibility
    ///check admin roles visibility
    ///check admin roles visibility
    ///check admin roles visibility
    ///check admin roles visibility
    ///check admin roles visibility
    ///check admin roles visibility
    ///check admin roles visibility
    ///check admin roles visibility
    ///check admin roles visibility
    ///check admin roles visibility
    ///check admin roles visibility
    ///check admin roles visibility
    ///check admin roles visibility
    ///check admin roles visibility
    ///check admin roles visibility
    ///check admin roles visibility
    ///check admin roles visibility
    ///check admin roles visibility
    ///check admin roles visibility --}}

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <div class="panel-heading-btn">
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                    </div>
                    <h4 class="panel-title"> Admin History Filter</h4>
                </div>
                <div class="panel-body users-filters">
                    <form method="get" action="{{ url('cms/admins/actions-history/') }}">
                        <div class="row" style="margin-bottom: 10px;">
                            <div class="col-md-2">
                                <select name="search-admin-id" class="form-control">
                                    <option value="">Search admin id</option>
                                    @foreach($admins as $admin)
                                        <option value="{{ $admin->id }}" {!! $search_admin_id == $admin->id ? 'selected="selected"' : '' !!}>{{ $admin->name ?? $admin->username }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="text" class="form-control" name="search-admin-id" value="{{ $search_admin_id }}" placeholder="Search admin ID">
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

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <h4 class="panel-title">Admin History Actions</h4>
                </div>
                <div class="panel-body">
                    <div class="row table-responsive-md">
                        <table class="table table-striped table-question-list">
                            <thead>
                                <tr>
                                    <th style="width: 20%">Date</th>
                                    <th style="width: 20%">Admin</th>
                                    <th style="width: 20%">Action</th>
                                    <th style="width: 20%">Vox ID</th>
                                    <th style="width: 20%">Question ID</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($history as $h)
                                    <tr>
                                        <td style="width: 20%">
                                            {{ date('d.m.Y, H:i:s', $h->created_at->timestamp) }}
                                        </td>
                                        <td style="width: 20%">
                                            <a href="{{ url('cms/admins/admins/edit/'.$h->admin_id) }}">{{ $h->admin->name }}</a>
                                        </td>
                                        <td style="width: 20%">
                                            {!! $h->info !!}
                                        </td>
                                        <td style="width: 20%">
                                            {!! $h->vox ? '<a href="'.url('cms/vox/edit/'.$h->vox_id).'">'.$h->vox->title.'</a>' : '' !!}
                                        </td>
                                        <td style="width: 20%">
                                            {{ $h->question ? '<a href="'.url('cms/vox/edit/'.$h->vox_id.'/question/'.$h->question_id).'">'.$h->question_id->question_id.'</a>' : '' }}
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

    <div style="text-align: center;"> 
        {{ $history->render() }}
    </div>

@endsection