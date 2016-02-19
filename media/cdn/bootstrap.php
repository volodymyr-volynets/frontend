<?php

class numbers_frontend_media_cdn_bootstrap implements numbers_frontend_media_cdn_interface {

	/**
	 * Add media to layout
	 */
	public static function add() {
		layout::add_css('//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css', 10000);
		layout::add_css('/numbers/media_submodules/numbers_frontend_html_bootstrap_fixes.css', 10001);
		layout::add_js('//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js', 10000);
		layout::add_js('/numbers/media_submodules/numbers_frontend_html_bootstrap_fixes.js', 10001);
	}
}