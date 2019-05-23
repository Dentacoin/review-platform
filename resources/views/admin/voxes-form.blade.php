@extends('admin')


@section('content')

<h1 class="page-header">
    {{ empty($item) ? trans('admin.page.'.$current_page.'.new.title') : trans('admin.page.'.$current_page.'.edit.title') }}
</h1>
<!-- end page-header -->

<div class="row">
    <!-- begin col-6 -->
    <div class="col-md-12 ui-sortable">
        {{ Form::open(array('id' => 'page-add', 'class' => 'form-horizontal', 'method' => 'post', 'files' => true)) }}

            <div class="panel panel-inverse panel-with-tabs" data-sortable-id="ui-unlimited-tabs-1">
                <div class="panel-heading p-0">
                    <div class="panel-heading-btn m-r-10 m-t-10">
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-success" data-click="panel-expand" data-original-title="" title=""><i class="fa fa-expand"></i></a>
                    </div>
                    <!-- begin nav-tabs -->
                    <div class="tab-overflow overflow-right">
                        <ul class="nav nav-tabs nav-tabs-inverse">
                            <li class="prev-button"><a href="javascript:;" data-click="prev-tab" class="text-success"><i class="fa fa-arrow-left"></i></a></li>
                            @foreach($langs as $code => $lang_info)
                                <li class="{{ $loop->first ? 'active' : '' }}"><a href="#nav-tab-{{ $code }}" data-toggle="tab" aria-expanded="false">{{ $lang_info['name'] }}</a></li>
                            @endforeach

                            <li class="next-button"><a href="javascript:;" data-click="next-tab" class="text-success"><i class="fa fa-arrow-right"></i></a></li>
                        </ul>
                    </div>
                </div>
                <div class="tab-content">
                    @foreach($langs as $code => $lang_info)
                        <div class="tab-pane fade{{ $loop->first ? ' active in' : '' }}" id="nav-tab-{{ $code }}">
                            <div class="form-group">
                                <label class="col-md-2 control-label" style="max-width: 200px;">{{ trans('admin.page.'.$current_page.'.lang-slug') }}</label>
                                <div class="col-md-{{ !empty($item) ? '6' : '10' }}">
                                    {{ Form::text('slug-'.$code, !empty($item) ? $item->translateOrNew($code)->slug : null, array('maxlength' => 256, 'class' => 'form-control')) }}
                                </div>
                                @if(!empty($item))
                                    <div class="col-md-2">
                                        <a href="{{ $item->getLink().'?testmode=0' }}" target="_blank" class="btn btn-primary btn-block">Preview Survey</a>
                                    </div>
                                    <div class="col-md-2">
                                        <a href="{{ $item->getLink().'?testmode=1' }}" target="_blank" class="btn btn-primary btn-block">Test</a>
                                    </div>
                                @endif
                            </div>
                            @if(!empty($item))
                                <div class="form-group">
                                    <div style="float: right; padding-right: 20px;">
                                        * Test mode disables bans, captcha, cross-checks
                                    </div>
                                </div>
                            @endif
                            <div class="form-group">
                                <label class="col-md-4 "><b>Title</b></label>
                                <label class="col-md-4 "><b>Survey Description</b></label>
                                <label class="col-md-4 "><b>Stats Description</b></label>
                            </div>
                            <div class="form-group">
                                <div class="col-md-4">
                                    {{ Form::text('title-'.$code, !empty($item) ? $item->{'title:'.$code} : null, array('maxlength' => 256, 'class' => 'form-control input-title', 'placeholder' => 'Title for Survey page - Website')) }}
                                </div>
                                <div class="col-md-4" max-symb="244">
                                    {{ Form::textarea('description-'.$code, !empty($item) ? $item->{'description:'.$code} : null, array('maxlength' => 2048, 'class' => 'form-control input-description', 'placeholder' => 'Description for Survey page - Website', 'id' => 'surv-desc')) }}
                                    <p class="textarea-symbols"><span class="symbol-count">0</span>/244 (Maximum symbols count)</p>
                                </div>
                                <div class="col-md-4">
                                    {{ Form::textarea('stats_description-'.$code, !empty($item) ? $item->translateOrNew($code)->stats_description : null, array('maxlength' => 2048, 'class' => 'form-control input-stats_description', 'placeholder' => 'Description for Stats page - Website')) }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <!-- begin panel -->
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <div class="panel-heading-btn">
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                    </div>
                    <h4 class="panel-title">{{ empty($item) ? trans('admin.page.'.$current_page.'.new.title') : trans('admin.page.'.$current_page.'.edit.title') }}</h4>
                </div>
                <div class="panel-body">
                    {!! csrf_field() !!}
                    @if(!empty($item))
                        <div class="form-group">
                            <label class="col-md-2 control-label" style="max-width: 200px;">{{ trans('admin.page.'.$current_page.'.reward_usd') }}</label>
                            <label class="col-md-10  control-label" style="text-align: left;">
                                {{ $item->questions->count() }} x {{ $item->getRewardPerQuestion()->dcn }} = {{ $item->getRewardTotal() }} DCN (${{ $item->getRewardTotal(true) }})
                            </label>
                        </div>
                        <div class="form-group">
                            <label class="col-md-2 control-label" style="max-width: 200px;">{{ trans('admin.page.'.$current_page.'.duration') }}</label>
                            <label class="col-md-10  control-label" style="text-align: left;">
                                {{ $item->questions->count() }} x 10sec = ~{{ ceil( $item->questions->count()/6 ) }} min
                            </label>
                        </div>
                    @endif

                    <div class="form-group">
                        <label class="col-md-2 control-label" style="max-width: 200px;">Sort order</label>
                        <div class="col-md-10">
                            {{ Form::number('sort_order', !empty($item) ? $item->sort_order : null, array('class' => 'form-control')) }}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-2 control-label" style="max-width: 200px;">{{ trans('admin.page.'.$current_page.'.type') }}</label>
                        <div class="col-md-10">
                            {{ Form::select('type', $types, !empty($item) ? $item->type : null, array('class' => 'form-control')) }}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-2 control-label" style="padding-top: 0px; max-width: 200px;">{{ trans('admin.page.'.$current_page.'.categories') }}</label>
                        <div class="col-md-10">
                            @foreach($category_list as $cat)
                                <label class="col-md-3" for="cat-{{ $cat->id }}">

                                    <input type="checkbox" name="categories[]" value="{{ $cat->id }}" id="cat-{{ $cat->id }}" {!! !empty($item) && $item->categories->where('vox_category_id', $cat->id)->isNotEmpty() ? 'checked="checked"' : '' !!} >
                                    
                                    {{ $cat->name }}
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="featured" class="col-md-2 control-label" style="padding-top: 0px; max-width: 200px;">Featured</label>
                        <div class="col-md-10">
                            <input type="checkbox" name="featured" value="1" id="featured" {!! !empty($item) && $item->featured ? 'checked="checked"' : '' !!} >
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="has_stats" class="col-md-2 control-label" style="padding-top: 0px; max-width: 200px;">Stats Enabled</label>
                        <div class="col-md-10">
                            <input type="checkbox" name="has_stats" value="1" id="has_stats" {!! !empty($item) && $item->has_stats ? 'checked="checked"' : '' !!} >
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="featured" class="col-md-2 control-label" style="padding-top: 0px; max-width: 200px;">Featured in Stats</label>
                        <div class="col-md-10">
                            <input type="checkbox" name="stats_featured" value="1" id="stats_featured" {!! !empty($item) && $item->stats_featured ? 'checked="checked"' : '' !!} >
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="featured" class="col-md-2 control-label" style="padding-top: 0px; max-width: 200px;">Thumb</label>
                        <div class="col-md-10">
                            {{ Form::file('photo', ['id' => 'photo', 'accept' => 'image/gif, image/jpg, image/jpeg, image/png']) }}<br/>
                            * Size: 520х352px, up to 2 MB<br/>
                            
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="featured" class="col-md-2 control-label" style="padding-top: 0px; max-width: 200px">Social Image</label>
                        <div class="col-md-10">
                            {{ Form::file('photo-social', ['id' => 'photo-social', 'accept' => 'image/gif, image/jpg, image/jpeg, image/png']) }}<br/>
                            * Size: 1200x628, up to 2 MB<br/>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="featured" class="col-md-2 control-label" style="padding-top: 0px; max-width: 200px;">&nbsp;</label>
                        @if(!empty($item) && $item->hasimage)
                            <div class="col-md-2">
                                Thumb<br/>
                                <a target="_blank" href="{{ $item->getImageUrl() }}">
                                    <img src="{{ $item->getImageUrl(true) }}" style="background: #2f7de1; width: 100%;" />
                                </a>
                                <br/>
                                <a href="{{ url('cms/'.$current_page.'/edit/'.$item->id.'/delpic') }}">Delete photo</a>
                            </div>
                        @endif
                        @if(!empty($item) && $item->hasimage_social)
                            <div class="form-group">
                                <div class="col-md-2">
                                    Original image<br/>
                                    <a target="_blank" href="{{ $item->getSocialImageUrl() }}">
                                        <img src="{{ $item->getSocialImageUrl() }}" style="background: #2f7de1; width: 100%;" />
                                    </a>
                                </div>
                                <div class="col-md-2">
                                    Survey Social<br/>
                                    <a target="_blank" href="{{ $item->getSocialImageUrl('survey') }}">
                                        <img src="{{ $item->getSocialImageUrl('survey') }}" style="background: #2f7de1; width: 100%;" />
                                    </a>
                                </div>
                                <div class="col-md-2">
                                    Stats Social<br/>
                                    <a target="_blank" href="{{ $item->getSocialImageUrl('stats') }}">
                                        <img src="{{ $item->getSocialImageUrl('stats') }}" style="background: #2f7de1; width: 100%;" />
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="form-group col-md-12">
                        <h3>Related</h3>
                        <p>You can add up to 6 related surveys</p>
                        <div class="form-group">
                            @for ($i=0; $i < 6 ; $i++) 
                                <label class="col-md-1 control-label">Related survey</label>
                                <div class="col-md-3">
                                    <select class="form-control select2" name="related_vox_id[]">
                                        <option value="">Select survey</option>
                                        @foreach($all_voxes as $vox)
                                            <option value="{{ $vox->id }}" {!! !empty($item) && $item->related->isNotEmpty() && !empty($item->related[$i]->related_vox_id) && ($vox->id == $item->related[$i]->related_vox_id) ? 'selected' : '' !!}>{{ $vox->title }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                @if($i == 2)
                                    </div>
                                    <div class="form-group">
                                @endif

                            @endfor
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary btn-block">{{ empty($item) ? trans('admin.page.'.$current_page.'.new.submit') : trans('admin.page.'.$current_page.'.edit.submit') }}</button>
                        </div>
                    </div>
                </div>

            </div>

        {{ Form::close() }}


        @if(!empty($item))

            @if($item->questions->isNotEmpty())
                <h3>Questions</h3>
                <div class="panel panel-inverse">
                    <div class="panel-heading">
                        <h4 class="panel-title">{{ trans('admin.page.'.$current_page.'.questions') }}</h4>
                    </div>
                    <div class="tab-content">

                        <table class="table table-striped table-question-list">
                            <thead>
                                <tr>
                                    <th>{{ trans('admin.page.'.$current_page.'.question-num') }}</th>
                                    <th>{{ trans('admin.page.'.$current_page.'.question-title') }}</th>
                                    <th>{{ trans('admin.page.'.$current_page.'.question-control') }}</th>
                                    <th>{{ trans('admin.page.'.$current_page.'.question-stats') }}</th>
                                    <th>{{ trans('admin.page.'.$current_page.'.question-type') }}</th>
                                    <th>{{ trans('admin.page.'.$current_page.'.question-trigger') }}</th>
                                    <th>Respondents</th>
                                    <th>{{ trans('admin.page.'.$current_page.'.question-edit') }}</th>
                                    <th>{{ trans('admin.page.'.$current_page.'.question-delete') }}</th>
                                </tr>
                            </thead>
                            <tbody class="questions-draggable">
                                @foreach($item->questions as $question)
                                    <tr question-id="{{ $question->id }}" {!! in_array($question->id, $linked_triggers) ? 'class="linked"' : '' !!}>
                                        <td>
                                            <input type="text" class="form-control question-number" style="width: 60px;" data-qid="{{ $question->id }}" value="{{ $question->order }}" />
                                        </td>
                                        <td>
                                            <textarea style="min-width: 360px;" class="form-control question-question" data-qid="{{ $question->id }}">{{ $question->question }}</textarea>
                                        </td>
                                        <td>
                                            {!! $question->is_control ? '<b>'.trans( 'admin.common.yes' ).'</b>' : trans( 'admin.common.no' ) !!}
                                        </td>
                                        <td>
                                            @if($question->used_for_stats=='standard')
                                                Yes
                                            @elseif($question->used_for_stats=='dependency')
                                                Related to: {{ $question->related->question }}
                                            @endif
                                        </td>
                                        <td>{{ trans('admin.enums.question-type.'.$question->type) }}</td>
                                        <td>{!! $triggers[$question->id] !!}</td>
                                        <td>
                                            <a href="{{ url('cms/vox/explorer/'.$question->id) }}" target="_blank">
                                                {!! $question->respondent_count() !!}
                                            </a>
                                        </td>
                                        <td>
                                            <a class="btn btn-sm btn-success" href="{{ url('cms/'.$current_page.'/edit/'.$item->id.'/question/'.$question->id) }}">
                                                <i class="fa fa-pencil"></i>
                                            </a>
                                        </td>
                                        <td>
                                            <a class="btn btn-sm btn-success" onclick="return confirm('{{ trans('admin.common.sure') }}')" href="{{ url('cms/'.$current_page.'/edit/'.$item->id.'/question-del/'.$question->id) }}">
                                                <i class="fa fa-remove"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif



            <a class="btn btn-primary btn-block" href="javascript: $('#add-new-question').show(); $('#add-new-question').prev().hide();">
                Add Question
            </a>
            <div id="add-new-question" style="display: none;">
                <h3>Add question</h3>
                
                @include('admin.parts.vox-question', [
                    'question' => null,
                    'next' => $item->questions->count()+1
                ])
            </div>

            <br/>

            <a class="btn btn-primary btn-block" href="javascript: $('#import-questions').show(); $('#import-questions').prev().hide();">
                Import / Export
            </a>
            <div id="import-questions" style="display: none;">
                <h3>Import / Export</h3>
                <div class="panel panel-inverse">
                    <div class="panel-heading">
                        Import / Export Options
                    </div>
                    <div class="tab-content">
                        <div class="row">
                            <div class="col-md-4">
                                <h4>Quick import</h4>
                                <form class="form-horizontal" id="translations-import-quick" method="post" action="{{ url('cms/'.$current_page.'/edit/'.$item->id.'/import-quick') }}" enctype="multipart/form-data">
                                    {!! csrf_field() !!}
                                    <div class="row">
                                        <div class="col-md-12">
                                            <input type="file" class="btn-block form-control" name="table" accept=".xls, .xlsx" />
                                        </div>
                                        <div class="col-md-12">
                                            <button type="submit" class="btn btn-success btn-block">
                                                Quick Import
                                            </button>
                                        </div>
                                    </div>
                                </form>
                                <br/>
                                <a href="{{ url('survey-import-template.xlsx') }}">Download sample</a>
                            </div>
                            <div class="col-md-4">
                                <h4>{{ trans('admin.page.'.$current_page.'.questions-export') }}</h4>
                                <a class="btn btn-primary btn-block" href="{{ url('cms/'.$current_page.'/edit/'.$item->id.'/export') }}" target="_blank">
                                    {{ trans('admin.page.'.$current_page.'.questions-export') }}
                                </a>
                            </div>
                            <div class="col-md-4">
                                <h4>{{ trans('admin.page.'.$current_page.'.questions-import') }}</h4>
                                <form class="form-horizontal" id="translations-import" method="post" action="{{ url('cms/'.$current_page.'/edit/'.$item->id.'/import') }}" enctype="multipart/form-data">
                                    {!! csrf_field() !!}
                                    <div class="row">
                                        <div class="col-md-12">
                                            <input type="file" class="btn-block form-control" name="table" accept=".xls, .xlsx" />
                                        </div>
                                        <div class="col-md-12">
                                            <button type="submit" class="btn btn-success btn-block">
                                                {{ trans('admin.page.'.$current_page.'.questions-import') }}
                                            </button>
                                        </div>
                                    </div>
                                </form>
                                <br/>
                                <i>* Export a translation file and fill the texts in it</i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


        @endif


    </div>
</div>

@endsection