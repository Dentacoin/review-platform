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
						<p>Total count: {{ $total_count }}</p>

		                <table class="table table-striped table-question-list">
		                    <thead>
		                        <tr>
		                            <th>ID</th>
		                            <th>Date</th>
		                            <th>Report</th>
		                            <th>Email</th>
		                            <th>Name</th>
		                            <th>Invoice Info</th>
		                            <th>Payment</th>
		                            <th>Price</th>
		                            <th>Payment Info</th>
		                            <th>Status</th>
		                        </tr>
		                    </thead>
		                    <tbody>
		                        @foreach($orders as $order)
		                            <tr>
		                                <td>
		                                    {{ $order->id }}
		                                </td>
		                                <td>
		                                    {{ $order->created_at->toDateTimeString() }}
		                                </td>
		                                <td>
		                                    <a href="{{ url('cms/vox/paid-reports/edit/'.$order->report->id) }}" target="_blank"> {{ $order->report->title }}</a>
		                                </td>
		                                <td>
		                                    {{ $order->email }}
		                                </td>
		                                <td>
		                                    {{ $order->name }}
		                                </td>
		                                <td>
                                            @if($order->company_name)
		                                        Company Name: {{ $order->company_name }} <br/>
		                                        Reg №: {{ $order->company_number }} <br/>
		                                        Country: {{ $order->country_id ? $order->country->name : '' }} <br/>
		                                        Address: {{ $order->address }} <br/>
		                                        VAT: {{ $order->vat }} <br/>
                                            @else
                                                -
                                            @endif
		                                </td>
		                                <td>
		                                    {{ config('payment-methods')[$order->payment_method] }}
		                                </td>
		                                <td>
		                                    {{ $order->price_with_currency }}
		                                </td>
		                                <td class="order-{{ $order->id }}">
		                                    {!! nl2br($order->payment_info) !!}
		                                </td>
		                                <td>
                                            @if($order->is_send)
												Sent
                                            @else
                                                <a class="btn btn-sm btn-success" href="{{ url('cms/orders/sended/'.$order->id) }}">
                                                    Mark as sent
                                                </a>
                                            @endif
											<br/>
											<a class="btn btn-sm btn-info add-payment-info" href="javascript:;" style="margin-top: 5px;" data-toggle="modal" data-target="#paymentModal" order-id="{{ $order->id }}" payment-info="{{ $order->payment_info }}">
												Add payment info
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

	<div id="paymentModal" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<!-- Modal content-->
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h4 class="modal-title">Payment Info</h4>
				</div>
				<div class="modal-body">
					<form class="payment-info-form" action="{{ url('cms/orders/add-payment-info/') }}" original-action="{{ url('cms/orders/add-payment-info/') }}" method="post" order-id="">
						<textarea style="min-height: 100px;" class="form-control" name="payment-info"></textarea>
						<button type="submit" class="btn btn-primary btn-block" style="margin-top: 20px;">Submit</button>
					</form>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				</div>
			</div>
	
		</div>
	</div>

@endsection