<?php

class numbers_frontend_media_libraries_bcmath_base implements \Numbers\Frontend\Media\CDN\Interface2 {

	/**
	 * Add media to layout
	 */
	public static function add() {
		\Layout::addJs('/numbers/media_submodules/numbers_frontend_media_libraries_bcmath_media_js_bcmath.js', -6000);
	}
}