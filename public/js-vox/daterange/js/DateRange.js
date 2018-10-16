/**
 * DateRange class represents date range and allows to change dateFrom and
 * dateTo via presets such as lastDays, lastWeeks, lastMonths, lastYears etc.
 */

function DateRange(date) {
	
	var referenceDate = date;
	var from = null;
	var to = null
	var firstDayOfWeek = 1;
	
	/**
	 * lastDays
	 */
	this.lastDays = function(days) {
		from = new Date();
		to = new Date();
		
		var ref = referenceDate;
		
		from = new Date(ref);
		from.setUTCDate(ref.getUTCDate() - days);
		
		to = new Date(ref);
		to.setUTCDate(ref.getDate() - 1);
		
		this.adjustUTCHours();
		
		return this;
	}
	
	this.lastDaysInclCurrent = function(days) {
		referenceDate.setUTCDate(referenceDate.getUTCDate() + 1);
		this.lastDays(days);
		
		return this;
	}
	
	/**
	 * lastWeeks
	 */
	this.lastWeeks = function(weeks) {
		from = this.getBeginningOfWeek();
		from.setUTCDate(from.getUTCDate() - (7 * weeks));
		
		to = new Date(from);
		to.setUTCDate(to.getUTCDate() + 6 + (7 * (weeks - 1)));
		
		this.adjustUTCHours();

		return this;
	}
	
	this.lastWeeksInclCurrent = function(weeks) {
		referenceDate.setUTCDate(referenceDate.getUTCDate() + 7);
		this.lastWeeks(weeks);
		
		return this;
	}
	
	this.lastWeeksWTY = function(weeks) {
		this.lastWeeks(weeks);
		this.setToYesterday();
		return this;
	}
	
	/**
	 * lastMonths
	 */
	this.lastMonths = function(months) {
		to = new Date(referenceDate);
		to.setUTCDate(0);
		
		from = new Date(to);
		from.setUTCDate(1);
		from.setUTCMonth(from.getMonth() - months + 1);
		
		this.adjustUTCHours();

		return this;
	}
	
	this.lastMonthsInclCurrent = function(months) {
		referenceDate.setUTCDate(1);
		referenceDate.setUTCMonth(referenceDate.getUTCMonth() + 1);
		this.lastMonths(months);
		
		return this;
	}
	
	this.lastMonthsMTY = function(months) {
		this.lastMonths(months);
		this.setToYesterday();
		return this;
	}
	
	/**
	 * lastQuarters
	 */
	this.lastQuarters = function(quarters) {
		to = new Date(referenceDate);
		to.setUTCDate(1);
		
		from = new Date(to);
		var quarter = Math.floor(to.getMonth() / 3) - 1;
		var from_mth = quarter * 3 - (quarters - 1) * 3;
		var to_mth = from_mth + 2 + (quarters - 1) * 3;
		
		from.setUTCMonth(from_mth);
		from.setUTCDate(1);
		
		to.setUTCMonth(to_mth + 1)
		to.setUTCDate(0);
		
		this.adjustUTCHours();

		return this;
	}
	
	this.lastQuartersInclCurrent = function(quarters) {
		this.lastQuarters(quarters);
		from.setUTCMonth(from.getUTCMonth() + 3);
		to.setUTCDate(1);
		to.setUTCMonth(to.getUTCMonth() + 4);
		to.setUTCDate(0);
		
		return this;
	}
	
	this.lastQuartersQTY = function(quarters) {
		this.lastQuarters(quarters);
		this.setToYesterday();
		return this;
	}
	
	/**
	 * lastYears
	 */
	this.lastYears = function(years) {
		var lastOfYear = new Date(referenceDate);
		lastOfYear.setUTCMonth(0);
		lastOfYear.setUTCDate(1);
		lastOfYear.setUTCDate(0);

		var firstOfYear = new Date(lastOfYear);
		firstOfYear.setUTCDate(1);
		firstOfYear.setUTCMonth(-12 * (years - 1));
		from = firstOfYear;
		to = lastOfYear;
		
		this.adjustUTCHours();
		
		return this;
	}
	
	this.lastYearsInclCurrent = function(years) {
		referenceDate.setUTCFullYear(referenceDate.getUTCFullYear() + 1);
		this.lastYears(years);
		
		return this;
	}
	
	this.lastYearsYTY = function(years) {
		this.lastYears(years);
		this.setToYesterday();
		return this;
	}
	
	/**
	 * Adjusts UTC time:
	 *  - date from to 00:00:00
	 *  - date to to 23:59:59
	 */
	this.adjustUTCHours = function() {
		from.setUTCHours(0, 0, 0, 0);
		to.setUTCHours(23, 59, 59, 0);
	}
	
	this.setToYesterday = function() {
		to = new Date(referenceDate);
		to.setUTCDate(to.getUTCDate() - 1);
		to.setUTCHours(23, 59, 59, 0);
	}
	
	/**
	 * Returns Date which is in the beginning of the week (according to
	 * firstDayOfWeek).
	 */
	this.getBeginningOfWeek = function() {
		var ref = new Date(referenceDate);
		var day = ref.getUTCDay();
		var diff;
		switch (firstDayOfWeek) {
			case 0:
				diff = day;
				break;
			case 1:
				diff = day - (day == 0 ? -6 : 1); // adjust when day is sunday
				break;
			default:
				throw new Exception('Unsupported firstDayOfWeek');
		}
		ref.setUTCDate(ref.getUTCDate() - diff);
		ref.setUTCHours(0, 0, 0, 0);
		return ref;
	}
	
	/**
	 * Getters and setters
	 */
	
	/**
	 * Return "date from" of DateRange.
	 */
	this.getFrom = function() {
		return from;
	}
	
	/**
	 * Return "date to" of DateRange.
	 */
	this.getTo = function() {
		return to;
	}
	
	/**
	 * Gets firstDayOfWeek which is used for week presets.
	 * 
	 * Note: Sunday is 0, Monday is 1, and so on.
	 */
	this.getFirstDayOfWeek = function() {
		return firstDayOfWeek;
	}
	
	/**
	 * Sets firstDayOfWeek which is used for week presets.
	 * 
	 * Note: Sunday is 0, Monday is 1, and so on.
	 */
	this.setFirstDayOfWeek = function(day) {
		firstDayOfWeek = day;
		return this;
	}
	
}
