<div class="row">
    <div class="col-md-12 ui-sortable">
        {{ Form::open([
            'id' => 'scale-'.( !empty($item) ? 'edit' : 'add') , 
            'url' => url('cms/vox/scales/'.( !empty($item) ? 'edit/'.$item->id : 'add') ), 
            'class' => 'form-horizontal scales-form', 
            'method' => 'post'
        ]) }}        
            {!! csrf_field() !!}

            <div class="panel panel-inverse panel-with-tabs" data-sortable-id="ui-unlimited-tabs-1">
                <div class="panel-heading p-0">
                    <div class="tab-overflow overflow-right">
                        <ul class="nav nav-tabs nav-tabs-inverse">
                            <li class="prev-button">
                                <a href="javascript:;" data-click="prev-tab" class="text-success">
                                    <i class="fa fa-arrow-left"></i>
                                </a>
                            </li>
                            @foreach($langs as $code => $lang_info)
                                <li class="{{ $loop->first ? 'active' : '' }}">
                                    <a href="#nav-tab-{{ $code }}" data-toggle="tab" aria-expanded="false">{{ $lang_info['name'] }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <div class="tab-content">
                    <div class="form-group">
                        <label class="col-md-2 control-label">Name</label>
                        <div class="col-md-10">
                            {{ Form::text('title', !empty($item) ? $item->title : '', array('class' => 'form-control')) }}
                        </div>
                    </div>
                    @foreach($langs as $code => $lang_info)
                        <div class="tab-pane fade{{ $loop->first ? ' active in' : '' }}" id="nav-tab-{{ $code }}">
                            <div class="form-group">
                                <label class="col-md-2 control-label">Answers</label>
                                <div class="col-md-10">
                                    {{ Form::text('answers-'.$code, !empty($item) ? $item->translateOrNew($code)->answers : null, array(
                                        'maxlength' => 256, 
                                        'class' => 'form-control', 
                                        'placeholder' => 'Permanent, Temporary, Removable, Neither ..'
                                    )) }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                    <div class="form-group">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-success btn-block">Save</button>
                        </div>
                    </div>
                </div>

                <div class="alert alert-danger" id="form-err" style="display: none;"></div>
                <div class="alert alert-success" id="form-succ" style="display: none;">Scale Saved</div>
            </div>
        </form>
    </div>
</div>