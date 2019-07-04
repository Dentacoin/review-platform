<!DOCTYPE html>

<html>
	<head>
	    <meta charset="utf-8" />
	    <title>Personal Data Export</title>
	    <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" name="viewport" />
	    <meta content="" name="description" />
	    <meta content="" name="author" />

	    <style type="text/css">
	    	html, body, div, span, applet, object, iframe, h1, h2, h3, h4, h5, h6, p, blockquote, pre, a, abbr, acronym, address, big, cite, code, del, dfn, em, img, ins, kbd, q, s, samp, small, strike, strong, sub, sup, tt, var, b, u, i, center, dl, dt, dd, ol, ul, li, fieldset, form, label, legend, table, caption, tbody, tfoot, thead, tr, th, td, article, aside, canvas, details, embed, figure, figcaption, footer, header, hgroup, menu, nav, output, ruby, section, summary, time, mark, audio, video {
			    margin: 0;
			    padding: 0;
			    border: 0;
			    font-size: 100%;
			    font: inherit;
			    vertical-align: baseline;
			}

			body {
			    line-height: 1; 
			}

			table {
			    border-collapse: collapse;
			    border-spacing: 0; 
			}

			html {
			    box-sizing: border-box;
			}

			*, *:before, *:after {
			box-sizing: inherit;
			}

			.table {
			    border-collapse: collapse;
			    width: 100%;
			    font-size: 14px;
			    text-align: left;
			    font-family: sans-serif;
			    margin-bottom: 30px;
			}

			table, th, td {
				border: 1px solid #d8d8d8;
				padding: 5px;
				min-width: 200px;
			}

			h1 {
				font-size: 24px;
				font-weight: bold;
			}

			h2 {
				font-size: 18px;
				font-weight: bold;
			}

			b,
			.bold {
				font-weight: bold;
			}

	    </style>
	</head>
    <body>
    	<div class="users-data">
			<table class="table">
				<tr class="row">
					<td colspan="2"> <h1> Personal Data </h1> </td>
				</tr>
				<tr class="row">
					<td colspan="2"> (sent upon user's request) </td>
				</tr>
				<tr class="row">
					<td colspan="2"> {{ date('F d, Y') }} 
					<br/><br/></td>
				</tr>
				@if($user->name)
					<tr class="row">
						<td> <b> Name </b> </td>
						<td> {{ $user->getName() }} </td>
					</tr>
				@endif
				@if($user->hasimage)
					<tr class="row">
						<td> <b> Profile photo </b> </td>
						<td> <img src="{{ $user->id }}.jpg"/> </td>
					</tr>
				@endif
				@if($user->email)
					<tr class="row">
						<td> <b> Email </b> </td>
						<td> {{ $user->email }} </td>
					</tr>
				@endif
				@if($user->phone)
					<tr class="row">
						<td> <b> Phone </b> </td>
						<td> {{ $user->phone }} </td>
					</tr>
				@endif
				@if($user->description)
					<tr class="row">
						<td> <b> Description </b> </td>
						<td> {{ $user->description }} </td>
					</tr>
				@endif
				@if($user->is_dentist)
					<tr class="row">
						<td> <b> Dentist? </b> </td>
						<td> {{ $user->is_clinic ? 'No' : 'Yes' }} </td>
					</tr>
				@endif
				@if($user->website)
					<tr class="row">
						<td> <b> Website </b> </td>
						<td> {{ $user->website }} </td>
					</tr>
				@endif
				@if($user->gender)
					<tr class="row">
						<td> <b> Gender </b> </td>
						<td> {{ $genders[$user->gender] }} </td>
					</tr>
				@endif
				@if($user->birthyear)
					<tr class="row">
						<td> <b> Birth year </b> </td>
						<td> {{ $user->birthyear }} </td>
					</tr>
				@endif
				@if($user->country_id)
					<tr class="row">
						<td> <b> Country </b> </td>
						<td> {{ $user->country->name }} </td>
					</tr>
				@endif
				@if($user->city_name)
					<tr class="row">
						<td> <b> City </b> </td>
						<td> {{ $user->city_name }} </td>
					</tr>
				@endif
				@if($user->address)
					<tr class="row">
						<td> <b> Address </b> </td>
						<td> {{ $user->address }} </td>
					</tr>
				@endif
				@if($user->zip)
					<tr class="row">
						<td> <b> ZIP code </b> </td>
						<td> {{ $user->zip }} </td>
					</tr>
				@endif
				@if($user->fb_id)
					<tr class="row">
						<td> <b> Facebook ID </b> </td>
						<td> {{ $user->fb_id }} </td>
					</tr>
				@endif
				@if($user->created_at)
					<tr class="row">
						<td> <b> Registration date </b> </td>
						<td> {{ $user->created_at->toDateTimeString() }} </td>
					</tr>
				@endif
				@if($user->verified_on)
					<tr class="row">
						<td> <b> Verification date </b> </td>
						<td> {{ $user->verified_on->toDateTimeString() }} </td>
					</tr>
				@endif
				@if($user->civic_id)
					<tr class="row">
						<td> <b> Civic id </b> </td>
						<td> {{ $user->civic_id }} </td>
					</tr>
				@endif
				@if($user->dcn_address)
					<tr class="row">
						<td> <b> Wallet address </b> </td>
						<td> {{ $user->dcn_address }} </td>
					</tr>
				@endif			
				<tr class="row">
					<td> <b> Privacy Policy accepted ? </b> </td>
					<td> {{ $user->gdpr_privacy ? 'Yes' : 'No'  }} </td>
				</tr>
			</table>

			@if($user->vox_rewards->isNotEmpty())
				<table class="table">
					<tr class="row">
						<td colspan="3"> 
						<br/><br/><h2> DentaVox Completed Surveys </h2> </td>
					</tr>
					<tr class="row">
						<td> <b> Date/Time </b> </td>
						<td> <b> Questionnaires </b> </td>
						<td> <b> DCN amount </b> </td>
					</tr>
					@foreach($user->vox_rewards as $reward)
						<tr>						
							<td>{{ $reward->created_at->toDateTimeString() }}</td>
							<td>{{ $reward->vox->title }}</td>
							<td>{{ $reward->reward }}</td>
						</tr>
					@endforeach
				</table>
			@endif

			@if($user->bans->isNotEmpty())
				<table class="table">
					<tr class="row">
						<td colspan="5"> 
						<br/><br/><h2> DentaVox Bans </h2> </td>
					</tr>
					<tr class="row">
						<td> <b> Date/Time </b> </td>
						<td> <b> Expires </b> </td>
						<td colspan="3"> <b> Type </b> </td>
					</tr>
					@foreach($user->bans as $ban)
						<tr>
							<td>{{ $ban->created_at->toDateTimeString() }}</td>
							@if( $ban->expires===null )
								<td>{{ trans('admin.page.'.$current_page.'.title-bans-permanent') }}</td>
							@else
								<td>{{ $ban->expires->toDateTimeString() }}</td>
							@endif
							<td>{{ $ban->type }} </td>
						</tr>
					@endforeach
				</table>
			@endif

			@if($user->history->isNotEmpty())
				<table class="table">
					<tr class="row">
						<td colspan="5"> 
						<br/><br/><h2> DentaVox Transactions </h2> </td>
					</tr>
					<tr class="row">
						<td> <b> Date/Time </b> </td>
						<td> <b> Transaction Hash </b> </td>
						<td> <b> Amounth </b> </td>
						<td> <b> Status </b> </td>
						<td> <b> Type </b> </td>
					</tr>
					@foreach($user->history as $history)
						<tr>
							<td>{{ $history->created_at->toDateTimeString() }}</td>
							<td>{{ $history->tx_hash }}</td>
							<td>{{ $history->amount }} </td>
							<td>{{ $history->status }} </td>
							<td>{{ $history->type }} </td>
						</tr>
					@endforeach
				</table>
			@endif

			@if($user->photos->isNotEmpty())
				<table class="table">
					<tr class="row">
						<td colspan="5">
						<br/><br/> <h2> Gallery </h2> </td>
					</tr>
					<tr>
						<td colspan="5">
							@foreach($user->photos as $photo)
								<img style="max-width: 200px;" src="{{ $photo->id }}.jpg">
							@endforeach
						</td>
					</tr>
				</table>
			@endif
		</div>
    </body>
</html>