<?php

namespace Numbers\Frontend\System\Model;
class Base {

	/**
	 * Start
	 */
	public static function start() {
		\Layout::addJs('/numbers/media_submodules/Numbers_Frontend_System_Media_JS_Functions.js', -32200);
		\Layout::addJs('/numbers/media_submodules/Numbers_Frontend_System_Media_JS_Base.js', -32100);
		\Layout::addJs('/numbers/media_submodules/Numbers_Frontend_System_Media_JS_Format.js', -32045);
	}
}