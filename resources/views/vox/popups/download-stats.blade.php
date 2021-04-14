<div class="popup fixed-popup popup-with-background download-stats-popup close-on-shield" id="download-stats-popup">
	<div class="popup-inner inner-white">
		<a href="javascript:;" class="closer">
			<img src="{{ url('new-vox-img/close-popup.png') }}">
		</a>
		<div class="flex flex-mobile flex-center break-tablet">
			<div class="content">
				<p class="h1">
					DOWNLOAD STATS
				</p>
				
				<form method="post" id="download-form" class="page-statistics" action="{{ getLangUrl('download-statistics') }}" stat-url="{{ getLangUrl('dental-survey-stats/'.$vox->slug) }}">
					{!! csrf_field() !!}

					<input type="hidden" id="stats-for" name="stats-for" value="">
					<input type="hidden" id="scale-for" name="scale-for" value="">

					<div class="download-formats alert-after">
						<label for="format-png">
							<i class="far fa-circle"></i>
							<input type="radio" name="download-format" value="png" id="format-png" class="download-format-radio">
							.PNG
						</label>
						<label for="format-pdf">
							<i class="far fa-circle"></i>
							<input type="radio" name="download-format" value="pdf" id="format-pdf" class="download-format-radio">
							.PDF
						</label>
						@if(empty(request('app')))
							<label for="format-xlsx">
								<i class="far fa-circle"></i>
								<input type="radio" name="download-format" value="xlsx" id="format-xlsx" class="download-format-radio">
								.XLSX
							</label>
						@endif
					</div>

					<div class="filters-wrapper alert-after">
						<div class="filters-custom-wrap">
							<b>Period:</b>
							@foreach($filters as $filterkey => $filter)
								@if($filterkey == 'all')
									<label for="download-date-all" class="active download-filter">
										<input type="radio" name="download-date" class="download-date-radio" id="download-date-all" checked="checked" value="all">
										<span class="d">{{ $filter }}</span>
										<span class="m">All times</span>
									</label>
								@endif
							@endforeach
							<label for="download-date-custom" class="download-custom-date download-filter">
								<input type="radio" name="download-date" class="download-date-radio" id="download-date-custom" value="custom">
								{!! trans('vox.page.stats.period') !!}
							</label>
						</div>

						<div class="filters-custom tac" style="display: none;">
							<div id="datepicker-extras-download">
								<div class="flex">
									<div>
										{!! trans('vox.page.stats.period-from') !!}:<br/>
										<input type="text" name="date-from-download" id="date-from-download" autocomplete="off" placeholder="dd/mm/yyyy">
									</div>
									-
									<div>
										{!! trans('vox.page.stats.period-to') !!}:<br/>
										<input type="text" name="date-to-download" id="date-to-download" autocomplete="off" placeholder="dd/mm/yyyy">
									</div>
								</div>
							</div>
							<div id="custom-datepicker-download" launched-date="{{ $launched_date }}">
							</div>
						</div>
					</div>

					<div class="download-demographics alert-after">
						<b>Demographic Breakdown:</b>


					</div>

				    <button type="submit" name="create_pdf" id="create_pdf" class="red-button">Download</button>
			   	</form>
			</div>
		</div>
	</div>
</div>