@extends('admin')

@section('content')

    <h1 class="page-header">Vox Badges</h1>

    <div class="panel-group">
        @foreach( $items as $item )
        <div class="panel panel-default main-panel">
            <div class="panel-heading" role="tab" id="">
                <h4 class="panel-title">
                    {{ $item-> name }}
                </h4>
            </div>
            <div class="panel-collapse collapse in">
                <div class="panel-body">
                    {{ Form::open(array('class' => 'form-horizontal', 'method' => 'post', 'files' => true)) }}
                        <input type="hidden" name="id" value="{{ $item->id }}">

                        <div class="form-group">
                            <label for="featured" class="col-md-3 control-label" style="padding-top: 0px;">Badge Image</label>
                            <div class="col-md-9">
                                {{ Form::file('photo', ['id' => 'photo', 'accept' => 'image/gif, image/jpg, image/jpeg, image/png']) }}<br/>
                                * PNG file with transparency, up to 2 MB, will be placed on 1200x628px canvas<br/>
                                @if(file_exists( $item->getImagePath() ))
                                    <a target="_blank" href="{{ $item->getImageUrl() }}">
                                        <img src="{{ $item->getImageUrl() }}" style="width: 200px;" />
                                    </a>
                                    &nbsp;
                                    &nbsp;
                                    &nbsp;
                                    <a href="{{ url('cms/'.$current_page.'/badges/delete/'.$item->id) }}" onclick="return confirm('Are you sure?');">
                                        Delete badge
                                    </a>
                                @endif
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-sm btn-success btn-block">Upload</button>
                            </div>
                        </div>

                    {{ Form::close() }}
                </div>
            </div>
        </div>
        @endforeach
    </div>

@endsection