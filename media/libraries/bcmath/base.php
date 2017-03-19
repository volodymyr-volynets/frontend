<?php

class numbers_frontend_media_libraries_bcmath_base implements numbers_frontend_media_cdn_interface {

	/**
	 * Add media to layout
	 */
	public static function add() {
		layout::add_js('/numbers/media_submodules/numbers_frontend_media_libraries_bcmath_media_js_bcmath.js', -6000);
	}
}