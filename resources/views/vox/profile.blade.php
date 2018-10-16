@extends('vox')

@section('content')

	<div class="container">
		<div class="col-md-3">
			@include('vox.template-parts.profile-menu')
		</div>
		<div class="col-md-9">
			<h2 class="page-title">
				<img src="{{ url('new-vox-img/profile-home.png') }}" />
				Overview
			</h2>


			<div class="form-horizontal profile-home-content">
				<h3>
					Your Dentacoin Balance
				</h3>
				<div class="balance">
					<div>
						<b><span class="dcn-amount">{{ $user->getVoxBalance() }}</span> DCN</b>
						<div class="convertor">
							= <span class="convertor-value"></span>
							<span class="convertor-currnecy">
								<span class="active-currency">
									USD
								</span>

								<div class="expander">
									@foreach( config('currencies') as $currency )
										<a currency="{{ $currency }}" {!! $currency=='USD' ? 'class="active"' : '' !!}>{{ $currency }}</a>
									@endforeach
								</div>
							</span>
						</div>
					</div>
					<div>
                        <a href="{{ getLangUrl('profile/wallet') }}" class="btn btn-block btn-primary form-control">
                            Your Dentacoin Wallet
                        </a>
					</div>
				</div>
			</div>

		</div>
	</div>

	<script type="text/javascript">
		var currency_rates = {!! $currencies !!};
		
	</script>

@endsection