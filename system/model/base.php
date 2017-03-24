<?php

class numbers_frontend_system_model_base {

	/**
	 * Start
	 */
	public static function start() {
		Layout::add_js('/numbers/media_submodules/numbers_frontend_system_media_js_functions.js', -32200);
		Layout::add_js('/numbers/media_submodules/numbers_frontend_system_media_js_base.js', -32100);
		Layout::add_js('/numbers/media_submodules/numbers_frontend_system_media_js_format.js', -32045);
	}
}