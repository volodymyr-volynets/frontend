/**
 * Numbers calendar
 *
 * @param array options
 *		id - id of the input element
 *		type - date, datetime, time
 *		format - variables from date function
 *		date_min - minimum date & time
 *		date_max - maximum date & time
 *		date_week_start_day - which day to start rendering (0-6)
 *		date_disable_week_days - which weekdays to disable, array of (0-6)
 *		i18n - translated date/time texts
 *		master_id - id of master input
 *		slave_id - id of slave input
 *		show_presets - whether we need to display presets panel
 *		holder_div_id - whether we have div holder
 */
var Numbers_Calendar = function (options) {
	// initializing object
	var result = new Object();
	result.id = options.id;
	result.elem = document.getElementById(options.id);
	result.var_id = 'numbers_calendar_var_' + result.id;
	result.type = options.type ? options.type : 'date';
	result.format = options.format ? options.format : Numbers.Format.getDateFormat(result.type);
	result.div_id = options.id + '_calendar_div';
	// we need to insert div element right after input
	var div = document.createElement("div");
	div.setAttribute('id', result.div_id);
	div.setAttribute('class', 'numbers_calendar_div numbers_prevent_selection');
	div.setAttribute('tabindex', -1);
	div.style.display = 'none';
	div.onfocus = function () {
		result.flag_is_focused = true;
		window[result.var_id].onfocus();
	};
	div.onblur = function () {
		window[result.var_id].onfocus(true);
	};
	div.onclick = function () {
		result.flag_is_focused = true;
		window[result.var_id].onfocus();
	};
	if (window.addEventListener) {
		div.addEventListener('DOMMouseScroll', function(event) { window[result.var_id].onscroll(event); }, false);
	}
	div.onmousewheel = function (event) {
		window[result.var_id].onscroll(event);
	};
	div.onkeyup = function (event) {
		window[result.var_id].onkeyup(event);
	};
	div.onmouseover = function () {
		document.body.style.overflow = 'hidden';
	};
	div.onmouseout = function () {
		document.body.style.overflow = 'auto';
	};
	// appending to holder if present
	if (options.holder_div_id) {
		document.getElementById(options.holder_div_id).appendChild(div);
	} else {
		result.elem.parentNode.appendChild(div);
	}
	result.div_elem = document.getElementById(result.div_id);
	// we need to set onfocus and onblur on input element
	result.elem.onfocus = function () {
		window[result.var_id].show(true);
	};
	result.elem.onblur = function () {
		window[result.var_id].onfocus(true);
	};
	result.elem.onkeyup = function (event) {
		window[result.var_id].onkeyup(event);
	};
	// initializing other elements
	result.date_month_id = result.div_id + '_date_month';
	result.date_month_elem = null;
	result.date_year_id = result.div_id + '_date_year';
	result.date_year_elem = null;
	result.date_days_id = result.div_id + '_date_days';
	result.date_days_elem = null;
	result.date_day_id = result.div_id + '_date_day';
	result.date_day_elem = null;
	result.time_hour_id = result.div_id + '_time_hour';
	result.time_hour_elem = null;
	result.time_minute_id = result.div_id + '_time_minute';
	result.time_minute_elem = null;
	result.time_second_id = result.div_id + '_time_second';
	result.time_second_elem = null;
	result.time_am_pm_id = result.div_id + '_time_am_pm';
	result.time_am_pm_elem = null;
	result.time_go_id = result.div_id + '_time_go_id';
	result.time_go_elem = null;
	result.date_week_start_day = options.date_week_start_day && options.date_week_start_day <= 6 ? options.date_week_start_day : 0;
	result.date_disable_week_days = options.date_disable_week_days ? options.date_disable_week_days : null;
	result.flag_show_presets = !!options.show_presets;
	// time specific flags
	result.flag_time_am_pm = false;
	result.flag_time_seconds = false;
	// current date & used date
	result.date_current = new Date();
	result.date_used = null;
	result.date_selected = null;
	// min/max dates
	result.date_min = options.date_min ? new Date(options.date_min) : null;
	result.date_max = options.date_max ? new Date(options.date_max) : null;
	result.date_min_year = 0;
	result.date_max_year = 0;
	result.flag_date_year_down = false;
	result.flag_date_year_up = false;
	// i18n
	if (options.i18n) {
		result.i18n = options.i18n;
	} else {
		result.i18n = {
			months: {
				days: [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31],
				full: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
				short: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
			},
			weeks: {
				full: ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
				short: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']
			},
			time: {
				am_pm: ['am', 'pm'],
				hour: ['Hour', 'Hr.'],
				minute: ['Minute', 'Min.'],
				second: ['Second', 'Sec.']
			},
			presets: {
				today: 'Today / Now'
			}
		};
		// translate
		for (var i in result.i18n.months.full) result.i18n.months.full[i] = i18n(null, result.i18n.months.full[i]);
		for (var i in result.i18n.months.short) result.i18n.months.short[i] = i18n(null, result.i18n.months.short[i]);
		for (var i in result.i18n.weeks.full) result.i18n.weeks.full[i] = i18n(null, result.i18n.weeks.full[i]);
		for (var i in result.i18n.weeks.short) result.i18n.weeks.short[i] = i18n(null, result.i18n.weeks.short[i]);
		for (var i in result.i18n.time.am_pm) result.i18n.time.am_pm[i] = i18n(null, result.i18n.time.am_pm[i]);
		for (var i in result.i18n.time.hour) result.i18n.time.hour[i] = i18n(null, result.i18n.time.hour[i]);
		for (var i in result.i18n.time.minute) result.i18n.time.minute[i] = i18n(null, result.i18n.time.minute[i]);
		for (var i in result.i18n.time.second) result.i18n.time.second[i] = i18n(null, result.i18n.time.second[i]);
		for (var i in result.i18n.presets) result.i18n.presets[i] = i18n(null, result.i18n.presets[i]);
	}
	// we need to calculate years range
	if (result.type == 'date' || result.type == 'datetime') {
		if (result.date_min != null) {
			result.date_min_year = result.date_min.getFullYear();
		} else {
			result.date_min_year = result.date_current.getFullYear() - 5;
			result.flag_date_year_down = true;
		}
		if (result.date_max != null) {
			result.date_max_year = result.date_max.getFullYear();
		} else {
			result.date_max_year = result.date_current.getFullYear() + 5;
			result.flag_date_year_up = true;
		}
	}
	// time specific settings
	if (result.type == 'datetime' || result.type == 'time') {
		// determine if we need to display am/pm
		if (result.format.search('a') != -1 || result.format.search('g') != -1) {
			result.flag_time_am_pm = true;
		}
		// determine if we need to show seconds
		if (result.format.search('s') != -1) {
			result.flag_time_seconds = true;
		}
	}
	// calendar specific flags
	result.flag_skeleton_rendered = false;
	result.flag_is_focused = false;
	result.flag_onscroll_lock = false;
	result.flag_onshow_lock = false;
	// master/slave
	result.master_id = options.master_id ? options.master_id : null;
	result.master_datetime = null;
	result.slave_id = options.slave_id ? options.slave_id : null;
	result.slave_datetime = null;

	/**
	 * Onkeyup handler
	 */
	result.onkeyup = function(event) {
		// we clode everything on escape
		if (event) {
			var code = event.which || event.keyCode;
			if (code == 27) {
				this.onfocus(true);
				return;
			}
		}
	};

	/**
	 * Onscroll handler
	 * @param object event
	 */
	result.onscroll = function (event) {
		event.preventDefault();
		event.stopPropagation();
		if (this.flag_onscroll_lock || !(this.type == 'datetime' || this.type == 'date')) {
			return;
		}
		var delta = 0;
		if (!event) {
			event = window.event;
		}
		// normalize the delta
		if (event.wheelDelta) { // IE and Opera
			delta = event.wheelDelta / 60;
		} else if (event.detail) { // W3C
			delta = -event.detail / 2;
		}
		if (delta >= 0) {
			this.prevNext(true, false);
		} else {
			this.prevNext(false, true);
		}
		this.flag_onscroll_lock = true;
		setInterval(function(){ window[result.var_id].flag_onscroll_lock = false }, 500);
	};

	/**
	 * Get calendar parameters
	 * @param string what
	 * @returns mixed
	 */
	result.get = function (what) {
		var data = {};
		data.year = parseInt(this.date_year_elem.value);
		data.month = parseInt(this.date_month_elem.value);
		data.day = parseInt(this.date_day_elem.value);
		data.am_pm = parseInt(this.time_am_pm_elem.value);
		data.hour = parseInt(this.time_hour_elem.value);
		data.minute = parseInt(this.time_minute_elem.value);
		data.second = parseInt(this.time_second_elem.value);
		// we need to take pm into account
		if (this.flag_time_am_pm && data.am_pm == 1) {
			data.hour += 12;
		}
		if (what == 'date_object') {
			if (this.type == 'time') {
				return new Date(2000, 1, 1, data.hour, data.minute, data.second);
			} else {
				return new Date(data.year, data.month, data.day, data.hour, data.minute, data.second);
			}
		} else {
			return data;
		}
	};

	/**
	 * Load master/slave dates
	 */
	result.loadMasterSlaveDatetime = function () {
		this.master_datetime = null;
		if (this.master_id) {
			var tmp = document.getElementById(this.master_id).value;
			if (tmp) {
				var date = Numbers.Format.readDate(tmp, this.type);
				if (date !== false) {
					this.master_datetime = date;
				}
			}
		}
		this.slave_datetime = null;
		if (this.slave_id) {
			var tmp = document.getElementById(this.slave_id).value;
			if (tmp) {
				var date = Numbers.Format.readDate(tmp, this.type);
				if (date !== false) {
					this.slave_datetime = date;
				}
			}
		}
	};

	/**
	 * Update date
	 */
	result.updateDateSelected = function () {
		if (this.elem.value) {
			var date = Numbers.Format.readDate(this.elem.value, this.type);
			if (date !== false) {
				this.date_used = date;
			}
		}
		this.date_selected = this.date_used ? this.date_used : this.date_current;
		// updating year/month only after we rendered skeleton
		if (this.flag_skeleton_rendered) {
			// date
			if (this.type == 'date' || this.type == 'datetime') {
				this.date_year_elem.value = this.date_selected.getFullYear();
				this.date_month_elem.value = this.date_selected.getMonth();
				this.date_day_elem.value = this.date_selected.getDate();
			}
		}
	};

	/**
	 * Check for flag_is_focused flag
	 * @returns boolean
	 */
	result.checkIfFocused = function () {
		return this.flag_is_focused;
	};

	/**
	 * Onfocus processor
	 * @param boolean only_postponed_check
	 */
	result.onfocus = function (only_postponed_check) {
		// if we are processing postponed onblur
		if (only_postponed_check) {
			this.flag_is_focused = false;
			var that = this;
			setTimeout(function () {
				if (!that.checkIfFocused()) {
					that.close();
				}
				that.flag_is_focused = false;
			}, 250);
		} else {
			this.flag_is_focused = true;
		}
	};

	/**
	 * This would be called when day is chosen
	 * @param int day
	 */
	result.dateDayChosen = function (year, month, day) {
		// for date type we autoclose the widget
		if (this.type == 'date') {
			this.updateInputElement(new Date(year, month, day));
			this.close();
		} else if (this.type == 'datetime') {
			this.flag_is_focused = true;
			// we must reset year/month/day in case we choose prev/next month
			this.date_year_elem.value = year;
			this.date_month_elem.value = month;
			this.date_day_elem.value = day;
			this.date_used = new Date(year, month, day);
			this.renderDays();
		}
	};

	/**
	 * Check master/slave time
	 * @returns boolean
	 */
	result.checkMasterSlaveTime = function () {
		// load date & time
		var cur_date = this.get('date_object'), cur_disable = false;
		if (this.master_datetime) {
			if (cur_date < this.master_datetime) {
				cur_disable = true;
			}
		}
		if (this.slave_datetime) {
			if (cur_date > this.slave_datetime) {
				cur_disable = true;
			}
		}
		if (cur_disable) {
			this.time_go_elem.style.display = 'none';
			return false;
		} else {
			this.time_go_elem.style.display = 'block';
			return true;
		}
	};

	/**
	 * This would be called when time is chosen
	 */
	result.timeChosen = function () {
		if (this.checkMasterSlaveTime()) {
			this.updateInputElement(this.get('date_object'));
			this.close();
		}
	};

	/**
	 * This function would update input element
	 * @param Date date_object
	 */
	result.updateInputElement = function (date_object) {
		var value = Numbers.Format.dateFormat(date_object, this.type, {format: this.format});
		this.elem.value = value;
		// onchange
		var event;
		if (typeof(Event) === 'function') {
			event = new Event('change');
		} else {
			event = document.createEvent('Event');
			event.initEvent('change', true, true);
		}
		this.elem.dispatchEvent(event);
	};

	/**
	 * Show calendar
	 */
	result.show = function (only_show) {
		// we need to lock show function to prevent double firing
		if (this.flag_onshow_lock) {
			return;
		}
		// render skeleton
		if (!this.flag_skeleton_rendered) {
			// we need to determine selected date & used date
			this.updateDateSelected();
			this.renderSkeleton();
			this.flag_skeleton_rendered = true;
			// we need to initialize few elements
			this.date_year_elem = document.getElementById(this.date_year_id);
			this.date_month_elem = document.getElementById(this.date_month_id);
			this.date_days_elem = document.getElementById(this.date_days_id);
			this.date_day_elem = document.getElementById(this.date_day_id);
			this.time_hour_elem = document.getElementById(this.time_hour_id);
			this.time_minute_elem = document.getElementById(this.time_minute_id);
			this.time_second_elem = document.getElementById(this.time_second_id);
			this.time_am_pm_elem = document.getElementById(this.time_am_pm_id);
			if (this.type == 'time' || this.type == 'datetime') {
				this.time_go_elem = document.getElementById(this.time_go_id);
			}
		}
		// hide/show calendar
		if (this.div_elem.style.display != 'none' && !only_show) {
			this.close();
		} else {
			this.updateDateSelected();
			this.loadMasterSlaveDatetime();
			// render days only for date types
			if (this.flag_skeleton_rendered && (this.type == 'date' || this.type == 'datetime')) {
				this.renderDays();
			}
			this.elem.focus();
			this.flag_is_focused = true;
			this.div_elem.style.display = 'block'; // or table
		}
		this.flag_onshow_lock = true;
		setInterval(function(){ window[result.var_id].flag_onshow_lock = false }, 500);
	};

	/**
	 * Check if we can click prev button
	 * @returns boolean
	 */
	result.dateCanPrev = function () {
		var year = parseInt(this.date_year_elem.value);
		var month = parseInt(this.date_month_elem.value);
		// checking for maxinum date
		if (this.date_min != null) {
			var year2 = this.date_min.getFullYear();
			var month2 = this.date_min.getMonth();
			if (year < year2) {
				return false;
			} else if (year == year2 && month <= month2) {
				return false;
			}
		} else {
			if (year < this.date_min_year) {
				return false;
			} else if (year == this.date_min_year && month == 0) {
				return false;
			}
		}
		return true;
	};

	/**
	 * Check if we can click next button
	 * @returns boolean
	 */
	result.dateCanNext = function () {
		var year = parseInt(this.date_year_elem.value);
		var month = parseInt(this.date_month_elem.value);
		// checking for maxinum date
		if (this.date_max != null) {
			var year2 = this.date_max.getFullYear();
			var month2 = this.date_max.getMonth();
			if (year > year2) {
				return false;
			} else if (year == year2 && month >= month2) {
				return false;
			}
		} else {
			if (year > this.date_max_year) {
				return false;
			} else if (year == this.date_max_year && month == 11) {
				return false;
			}
		}
		return true;
	};

	/**
	 * Handler for prev/next buttons
	 * @param int next
	 */
	result.prevNext = function (next, prev) {
		this.flag_is_focused = true;
		var year = parseInt(this.date_year_elem.value);
		var month = parseInt(this.date_month_elem.value);
		// next
		if (next && this.dateCanNext()) {
			if (month == 11) {
				month = 0;
				year += 1;
			} else {
				month++;
			}
		}
		// prev
		if (prev && this.dateCanPrev()) {
			if (month == 0) {
				month = 11;
				year -= 1;
			} else {
				month--;
			}
		}
		// putting values back
		this.date_year_elem.value = year;
		this.date_month_elem.value = month;
		this.renderDays();
	};

	/**
	 * Time handler
	 * @param string what
	 * @param boolean down
	 */
	result.timeChanged = function (what, down) {
		this.flag_is_focused = true;
		var am_pm = parseInt(this.time_am_pm_elem.value);
		var hour = parseInt(this.time_hour_elem.value);
		var minute = parseInt(this.time_minute_elem.value);
		var second = parseInt(this.time_second_elem.value);
		if (what == 'am_pm') {
			am_pm = am_pm == 0 ? 1 : 0;
		}
		if (what == 'hour') {
			var max_hours = this.flag_time_am_pm ? 11 : 23;
			if (down) {
				hour--;
				if (hour == -1) {
					hour = max_hours;
				}
			} else {
				hour++;
				if (hour > max_hours) {
					hour = 0;
				}
			}
		}
		if (what == 'minute') {
			if (down) {
				minute--;
				if (minute == -1) {
					minute = 59;
				}
			} else {
				minute++;
				if (minute >= 60) {
					minute = 0;
				}
			}
		}
		if (what == 'second') {
			if (down) {
				second--;
				if (second == -1) {
					second = 59;
				}
			} else {
				second++;
				if (second >= 60) {
					second = 0;
				}
			}
		}
		this.time_am_pm_elem.value = am_pm;
		this.time_hour_elem.value = hour;
		this.time_minute_elem.value = minute;
		this.time_second_elem.value = second;
		// check if we have time in range
		this.checkMasterSlaveTime();
	};

	/**
	 * This would be called when user changes month in select element
	 *
	 * @param int month
	 */
	result.monthChanged = function (month) {
		this.renderDays();
	};

	/**
	 * This will be called when user changes the year in select element
	 * @param mixed year
	 */
	result.yearChanged = function (year) {
		// if user requested earlier dates
		if (year == 'down') {
			var prev_year = this.date_min_year - 1;
			for (var i = prev_year; i >= this.date_min_year - 5; i--) {
				var option = new Option(i18n(null, i), i);
				this.date_year_elem.insertBefore(option, this.date_year_elem.options[1]);
			}
			this.date_min_year = i + 1;
			this.date_year_elem.value = prev_year;
		}
		// if user requested latest dates
		if (year == 'up') {
			var next_year = this.date_max_year + 1;
			for (var i = next_year; i <= this.date_max_year + 5; i++) {
				var option = new Option(i18n(null, i), i);
				this.date_year_elem.insertBefore(option, this.date_year_elem.options[this.date_year_elem.options.length - 1]);
			}
			this.date_max_year = i - 1;
			this.date_year_elem.value = next_year;
		}
		this.renderDays();
	};

	/**
	 * Close calendar
	 */
	result.close = function () {
		this.div_elem.style.display = 'none';
	};

	/**
	 * Render skeleton
	 */
	result.renderSkeleton = function () {
		var html = '';
		html += '<table dir="ltr">';
		// date elements
		if (this.type == 'date' || this.type == 'datetime') {
			// date header
			html += '<tr>';
				html += '<td align="left" width="25"><span onclick="' + this.var_id + '.prevNext(0, 1);" id="' + this.id + '_calendar_header_prev" class="numbers_calendar_header_button"><i class="fa fa-chevron-left"></i></span></td>';
				html += '<td align="center">';
					html += '<table width="100%">';
						html += '<tr>';
							html += '<td>';
								// hidden day element
								html += '<input type="hidden" id="' + this.date_day_id + '" value="" />';
								// month select
								html += '<select id="' + this.date_month_id + '" class="numbers_calendar_header_date_select" onchange="' + this.var_id + '.monthChanged(this.value);" onfocus="' + this.var_id + '.onfocus();" onblur="' + this.var_id + '.onfocus(true);">';
								// we need to select proper month
								var selected_month = this.date_selected.getMonth();
								for (var i in this.i18n.months.full) {
									html += '<option value="' + i + '" ' + (selected_month == i ? 'selected' : '') + '>' + this.i18n.months.full[i] + '</option>';
								}
								html += '</select>';
								html += '</td>';
								html += '<td>';
								// year select
								html += '<select id="' + this.date_year_id + '" class="numbers_calendar_header_date_select" onchange="' + this.var_id + '.yearChanged(this.value);" onfocus="' + this.var_id + '.onfocus();" onblur="' + this.var_id + '.onfocus(true);">';
								if (this.flag_date_year_down) {
									html += '<option value="down">+++</option>';
								}
								var year_selected = this.date_selected.getFullYear();
								for (var i = this.date_min_year; i <= this.date_max_year; i++) {
									html += '<option value="' + i + '" ' + (year_selected == i ? 'selected' : '') + '>' + i18n(null, i) + '</option>';
								}
								if (this.flag_date_year_up) {
									html += '<option value="up">+++</option>';
								}
								html += '</select>';
							html += '</td>';
						html += '</tr>';
					html += '</table>';
				html += '</td>';
				html += '<td align="right" width="25"><span onclick="' + this.var_id + '.prevNext(1, 0);" id="' + this.id + '_calendar_header_next" class="numbers_calendar_header_button"><i class="fas fa-chevron-right"></i></a></td>';
			html += '</tr>';
			// date days
			html += '<tr>';
				html += '<td colspan="3" id="' + this.date_days_id + '">&nbsp;</td>';
			html += '</tr>';
		}
		// separator
		if (this.type == 'datetime') {
			html += '<tr>';
			html += '<td colspan="3"><hr/></td>';
			html += '</tr>';
		}
		// hidden elements
		if (this.type == 'time') {
			html += '<input type="hidden" id="' + this.date_year_id + '" value="" />';
			html += '<input type="hidden" id="' + this.date_month_id + '" value="" />';
			html += '<input type="hidden" id="' + this.date_day_id + '" value="" />';
		}
		if (this.type == 'date') {
			html += '<input type="hidden" id="' + this.time_hour_id + '" value="" />';
			html += '<input type="hidden" id="' + this.time_minute_id + '" value="" />';
			html += '<input type="hidden" id="' + this.time_second_id + '" value="" />';
			html += '<input type="hidden" id="' + this.time_am_pm_id + '" value="" />';
		}
		// time elements
		if (this.type == 'time' || this.type == 'datetime') {
			var hide_am_pm = this.flag_time_am_pm ? '' : ' style="display: none;"';
			var hide_seconds = this.flag_time_seconds ? '' : ' style="display: none;"';
			html += '<tr>';
				html += '<td colspan="3" align="center">';
					var rtl = Numbers.I18n.rtl() ? ' dir="rtl" ' : '';
					html += '<table width="100%"' + rtl + '>';
						html += '<tr>';
							html += '<td align="center"><span onclick="' + this.var_id + '.timeChanged(\'hour\');" class="numbers_calendar_header_button"><i class="fa fa-chevron-up"></i></span></td>';
							html += '<td>&nbsp;</td>';
							html += '<td align="center"><span onclick="' + this.var_id + '.timeChanged(\'minute\');" class="numbers_calendar_header_button"><i class="fa fa-chevron-up"></i></span></td>';
							html += '<td' + hide_seconds + '>&nbsp;</td>';
							html += '<td align="center"' + hide_seconds + '><span onclick="' + this.var_id + '.timeChanged(\'second\');" class="numbers_calendar_header_button"><i class="fa fa-chevron-up"></i></span></td>';
							html += '<td align="center"' + hide_am_pm + '><span onclick="' + this.var_id + '.timeChanged(\'am_pm\');" class="numbers_calendar_header_button"><i class="fa fa-chevron-up"></i></span></td>';
							html += '<td align="center" width="33" rowspan="3" valign="middle"><a href="javascript:void(0);" onclick="' + this.var_id + '.timeChosen();" id="' + this.time_go_id + '" class="numbers_calendar_time_button"><i class="far fa-arrow-alt-circle-right"></i></a></td>';
						html += '</tr>';
						html += '<tr>';
							html += '<td align="center">';
								var max_hours = 23;
								html += '<select id="' + this.time_hour_id + '" class="numbers_calendar_header_date_select" onfocus="' + this.id + '.onfocus();" onblur="' + this.id + '.onfocus(true);">';
								var selected_hour = this.date_selected.getHours();
								var label, selected_am_pm = 0, selected_hour_original = selected_hour;
								// special handling for am/pm
								if (this.flag_time_am_pm) {
									max_hours = 11;
									if (selected_hour > 12) {
										selected_hour = selected_hour - 12;
										selected_am_pm = 1;
									}
								}
								for (var i = 0; i <= max_hours; i++) {
									label = i;
									if (this.flag_time_am_pm && i == 0) {
										label = 12;
									}
									html += '<option value="' + i + '" ' + (selected_hour == i ? 'selected' : '') + '>' + i18n(null, label < 10 ? ('0' + label) : label) + '</option>';
								}
								html += '</select>';
							html += '</td>';
							html += '<td>:</td>';
							html += '<td align="center">';
								html += '<select id="' + this.time_minute_id + '" class="numbers_calendar_header_date_select" onfocus="' + this.id + '.onfocus();" onblur="' + this.id + '.onfocus(true);">';
								var selected_minute = this.date_selected.getMinutes();
								for (var i = 0; i <= 59; i++) {
									html += '<option value="' + i + '" ' + (selected_minute == i ? 'selected' : '') + '>' + i18n(null, i < 10 ? ('0' + i) : i) + '</option>';
								}
								html += '</select>';
							html += '</td>';
							html += '<td' + hide_seconds + '>:</td>';
								html += '<td align="center"' + hide_seconds + '>';
								html += '<select id="' + this.time_second_id + '" class="numbers_calendar_header_date_select" onfocus="' + this.id + '.onfocus();" onblur="' + this.id + '.onfocus(true);">';
								var selected_second = this.date_selected.getSeconds();
								for (var i = 0; i <= 59; i++) {
									html += '<option value="' + i + '" ' + (selected_second == i ? 'selected' : '') + '>' + i18n(null, i < 10 ? ('0' + i) : i) + '</option>';
								}
								html += '</select>';
							html += '</td>';
							html += '<td align="center"' + hide_am_pm + '>';
							html += '<select id="' + this.time_am_pm_id + '" class="numbers_calendar_header_date_select" onfocus="' + this.id + '.onfocus();" onblur="' + this.id + '.onfocus(true);">';
							for (var i in this.i18n.time.am_pm) {
								html += '<option value="' + i + '" ' + (selected_am_pm == i ? 'selected' : '') + '>' + (this.i18n.time.am_pm[i]) + '</option>';
							}
							html += '</select>';
							html += '</td>';
						html += '</tr>';
						html += '<tr>';
							html += '<td align="center"><span onclick="' + this.var_id + '.timeChanged(\'hour\', true);" class="numbers_calendar_header_button"><i class="fas fa-chevron-down"></i></span></td>';
							html += '<td>&nbsp;</td>';
							html += '<td align="center"><span onclick="' + this.var_id + '.timeChanged(\'minute\', true);" class="numbers_calendar_header_button"><i class="fas fa-chevron-down"></i></a></td>';
							html += '<td' + hide_seconds + '>&nbsp;</td>';
							html += '<td align="center"' + hide_seconds + '><span onclick="' + this.var_id + '.timeChanged(\'second\', true);" class="numbers_calendar_header_button"><i class="fas fa-chevron-down"></i></span></td>';
							html += '<td align="center"' + hide_am_pm + '><span onclick="' + this.var_id + '.timeChanged(\'am_pm\');" class="numbers_calendar_header_button"><i class="fas fa-chevron-down"></i></span></td>';
						html += '</tr>';
						// names
						html += '<tr>';
							html += '<td align="center">' + this.i18n.time.hour[1] + '</td>';
							html += '<td>&nbsp;</td>';
							html += '<td align="center">' + this.i18n.time.minute[1] + '</td>';
							html += '<td' + hide_seconds + '>&nbsp;</td>';
							html += '<td align="center"' + hide_seconds + '>' + this.i18n.time.second[1] + '</td>';
						html += '</tr>';
					html += '</table>';
				html += '</td>';
			html += '</tr>';
		}
		// whether we need to show presets panel
		if (this.flag_show_presets) {
			html+= '<tr>';
				html+= '<td colspan="3"><hr/></td>';
			html+= '</tr>';
			html+= '<tr>';
				html+= '<td colspan="3">';
					html+= '<a href="javascript:void(0);" onclick="' + this.var_id + '.updateInputElement(new Date()); ' + this.var_id + '.close();">' + result.i18n.presets.today + '</a><br/>';
				html+= '</td>';
			html+= '</tr>';
		}
		html += '</table>';
		// adding content to the div
		this.div_elem.innerHTML = html;
	};

	/**
	 * Render date days
	 */
	result.renderDays = function () {
		var year = parseInt(this.date_year_elem.value);
		var month = parseInt(this.date_month_elem.value);
		// get number of days in this month
		var this_month_total_days = this.i18n.months.days[month];
		if (month == 1) { // February
			var is_leap = new Date(year, 1, 29).getDate() == 29;
			this_month_total_days = is_leap ? 29 : 28;
		}
		// get number of days in previous month
		var prev_moth_object = new Date(year, month, 0);
		var prev_month_total_days = prev_moth_object.getDate();
		var next_moth_object = new Date(year, month + 1, 1);
		var week_days_hash = {}, week_days_index = 0;
		var rtl = Numbers.I18n.rtl() ? ' dir="rtl" ' : '';
		// rendering table
		html = '<table class="numbers_calendar_date_days" cellpadding="2"' + rtl + '>';
		// render header
		html += '<tr>';
		for (var t = this.date_week_start_day; t < this.i18n.weeks.short.length; t++) {
			html += '<th>' + this.i18n.weeks.short[t] + '</th>';
			week_days_hash[week_days_index] = t;
			week_days_index++;
		}
		if (this.date_week_start_day != 0) {
			for (var t = 0; t < this.date_week_start_day; t++) {
				html += '<th>' + this.i18n.weeks.short[t] + '</th>';
				week_days_hash[week_days_index] = t;
				week_days_index++;
			}
		}
		html += '</tr>';
		// render days
		var cur_day = 1, cur_month, cur_year, onclick, value, classes, cur_disable, cur_date;
		var first_day_of_month_obj = new Date(year, month, 1);
		var first_day_of_week = first_day_of_month_obj.getDay(); // Returns the day of the week (from 0-6)
		var flag_prev_month = false, prev_days_left = 0, flag_next_month = false, next_days_start = 1;
		// we need to preprocess master/slave datetimes
		var cur_master_datetime, cur_slave_datetime;
		if (this.master_datetime) {
			cur_master_datetime = new Date(this.master_datetime.getFullYear(), this.master_datetime.getMonth(), this.master_datetime.getDate());
		}
		if (this.slave_datetime) {
			cur_slave_datetime = new Date(this.slave_datetime.getFullYear(), this.slave_datetime.getMonth(), this.slave_datetime.getDate());
		}
		if (first_day_of_week != this.date_week_start_day) {
			flag_prev_month = true;
			prev_days_left = first_day_of_week - 1 - this.date_week_start_day;
			if (prev_days_left < -1) {
				prev_days_left += 7;
			}
		}
		for (var i = 0; i < 6; i++) {
			html += '<tr>';
			for (var j = 0; j < 7; j++) {
				html += '<td align="center">';
				classes = 'numbers_calendar_date_day_cell';
				if (flag_prev_month) {
					cur_year = prev_moth_object.getFullYear();
					cur_month = prev_moth_object.getMonth();
					value = prev_month_total_days - prev_days_left;
					onclick = this.var_id + '.dateDayChosen(' + cur_year + ', ' + cur_month + ', ' + value + ');';
					classes += ' numbers_calendar_date_day_prev';
					prev_days_left--;
					if (prev_days_left == -1) {
						flag_prev_month = false;
					}
				} else if (flag_next_month) {
					cur_year = next_moth_object.getFullYear();
					cur_month = next_moth_object.getMonth();
					onclick = this.var_id + '.dateDayChosen(' + cur_year + ', ' + cur_month + ', ' + next_days_start + ');';
					value = next_days_start;
					classes += ' numbers_calendar_date_day_prev';
					next_days_start++;
				} else {
					cur_year = year;
					cur_month = month;
					onclick = this.var_id + '.dateDayChosen(' + year + ', ' + month + ', ' + cur_day + ');';
					value = cur_day;
					// we need to prepend special class for current day
					if (this.date_current.getFullYear() == year && this.date_current.getMonth() == month && this.date_current.getDate() == cur_day) {
						classes += ' numbers_calendar_date_day_current';
					}
					// processing date used
					if (this.date_used != null) {
						if (this.date_used.getFullYear() == year && this.date_used.getMonth() == month && this.date_used.getDate() == cur_day) {
							classes += ' numbers_calendar_date_day_clicked';
						}
					}
					cur_day++;
					if (cur_day > this_month_total_days) {
						flag_next_month = true;
					}
				}
				// we need to determine whether we need to disable element
				cur_disable = false;
				if (this.date_disable_week_days != null && this.date_disable_week_days.indexOf(week_days_hash[j]) != -1) {
					cur_disable = true;
				}
				cur_date = new Date(cur_year, cur_month, value);
				if (cur_master_datetime) {
					if (cur_date < cur_master_datetime) {
						cur_disable = true;
					}
				}
				if (cur_slave_datetime) {
					if (cur_date > cur_slave_datetime) {
						cur_disable = true;
					}
				}
				// rendering
				if (cur_disable) {
					html += '<span class="' + classes + ' numbers_calendar_date_disabled" onclick="return false;">&nbsp;' + i18n(null, value) + '&nbsp;</span>';
				} else {
					classes += ' numbers_calendar_date_day_hover';
					html += '<span class="' + classes + '" onclick="' + onclick + '">&nbsp;' + i18n(null, value) + '&nbsp;</span>';
				}
				html += '</td>';
			}
			html += '</tr>';
			// we exit if we rendered all days
			if (cur_day > this_month_total_days) {
				break;
			}
		}
		html += '</table>';
		this.date_days_elem.innerHTML = html;
		// handling prev/next buttons
		document.getElementById(this.id + '_calendar_header_next').style.display = this.dateCanNext() ? 'inline-block' : 'none';
		document.getElementById(this.id + '_calendar_header_prev').style.display = this.dateCanPrev() ? 'inline-block' : 'none';
	};

	// we need to set a variable in global scope
	window[result.var_id] = result;
};

/**
 * Extending JQuery if loaded
 */
if (window.jQuery) {
	(function ($) {
		$.fn.numbersCalendar = function (options) {
			if (!options) options = {};
			// loop through all elements
			return this.each(function () {
				var elem = $(this), id = elem.attr('id'), options2 = $.extend({}, options);
				if (!id) {
					id = 'numbers_calendar_random_generated_id_' + Math.round(Math.random() * 1000) + '_' + Math.round(Math.random() * 1000) + '_' + Math.round(Math.random() * 1000);
					elem.attr('id', id);
				}
				options2.id = id;
				Numbers_Calendar(options2);
			});
		};
	})(jQuery);
}