<?php

namespace Numbers\Frontend\Media\Libraries\jsSHA;
class Base implements \Numbers\Frontend\Media\CDN\Interface2 {

	/**
	 * Add media to layout
	 */
	public static function add() {
		\Layout::addJs('/numbers/media_submodules/numbers_frontend_media_libraries_jssha_media_js_sha.js', -5001);
		\Layout::addJs('/numbers/media_submodules/numbers_frontend_media_libraries_jssha_media_js_functions.js', -5000);
	}
}