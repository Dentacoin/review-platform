@extends('admin')

@section('content')

    <div class="flex" style="justify-content: space-between;">
        <h1 class="page-header">Review Questions</h1>
        <div>
            <a href="{{ url('cms/trp/'.$current_subpage.'/add') }}" class="btn btn-success pull-right">Add new question</a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <div class="panel-heading-btn">
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                    </div>
                    <h4 class="panel-title">Review Questions</h4>
                </div>
                <div class="panel-body">
                    <div class="row table-responsive-md">
                        <table class="table table-striped table-question-list">
                            <thead>
                                <tr>
                                    @if($admin->id == 1)
                                        <th>ID</th>
                                    @endif
                                    <th>Order</th>
                                    <th>Type</th>
                                    <th>Question</th>
                                    <th>Edit</th>
                                </tr>
                            </thead>
                            <tbody class="questions-draggable" url="{{ url('cms/trp/questions/reorder') }}">
                                @foreach($questions as $question)
                                    <tr question-order="{{ $question->order }}" question-id="{{ $question->id }}" >
                                        
                                        @if($admin->id == 1)
                                            <td>
                                                {{ $question->id }}
                                            </td>
                                        @endif
                                        <td class="question-number">
                                            {{ $question->order }}
                                        </td>
                                        <td>
                                            {{ $question->type }}
                                        </td>
                                        <td>
                                            {{ $question->label }}
                                        </td>
                                        <td>
                                            <a class="btn btn-sm btn-primary" href="{{ url('cms/trp/questions/edit/'.$question->id) }}">
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
    </div>
@endsection