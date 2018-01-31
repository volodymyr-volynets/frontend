// special variable to handle buttons
var numbers_frontend_form_submit_hidden_initiator = null;

/**
 * Form
 *
 * @type object
 */
Numbers.Form = {

	/**
	 * Data
	 *
	 * @type object
	 */
	data: {},

	/**
	 * Get form data
	 *
	 * @param object form_or_element
	 * @return object
	 */
	getFormData: function(form_or_element) {
		var form = this.getForm(form_or_element);
		var name = form.attr('name');
		if (name in this.data) {
			return this.data[name];
		} else {
			return {};
		}
	},

	/**
	 * This function would be called when user submits the form
	 *
	 * @param object form
	 * @returns boolean
	 */
	onFormSubmit: function(form) {
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
		// form data
		var form_data = new FormData(document.getElementById(form_id));
		form_data.append('__ajax', 1);
		form_data.append(numbers_frontend_form_submit_hidden_initiator, 1);
		form_data.append('__ajax_form_id', form_id);
		$('input[type=file]').each(function() {
			if ($(this)[0].files[0]) {
				var file = $(this)[0].files[0];
				var filename = $(this).attr("data-filename");
				var	name = $(this).attr("name");
				form_data.append(name, file, filename);
			}
		});
		// send data to the server
		$.ajax({
			url: Numbers.controller_full,
			method: 'post',
			data: form_data,
			dataType: 'json',
			cache: false,
			contentType: false,
			processData: false,
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
				} else {
					// todo: open error dialog in popup window
					print_r2(data.error);
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
	triggerSubmit: function(form_or_element, button) {
		// make sure we have a form
		form_or_element = $(form_or_element);
		if (form_or_element.is('form')) {
			var form = form_or_element;
		} else {
			var form = form_or_element.closest('form');
			$("[name='__form_onchange_field_values_key']", "#" + $(form).attr('id')).val(form_or_element.attr('data-field_values_key'));
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
	triggerSubmitOnButton: function(button) {
		numbers_frontend_form_submit_hidden_initiator = $(button).attr('name');
	},

	/**
	 * Details: delete row
	 *
	 * @param string row_id
	 */
	detailsDeleteRow: function(form_id, row_id) {
		var tr = $('#' + row_id), that = this, form = $('#' + form_id);
        tr.css('background-color', 'lightcoral');
		tr.find('td').fadeOut(400, function() {
			tr.remove();
			that.triggerSubmit(form)
		});
	},

	/**
	 * Mapping for error message types, important data must be in reverse order
	 *
	 * @type object
	 */
	ErrorMap: {
		success: 'has-success',
		warning: 'has-warning',
		danger: 'has-error'
	},

	/**
	 * Add an error to the form or field
	 *
	 * @param element form_or_element
	 * @param string type
	 * @param string message
	 * @param string field
	 * @param object options
	 */
	error: function(form_or_element, type, message, field, options) {
		if (!options) options = {};
		// if its an array of message we process them one by one
		if (is_array(message)) {
			for (var i in message) {
				this.error(form_or_element, type, message[i], field, options);
			}
			return;
		}
		// generate hash
		var hash = sha1(message);
		// i18n
		if (!options.skip_i18n) {
			message = i18n(null, message, options);
		}
		var form = this.getForm(form_or_element);
		if (field) {
			var field_element = $('[name="' + field + '"]', $(form));
			var form_group = $(field_element).closest('.form-group');
			var text_class = 'text-' + (isset(type) ? type : 'primary');
			if (type == 'reset') {
				form_group.children('.numbers_field_error_messages').remove();
			} else {
				form_group.find('div[data-field_value_hash="' + hash + '"]').remove();
				form_group.append('<div class="numbers_field_error_messages ' + text_class + '" data-field_value_hash="' + hash + '">' + message + '</div>');
			}
			// update form group class
			form_group.removeClass('has-warning has-error has-success');
			var class_name;
			for (var i in this.error_map) {
				if (form_group.find('.numbers_field_error_messages.text-' + i).length > 0) {
					class_name = this.error_map[i];
				}
			}
			if (class_name) {
				form_group.addClass(class_name);
			}
		} else {
			var message_continer = $(form).find('.form_message_container');
			if (message_continer.find('.alert-' + type).length === 0) {
				message_continer.append('<div role="alert" class="alert alert-' + type + '"><ul></ul></div>');
			}
			message_continer.find('ul').prepend('<li>' + message + '</li>');
		}
	},

	/**
	 * Get form
	 *
	 * @param mixed form_or_element
	 * @returns object
	 */
	getForm: function(form_or_element) {
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
	getAllValues: function(form) {
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
	getPath: function(element, neighbour) {
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
	 * @param string neighbour
	 * @param boolean last
	 * @returns string
	 */
	getName: function(element, neighbour, last) {
		if (last) {
			return this.getPath(element).pop();
		} else if (neighbour) {
			var path = this.getPath(element, neighbour);
			if (path instanceof Array) {
				var name = path.shift();
				for (var i in path) {
					name+= '[' + path[i] + ']';
				}
				return name;
			} else {
				return path;
			}
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
	getValue: function(values, path, element, neighbour) {
		if (path) {
			return array_key_get(values, path);
		} else if (element) {
			return array_key_get(values, this.getPath(element, neighbour));
		}
	},

	/**
	 * Set value
	 *
	 * @param mixed form
	 * @param mixed path
	 * @param mixed value
	 */
	setValue: function(form, path, value) {
		if (path instanceof Array) {
			var name = path.shift();
			for (var i in path) {
				name+= '[' + path[i] + ']';
			}
		} else {
			var name = path;
		}
		$('[name="' + addslashes(name) + '"]', $(form)).val(value);
	},

	/**
	 * Locks
	 *
	 * @type object
	 */
	Locks: {},

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
		var lock = this.getForm(element).attr('name') + '::' + $(element).attr('name');
		switch (action) {
			case 'lock':
				if (!duration) duration = this.lock_timeout - 100;
				this.Locks[lock] = (new Date()).getTime() + duration;
				break;
			case 'is_locked':
				if (!this.Locks[lock]) {
					return false;
				} else if (this.Locks[lock] < (new Date()).getTime()) {
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
	Focuses: {},

	/**
	 * Focus
	 *
	 * @param object element
	 * @param boolean end
	 */
	focus: function(element, end) {
		var focus = this.getForm(element).attr('name') + '::' + $(element).attr('name');
		if (!$(element).is(':focus')) {
			this.Focuses[focus] = null;
			return;
		}
		// record position
		if (!end) {
			this.Focuses[focus] = {
				selection_start: element.selectionStart,
				selection_end: element.selectionEnd
			};
		} else if (this.Focuses[focus]) {
			element.setSelectionRange(this.Focuses[focus].selection_start, this.Focuses[focus].selection_end);
		}
	},

	/**
	 * List filter/sort toggle
	 *
	 * @param mixed form
	 */
	listFilterSortToggle: function(form_or_element, show) {
		var form = this.getForm(form_or_element);
		var data = this.getFormData(form_or_element);
		if (data.has_errors) {
			$('.numbers_form_filter_sort_container', form).show();
		} else if (show) {
			if (data.submitted || data.list_rendered) {
				$('.numbers_form_filter_sort_container', form).hide();
			} else {
				$('.numbers_form_filter_sort_container', form).show();
			}
		} else {
			$('.numbers_form_filter_sort_container', form).toggle();
		}
	}
}

/**
 * Report
 *
 * @type object
 */
Numbers.Form.Report = {

	/**
	 * Format change event
	 *
	 * @param object element
	 */
	onFormatChanged: function(element) {
		if ($(element).val() == 'printable') {
			element.form.target = '_blank';
		} else {
			element.form.target = '';
		}
	}
}