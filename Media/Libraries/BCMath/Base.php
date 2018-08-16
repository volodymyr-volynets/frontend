<?php

namespace Numbers\Frontend\Media\Libraries\BCMath;
class Base implements \Numbers\Frontend\Media\CDN\Interface2 {

	/**
	 * Add media to layout
	 */
	public static function add() {
		\Layout::addJs('/numbers/media_submodules/Numbers_Frontend_Media_Libraries_BCMath_Media_JS_BCMath.js', -6000);
	}
}