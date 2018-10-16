(function($) {
	var $current_target;
	var $dropdown;
	
	// form elements
	var $datepicker;
	
	var $parameters;
	
	var $daterangePreset;
	var $parameter1;
	var $aggregation;
	var $aggregationWrap;
	
	var $enableComparison;
	var $comparisonPreset;
	
	var default_options = {
		
		aggregations : ['-', 'daily', 'weekly', 'monthly', 'yearly'],
        values : {}

	};

    var default_aggregation = 'daily';

	var db = {
		
		aggregations : {
			'-' : {
				title : "Inherit",
				presets : []
			},
			'hourly' : {
				title : "Hourly",
				presets : ['custom', 'lastdays']
			},
			'daily' : {
				title : "Daily",
				presets : ['custom', 'lastdays', 'yesterday', 'today']
			},
			'weekly' : {
				title : "Weekly",
				presets : ['custom', 'lastweeks']
			},
			'monthly' : {
				title : "Monthly",
				presets : ['custom', 'lastmonths']
			},
			'quarterly' : {
				title : "Quarterly",
				presets : ['custom', 'lastquarters']
			},
			'yearly' : {
				title : "Yearly",
				presets : ['custom', 'lastyears']
			},
			'whole' : {
				title : "Whole period",
				presets : ['custom', 'lastdays', 'lastweeks', 'lastmonths', 'lastquarters', 'lastyears']
			}
		},
		
		date_presets : {
			'custom' : {
				title: "Custom",
				dates: function() {return null;}
			},
			'today' : {
				title: "Today",
				dates: function() {
					var dates = [];
					dates[0] = ((new Date()).setHours(0,0,0,0)).valueOf();
					dates[1] = new Date(dates[0]).setHours(23,59,59,0).valueOf();
					return dates;
				}
			},
			'yesterday' : {
				title: "Yesterday",
				dates: function() {
					var dates = [];
					dates[0] = ((new Date()).setHours(0,0,0,0)).valueOf() - 24*3600*1000;
					dates[1] = new Date(dates[0]).setHours(23,59,59,0).valueOf();
					return dates;
				}
			},
			'lastdays' : {
				title: "Last Day(s)",
				parameters: true,
				defaults : {
					parameter1 : 7
				},
				dates: function() {
					var days = internal.getParameter1();
					var dates = [];
					
					var today = new Date().clearTime();
					dates[0] = new Date(today).setDate(today.getDate() - days).valueOf();
					dates[1] = new Date(today);
					dates[1].setDate(today.getDate() - 1);
					dates[1].setHours(23,59,59,0).valueOf();
					
					return dates;
				}
			},
			'lastweeks' : {
				title: "Last Week(s)",
				parameters: true,
				defaults : {
					parameter1 : 2
				},
				dates: function() {
					var dates = [];
					var weeks = internal.getParameter1();
					
					var monday = internal.getMonday(new Date());
					monday.setDate(monday.getDate() - (7 * weeks));
					dates[0] = monday.valueOf();
					var sunday = new Date(monday);
					sunday.setDate(sunday.getDate()+6 + (7 * (weeks - 1)));
					sunday.setHours(23,59,59,0);
					dates[1] = sunday.valueOf();
					
					return dates;
				}
			},
			'lastmonths' : {
				title: "Last Month(s)",
				parameters: true,
				defaults : {
					parameter1 : 3
				},
				dates: function() {
					var months = internal.getParameter1();
					var dates = [];
					
					var lastOfMonth = new Date().setDate(0);
					var firstOfMonth = new Date(lastOfMonth);
					firstOfMonth.setDate(1);
					firstOfMonth.setMonth(firstOfMonth.getMonth() - months + 1);
					dates[0] = firstOfMonth.valueOf();
					dates[1] = lastOfMonth.valueOf();
					
					return dates;
				}
			},
			'lastquarters' : {
				title: "Last Quarters(s)",
				parameters: true,
				defaults : {
					parameter1 : 2
				},
				dates: function() {
					// TODO: fix -- works as months now
					var months = internal.getParameter1() * 3;
					var dates = [];
					
					var lastOfMonth = new Date().setDate(0);
					var firstOfMonth = new Date(lastOfMonth);
					firstOfMonth.setDate(1);
					firstOfMonth.setMonth(firstOfMonth.getMonth() - months + 1);
					dates[0] = firstOfMonth.valueOf();
					dates[1] = lastOfMonth.valueOf();
					
					return dates;
				}
			},
			'lastyears' : {
				title: "Last Year(s)",
				parameters: true,
				defaults : {
					parameter1 : 1
				},
				dates: function() {
					var years = internal.getParameter1();
					var dates = [];
					
					var lastOfYear = new Date();
					lastOfYear.setDate(0);
					lastOfYear.setMonth(-1);
					
					var firstOfYear = new Date(lastOfYear);
					firstOfYear.setDate(1);
					firstOfYear.setMonth(-12*(years - 1));
					dates[0] = firstOfYear.valueOf();
					dates[1] = lastOfYear.valueOf();
					
					return dates;
				}
			}
		}	
	
	};
	
	var methods = {
		
		init : function(options) {
			return this.each(function() {
				var $this = $(this);
				var data = $this.data('DateRangesWidget');
                $this.data('test', internal);
         
				// initialize data in dom element
				if (!data) {
					
					var effective_options = $.extend({}, default_options, options);
					
					$this.data('DateRangesWidget', {
						options : effective_options
					});

				}
				internal.createElements($this);
				internal.updateDateField($this);


			});
		}
		
		/*
		remove : function() {
			this.text('');
		},
		
		destroy : function() {
			return this.each(function() {
				var $this = $(this),
				data = $this.data('DateRangesWidget');

				// Namespacing FTW
				$(window).unbind('.DateRangesWidget');
				data.target.remove();
				$this.removeData('DateRangesWidget');
			})
		}
		*/
	};
	
	var internal = {
		
		refreshForm : function() {
			var lastSel = $datepicker.DatePickerGetLastSel();

			if ($('.comparison-preset', $dropdown).val() != 'custom') {
				lastSel = lastSel % 2;
				$datepicker.DatePickerSetLastSel(lastSel);
			}
			$('.dr', $dropdown).removeClass('active');
			$('.dr[lastSel=' + lastSel + ']', $dropdown).addClass('active');

			var dates = $datepicker.DatePickerGetDate()[0];
			//console.log('dates', dates);

			var newFrom = dates[0].getDate() + '/' + (dates[0].getMonth()+1) + '/' + dates[0].getFullYear();
			var newTo = dates[1].getDate() + '/' + (dates[1].getMonth()+1) + '/' + dates[1].getFullYear();
			
			var oldFrom = $('.dr1.from', $dropdown).val();
			var oldTo = $('.dr1.to', $dropdown).val();
			
			if (newFrom != oldFrom || newTo != oldTo) {
				$('.dr1.from', $dropdown).val(newFrom);
				$('.dr1.to', $dropdown).val(newTo);

			}

            $('.dr1.from_millis', $dropdown).val(dates[0].getTime());
            $('.dr1.to_millis', $dropdown).val(dates[1].getTime());
			
			if (dates[2]) {
				$('.dr2.from', $dropdown).val(dates[2].getDate() + '/' + (dates[2].getMonth()+1) + '/' + dates[2].getFullYear());
			}
			if (dates[3]) {
				$('.dr2.to', $dropdown).val(dates[3].getDate() + '/' + (dates[3].getMonth()+1) + '/' + dates[3].getFullYear());
			}
		},
		
		createElements : function($target) {
			// modify div to act like a dropdown
			$target.html(
				'<div class="date-range-field">'+
					'<span class="aggregation"></span>'+
					'<span class="main"></span>'+
					//'<span class="comparison-divider"> Cmp to: </span>'+
					'<span class="comparison"></span>'+
					'<a href="#">&#9660;</a>'+
				'</div>'
			);
			
			// only one dropdown exists even though multiple widgets may be on the page
			if (!$dropdown) {
				$dropdown = $(
				'<div id="datepicker-dropdown">'+
					'<div class="date-ranges-picker"></div>'+
					'<div class="date-ranges-form">'+
						'<div class="aggregation-wrap">'+
							'Aggregation:'+
							'<select class="aggregation">'+
							'</select>'+
						'</div>'+
						'<div class="main-daterange">'+
							'<div>'+
								'Date Range:'+
								'<select class="daterange-preset">'+
								'</select>'+
								'<span class="parameters">'+
									'<input type="number" class="daterange-preset-parameter1" />'+
								'</span>'+
							'</div>'+
							'<input type="text" class="dr dr1 from" lastSel="0" /> - <input type="text" class="dr dr1 to" lastSel="1" />'+
                            '<input type="hidden" class="dr dr1 from_millis" lastSel="2" /><input type="hidden" class="dr dr1 to_millis" lastSel="3" />'+
						'</div>'+
						'<div>'+
							'<input type="checkbox" checked="checked" class="enable-comparison" /> Compare to:'+
							'<select class="comparison-preset">'+
								'<option value="custom">Custom</option>'+
								'<option value="previousperiod" selected="selected">Previous period</option>'+
								'<option value="previousyear">Previous year</option>'+
							'</select>'+
						'</div>'+
						'<div class="comparison-daterange">'+
							'<input type="text" class="dr dr2 from" lastSel="2" /> - <input type="text" class="dr dr2 to" lastSel="3" />'+
                            '<input type="hidden" class="dr dr2 from_millis" lastSel="2" /><input type="hidden" class="dr dr2 to_millis" lastSel="3" />'+
           			'</div>'+
           				'<div class="btn-group">'+
						'<button class="btn btn-mini" id="button-ok">Apply</button>'+
						'<button class="btn btn-mini" id="button-cancel">Cancel</button>'+
						'</div>'+
					'</div>'+
				'</div>');
				$dropdown.appendTo($('body'));
				
				$aggregation = $('.aggregation', $dropdown);
				$aggregationWrap = $('.aggregation-wrap', $dropdown);
				
				$datepicker = $('.date-ranges-picker', $dropdown);
				
				$daterangePreset = $('.daterange-preset', $dropdown);
				$parameters = $('.parameters', $dropdown);
				$parameter1 = $('.daterange-preset-parameter1', $dropdown);
				
				$enableComparison = $('.enable-comparison', $dropdown);
				$comparisonPreset = $('.comparison-preset', $dropdown);




				// TODO: inherit options from DRW options
				$datepicker.DatePicker({
					mode: 'tworanges',
					starts: 1,
					calendars: 3,
					inline: true,
					//date: [new Date('2012-09-03'), new Date('2012-09-09'), new Date('2012-09-10'), new Date('2012-09-16')],
					onChange: function(dates, el, options) {
						// user clicked on datepicker
						internal.setDaterangePreset('custom');
                        console.log("onchange datepicker");
					}
				});
				
				/**
				 * Handle change of aggregation.
				 */
				$aggregation.change(function() {
					internal.populateDateRangePresets();					
				});
				
				
				/**
				 * Handle change of datePreset
				 */
				$daterangePreset.change(function() {
					var date_preset = internal.getDaterangePreset();
					if (date_preset.parameters) {
						//console.log(internal.getParameter1());
						if (!$.isNumeric(internal.getParameter1())) {
							internal.setParameter1(date_preset.defaults.parameter1);
						}
						$parameters.show();
					} else {
						$parameters.hide();
					}
					$('.dr1', $dropdown).prop('disabled', ($daterangePreset.val() == 'custom' ? false : true));
					
					internal.recalculateDaterange();
				});
				
				$parameter1.change(function() {
					var p1 = internal.getParameter1();
					//console.log(p1);
					if (!$.isNumeric(p1) || p1 < 1)
						internal.setParameter1(1);
					internal.recalculateDaterange();
				});
				
				/**
				 * Handle enable/disable comparison.
				 */
				$enableComparison.change(function() {
					internal.setComparisonEnabled($(this).is(':checked'));
				});
				
				/**
				 * Handle change of comparison preset.
				 */
				$comparisonPreset.change(function() {
					internal.recalculateComparison();
				});
				
				/**
				 * Handle clicking on date field.
				 */
				$('.dr', $dropdown).click(function() {
					// set active date field for datepicker
					$datepicker.DatePickerSetLastSel($(this).attr('lastSel'));
					//internal.refreshForm(); // don't refresh
				});
				
				/**
				 * Handle clicking on OK button.
				 */
				$('#button-ok', $dropdown).click(function() {
					internal.retractDropdown($current_target);
					internal.saveValues($current_target);
					internal.updateDateField($current_target);
					return false;
				});
				
				/**
				 * Handle clicking on OK button.
				 */
				$('#button-cancel', $dropdown).click(function() {
					//console.log('cancel')
					var $this = $(this);
					internal.retractDropdown($current_target);
					return false;
				});
				
			}
			
			/**
			 * Handle expand/retract of dropdown.
			 */
			$target.bind('click', function() {
				var $this = $(this);
				//console.log($this);
				//console.log('clicked on ', $this);
				if ($this.hasClass('DRWClosed')) {
					internal.expandDropdown($this);
				} else {
					internal.retractDropdown($this);
				}
				return false;
			});
			
			$target.addClass('DRWInitialized');
			$target.addClass('DRWClosed');
		},
		
		recalculateDaterange : function() {
			var date_preset = internal.getDaterangePreset();
			
			var dates = $datepicker.DatePickerGetDate()[0];
			//console.log('original dates', dates);
			
			// TODO: remove
			if (date_preset.dates == undefined) throw date_preset.title + " doesn't have dates()";
			
			var d = date_preset.dates();
			if (d != null) {
				dates[0] = d[0];
				dates[1] = d[1];
			}
			//console.log('new dates', dates);
			$datepicker.DatePickerSetDate(dates);
			internal.recalculateComparison();
			/*
			$('.main-daterange input.dr', $dropdown).prop('disabled', ($this.val() == 'custom' ? false : true));

			$('.comparison-preset', $dropdown).change();
			internal.refreshForm(); // should do only one refresh call
			*/
		},
		
		recalculateComparison : function() {
			var dates = $datepicker.DatePickerGetDate()[0];
			if (dates.length >= 2) {
				var comparisonPreset = internal.getComparisonPreset();
				//console.log(comparisonPreset);
				switch (comparisonPreset) {
					case 'previousperiod':
						var days = parseInt((dates[1]-dates[0])/(24*3600*1000));
						dates[2] = new Date(dates[0]).setDate(dates[0].getDate() - (days+1));
						dates[3] = new Date(dates[1]).setDate(dates[1].getDate() - (days+1));
						break;
					case 'previousyear':
						dates[2] = new Date(dates[0]).setFullYear(dates[0].getFullYear(dates[0]) - 1);
						dates[3] = new Date(dates[1]).setFullYear(dates[1].getFullYear(dates[1]) - 1);
						break;
				}
				$datepicker.DatePickerSetDate(dates);
				//console.log('comp', $this.val());
				$('.comparison-daterange input.dr', $dropdown).prop('disabled', (comparisonPreset == 'custom' ? false : true));
				internal.refreshForm();
			}
		},
		
		populateAggregations : function(aggregations) {
			var $select = $('select.aggregation', $dropdown);
			
			$select.html('');
			$.each(aggregations, function(i, aggregation) {
				$select.append($("<option/>", {
					value : aggregation,
					text : db.aggregations[aggregation].title
				}));
			});
			internal.populateDateRangePresets();
		},
		
		/**
		 * Loads values from target element's data to controls.
		 */
		loadValues : function($target) {
			var values = $target.data('DateRangesWidget').options.values;
			//console.log('load', values);
			// handle initial values
			$('.dr1.from', $dropdown).val(values.dr1from);
			$('.dr1.from', $dropdown).change();
			$('.dr1.to', $dropdown).val(values.dr1to);
			$('.dr1.to', $dropdown).change();
			$('.dr2.from', $dropdown).val(values.dr2from)
			$('.dr2.from', $dropdown).change();
			$('.dr2.to', $dropdown).val(values.dr2to)
			$('.dr2.to', $dropdown).change();
			
			$aggregation.val(values.aggregation);
			$aggregation.change();
			
			$daterangePreset.val(values.daterangePreset);
			$daterangePreset.change();
			
			$parameter1.val(values.parameter1);
			$parameter1.change();
			
			$enableComparison.prop('checked', values.comparisonEnabled);
			$enableComparison.change();
			
			$comparisonPreset.val(values.comparisonPreset);
			$comparisonPreset.change();
		},
		
		/**
		 * Stores values from controls to target element's data.
		 */
		saveValues : function($target) {
			var data = $target.data('DateRangesWidget');
			var values = data.options.values;
			values.aggregation = internal.getAggregation();
			values.daterangePreset = internal.getDaterangePresetVal()
			values.parameter1 = internal.getParameter1();
			values.dr1from = $('.dr1.from', $dropdown).val()
			values.dr1to = $('.dr1.to', $dropdown).val()
            values.dr1from_millis = $('.dr1.from_millis', $dropdown).val()
            values.dr1to_millis = $('.dr1.to_millis', $dropdown).val()
			
			values.comparisonEnabled = internal.getComparisonEnabled();
			values.comparisonPreset = internal.getComparisonPreset();
			values.dr2from = $('.dr2.from', $dropdown).val()
			values.dr2to = $('.dr2.to', $dropdown).val()

            values.dr2from_millis = $('.dr2.from_millis', $dropdown).val()
            values.dr2to_millis = $('.dr2.to_millis', $dropdown).val()
			$target.data('DateRangesWidget', data);

            if($target.data().DateRangesWidget.options.onChange)
                $target.data().DateRangesWidget.options.onChange(values);

		},

		
		/**
		 * Updates target div with data from target element's data
		 */
		updateDateField : function($target) {
			var values = $target.data("DateRangesWidget").options.values;
			//console.log('values', values);
			if (values.aggregation) {
				$('span.aggregation', $target).text(values.aggregation);
				$('span.aggregation', $target).show();
			} else {
				$('span.aggregation', $target).hide();
			}



			if (values.dr1from && values.dr1to) {
				$('span.main', $target).text(values.dr1from + ' - ' + values.dr1to);
				
			} else if(values.daterangePreset) {
                var dates = db.date_presets[values.daterangePreset].dates();
                $('span.main', $target).text(dates[0] + ' - ' + dates[1]);

            }
            else {
				$('span.main', $target).text('N/A');
			}
			if (values.comparisonEnabled && values.dr2from && values.dr2to) {
				$('span.comparison', $target).text(values.dr2from + ' - ' + values.dr2to);
				$('span.comparison', $target).show();
				$('span.comparison-divider', $target).show();
			} else {
				$('span.comparison-divider', $target).hide();
				$('span.comparison', $target).hide();
			}


			return true;
		},
		
		getAggregation : function() {
			return $aggregation.val();
		},
		
		getDaterangePresetVal : function() {
			return $daterangePreset.val();
		},
		
		getDaterangePreset : function() {
			return db.date_presets[$daterangePreset.val()];
		},
		
		setDaterangePreset : function(value) {
			$daterangePreset.val(value);
			$daterangePreset.change();
		},
		
		getParameter1 : function() {
			return parseInt($parameter1.val());
		},
		
		setParameter1 : function(value) {
			$parameter1.val(value);
		},
		
		setComparisonEnabled : function(enabled) {
			$datepicker.DatePickerSetMode(enabled ? 'tworanges' : 'range');
		},
		
		getComparisonEnabled : function() {
			return $enableComparison.prop('checked');
		},
		
		getComparisonPreset : function() {
			return $comparisonPreset.val();
		},
		
		populateDateRangePresets : function() {
			var aggregation = internal.getAggregation();
            if(!aggregation)
                aggregation = default_aggregation;
			var main_presets_keys =  db.aggregations[aggregation].presets;

            var $other_presets = $('<optgroup/>', {label : 'Other presets'})
			var valueBackup = $daterangePreset.val();
			
			$daterangePreset.html('');
			
			// add main presets
			$.each(main_presets_keys, function(i, main_preset_key) {
				var date_preset = db.date_presets[main_preset_key];
				if (date_preset == undefined) throw 'Invalid preset "' + main_preset_key + '".';
				$daterangePreset.append($("<option/>", {
					value : main_preset_key,
					text : date_preset.title
				}));
			});
			
			// add other presets
			$.each(db.date_presets, function(preset_key, date_preset) {
				if ($.inArray(preset_key, main_presets_keys) == -1) {
					$other_presets.append($("<option/>", {
						value : preset_key,
						text : date_preset.title
					}));
				}
			});
			$daterangePreset.append($other_presets);
			
			$daterangePreset.val(valueBackup);
		},
		
		expandDropdown : function($target) {
			var options = $target.data("DateRangesWidget").options;
			$current_target = $target;
			
			// init aggregations
			if (options.aggregations.length > 0) {
				internal.populateAggregations(options.aggregations);
				$aggregationWrap.show();
			} else {
				$aggregationWrap.hide();
			}
			
			internal.loadValues($target);
			
			
			// retract all other dropdowns
			$('.DRWOpened').each(function() {
				internal.retractDropdown($(this));
			});
			
			var leftDistance = $target.offset().left;
			var rightDistance = $(document).width() - $target.offset().left - $target.width();
			//console.log(leftDistance, rightDistance);
			$dropdown.show();
			if (rightDistance > leftDistance) {
				//console.log('aligning left')
				//console.log($target.offset().top, $target.height());
				// align left edges
				$dropdown.offset({
					left : $target.offset().left,
					top : $target.offset().top + $target.height() - 1
				});
				$dropdown.css('border-radius',  '0 5px 5px 5px')
			} else {
				//console.log('aligning right')
				// align right edges
				var fix = parseInt($dropdown.css('padding-left').replace('px', '')) +
					parseInt($dropdown.css('padding-right').replace('px', '')) +
					parseInt($dropdown.css('border-left-width').replace('px', '')) +
					parseInt($dropdown.css('border-right-width').replace('px', ''))
				$dropdown.offset({
					left : $target.offset().left + $target.width() - $dropdown.width() - fix,
					top : $target.offset().top + $target.height() - 1
				});
				$dropdown.css('border-radius',  '5px 0 5px 5px')
			}
			
			
			// switch to up-arrow
			$('.date-range-field a', $target).html('&#9650;');
			$('.date-range-field', $target).css({borderBottomLeftRadius:0, borderBottomRightRadius:0});
			$('.date-range-field a', $target).css({borderBottomRightRadius:0});
			$target.addClass('DRWOpened');
			$target.removeClass('DRWClosed');
			
			
			// refresh
			internal.recalculateDaterange();
		},
		
		retractDropdown : function($target) {
			//console.log('retract', $target);
			
			$dropdown.hide();
			$('.date-range-field a', $target).html('&#9660;');
			$('.date-range-field', $target).css({borderBottomLeftRadius:5, borderBottomRightRadius:5});
			$('.date-range-field a', $target).css({borderBottomRightRadius:5});
			$target.addClass('DRWClosed');
			$target.removeClass('DRWOpened');
		},

		getMonday : function(d) {
			d = new Date(d);
			var day = d.getDay();
			var diff = d.getDate() - day + (day == 0 ? -6 : 1); // adjust when day is sunday
			return new Date(d.setDate(diff));
		}
		
	};
	
	$.fn.DateRangesWidget = function(method) {



		if (methods[method]) {
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		} else if (typeof method === 'object' || !method) {
			return methods.init.apply( this, arguments );
		} else {
			$.error('Method ' +  method + ' does not exist on jQuery.DateRangesWidget');
		}
  
	};

})( jQuery );
