<form class="front-form search-form">
	<i class="fas fa-search"></i>
	<input id="search-input" type="text" name="location" value="{{ !empty($query) ? $formattedAddress : '' }}" placeholder="{!! nl2br(trans('trp.common.search-placeholder')) !!}" autocomplete="off" />
	<input type="submit" value="">			    		
	<div class="loader">
		<i class="fas fa-circle-notch fa-spin fa-3x fa-fw"></i>
	</div>
	<div class="results" style="display: none;">
		<div class="locations-results results-type">
			<span class="result-title">
				{!! nl2br(trans('trp.common.search-locations')) !!}
				
			</span>

			<div class="clearfix list">
			</div>
		</div>
		<div class="dentists-results results-type">
			<span class="result-title">
				{!! nl2br(trans('trp.common.search-dentists')) !!}
				
			</span>

			<div class="clearfix list">
			</div>
		</div>
		<div class="global-results results-type">
			<span class="result-title">
				{!! nl2br(trans('trp.common.search-global')) !!}
				
			</span>

			<div class="clearfix list">
				<a href="{{ getLangUrl('dentists/worldwide') }}" class="special">
					{!! nl2br(trans('trp.common.search-partners')) !!}
				</a>
			</div>
			<div class="clearfix list">
				<a href="{{ getLangUrl('dentist-listings-by-country') }}" class="special">
					{!! nl2br(trans('trp.common.search-dentists-countries')) !!}
				</a>
			</div>
		</div>
	</div>
</form>	