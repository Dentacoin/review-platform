<div class="form-group clearfix">
    <div class="col-md-12">
        <a href="{{ url('cms/vox/') }}">Surveys</a> &raquo;
        <a href="{{ url('cms/vox/edit/'.$item->id) }}"> {{ $item->title }}</a>  
        @if(!empty($question))
        &raquo;
        {{ $question->question }}
        @endif
    </div>
</div>


<div class="row">
    <div class="col-md-12">

        {{ Form::open(array('id' => 'question-'.( !empty($question) ? 'edit' : 'add') , 'url' => url('cms/'.$current_page.'/edit/'.$item->id.'/question/'.( !empty($question) ? $question->id : 'add') ), 'class' => 'form-horizontal questions-form', 'method' => 'post')) }}


            <div class="panel panel-inverse panel-with-tabs custom-tabs">
                <div class="panel-heading p-0">
                    <!-- begin nav-tabs -->
                    <div class="tab-overflow overflow-right">
                        <ul class="nav nav-tabs nav-tabs-inverse">
                            @foreach($langs as $code => $lang_info)
                                <li class="{{ $loop->first ? 'active' : '' }}">
                                    <a href="javascript:;" lang="{{ $code }}" data-toggle="tab" aria-expanded="false">{{ $lang_info['name'] }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <div class="tab-content">
                    <div class="form-group clearfix">
                        <h3 class="col-md-1" style="margin-top: 0px;">{{ trans('admin.page.'.$current_page.'.question-question') }}</h3>
                        <label class="col-md-1 control-label">{{ trans('admin.page.'.$current_page.'.question-order') }}</label>
                        <div class="col-md-5" style="display: flex;"> 
                            {{ Form::text('order', !empty($question) ? $question->order : (!empty($next) ? $next : ''), array('maxlength' => 256, 'class' => 'form-control input-title', 'style' => 'width: 50px;' )) }}
                            @foreach($langs as $code => $lang_info)
                                <div class="tab-pane fade{{ $loop->first ? ' active in' : '' }} lang-{{ $code  }} " style="flex: 1;">
                                    {{ Form::textarea('question-'.$code, !empty($question) ? $question->{'question:'.$code} : '', array('maxlength' => 2048, 'class' => 'form-control input-title', 'style' => 'max-height: 34px;')) }}
                                </div>
                            @endforeach
                        </div>
                        <div class="col-md-5">
                            How QUESTION tooltips work: <br/>
                            Do you [includes cigars, e-cigarettes and any other tobacco products]smoke cigarettes[/]?
                        </div>
                    </div>
                    <div class="form-group clearfix">
                        <label class="col-md-2 control-label">{{ trans('admin.page.'.$current_page.'.question-type') }}</label>
                        <div class="col-md-2">
                            {{ Form::select('type', $question_types, !empty($question) ? $question->type : null, array('class' => 'form-control question-type-input')) }}
                        </div>
                        <label class="col-md-1 control-label">{{ trans('admin.page.'.$current_page.'.question-scale') }}</label>
                        <div class="col-md-2">
                            {{ Form::select('question_scale', ['' => '-'] + $scales, !empty($question) ? $question->vox_scale_id : '', array('class' => 'form-control question-scale-input')) }}
                        </div>
                        <div class="col-md-5">
                            {!! nl2br(trans('admin.page.'.$current_page.'.question-scale-hint')) !!}
                        </div>
                    </div>
                    <div class="form-group clearfix">
                        <label class="col-md-2 control-label" for="dont_randomize_answers">Don’t randomize</label>
                        <div class="col-md-1">
                            <input type="checkbox" name="dont_randomize_answers" class="form-control" value="1" id="dont_randomize_answers" style="vertical-align: sub;width: 30px;" {!! !empty($question) && !empty($question->dont_randomize_answers) ? 'checked="checked"' : '' !!} />
                        </div>                        
                    </div>

                    @foreach($langs as $code => $lang_info)
                        <div class="tab-pane questions-pane fade{{ $loop->first ? ' active in' : '' }} lang-{{ $code  }}" lang="{{ $code }}">
                            <div class="form-group answers-group">
                                <h3 class="col-md-2" style="margin-top: 0px;">{{ trans('admin.page.'.$current_page.'.question-answers') }}</h3>
                                <div class="col-md-10 answers-list answers-draggable">
                                    @if(!empty($question) && !empty($question->{'answers:'.$code}) )
                                        @foreach(json_decode($question->{'answers:'.$code}, true) as $key => $ans)
                                            <div class="flex input-group">
                                                <div class="col col-60">
                                                    {{ $question_answers_count[$key+1] ?? '' }}
                                                </div>
                                                <div class="col">
                                                    {{ Form::text('answers-'.$code.'[]', $ans, array('maxlength' => 2048, 'class' => 'form-control', 'placeholder' => 'Answer or name of the scale:weak,medium,strong', 'style' => 'display: inline-block; width: calc(100% - 60px);')) }}
                                                    
                                                    <div class="input-group-btn" style="display: inline-block;">
                                                        <button class="btn btn-default btn-remove-answer" type="button">
                                                            <i class="glyphicon glyphicon-remove"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="input-group">
                                            {{ Form::text('answers-'.$code.'[]', '', array('maxlength' => 2048, 'class' => 'form-control', 'placeholder' => 'Answer or name of the scale:weak,medium,strong')) }}
                                            <div class="input-group-btn">
                                                <button class="btn btn-default btn-remove-answer" type="button">
                                                    <i class="glyphicon glyphicon-remove"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group answers-group-add">
                                <label class="col-md-2 control-label"></label>
                                <div class="col-md-10">
                                    <p class="answers-error" style="font-size: 20px;color: #ff8d69;display: none;">The recommended number of answers is up to 10</p>
                                    {{ trans('admin.page.'.$current_page.'.answers-add-hint') }}<br/>
                                    * Use "#" before an answer to disable randomizing.<br/><br/>

                                    How ANSWER tooltips work: <br/>
                                    Do you [includes cigars, e-cigarettes and any other tobacco products]smoke cigarettes[/]?<br/>
                                    <br/>
                                    <a href="javascript:;" class="btn btn-success btn-block btn-add-answer">{{ trans('admin.page.'.$current_page.'.answers-add') }}</a>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <div class="form-group clearfix">
                        <h3 class="col-md-1" style="margin-top: 0px;">Settings</h3>
                        @if(empty($question) || empty($question->question_trigger) )
                            <div class="form-group clearfix col-md-11">
                                <label class="col-md-1 control-label">Triggers</label>
                                <div class="col-md-11">
                                    <a class="btn btn-primary show-trigger-controls" href="javascript:;" style="margin-left: 15px;">
                                        Show Trigger Controls
                                    </a>
                                </div>
                            </div>
                        @endif

                        <div id="trigger-widgets" {!! empty($question) || empty($question->question_trigger) ? 'style="display: none;"' : '' !!} class="col-md-11" >
                            <div class="form-group clearfix">
                                <label class="col-md-1 control-label">{{ trans('admin.page.'.$current_page.'.question-trigger') }}</label>
                                <div class="col-md-11 triggers-list">
                                    @if(!empty($question) && !empty($question->question_trigger) )
                                        @foreach(explode(';',$question->question_trigger) as $trigger)
                                            @if( $trigger==-1 )
                                                <div class="input-group same-as-before" id="trigger-group-template" style="display: block;">
                                                    <div class="template-box clearfix" style="line-height: 34px; font-size: 18px;"> 
                                                        This question will have the same trigger as the one before it
                                                        <input type="hidden" name="triggers[]" value="-1">
                                                    </div>
                                                    <div class="input-group-btn">
                                                        <button class="btn btn-default btn-remove-trigger" type="button">
                                                            <i class="glyphicon glyphicon-remove"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="input-group clearfix">
                                                    <div class="template-box clearfix">
                                                        <select name="triggers[]" class="form-control select2" style="width: 50%; float: left;">
                                                            <!-- <option value="">Select question</option>                                                            
                                                            <optgroup label="Demographic questions">
                                                                @foreach(config('vox.details_fields') as $kdf => $dq)
                                                                    <option value="{{ $kdf }}" {{ !empty($question) && explode(':', $trigger)[0] == $kdf ? 'selected="selected"' : '' }}>{{ $dq['label'] }}</option>
                                                                @endforeach
                                                                <option value="age_groups" {{ !empty($question) && explode(':', $trigger)[0] == 'age_groups' ? 'selected="selected"' : '' }}>Age Group</option>
                                                            </optgroup> -->
                                                            <optgroup label="Welcome survey questions">
                                                                @foreach(App\Models\Vox::find(11)->questions as $wq)
                                                                    <option value="{{ $wq->id }}" {{ !empty($question) && explode(':', $trigger)[0] == $wq->id ? 'selected="selected"' : '' }}>{{ $wq->question }}</option>
                                                                @endforeach
                                                            </optgroup>
                                                            <optgroup label="{{ $item->title }} survey questions">
                                                                @foreach($item->questions as $iq)
                                                                    <option value="{{ $iq->id }}" {{ !empty($question) && explode(':', $trigger)[0] == $iq->id ? 'selected="selected"' : '' }}>{{ $iq->question }}</option>
                                                                @endforeach
                                                            </optgroup>
                                                        </select>
                                                        {{ Form::text('answers-number[]', !empty(explode(':', $trigger)[1]) ? explode(':', $trigger)[1] : null, array('maxlength' => 256, 'class' => 'form-control', 'style' => 'width: 50%; float: left;', 'placeholder' => 'Answer numbers')) }}
                                                    </div>
                                                    <div class="input-group-btn">
                                                        <button class="btn btn-default btn-remove-trigger" type="button">
                                                            <i class="glyphicon glyphicon-remove"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                            <div class="form-group clearfix">
                                <label class="col-md-1">
                                </label>
                                <div class="col-md-11">
                                    To enable a trigger, first select the question from the dropdown and then type the number of the answer(s) that will trigger the present question.<br/>
                                    Example: "Question text?" / One trigger answer: 1 (for the first answer), 2 (for the second answer, etc.); 2+ answers: 1, 2, 3, 4<br/>
                                    <!-- To enable a previously selected trigger, click Add previous trigger. -->
                                    <span style="display: none;" class="show-me">To enable another trigger, click <a href="javascript:;" id="close-and-add-trigger">here</a>.</span>
                                </div>
                            </div>
                            <div class="form-group clearfix">
                                <label class="col-md-1">
                                </label>
                                <div class="col-md-11">
                                    <a href="javascript:;" class="btn btn-white btn-block btn-add-new-trigger" style="margin-top: 10px;{!! !empty($question) && $question->question_trigger=='-1' ? 'display: none;' : '' !!}" >
                                        Аdd another trigger
                                    </a>
                                    <a href="javascript:;" class="btn btn-white btn-block btn-add-old-trigger" style="margin-top: 10px;{!! !empty($question) && $question->question_trigger=='-1' ? 'display: none;' : '' !!}" >
                                        Copy from previous question
                                    </a>
                                    <a href="javascript:;" class="btn btn-success btn-block btn-add-trigger" style="margin-top: 10px;{!! !empty($question) && $question->question_trigger=='-1' ? 'display: none;' : '' !!}" >
                                        <!-- {{ trans('admin.page.'.$current_page.'.trigger-add') }} -->
                                        Same as previous
                                    </a>
                                </div>
                            </div>
                            <div class="form-group clearfix">
                                <label class="col-md-1 control-label">
                                    Trigger Logic
                                </label>
                                <div class="col-md-11">
                                    <label for="trigger-type-yes" style="display: block;">
                                        <input type="radio" id="trigger-type-yes" name="trigger_type" value="or" {!! empty($question) || $question->trigger_type=='or' ? 'checked="checked"' : '' !!} />
                                        ANY of the conditions should be met (A or B or C)
                                    </label>
                                    <label for="trigger-type-no" style="display: block;">
                                        <input type="radio" id="trigger-type-no" name="trigger_type" value="and" {!! !empty($question) && $question->trigger_type=='and' ? 'checked="checked"' : '' !!} />
                                        ALL the conditions should be met (A and B and C)
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-2 control-label">{{ trans('admin.page.'.$current_page.'.question-control') }}</label>
                        <div class="col-md-5">
                            {{ Form::text('is_control', !empty($question) && ($question->is_control != '-1') ? $question->is_control : '', array('maxlength' => 256, 'class' => 'form-control input-title')) }}

                            <label for="is_control_prev">
                                <input type="checkbox" name="is_control_prev" value="-1" id="is_control_prev" style="vertical-align: sub;" {!! !empty($question->is_control) && ($question->is_control == '-1') ? 'checked="checked"' : '' !!} />
                                Same as previous question
                            </label>
                        </div>

                        <div class="col-md-4">
                            {{ trans('admin.page.'.$current_page.'.question-control-hint') }}
                        </div>
                    </div>


                    <div class="form-group clearfix">
                        <h3 class="col-md-1" style="margin-top: 0px;">Stats</h3>
                        <label class="col-md-1 control-label">Show in Stats</label>
                        <div class="col-md-2">
                            {{ Form::select('used_for_stats', $stat_types, !empty($question) ? $question->used_for_stats : old('used_for_stats'), array('class' => 'form-control question-stats-input')) }}
                        </div>
                        <div class="stat_title col-md-8">
                            <div class="form-group clearfix">
                                @foreach($langs as $code => $lang_info)
                                    <div class="tab-pane fade{{ $loop->first ? ' active in' : '' }} lang-{{ $code  }} col-md-12">
                                        {{ Form::text('stats_title-'.$code, !empty($question) ? $question->translateorNew($code)->stats_title : old('stats_title-'.$code), array('maxlength' => 256, 'class' => 'form-control input-title', 'placeholder' => 'Statistics title in '.$lang_info['name'])) }}
                                    </div>
                                @endforeach
                            </div>
                            <div class="form-group clearfix">
                                @foreach($langs as $code => $lang_info)
                                    <div class="tab-pane fade{{ $loop->first ? ' active in' : '' }} lang-{{ $code  }} col-md-12">
                                        {{ Form::textarea('stats_subtitle-'.$code, !empty($question) ? $question->translateorNew($code)->stats_subtitle : old('stats_subtitle-'.$code), array('maxlength' => 2048, 'class' => 'form-control input-title', 'style' => 'max-height: 100px;', 'placeholder' => 'Statistics subtitle in '.$lang_info['name'])) }}
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="stat_title">
                        <div class="form-group clearfix">
                            <label class="col-md-2 control-label">First in stats</label>
                            <div class="col-md-10">
                                <label for="stats_featured">
                                    <input type="checkbox" name="stats_featured" value="1" id="stats_featured" style="vertical-align: sub;" {!! !empty($question) && $question->stats_featured ? 'checked="checked"' : old('stats_featured') !!} />
                                    Show this question in the Survey, on the main stats page
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    @if(!empty($question) && $question->type == 'multiple_choice')
                        <div class="stat_title">
                            <div class="form-group clearfix">
                                <label class="col-md-2 control-label">
                                    Show Only Top Answers 
                                    <!-- <br/>(multiple choice only) -->
                                </label>
                                <div class="col-md-2">
                                    {{ Form::select('stats_top_answers', $stat_top_answers, !empty($question) ? $question->stats_top_answers : old('stats_top_answers'), array('class' => 'form-control')) }}
                                </div>
                                @if(!empty($question->{'answers:en'}))
                                    <div class="col-md-3" style="margin-top: 8px;margin-left: -18px;color: #348fe2;">
                                        Current answers count: {{ count(json_decode($question->{'answers:en'}, true)) }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                    <div class="form-group clearfix" id="stat_standard">
                        <label class="col-md-2 control-label">Demographics</label>
                        <div class="col-md-10">
                            @foreach( config('vox.stats_scales') as $k => $v)
                                <label for="stats_fields-{{ $k }}">
                                    <input type="checkbox" name="stats_fields[]" value="{{ $k }}" id="stats_fields-{{ $k }}" style="vertical-align: sub;" {!! !empty($question) && in_array($k, $question->stats_fields) ? 'checked="checked"' : '' !!} />
                                    {{ trans('vox.page.stats.group-by-'.$k) }} &nbsp;&nbsp;&nbsp;&nbsp;
                                </label>
                            @endforeach
                        </div>
                    </div>

                    @if(!empty($question) && !empty($question->vox_scale_id) && !empty($question->{'answers:en'}) )
                        <div class="form-group clearfix" id="stats_scale_answers">
                            <label class="col-md-2 control-label">Show in stats scale answers</label>
                            <div class="col-md-10">
                                @foreach(json_decode($question->{'answers:en'}, true) as $key => $ans)
                                    <label for="stats-scale-answers-{{ $key + 1 }}">
                                        <input type="checkbox" name="stats_scale_answers[]" value="{{ $key + 1 }}" id="stats-scale-answers-{{ $key + 1 }}" style="vertical-align: sub;" {!! !empty($question->stats_scale_answers) && in_array(($key+1), json_decode($question->stats_scale_answers, true)) ? 'checked="checked"' : '' !!} />
                                        {{ $question->removeAnswerTooltip($ans) }} &nbsp;&nbsp;&nbsp;&nbsp;
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="form-group clearfix" id="stat_relations">
                        <label class="col-md-2 control-label">Related question</label>
                        <div class="col-md-5">
                            <select name="stats_relation_id" class="form-control">
                                <optgroup label="Welcome survey questions">
                                    @foreach(App\Models\Vox::find(11)->questions as $wq)
                                        <option value="{{ $wq->id }}" {{ !empty($question) && $question->used_for_stats=='dependency' && $question->stats_relation_id == $wq->id ? 'selected="selected"' : '' }}>{{ $wq->question }}</option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="{{ $item->title }} survey questions">
                                    @foreach($item->questions as $iq)
                                        <option value="{{ $iq->id }}" {{ !empty($question) && $question->used_for_stats=='dependency' && $question->stats_relation_id == $iq->id ? 'selected="selected"' : '' }}>{{ $iq->question }}</option>
                                    @endforeach
                                </optgroup>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <input type="text" name="stats_answer_id" class="form-control" value="{!! !empty($question) && $question->used_for_stats=='dependency' ? $question->stats_answer_id : old('stats_answer_id') !!}" placeholder="Select answer number">
                        </div>
                    </div>

                    <div class="form-group clearfix" style="margin-top: 40px;">
                        <h3 class="col-md-12" style="margin-top: 0px;">Cross-checks</h3>
                    </div>

                    <div class="form-group clearfix">
                        <label class="col-md-2 control-label">Welcome questions</label>
                        <div class="col-md-4">
                            {{ Form::select('cross_check', ['' => '-'] + App\Models\Vox::getDemographicQuestions(), !empty($question) ? $question->cross_check : '', array('class' => 'form-control question-scale-input select2', 'style' => 'width: 100%', 'id' => 'select-cross')) }}
                        </div>
                    </div>

                    <div class="clearfix">
                        <div class="col-md-2"></div>
                        <div class="col-md-6" style="margin-left: -170px;">
                            <div id="habits-table" {!! !empty($question->cross_check) ? '' : 'style="display:none;"' !!}>
                                <p style="padding-left: 17px;">
                                    Please make sure your answers match the selected Cross-check question's answers
                                </p>
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Cross-checks answers</th>
                                            <th>Current answers</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach(App\Models\Vox::getDemographicAnswers() as $ke => $va)
                                            @foreach($va as $ans)
                                                <tr class="q-id id-{{ $ke }}" {!! !empty($question->cross_check) && $question->cross_check == $ke ? '' : 'style="display:none;"' !!}>
                                                    <td>{{ $ans }}</td>
                                                    <td></td>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                    </tbody>

                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="form-group" style="margin-top: 60px;">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-block btn-success">{{ trans('admin.page.'.$current_page.'.question-add') }}</button>
                        </div>
                    </div>

                </div>
            </div>

        {{ Form::close() }}
    </div>
</div>

<div style="display: none;">
    <div class="flex input-group ui-sortable-handle" id="input-group-template">
        <div class="col col-60">

        </div>
        <div class="col">
            {{ Form::text('something', '', array('maxlength' => 2048, 'class' => 'form-control answer-name', 'placeholder' => 'Answer or name of the scale:weak,medium,strong', 'style' => 'display: inline-block; width: calc(100% - 60px);')) }}
            <div class="input-group-btn" style="display: inline-block;">
                <button class="btn btn-default btn-remove-answer" type="button">
                    <i class="glyphicon glyphicon-remove"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<div style="display: none;">
    <div class="input-group same-as-before" id="trigger-group-template" >
        <div class="template-box clearfix" style="line-height: 34px; font-size: 18px;"> 
            This question will have the same trigger as the one before it
            <input type="hidden" name="triggers[]" value="-1">
        </div>
        <div class="input-group-btn">
            <button class="btn btn-default btn-remove-trigger button-close-trigger" type="button">
                <i class="glyphicon glyphicon-remove"></i>
            </button>
        </div>
    </div>

     <div id="old-trigger-group-template" >
        @if(!empty($question) && !empty($triggers_ids) )
            @foreach($triggers_ids as $q => $a)
                <div class="input-group">
                    <div class="template-box clearfix"> 
                        <select name="triggers[]" class="form-control" style="width: 50%; float: left;">
                            <option value="">Select question</option>
                            <optgroup label="Welcome survey questions">
                                @foreach(App\Models\Vox::find(11)->questions as $wq)
                                    <option value="{{ $wq->id }}" {{ $q == $wq->id ? 'selected="selected"' : '' }}>{{ $wq->question }}</option>
                                @endforeach
                            </optgroup>
                            <optgroup label="{{ $item->title }} survey questions">
                                @foreach($item->questions as $iq)
                                    <option value="{{ $iq->id }}" {{ $q == $iq->id ? 'selected="selected"' : '' }}>{{ $iq->question }}</option>
                                @endforeach
                            </optgroup>
                        </select>
                        {{ Form::text('answers-number[]', !empty($a) ? $a : null, array('maxlength' => 256, 'class' => 'form-control', 'style' => 'width: 50%; float: left;', 'placeholder' => 'Answer numbers')) }}
                    </div>
                    <div class="input-group-btn">
                        <button class="btn btn-default btn-remove-trigger" type="button">
                            <i class="glyphicon glyphicon-remove"></i>
                        </button>
                    </div>
                </div>
            @endforeach
        @else 
            <div class="input-group">
                <div class="template-box clearfix"> 
                    <select name="triggers[]" class="form-control" style="width: 50%; float: left;">
                        <option value="">Select question</option>
                        <optgroup label="Welcome survey questions">
                            @foreach(App\Models\Vox::find(11)->questions as $wq)
                                <option value="{{ $wq->id }}" {{ !empty($trigger_question_id) && $trigger_question_id == $wq->id ? 'selected="selected"' : '' }}>{{ $wq->question }}</option>
                            @endforeach
                        </optgroup>
                        <optgroup label="{{ $item->title }} survey questions">
                            @foreach($item->questions as $iq)
                                <option value="{{ $iq->id }}" {{ !empty($trigger_question_id) && $trigger_question_id == $iq->id ? 'selected="selected"' : '' }}>{{ $iq->question }}</option>
                            @endforeach
                        </optgroup>
                    </select>
                    {{ Form::text('answers-number[]', !empty($trigger_valid_answers) ? $trigger_valid_answers : null, array('maxlength' => 256, 'class' => 'form-control', 'style' => 'width: 50%; float: left;', 'placeholder' => 'Answer numbers')) }}
                </div>
                <div class="input-group-btn">
                    <button class="btn btn-default btn-remove-trigger" type="button">
                        <i class="glyphicon glyphicon-remove"></i>
                    </button>
                </div>
            </div>
        @endif
    </div>

    @if(!empty($trigger_type))
        <input type="hidden" id="old-trigger-type" value="{{ $trigger_type }}" />
    @endif


    <div class="input-group" id="new-trigger-group-template" >
        <div class="template-box clearfix"> 
            <select name="triggers[]" class="form-control" style="width: 50%; float: left;">
                <option value="">Select question</option>
                <optgroup label="Welcome survey questions">
                    @foreach(App\Models\Vox::find(11)->questions as $wq)
                        <option value="{{ $wq->id }}">{{ $wq->question }}</option>
                    @endforeach
                </optgroup>
                <optgroup label="{{ $item->title }} survey questions">
                    @foreach($item->questions as $iq)
                        <option value="{{ $iq->id }}">{{ $iq->question }}</option>
                    @endforeach
                </optgroup>
            </select>
            {{ Form::text('answers-number[]', null, array('maxlength' => 256, 'class' => 'form-control', 'style' => 'width: 50%; float: left;', 'placeholder' => 'Answer numbers')) }}
        </div>
        <div class="input-group-btn">
            <button class="btn btn-default btn-remove-trigger" type="button">
                <i class="glyphicon glyphicon-remove"></i>
            </button>
        </div>
    </div>
</div>
