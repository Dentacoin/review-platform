@extends('vox')

@section('content')

	<div class="container daily-polls-wrapper">
		{!! csrf_field() !!}

		<a class="back-home" href="{{ getLangUrl('/') }}">
			{!! nl2br(trans('vox.daily-polls.popup.back')) !!}
		</a>
		<h1>{!! nl2br(trans('vox.daily-polls.title')) !!}</h1>
		
		<div id="calendar" data-link="{{ getLangUrl('get-polls') }}"></div>

		<div class="monthly-description tac" style="{!! !empty($monthly_descr) ? '' : 'display:none;' !!}">
			<div class="container">
				<h2>{!! nl2br(trans('vox.daily-polls.monthly-polls')) !!}</h2>
				<p>
					@if(!empty($monthly_descr))
						{{ $monthly_descr->description }}
					@endif
				</p>
			</div>
		</div>

		@if(!empty($date_poll))
			<script type="text/javascript">
				var go_to_date = '{!! $date_poll !!}';
				var go_to_month = '{!! $poll_month !!}';
				var go_to_year = '{!! $poll_year !!}';
				var cur_date = '{!! date("Y-m-d") !!}';
				@if(!empty($poll_stats))
					var poll_stats = true;
				@endif
			</script>
		@endif

		@if(false)
			@php
				$calendar = new App\Helpers\Calendar();
			@endphp

			{!! $calendar->show() !!}

			<style type="text/css">
				div#poll-calendar{
					margin:0px auto;
					padding:0px;
					width: 602px;
					font-family:Helvetica, "Times New Roman", Times, serif;
					}
					
					div#poll-calendar div.box{
						position:relative;
						top:0px;
						left:0px;
						width:100%;
						height:40px;
						background-color:   #787878 ;      
					}
					
					div#poll-calendar div.header{
						line-height:40px;  
						vertical-align:middle;
						position:absolute;
						left:11px;
						top:0px;
						width:582px;
						height:40px;   
						text-align:center;
					}
					
					div#poll-calendar div.header a.prev,div#poll-calendar div.header a.next{ 
						position:absolute;
						top:0px;   
						height: 17px;
						display:block;
						cursor:pointer;
						text-decoration:none;
						color:#FFF;
					}
					
					div#poll-calendar div.header span.title{
						color:#FFF;
						font-size:18px;
					}
					
					
					div#poll-calendar div.header a.prev{
						left:0px;
					}
					
					div#poll-calendar div.header a.next{
						right:0px;
					}
					
					
					
					
					/*******************************Calendar Content Cells*********************************/
					div#poll-calendar div.box-content{
						border:1px solid #787878 ;
						border-top:none;
					}
					
					
					
					div#poll-calendar ul.label{
						float:left;
						margin: 0px;
						padding: 0px;
						margin-top:5px;
						margin-left: 5px;
					}
					
					div#poll-calendar ul.label li{
						margin:0px;
						padding:0px;
						margin-right:5px;  
						float:left;
						list-style-type:none;
						width:80px;
						height:40px;
						line-height:40px;
						vertical-align:middle;
						text-align:center;
						color:#000;
						font-size: 15px;
						background-color: transparent;
					}
					
					
					div#poll-calendar ul.dates{
						float:left;
						margin: 0px;
						padding: 0px;
						margin-left: 5px;
						margin-bottom: 5px;
					}
					
					/** overall width = width+padding-right**/
					div#poll-calendar ul.dates li{
						margin:0px;
						padding:0px;
						margin-right:5px;
						margin-top: 5px;
						line-height:80px;
						vertical-align:middle;
						float:left;
						list-style-type:none;
						width:80px;
						height:80px;
						font-size:25px;
						background-color: #DDD;
						color:#000;
						text-align:center; 
					}
					
					:focus{
						outline:none;
					}
					
					div.clear{
						clear:both;
					}     
			</style>
		@endif
	</div>
    	
@endsection