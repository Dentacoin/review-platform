@extends('admin')


@section('content')

<h1 class="page-header">
    {{ empty($item) ? trans('admin.page.'.$current_page.'.new.title') : trans('admin.page.'.$current_page.'.edit.title') }}
</h1>

@if(!empty($error))
   <i class="fa fa-exclamation-triangle err-vox" data-toggle="modal" data-target="#errorsModal"></i>
@endif

@if(!empty($item) && !empty($questions_order_bug))
    <div class="alert alert-danger m-b-15">
        Please, reorder the questions. There are duplicated or missing order numbers.
    </div>
@endif
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
                                <div class="col-md-{{ !empty($item) ? '4' : '10' }}">
                                    {{ Form::text('slug-'.$code, !empty($item) ? $item->translateOrNew($code)->slug : null, array('maxlength' => 256, 'class' => 'form-control')) }}
                                </div>
                                @if(!empty($item))
                                    <div class="col-md-2">
                                        <!-- <a href="{{ $item->getLink().'?testmode=0' }}" target="_blank" class="btn btn-primary btn-block">Preview Survey</a> -->
                                        <a href="{{ $item->getStatsList() }}" target="_blank" class="btn btn-primary btn-block">Preview Stats</a>
                                    </div>
                                    <div class="col-md-2">
                                        <a href="javascript:;" data-toggle="modal" data-target="#exportStats" class="btn btn-primary btn-block">Export Stats</a>
                                    </div>
                                    <div class="col-md-2">
                                        <a href="{{ $item->getLink().'?testmode=1' }}" target="_blank" class="btn btn-primary btn-block">Test Survey</a>
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
                                @if(!empty($item->dcn_questions_count))
                                    {{ $item->dcn_questions_count }} x {{ $item->getRewardPerQuestion()->dcn }} = {{ $item->getRewardTotal() }} DCN (${{ $item->getRewardTotal(true) }})
                                @else
                                    {{ $item->questions->count() }} x {{ $item->getRewardPerQuestion()->dcn }} = {{ $item->getRewardTotal() }} DCN (${{ $item->getRewardTotal(true) }})
                                @endif
                            </label>
                        </div>
                        <div class="form-group">
                            <label class="col-md-2 control-label" style="max-width: 200px;">{{ trans('admin.page.'.$current_page.'.duration') }}</label>
                            <label class="col-md-10  control-label" style="text-align: left;">
                                @if(!empty($item->dcn_questions_count))
                                    {{ $item->dcn_questions_count }} x 10sec = ~{{ ceil( $item->dcn_questions_count/6 ) }} min
                                @else
                                    {{ $item->questions->count() }} x 10sec = ~{{ ceil( $item->questions->count()/6 ) }} min
                                @endif
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
                    <div class="form-group" id="has-stats-already" data="{!! empty($item) ? 'yes' : ($item->has_stats ? 'yes' : 'no') !!}">
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
                        <label for="featured" class="col-md-2 control-label" style="padding-top: 0px; max-width: 200px">Stats Image</label>
                        <div class="col-md-10">
                            {{ Form::file('photo-stats', ['id' => 'photo-stats', 'accept' => 'image/gif, image/jpg, image/jpeg, image/png']) }}<br/>
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

                    <style type="text/css">
                        .target-wrapper .select2-container {
                            width: 100% !important;
                        }

                        .target-wrapper .select2-container--default .select2-selection--multiple {
                            height: auto !important;
                        }

                    </style>

                    <div class="form-group col-md-12">
                        <h3 style="display: inline-block; margin-right: 20px;">TARGETING</h3> <a href="javascript:;" class="btn btn-primary target-button">Show target groups</a>
                        @if(empty($item->country_percentage))
                            <div class="alert alert-danger" style="display: inline-block;">Missing Country Percentage</div>
                        @endif
                        <div class="col-md-12">

                            <div class="target-wrapper" style="display: none; margin-top: 30px;"> 
                                @foreach(config('vox.details_fields') as $key => $value)
                                    <div class="form-group" style="border-bottom: 1px solid #9E9E9E; padding-bottom: 15px;">
                                        <label class="col-md-1 control-label" style="padding-top: 0px; max-width: 200px;">{{ trans('admin.common.'.$key) }}</label>
                                        <div class="col-md-11">
                                            @foreach($value['values'] as $k => $v)
                                                <label class="col-md-3" for="{{ $k }}">

                                                    <input type="checkbox" name="{{ $key }}[]" value="{{ $k }}" id="{{ $k }}" {!! !empty($item) && !empty($item->$key) && in_array($k, $item->$key) ? 'checked="checked"' : '' !!}>
                                                    
                                                    {{ $v }}
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach

                                <div class="form-group" style="border-bottom: 1px solid #9E9E9E; padding-bottom: 15px;">
                                    <label class="col-md-1 control-label" style="padding-top: 0px; max-width: 200px;">Age groups</label>
                                    <div class="col-md-11">
                                        @foreach(config('vox.age_groups') as $key => $value)
                                            <label class="col-md-3" for="{{ $key }}">

                                                <input type="checkbox" name="age[]" value="{{ $key }}" id="{{ $key }}" {!! !empty($item) && !empty($item->age) && in_array($key, $item->age) ? 'checked="checked"' : '' !!}>
                                                
                                                {{ $value }}
                                            </label>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="form-group" style="border-bottom: 1px solid #9E9E9E; padding-bottom: 15px;">
                                    <label class="col-md-1 control-label" style="padding-top: 0px; max-width: 200px;">Gender</label>
                                    <div class="col-md-11">
                                        <label class="col-md-3" for="m">
                                            <input type="checkbox" name="gender[]" value="m" id="m" {!! !empty($item) && !empty($item->gender) && in_array('m', $item->gender) ? 'checked="checked"' : '' !!}>                                            
                                            Men
                                        </label>
                                        <label class="col-md-3" for="f">
                                            <input type="checkbox" name="gender[]" value="f" id="f" {!! !empty($item) && !empty($item->gender) && in_array('f', $item->gender) ? 'checked="checked"' : '' !!}>                                            
                                            Women
                                        </label>
                                    </div>
                                </div>

                                <div class="form-group" style="border-bottom: 1px solid #9E9E9E; padding-bottom: 15px;">
                                    <label class="col-md-1 control-label" style="padding-top: 0px; max-width: 200px;">User type</label>
                                    <div class="col-md-11">
                                        <label class="col-md-3" for="dentists">
                                            <input type="checkbox" name="dentists_patients[]" value="dentists" id="dentists" {!! !empty($item) && !empty($item->dentists_patients) && in_array('dentists', $item->dentists_patients) ? 'checked="checked"' : '' !!}>                                            
                                            Dentists
                                        </label>
                                        <label class="col-md-3" for="patients">
                                            <input type="checkbox" name="dentists_patients[]" value="patients" id="patients" {!! !empty($item) && !empty($item->dentists_patients) && in_array('patients', $item->dentists_patients) ? 'checked="checked"' : '' !!}>                                            
                                            Patients
                                        </label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-1 control-label">Countries</label>
                                    <div class="col-md-2">
                                        <select class="form-control select2" name="countries_ids[]" multiple>
                                            @foreach( \App\Models\Country::with('translations')->get() as $country )
                                                <option value="{{ $country->id }}" {!! !empty($item) && !empty($item->countries_ids) && in_array($country->id, $item->countries_ids) ? 'selected="selected"' : null !!}>{{ $country->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <label class="col-md-1 control-label">Limit not valid for countries</label>
                                    <div class="col-md-2">
                                        <select class="form-control select2" name="exclude_countries_ids[]" multiple>
                                            @foreach( \App\Models\Country::with('translations')->get() as $country )
                                                <option value="{{ $country->id }}" {!! !empty($item) && !empty($item->exclude_countries_ids) && in_array($country->id, $item->exclude_countries_ids) ? 'selected="selected"' : null !!}>{{ $country->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <label class="col-md-1 control-label">
                                        Max percentage of users from one country
                                        <!-- <br/> (this will not apply if there is only one selected country) -->
                                    </label>
                                    <div class="col-md-2">

                                        <style type="text/css">
                                            input::-webkit-outer-spin-button,
                                            input::-webkit-inner-spin-button {
                                                -webkit-appearance: none;
                                                margin: 0;
                                            }

                                            /* Firefox */
                                            input[type=number] {
                                                -moz-appearance: textfield;
                                            }
                                        </style>
                                        {{ Form::number( 'country_percentage', !empty($item) ? $item->country_percentage : '' , array('class' => 'form-control', 'placeholder' => 'Number from 1 to 100') ) }}
                                    </div>
                                    @if(!empty($item) && !empty($item->users_percentage) && !empty($item->country_percentage))
                                        <div class="col-md-3" style="border: 1px solid black;padding-top: 10px;padding-bottom: 10px;">
                                            <b> Current users percentage :</b> <br/><br/>

                                            @foreach($item->users_percentage as $c => $up)
                                                <p {!! intval($item->country_percentage) <= intval($up) ? 'style="color:red;"' : ( !empty($item->exclude_countries_ids) && in_array($c, $item->exclude_countries_ids) ? 'style="color:blue;"' : '') !!}> {{ App\Models\Country::find($c)->name }} : {{ $up }}% <p/>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    @if(!empty($item->complex))
                        <div class="form-group triggers-wrapper col-md-12">
                            <h3 style="display: inline-block; margin-right: 20px;">Calculating survey max DCN reward <input type="checkbox" name="manually_calc_reward" id="manually-calc-reward" value="1" {!! !empty($item->manually_calc_reward) ? 'checked="checked"' : '' !!}></h3> <a href="javascript:;" class="btn btn-primary triggers-button">Show triggers</a>

                            @if(empty($item->manually_calc_reward))
                                <div class="alert alert-danger" style="display: inline-block;">Not calculated reward</div>
                            @endif
                            <br/>                            

                            <div class="calculating-wrapper" style="display: none;">
                                <!-- @if(!empty($item->dcn_questions_count))
                                    <p>Calculated {{ $item->dcn_questions_count }} questions from {{ $item->questions->count() }} original questions</p>
                                @endif -->
                                @foreach($q_trigger_obj as $iq)
                                    @if(!empty($iq))
                                        @if(is_object($iq) && $iq->type == 'multiple_choice')
                                            <div class="col-md-12" style="display: none;">
                                                <select name="count_dcn_questions[]" class="form-control col" style="flex:1;">
                                                    <option value="{{ $iq->id }}">{{ $iq->question }}</option>
                                                </select>
                                                <input type="text" name="count_dcn_answers[]" value="{!! $q_trigger_multiple_answ[$iq->id] !!}">
                                            </div>
                                        @else
                                            <div class="col-md-12" style="display: flex;">
                                                <select name="count_dcn_questions[]" class="form-control col" style="flex:1;">
                                                    <option value="{{ is_object($iq) ? $iq->id : $iq }}">{{ is_object($iq) ? $iq->question : ($iq == 'age_groups' ? 'Age groups' : ( $iq == 'gender' ? 'Gender' : config('vox.details_fields.'.$iq)['label'])) }}</option>
                                                </select>
                                                <select name="count_dcn_answers[]" class="form-control col" style="flex:1;">
                                                    <option value="">-</option>
                                                    @if(is_object($iq))
                                                        @if($iq->type == 'number')
                                                            @for($i=explode(':', $iq->number_limit)[0];$i<=explode(':', $iq->number_limit)[1]; $i++)
                                                                <option value="{{ $i }}" {!! !empty($item->dcn_questions_triggers) && array_key_exists($iq->id, $item->dcn_questions_triggers) && (intval($item->dcn_questions_triggers[$iq->id]) == ($i) ) ? 'selected="selected"' : '' !!}>{{ $i }}</option>
                                                            @endfor
                                                        @else 
                                                            @foreach($iq->vox_scale_id && !empty($scales_arr[$iq->vox_scale_id]) ? explode(',', $scales_arr[$iq->vox_scale_id]->answers) :  json_decode($iq->answers, true) as $key => $ans)
                                                                <option value="{{ $key + 1 }}" {!! !empty($item->dcn_questions_triggers) && array_key_exists($iq->id, $item->dcn_questions_triggers) && (intval($item->dcn_questions_triggers[$iq->id]) == ($key + 1) ) ? 'selected="selected"' : '' !!}>{{ $ans }}</option>
                                                            @endforeach
                                                        @endif
                                                    @else
                                                        @if($iq == 'gender')
                                                            <option value="1" {!! !empty($item->dcn_questions_triggers) && array_key_exists($iq, $item->dcn_questions_triggers) && (intval($item->dcn_questions_triggers[$iq]) == 1 ) ? 'selected="selected"' : '' !!}>Male</option>
                                                            <option value="2" {!! !empty($item->dcn_questions_triggers) && array_key_exists($iq, $item->dcn_questions_triggers) && (intval($item->dcn_questions_triggers[$iq]) == 2 ) ? 'selected="selected"' : '' !!}>Female</option>
                                                        @elseif($iq == 'age_groups')
                                                            @foreach(config('vox.age_groups') as $key => $val)
                                                                <option value="{{ $loop->iteration }}" {!! !empty($item->dcn_questions_triggers) && array_key_exists($iq, $item->dcn_questions_triggers) && (intval($item->dcn_questions_triggers[$iq]) == $loop->iteration ) ? 'selected="selected"' : '' !!}>{{ $val }}</option>
                                                            @endforeach
                                                        @else
                                                            @foreach(config('vox.details_fields.'.$iq.'.values') as $key => $val)
                                                                <option value="{{ $loop->iteration }}" {!! !empty($item->dcn_questions_triggers) && array_key_exists($iq, $item->dcn_questions_triggers) && (intval($item->dcn_questions_triggers[$iq]) == $loop->iteration ) ? 'selected="selected"' : '' !!}>{{ $val }}</option>
                                                            @endforeach
                                                        @endif
                                                    @endif
                                                </select>
                                            </div>
                                        @endif
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="form-group">
                        <div class="col-md-12">
                            <a href="javascript:;" id="generate-stats" class="btn btn-primary btn-block">{{ empty($item) ? trans('admin.page.'.$current_page.'.new.submit') : trans('admin.page.'.$current_page.'.edit.submit') }} </a>
                            <button type="submit" class="btn btn-primary btn-block" style="display: none;">{{ empty($item) ? trans('admin.page.'.$current_page.'.new.submit') : trans('admin.page.'.$current_page.'.edit.submit') }}</button>
                        </div>
                    </div>
                </div>

            </div>

        {{ Form::close() }}


        @if(!empty($item))

            @if($item->questions->isNotEmpty())
                <h3>Questions</h3>

                <p>
                    Hints: <br/>

                    For bulk delete you need to check the checkboxes, then click button 'Delete selected questions'. <br/>
                    For multiple re-arrange - hold the CTRL button and click on the questions. <br/>
                </p>
                <div class="panel panel-inverse">
                    <div class="panel-heading">
                        <h4 class="panel-title">{{ trans('admin.page.'.$current_page.'.questions') }}</h4>
                    </div>
                    <div class="tab-content">
                        <form method="post" action="{{ url('cms/vox-questions/mass-delete') }}" class="table-responsive-md" id="mass-delete-form">
                            <table class="table table-striped table-question-list">
                                <thead>
                                    <tr>
                                        <th><a href="javascript:;" class="table-select-all">All / None</a></th>
                                        <th>{{ trans('admin.page.'.$current_page.'.question-num') }}</th>
                                        <th>{{ trans('admin.page.'.$current_page.'.question-title') }}</th>
                                        <th>{{ trans('admin.page.'.$current_page.'.question-control') }}</th>
                                        <th>{{ trans('admin.page.'.$current_page.'.question-stats') }}</th>
                                        <th>{{ trans('admin.page.'.$current_page.'.question-type') }}</th>
                                        <th>{{ trans('admin.page.'.$current_page.'.question-trigger') }}</th>
                                        <th>Respondents</th>
                                        <th>Test question</th>
                                        <th>Duplicate</th>
                                        <th>{{ trans('admin.page.'.$current_page.'.question-edit') }}</th>
                                        <th>{{ trans('admin.page.'.$current_page.'.question-delete') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="questions-draggable">
                                    @foreach($item->questions as $question)
                                        <tr question-id="{{ $question->id }}" {!! in_array($question->id, $linked_triggers) ? 'class="linked"' : '' !!}>
                                            <td>
                                                <input type="checkbox" name="ids[]" value="{{ $question->id }}" />
                                            </td>
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
                                                    Related to: {!! $question->related->question !!}
                                                @endif
                                            </td>
                                            <td>{{ trans('admin.enums.question-type.'.$question->type) }}</td>
                                            <td>{!! $triggers[$question->id] !!}</td>
                                            <td>
                                                <a href="{{ url('cms/vox/explorer/'.$item->id.'/'.$question->id) }}" target="_blank">
                                                    {!! $question->respondent_count() !!}
                                                </a>
                                            </td>
                                            <td>
                                                @if(empty($question->question_trigger) && $question->order != 1)
                                                    <a class="btn btn-sm btn-info" href="{{ $item->getLink().'?testmode=1&start-from='.$question->id.'&q-id='.(!empty(App\Models\VoxQuestion::where('vox_id', $question->vox_id)->where('order', $question->order -1)->first()) ? App\Models\VoxQuestion::where('vox_id', $question->vox_id)->where('order', $question->order -1)->first()->id : $question->id) }}" target="_blank">
                                                        Test
                                                    </a>
                                                @endif
                                            </td>
                                            <td>
                                                <a class="btn btn-sm btn-success diplicate-q-button" href="javascript:;" q-id="{{ $question->id }}" data-toggle="modal" data-target="#duplicateModal">
                                                    <i class="fa fa-paste"></i>
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
                            <button type="submit" name="mass-delete" value="1" class="btn btn-block btn-primary" id="mass-delete-button">Delete selected questions</button>
                        </form>
                    </div>
                </div>

                <style type="text/css">
                    table tr td:nth-child(7) {
                        word-break: break-all;
                    }
                </style>
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
                                <a href="{{ url('survey-import-template.xlsx') }}">Download sample</a> (also hints for Prevous question)
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

<div id="duplicateModal" class="modal fade" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Dupicate question</h4>
            </div>
            <div class="modal-body">
                <form action="{{ url('cms/'.$current_page.'/duplicate-question') }}" method="post">
                    Pick survey:
                    <select class="form-control select2" name="duplicate-question-vox" style="width: 100%;">
                        <option value="">Select survey</option>
                        @foreach($all_voxes as $survey)
                            <option value="{{ $survey->id }}" {!! !empty($item) && $survey->id==$item->id ? 'selected="selected"' : '' !!}>{{ $survey->title }}</option>
                        @endforeach
                    </select>
                    <input type="hidden" name="d-question" id="d-question" value="">
                    <input type="hidden" name="current-vox" id="current-vox" value="{{ !empty($item) ? $item->id : '' }} ">
                    <button type="submit" class="btn btn-primary btn-block" style="margin-top: 20px;">Save</button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>

    </div>
</div>

@if(!empty($item) && $item->questions->isNotEmpty())
    <div id="exportStats" class="modal fade" role="dialog">
        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Export stats</h4>
                </div>
                <div class="modal-body">
                    <form method="post" action="{{ url('cms/vox/export-stats') }}" id="export-stats-form">
                        {!! csrf_field() !!}
                        <div class="form-group clearfix" id="stat_standard" style="margin-top: 10px;">
                            <input type="hidden" name="vox-id" value="{{ $item->id }}">
                            <select class="form-control select2type" multiple name="chosen-qs[]">
                                @foreach($item->questions as $qq)
                                    <option value="{{ $qq->id }}">{{ $qq->question }}</option>
                                @endforeach
                            </select>

                            <br/>
                            <br/>

                            <label class="col-md-2 control-label">Demographics</label>
                            <div class="col-md-10">
                                <label for="demographics-relation">
                                    <input type="checkbox" name="demographics[]" value="relation" id="demographics-relation" style="vertical-align: sub;" />
                                    Relation &nbsp;&nbsp;&nbsp;&nbsp;
                                </label>
                                @foreach( config('vox.stats_scales') as $k => $v)
                                    <label for="demographics-{{ $k }}">
                                        <input type="checkbox" name="demographics[]" value="{{ $k }}" id="demographics-{{ $k }}" style="vertical-align: sub;" />
                                        {{ trans('vox.page.stats.group-by-'.$k) }} &nbsp;&nbsp;&nbsp;&nbsp;
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-sm btn-success btn-block">Submit</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>

        </div>
    </div>
@endif


@if(!empty($error))
    <div id="errorsModal" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Errors</h4>
                </div>
                <div class="modal-body">
                    @foreach($error_arr as $key => $value)
                        {{ $key+1 }}. <a href="{!! isset($value['link']) ? $value['link'] : 'javascript:;'  !!}" {!! isset($value['link']) ? 'target="_blank"' : '' !!}>{{ $value['error'] }}</a><br/>
                    @endforeach

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>

        </div>
    </div>
@endif

@endsection