<?php

class numbers_frontend_media_cdn_bootstrap implements numbers_frontend_media_cdn_interface {

	/**
	 * Add media to layout
	 */
	public static function add() {
		Layout::add_css('//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css', 10000);
		Layout::add_js('//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js', 10000);
		Layout::add_css('/numbers/media_submodules/numbers_frontend_html_renderers_bootstrap_media_css_base.css', 10001);
		Layout::add_js('/numbers/media_submodules/numbers_frontend_html_renderers_bootstrap_media_js_base.js', 10001);
	}
}