<?php

namespace Numbers\Frontend\Media\Libraries\jsSHA;
class Base implements \Numbers\Frontend\Media\CDN\Interface2 {

	/**
	 * Add media to layout
	 */
	public static function add() {
		\Layout::addJs('/numbers/media_submodules/Numbers_Frontend_Media_Libraries_jsSHA_Media_JS_SHA.js', -5001);
		\Layout::addJs('/numbers/media_submodules/Numbers_Frontend_Media_Libraries_jsSHA_Media_JS_Functions.js', -5000);
	}
}