<?php

class numbers_frontend_media_libraries_loadmask_base implements numbers_frontend_media_cdn_interface {

	/**
	 * Add media to layout
	 */
	public static function add() {
		layout::add_js('/numbers/media_submodules/numbers_frontend_media_libraries_loadmask_media_js_spinner.js', 10005);
		layout::add_js('/numbers/media_submodules/numbers_frontend_media_libraries_loadmask_media_js_base.js', 10010);
		layout::add_css('/numbers/media_submodules/numbers_frontend_media_libraries_loadmask_media_css_base.css', 10000);
	}
}