<?php

namespace Numbers\Frontend\Media\CDN;
class Bootstrap implements \Numbers\Frontend\Media\CDN\Interface2 {

	/**
	 * Add media to layout
	 */
	public static function add() {
		\Layout::addCss('//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css', 10000);
		\Layout::addJs('//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js', 10000);
		\Layout::addCss('/numbers/media_submodules/numbers_frontend_html_renderers_bootstrap_media_css_base.css', 10001);
		\Layout::addJs('/numbers/media_submodules/numbers_frontend_html_renderers_bootstrap_media_js_base.js', 10001);
	}
}