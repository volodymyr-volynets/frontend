<?php

class numbers_frontend_media_libraries_jssha_base implements numbers_frontend_media_cdn_interface {

	/**
	 * Add media to layout
	 */
	public static function add() {
		Layout::add_js('/numbers/media_submodules/numbers_frontend_media_libraries_jssha_media_js_sha.js', -5001);
		Layout::add_js('/numbers/media_submodules/numbers_frontend_media_libraries_jssha_media_js_functions.js', -5000);
	}
}