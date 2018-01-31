<?php

namespace Numbers\Frontend\Media\Libraries\LoadMask;
class Base implements \Numbers\Frontend\Media\CDN\Interface2 {

	/**
	 * Add media to layout
	 */
	public static function add() {
		\Layout::addJs('/numbers/media_submodules/Numbers_Frontend_Media_Libraries_LoadMask_Media_JS_Spinner.js', 10005);
		\Layout::addJs('/numbers/media_submodules/Numbers_Frontend_Media_Libraries_LoadMask_Media_JS_Base.js', 10010);
		\Layout::addCss('/numbers/media_submodules/Numbers_Frontend_Media_Libraries_LoadMask_Media_CSS_Base.css', 10000);
	}
}