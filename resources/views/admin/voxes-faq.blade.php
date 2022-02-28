@extends('admin')

@section('content')

    <h1 class="page-header">FAQ</h1>
    <!-- end page-header -->
    <div style="margin-bottom: 20px;">
        @foreach($langs as $code => $lang_info)
            <a href="{{ url('cms/trp-faq/'.$code) }}" class="btn btn-success">{{ $lang_info['name'] }}</a>
        @endforeach
    </div>

    <div class="panel-group" id="faq-accordion" role="tablist" aria-multiselectable="true">
        @foreach( $content as $section )
            <div class="panel panel-default main-panel">
                <div class="panel-heading" role="tab" id="">
                    <h4 class="panel-title">
                        <input type="text" name="titles[]" class="form-control section-title" placeholder="Section title" value="{{ $section['title'] }}">
                    </h4>
                    <a class="closer">
                        X
                    </a>
                </div>
                <div id="" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="">
                    <div class="panel-body">
                        <div class="panel-group" role="tablist" aria-multiselectable="true">
                            @if(isset($section['questions']))
                                @foreach($section['questions'] as $question)
                                    <div class="panel panel-default question-panel">
                                        <div class="panel-heading" role="tab" id="">
                                            <h4 class="panel-title">
                                                <input type="text" name="titles[]" class="form-control" placeholder="Question" value="{{ $question[0] }}">
                                            </h4>
                                            <a class="closer">
                                                X
                                            </a>
                                        </div>
                                        <div id="" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="">
                                            <div class="panel-body">
                                                <textarea name="contents[]" class="form-control" style="min-height: 100px;" placeholder="Answer">{{ $question[1] }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                        <a class="btn btn-success btn-block btn-new-faq">
                            Add Question
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row">
        <!-- begin col-6 -->
        <div class="col-md-6">
            <a href="javascript:;" class="btn btn-success btn-block add-faq">
                Add another section
            </a>
        </div>
        <div class="col-md-6">
            <a href="javascript:;" class="btn btn-primary btn-block save-faq">
                Save
            </a>
        </div>
    </div>

    <div id="accordion-template" style="display: none;">
        <div class="panel panel-default main-panel">
            <div class="panel-heading" role="tab" id="">
                <h4 class="panel-title" style="display: flex;">
                    <input type="text" name="titles[]" class="form-control section-title" placeholder="Section title">
                </h4>
                <a class="closer">
                    X
                </a>
            </div>
            <div id="" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="">
                <div class="panel-body">
                    <div class="panel-group" role="tablist" aria-multiselectable="true">
                    </div>
                    <a class="btn btn-success btn-block btn-new-faq">
                        Add Question
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div id="question-template" style="display: none;">
        <div class="panel panel-default question-panel">
            <div class="panel-heading" role="tab" id="">
                <h4 class="panel-title">
                    <input type="text" name="titles[]" class="form-control" placeholder="Question">
                </h4>
                <a class="closer">
                    X
                </a>
            </div>
            <div id="" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="">
                <div class="panel-body">
                    <textarea name="contents[]" class="form-control" style="min-height: 100px;" placeholder="Answer"></textarea>
                </div>
            </div>
        </div>
    </div>

    <style type="text/css">
        html,body {
            height: auto !important;
        }
    </style>

@endsection