@extends('admin')

@section('content')

<h1 class="page-header">
    Orders
</h1>
<!-- end page-header -->

	{{ Form::open(array('class' => 'form-horizontal', 'method' => 'post')) }}
		<div class="row">
		    <div class="col-md-12">
		        <div class="panel panel-inverse">
		            <div class="panel-heading">
		                <h4 class="panel-title">Orders</h4>
		            </div>
		            <div class="tab-content">

		                <table class="table table-striped table-question-list">
		                    <thead>
		                        <tr>
		                            <th>ID</th>
		                            <th>Report</th>
		                            <th>Email</th>
		                            <th>Invoice Info</th>
		                            <th>Payment method</th>
		                            <th>Price</th>
		                            <th>Sended</th>
		                        </tr>
		                    </thead>
		                    <tbody>
		                        @foreach($orders as $order)
		                            <tr>
		                                <td>
		                                    {{ $order->id }}
		                                </td>
		                                <td>
		                                    <a href="{{ url('cms/vox/paid-reports/edit/'.$order->report->id) }}" target="_blank"> {{ $order->report->title }}</a>
		                                </td>
		                                <td>
		                                    {{ $order->email }}
		                                </td>
		                                <td>
                                            @if($order->company_name)
		                                        Company Name: {{ $order->company_name }} <br/>
		                                        Reg №: {{ $order->company_number }} <br/>
		                                        Country: {{ App\Models\Country::find($order->country_id)->name }} <br/>
		                                        Address: {{ $order->address }} <br/>
		                                        VAT: {{ $order->vat }} <br/>
                                            @else
                                                -
                                            @endif
		                                </td>
		                                <td>
		                                    {{ $order->payment_method }}
		                                </td>
		                                <td>
		                                    {{ $order->price }}$
		                                </td>
		                                <td>
                                            @if($order->is_send)
                                                Yes
                                            @else
                                                <a class="btn btn-sm btn-success" href="{{ url('cms/orders/sended/'.$order->id) }}">
                                                    Sended
                                                </a>
                                            @endif
		                                </td>
		                            </tr>
		                        @endforeach
		                    </tbody>
		                </table>
		            </div>
		        </div>
		    </div>
		</div>
	{{ Form::close() }}

	@if($total_pages > 1)
        <nav aria-label="Page navigation" style="text-align: center;">
            <ul class="pagination">
                <li class="{{ ($page <= 1 ?  'disabled' : '' ) }}">
                    <a class="page-link" href="{{ url('cms/orders/?page=1'.$pagination_link) }}" aria-label="Previous">
                        <span aria-hidden="true"> << </span>
                    </a>
                </li>
                <li class="{{ ($page <= 1 ?  'disabled' : '' ) }}">
                    <a class="page-link prev" href="{{ url('cms/orders/?page='.($page>1 ? $page-1 : '1').$pagination_link) }}"  aria-label="Previous">
                        <span aria-hidden="true"> < </span>
                    </a>
                </li>
                @for($i=$start; $i<=$end; $i++)
                    <li class="{{ ($i == $page ?  'active' : '') }}">
                        <a class="page-link" href="{{ url('cms/orders/?page='.$i.$pagination_link) }}">{{ $i }}</a>
                    </li>
                @endfor
                <li class="{{ ($page >= $total_pages ? 'disabled' : '') }}">
                    <a class="page-link next" href="{{ url('cms/orders/?page='.($page < $total_pages ? $page+1 :  $total_pages).$pagination_link) }}" aria-label="Next"> <span aria-hidden="true"> > </span> </a>
                </li>
                <li class="{{ ($page >= $total_pages ? 'disabled' : '') }}">
                    <a class="page-link" href="{{ url('cms/orders/?page='.$total_pages.$pagination_link) }}" aria-label="Next"> <span aria-hidden="true"> >> </span>  </a>
                </li>
            </ul>
        </nav>
    @endif

@endsection