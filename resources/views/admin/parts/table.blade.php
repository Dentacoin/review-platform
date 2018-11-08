<div class="dataTables_wrapper">
    <div class="row">
    	<div class="col-sm-12">
    		<table class="table table-striped">
                <thead>
                    <tr>
                    	@foreach($table_fields as $k => $v)
                           	<th>
                                @if($k=='selector')
                                    <a href="javascript:;" class="table-select-all">All / None</a>
                                @elseif(!empty($v['label']))
                                    {{ $v['label'] }}
                                @else
                                    {{ trans('admin.page.'.$current_page.'.table.'.$k) }}
                                @endif
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                	@foreach($table_data as $row)
                    	<tr>
                    		@foreach($table_fields as $k => $v)
                                @if(!empty($v['template']))
                                    <td>@include($v['template'], array('item' => $row) )</td>
                                @elseif(!empty($v['format']))
                                    @if($v['format']=='selector')
                                        <td>
                                            <input type="checkbox" name="ids[]" value="{{ $row->id }}" />
                                        </td>
                                    @elseif($v['format']=='update')
                                        <td><a class="btn btn-sm btn-primary" href="{{ url('cms/'.$current_page.( !empty($table_subpage) ? '/'.$table_subpage : '' ).'/edit/'.$row->id) }}">{{ trans('admin.table.edit') }}</a></td>
                                    @elseif($v['format']=='delete')
                                        @if(!empty($row->deleted_at))
                                            <td><a class="btn btn-sm btn-deafult" href="{{ url('cms/'.$current_page.( !empty($table_subpage) ? '/'.$table_subpage : '' ).'/restore/'.$row->id) }}">{{ trans('admin.table.restore') }}</a></td>
                                        @else
                                            <td><a class="btn btn-sm btn-deafult" href="{{ url('cms/'.$current_page.( !empty($table_subpage) ? '/'.$table_subpage : '' ).'/delete/'.$row->id) }}">{{ trans('admin.table.delete') }}</a></td>
                                        @endif
                                    @elseif($v['format']=='date')
                                        <td>{{ !empty($row[$k]->timestamp) && $row[$k]->timestamp>0 ? date('d.m.Y', $row[$k]->timestamp) : trans('admin.table.na') }}</td>
                                    @elseif($v['format']=='datetime')
                                        <td>{{ !empty($row[$k]->timestamp) && $row[$k]->timestamp>0 ? date('d.m.Y, H:i:s', $row[$k]->timestamp) : trans('admin.table.na') }}</td>
                                    @elseif($v['format']=='bool')
                                        <td>{!! $row[$k] ? '<span class="label label-success">'.trans('admin.common.yes').'</span>' : '<span class="label label-warning">'.trans('admin.common.no').'</span>' !!}</td>
                                    @elseif($v['format']=='set')
                                        <td>
                                            @foreach($row[$k] as $setval)
                                                @if(!empty($setval))
                                                    {{ trans('admin.enums.'.( !empty($v['enum_name']) ? $v['enum_name'] : $k ).'.'.$setval) }} 
                                                @endif
                                            @endforeach
                                        </td>
                                    @elseif($v['format']=='city')
                                        <td>
                                            {{ !empty($row[$k]) ? App\Models\City::find($row[$k])->name : '' }}
                                        </td>
                                    @elseif($v['format']=='country')
                                        <td>
                                            {{ !empty($row[$k]) ? App\Models\Country::find($row[$k])->name : '' }}
                                        </td>
                                    @elseif($v['format']=='enum')
                                        <td>
                                            @if(!empty($row[$k]))
                                                {{ trans('admin.enums.'.( !empty($v['enum_name']) ? $v['enum_name'] : $k ).'.'.$row[$k]) }}
                                            @endif
                                        </td>
                                    @elseif($v['format']=='user')
                                        @if($row->$k)
                                            <td><a href="{{ url('cms/users/edit/'.$row->$k->id) }}" target="_blank">{{ $row->$k->name }}</a></td>
                                        @else
                                            <td>-</td>
                                        @endif
                                    @elseif($v['format']=='set')
                                        <td>{{ implode(', ', $row[$k]) }}</td>
                                    @elseif($v['format']=='break-word')
                                        <td style="word-break: break-word;">{{ $row[$k] }}</td>
                                    @endif
                                @elseif(count(explode('.', $k))==2)
                                    <td>{{ $row[explode('.', $k)[0]][explode('.', $k)[1]] }}</td>
                                @else
                                    <td>{{ $row[$k] }}</td>
                                @endif
	                        @endforeach
                    	</tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @if($table_pagination && $table_data->total()>$table_data->count())
    <div class="row">
    	<div class="col-sm-5">
    	</div>
    	<div class="col-sm-7">
    		<div class="dataTables_paginate paging_simple_numbers" id="data-table_paginate">
    			{!! $table_data->appends($pagination_link)->render() !!}
    		</div>
    	</div>
    </div>
    @endif
</div>