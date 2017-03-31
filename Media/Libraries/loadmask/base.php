<?php

namespace Numbers\Frontend\Media\Libraries\LoadMask;
class Base implements \Numbers\Frontend\Media\CDN\Interface2 {

	/**
	 * Add media to layout
	 */
	public static function add() {
		\Layout::addJs('/numbers/media_submodules/numbers_frontend_media_libraries_loadmask_media_js_spinner.js', 10005);
		\Layout::addJs('/numbers/media_submodules/numbers_frontend_media_libraries_loadmask_media_js_base.js', 10010);
		\Layout::addCss('/numbers/media_submodules/numbers_frontend_media_libraries_loadmask_media_css_base.css', 10000);
	}
}