/**
 * Numbers checkbox object
 *
 * @param array options
 *		id - id of element
 *		label_on - on label
 *		label_off - off label
 *		oposite_checkbox - show oposite labels
 */
var numbers_checkbox = function (options) {
	// initializing object
	var result = new Object();
	result.id = options.id;
	result.elem = document.getElementById(options.id);
	result.var_id = 'numbers_checkbox_var_' + result.id;
	result.div_id = options.id + '_checkbox_div';
	result.checked = result.elem.checked;
	result.oposite_checkbox = options.oposite_checkbox ? true : (result.elem.getAttribute('data-oposite_checkbox') ? true : false);
	// labels
	var temp = result.elem.getAttribute('data-label_on');
	var label_on = options.label_on ? options.label_on : (temp ? temp : 'Yes');
	var temp = result.elem.getAttribute('data-label_off');
	var label_off = options.label_off ? options.label_off : (temp ? temp : 'No');
	// replacement elements
	var container = document.createElement("div");
	container.style.position = 'relative';
	container.style.textAlign = 'left';
	container.className = 'numbers_frontend_components_select_numbers_base_group';
	container.id = result.div_id;
	container.innerHTML = '<div class="numbers_frontend_components_select_numbers_base_wrapper">\n\
								<div class="numbers_frontend_components_select_numbers_base_toggle_on numbers_prevent_selection">' + numbers.i18n.get(null, label_on) + '</div>\n\
								<span class="numbers_frontend_components_select_numbers_base_toggle_middle numbers_prevent_selection">&nbsp;</span>\n\
								<div class="numbers_frontend_components_select_numbers_base_toggle_off numbers_prevent_selection">' + numbers.i18n.get(null, label_off) + '</div>\n\
							</div>';
	result.elem.parentNode.insertBefore(container, result.elem.nextSibling);
	// hide select element
	result.elem.style.display = 'none';
	// attach onclick event
	result.div_elem = document.getElementById(result.div_id);
	result.div_elem.onclick = function() {
		result.onclick();
	};
	result.onclick = function() {
		if (this.checked) {
			this.elem.checked = this.checked = false;
		} else {
			this.elem.checked = this.checked = true;
		}
		//this.elem.click();
		result.update();
	}
	// onclick processor
	result.update = function() {
		var temp = this.checked;
		if (this.oposite_checkbox) temp = !temp;
		if (temp) {
			this.div_elem.classList.remove("numbers_frontend_components_select_numbers_base_off");
			this.div_elem.className+= ' numbers_frontend_components_select_numbers_base_on';
		} else {
			this.div_elem.classList.remove("numbers_frontend_components_select_numbers_base_on");
			this.div_elem.className+= ' numbers_frontend_components_select_numbers_base_off';
		}
	};
	// update element
	result.update();
	// we need to set a variable in global scope
	window[result.var_id] = result;
};

/**
 * Extending JQuery if loaded
 */
if (window.jQuery) {
	(function ($) {
		$.fn.numbers_checkbox = function (options) {
			if (!options) options = {};
			// loop through all elements
			return this.each(function () {
				var elem = $(this), id = elem.attr('id'), options2 = $.extend({}, options);
				if (!id) {
					id = 'numbers_checkbox_random_generated_id_' + Math.round(Math.random() * 1000) + '_' + Math.round(Math.random() * 1000) + '_' + Math.round(Math.random() * 1000);
					elem.attr('id', id);
				}
				options2.id = id;
				numbers_checkbox(options2);
			});
		};
	})(jQuery);
}