/**
 * Format
 *
 * @type object
 */
numbers.format = {

	/**
	 * Format date based on format
	 *
	 * @param string value
	 * @param string type
	 * @param array options
	 * @returns string
	 */
	date_format: function(value, type, options) {
		if (!value) return null;
		if (!type) type = 'date';
		if (!options) options = {};
		// processing format
		if (options.format) {
			var format = options.format;
		} else {
			var format = this.get_date_format(type);
		}
		// formatting string
		if (typeof value == 'object') {
			var datetime = value;
		} else {
			var datetime = new Date(value);
		}
		var result = format;
		result = result.replace('Y', datetime.getFullYear());
		result = result.replace('d', datetime.getDate() < 10 ? ('0'+ datetime.getDate()) : datetime.getDate());
		result = result.replace('m', (datetime.getMonth() + 1 < 10) ? ('0'+ (datetime.getMonth() + 1)) : (datetime.getMonth() + 1));
		result = result.replace('H', (datetime.getHours() < 10) ? ('0'+ datetime.getHours()) : datetime.getHours());
		result = result.replace('i', (datetime.getMinutes() < 10) ? ('0'+ datetime.getMinutes()) : datetime.getMinutes());
		result = result.replace('s', (datetime.getSeconds() < 10) ? ('0'+ datetime.getSeconds()) : datetime.getSeconds());
		var hours = datetime.getHours();
		result = result.replace('a', (hours >= 12) ? 'pm' : 'am');
		var ghours = hours > 12 ? (hours - 12) : hours;
		result = result.replace('g', (ghours < 10) ? ('0' + ghours) : ghours);
		return result;
	},

	/**
	 * Get date format
	 *
	 * @param string type
	 * @returns string
	 */
	get_date_format: function(type) {
		return array_key_get(numbers, 'flag.global.format.' + type);
	},

	/**
	 * Format date
	 *
	 * @param string value
	 * @param array options
	 * @returns string
	 */
	date: function(value, options) {
		return this.date_format(value, 'date', options);
	},

	/**
	 * Format datetime
	 *
	 * @param string value
	 * @param array options
	 * @returns string
	 */
	datetime: function(value, options) {
		return this.date_format(value, 'datetime', options);
	},

	/**
	 * Format time
	 *
	 * @param string value
	 * @param array options
	 * @returns string
	 */
	time: function(value, options) {
		return this.date_format(value, 'time', options);
	},

	/**
	 * Read float
	 *
	 * @param string amount
	 * @param array options
	 *		boolean - bcnumeric
	 *		boolean - valid_check
	 * @returns mixed
	 */
	read_floatval: function(amount, options) {
		if (!options) options = {};
		// remove currency symbol and name, thousands separator
		var locale_options = array_key_get(numbers, 'flag.global.format.locale_options');
		amount = amount.toString().replace(locale_options.int_curr_symbol, '').replace(locale_options.currency_symbol, '').replace(locale_options.mon_thousands_sep, '').replace(' ', '');
		// handle decimal separator
		if (locale_options.mon_decimal_point != '.') {
			amount = amount.replace(locale_options.mon_decimal_point, '.');
		}
		// sanitize only check
		if (options.valid_check) {
			return !isNaN(parseFloat(amount));
		}
		// if we are processing bc numeric data type
		if (options.bcnumeric) {
			if (isNaN(parseFloat(amount)) || amount == '') {
				amount = '0';
			}
			return amount;
		}
		// process based on type
		if (options.intval) {
			return parseInt(amount);
		} else {
			return parseFloat(amount);
		}
	},

	/**
	 * Read bcnumeric
	 *
	 * @param string amount
	 * @param array options
	 * @returns string
	 */
	read_bcnumeric: function(amount, options) {
		if (!options) options = {};
		options.bcnumeric = true;
		return this.read_floatval(amount, options);
	},

	/**
	 * Read intval
	 *
	 * @param string amount
	 * @param array options
	 * @returns int
	 */
	read_intval: function(amount, options) {
		if (!options) options = {};
		options.intval = true;
		return this.read_floatval(amount, options);
	}
};