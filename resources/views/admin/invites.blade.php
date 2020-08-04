@extends('admin')

@section('content')

<div class="flex" style="justify-content: space-between;">
    <h1 class="page-header">
        Invites
    </h1>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <h4 class="panel-title">Invites</h4>
            </div>
            <div class="panel-body" id="link" link="{{ url('cms/update-blog-preferences') }}">
                <div class="row table-responsive-md">

                    <table class="table table-striped table-question-list">
                        <thead>
                            <tr>
                                <th>From</th>
                                <th>To</th>
                                <th>Delete</th>
                            </tr>
                        </thead>
                        <tbody>
                        	@foreach($invites as $invite)
	                            <tr>
	                                <td>
	                                    <a href="{{ url('cms/users/edit/'.$invite->user_id) }}"> {{ !empty($invite->user) ? $invite->user->name : 'unknown' }}</a>
	                                </td>
	                                <td>
	                                    {!! $invite->invited_email !!}
	                                </td>
                                    <td>
                                        <a class="btn btn-sm btn-deafult" href="{{ url('cms/invites/delete/'.$invite->id) }}" onclick="return confirm('Are you sure you want to DELETE this?');">{{ trans('admin.table.delete') }}</a>
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

@endsection