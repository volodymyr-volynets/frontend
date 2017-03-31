/**
 * Functions
 */

/**
 * Check if value is numeric
 *
 * @param mixed value
 * @returns boolean
 */
function is_numeric(value) {
	if (value === "") return false;
	return !isNaN(value * 1);
}

/**
 * Check if value is an array
 *
 * @param mixed value
 * @returns boolean
 */
function is_array(value) {
	return Array.isArray(value);
}

/**
 * Intval
 *
 * @param mixed value
 * @returns intager
 */
function intval(value) {
	var temp = parseInt(value);
	if (isNaN(temp)) {
		temp = 0;
	}
	return temp;
}

/**
 * Check if value is in array
 *
 * @param mixed needle
 * @param array haystack
 * @param boolean strict
 * @returns boolean
 */
function in_array(needle, haystack, strict) {
	var key = '', strict = !!strict;
	if (strict) {
		for (key in haystack) {
			if (haystack[key] === needle) {
				return true;
			}
		}
	} else {
		for (key in haystack) {
			if (haystack[key] == needle) {
				return true;
			}
		}
	}
	return false;
}

/**
 * Check if value is empty
 *
 * @param mixed value
 * @returns boolean
 */
function empty(value) {
	var key;
	if (value === "" || value === 0 || value === "0" || value === null || value === false || typeof value === 'undefined') {
		return true;
	}
	if (typeof value == 'object') {
		for (key in value) {
			return false;
		}
		return true;
	}
	return false;
}

/**
 * Check if value is set
 *
 * @returns boolean
 */
function isset() {
	var a = arguments, l = a.length, i = 0, undef;
	if (l === 0) {
		throw new Error('Empty isset');
	}
	while (i !== l) {
		if (a[i] === undef || a[i] === null) {
			return false;
		}
		i++;
	}
	return true;
}

/**
 * Convert value to human readible format
 *
 * @param mixed value
 * @param int max
 * @param string sep
 * @param int l
 * @returns string
 */
function print_r(value, max, sep, l) {
	l = l || 0;
	max = max || 99;
	sep = sep || ' ';
	var x = value;
	if (l > max) {
		return "[WARNING: Too much recursion]\n";
	}
	var i, r = '', t = typeof x, tab = '';
	if (x === null) {
		r += "(null)\n";
	} else if (t == 'object') {
		l++;
		for (i = 0; i < l; i++) {
			tab += sep;
		}
		if (x && x.length) {
			t = 'array';
		}
		r += '(' + t + ") :\n";
		for (i in x) {
			try {
				r += tab + '[' + i + '] : ' + print_r(x[i], max, sep, (l + 1));
			} catch (e) {
				return "[ERROR: " + e + "]\n";
			}
		}
	} else {
		if (t == 'string') {
			if (x == '') {
				x = '(empty)';
			}
		}
		r += '(' + t + ') ' + x + "\n";
	}
	return r;
}

/**
 * Alert value to the screen, used when debugging
 *
 * @param mixed value
 */
function print_r2(value) {
	alert(print_r(value));
}

/**
 * Get value from object by keys
 *
 * @param object arr
 * @param mixed keys
 * @returns mixed
 */
function array_key_get(arr, keys) {
	if (keys == null || (keys instanceof Array && keys.length == 0)) {
		return arr;
	} else {
		// convert non arrays to array
		if (!(keys instanceof Array)) {
			keys = keys.toString().replace(/\./g, ',').split(',');
		}
		// loop though keys
		var key = keys.shift();
		if (arr.hasOwnProperty(key)) {
			return array_key_get(arr[key], keys);
		} else {
			return null;
		}
	}
}

/**
 * Set value in object by keys
 *
 * @param object arr
 * @param mixed keys
 * @param mixed value
 * @returns object
 */
function array_key_set(arr, keys, value) {
	if (keys == null) {
		arr = value;
	} else {
		// convert non arrays to array
		if (!(keys instanceof Array)) {
			keys = keys.toString().replace(/\./g, ',').split(',');
		}
		// loop though keys
		var key = keys.shift();
		if (!arr.hasOwnProperty(key)) {
			arr[key] = {};
		}
		if (keys.length == 0) {
			arr[key] = value;
		} else {
			arr[key] = array_key_set(arr[key], keys, value);
		}
	}
	return arr;
}

/**
 * Strip tags
 *
 * @param string str
 * @param string allowable_tags
 * @returns string
 */
function strip_tags(str, allowable_tags) {
	allowable_tags = (((allowable_tags || '') + '').toLowerCase().match(/<[a-z][a-z0-9]*>/g) || []).join('');
	return str.replace(/<!--[\s\S]*?-->|<\?(?:php)?[\s\S]*?\?>/gi, '').replace(/<\/?([a-z][a-z0-9]*)\b[^>]*>/gi, function($0, $1) {
		return allowable_tags.indexOf('<' + $1.toLowerCase() + '>') > -1 ? $0 : '';
	});
}

/**
 * Pad string
 *
 * @param string input
 * @param int length
 * @param string string
 * @param string type
 *		string left
 *		string right
 *		string both
 * @returns string
 */
function mb_str_pad(input, length, string, type) {
	if (!type) type = 'right';
	if (!isset(string)) string = ' ';
	if (!isset(input)) input = '';
	if (type == 'right') {
		while (input.length < length) {
			input = input + string;
		}
	} else if (type == 'left') {
		while (input.length < length) {
			input = string + input;
		}
	} else if (type == 'both') {
		// if not an even number, the right side gets the extra padding
		var counter = 1;
		while (input.length < length) {
			if (counter % 2) {
				input+= string;
			} else {
				input = string + input;
			}
			counter++;
		}
	}
    return input;
}

/**
 * Contains method to determine if value exists in select
 *
 * @param string value
 * @returns boolean
 */
HTMLSelectElement.prototype.value_exists = function(value) {
    for (var i = 0, l = this.options.length; i < l; i++) {
        if (this.options[i].value == value) {
            return true;
        }
    }
    return false;
};

/**
 * i18n, alias
 *
 * @param mixed i18n
 * @param mixed text
 * @param array options
 * @return string
 */
var i18n = function(i18n, text, options) {
	return Numbers.I18n.get(i18n, text, options);
};

/**
 * i18n if
 *
 * @param type text
 * @param type translate
 * @return string
 */
var i18n_if = function(text, translate) {
	if (translate) {
		return Numbers.I18n.get(null, text, options);
	} else {
		return text;
	}
};