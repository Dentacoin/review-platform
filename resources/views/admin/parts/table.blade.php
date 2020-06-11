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
                                @elseif($k=='checkboxes')
                                    <a href="javascript:;" class="table-select-all">All / None</a>
                                @elseif(!empty($v['label']))
                                    @if(!empty($v['order']))
                                        @if( !request()->input( $v['orderKey'] ) )

                                            <a href="{{ !empty($current_url) ? $current_url.'?&'.$v['orderKey'].'=asc'.(!empty($show_all) ? '&show_all=1' : '') : 'javascript:;' }}" class="order">{{ $v['label'] }}</a>
                                        @elseif( request()->input( $v['orderKey'] )=='desc' )
                                            <a href="{{ !empty($current_url) ? $current_url.'?&'.$v['orderKey'].'=asc'.(!empty($show_all) ? '&show_all=1' : '') : 'javascript:;' }}" class="order asc">{{ $v['label'] }}</a>
                                        @else
                                            <a href="{{ !empty($current_url) ? $current_url.'?&'.$v['orderKey'].'=desc'.(!empty($show_all) ? '&show_all=1' : '') : 'javascript:;' }}" class="order desc">{{ $v['label'] }}</a>
                                        @endif

                                    @else
                                        {{ $v['label'] }}
                                    @endif
                                @else
                                    @if(!empty($v['order']))
                                        <a href="javascript:;" class="order">{{ trans('admin.page.'.$current_page.'.table.'.$k) }}</a>
                                    @else
                                        {{ trans('admin.page.'.$current_page.'.table.'.$k) }}
                                    @endif
                                @endif
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                	@foreach($table_data as $row)
                    	<tr {!! !empty($row->id) ? 'item-id="'.$row->id.'"' : '' !!} {!! !empty($row->deleted_at) ? 'style="opacity: 0.7;"' : '' !!}>
                    		@foreach($table_fields as $k => $v)
                                @if(!empty($v['template']))
                                    <td {!! !empty($v['width']) ? 'style="width:'.$v['width'].'"' : '' !!}>@include($v['template'], array('item' => $row) )</td>
                                @elseif(!empty($v['format']))
                                    @if($v['format']=='selector')
                                        <td>
                                            @if(empty($row->deleted_at))
                                                @if($row->user)
                                                    <input type="checkbox" name="ids[]" value="{{ $row->user->id }}" />
                                                @else
                                                    <input type="checkbox" name="ids[]" value="{{ $row->id }}" />
                                                @endif
                                            @endif
                                        </td>
                                    @elseif($v['format']=='checkboxes')
                                        <td>
                                            <input type="checkbox" name="ids[]" value="{{ $row->id }}" />
                                        </td>
                                    @elseif($v['format']=='update')
                                        <td>
                                            <a class="btn btn-sm btn-primary" href="{{ url('cms/'.$current_page.( !empty($current_subpage) ? '/'.$current_subpage : '' ).'/edit/'.$row->id) }}">
                                                <i class="fa fa-pencil"></i>
                                            </a>
                                        </td>
                                    @elseif($v['format']=='delete')
                                        @if(!empty($row->deleted_at))
                                            <td><a class="btn btn-sm btn-deafult" href="{{ url('cms/'.$current_page.( !empty($current_subpage) ? '/'.$current_subpage : '' ).'/restore/'.$row->id) }}">{{ trans('admin.table.restore') }}</a></td>
                                        @else
                                            <td><a class="btn btn-sm btn-deafult" href="{{ url('cms/'.$current_page.( !empty($current_subpage) ? '/'.$current_subpage : '' ).'/delete/'.$row->id) }}" onclick="return confirm('Are you sure you want to DELETE this?');">{{ trans('admin.table.delete') }}</a></td>
                                        @endif
                                    @elseif($v['format']=='date')
                                        <td {!! !empty($v['width']) ? 'style="width:'.$v['width'].'"' : '' !!}>{{ !empty($row[$k]->timestamp) && $row[$k]->timestamp>0 ? date('d.m.Y', $row[$k]->timestamp) : trans('admin.table.na') }}</td>
                                    @elseif($v['format']=='datetime')

                                        <td {!! !empty($v['width']) ? 'style="width:'.$v['width'].'"' : '' !!}>
                                            
                                            @if(count(explode('.', $k))==2)
                                                {{ !empty($row[explode('.', $k)[0]][explode('.', $k)[1]]) ? date('d.m.Y, H:i:s', $row[explode('.', $k)[0]][explode('.', $k)[1]]->timestamp) : '' }}
                                            @else
                                                {{ !empty($row[$k]->timestamp) && $row[$k]->timestamp>0 ? date('d.m.Y, H:i:s', $row[$k]->timestamp) : trans('admin.table.na') }}
                                            @endif
                                        </td>
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
                                            @if(count(explode('.', $k))==2)
                                                {{ !empty($row[explode('.', $k)[0]][explode('.', $k)[1]]) ? App\Models\Country::find($row[explode('.', $k)[0]][explode('.', $k)[1]])->name : '' }}
                                            @else
                                                {{ !empty($row[$k]) ? App\Models\Country::find($row[$k])->name : '' }}
                                            @endif
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
                                    <td {!! !empty($v['width']) ? 'style="width:'.$v['width'].'"' : '' !!}>{{ $row[$k] }}</td>
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