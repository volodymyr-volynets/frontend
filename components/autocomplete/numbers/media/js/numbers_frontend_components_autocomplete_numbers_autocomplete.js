/**
 * Numbers autocomplete object
 *
 * @param array options
 *		multiple - whether its a multiple
 */
var numbers_autocomplete = function (options) {
	// initializing object
	var result = new Object();
	result.id = options.id;
	result.form_id = options.form_id;
	result.name = options.name;
	result.name_hidden = result.id + '_hidden_class';
	result.elem = document.getElementById(options.id);
	result.var_id = 'numbers_autocomplete_var_' + result.id;
	result.div_id = result.id + '_div';
	result.div_id_content = result.div_id + '_content';
	result.multiple = false;
	if (options.multiple) {
		result.multiple = true;
	}
	result.rn_attrattr_id = null;
	if (options.rn_attrattr_id) {
		result.rn_attrattr_id = options.rn_attrattr_id;
	}
	result.values = {};
	if (options.values) {
		result.values = options.values;
	}
	// create div
	result.div_elem = document.getElementById(result.div_id);
	// onfocus/onblur handlers
	result.elem.onfocus = function() {
		window[result.var_id].onfocus();
	};
	result.elem.onblur = function() {
		window[result.var_id].onfocus(true);
	};
	result.elem.onkeyup = function (event) {
		window[result.var_id].onkeyup(event);
	};
	// autocomplete specific flags
	result.flag_is_focused = false;

	/**
	 * Onkeydown handler
	 */
	result.onkeydown = function(event) {
		if (!this.multiple) {
			return;
		}
		if (event) {
			var code = event.which || event.keyCode;
			// if backspace or delete
			if (!in_array(code, [8, 46])) {
				return;
			}
		}
		// we stop deletition if we have empty text
		var text = this.get_search_input();
		if (!text) {
			event.preventDefault();
			event.stopPropagation();
		}
	};

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
		// filtering
		this.query();
	};

	/**
	 * Query server
	 */
	result.query = function () {
		var text = this.get_search_input();
		var data = {
			__ajax: true,
			__ajax_form_id: this.form_id,
			__ajax_autocomplete: {
				id: this.id,
				name: this.name,
				rn_attrattr_id: this.rn_attrattr_id,
				text: text
			}
		};
		// show spinner while waiting for results
		$('#' + result.div_id_content).html('<i class="fa fa-spinner fa-spin"></i>');
		$.ajax({
			url: numbers.controller_full,
			method: 'post',
			data: data,
			dataType: 'json',
			success: function (data) {
				if (data.success) {
					$('#' + result.div_id_content).html(data.html);
				}
			},
			error: function (jqXHR, textStatus, errorThrown) {
				print_r2(jqXHR.responseText);
			}
		});
	};

	/**
	 * Choose
	 *
	 * @param mixed id
	 */
	result.choose = function (id, name) {
		if (this.multiple) {
			if (!this.values[id]) {
				this.values[id] = name;
				$('<input>').attr({
					type: 'hidden',
					class: this.name_hidden,
					name: this.name + '[]',
					value: id
				}).insertAfter($(this.elem));
				this.close();
			}
		} else {
			// reset values
			this.values = {};
			this.values[id] = name;
			$('.' + result.name_hidden).val(id);
			this.close();
		}
	};

	/**
	 * Unchoose
	 *
	 * @param string id
	 */
	result.unchoose = function (id) {
		if (this.multiple) {
			if (this.values[id]) {
				$('.' + this.name_hidden).each(function() {
					if ($(this).val() == id) {
						this.remove();
					}
				});
				delete this.values[id];
			}
			this.close();
		}
	}

	/**
	 * Get searc input
	 *
	 * @returns string
	 */
	result.get_search_input = function () {
		var text = $(this.elem).html();
		return text.replace(/<([^>]+?)([^>]*?)>(.*?)<\/\1>/ig, '').toLowerCase().replace(/&nbsp;/g, ' ').replace('<br>', '').trim();
	};

	/**
	 * Onfocus processor
	 * @param boolean only_postponed_check
	 */
	result.onfocus = function (only_postponed_check, cancelation) {
		// we need to clear a selection
		if (cancelation) {
			window.getSelection().removeAllRanges();
		}
		// if we are processing postponed onblur
		if (only_postponed_check) {
			this.flag_is_focused = false;
			var that = this;
			setTimeout(function () {
				if (!that.check_if_focused()) {
					that.close();
				}
			}, 200);
		} else {
			this.flag_is_focused = true;
			this.elem.focus();
			// if not multiple we empty
			if (!this.multiple) {
				this.elem.innerHTML = '';
			}
			// we show empty div
			$('#' + result.div_id_content).html('');
			$('#' + result.div_id).show();
		}
	};

	/**
	 * Check for flag_is_focused flag
	 * @returns boolean
	 */
	result.check_if_focused = function () {
		return this.flag_is_focused;
	};

	/**
	 * Close
	 */
	result.close = function () {
		this.render_value();
		this.div_elem.style.display = 'none';
		// handle selection
		var obj = window.getSelection();
		if (obj.anchorNode && obj.anchorNode.parentNode.id == this.id) {
			obj.removeAllRanges();
		}
		this.elem.blur();
	};

	/**
	 * Render value
	 */
	result.render_value = function () {
		var html = '';
		if (this.multiple) {
			var text = this.get_search_input();
			this.elem.innerHTML = '';
			for (var i in this.values) {
				var span = document.createElement("span");
				span.setAttribute('contenteditable', false);
				html = '';
				html+= this.values[i];
				html+= ' <a href="javascript: void(0);" class="numbers_autocomplete_option_multiple_item_close" onclick="window[\'' + result.var_id + '\'].unchoose(\'' + i + '\');"><i class="fa fa-times"></i></a> ';
				span.innerHTML = html;
				span.className = 'numbers_autocomplete_multiple_item numbers_autocomplete_noneditable_item';
				this.elem.appendChild(span);
				this.elem.innerHTML+= '&nbsp;&nbsp;';
			}
			//this.elem.innerHTML+= text;
		} else {
			var value = $('.' + result.name_hidden).val();
			if (this.values[value]) {
				html+= this.values[value];
			} else {
				html+= value;
			}
			result.elem.innerHTML = html;
		}
	};

	/**
	 * Empty
	 */
	result.empty = function () {
		if (this.multiple) {
			$('.' + this.name_hidden).each(function() {
				this.remove();
			});
			this.values = {};
		} else {
			$('.' + this.name_hidden).val('');
		}
		window.getSelection().removeAllRanges();
		this.close();
	}

	// we need to set a variable in global scope
	result.render_value();
	window[result.var_id] = result;
};

/**
 * Extending JQuery if loaded
 */
if (window.jQuery) {
	(function ($) {
		$.fn.numbers_autocomplete = function (options) {
			if (!options) options = {};
			// loop through all elements
			return this.each(function () {
				var elem = $(this), id = elem.attr('id'), options2 = $.extend({}, options);
				if (!id) {
					id = 'numbers_autocomplete_random_generated_id_' + Math.round(Math.random() * 1000) + '_' + Math.round(Math.random() * 1000) + '_' + Math.round(Math.random() * 1000);
					elem.attr('id', id);
				}
				options2.id = id;
				numbers_autocomplete(options2);
			});
		};
	})(jQuery);
}