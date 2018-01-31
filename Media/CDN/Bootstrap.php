<?php

namespace Numbers\Frontend\Media\CDN;
class Bootstrap implements \Numbers\Frontend\Media\CDN\Interface2 {

	/**
	 * Add media to layout
	 */
	public static function add() {
		\Layout::addCss('https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css', 10000);
		//\Layout::addJs('https://code.jquery.com/jquery-3.2.1.slim.min.js', 10001);
		\Layout::addJs('http://code.jquery.com/jquery-3.3.1.min.js', 10001);
		\Layout::addJs('https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js', 10002);
		\Layout::addJs('https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js', 10003);
		\Layout::addCss('/numbers/media_submodules/Numbers_Frontend_HTML_Renderers_Bootstrap_Media_CSS_Base.css', 10004);
		\Layout::addJs('/numbers/media_submodules/Numbers_Frontend_HTML_Renderers_Bootstrap_Media_JS_Base.js', 10004);
	}
}