<div class="form-group clearfix" style="align-items: center;display: flex;">
    <div class="{{ !empty($question) ? 'col-md-11' : 'col-md-12' }}">
        <a href="{{ url('cms/vox/') }}">Surveys</a> &raquo;
        <a href="{{ url('cms/vox/edit/'.$item->id) }}"> {{ $item->title }}</a>  
        @if(!empty($question))
        &raquo;
        {!! $question->question !!}
        @endif
    </div>
    @if(!empty($question) && empty($question->question_trigger) && $question->order != 1)
        <div class="col-md-1">
            <a class="btn btn-block btn-info" href="{{ $item->getLink().'?testmode=1&start-from='.$question->id.'&q-id='.(!empty(App\Models\VoxQuestion::where('vox_id', $question->vox_id)->where('order', $question->order -1)->first()) ? App\Models\VoxQuestion::where('vox_id', $question->vox_id)->where('order', $question->order -1)->first()->id : $question->id) }}" target="_blank">
                Test
            </a>
        </div>
    @endif
</div>


<div class="row">
    <div class="col-md-12">

        {{ Form::open(array('id' => 'question-'.( !empty($question) ? 'edit' : 'add') , 'url' => url('cms/'.$current_page.'/edit/'.$item->id.'/question/'.( !empty($question) ? $question->id : 'add') ), 'class' => 'form-horizontal questions-form', 'method' => 'post', 'files' => true)) }}

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
                                    {{ Form::textarea('question-'.$code, !empty($question) ? $question->{'question:'.$code} : '', array('maxlength' => 2048, 'class' => 'form-control input-title', 'style' => 'height: 34px;')) }}
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
                        <div class="col-md-3 question-scale-wrapper">
                            <label class="col-md-4 control-label">{{ trans('admin.page.'.$current_page.'.question-scale') }}</label>
                            <div class="col-md-8" style="padding-right: 0px;">
                                {{ Form::select('question_scale', ['' => '-'] + $scales, !empty($question) ? $question->vox_scale_id : '', array('class' => 'form-control question-scale-input select2', 'style' => 'width: 100%')) }}
                            </div>
                        </div>
                        <div class="col-md-3 question-number-wrapper">
                            <label class="col-md-4 control-label">Number limit</label>
                            <div class="col-md-4">
                                <input type="number" name="number-min" placeholder="From" value="{!! !empty($question) && !empty($question->number_limit) ? explode(':', $question->number_limit)[0] : '' !!}" class="form-control">
                            </div>
                            <div class="col-md-4" style="padding-right: 0px;">
                                <input type="number" name="number-max" placeholder="To" value="{!! !empty($question) && !empty($question->number_limit) ? explode(':', $question->number_limit)[1] : '' !!}" class="form-control">                                
                            </div>
                        </div>
                        <div class="col-md-5 hint-for-scale">
                            {!! nl2br(trans('admin.page.'.$current_page.'.question-scale-hint')) !!}
                        </div>
                    </div>
                    <div class="form-group answers-randomize clearfix">
                        <label class="col-md-2 control-label" for="dont_randomize_answers">Don’t randomize</label>
                        <div class="col-md-1">
                            <input type="checkbox" name="dont_randomize_answers" class="form-control" value="1" id="dont_randomize_answers" style="vertical-align: sub;width: 30px;" {!! !empty($question) && !empty($question->dont_randomize_answers) ? 'checked="checked"' : '' !!} />
                        </div>
                    </div>
                    <div class="form-group clearfix">
                        <label class="col-md-2 control-label" for="q_img">Question Image</label>
                        <div class="col-md-5">
                            @if(!empty($question) && $question->has_image)
                                <a target="_blank" href="{{ $question->getImageUrl() }}">
                                    <img src="{{ $question->getImageUrl(true) }}" style="background: #2f7de1; max-width: 200px;" />
                                </a>
                                <br/>
                                <a href="{{ url('cms/'.$current_page.'/edit/'.$item->id.'/question/'.$question->id.'/delete-question-image') }}">Delete photo</a>
                            @else
                                {{ Form::file('question-photo', ['id' => 'q_img' ,'accept' => 'image/gif, image/jpg, image/jpeg, image/png']) }}
                            @endif
                        </div>
                    </div>
                    <div class="form-group clearfix">
                        <label class="col-md-2 control-label" for="image_in_question">Question image on left</label>
                        <div class="col-md-1">
                            <input type="checkbox" name="image_in_question" class="form-control" value="1" id="image_in_question" style="vertical-align: sub;width: 30px;" {!! !empty($question) && !empty($question->image_in_question) ? 'checked="checked"' : '' !!} />
                        </div>
                    </div>
                    <div class="form-group clearfix">
                        <label class="col-md-2 control-label" for="image_in_tooltip">Question image in tooltip</label>
                        <div class="col-md-1">
                            <input type="checkbox" name="image_in_tooltip" class="form-control" value="1" id="image_in_tooltip" style="vertical-align: sub;width: 30px;" {!! !empty($question) && !empty($question->image_in_tooltip) ? 'checked="checked"' : '' !!} />
                        </div>
                    </div>
                    
                    <div class="form-group clearfix rank-explanation" style="display: {!! !empty($question) && $question->type == 'rank' ? 'block' : 'none' !!};">
                        <label class="col-md-2 control-label">Ranking explanation</label>
                        <div class="col-md-10">
                            @foreach($langs as $code => $lang_info)
                                <div class="tab-pane fade{{ $loop->first ? ' active in' : '' }} lang-{{ $code  }}">
                                    {{ Form::textarea('rank_explanation-'.$code, !empty($question) ? $question->translateorNew($code)->rank_explanation : old('rank_explanation-'.$code), array('maxlength' => 256, 'class' => 'form-control', 'placeholder' => 'If empty - default is: '.trans('vox.page.questionnaire.rank-info'), 'style' => 'max-height: 60px;')) }}
                                </div>
                            @endforeach
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
                                                <div class="col" style="display: flex; align-items: center;">
                                                    {{ Form::text('answers-'.$code.'[]', $ans, array('maxlength' => 2048, 'class' => 'form-control', 'placeholder' => 'Answer or name of the scale:weak,medium,strong', 'style' => 'display: inline-block; width: calc(100% - 60px);margin-right: 20px;')) }}

                                                    @if($question->answers_images_filename && !empty(json_decode($question->answers_images_filename, true)[$key]) )
                                                        <div class="answer-image-wrap">
                                                            <a href="{{ $question->getAnswerImageUrl(false, $key) }}" target="_blank">
                                                                <img src="{{ $question->getAnswerImageUrl(true, $key) }}">
                                                            </a>
                                                            <a class="btn btn-primary delete-answer-avatar" href="{{ url('cms/'.$current_page.'/edit/'.$item->id.'/question/'.$question->id.'/delete-answer-image/'.(json_decode($question->answers_images_filename, true)[$key])) }}">
                                                                <i class="fa fa-remove"></i>
                                                            </a>
                                                        </div>
                                                    @endif
                                                    <input type="hidden" name="filename[]" value="{{ !empty($question->answers_images_filename) && !empty(json_decode($question->answers_images_filename, true)[$key]) ? json_decode($question->answers_images_filename, true)[$key] : '' }}">
                                                    <input accept="image/gif, image/jpg, image/jpeg, image/png" style="width: 100px; display: {{ $question->answers_images_filename && !empty(json_decode($question->answers_images_filename, true)[$key]) ? 'none' : 'inline-block' }};" name="answer-photos[]" type="file">
                                                    
                                                    <div class="input-group-btn" style="display: inline-block;width: auto;">
                                                        <button class="btn btn-default btn-remove-answer" type="button">
                                                            <i class="glyphicon glyphicon-remove"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="flex input-group">
                                            {{ Form::text('answers-'.$code.'[]', '', array('maxlength' => 2048, 'class' => 'form-control', 'placeholder' => 'Answer or name of the scale:weak,medium,strong')) }}

                                            <input type="hidden" name="filename[]" value="">
                                            {{ Form::file('answer-photos[]', ['accept' => 'image/gif, image/jpg, image/jpeg, image/png', 'style' => 'width: 100px;']) }}
                                            
                                            <div class="input-group-btn" style="display: inline-block;width: auto;">
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
                                    Do you [includes cigars, e-cigarettes and any other tobacco products]smoke cigarettes[/]?<br/><br/>

                                    How clickable links works: <br/>
                                    I think {https://dentavox.dentacoin.com}DentaVox{/} is the best site ever <br/><br/>

                                    <br/>
                                    <a href="javascript:;" class="btn btn-success btn-block btn-add-answer">{{ trans('admin.page.'.$current_page.'.answers-add') }}</a>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    @if($item->questions->isNotEmpty())
                        <div class="form-group clearfix" style="margin-top: 40px;">
                            <h3 class="col-md-3" style="margin-top: 0px;">Previous question answers</h3>
                        </div>
                        <div class="form-group clearfix">
                            <label class="col-md-2 control-label">Show only the answers chosen <br/> in previous question </label>
                            <div class="col-md-4">
                                <select name="prev_q_id_answers" class="form-control select2" style="width: 50%; float: left;">
                                    <option value="">Select question</option>
                                    @foreach($item->questions as $iq)
                                        @if(empty($question) || ($iq->order < $question->order && $iq->type == 'multiple_choice'))
                                            <option value="{{ $iq->id }}" {{ !empty($question) && $question->prev_q_id_answers == $iq->id ? 'selected="selected"' : '' }}>{{ $iq->question }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group clearfix">
                            <label class="col-md-2 control-label" for="remove_answers_with_diez">Remove answers with #</label>
                            <input type="checkbox" name="remove_answers_with_diez" value="1" id="remove_answers_with_diez" style="vertical-align: sub; margin-top: 10px;" {!! !empty($question) && $question->remove_answers_with_diez ? 'checked="checked"' : '' !!} />
                        </div>
                        <div class="col-md-5 hint-for-scale">
                            * If you want to add additional answers - add them as you add normal answers
                        </div>
                    @endif

                    @if(!empty($question) && $question->type == 'multiple_choice' && !empty($question->{'answers:'.$code}) )
                        <div class="form-group clearfix" style="margin-top: 40px;">
                            <h3 class="col-md-3" style="margin-top: 0px;">Exclude answers</h3>
                        </div>
                        <div class="form-group clearfix">
                            <label class="col-md-2 control-label" for="exclude_answers">Exclude answers</label>
                            <input type="checkbox" name="exclude_answers_checked" value="1" id="exclude_answers" style="vertical-align: sub; margin-top: 10px;" {!! $question->excluded_answers ? 'checked="checked"' : '' !!} />
                        </div>

                        <div class="answer-groups-wrapper" {!! empty($question->excluded_answers) ? 'style="display:none;"' : '' !!}>
                            <div class="form-group">
                                <div class="col-md-2"></div>
                                <label class="col-md-4 control-label" style="text-align: left; padding-left: 0px;">Answers</label>
                                <label class="col-md-4 control-label" style="text-align: left;">Groups</label>
                            </div>

                            <div class="form-group clearfix">
                                <div class="col-md-2"></div>
                                <ul class="answer-group with-answers col-md-4">
                                    @foreach(json_decode($question->{'answers:'.$code}, true) as $key => $ans)
                                        @if(empty($question->excluded_answers) || (!in_array($key+1, $excluded_answers))) 
                                            <li class="answer-from-group" answer="{{ $key+1 }}">{{ $ans }}</li>
                                        @endif
                                    @endforeach
                                </ul>
                                <div class="answer-groups col-md-4">
                                    <div class="groups">
                                        @for($i = 1; $i <= 10; $i++)
                                            <ul class="answer-group" group="{{ $i }}" {!! $question->excluded_answers ? ($i > count($question->excluded_answers) ? 'style="display:none;"' : '') : ($i>2 ? 'style="display:none;"' : '') !!}>
                                                Group {{ $i }}

                                                @if(!empty($question->excluded_answers))
                                                    @foreach($question->excluded_answers as $k => $excluded_answers_array)
                                                        @if($k+1 == $i)
                                                            @foreach(json_decode($question->{'answers:'.$code}, true) as $key => $ans)
                                                                @if(in_array($key+1, $excluded_answers_array))
                                                                    <li class="answer-from-group" answer="{{ $key+1 }}">{{ $ans }}</li>
                                                                @endif
                                                            @endforeach
                                                        @endif
                                                    @endforeach
                                                @endif
                                            </ul>
                                        @endfor
                                        <a href="javascript:;" class="btn btn-info add-answer-group">Add group</a>
                                        <p style="font-size: 10px;">* 10 groups max</p>
                                    </div>
                                </div>
                            </div>

                            <input type="hidden" name="excluded-answers" value="{{ json_encode($question->excluded_answers) }}" id="excluded-answers"/>
                        </div>
                    @endif

                    <div class="form-group clearfix" style="margin-top: 40px;">
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
                                <label class="col-md-1 control-label">
                                    {{ trans('admin.page.'.$current_page.'.question-trigger') }} <br/><br/>
                                    <div class="legend-btn" data-toggle="modal" data-target="#legendModal" style="background-color: #ab3bff;text-align: center;color: white;padding: 10px;border-radius: 5px;font-size: 9px;">Check Demographic's Answers</div>
                                </label>
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
                                                <div class="input-group clearfix" style="display: flex;">
                                                    <div class="template-box clearfix" style="max-width: calc(100% - 40px);width: 100%;">
                                                        <select name="triggers[]" class="form-control select2 trigger-select" style="width: 50%; float: left;">
                                                            <option value="">Select question</option>                                                            
                                                            <optgroup label="{{ $item->title }} survey questions">
                                                                @foreach($item->questions as $iq)
                                                                    @if($iq->order < $question->order)
                                                                        <option value="{{ $iq->id }}" {{ !empty($question) && explode(':', $trigger)[0] == $iq->id ? 'selected="selected"' : '' }}>{{ $iq->order }}. {{ $iq->question }}</option>
                                                                    @endif
                                                                @endforeach
                                                            </optgroup>
                                                            <optgroup label="Welcome survey questions">
                                                                @foreach(App\Models\Vox::find(11)->questions as $wq)
                                                                    <option value="{{ $wq->id }}" {{ !empty($question) && explode(':', $trigger)[0] == $wq->id ? 'selected="selected"' : '' }}>{{ $wq->question }}</option>
                                                                @endforeach
                                                            </optgroup>
                                                            <optgroup label="Demographic questions">
                                                                @foreach(config('vox.details_fields') as $kdf => $dq)
                                                                    <option value="{{ $kdf }}" demographic-ans {{ !empty($question) && explode(':', $trigger)[0] == $kdf ? 'selected="selected"' : '' }}>{{ $dq['label'] }}</option>
                                                                @endforeach
                                                                <option value="age_groups" demographic-ans {{ !empty($question) && explode(':', $trigger)[0] == 'age_groups' ? 'selected="selected"' : '' }}>Age Groups</option>
                                                                <option value="gender" demographic-ans {{ !empty($question) && explode(':', $trigger)[0] == 'gender' ? 'selected="selected"' : '' }}>Gender</option>
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
                                    Example: "Question text?" / One trigger answer: 1 (for the first answer), 2 (for the second answer, etc.); 2+ answers: 1, 2, 3, 4<br/><br/>
                                    For NOT chosen trigger answers - add ! before the answers<br/>
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

                    <div class="form-group question-control-wrap">
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

                    <div class="form-group question-stats clearfix">
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

                            <label for="stats_title_question" style="display: block;margin-top: -20px;margin-bottom: 20px;">
                                <input type="checkbox" name="stats_title_question" value="1" id="stats_title_question" {!! !empty($question->stats_title_question) ? 'checked="checked"' : '' !!} style="vertical-align:sub;"/>
                                Take question as is
                            </label>

                            <div class="form-group clearfix">
                                @foreach($langs as $code => $lang_info)
                                    <div class="tab-pane fade{{ $loop->first ? ' active in' : '' }} lang-{{ $code  }} col-md-12">
                                        {{ Form::text('stats_subtitle-'.$code, !empty($question) ? $question->translateorNew($code)->stats_subtitle : old('stats_subtitle-'.$code), array('maxlength' => 2048, 'class' => 'form-control input-title', 'style' => 'max-height: 100px;', 'placeholder' => 'Statistics subtitle in '.$lang_info['name'])) }}
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
                    
                    <div class="stat_title" style="display: {!! !empty($question) && $question->type == 'multiple_choice' ? 'block' : 'none' !!};">
                        <div class="form-group clearfix">
                            <label class="col-md-2 control-label">
                                Show Only Top Answers 
                                <br/>(multiple choice only)
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
                                        {!! strip_tags($question->removeAnswerTooltip($ans)) !!} &nbsp;&nbsp;&nbsp;&nbsp;
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

                    <div class="form-group clearfix">
                        <label class="col-md-2 control-label">Order stats answers with # as they are</label>
                        <div class="col-md-10">
                            <label for="order_stats_answers_with_diez_as_they_are">
                                <input type="checkbox" name="order_stats_answers_with_diez_as_they_are" class="form-control" value="1" id="order_stats_answers_with_diez_as_they_are" style="vertical-align: sub; width: 14px;" {!! !empty($question) && !empty($question->order_stats_answers_with_diez_as_they_are) ? 'checked="checked"' : '' !!} />
                            </label>
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
        <div class="col" style="display: flex; align-items: center;">
            {{ Form::text('something', '', array('maxlength' => 2048, 'class' => 'form-control answer-name', 'placeholder' => 'Answer or name of the scale:weak,medium,strong', 'style' => 'display: inline-block; width: calc(100% - 60px);margin-right: 20px;')) }}

            <input type="hidden" name="filename[]" value="">
            {{ Form::file('answer-photos[]', ['accept' => 'image/gif, image/jpg, image/jpeg, image/png', 'style' => 'width: 100px; display: inline-block;']) }}
            
            <div class="input-group-btn" style="display: inline-block;width: auto;">
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
                        <select name="triggers[]" class="form-control trigger-select" style="width: 50%; float: left;">
                            <option value="">Select question</option>                                                            
                            <optgroup label="{{ $item->title }} survey questions">
                                @foreach($item->questions as $iq)
                                    <option value="{{ $iq->id }}" {{ $q == $iq->id ? 'selected="selected"' : '' }}>{{ $iq->question }}</option>
                                @endforeach
                            </optgroup>
                            <optgroup label="Welcome survey questions">
                                @foreach(App\Models\Vox::find(11)->questions as $wq)
                                    <option value="{{ $wq->id }}" {{ $q == $wq->id ? 'selected="selected"' : '' }}>{{ $wq->question }}</option>
                                @endforeach
                            </optgroup>
                            <optgroup label="Demographic questions">
                                @foreach(config('vox.details_fields') as $kdf => $dq)
                                    <option value="{{ $kdf }}" demographic-ans {{ $q == $kdf ? 'selected="selected"' : '' }}>{{ $dq['label'] }}</option>
                                @endforeach
                                <option value="age_groups" demographic-ans {{ $q == 'age_groups' ? 'selected="selected"' : '' }}>Age Groups</option>
                                <option value="gender" demographic-ans {{ $q == 'gender' ? 'selected="selected"' : '' }}>Gender</option>
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
                    <select name="triggers[]" class="form-control trigger-select" style="width: 50%; float: left;">
                        <option value="">Select question</option>
                        <optgroup label="{{ $item->title }} survey questions">
                            @foreach($item->questions as $iq)
                                <option value="{{ $iq->id }}" {{ !empty($trigger_question_id) && $trigger_question_id == $iq->id ? 'selected="selected"' : '' }}>{{ $iq->question }}</option>
                            @endforeach
                        </optgroup>
                        <optgroup label="Welcome survey questions">
                            @foreach(App\Models\Vox::find(11)->questions as $wq)
                                <option value="{{ $wq->id }}" {{ !empty($trigger_question_id) && $trigger_question_id == $wq->id ? 'selected="selected"' : '' }}>{{ $wq->question }}</option>
                            @endforeach
                        </optgroup>
                        <optgroup label="Demographic questions">
                            @foreach(config('vox.details_fields') as $kdf => $dq)
                                <option value="{{ $kdf }}" demographic-ans {{ !empty($trigger_question_id) && $trigger_question_id == $kdf ? 'selected="selected"' : '' }}>{{ $dq['label'] }}</option>
                            @endforeach
                            <option value="age_groups" demographic-ans {{ !empty($trigger_question_id) && $trigger_question_id == 'age_groups' ? 'selected="selected"' : '' }}>Age Groups</option>
                            <option value="gender" demographic-ans {{ !empty($trigger_question_id) && $trigger_question_id == 'gender' ? 'selected="selected"' : '' }}>Gender</option>
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
        <div class="template-box clearfix" style="width: 100%;">
            <select name="triggers[]" class="form-control" style="width: 50%; float: left;">
                <option value="">Select question</option>
                <optgroup label="{{ $item->title }} survey questions">
                    @foreach($item->questions as $iq)
                        <option value="{{ $iq->id }}" {{ !empty($question) && $question->id == $iq->id ? 'selected="selected"' : '' }}>{{ $iq->question }}</option>
                    @endforeach
                </optgroup>
                <optgroup label="Welcome survey questions">
                    @foreach(App\Models\Vox::find(11)->questions as $wq)
                        <option value="{{ $wq->id }}">{{ $wq->question }}</option>
                    @endforeach
                </optgroup>
                <optgroup label="Demographic questions">
                    @foreach(config('vox.details_fields') as $kdf => $dq)
                        <option value="{{ $kdf }}" demographic-ans>{{ $dq['label'] }}</option>
                    @endforeach
                    <option value="age_groups" demographic-ans>Age Groups</option>
                    <option value="gender" demographic-ans>Gender</option>
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



<div id="legendModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Demographic answers</h4>
            </div>
            <div class="modal-body">
                @foreach(config('vox.details_fields') as $key => $value)
                    <h4>{{ $value['label'] }}</h4>

                    @foreach($value['values'] as $k => $v)
                        <p><b>{{ $loop->iteration }}</b> : {{ $v }}</p>
                    @endforeach
                    <br/>
                @endforeach

                <h4>Age groups</h4>
                @foreach(config('vox.age_groups') as $key => $value)
                    <p><b>{{ $loop->iteration }}</b> : {{ $value }}</p>
                @endforeach
                <br/>

                <h4>Gender</h4>
                <p><b>1</b> : Male</p>
                <p><b>2</b> : Female</p>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>

    </div>
</div>