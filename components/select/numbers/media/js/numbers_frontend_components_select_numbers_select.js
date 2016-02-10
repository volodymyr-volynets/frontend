/**
 * Numbers select object
 *
 * @param array options
 *		searchable - whether search is enabled
 *		tree - whether we have multi-level tree
 *		color_picker - whether its a color select
 */
var numbers_select = function (options) {
	// initializing object
	var result = new Object();
	result.id = options.id;
	result.elem = document.getElementById(options.id);
	result.searchable = options.searchable ? options.searchable : (result.elem.getAttribute('searchable') == 'searchable' ? true : false);
	result.tree = options.tree ? options.tree : (result.elem.getAttribute('tree') == 'tree' ? true : false);
	result.color_picker = options.color_picker ? options.color_picker : (result.elem.getAttribute('color_picker') == 'color_picker' ? true : false);
	result.optgroups = result.elem.getAttribute('optgroups') == 'optgroups' ? true : false;
	result.var_id = 'numbers_select_var_' + result.id;
	result.div_id = options.id + '_select_div';
	result.table_id = options.id + '_select_table';
	result.table_tr_class = options.id + '_select_table_tr_class';
	result.data = [];
	result.data_max_level = 0;
	result.replacement_div_id = options.id + '_select_replacement_div';
	// replacement elements
	var container = document.createElement("div");
	container.style.position = 'relative';
	var temp = '<div class="' + result.elem.className + ' numbers_select_icons" onclick="window[\'' + result.var_id + '\'].show();"><i class="fa fa-caret-down"></i></div>';
	temp+= '<div class="' + result.elem.className + ' numbers_select_replacement" id="' + result.replacement_div_id + '" onkeyup="window[\'' + result.var_id + '\'].onkeyup(event);" onkeydown="window[\'' + result.var_id + '\'].onkeydown(event);" tabindex="-1"' + (result.searchable ? ' contenteditable="true"' : '') + '></div>';
	temp+= '<div id="' + result.div_id + '" class="numbers_select_div" tabindex="-1" style="display:none;"></div>';
	container.innerHTML = temp;
	result.elem.parentNode.insertBefore(container, result.elem.nextSibling);
	// hide select element
	result.elem.style.display = 'none';
	// put elements into object
	result.replacement_div_elem = document.getElementById(result.replacement_div_id);
	result.div_elem = document.getElementById(result.div_id);
	// onfocus/onblur handlers
	result.replacement_div_elem.onfocus = function() {
		window[result.var_id].show(true);
	};
	result.replacement_div_elem.onblur = function() {
		window[result.var_id].onfocus(true);
	};
	// we need to insert div element right after input
	result.div_elem.onfocus = function () {
		window[result.var_id].onfocus();
	};
	result.div_elem.onblur = function () {
		window[result.var_id].onfocus(true);
	};
	// i18n
	if (options.i18n) {
		result.i18n = options.i18n;
	} else {
		result.i18n = {
			select: {short: 'Select All'},
			deselect: {short: 'None'},
			no_rows: {short: 'No options'}
		};
	}
	// calendar specific flags
	result.flag_data_prepered = false;
	result.flag_skeleton_rendered = false;
	result.flag_is_focused = false;

	/**
	 * Check for flag_is_focused flag
	 * @returns boolean
	 */
	result.check_if_focused = function () {
		return this.flag_is_focused;
	};

	/**
	 * Onkeydown handler
	 */
	result.onkeydown = function(event) {
		if (!this.elem.multiple) {
			return;
		}
		if (event) {
			var code = event.which || event.keyCode;
			// if backspace or delete
			if (!in_array(code, [8, 46])) {
				return;
			}
		}
		var node = document.getSelection().anchorNode;
		if (node.nodeType == 3) {
			node = node.parentNode;
		}
		if (node != undefined && node.nodeType === 1 && node.nodeName.toUpperCase() == 'SPAN') {
			if(node.className.indexOf('numbers_select_noneditable_item') != -1) {
				node.parentNode.removeChild(node);
				var id = node.getAttribute('search-id');
				this.unchoose(id);
				event.preventDefault();
				event.stopPropagation();
				// we need to remove last child span and add extra space
				if (this.replacement_div_elem.lastChild) {
					this.replacement_div_elem.removeChild(this.replacement_div_elem.lastChild);
					this.replacement_div_elem.innerHTML+= '&nbsp;';
				}
			}
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
		if (this.searchable) {
			this.filter();
		}
	};

	/**
	 * Get searc input
	 *
	 * @returns string
	 */
	result.get_search_input = function () {
		if (this.replacement_div_elem.lastChild && this.replacement_div_elem.lastChild.nodeType == 3) {
			return this.replacement_div_elem.lastChild.textContent.toLowerCase().trim();
		} else {
			return '';
		}
	};

	/**
	 * Filter rows
	 */
	result.filter = function () {
		var text = this.get_search_input();
		var trs = document.getElementsByClassName(this.table_tr_class), temp, counter = 0, none = 0;
		for (var i = 0; i < trs.length; i++) {
			temp = parseInt(trs[i].getAttribute('search-id'));
			if (this.data[temp].text_lower.indexOf(text) != -1) {
				trs[i].style.display = 'table-row';
				counter++;
			} else {
				trs[i].style.display = 'none';
				none++;
			}
		}
		// no rows
		document.getElementById(this.table_tr_class + '_no_rows').style.display = (counter == 0) ? 'table-row' : 'none';
		// we need to hide tree if we hid rows
		if (this.tree) {
			var table = document.getElementById(this.table_id);
			if (none) {
				if (table.className.indexOf('numbers_select_option_table_hide_tree') == -1) {
					table.className+= ' numbers_select_option_table_hide_tree';
				}
			} else {
				table.className = table.className.replace('numbers_select_option_table_hide_tree', '');
			}
		}
	};

	/**
	 * This will be triggered if something is selected
	 * @param string value
	 */
	result.chosen = function (id, tr) {
		if (this.elem.multiple) {
			if (this.elem.options[this.data[id].id].selected) {
				this.data[id].selected = false;
				this.elem.options[this.data[id].id].selected = false;
				tr.className = tr.className.replace('numbers_select_option_table_checked', '');
			} else {
				this.data[id].selected = true;
				this.elem.options[this.data[id].id].selected = true;
				tr.className+= ' numbers_select_option_table_checked';
			}
			this.render_value();
		} else {
			// we need to remove checked from previously selected rows
			if (this.elem.selectedIndex != -1) {
				trs = document.getElementById(this.table_id).getElementsByClassName('numbers_select_option_table_checked');
				for (var i = 0; i < trs.length; i++) {
					trs[i].className = trs[i].className.replace('numbers_select_option_table_checked', '');
				}
			}
			for (var i = 0; i < this.data.length; i++) {
				this.data[i].selected = false;
			}
			this.data[id].selected = true;
			this.elem.selectedIndex = this.data[id].id;
			tr.className+= ' numbers_select_option_table_checked';
			this.render_value();
			this.show();
		}
	};

	/**
	 * Unchoose
	 * @param string id
	 */
	result.unchoose = function (id) {
		var tr = document.getElementById(this.table_id).querySelector('tr[search-id=\'' + id + '\']');
		this.chosen(id, tr);
	};

	/**
	 * Select/deselect all
	 * @param boolean deselect
	 */
	result.select = function (deselect) {
		deselect = deselect ? false : true;
		for (var i = 0; i < this.data.length; i++) {
			if (!this.data[i].disabled) {
				this.elem.options[this.data[i].id].selected = deselect;
				this.data[i].selected = deselect;
			}
		}
		this.render_value();
		// checkmarks
		var trs = document.getElementsByClassName(this.table_tr_class);
		for (var i = 0; i < trs.length; i++) {
			if (!deselect) {
				trs[i].className = trs[i].className.replace('numbers_select_option_table_checked', '');
			} else {
				trs[i].className+= ' numbers_select_option_table_checked';
			}
		}
	};

	/**
	 * Render value
	 */
	result.render_value = function () {
		var html = '';
		if (this.elem.multiple) {
			// we need to refresh data
			if (!this.flag_data_prepered) {
				this.refresh_data();
				this.flag_data_prepered = true;
			}
			var text = this.get_search_input();
			this.replacement_div_elem.innerHTML = '';
			for (var i = 0; i < this.data.length; i++) {
				if (this.data[i].selected) {
					var span = document.createElement("span");
					//span.setAttribute('contenteditable', false);
					html = '';
					if (this.data[i].icon_class) {
						html+= '<i class="numbers_select_option_table_icon ' + this.data[i].icon_class + '"></i> ';
					}
					if (this.color_picker && this.data[i].value != '') {
						html+= '<span class="numbers_select_option_table_color" style="background-color:#' + this.data[i].value + ';">&nbsp;</span> ';
					}
					html+= this.data[i].text;
					html+= ' <a href="javascript: void(0);" class="numbers_select_option_multiple_item_close" onclick="window[\'' + result.var_id + '\'].unchoose(' + i + ');"><i class="fa fa-times"></i></a> ';
					span.innerHTML = html;
					span.className = 'numbers_select_multiple_item numbers_select_noneditable_item';
					span.setAttribute('search-id', i);
					this.replacement_div_elem.appendChild(span);
					this.replacement_div_elem.innerHTML+= '&nbsp;';
				}
			}
			this.replacement_div_elem.innerHTML+= text;
			if (text) {
				this.filter();
			}
		} else if (!result.elem.multiple && result.elem.selectedIndex != -1) {
			var icon_class = this.elem.options[result.elem.selectedIndex].getAttribute('icon_class');
			if (icon_class) {
				html+= '<i class="' + icon_class + '"></i> ';
			}
			if (this.color_picker && this.elem.options[result.elem.selectedIndex].value != '') {
				html+= '<span class="numbers_select_option_table_color" style="background-color:#' + this.elem.options[result.elem.selectedIndex].value + ';">&nbsp;</span> ';
			}
			html+= this.elem.options[result.elem.selectedIndex].text;
			result.replacement_div_elem.innerHTML = html;
		}
	};

	/**
	 * Onfocus processor
	 * @param boolean only_postponed_check
	 */
	result.onfocus = function (only_postponed_check) {
		// if we are processing postponed onblur
		if (only_postponed_check) {
			this.flag_is_focused = false;
			var that = this;
			setTimeout(function () {
				if (!that.check_if_focused()) {
					that.close();
				}
			}, 100);
		} else {
			this.flag_is_focused = true;
		}
	};

	/**
	 * Show
	 */
	result.show = function (only_show) {
		// render skeleton
		if (!this.flag_skeleton_rendered) {
			this.render_skeleton();
			this.flag_skeleton_rendered = true;
		}
		// hide/show
		if (this.div_elem.style.display != 'none' && !only_show) {
			this.close();
		} else {
			this.replacement_div_elem.focus();
			// handling ediatable div
			if (this.searchable) {
				if (!this.elem.multiple) {
					this.replacement_div_elem.innerHTML = '';
				}
			}
			this.flag_is_focused = true;
			this.onkeyup();
			this.div_elem.style.display = 'block'; // or table
		}
	};

	/**
	 * Close
	 */
	result.close = function () {
		this.render_value();
		this.div_elem.style.display = 'none';
		this.replacement_div_elem.blur();
	};

	/**
	 * Refresh data
	 */
	result.refresh_data = function () {
		this.data = [];
		var level = 0, elem, optgroup_label, index = 0, hash = {};
		// we need to add all/none options if multiple
		if (this.elem.multiple) {
			this.data[-1] = {
				id: 0,
				value: '',
				text: 'All/None',
				text_lower: '',
				disabled: true,
				selected: false,
				// optional
				level: 0,
				title: '',
				icon_class: '',
				text_right: ''
			};
		}
		for (var i = 0; i < this.elem.options.length; i++) {
			// processng level
			level = parseInt(this.elem.options[i].getAttribute('level'));
			elem = {
				// main attributes
				id: i,
				value: this.elem.options[i].value,
				text: this.elem.options[i].text,
				text_lower: this.elem.options[i].text.toLowerCase(),
				disabled: this.elem.options[i].disabled,
				selected: this.elem.options[i].selected,
				// optional
				level: level,
				title: this.elem.options[i].getAttribute('title'),
				icon_class: this.elem.options[i].getAttribute('icon_class'),
				text_right: this.elem.options[i].getAttribute('text_right')
			};
			// we need to adjust level for optgroups
			if (this.optgroups) {
				if (this.elem.options[i].parentNode && this.elem.options[i].parentNode.label) {
					this.tree = true;
					optgroup_label = this.elem.options[i].parentNode.label;
					if (!(optgroup_label in hash)) {
						this.data[index] = {
							id: 0,
							value: '',
							text: optgroup_label,
							text_lower: optgroup_label.toLowerCase(),
							disabled: true,
							selected: false,
							// optional
							level: 0,
							title: '',
							icon_class: '',
							text_right: ''
						};
						hash[optgroup_label] = index;
						index++;
					}
					elem.level = 1;
				}
			}
			// we need to update max level
			if (elem.level > this.data_max_level) {
				this.data_max_level = elem.level;
			}
			this.data[index] = elem;
			index++;
		}
	};

	/**
	 * Render skeleton
	 */
	result.render_skeleton = function () {
		if (!this.flag_data_prepered) {
			this.refresh_data();
			this.flag_data_prepered = true;
		}
		var i, j, k, colspan, status = '', hash = {}, hash2 = {};
		var html = '<table id="' + this.table_id + '" class="numbers_select_option_table" width="100%" cellpadding="0" cellspacing="0">';
			// select/deselect
			if (-1 in this.data) {
				html+= '<tr search-id="-1">';
					html+= '<td colspan="' + (this.data_max_level + 2) + '" valign="middle" class="numbers_select_option_table_td">';
						html+= '<a href="javascript: void(0);" onclick="window[\'' + result.var_id + '\'].select(false);">' + result.i18n.select.short + '</a> / <a href="javascript: void(0);" onclick="window[\'' + result.var_id + '\'].select(true);">' + result.i18n.deselect.short + '</a>';
					html+= '</td>';
				html+= '</tr>';
			}
			for (i = 0; i < this.data.length; i++) {
				if (this.data[i].disabled) {
					html+= '<tr class="' + this.table_tr_class + '" search-id="' + i + '">';
				} else {
					html+= '<tr onclick="' + this.var_id + '.chosen(' + i + ', this);" class="' + this.table_tr_class + (this.data[i].selected ? ' numbers_select_option_table_checked' : '') + ' numbers_select_option_table_tr_hover" search-id="' + i + '">';
				}
					if (this.data[i].level == 0) {
						hash2 = {};
					}
					if (this.data[i].level > 0) {
						for (j = 0; j < this.data[i].level; j++) {
							if (!result.tree) {
								html+= '<td class="numbers_select_option_table_level">&nbsp;</td>';
							} else {
								status = '';
								if (j < this.data[i].level) {
									for (k = i + 1; k < this.data.length; k++) {
										if (this.data[k].level == j) {
											status = 'next';
											break;
										}
									}
								}
								if (status == 'next' && hash2[j]) {
									status = 'blank';
								}
								if (status == 'next' && j == this.data[i].level - 1) {
									status = 'nextchild';
								}
								if (status == 'nextchild' && i + 1 < this.data.length) {
									if (this.data[i + 1].level < this.data[i].level) {
										if (j == 0) {
											hash2[j] = 1;
										}
										status = 'last';
									} else {
										for (k = i + 1; k < this.data.length; k++) {
											if (this.data[k].level == this.data[i].level) {
												break;
											}
											if (this.data[k].level < this.data[i].level) {
												if (j == 0) {
													hash2[j] = 1;
												}
												status = 'last';
												break;
											}
										}
									}
								}
								if (status == 'next') {
									for (k = i + 1; k < this.data.length; k++) {
										if (this.data[k].level >= j) {
											continue;
										} else {
											status = 'blank';
											break;
										}
									}
								}
								if (!status) {
									if (j < this.data[i].level) {
										for (k = i + 1; k < this.data.length; k++) {
											if (this.data[k].level == j + 1) {
												status = 'next';
												break;
											}
										}
									}
									if (!status) {
										if (!hash[j]) {
											hash[j] = 1;
											status = 'last';
										} else {
											status = 'blank';
										}
									}
									if (status == 'next' && j == this.data[i].level - 1) {
										status = 'nextchild';
									}
									if (status == 'nextchild' && i + 1 < this.data.length) {
										if (this.data[i + 1].level < this.data[i].level) {
											status = 'last';
										}
									}
									if (status == 'next') {
										for (k = i + 1; k < this.data.length; k++) {
											if (this.data[k].level >= j) {
												continue;
											} else {
												status = 'blank';
												break;
											}
										}
									}
								}
								switch (status) {
									case 'next':
										html+= '<td class="numbers_select_option_table_level"><span class="numbers_select_option_table_level_next">&nbsp;</span></td>';
										break;
									case 'last':
										html+= '<td class="numbers_select_option_table_level"><table class="numbers_select_option_table_level_last" cellpadding="0" cellspacing="0"><tr><td class="numbers_select_option_table_level_last_left">&nbsp;</td></tr><tr><td class="numbers_select_option_table_level_last_sep">&nbsp;</td></tr></table></td>';
										break;
									case 'nextchild':
										html+= '<td class="numbers_select_option_table_level"><table class="numbers_select_option_table_level_nextchild" cellpadding="0" cellspacing="0"><tr><td>&nbsp;</td></tr><tr><td class="numbers_select_option_table_level_nextchild_sep">&nbsp;</td></tr></table></td>';
										break;
									case 'blank':
										html+= '<td class="numbers_select_option_table_level"></td>';
										break;
									default:
										html+= '<td class="numbers_select_option_table_level">1</td>';
								}
							}
						}
						colspan = this.data_max_level - this.data[i].level + 1;
					} else {
						colspan = this.data_max_level + 1;
					}
					html+= '<td colspan="' + colspan + '" valign="middle" class="numbers_select_option_table_td">';
						if (this.data[i].icon_class) {
							html+= '<i class="numbers_select_option_table_icon ' + this.data[i].icon_class + '"></i> ';
						}
						if (this.color_picker && this.data[i].value != '') {
							html+= '<span class="numbers_select_option_table_color" style="background-color:#' + this.data[i].value + ';">&nbsp;</span> ';
						}
						html+= this.data[i].text;
					html+= '</td>';
					html+= '<td width="1%">';
						html+= '<i class="fa numbers_select_option_table_checked_icon"></i>';
					html+= '</td>';
				html+= '</tr>';
			}
			// no rows found notification
			html+= '<tr id="' + this.table_tr_class + '_no_rows" style="display:none;">';
				html+= '<td colspan="' + this.data_max_level + '">';
					html+= result.i18n.no_rows.short;
				html+= '</td>';
				html+= '<td width="1%">&nbsp;</td>';
			html+= '</tr>';
		html+= '</table>';
		// adding content to the div
		this.div_elem.innerHTML = html;
	};

	// we need to set a variable in global scope
	result.render_value();
	window[result.var_id] = result;
};

/**
 * Extending JQuery if loaded
 */
if (window.jQuery) {
	(function ($) {
		$.fn.numbers_select = function (options) {
			if (!options) options = {};
			// loop through all elements
			return this.each(function () {
				var elem = $(this), id = elem.attr('id'), options2 = $.extend({}, options);
				if (!id) {
					id = 'numbers_select_random_generated_id_' + Math.round(Math.random() * 1000) + '_' + Math.round(Math.random() * 1000) + '_' + Math.round(Math.random() * 1000);
					elem.attr('id', id);
				}
				options2.id = id;
				numbers_select(options2);
			});
		};
	})(jQuery);
}