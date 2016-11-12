/**
 * List
 *
 * @type object
 */
numbers.frontend_list = {

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
			data: $('#' + form_id).serialize() + '&__ajax=1',
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
		return false;
	},

	/**
	 * Trigger submit through submit button
	 *
	 * @param object form
	 */
	trigger_submit: function(form) {
		$("[name='submit_hidden']", "#" + $(form).attr('id')).submit();
	}
}