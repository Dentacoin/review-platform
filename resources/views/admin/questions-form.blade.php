@extends('admin')

@section('content')

    <h1 class="page-header">{{ empty($item) ? 'Add question' : 'Edit question' }}</h1>

    <div class="row">
        <div class="col-md-12 ui-sortable">
            {{ Form::open(array(
                'id' => 'quest-add', 
                'class' => 'form-horizontal', 
                'method' => 'post', 
                'files' => true
            )) }}
                {!! csrf_field() !!}
                
                <div class="panel panel-inverse panel-with-tabs" data-sortable-id="ui-unlimited-tabs-1">
                    <div class="panel-heading p-0">
                        <div class="tab-overflow overflow-right">
                            <ul class="nav nav-tabs nav-tabs-inverse">
                                @foreach($langs as $code => $lang_info)
                                    <li class="{{ $loop->first ? 'active' : '' }}"><a href="#nav-tab-{{ $code }}" data-toggle="tab" aria-expanded="false">{{ $lang_info['name'] }}</a></li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    <div class="tab-content">
                        <div style="display: none">
                            {{ Form::text('type', !empty($item) ? $item->type : 'new', array('class' => 'form-control')) }}
                        </div>
                        @foreach($langs as $code => $lang_info)
                            <div class="lang-tab tab-pane fade{{ $loop->first ? ' active in' : '' }}" data-lang="{{ $code }}" id="nav-tab-{{ $code }}">
                                <div class="form-group">
                                    <label class="col-md-2 control-label">{{ trans('admin.page.'.$current_page.'.label') }}</label>
                                    <div class="col-md-10">
                                        {{ Form::text('label-'.$code, !empty($item) ? $item->{'label:'.$code} : null, array('maxlength' => 256, 'class' => 'form-control')) }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        <div class="form-group">
                            <label class="col-md-9 control-label"></label>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-block btn-success">{{ empty($item) ? 'Add' : 'Save' }}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

@endsection