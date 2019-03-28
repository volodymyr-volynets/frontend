/**
 * Numbers object
 *
 * @type object
 */
if (!Numbers) {
	var Numbers = {

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
		 * Locks used by application
		 *
		 * @type object
		 */
		locks: {},

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
		Error: {
			count: 0,
			init: function() {
				window.onerror = function (message, file, line, col, error) {
					Numbers.Error.count++;
					// if we have toolbar
					if ($('#debuging_toolbar_js_a').length) {
						$('#debuging_toolbar_js_a').html('Js (' + Numbers.Error.count + ')');
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
					Numbers.Error.sendData(data);
				};
			},
			sendData: function(data) {
				var img = document.createElement('img');
				var src = '/Numbers/Frontend/System/Controller/Error?token=' + encodeURIComponent(Numbers.token) + '&data=' + encodeURIComponent(JSON.stringify(data));
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
		I18n: {

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
				if (Numbers.I18n.hasOwnProperty('__custom')) {
					text = Numbers.I18n.__custom.get(i18n, text, options);
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
				var format = array_key_get(Numbers, 'flag.global.format');
				return !empty(format.rtl);
			}
		},

		/**
		 * Domains
		 *
		 * @type object
		 */
		ObjectDataDomains: {

			/**
			 * Data
			 *
			 * @type object
			 */
			data: {},

			/**
			 * Get setting
			 *
			 * @param string domain
			 * @param string property
			 * @returns mixed
			 */
			getSetting: function(domain, property) {
				var keys = [];
				if (isset(domain)) {
					keys.push(domain);
					if (isset(property)) {
						keys.push(property);
					}
				}
				return array_key_get(this.data, keys);
			}
		},

		/**
		 * Currencies
		 *
		 * @type object
		 */
		CountriesCurrencies: {

			/**
			 * Data
			 *
			 * @type object
			 */
			data: {}
		},

		/* Widgets */
		Widgets: {},

		/* Other data */
		OtherData: {},

		/**
		 * Preload new js
		 *
		 * @param array js
		 * @return boolean
		 */
		preloadNewJs: function(js) {
			var new_scripts = false;
			for (var i in js) {
				if (!script_exists(js[i])) {
					let script = document.createElement('script');
					script.src = js[i];
					script.async = true;
					script.type = 'text/javascript';
					document.getElementsByTagName('head')[0].appendChild(script);
					new_scripts = true;
				}
			}
			return new_scripts;
		},

		/**
		 * Preload new CSS
		 *
		 * @param array css
		 * @returns boolean
		 */
		preloadNewCss: function(css) {
			var new_scripts = false;
			for (var i in css) {
				if (!style_exists(css[i])) {
					$("<link/>", {
						rel: "stylesheet",
						type: "text/css",
						href: css[i]
					}).appendTo("head");
					new_scripts = true;
				}
			}
			return new_scripts;
		}
	};

	// initializing
	Numbers.Error.init();
}