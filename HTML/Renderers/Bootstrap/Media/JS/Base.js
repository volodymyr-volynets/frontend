
$(document).ready(function() {
	// menu click
	$('.dropdown-menu a.dropdown-toggle').on('click', function (event) {
		var elem = $(this);
		// if we are in desktop mode
		if ($(window).width() > 991) {
			var href = elem.attr('href');
			if (href != 'javascript:void(0);') {
				window.location.href = href;
				return true;
			}
		}
		var parent = $(this).offsetParent('.dropdown-menu');
		if (!$(this).next().hasClass('show')) {
			$(this).parents('.dropdown-menu').first().find('.show').removeClass('show');
		} else {
			var href = elem.attr('href');
			if (href != 'javascript:void(0);') {
				window.location.href = href;
				return true;
			}
		}
		var submenu = $(this).next('.dropdown-menu');
		submenu.toggleClass('show');
		$(this).parent('li').toggleClass('show');
		$(this).parents('li.nav-item.dropdown.show').on('hidden.bs.dropdown', function (e) {
			$('.dropdown-menu .show').removeClass('show');
		});
		if (!parent.parent().hasClass('navbar-nav')) {
			var position = $(elem.next()).position();
			if (position.left < 0) {
				elem.next().css({"top": elem[0].offsetTop, "left": -parent.outerWidth()});
			} else {
				elem.next().css({"top": elem[0].offsetTop, "left": parent.outerWidth()});
			}
		}
		return false;
	});
});

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
				data: '__ajax=1&item=' + i,
				dataType: 'json',
				success: function (data) {
					if (data.success) {
						$('#menu_item_id_' + data.item).html(data.data);
					}
				}
			});
		}
	}
};