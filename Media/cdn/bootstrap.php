<?php

namespace Numbers\Frontend\Media\CDN;
class Bootstrap implements \Numbers\Frontend\Media\CDN\Interface2 {

	/**
	 * Add media to layout
	 */
	public static function add() {
		\Layout::addCss('//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css', 10000);
		\Layout::addJs('//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js', 10000);
		\Layout::addCss('/numbers/media_submodules/Numbers_Frontend_HTML_Renderers_Bootstrap_Media_CSS_Base.css', 10001);
		\Layout::addJs('/numbers/media_submodules/Numbers_Frontend_HTML_Renderers_Bootstrap_Media_JS_Base.js', 10001);
	}
}