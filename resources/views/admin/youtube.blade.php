@extends('admin')

@section('content')


<!-- end page-header -->

<div class="flex" style="justify-content: space-between;">
    <h1 class="page-header">Video Reviews</h1>
    <div>
        <a href="{{ url('cms/trp/youtube/new-token') }}" class="btn btn-primary pull-right">Generate Access Token</a>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <div class="panel-heading-btn">
                    <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                </div>
                <h4 class="panel-title">Video Reviews</h4>
            </div>
            <div class="panel-body">
        		<div class="panel-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Info</th>
                                <th>Video</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pending as $review)
                                <tr>
                                    <td>
                                        {{ $review->created_at->toTimeString() }} {{ $review->created_at->toFormattedDateString() }}<br/>
                                        <a href="{{ url('cms/users/users/edit/'.$review->user->id) }}" target="_blank">
                                            {{ $review->user->getNames() }}
                                        </a>
                                        ->
                                        @if($review->clinic_id)
                                            <a href="{{ url('cms/users/users/edit/'.$review->clinic->id) }}" target="_blank">
                                                {{ $review->clinic->getNames() }}
                                            </a>
                                        @endif
                                        @if($review->dentist_id)
                                            <a href="{{ url('cms/users/users/edit/'.$review->dentist->id) }}" target="_blank">
                                                {{ $review->dentist->getNames() }}
                                            </a>
                                        @endif
                                        <br/>
                                        Rating: {{ $review->rating }}
                                    </td>
                                    <td>
                                        <iframe width="480" height="270" src="https://www.youtube.com/embed/{{ $review->youtube_id }}" frameborder="0" allow="encrypted-media" allowfullscreen></iframe>
                                    </td>
                                    <td>
                                        @if(!$review->youtube_approved)
                                            <a class="btn btn-primary btn-block" href="{{ url('cms/trp/youtube/approve/'.$review->id) }}">
                                                <i class="fa fa-check"></i>
                                                Approve
                                            </a>
                                        @endif
                                        <br/>
                                        <a class="btn btn-success btn-block" href="{{ url('cms/trp/youtube/delete/'.$review->id) }}">
                                            <i class="fa fa-remove"></i>
                                            Delete
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
</div>
@endsection