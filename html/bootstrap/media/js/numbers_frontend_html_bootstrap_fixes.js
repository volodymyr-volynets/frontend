/* js fixes for bootstrap */
$(document).ready(function() {
	setTimeout(function(){ bootstrap_fix_navbar(); }, 10);
	bootstrap_fix_navbar_submenus();
	// resize handler
	$(window).resize(function() {
		bootstrap_fix_navbar();
	});
});

/**
 * Fixes for navigation - submenus
 */
function bootstrap_fix_navbar_submenus() {
	$('.dropdown-submenu').mouseover(function() {
		if ($(window).width() - $(this).offset().left - $(this).width() < 200) {
			if(!$(this).hasClass("dropdown-submenu-pull-left")){
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
		hidden++;
		$('li.navbar-nav-others').css('display', 'inline');
		$('li.navbar-nav-li-level1-others').each(function(index, obj) {
			if (hash[$(obj).attr('search-id')]) {
				$(obj).css('display', 'inline');
			} else {
				$(obj).css('display', 'none');
			}
		});
	}
}