@extends('admin')

@section('content')

    <h1 class="page-header">Testimonial slider</h1>

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <div class="panel-heading-btn">
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                    </div>
                    <h4 class="panel-title">Testimonial slider</h4>
                </div>
                <div class="panel-body">
                    <div class="row table-responsive-md">
                        <table class="table table-striped table-question-list">
                            <thead>
                                <tr>
                                    <th>Order</th>
                                    <th>Image</th>
                                    <th>Name</th>
                                    <th>Position</th>
                                    <th>Quote</th>
                                    <th>Edit</th>
                                    <th>Delete</th>
                                </tr>
                            </thead>
                            <tbody class="questions-draggable" url="{{ url('cms/trp/testimonials/reorder') }}">
                                @foreach($testimonials as $testimonial)
                                    <tr question-order="{{ $testimonial->order }}" question-id="{{ $testimonial->id }}" >
                                        
                                        <td class="question-number">
                                            {{ $testimonial->order_dentist }}
                                        </td>
                                        <td>
                                            <img style="max-width: 50px;border-radius: 50%;" src="{{ $testimonial->getImageUrl() }}">
                                        </td>
                                        <td>
                                            {{ $testimonial->name }}
                                        </td>
                                        <td>
                                            {{ $testimonial->job }}
                                        </td>
                                        <td>
                                            {{ $testimonial->description }}
                                        </td>
                                        <td>
                                            <a class="btn btn-sm btn-primary" href="{{ url('cms/trp/testimonials/edit/'.$testimonial->id) }}">
                                                <i class="fa fa-pencil"></i>
                                            </a>
                                        </td>
                                        <td>
                                            <a class="btn btn-sm btn-deafult" href="{{ url('cms/trp/testimonials/delete/'.$testimonial->id) }}" onclick="return confirm('Are you sure you want to DELETE this?');">
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

    <div class="row">
        <div class="col-md-12">
            <form class="form-horizontal" id="admin-add" method="post" enctype= multipart/form-data action="{{ url('cms/trp/testimonials/add') }}">
                {!! csrf_field() !!}

                <div class="panel panel-inverse panel-with-tabs" data-sortable-id="ui-unlimited-tabs-1">
                    <div class="panel-heading p-0">
                        <div class="panel-heading-btn m-r-10 m-t-10">
                            <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-success" data-click="panel-expand" data-original-title="" title=""><i class="fa fa-expand"></i></a>
                        </div>
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
                                    <label class="col-md-2 control-label" style="max-width: 200px;">Name</label>
                                    <div class="col-md-10">
                                        {{ Form::text('name-'.$code, null, array('maxlength' => 128, 'class' => 'form-control')) }}
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-2 control-label" style="max-width: 200px;">Position</label>
                                    <div class="col-md-10">
                                        {{ Form::text('job-'.$code, null, array('maxlength' => 128, 'class' => 'form-control')) }}
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-2 control-label" style="max-width: 200px;">Quote</label>
                                    <div class="col-md-10">
                                        {{ Form::textarea('description-'.$code, null, array('maxlength' => 2048, 'class' => 'form-control')) }}
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-2 control-label" style="max-width: 200px;">Alt image text</label>
                                    <div class="col-md-6">
                                        {{ Form::text('alt_image_text-'.$code, null, array('maxlength' => 128, 'class' => 'form-control')) }}
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        <div class="form-group">
                            <label class="col-md-2 control-label" style="max-width: 200px;">Image</label>
                            <div class="col-md-10">
                                <input type="file" name="image" class="form-control" />
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-2" style="max-width: 200px;"></div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-block btn-success">Add</button>
                            </div>                        
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <div class="panel-heading-btn">
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"></a>
                    </div>
                    <h4 class="panel-title">Export Translations</h4>
                </div>
                <div class="panel-body">
                    <form class="form-horizontal" method="post" action="{{ url('cms/trp/testimonials/export') }}">
                        {!! csrf_field() !!}
                        <div class="form-group">
                            <div class="col-md-12">
                                Use this feature to download a translation table. You will receive a three column file. <br/>
                                Don't edit anything in the first one. <br/>
                                The second contains phrases in the language "FROM". <br/>
                                In the <b>third column</b>, complete the translations. If there are any, they will be filled in there so you can easily edit them. <br/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-4 control-label">From</label>
                            <div class="col-md-8">
                                <select class="form-control" name="from">
                                    @foreach($langs as $key => $lang_info)
                                        <option value="{{ $key }}">{{ $lang_info['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-4 control-label">To</label>
                            <div class="col-md-8">
                                <select class="form-control" name="to">
                                    @foreach($langs as $key => $lang_info)
                                        <option value="{{ $key }}">{{ $lang_info['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-12">
                                <button type="submit" name="export" value="1" class="btn btn-block btn-success">Download</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <div class="panel-heading-btn">
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"></a>
                    </div>
                    <h4 class="panel-title">Import Translations</h4>
                </div>
                <div class="panel-body">
                    <form class="form-horizontal" method="post" enctype="multipart/form-data" action="{{ url('cms/trp/testimonials/import') }}">
                        {!! csrf_field() !!}
                        <div class="form-group">
                            <div class="col-md-12">
                                Select a translation file (previously generated by the system). <br/>
                                Then select the languages ​​that were <b>used when exporting</b> the template. <br/>
                                Check again just in case and click the "Upload" button. <br/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-4 control-label">From</label>
                            <div class="col-md-8">
                                <select class="form-control" name="source">
                                    @foreach($langs as $key => $lang_info)
                                        <option value="{{ $key }}">{{ $lang_info['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-4 control-label">To</label>
                            <div class="col-md-8">
                                <select class="form-control" name="from">
                                    @foreach($langs as $key => $lang_info)
                                        <option value="{{ $key }}" >{{ $lang_info['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-4 control-label">Choose file</label>
                            <div class="col-md-8">
                                <input type="file" name="table" accept=".xls" />
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-12">
                                <button type="submit" name="import" class="btn btn-block btn-success" value="1">Upload</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection