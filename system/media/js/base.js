/**
 * Numbers object
 *
 * @type object
 */
var numbers = {

	/**
	 * Token for communication with backend
	 *
	 * @type string
	 */
	token: null,

	/**
	 * System flags
	 *
	 * @type object
	 */
	flag: {},

	/**
	 * Generate url
	 *
	 * @param mixed controller
	 * @param string action
	 * @param mixed id
	 * @returns {String}
	 */
	url: function(controller, action, id, options) {
		var result = [];
		// processng controller
		if (Array.isArray(controller)) {
			result = controller;
		} else {
			controller = controller + '';
			if (controller[0] == '/') {
				result.push(controller.substr(1, controller.length()));
			} else if (controller.indexOf('.') != -1) {
				result = controller.split('.');
			} else if (controller.indexOf('_') != -1) {
				result = controller.split('_');
			} else {
				if (!controller) {
					controller = 'index';
				}
				result.push(controller);
			}
		}
		// processing action
		if (action) {
			result.push('~' + action);
		}
		// processing id
		if (id) {
			if (!action) {
				result.push('~index');
			}
			result.push(id);
		}
		// host
		if (options && options['host']) {
			var host = location.protocol + '//' + location.hostname + (location.port ? ':' + location.port : '');
			return host + '/' + result.join('/');
		} else {
			return '/' + result.join('/');
		}
	},

	/**
	 * Error handling
	 */
	error: {
		count: 0,
		init: function() {
			window.onerror = function (message, file, line, col, error) {
				numbers.error.count++;
				// if we have toolbar
				if ($('#debuging_toolbar_js_a').length) {
					$('#debuging_toolbar_js_a').html('Js (' + numbers.error.count + ')');
					$('#debuging_toolbar_js_a').css('color', 'red');
					var str = '<br/>';
					str+= 'Message: ' + message + '<br/>';
					str+= 'File: ' + file + '<br/>';
					str+= 'Line: ' + line + '<br/>';
					str+= 'Column: ' + col + '<br/>';
					str+= '<hr/>';
					$('#debuging_toolbar_js_data').append(str);
					alert('Javascript Error: ' + message);
				}
				// todo: send data to server for further processing
				var data = {
					message: message,
					file: file,
					line: line,
					col: col
				};
				numbers.error.send_data(data);
			};
		},
		send_data: function(data) {
			var img = document.createElement('img');
			var src = '/numbers/frontend/system/controller/error.png?token=' + encodeURIComponent(numbers.token) + '&data=' + encodeURIComponent(JSON.stringify(data));
			img.crossOrigin = 'anonymous';
			img.onload = function success() {
				//console.log('success', data);
			};
			img.onerror = img.onabort = function failure() {
				//console.error('failure', data);
			};
			img.src = src;
		}
	},

	/**
	 * Extend
	 *
	 * @param object parent
	 * @param object child
	 * @returns object
	 */
	extend: function(parent, child) {
		var temp = function(){};
		temp.prototype = parent.prototype;
		child.prototype = new temp();
		child.parent = parent;
		child.prototype.constructor = child;    
		return child;
	},

	/**
	 * I18n
	 *
	 * @type object
	 */
	i18n: {

		/**
		 * Get translation
		 *
		 * @param string i18n
		 * @param string text
		 * @param array options
		 * @return string
		 */
		get: function(i18n, text, options) {
			if (!options) options = {};
			// translate though used submodule
			if (numbers.i18n.hasOwnProperty('__custom')) {
				text = numbers.i18n.__custom.get(i18n, text, options);
			}
			// if we need to handle replaces, for example:
			//		"Error occured on line [line_number]"
			// important: replaces must be translated/formatted separatly
			if (options.replace) {
				for (var i in options.replace) {
					text = text.replace(i, options.replace[i]);
				}
			}
			return text;
		},

		/**
		 * Rtl
		 *
		 * @returns boolean
		 */
		rtl: function() {
			var format = array_key_get(numbers, 'flag.global.format');
			return !empty(format.rtl);
		}
	}
};

// initializing
numbers.error.init();