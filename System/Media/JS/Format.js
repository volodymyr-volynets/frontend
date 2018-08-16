/**
 * Format
 *
 * @type object
 */
Numbers.Format = {

	/**
	 * Format date based on format
	 *
	 * @param string value
	 * @param string type
	 * @param array options
	 * @returns string
	 */
	dateFormat: function(value, type, options) {
		if (!value) return null;
		if (!type) type = 'date';
		if (!options) options = {};
		// processing format
		if (options.format) {
			var format = options.format;
		} else {
			var format = this.getDateFormat(type);
		}
		// formatting string
		if (typeof value === 'object') {
			var datetime = value;
		} else {
			var datetime = new Date(value);
		}
		var result = format;
		var temp = datetime.getFullYear();
		result = result.replace('Y', i18n(null, temp));
		temp = datetime.getDate() < 10 ? ('0'+ datetime.getDate()) : datetime.getDate();
		result = result.replace('d', i18n(null, temp));
		temp = (datetime.getMonth() + 1 < 10) ? ('0' + (datetime.getMonth() + 1)) : (datetime.getMonth() + 1);
		result = result.replace('m', i18n(null, temp));
		temp = (datetime.getHours() < 10) ? ('0' + datetime.getHours()) : datetime.getHours();
		result = result.replace('H', i18n(null, temp));
		temp = (datetime.getMinutes() < 10) ? ('0'+ datetime.getMinutes()) : datetime.getMinutes();
		result = result.replace('i', i18n(null, temp));
		temp = (datetime.getSeconds() < 10) ? ('0'+ datetime.getSeconds()) : datetime.getSeconds();
		result = result.replace('s', i18n(null, temp));
		var hours = datetime.getHours();
		result = result.replace('a', (hours >= 12) ? i18n(null, 'pm') : i18n(null, 'am'));
		var ghours = hours > 12 ? (hours - 12) : hours;
		temp = (ghours < 10) ? ('0' + ghours) : ghours;
		result = result.replace('g', i18n(null, temp));
		return result;
	},

	/**
	 * Get date format
	 *
	 * @param string type
	 * @returns string
	 */
	getDateFormat: function(type) {
		return array_key_get(Numbers, 'flag.global.format.' + type);
	},

	/**
	 * Format date
	 *
	 * @param string value
	 * @param array options
	 * @returns string
	 */
	date: function(value, options) {
		return this.dateFormat(value, 'date', options);
	},

	/**
	 * Format datetime
	 *
	 * @param string value
	 * @param array options
	 * @returns string
	 */
	datetime: function(value, options) {
		return this.dateFormat(value, 'datetime', options);
	},

	/**
	 * Format time
	 *
	 * @param string value
	 * @param array options
	 * @returns string
	 */
	time: function(value, options) {
		return this.dateFormat(value, 'time', options);
	},

	/**
	 * Read date
	 *
	 * @param string date
	 * @param string type
	 * @return string
	 */
	readDate: function(date, type) {
		if (empty(date)) {
			return null;
		}
		if (!type) type = 'date';
		// convert numbers
		date = this.numberToFromNativeLanguage(date.toString(), {}, true);
		date = date.replace(i18n(null, 'am'), 'am').replace(i18n(null, 'pm'), 'pm');
		// parse date
		var msec = Date.parse(date);
		if (!isNaN(msec)) {
			return new Date(msec);
		}
		return false;
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
	readFloatval: function(amount, options) {
		if (!options) options = {};
		// cleanup the number
		var locale_options = array_key_get(Numbers, 'flag.global.format.locale_options');
		amount =  amount.toString().replace(new RegExp(locale_options.mon_thousands_sep, 'g'), '').replace(/\s/g, '');
		var negative = /[\-\(]/.test(amount);
		if (locale_options.mon_decimal_point != '.') { // handle decimal separator
			amount = amount.replace(new RegExp(locale_options.mon_decimal_point, 'g'), '.');
		}
		// convert number from native locale
		amount = this.numberToFromNativeLanguage(amount, options, true);
		// get rid of all non digits
		amount = amount.replace(/[^\d.]/g, '');
		if (negative) {
			amount = '-' + amount;
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
	readBcnumeric: function(amount, options) {
		if (!options) options = {};
		options.bcnumeric = true;
		return this.readFloatval(amount, options);
	},

	/**
	 * Read intval
	 *
	 * @param string amount
	 * @param array options
	 * @returns int
	 */
	readIntval: function(amount, options) {
		if (!options) options = {};
		options.intval = true;
		return this.readFloatval(amount, options);
	},

	/**
	 * Amount
	 *
	 * @param mixed amount
	 * @param array options
	 *		boolean skip_user_settings
	 *		string format
	 *		string symbol
	 *		boolean accounting
	 *		int digits
	 *		int decimals
	 *		string currency_code
	 * @return string
	 */
	amount: function(amount, options) {
		if (!options) options = {};
		var format = array_key_get(Numbers, 'flag.global.format'), type;
		// if currency code is passed we need to load symbol
		if (options.currency_code) {
			if (!isset(options.symbol) || (isset(options.symbol) && options.symbol !== false)) {
				options.symbol = Numbers.CountriesCurrencies.data[options.currency_code].symbol;
			}
			// override decimals only if not set
			if (!isset(options.decimals)) {
				options.decimals = Numbers.CountriesCurrencies.data[options.currency_code].fraction_digits;
			}
		}
		// user defined monetary options
		if (!options.skip_user_settings) {
			// if type is not set then grab it from settings
			if (options.type) {
				type = options.type;
			} else if (!options.fs) {
				type = format.amount_frm;
			} else {
				type = format.amount_fs;
			}
			if (type == 10) { // Amount (Locale, With Currency Symbol)
				if (!options.hasOwnProperty('symbol')) {
					options.symbol = format.locale_options.currency_symbol;
				}
			} else if (type == 20) { // Amount (Locale, Without Currency Symbol)
				options.symbol = false;
			} else if (type == 30) { // Accounting (Locale, With Currency Symbol)
				if (!options.hasOwnProperty('symbol')) {
					options.symbol = format.locale_options.currency_symbol;
				}
				if (!options.hasOwnProperty('accounting')) {
					options.accounting = true;
				}
			} else if (type == 40) { // Accounting (Locale, Without Currency Symbol)
				options.symbol = false;
				if (!options.hasOwnProperty('accounting')) {
					options.accounting = true;
				}
			} else if (type == 99) { // Plain Amount
				return amount.toString();
			}
			options.type = type;
		}
		// other settings
		if (!options.hasOwnProperty('decimals')) {
			options.decimals = 2;
		}
		return this.moneyFormat(amount, options);
	},

	/**
	 * Number
	 *
	 * @see Format::amount()
	 */
	number: function(amount, options) {
		if (!options) options = {};
		options.symbol = false;
		return this.amount(amount, options);
	},

	/**
	 * Quantity
	 *
	 * @see Format::amount()
	 */
	quantity: function(amount, options) {
		if (!options) options = {};
		options.symbol = false;
		options.decimals = Numbers.ObjectDataDomains.getSetting('quantity', 'scale');
		return this.amount(amount, options);
	},

	/**
	 * Unit price
	 *
	 * @see Format::amount()
	 */
	unitPrice: function(amount, options) {
		if (!options) options = {};
		options.decimals = Numbers.ObjectDataDomains.getSetting('unit_price', 'scale');
		return this.amount(amount, options);
	},

	/**
	 * Unit price (no symbol)
	 *
	 * @see Format::amount()
	 */
	unitPrice2: function(amount, options) {
		if (!options) options = {};
		options.symbol = false;
		options.decimals = Numbers.ObjectDataDomains.getSetting('unit_price', 'scale');
		return this.amount(amount, options);
	},

	/**
	 * Currency Rate
	 *
	 * @param float amount
	 * @param array options
	 * @return string
	 */
	currencyRate: function(amount, options = []) {
		if (!options) options = {};
		options.decimals = Numbers.ObjectDataDomains.getSetting('currency_rate', 'scale');
		return this.amount(amount, options);
	},

	/**
	 * Id
	 *
	 * @param mixed id
	 * @param array options
	 */
	id: function(id, options) {
		return this.numberToFromNativeLanguage(id, options);
	},

	/**
	 * Translate a number to/from native language
	 *
	 * @param string $amount
	 * @param array $options
	 * @return string
	 */
	numberToFromNativeLanguage: function(number, options, from) {
		if (Numbers.Format.__custom) {
			if (!from) {
				if (Numbers.Format.__custom.amount) {
					number = Numbers.Format.__custom.amount(number, options);
				}
			} else {
				if (Numbers.Format.__custom.readFloatval) {
					number = Numbers.Format.__custom.readFloatval(number, options);
				}
			}
		}
		return number;
	},

	/**
	 * Money format
	 *
	 * @param string amount
	 * @param object options
	 * @returns string
	 */
	moneyFormat: function(amount, options) {
		if (!options) options = {};
		var format = array_key_get(Numbers, 'flag.global.format');
		if (!options.hasOwnProperty('decimals')) {
			options.decimals = 2;
		}
		if (options.symbol) {
			options.symbol = options.symbol.replace(format.locale_options.mon_decimal_point, format.locale_options.mon_thousands_sep);
		}
		if (typeof amount !== 'string') {
			amount = amount.toFixed(options.decimals).toString();
		}
		var negative = /[\-]/.test(amount);
		amount = amount.replace(/\-/g, '');
		// if the number portion has been formatted
		if (!options.amount_partially_formatted) {
			var temp = amount.split('.');
			var number = temp.shift(), fraction = '';
			if (temp.length > 0) {
				fraction = temp.shift();
			}
			// process number
			if (number == '') number = '0';
			if (format.locale_options.mon_thousands_sep + '' != '') {
				var temp = '', counter = 0, mon_grouping;
				for (var i = number.length - 1; i >= 0; i--) {
					// grab group size
					if (counter == 0) {
						if (!mon_grouping) mon_grouping = format.locale_options.mon_grouping;
						if (mon_grouping.length > 1) {
							counter = mon_grouping.shift();
						} else {
							counter = mon_grouping[0];
						}
					}
					// skip number of characters
					counter--;
					temp = number[i] + temp;
					if (counter == 0 && i > 0) {
						temp = format.locale_options.mon_thousands_sep + temp;
					}
				}
				number = temp;
			}
			// left precision
			if (options.digits) {
				if (number.length < options.digits) {
					number = mb_str_pad(number, options.digits, ' ', 'left')
				}
			}
			// right precision
			if (options.decimals > 0) {
				fraction = mb_str_pad(fraction, options.decimals, '0', 'right').substring(0, options.decimals);
				number = number + format.locale_options.mon_decimal_point + fraction;
			}
		} else {
			var number = amount;
		}
		// convert number to native locale
		number = this.numberToFromNativeLanguage(number);
		// format based on settings
		var cs_precedes = negative ? format.locale_options.n_cs_precedes : format.locale_options.p_cs_precedes;
		var sep_by_space = negative ? format.locale_options.n_sep_by_space : format.locale_options.p_sep_by_space;
		var sign_posn = negative ? format.locale_options.n_sign_posn : format.locale_options.p_sign_posn;
		// if we are formatting
		if (options.accounting) {
			if (options.symbol) {
				number = (cs_precedes ? (options.symbol + (sep_by_space === 1 ? ' ' : '')) : '') + number + (!cs_precedes ? ((sep_by_space === 1 ? ' ' : '') + options.symbol) : '');
			}
			if (negative) {
				number = '(' + number + ')';
			} else {
				number = ' ' + number + ' ';
			}
		} else {
			var positive_sign = format.locale_options.positive_sign, negative_sign = format.locale_options.negative_sign;
			var sign = negative ? negative_sign : positive_sign, other_sign = negative ? positive_sign : negative_sign;
			var sign_padding = sign_posn ? new Array(other_sign.length - sign.length + 1).join(' ') : '';
			switch (sign_posn) {
				case 0: // parentheses surround value and currency symbol
					if (options.symbol) {
						number = (cs_precedes ? (options.symbol + (sep_by_space === 1 ? ' ' : '')) : '') + number + (!cs_precedes ? ((sep_by_space === 1 ? ' ' : '') + options.symbol) : '');
					}
					number = '(' + number + ')';
					break;
				case 1: // sign precedes
					if (options.symbol) {
						number = cs_precedes ? (options.symbol + (sep_by_space === 1 ? ' ' : '') + number) : (number + (sep_by_space === 1 ? ' ' : '') + options.symbol);
					}
					number = sign_padding + sign + (sep_by_space === 2 ? ' ' : '') + number;
					break;
				case 2: // sign follows
					if (options.symbol) {
						number = cs_precedes ? (options.symbol + (sep_by_space === 1 ? ' ' : '') + number) : (number + (sep_by_space === 1 ? ' ' : '') + options.symbol);
					}
					number = number + (sep_by_space === 2 ? ' ' : '') + sign + sign_padding;
					break;
				case 3: //sign precedes currency symbol
					var symbol = '';
					if (options.symbol) {
						symbol = cs_precedes ? (options.symbol + (sep_by_space === 1 ? ' ' : '')) : ((sep_by_space === 2 ? ' ' : '') + options.symbol);
					}
					number = cs_precedes ? (sign_padding + sign + (sep_by_space === 2 ? ' ' : '') + symbol + number) : (number + (sep_by_space === 1 ? ' ' : '') + sign + sign_padding + symbol);
					break;
				case 4: // sign succeeds currency symbol
					var symbol = '', symbol_sep = '';
					if (options.symbol) {
						symbol = options.symbol;
						symbol_sep = (sep_by_space === 1 ? ' ' : '');
					}
					number = cs_precedes ? (symbol + (sep_by_space === 2 ? ' ' : '') + sign_padding + sign + symbol_sep + number) : (number + symbol_sep + symbol + (sep_by_space === 2 ? ' ' : '') + sign + sign_padding);
					break;
			}
		}
		return number;
	},

	/**
	 * First name
	 *
	 * @param string name
	 * @returns string
	 */
	firstName: function(name) {
		var result = name.trim().split(' ');
		if (result.length <= 2) {
			return result[0];
		} else {
			return result[0] + ' ' + result[1];
		}
	}
};