
numbers.frontend_list = {
	submit: function(obj) {
		// some functions would require full form submittion
		var no_ajax = $(obj).attr('no_ajax');
		if (no_ajax) {
			return true;
		}
		// proceed with ajax call
		var form_id = $(obj).attr('id');
		var wrapper_id = form_id + '_wrapper';
		var mask_id = form_id + '_mask';
		$('#' + mask_id).mask({overlayOpacity: 0.25, delay: 0});
		// making ajax call to backend
		var request = $.ajax({
			url: numbers.controller_full,
			method: 'POST',
			data: $('#' + form_id).serialize() + '&__ajax=1',
			dataType: "json"
		}).done(function(data) {
			if (data.success) {
				$('#' + wrapper_id).html(data.html);
				eval(data.js);
				// remove mask after 100 miliseconds to let js to take affect
				setTimeout(function() {
					$('#' + mask_id).unmask();
				}, 100);
			}
		}).fail(function(jqXHR, textStatus) {
			print_r2(textStatus);
		});
		return false;
	}
}