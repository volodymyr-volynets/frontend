/* js fixes for bootstrap */
$(document).ready(function() {
	if ($('.navbar-header').length) {
		setTimeout(function(){ bootstrap_fix_navbar(); }, 10);
		bootstrap_fix_navbar_submenus();
		// resize handler
		$(window).resize(function() {
			bootstrap_fix_navbar();
		});
		// fix hover
		$('.navbar-nav-li-level1').hover(function() {
			$(this).addClass('open');
		},
		function() {
			$(this).removeClass('open');
		});
	}
});

/**
 * Fixes for navigation - submenus
 */
function bootstrap_fix_navbar_submenus() {
	$('.dropdown-submenu').mouseover(function() {
		var width = $(this).width();
		if (width == 0) {
			width = 200;
		}
		if ($(window).width() - $(this).offset().left - width < 201) {
			if(!$(this).hasClass("dropdown-submenu-pull-left")) {
				$(this).addClass('dropdown-submenu-pull-left');
			}
		}
	});
}

/**
 * Fixes for navigation - in general
 */
function bootstrap_fix_navbar() {
	// we need to check if navbar-toggle button is visible
	if ($('.navbar-toggle').is(':visible')) {
		$('li.navbar-nav-others').css('display', 'none');
		$('li.navbar-nav-li-level1').each(function(index, obj) {
			$(obj).css('display', 'inline');
		});
		return;
	}
	var first_y = $('.navbar-header').offset().top, last = null, hash = {}, hidden = 0;
	var data = [];
	$('li.navbar-nav-others').css('display', 'none');
	$('li.navbar-nav-li-level1').each(function(index, obj) {
		$(obj).css('display', 'inline');
		data.push(obj);
	});
	for (var i = data.length - 1; i >= 0; i--) {
		var top = $(data[i]).offset().top;
		if (top - first_y >= 10) {
			$(data[i]).css('display', 'none');
			hash[$(data[i]).attr('search-id')] = 1;
			hidden++;
		} else {
			last = data[i];
			break;
		}
	}
	// if we have hidden menu items
	if (hidden > 0) {
		$('.navbar-nav').css('display', 'table');
		// hide last element
		$(last).css('display', 'none');
		hash[$(last).attr('search-id')] = 1;
		$('li.navbar-nav-others').css('display', 'inline');
		$('li.navbar-nav-li-level1-others').each(function(index, obj) {
			if (hash[$(obj).attr('search-id')]) {
				// must be block, inline causes with absolute left positioning
				$(obj).css('display', 'block');
			} else {
				$(obj).css('display', 'none');
			}
		});
	}
}

/**
 * Modal interfaces
 *
 * @type object
 */
Numbers.Modal = {
	/**
	 * Show modal
	 *
	 * @param string id
	 */
	show: function(id) {
		$('#' + id).modal('show');
	},
	/**
	 * Hide modal
	 *
	 * @param string id
	 */
	hide: function(id) {
		$('#' + id).modal('hide');
		$('#' + id).removeClass("in");
		$('.modal-backdrop').remove();
	}
};

/**
 * Menu
 *
 * @type object
 */
Numbers.Menu = {

	/**
	 * Name generator
	 *
	 * @type object
	 */
	name_generator: {},

	/**
	 * Update menu items
	 */
	updateItems: function() {
		if (empty(this.name_generator)) return;
		for (var i in this.name_generator) {
			$.ajax({
				url: this.name_generator[i],
				method: 'post',
				data: '__ajax=1',
				dataType: 'json',
				success: function (data) {
					if (data.success) {
						$('#menu_item_id_' + i).html(data.data);
					}
				}
			});
		}
	}
};