// special variable to handle buttons
var numbers_frontend_form_submit_hidden_initiator = null;

/**
 * Form
 *
 * @type object
 */
numbers.form = {

	/**
	 * This function would be called when user submits the form
	 *
	 * @param formect form
	 * @returns boolean
	 */
	on_form_submit: function(form) {
		// some functions would require full form submittion
		var no_ajax = $(form).attr('no_ajax');
		if (no_ajax) {
			return true;
		}
		// proceed with ajax call
		var form_id = $(form).attr('id');
		var wrapper_id = form_id + '_wrapper';
		var mask_id = form_id + '_mask';
		$('#' + mask_id).mask({overlayOpacity: 0.25, delay: 0});
		$.ajax({
			url: numbers.controller_full,
			method: 'post',
			data: $('#' + form_id).serialize() + '&__ajax=1&' + numbers_frontend_form_submit_hidden_initiator + '=1&__ajax_form_id=' + form_id,
			dataType: 'json',
			success: function (data) {
				if (data.success) {
					$('#' + wrapper_id).html(data.html);
					eval(data.js);
					// remove mask after 100 miliseconds to let js to take affect
					setTimeout(function() {
						$('#' + mask_id).unmask();
						// we need to trigger resize to redraw a screen
						$(window).trigger('resize');
					}, 100);
				}
			},
			error: function (jqXHR, textStatus, errorThrown) {
				print_r2(jqXHR.responseText);
			}
		});
		// reset initiator variable
		numbers_frontend_form_submit_hidden_initiator = null;
		return false;
	},

	/**
	 * Trigger submit button
	 *
	 * @param object form/element
	 * @param string button
	 */
	trigger_submit: function(form_or_element, button) {
		// make sure we have a form
		form_or_element = $(form_or_element);
		if (form_or_element.is('form')) {
			var form = form_or_element;
		} else {
			var form = form_or_element.closest('form');
			$("[name='__form_onchange_field_values_key']", "#" + $(form).attr('id')).val(form_or_element.attr('field_values_key'));
		}
		// by default we call refresh
		if (!button) {
			button = '__submit_refresh';
		}
		numbers_frontend_form_submit_hidden_initiator = button;
		$("[name='" + button + "']", "#" + $(form).attr('id')).click();
	},

	/**
	 * Trigger submit through hidden submit buttons
	 *
	 * @param object button
	 */
	trigger_submit_on_button: function(button) {
		numbers_frontend_form_submit_hidden_initiator = $(button).attr('name');
	},

	/**
	 * Details: delete row
	 *
	 * @param string row_id
	 */
	details_delete_row: function(form_id, row_id) {
		var tr = $('#' + row_id), that = this, form = $('#' + form_id);
        tr.css('background-color', 'lightcoral');
		tr.find('td').fadeOut(400, function() {
			tr.remove();
			that.trigger_submit(form)
		});
	},

	/**
	 * Get form
	 *
	 * @param mixed form_or_element
	 * @returns object
	 */
	get_form: function(form_or_element) {
		var form = $(form_or_element);
		if (!form.is('form')) {
			form = form.closest('form');
		}
		return form;
	},

	/**
	 * Get all form values
	 *
	 * @param mixed form
	 * @param object options
	 * @returns object
	 */
	get_all_values: function(form) {
		var result = {};
		// get all elements
		$.each($(form)[0].elements, function(index, elem) {
			var keys = $(elem).attr('name').replace(/\]/g, '').split('[');
			result = array_key_set(result, keys, $(elem).val());
		});
		return result;
	},

	/**
	 * Get path
	 *
	 * @param object element
	 * @returns array
	 */
	get_path: function(element, neighbour) {
		var temp = $(element).attr('name').replace(/\]/g, '').split('[');
		if (neighbour) {
			temp.pop();
			temp.push(neighbour);
		}
		return temp;
	},

	/**
	 * Get name
	 *
	 * @param object element
	 * @returns string
	 */
	get_name: function(element, last) {
		if (last) {
			return this.get_path(element).pop();
		} else {
			return $(element).attr('name');
		}
	},

	/**
	 * Get value
	 *
	 * @param mixed element
	 * @returns array
	 */
	get_value: function(values, path, element, neighbour) {
		if (path) {
			return array_key_get(values, path);
		} else if (element) {
			return array_key_get(values, this.get_path(element, neighbour));
		}
	},

	/**
	 * Set value
	 *
	 * @param mixed form
	 * @param mixed path
	 * @param mixed value
	 */
	set_value: function(form, path, value) {
		if (path instanceof Array) {
			var name = path.shift();
			for (var i in path) {
				name+= '[' + path[i] + ']';
			}
		} else {
			var name = path;
		}
		$('[name="' + name + '"]', $(form)).val(value);
	},

	/**
	 * Locks
	 *
	 * @type object
	 */
	locks: {},

	/**
	 * Lock timeout
	 *
	 * @type int
	 */
	lock_timeout: 2000,

	/**
	 * Lock
	 *
	 * @param mixed element
	 * @param string action
	 * @returns mixed
	 */
	lock: function(element, action, duration) {
		if (!action) {
			action = 'is_locked';
		}
		var lock = this.get_form(element).attr('name') + '::' + $(element).attr('name');
		switch (action) {
			case 'lock':
				if (!duration) duration = this.lock_timeout - 100;
				this.locks[lock] = (new Date()).getTime() + duration;
				break;
			case 'is_locked':
				if (!this.locks[lock]) {
					return false;
				} else if (this.locks[lock] < (new Date()).getTime()) {
					return false;
				} else {
					return true;
				}
				break;
		}
	},

	/**
	 * Focuses
	 *
	 * @type object
	 */
	focuses: {},

	/**
	 * Focus
	 *
	 * @param object element
	 * @param boolean end
	 */
	focus: function(element, end) {
		var focus = this.get_form(element).attr('name') + '::' + $(element).attr('name');
		if (!$(element).is(':focus')) {
			this.focuses[focus] = null;
			return;
		}
		// record position
		if (!end) {
			this.focuses[focus] = {
				selection_start: element.selectionStart,
				selection_end: element.selectionEnd
			};
		} else if (this.focuses[focus]) {
			element.setSelectionRange(this.focuses[focus].selection_start, this.focuses[focus].selection_end);
		}
	}
}