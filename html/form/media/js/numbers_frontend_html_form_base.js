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
	 * @param object form
	 * @param string button
	 */
	trigger_submit: function(form, button) {
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
        tr.css("background-color", "lightcoral");
		tr.find("td").fadeOut(400, function() {
			tr.remove();
			that.trigger_submit(form)
		});
	}
};